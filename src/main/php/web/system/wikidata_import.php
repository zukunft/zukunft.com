<?php

/*

    web/system/wikidata_import.php - the preview and the confirm of the import of the values of wikidata
    ------------------------------

    the frontend side of api/wikidataImport: asks the backend for the preview of the values that wikidata
    would add to a phrase or to an entity of a wikidata id, shows the new words, triples and values, the
    values that are already in line and the conflicts, and sends the confirmed import to the backend;
    the confirm carries only the checksum of the previewed import json, because the form is sent as a
    get request, so the backend is asked for the preview again and the import is only written if the
    user has seen the same import


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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::API_OBJECT . 'api_message.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class wikidata_import
{

    // the name of the confirm form of the preview
    const string FORM_NAME = 'import_preview';
    // the separator of the words of a value in a table cell e.g. "population, Zurich (city), 2021"
    const string WORD_SEPARATOR = ', ';

    // the answer of the backend (see cfg/import/import_wikidata_preview.php::compared)
    public array $preview;

    /**
     * @param array $preview the preview of the backend, which a unit test sets without a backend call
     */
    function __construct(array $preview = [])
    {
        $this->preview = $preview;
    }


    /*
     * api
     */

    /**
     * ask the backend for the preview of the phrase (url_var::PHRASE) or of the wikidata id
     * (url_var::EXTERNAL_KEY) of the page url
     *
     * @param array $url_arr the url vars of the preview page
     * @param user_message $msg with the requesting user and to tell why nothing can be imported
     * @return bool true if the backend has answered with a preview
     */
    function load(array $url_arr, user_message $msg): bool
    {
        $this->preview = [];
        $data = $this->request_vars($url_arr);
        if ($data == []) {
            // the refresh icon and the admin form always name the phrase or the wikidata id
            log_err_msg_ui('the import preview has been called without a phrase or a wikidata id', $msg);
        } else {
            $rest = new rest_call();
            $body = new api_message()->validate($rest->api_get(self::class, $data, $msg));
            if (array_key_exists(json_fields::MSG, $body)) {
                $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $body[json_fields::MSG]]);
            } else {
                $this->preview = $body;
            }
        }
        return $this->preview != [];
    }

    /**
     * the import json of the preview that the user has confirmed, but only if the preview of the backend is still
     * the one that the user has seen, because the confirm url carries only its checksum (see frontend::action_import)
     *
     * @param array $url_arr the url vars of the confirm with the phrase or the wikidata id and the checksum
     * @param user_message $msg with the requesting user, who writes the import, and to tell why nothing is written
     * @return array the import json to write or an empty array if nothing is written, which is reported
     */
    function confirmed_import(array $url_arr, user_message $msg): array
    {
        $result = [];
        if ($msg->usr == null) {
            $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => self::class]);
        } elseif ($this->load($url_arr, $msg)) {
            if ($this->checksum() != (string)($url_arr[url_var::IMPORT_CHECKSUM] ?? '')) {
                $msg->add_id(msg_id::IMPORT_PREVIEW_CHANGED);
            } else {
                $result = $this->import_json();
            }
        }
        return $result;
    }

    /**
     * @param array $url_arr the url vars of the preview page
     * @return array the url vars of the api call: the phrase or the wikidata id, empty if the url names none
     */
    private function request_vars(array $url_arr): array
    {
        $result = [];
        $phr_id = (int)($url_arr[url_var::PHRASE] ?? 0);
        $key = (string)($url_arr[url_var::EXTERNAL_KEY] ?? '');
        if ($phr_id != 0) {
            $result[url_var::PHRASE] = $phr_id;
        } elseif ($key != '') {
            $result[url_var::EXTERNAL_KEY] = $key;
        }
        return $result;
    }

    /**
     * @return array the import json that the confirm writes
     */
    private function import_json(): array
    {
        return $this->preview[json_fields::PREVIEW_IMPORT] ?? [];
    }

    /**
     * @return string the checksum of the import json, so that the confirm imports only what the user has seen
     */
    function checksum(): string
    {
        return md5(json_encode($this->import_json()));
    }


    /*
     * html
     */

    /**
     * @param array $url_arr the url vars of the preview page, which the confirm form repeats
     * @param user_message $msg to tell the user that nothing is to import
     * @return string the html code of the preview with the confirm, empty without preview, which the messages explain
     */
    function html(array $url_arr, user_message $msg): string
    {
        $result = '';
        if ($this->preview != []) {
            $html = new html_base();
            $result .= $this->name_list(msg_id::IMPORT_PREVIEW_WORDS_NEW, $this->preview[json_fields::PREVIEW_WORDS_NEW] ?? []);
            $result .= $this->name_list(msg_id::IMPORT_PREVIEW_TRIPLES_NEW, $this->preview[json_fields::PREVIEW_TRIPLES_NEW] ?? []);
            $result .= $this->value_table(msg_id::IMPORT_PREVIEW_VALUES_NEW, $this->preview[json_fields::PREVIEW_VALUES_NEW] ?? []);
            $result .= $this->conflict_table($this->preview[json_fields::PREVIEW_VALUES_CONFLICT] ?? []);
            $result .= $this->value_table(msg_id::IMPORT_PREVIEW_VALUES_SAME, $this->preview[json_fields::PREVIEW_VALUES_SAME] ?? []);
            $problems = (string)($this->preview[json_fields::PREVIEW_PROBLEMS] ?? '');
            if ($problems != '') {
                global $mtr;
                $result .= $html->text_h3($mtr->txt(msg_id::IMPORT_PREVIEW_PROBLEMS));
                $result .= $html->p(htmlspecialchars($problems, ENT_QUOTES));
            }
            if ($this->is_in_line()) {
                $vars = [
                    msg_id::VAR_NAME => (string)($this->preview[json_fields::NAME] ?? ''),
                    msg_id::VAR_ID => (string)($this->preview[json_fields::EXTERNAL_KEY] ?? ''),
                ];
                $msg->add(msg_id::WIKIDATA_IN_LINE, $vars, true);
            } else {
                $result .= $this->confirm_form($url_arr);
            }
        }
        return $result;
    }

    /**
     * @return bool true if all values of wikidata are already in the pod, so that nothing is to import
     */
    private function is_in_line(): bool
    {
        return ($this->preview[json_fields::PREVIEW_VALUES_NEW] ?? []) == []
            and ($this->preview[json_fields::PREVIEW_VALUES_CONFLICT] ?? []) == [];
    }

    /**
     * @param msg_id $title_id the title of the list e.g. the new words
     * @param array $names the names e.g. of the new words, which come from wikidata, so they are escaped
     * @return string the html code of the title and the names, empty if there is no name
     */
    private function name_list(msg_id $title_id, array $names): string
    {
        global $mtr;
        $result = '';
        if ($names != []) {
            $html = new html_base();
            $escaped = array_map(fn($name) => htmlspecialchars((string)$name, ENT_QUOTES), $names);
            $result = $html->text_h3($mtr->txt($title_id)) . $html->p(implode(self::WORD_SEPARATOR, $escaped));
        }
        return $result;
    }

    /**
     * @param msg_id $title_id the title of the table e.g. the new values
     * @param array $values the values of the import json with their words and number
     * @return string the html code of the title and the table with one row per value, empty if there is no value
     */
    private function value_table(msg_id $title_id, array $values): string
    {
        global $mtr;
        $result = '';
        if ($values != []) {
            $html = new html_base();
            $rows = '';
            foreach ($values as $val_json) {
                $rows .= $html->tr($html->td($this->words_text($val_json)) . $html->td($this->number_text($val_json)));
            }
            $result = $html->text_h3($mtr->txt($title_id)) . $html->tbl($rows);
        }
        return $result;
    }

    /**
     * @param array $conflicts the values of wikidata with another number in the pod (see import_wikidata_preview::conflict)
     * @return string the html code of the title and the table with the number of wikidata, the number of the pod
     *                and how the confirm imports it, empty if there is no conflict
     */
    private function conflict_table(array $conflicts): string
    {
        global $mtr;
        $result = '';
        if ($conflicts != []) {
            $html = new html_base();
            $lib = new library();
            $rows = '';
            foreach ($conflicts as $conflict) {
                $word = (string)($conflict[json_fields::PREVIEW_CONFLICT_WORD] ?? '');
                if ($word == '') {
                    $how = $mtr->txt(msg_id::IMPORT_PREVIEW_AS_USER_VALUE);
                } else {
                    $how = $lib->msg_var_text([[msg_id::IMPORT_PREVIEW_WITH_WORD, [msg_id::VAR_NAME => $word]]], $mtr);
                }
                $old = (string)($conflict[json_fields::PREVIEW_NUMBER_OLD] ?? '');
                $rows .= $html->tr(
                    $html->td($this->words_text($conflict))
                    . $html->td($this->number_text($conflict))
                    . $html->td(htmlspecialchars($old, ENT_QUOTES))
                    . $html->td(htmlspecialchars($how, ENT_QUOTES)));
            }
            $result = $html->text_h3($mtr->txt(msg_id::IMPORT_PREVIEW_VALUES_CONFLICT)) . $html->tbl($rows);
        }
        return $result;
    }

    /**
     * @param array $val_json a value of the import json
     * @return string the escaped words of the value e.g. "population, Zurich (city), 2021"
     */
    private function words_text(array $val_json): string
    {
        $words = array_map(fn($word) => (string)$word, $val_json[json_fields::WORDS] ?? []);
        return htmlspecialchars(implode(self::WORD_SEPARATOR, $words), ENT_QUOTES);
    }

    /**
     * @param array $val_json a value of the import json
     * @return string the escaped number of wikidata e.g. "421878"
     */
    private function number_text(array $val_json): string
    {
        return htmlspecialchars((string)($val_json[json_fields::NUMBER] ?? ''), ENT_QUOTES);
    }

    /**
     * @param array $url_arr the url vars of the preview page with the phrase or the wikidata id and the back target
     * @return string the html code of the form whose button writes the import of this preview
     */
    private function confirm_form(array $url_arr): string
    {
        global $mtr;
        $html = new html_base();
        $result = $html->form_start(self::FORM_NAME);
        $result .= $html->form_hidden(url_var::MASK, (string)views::IMPORT_PREVIEW_ID, self::FORM_NAME);
        foreach ($this->request_vars($url_arr) as $key => $val) {
            $result .= $html->form_hidden($key, (string)$val, self::FORM_NAME);
        }
        foreach ([url_var::BACK . url_var::MASK, url_var::BACK . url_var::ID] as $back_key) {
            if (array_key_exists($back_key, $url_arr)) {
                $result .= $html->form_hidden($back_key, (string)$url_arr[$back_key], self::FORM_NAME);
            }
        }
        $result .= $html->form_hidden(url_var::ACTION, url_var::ACTION_IMPORT, self::FORM_NAME);
        $result .= $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRMED, self::FORM_NAME);
        $result .= $html->form_hidden(url_var::IMPORT_CHECKSUM, $this->checksum(), self::FORM_NAME);
        $result .= $html->button_bs($mtr->txt(msg_id::IMPORT_PREVIEW_CONFIRM), '', '', url_var::POST_SUBMIT);
        $result .= $html->form_end();
        return $result;
    }

    /**
     * the form of the admin view "Import from Wikidata": the wikidata id of the entity whose values are imported,
     * which opens the import preview like the refresh icon of a wikidata reference
     *
     * @return string the html code of the form with the wikidata id field
     */
    function key_form(): string
    {
        global $mtr;
        $html = new html_base();
        $result = $html->form_start(self::FORM_NAME);
        $result .= $html->form_field(url_var::EXTERNAL_KEY, msg_id::WIKIDATA_ID_FIELD, '', html_base::INPUT_TEXT,
            '', '', self::FORM_NAME);
        $result .= $html->form_hidden(url_var::MASK, (string)views::IMPORT_PREVIEW_ID, self::FORM_NAME);
        $result .= $html->form_hidden(url_var::BACK . url_var::MASK, (string)views::IMPORT_WIKIDATA_ID, self::FORM_NAME);
        // no submit name, because the button only opens the preview, which writes nothing
        $result .= $html->button_bs($mtr->txt(msg_id::IMPORT_PREVIEW_SHOW));
        $result .= $html->form_end();
        return $result;
    }
}
