<?php

/*

    cfg/ref/ref_link_wikipedia.php - take over the description of a phrase from wikipedia
    ------------------------------

    the refresh icon of a wikipedia reference asks the backend to read the summary of the
    wikipedia page, so that the user can confirm the lead text of the page as the description
    of the phrase and the wikidata item of the page as its wikidata reference


    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as
    published by the Free Software Foundation, either version 3 of
    the License, or (at your option) any later version.
    zukunft.com is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;
use DateTimeZone;

/**
 * $abbr $wik
 */
class ref_link_wikipedia
{

    // the summary api of the english wikipedia, because the wikipedia reference type links to the
    // english wikipedia (see ref_types.csv); the page name is added with an underscore for each space
    const string SUMMARY_URL = 'https://en.wikipedia.org/api/rest_v1/page/summary/';
    const string NAME_SPACE = ' ';
    const string PAGE_SPACE = '_';
    // the fields of the summary json
    const string WP_TYPE = 'type';
    const string WP_EXTRACT = 'extract';
    const string WP_WIKIDATA_ID = 'wikibase_item';
    // the time of the last revision of the page e.g. "2026-11-07T14:48:15Z", always in utc
    const string WP_TIMESTAMP = 'timestamp';
    const string WP_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    const string WP_TIME_ZONE = 'UTC';
    // only a standard page describes one thing, whereas e.g. a disambiguation page lists several
    const string WP_TYPE_STANDARD = 'standard';
    // the lead text is plain text, in which the paragraphs are separated by a new line
    const string PARAGRAPH_SEPARATOR = "\n";
    // a bracket of the lead text, which may contain one more bracket e.g. "(German: [ˈtsyːrɪç])"
    const string BRACKET_PATTERN = '/\s*\((?:[^()]|\([^()]*\))*\)/u';
    // a bracket is a pronunciation if it contains the ipa in slashes or square brackets or names it
    const string PRONUNCIATION_PATTERN = '/\/[^\/]+\/|\[[^\]]+\]|pronounced|pronunciation|listen|\bIPA\b/iu';
    const string WIKIDATA_ID_PATTERN = '/^Q[1-9][0-9]*$/';

    /**
     * take over the description and the wikidata id from the wikipedia page of the phrase if the
     * url asks for it (url_var::REFRESH_WIKIPEDIA); the change is only in memory, so that the
     * frontend can show it on the confirm page before it is saved
     *
     * @param word|triple $phr_obj the loaded phrase whose description should be taken from wikipedia
     * @param array $url_array the api request parameters with the requested refresh
     * @param user_message $msg to tell the user why nothing could be taken from wikipedia
     * @return void
     */
    function refresh_from_url(word|triple $phr_obj, array $url_array, user_message $msg): void
    {
        if (($url_array[url_var::REFRESH] ?? '') == url_var::REFRESH_WIKIPEDIA) {
            $phr_obj->load_references_related($msg);
            // like the api answer only the references that the user can read, e.g. never a private one of another user
            $phr_obj->references_related->filter_readable_by($msg->usr);
            $wiki_ref = $phr_obj->references_related->get_by_type_code_id(ref_types::WIKIPEDIA);
            if ($wiki_ref == null) {
                $msg->add(msg_id::WIKIPEDIA_REF_MISSING, [msg_id::VAR_NAME => $phr_obj->name()]);
            } else {
                $page = $wiki_ref->get_external_key() ?? '';
                $summary = $this->summary($page, $msg);
                if ($summary != []) {
                    $this->take_over($phr_obj, $wiki_ref, $summary, $page, $msg);
                }
            }
        }
    }

    /**
     * @param array $summary the summary json of a wikipedia page
     * @return string the first paragraph of the lead text without the pronunciation, empty if the page describes not one thing
     */
    function description(array $summary): string
    {
        $result = '';
        if (($summary[self::WP_TYPE] ?? '') == self::WP_TYPE_STANDARD) {
            // trimmed first, because e.g. the extract of the page Health starts with a line break
            $extract = trim($summary[self::WP_EXTRACT] ?? '');
            $paragraphs = explode(self::PARAGRAPH_SEPARATOR, $extract);
            $result = trim($this->without_pronunciation($paragraphs[0]));
        }
        return $result;
    }

    /**
     * @param string $txt a lead text of wikipedia e.g. "Zürich (German: [ˈtsyːrɪç]) is the largest city"
     * @return string the text without the brackets that contain a pronunciation e.g. "Zürich is the largest city"
     */
    function without_pronunciation(string $txt): string
    {
        return preg_replace_callback(self::BRACKET_PATTERN, [$this, 'bracket_without_pronunciation'], $txt);
    }

    /**
     * @param array $summary the summary json of a wikipedia page
     * @return string the wikidata id of the page e.g. "Q72", empty if the page names no valid wikidata id
     */
    function wikidata_id(array $summary): string
    {
        $wikidata_id = (string)($summary[self::WP_WIKIDATA_ID] ?? '');
        $result = '';
        if (preg_match(self::WIKIDATA_ID_PATTERN, $wikidata_id) === 1) {
            $result = $wikidata_id;
        }
        return $result;
    }

    /**
     * @param array $summary the summary json of a wikipedia page
     * @return DateTime|null the time of the last revision of the page, null if the page names no valid time
     */
    function page_time(array $summary): ?DateTime
    {
        $txt = (string)($summary[self::WP_TIMESTAMP] ?? '');
        $time = DateTime::createFromFormat(self::WP_TIME_FORMAT, $txt, new DateTimeZone(self::WP_TIME_ZONE));
        $result = null;
        // the format back refuses e.g. the month 13, which php would roll over into the next year
        if ($time !== false and $time->format(self::WP_TIME_FORMAT) === $txt) {
            $result = $time;
        }
        return $result;
    }

    /**
     * @param array $match the bracket found by BRACKET_PATTERN incl. the spaces before it
     * @return string empty if the bracket is a pronunciation, else the unchanged bracket
     */
    private function bracket_without_pronunciation(array $match): string
    {
        $result = $match[0];
        if (preg_match(self::PRONUNCIATION_PATTERN, $match[0]) === 1) {
            $result = '';
        }
        return $result;
    }

    /**
     * read the summary of a wikipedia page; public, so that a write test can compare what the live
     * page gives with the values of the last test run
     *
     * @param string $page the name of the wikipedia page as the external key of the reference e.g. "Zurich"
     * @param user_message $msg to tell the user that the page cannot be read
     * @return array the summary json of the page, empty if the page cannot be read
     */
    function summary(string $page, user_message $msg): array
    {
        $page_name = str_replace(self::NAME_SPACE, self::PAGE_SPACE, $page);
        $url = self::SUMMARY_URL . rawurlencode($page_name);
        $context = import_wikidata::request_context();
        // the @ suppresses the php warning of a failed request, which the user gets as message instead
        $json = @file_get_contents($url, false, $context);
        $result = [];
        if ($json !== false) {
            $result = json_decode($json, true) ?? [];
        }
        if (!is_array($result) or $result == []) {
            $result = [];
            $msg->add(msg_id::WIKIPEDIA_READ_FAILED, [msg_id::VAR_NAME => $page]);
        }
        return $result;
    }

    /**
     * set the description and the wikidata reference of the phrase based on the wikipedia summary
     * and the time of the page on the wikipedia reference, so that the frontend can name it
     *
     * @param word|triple $phr_obj the phrase that gets the description of the wikipedia page
     * @param ref $wiki_ref the wikipedia reference of the phrase that gets the time of the page
     * @param array $summary the summary json of the wikipedia page
     * @param string $page the name of the wikipedia page to name it in the message
     * @param user_message $msg to tell the user that the page has no lead text
     * @return void
     */
    private function take_over(word|triple $phr_obj, ref $wiki_ref, array $summary, string $page, user_message $msg): void
    {
        $description = $this->description($summary);
        if ($description == '') {
            $msg->add(msg_id::WIKIPEDIA_NO_DESCRIPTION, [msg_id::VAR_NAME => $page]);
        } else {
            $phr_obj->set_description($description);
            $this->set_wikidata_id($phr_obj, $this->wikidata_id($summary));
            $wiki_ref->set_page_time($this->page_time($summary));
        }
    }

    /**
     * change the external key of the wikidata reference of the phrase or add a new one, which is
     * saved only after the user has confirmed it
     *
     * @param word|triple $phr_obj the phrase with the loaded references
     * @param string $wikidata_id the wikidata id of the wikipedia page e.g. "Q72"
     * @return void
     */
    private function set_wikidata_id(word|triple $phr_obj, string $wikidata_id): void
    {
        global $sys;
        // a page without a wikidata item keeps the wikidata reference of the phrase as it is
        if ($wikidata_id != '') {
            $wd_ref = $phr_obj->references_related->get_by_type_code_id(ref_types::WIKIDATA);
            if ($wd_ref == null) {
                $wd_ref = new ref($phr_obj->get_user());
                $wd_type_id = $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA);
                $wd_ref->set(0, $phr_obj->phrase(), $wd_type_id, $wikidata_id);
                $phr_obj->references_related->add_direct($wd_ref);
            } else {
                $wd_ref->set_external_key($wikidata_id);
            }
        }
    }

}
