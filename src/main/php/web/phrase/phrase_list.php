<?php

/*

    web/phrase/phrase_list.php - create the html code to display a phrase list
    --------------------------

    TODO create a value matrix based on this phrase list


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\phrase;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

//include_once html_paths::SANDBOX . 'sandbox_list_named.php';
include_once html_paths::GROUP . 'group.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
//include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VERB . 'verb.php';
include_once html_paths::VERB . 'verb_list.php';
include_once html_paths::VIEW . 'view_list.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'triple_list.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::WORD . 'word_list.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_ENUM . 'foaf_direction.php';
include_once html_paths::SHARED_ENUM . 'languages.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\group\group;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list_named;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\triple_list;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\word\word_list;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class phrase_list extends sandbox_list_named
{

    // the link levels below "column (system)": the column tiers and their column definitions
    const int COLUMN_LEVELS = 2;

    /*
     * set and get
     */

    /**
     * set the vars of this phrase list frontend object based on the url array
     * the comma-separated phrase ids are read from the CONTEXT field;
     * the sign of each id encodes the class (positive = word, negative = triple)
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        if (array_key_exists(url_var::CONTEXT, $url_array)) {
            $id_csv = $url_array[url_var::CONTEXT];
            if ($id_csv !== '' && $id_csv !== null) {
                foreach (explode(',', $id_csv) as $id_str) {
                    $id_int = (int)$id_str;
                    $phr = new phrase();
                    // positive phrase id = word, negative = triple (see phrase::id())
                    if ($id_int >= 0) {
                        $phr->set_obj(new word());
                    } else {
                        $phr->set_obj(new triple());
                    }
                    $phr->set_id($id_int);
                    $this->add($phr, $msg);
                }
            }
        }
        return $msg;
    }

    /**
     * set the vars of a phrase list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new phrase());
    }


    /*
     * load
     */

    /**
     * add the phrases related to the given phrase to the list
     * @param phrase $phr
     * @param foaf_direction $direction
     * @param verb_list|null $link_types
     * @return bool
     */
    function load_related(phrase $phr, foaf_direction $direction, ?verb_list $link_types = null): bool
    {
        $result = false;

        // TODO move the
        $api = new rest_call();
        $data = array();
        $data[url_var::PHRASE] = $phr->id();
        $data[url_var::DIRECTION] = $direction->value;
        $data[url_var::LEVELS] = 1;
        $json_body = $api->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * add the phrases related to the phrase with the given name to the list
     *
     * by name and not by id, because the frontend knows a system phrase from a shared const with
     * its name, but never its database id (see docs/llm/constants.md)
     *
     * @param string $name the name of the phrase whose related phrases should be added
     * @param foaf_direction $direction up for the parents, down for the children
     * @param user_message $msg to report a problem of the api message to the user
     * @param int $levels the number of link levels to follow, one for the direct links only
     * @return bool true if at least one phrase has been added to this list
     */
    function load_related_by_name(
        string         $name,
        foaf_direction $direction,
        user_message   $msg,
        int            $levels = 1
    ): bool
    {
        $count = $this->count();
        $api = new rest_call();
        $data = array();
        $data[url_var::NAME] = $name;
        $data[url_var::DIRECTION] = $direction->value;
        $data[url_var::LEVELS] = $levels;
        $json_body = $api->api_get(self::class, $data);
        $msg->merge($this->api_mapper($json_body));
        return $this->count() > $count;
    }

    /**
     * add the phrases linked to any of the given phrases to this list
     *
     * one call for the whole list, because a page normally needs the links of many phrases at
     * once, e.g. of every phrase that the values of a table carry
     *
     * @param phrase_list $phr_lst the phrases whose linked phrases should be added
     * @param foaf_direction $direction up for the parents, down for the children
     * @param user_message $msg to report a problem of the api message to the user
     * @return bool true if at least one phrase has been added to this list
     */
    function load_related_by_ids(
        phrase_list    $phr_lst,
        foaf_direction $direction,
        user_message   $msg
    ): bool
    {
        $count = $this->count();
        $api = new rest_call();
        $data = array();
        $data[url_var::ID_LST] = implode(',', $phr_lst->ids());
        $data[url_var::DIRECTION] = $direction->value;
        $json_body = $api->api_get(self::class, $data);
        $msg->merge($this->api_mapper($json_body));
        return $this->count() > $count;
    }

    /**
     * add the triples that define the table columns to this list
     *
     * a column is defined by a triple that links a phrase to a system column tier, e.g. the
     * triple "column loss" links "loss" to "mayor column (system)", so the definitions of one
     * tier are the triples that point to the tier phrase; without them a value table falls back
     * to the impact ranking instead of the column order the tiers and the order triples give
     *
     * the tiers themselves point to "column (system)", so two levels below that phrase are the
     * tiers and their definitions, which is why one api call fills the cache
     *
     * @param user_message $msg to report a problem of the api message to the user
     * @return bool true if at least one column definition has been added to this list
     */
    function load_column_definitions(user_message $msg): bool
    {
        return $this->load_related_by_name(
            triples::SYSTEM_COLUMN, foaf_direction::DOWN, $msg, self::COLUMN_LEVELS);
    }

    /**
     * add the phrases related to the given formula to the list
     * @param formula $frm
     * @return bool
     */
    function load_by_formula(formula $frm, user_message $msg): bool
    {
        $result = false;

        // TODO move the
        $api = new rest_call();
        $data = array();
        $data[url_var::FORMULAS] = $frm->id();
        $json_body = $api->api_get(self::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }

    /**
     * if the phrase list is empty fill it with some general suggested phrases
     * to offer to the user at least a basic selection even if the backend connection is temporary lost
     * @return bool
     */
    function load_fallback(user_message $msg): bool
    {
        $result = false;
        if ($this->is_empty()) {
            // TODO Prio 3 replace with an frequently generated preloaded list
            $this->set_lst($this->phrases_often_used($msg)->lst());
            $result = true;
        }
        return $result;
    }

    /**
     * @return phrase_list with the most often used phrases as a frontend fallback list
     */
    private function phrases_often_used(user_message $msg): phrase_list
    {
        $lst = new phrase_list();
        foreach (words::BASE_WORDS as $wrd_array) {
            $wrd = new word();
            $wrd->set_name($wrd_array[0]);
            $wrd->set_id($wrd_array[1]);
            $lst->add($wrd->phrase(), $msg);
        }
        foreach (triples::BASE_TRIPLES as $trp_array) {
            $trp = new triple();
            $trp->set_name($trp_array[0]);
            $trp->set_id($trp_array[1]);
            $lst->add($trp->phrase(), $msg);
        }
        return $lst;
    }


    /*
     * related
     */

    /**
     * get the phrase of the most relevant result
     * e.g. "happy time points" for "global problems"
     * @return phrase_list the main phrase of the most relevant result
     */
    function result_phrases_most_relevant(): phrase_list
    {
        $phr = new phrase_list();
        // TODO review temp solution
        //$phr->load_by_name();
        return $phr;
    }

    /**
     * get the names of the phrases that this list links to the given phrase by a triple
     * e.g. for "global problem" the names "global warming" and "populism", because the list
     * contains the triples "global warming (global problem)" and "populism (global problem)"
     * the verb does not matter, because e.g. "is a" and "can be" both classify the from side
     *
     * @param phrase $phr the phrase whose children should be returned
     * @return array the names of the phrases that link to the given phrase
     */
    function child_names(phrase $phr): array
    {
        $result = [];
        foreach ($this->lst() as $lst_phr) {
            if ($lst_phr->is_triple()) {
                $trp = $lst_phr->obj();
                if ($trp->get_to()?->name() == $phr->name()) {
                    $name = $trp->get_from()?->name();
                    if ($name != null and !in_array($name, $result)) {
                        $result[] = $name;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * the phrases that this list links to the given phrase by a triple
     *
     * the phrase counterpart of child_names, used where the id is needed and not only the name,
     * e.g. to load the values of the global problems from the api; unlike children() this
     * matches the "to" side and returns the linked phrases instead of the linking triples
     *
     * @param phrase $phr the phrase whose children should be returned
     * @return phrase_list the phrases that link to the given phrase
     */
    function child_phrases(phrase $phr): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $lst_phr) {
            if ($lst_phr->is_triple()) {
                $trp = $lst_phr->obj();
                if ($trp->get_to()?->name() == $phr->name()) {
                    $from = $trp->get_from();
                    if ($from != null and !$result->has_id($from->id())) {
                        $result->add_phrase($from);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * the names of the phrases that this list defines as table columns, in the order that the
     * column order triples of solution_prio.json give: the "is next main column after" triples
     * chain the main columns and the "is explaining column for" triples place a column behind
     * the main column it explains, e.g. "column loss" explains "column problem (high prio)",
     * so the loss column is right of the problem column and left of the next main column
     *
     * a column is defined by a triple "<phrase> can be <tier>", so this list must carry those
     * triples; a phrase without such a triple is not returned and the caller falls back to its
     * own ranking (see value_list::table_by_related_columns); a definition that the chain does
     * not place is appended, ordered by its tier: a prime column first (shown on every screen),
     * then a second column (hidden on a small screen), then a third column (only on a wide one)
     *
     * @return array the column phrase names, the leftmost column first
     */
    function column_names(): array
    {
        $col_by_def = $this->column_definitions();
        $result = [];
        foreach ($this->definition_order(array_keys($col_by_def)) as $def_name) {
            // a phrase defined as a column twice keeps its first position
            if (!in_array($col_by_def[$def_name], $result)) {
                $result[] = $col_by_def[$def_name];
            }
        }
        return $result;
    }

    /**
     * the column definitions of this list, keyed by the name of the defining triple
     *
     * @return array per definition triple name e.g. "column loss" the column phrase name "loss",
     *               ordered by the tier, which orders the definitions the chain does not place
     */
    private function column_definitions(): array
    {
        $result = [];
        foreach (triples::SYSTEM_COLUMN_TIERS as $tier) {
            foreach ($this->lst() as $phr) {
                if ($phr->is_triple()) {
                    $trp = $phr->obj();
                    // the tier is the "to" side, so the column phrase is the "from" side
                    if ($trp->get_to()?->name() == $tier) {
                        $name = $trp->get_from()?->name();
                        // a phrase assigned to two tiers keeps the more important one
                        if ($name != null and !in_array($name, $result)) {
                            $result[$phr->name()] = $name;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * order the given column definitions by the main column chain of this list
     *
     * each main column is directly followed by the columns that explain it, so the next main
     * column starts only once the explaining columns of the previous one are placed
     *
     * @param array $def_names the names of the column definition triples e.g. "column loss"
     * @return array the definition names, the leftmost column first
     */
    private function definition_order(array $def_names): array
    {
        $explains = $this->explaining_map();
        $mains = $this->main_column_chain();
        foreach (array_keys($explains) as $main) {
            // a main column that no chain places still keeps its explaining columns
            if (!in_array($main, $mains)) {
                $mains[] = $main;
            }
        }
        $result = [];
        foreach ($mains as $main) {
            $group = array_merge([$main], $explains[$main] ?? []);
            foreach ($group as $name) {
                // only a definition of this list can be a column, and only one column
                if (in_array($name, $def_names) and !in_array($name, $result)) {
                    $result[] = $name;
                }
            }
        }
        // a definition that the chain does not place keeps the order of the definitions
        foreach ($def_names as $name) {
            if (!in_array($name, $result)) {
                $result[] = $name;
            }
        }
        return $result;
    }

    /**
     * walk the "is next main column after" triples of this list from the leftmost main column
     *
     * @return array the names of the main column definitions, the leftmost main column first
     */
    private function main_column_chain(): array
    {
        $next = $this->main_column_map();
        $result = [];
        // a main column that follows no other main column starts a chain, so the walk begins there
        foreach (array_keys($next) as $head) {
            if (!in_array($head, $next)) {
                $name = $head;
                $steps = 0;
                // the step limit stops a circular chain, which the data should not contain
                while ($name != '' and !in_array($name, $result) and $steps <= count($next)) {
                    $result[] = $name;
                    $name = $next[$name] ?? '';
                    $steps++;
                }
            }
        }
        return $result;
    }

    /**
     * @return array per main column definition name the main column that this list places behind it
     */
    private function main_column_map(): array
    {
        $result = [];
        foreach ($this->lst() as $phr) {
            if ($phr->is_triple()) {
                $trp = $phr->obj();
                // the "to" side is the main column before, so the "from" side follows it
                if ($trp->get_verb()?->name() == verbs::BEFORE_NAME) {
                    $result[$trp->get_to()?->name()] = $trp->get_from()?->name();
                }
            }
        }
        return $result;
    }

    /**
     * @return array per main column definition name the names of the columns that explain it,
     *               in the order of the "is explaining column for" triples of this list
     */
    private function explaining_map(): array
    {
        $result = [];
        foreach ($this->lst() as $phr) {
            if ($phr->is_triple()) {
                $trp = $phr->obj();
                // the "to" side is the explained main column, so the "from" side explains it
                if ($trp->get_verb()?->name() == verbs::AFTER_NAME) {
                    $result[$trp->get_to()?->name()][] = $trp->get_from()?->name();
                }
            }
        }
        return $result;
    }

    /**
     * the phrase that this list defines as the table column of the given name
     *
     * the caller has the column name from column_names() and needs the phrase itself to head the
     * column with a link and to ask child_names() which phrases belong into that column
     *
     * @param string $name the name of a column phrase e.g. "solution"
     * @return phrase|null the column phrase or null if this list defines no column of that name
     */
    function column_phrase(string $name): ?phrase
    {
        $result = null;
        foreach ($this->lst() as $phr) {
            if ($phr->is_triple() and $result == null) {
                $trp = $phr->obj();
                // the tier is the "to" side, so the column phrase is the "from" side
                if (in_array($trp->get_to()?->name(), triples::SYSTEM_COLUMN_TIERS)) {
                    if ($trp->get_from()?->name() == $name) {
                        $result = $trp->get_from();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * build the category subtitle html for the page-title
     * based on the verbs::CATEGORY_VERBS priority list
     *
     * e.g. "CHF is symbol for <Swiss Franc>"
     * or if not a symbol e.g. "Zurich is a <city>, <canton>, <Company>")
     *
     * @param phrase $phr the starting phrase whose category subtitle is being rendered
     * @param int|null $max the maximal number of links to show
     * @return string the rendered subtitle html, or '' when no category verb matches
     */
    function category_subtitle(
        phrase $phr,
        ?int   $max = null
    ): string
    {
        global $ui_sys;

        $result = '';

        $vrb_cac = $ui_sys->typ_lst_cache->vrb ?? null;

        if ($this->is_empty()) {
            log_debug('list of related phrase is empty for ' . $phr->dsp_id());
            // an empty related list is normal (e.g. a word with no connecting triples)
        } elseif ($vrb_cac === null) {
            log_err('the verb type cache is not loaded, so the category subtitle for '
                . $phr->dsp_id() . ' cannot be built');
        } else {
            $this->sort_by_impact();
            foreach (verbs::CATEGORY_VERBS as [$vrb_code_id, $direction]) {
                // the first (highest-priority) category verb with matching entries wins
                if ($result === '') {
                    $vrb = $vrb_cac->get_by_code_id($vrb_code_id);
                    if ($vrb === null) {
                        log_err('the category verb "' . $vrb_code_id . '" is missing in the verb cache');
                    } else {
                        $links = [];
                        foreach ($this->lst() as $lnk_phr) {
                            if ($lnk_phr->is_triple()) {
                                $trp = $lnk_phr->obj();
                                $lnk = $trp->get_link_by_verb($phr, $vrb, $direction);
                                if ($lnk !== null) {
                                    if (count($links) < $max or $max === null) {
                                        $links[] = $lnk;
                                    } else {
                                        $links[] = $this->more_link($phr->id());
                                    }
                                }
                            }
                        }
                        if (count($links) > 0) {
                            $result = $vrb->name() . ' ' . implode(', ', $links);
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * subtitle for a formula page: the assigned phrases as a plain comma-separated list of
     * links (each carrying its description as a tooltip), sorted by impact for a deterministic
     * order and capped at $max with a trailing "..." when more phrases are assigned
     * @param int|null $max the max number of phrases shown before the "..." placeholder
     * @return string the html code of the assigned-phrase links
     */
    function assigned_subtitle(?int $max = null): string
    {
        $result = '';
        if (!$this->is_empty()) {
            $this->sort_by_impact();
            $links = [];
            $i = 0;
            foreach ($this->lst() as $phr) {
                if ($max === null or $i < $max) {
                    $links[] = $phr->name_link();
                }
                $i++;
            }
            if ($max !== null and $this->count() > $max) {
                $links[] = '...';
            }
            $result = implode(', ', $links);
        }
        return $result;
    }

    /**
     * placeholder link to all related object for a (too) long list
     * @param int $parent_id the id of the object who related objects should be shown
     * @return ?string the html code of the more placeholder
     */
    private function more_link(
        int $parent_id
    ): ?string
    {
        $html = new html_base();
        $url = $html->url_back(views::WORD_RELATED_ID, $parent_id);
        return $html->ref($url, '...');
    }


    /*
     * select
     */

    /**
     * get all phrases that are connected to the given phrase
     * selected by the given verb
     * @param phrase $phr the parent phrase
     * @param verb|null $vrb the verb to filter the child phrases
     * @return phrase_list the filtered children
     */
    function children(phrase $phr, user_message $msg, verb|null $vrb = null): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if ($trp->get_verb()->id() == $vrb?->id() or $vrb == null) {
                    if ($trp->get_from()->id() == $phr->id()) {
                        $result->add($trp, $msg);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get the phrase of this cache that matches the given phrase, so that a phrase which
     * carries only the id and the name (e.g. a phrase of a value group) can be enriched
     * with the vars that only the fully loaded phrase has, like the description
     *
     * @param phrase $phr the phrase to look up, e.g. a phrase of a value group
     * @return phrase the phrase of this cache or the given phrase if this cache has none
     */
    function cached_phrase(phrase $phr): phrase
    {
        $result = $phr;
        foreach ($this->lst() as $cac_phr) {
            if ($cac_phr->id() == $phr->id() and $result === $phr) {
                $result = $cac_phr;
            }
        }
        return $result;
    }

    /**
     * get the tooltip text for the given phrase based on this cache:
     * the description of the phrase itself, or - if the phrase has none - the description of
     * the phrase it is a symbol for, e.g. the description of "million" is used for "mio",
     * because a symbol is only useful if the reader knows what it stands for
     *
     * @param phrase $phr the phrase that should get a tooltip, e.g. "mio"
     * @param user_message $msg to enrich with problems and suggested solutions
     * @return string the tooltip text or '' if this cache knows no description
     */
    function tooltip(phrase $phr, user_message $msg): string
    {
        global $ui_sys;

        $result = $this->cached_phrase($phr)->get_description() ?? '';
        if ($result == '') {
            $vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id(verbs::SYMBOL);
            if ($vrb != null) {
                // the symbol is the from side, so the described phrase is the to side
                foreach ($this->lst() as $cac_phr) {
                    if ($cac_phr->is_triple() and $result == '') {
                        $trp = $cac_phr->obj();
                        if ($trp->get_verb()?->id() == $vrb->id()
                            and $trp->get_from()?->id() == $phr->id()) {
                            $result = $this->cached_phrase($trp->get_to())->get_description() ?? '';
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get all phrases that are connected to the given phrase
     * selected by the given verb
     * @param phrase $phr the parent phrase
     * @param verb|null $vrb the verb to filter the child phrases
     * @return phrase_list the filtered parents
     */
    function parents(phrase $phr, user_message $msg, verb|null $vrb = null): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if ($trp->get_verb()->id() == $vrb?->id() or $vrb == null) {
                    if ($trp->get_to()->id() == $phr->id()) {
                        $result->add($trp->get_from(), $msg);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get all triples that are connected to the given phrase
     * selected by the given verb
     * @param phrase $phr the parent phrase
     * @param verb|null $vrb the verb to filter the child phrases
     * @return phrase_list the filtered parents
     */
    function parent_triples(phrase $phr, user_message $msg, verb|null $vrb = null): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if ($trp->get_verb()->id() == $vrb?->id() or $vrb == null) {
                    if ($trp->get_to()->id() == $phr->id()) {
                        $result->add($trp, $msg);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * get the triples that point to the given phrase excluding the given verbs
     * e.g. to show the related phrases without the alias and symbol entries on the default word page
     * @param phrase $phr the target phrase
     * @param array $vrb_ids the database ids of the verbs to exclude
     * @return phrase_list the triples to the given phrase without the excluded verbs
     */
    function parent_triples_ex_verbs(phrase $phr, array $vrb_ids, user_message $msg): phrase_list
    {
        $result = new phrase_list;
        foreach ($this->lst() as $trp) {
            if ($trp->is_triple()) {
                if (!in_array($trp->get_verb()?->id(), $vrb_ids)) {
                    if ($trp->get_to()->id() == $phr->id()) {
                        $result->add($trp, $msg);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * to select a phrase from this list
     * @param string $name the unique name within the html form for this selector
     * @param string $form the name of the html form
     * @param int|null $selected the row id of the suggested phrase or the already selected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param msg_id $label_id the translation id for the text shown to the user
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    public function phrase_selector(
        string $name,
        string $form,
        ?int   $selected = null,
        string $pattern = '',
        msg_id $label_id = msg_id::FORM_SELECT_PHRASE,
        string $style = view_styles::COL_SM_4
    ): string
    {
        return $this->selector($form, $selected, $name, $label_id, $style, html_selector::TYPE_DATALIST);
    }

    /**
     * create the HTML code to select a view
     * overrides db_object::view_selector for phrase_list objects
     * since a phrase_list is not a sandbox object, the default view falls back to the bare phrase view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string       $form,
        view_list    $msk_lst,
        user_message $msg,
        string       $name = url_var::VIEW,
        msg_id       $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        $msk_lst = $msk_lst->ex_system($msg);
        return $msk_lst->selector($form, views::PHRASE_ID, $name, $msg_id);
    }

    /**
     * the html code to select a filename e.g. to upload the file
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code to select a file
     */
    public function select_file(string $form, ?data_object $cfg = null): string
    {
        $name = '';
        $lst = [];
        if ($cfg !== null && $cfg->has_file_list()) {
            $lst = $cfg->file_list();
            if ($lst !== []) {
                $name = $lst[0];
            }
        }
        return $this->file_selector($form, $name, $lst);
    }

    /**
     * get the most useful time for the given phrases
     * similar to the backend function with the same name
     * TODO: review
     * @param term_list|null $trm_lst a list of preloaded terms that should be used for the transformation
     * @return phrase|null with the most useful time phrase
     */
    function assume_time(user_message $msg, ?term_list $trm_lst = null): ?phrase
    {
        $time_phr = null;
        $wrd_lst = $this->wrd_lst_all($msg);
        $time_wrd = $wrd_lst->assume_time($msg, $trm_lst);
        if (isset($time_wrd)) {
            $time_phr = $time_wrd;
        }
        return $time_phr;
    }

    /**
     * build a word list including the triple words or in other words flatten the list e.g. for parent inclusions
     * @return word_list with all words of the phrases split into single words
     */
    function wrd_lst_all(user_message $msg): word_list
    {
        log_debug('phrase_list->wrd_lst_all for ' . $this->dsp_id());

        $wrd_lst = new word_list();

        // fill the word list
        foreach ($this->lst() as $phr) {
            if ($phr->obj() == null) {
                log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
            } else {
                if ($phr->obj()->id() == 0) {
                    log_err('Phrase ' . $phr->dsp_id() . ' could not be loaded', 'phrase_list->wrd_lst_all');
                } else {
                    if ($phr->name() == '') {
                        $phr->load($msg);
                        log_warning('Phrase ' . $phr->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                    }
                    // TODO check if old can ge removed: if ($phr->id() > 0) {
                    if (get_class($phr->obj()) == word::class) {
                        $wrd_lst->add($phr->obj(), $msg);
                    } elseif (get_class($phr->obj()) == triple::class) {
                        // use the recursive triple function to include the foaf words
                        $sub_wrd_lst = $phr->obj()->wrd_lst($msg);
                        foreach ($sub_wrd_lst->lst() as $wrd) {
                            if ($wrd->name() == '') {
                                $wrd->load_by_id($wrd->id(), $msg);
                                log_warning('Word ' . $wrd->dsp_id() . ' needs unexpected reload', 'phrase_list->wrd_lst_all');
                            }
                            $wrd_lst->add($wrd, $msg);
                        }
                    } else {
                        log_err('The phrase list ' . $this->dsp_id() . ' contains ' . $phr->obj()->dsp_id() . ', which is neither a word nor a phrase, but it is a ' . get_class($phr->obj), 'phrase_list->wrd_lst_all');
                    }
                }
            }
        }

        log_debug($wrd_lst->dsp_id());
        return $wrd_lst;
    }

    /**
     * get the words from the phrase list
     * @return word_list with the direct words of the phrase list
     */
    function word_list(user_message $msg): word_list
    {
        $wrd_lst = new word_list();

        // fill up the word list
        foreach ($this->lst() as $phr) {
            $wrd = $phr->obj();
            if ($wrd == null) {
                log_err('Object of phrase ' . $phr->dsp_id() . ' missing');
            } elseif ($wrd->id() == 0) {
                log_err('Id of phrase ' . $phr->dsp_id() . ' missing');
            } elseif ($wrd->name() == '') {
                log_warning('Name of phrase ' . $phr->dsp_id() . ' is empty');
            } elseif ($wrd::class == word::class) {
                $wrd_lst->add($wrd, $msg);
            }
        }

        return $wrd_lst;
    }

    /**
     * get the triples from the phrase list
     * @return triple_list with the direct triples of the phrase list
     */
    function triple_list(user_message $msg): triple_list
    {
        $trp_lst = new triple_list();

        // fill up the triple list
        foreach ($this->lst() as $phr) {
            $trp = $phr->obj();
            if ($trp == null) {
                log_err('Object of phrase ' . $phr->dsp_id() . ' missing');
            } elseif ($trp->id() == 0) {
                log_err('Id of phrase ' . $phr->dsp_id() . ' missing');
            } elseif ($trp->name() == '') {
                log_warning('Name of phrase ' . $phr->dsp_id() . ' is empty');
            } elseif ($trp::class == triple::class) {
                $trp_lst->add($trp, $msg);
            }
        }

        return $trp_lst;
    }


    /*
     * sort
     */

    /**
     * sort this phrase list in place so that the phrase with the highest impact is first
     * the impact is the system calculated relevance of the wrapped word or triple
     * phrases with the same (or no) impact are sorted by name and finally by id so that the
     * order is always deterministic and the html does not change between runs e.g. for the
     * snapshot tests, also when the phrase names are not loaded (only the ids are known)
     * @return void
     */
    function sort_by_impact(): void
    {
        $lst = $this->lst();
        usort($lst, function (phrase $a, phrase $b) {
            return $b->impact() <=> $a->impact()
                ?: strcmp($a->name() ?? '', $b->name() ?? '')
                    ?: $a->id() <=> $b->id();
        });
        $this->set_lst($lst);
    }

    /**
     * @return float the highest system calculated impact of the phrases in this list (0.0 if the list is empty)
     */
    function max_impact(): float
    {
        $max = 0.0;
        foreach ($this->lst() as $phr) {
            if ($phr->impact() > $max) {
                $max = $phr->impact();
            }
        }
        return $max;
    }


    /*
     * display
     */

    /**
     * create the html code to display the phrases sorted with the highest impact first
     * e.g. to show the stock with the highest market capitalisation first
     * in contrast to name_link() which sorts the phrases by name
     * @return string with a list of the phrase names with html links
     */
    function name_link_by_impact(): string
    {
        $this->sort_by_impact();
        return implode(', ', $this->names_linked());
    }

    /**
     * html for the parent triples pointing to $phr (excluding the given verbs) grouped by verb:
     * each verb group shows the verb name as a small header linked to the verb default view,
     * followed by the linked (from) phrases sorted by impact and name; the verb groups are
     * ordered by verb name so the html order is deterministic
     * e.g. for "currency" the "can have" group lists "ranked by daily turnover, ..."
     * @param phrase $phr the page phrase whose related phrases are shown
     * @param array $vrb_ids the database ids of the verbs to exclude (e.g. symbol, alias, is a)
     * @return string the html code of the grouped related phrases
     */
    function name_link_grouped_by_verb(phrase $phr, array $vrb_ids, user_message $msg): string
    {
        $html = new html_base();
        $result = '';

        // collect the linked (from) phrases per verb of the parent triples
        $grp_lst = [];
        foreach ($this->parent_triples_ex_verbs($phr, $vrb_ids, $msg)->lst() as $trp) {
            $vrb = $trp->get_verb();
            $from = $trp->get_from();
            if ($vrb != null and $from != null) {
                $vrb_id = $vrb->id();
                if (!array_key_exists($vrb_id, $grp_lst)) {
                    $grp_lst[$vrb_id] = ['verb' => $vrb, 'phrases' => new phrase_list()];
                }
                $grp_lst[$vrb_id]['phrases']->add($from, $msg);
            }
        }

        // order the verb groups by verb name for a deterministic html order
        usort($grp_lst, fn(array $a, array $b) => strcmp($a['verb']->name(), $b['verb']->name()));

        // render each verb group as a header linked to the verb page (the header is a block
        // element, so it starts a new line) followed by the impact-and-name sorted phrases
        foreach ($grp_lst as $grp) {
            $result .= $html->dsp_text_h3($grp['verb']->name_link());
            $result .= $grp['phrases']->name_link_by_impact();
        }
        return $result;
    }

    function name_link_list(?phrase_list $phr_lst_header = null): string
    {
        $result = '';
        $this->sort_by_impact();
        if ($phr_lst_header != null) {
            if (!$phr_lst_header->is_empty()) {
                $this->remove($phr_lst_header);
            }
        }
        foreach ($this->lst() as $phr) {
            if ($result <> '') {
                $result .= ', ';
            }
            $result .= $phr->name_link();
        }
        return $result;
    }

    /**
     * the plural of each phrase is its own user data, so the list asks every phrase instead of
     * adding an "s" to the list text, which would pluralise only the last phrase of the list
     *
     * @param string $lan the code of the user interface language e.g. "en"
     * @returns string the html code to display the plural of the phrases with the most useful link
     */
    function plural(string $lan = languages::DEFAULT): string
    {
        $result = '';
        $this->sort_by_impact();
        foreach ($this->lst() as $phr) {
            if ($result <> '') {
                $result .= ', ';
            }
            $result .= $phr->name_link_plural($lan);
        }
        return $result;
    }

    /**
     * @returns string the html code to display the phrases for a sentence start
     * TODO replace adding the s with a language specific functions that can include exceptions
     */
    private function InitCap(): string
    {
        return strtoupper(substr($this->plural(), 0, 1)) . substr($this->plural(), 1);
    }

    /**
     * @returns string the html code to display the phrases as a headline
     */
    function headline(): string
    {
        $html = new html_base();
        return $html->text_h2($this->InitCap());
    }

    /**
     * the old long form to encode
     */
    function id_url_long(): string
    {
        $lib = new library();
        return $lib->ids_to_url($this->id_lst(), "phrase");
    }


    /*
     * filter
     */

    /**
     * @return phrase_list list of the measure / unit phrases e.g. m/s
     */
    function measure_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if ($phr->is_measure($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list without the measure / unit phrases e.g. speed of light
     */
    function ex_measure_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if (!$phr->is_measure($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list of the scaling phrases e.g. billion
     */
    function scaling_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if ($phr->is_scaling($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list without the scaling phrases e.g. without billion
     */
    function ex_scaling_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if (!$phr->is_scaling($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list without the time phrases e.g. second
     */
    function ex_time(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if (!$phr->is_time($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list of information only phrases
     */
    function info_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if ($phr->is_info($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }

    /**
     * @return phrase_list list of phrases without the info phrases e.g. without 1967 (year of definition)
     */
    function ex_info_list(user_message $msg): phrase_list
    {
        $result = new phrase_list();
        foreach ($this->lst() as $phr) {
            if (!$phr->is_info($msg)) {
                $result->add($phr, $msg);
            }
        }
        return $result;
    }


    /*
     * info
     */

    /**
     * @return bool true if one of the phrases is of type percent
     */
    function has_percent(user_message $msg): bool
    {
        $result = false;
        foreach ($this->lst() as $phr) {
            if ($phr->is_percent($msg)) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * removes all terms from this list that are not in the given list
     * @param phrase_list $new_lst the terms that should remain in this list
     * @returns phrase_list with the phrases of this list and the new list
     */
    function intersect(phrase_list $new_lst): phrase_list
    {
        if (!$new_lst->is_empty()) {
            if ($this->is_empty()) {
                $this->set_lst($new_lst->lst());
            } else {
                // next line would work if array_intersect could handle objects
                // $this->lst = array_intersect($this->lst, $new_lst->lst());
                $found_lst = new phrase_list();
                foreach ($new_lst->lst() as $phr) {
                    $id = $phr->id();
                    $id_lst = $this->id_lst();
                    if (in_array($id, $id_lst)) {
                        $found_lst->add_phrase($phr);
                    }
                }
                $this->set_lst($found_lst->lst());
            }
        }
        return $this;
    }

    /**
     * @returns phrase_list with the phrases that are used in all values of the list
     */
    protected function common_phrases(): phrase_list
    {
        // get common words
        $common_phr_lst = new phrase_list();
        foreach ($this->lst() as $val) {
            if ($val != null) {
                if ($val->phr_lst() != null) {
                    if ($val->phr_lst()->lst != null) {
                        $common_phr_lst->intersect($val->phr_lst());
                    }
                }
            }
        }
        return $common_phr_lst;
    }

    /**
     * @return phrase|null the dominate phrase of the list
     * used to guess which related phrase a human user might use next
     * if no phrase is dominant, the phrase is selected by the parent phrase
     */
    function mainly(): ?phrase
    {
        $phr = null;
        if ($this->count() > 1) {
            // TODO get from frontend config
            // $is_dominant_pct = $ui_sys->cfg->get(config::MIN_PCT_OF_PHRASES_TO_PRESELECT, $db_con);
            $is_dominant_pct = 0.3;
            $count_lst = array_count_values($this->id_lst());
            sort($count_lst);
            if (($count_lst[0] / $this->count()) > $is_dominant_pct) {
                $id = $count_lst[0];
                $phr = $this->get($id);
            }
        }
        return $phr;
    }

    /**
     * add a phrase to the list
     * @returns bool true if the phrase has been added
     */
    function add_phrase(phrase $phr): bool
    {
        return parent::add_obj($phr);
    }

    /**
     * remove all phrases of the given list from this list
     * @param phrase_list $del_lst of phrases that should be deleted
     * @return phrase_list with the remaining phrases
     */
    function remove(phrase_list $del_lst): phrase_list
    {
        if (!$del_lst->is_empty()) {
            // next line would work if array_intersect could handle objects
            // $this->lst = array_intersect($this->lst, $new_lst->lst());
            $remain_lst = new phrase_list();
            foreach ($this->lst() as $phr) {
                if (!in_array($phr->id(), $del_lst->id_lst())) {
                    $remain_lst->add_phrase($phr);
                }
            }
            $this->set_lst($remain_lst->lst());
        }
        return $this;
    }

    /**
     * @return string one string with all names of the list and reduced in size mainly for debugging
     * this function is called from dsp_id, so no other call is allowed
     */
    function dsp_name(): string
    {
        global $ui_sys;
        $debug = $ui_sys->debug;
        $lib = new library();

        $name_lst = $this->names();
        if ($debug > 10) {
            $result = '"' . implode('","', $name_lst) . '"';
        } else {
            $result = '"' . implode('","', array_slice($name_lst, 0, 7));
            if (count($name_lst) > 8) {
                $result .= ' ... total ' . $lib->dsp_count($this->lst());
            }
            $result .= '"';
        }

        return $result;
    }

    /**
     * @return array all phrases that are part of given list and this list
     */
    function common(array $filter_lst): array
    {
        $result = array();
        $lib = new library();
        if (count($this->lst()) > 0) {
            foreach ($this->lst() as $phr) {
                if (isset($phr)) {
                    if (in_array($phr, $filter_lst)) {
                        $result[] = $phr;
                    }
                }
            }
            $this->set_lst($result);
            $this->id_lst();
        }
        log_debug($lib->dsp_count($this->lst()));
        return $result;
    }

    /*
     * repeat backend
     */

    /**
     * TODO Prio 1 review
     * @return group|null the group with only the id set based to this list or null if no group matches
     */
    function get_grp_id(bool $do_save = true): ?group
    {
        $grp = null;
        if ($this->is_empty()) {
            // TODO Prio 0 switch back to error
            log_warning('Cannot create phrase group for an empty list.', 'phrase_list->get_grp');
        } else {
            $grp = new group();
            /*
            $grp_id = new group_id();
            $grp->set_id($grp_id->get_id($this));
            $grp->set_phrase_list(clone $this);
            */
        }
        return $grp;
    }

    /*
     * to review
     */

    /**
     * TODO review
     * offer the user to add a new value for these phrases
     * similar to value.php/btn_add
     */
    function btn_add_value($back): string
    {
        $result = \Zukunft\ZukunftCom\main\php\web\html\btn_add_value($this, Null, $back);
        /*
        zu_debug('phrase_list->btn_add_value');
        $val_btn_title = '';
        $url_phr = '';
        if (!empty($this->lst)) {
          $val_btn_title = "add new value similar to ".htmlentities($this->name());
        } else {
          $val_btn_title = "add new value";
        }
        $url_phr = $this->id_url_long();

        $val_btn_call  = rest_ctrl::PATH_FIXED .'value_add.php?back='.$back.$url_phr;
        $result .= \html\btn_add ($val_btn_title, $val_btn_call);
        zu_debug('phrase_list->btn_add_value -> done');
        */
        return $result;
    }

    /**
     * TODO review
     * shows all phrases that are part of a list
     * e.g. used to display all phrases linked to a word
     * @returns string the html code to edit a linked word
     */
    function dsp_graph(phrase $root_phr, user_message $msg, string $back = ''): string
    {
        log_debug();
        $result = '';

        // loop over the link types
        if ($this->lst() == null) {
            $result .= 'Nothing linked to ' . $root_phr->name() . ' until now. Click here to link it.';
        } else {
            $phr_lst = new phrase_list();
            $phr_lst->set_from_json($this->api_json([], $msg));
            $wrd_lst = $phr_lst->wrd_lst_all($msg);
            $result .= $wrd_lst->tbl($back);
            foreach ($this->lst() as $phr) {
                // show the RDF graph for this verb
                $phr->name();
            }
        }

        return $result;
    }

}
