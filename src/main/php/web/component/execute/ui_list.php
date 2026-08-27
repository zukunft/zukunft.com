<?php

/*

    web/component/execute/ui_list.php - create the html for listed related to an object
    ---------------------------------

    function to create the pure HTML frontend code to display lists of objects related to a given object

    The main sections of this object are
    - object vars:       the variables of this word object


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

namespace Zukunft\ZukunftCom\main\php\web\component\execute;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::EXECUTE . 'ui_log.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::TYPES . 'type_object.php';
//include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::VIEW . 'view.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'foaf_direction.php';

//include_once test_paths::CONST . 'triple_names.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;

class ui_list extends ui_base
{

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "canton of Zurich"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function parents_of_word(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::UP, $msg, $this->related_list($wrd, $phr_lst));
    }

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "canton of Zurich"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function children_of_word(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        global $ui_sys;
        $result = '';
        $phr_cac = $this->related_list($wrd, $phr_lst);
        $is_vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id(verbs::IS);
        if ($phr_cac != null and $is_vrb != null) {
            $phr = $wrd->phrase();
            // the children of a word are its subclasses, i.e. the phrases that "are a" this word
            $children = $phr_cac->parents($phr, $msg, $is_vrb);
            if (!$children->is_empty()) {
                $html = new html_base();
                if ($children->count() == 1) {
                    // a single child reads as the full statement, e.g. "Euro is a currency";
                    // name_link() is already safe html, the verb and phrase names are user input
                    // rendered raw by dsp_text_h2 below, so escape them (stored xss via the name)
                    $header = $children->name_link() . ' ' . $html->esc($is_vrb->name()) . ' ' . $html->esc($phr->name());
                } else {
                    // several children get a header of the word plural and the verb plural,
                    // e.g. "currencies are", followed by the list of the child phrases
                    $plural = $wrd->get_plural();
                    if ($plural == null or $plural == '') {
                        $plural = $phr->name();
                    }
                    $header = $html->esc($plural) . ' ' . $html->esc($is_vrb->plural_reverse());
                }
                // start with a line break and the header as an h4 subtitle, then (for several
                // children) the linked child phrases
                $result = $html->br() . $html->dsp_text_h2($header);
                if ($children->count() > 1) {
                    $result .= $children->name_link();
                }
            }
        }
        return $result;
    }

    /**
     * prefer the related phrases loaded together with the word or triple (api_types::INCL_RELATED)
     * over the general phrase cache so that the page shows the phrases related to this object
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases given by the caller
     * @return phrase_list|null the related phrases of the object or the given cache list
     */
    private function related_list(word|phrase|db_object $wrd, ?phrase_list $phr_lst): ?phrase_list
    {
        if ($wrd::class == word::class or $wrd::class == triple::class) {
            if ($wrd->phr_lst != null) {
                $phr_lst = $wrd->phr_lst;
            }
        }
        return $phr_lst;
    }

    /**
     * HTML for the phrases that are an alias of the given phrase
     * e.g. for "US dollar" the line 'has aliases: $, U.S. dollar'
     * where "$" links to the word page and "aliases" to the verb page
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the alias line or an empty string if there is no alias
     */
    function phrase_aliases(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_by_verb($wrd, verbs::ALIAS, msg_id::PHRASE_ALIAS, msg_id::PHRASE_ALIASES, $phr_lst, $msg);
    }

    /**
     * HTML for the symbols of the given phrase
     * e.g. for "US dollar" the line 'has symbol: USD'
     * where "USD" links to the word page and "symbol" to the verb page
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the symbol line or an empty string if there is no symbol
     */
    function phrase_symbols(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_by_verb($wrd, verbs::SYMBOL, msg_id::PHRASE_SYMBOL, msg_id::PHRASE_SYMBOLS, $phr_lst, $msg);
    }

    /**
     * HTML for the phrases related to the given phrase excluding the alias and symbol entries
     * because these are already shown by the phrase_aliases and phrase_symbols components
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the remaining related phrases
     */
    function phrases_related_ex_symbols(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_related_ex_verbs($wrd, $phr_lst, [verbs::SYMBOL, verbs::ALIAS], $msg);
    }

    /**
     * HTML for the phrases related to the given phrase excluding the alias, symbol and "is a"
     * entries, because the alias and symbol have their own components and the "is a" parents
     * are already shown in the page subtitle (e.g. on the default word page)
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the remaining related phrases
     */
    function phrases_related_ex_subtitle(word|db_object $wrd, user_message $msg, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_related_ex_verbs($wrd, $phr_lst, [verbs::SYMBOL, verbs::ALIAS, verbs::IS], $msg, true);
    }

    /**
     * HTML for the phrases related to the given phrase excluding the triples linked by the
     * verbs in $ex_vrb_lst (an empty list shows all related phrases)
     * sorted with the highest impact first e.g. for stocks the highest market capitalisation
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @param array $ex_vrb_lst the code ids of the verbs whose triples should not be shown
     * @param bool $grouped true to group the phrases by verb (each verb a linked header) as on
     *                      the default word/triple page; false for a flat impact-sorted list
     * @return string the html code with the remaining related phrases
     */
    private function phrases_related_ex_verbs(
        word|phrase|db_object $wrd,
        ?phrase_list          $phr_lst,
        array                 $ex_vrb_lst,
        user_message          $msg,
        bool                  $grouped = false
    ): string
    {
        global $ui_sys;

        // the object can be a phrase directly (e.g. the related-phrases component) or a
        // word/triple that carries one
        if ($wrd::class == phrase::class) {
            $phr = $wrd;
        } else {
            $phr = $wrd->phrase();
        }
        $result = '';
        $phr_cac = $this->related_list($wrd, $phr_lst);
        $vrb_cac = $ui_sys?->typ_lst_cache?->vrb;
        if ($phr_cac != null and $vrb_cac != null) {
            $vrb_ids = [];
            foreach ($ex_vrb_lst as $vrb_code_id) {
                $vrb_ids[] = $vrb_cac->id($vrb_code_id);
            }
            if ($grouped) {
                $result = $phr_cac->name_link_grouped_by_verb($phr, $vrb_ids, $msg);
            } else {
                $result = $phr_cac->parent_triples_ex_verbs($phr, $vrb_ids, $msg)->name_link_by_impact();
            }
        }
        return $result;
    }

    /**
     * HTML for the phrases linked to the given phrase by the given verb
     * e.g. for "US dollar" and the alias verb the line 'has aliases: $, U.S. dollar'
     * where "$" links to the word page and "aliases" to the verb page
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param string $vrb_code_id the code id of the verb to select the related phrases
     * @param msg_id $msg_one the text for the verb link if there is one related phrase
     * @param msg_id $msg_many the text for the verb link if there are several related phrases
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the related phrase line or an empty string if there is none
     */
    private function phrases_by_verb(
        word|db_object $wrd,
        string         $vrb_code_id,
        msg_id         $msg_one,
        msg_id         $msg_many,
        ?phrase_list   $phr_lst,
        user_message   $msg
    ): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $result = '';
        $vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id($vrb_code_id);
        $phr_cac = $this->related_list($wrd, $phr_lst);
        if ($vrb != null and $phr_cac != null) {
            $lst = $phr_cac->parents($wrd->phrase(), $msg, $vrb);
            if (!$lst->is_empty()) {
                $msg = $msg_one;
                if ($lst->count() > 1) {
                    $msg = $msg_many;
                }
                $vrb_lnk = $html->ref($html->url_back(views::VERB_ID, $vrb->id()), $mtr->txt($msg));
                $result = $html->span(
                    $mtr->txt(msg_id::PHRASE_HAS) . ' ' . $vrb_lnk . ': ' . $lst->name_link(),
                    styles::TEXT_NOWRAP
                );
            }
        }
        return $result;
    }

    /**
     * HTML for the list of words and triples assigned to the given formula in order of impact
     * @param formula|db_object $frm the formula whose assigned phrases should be shown
     * @param data_object|null $cac the cached formula links and phrases for a display without a backend call
     * @param bool $test_mode true to skip the api load and use only the passed cache for a reproducible result
     * @return string the html code with the linked names of the assigned phrases
     */
    function phrases_of_formula(formula|db_object $frm, user_message $msg, ?data_object $cac = null, bool $test_mode = false): string
    {
        global $ui_sys;

        $page = new system_page();
        $result = $page->system_sub_tile(msg_id::FORM_SUB_TITLE_ASSIGNED_PHRASES);

        // a formula loaded for its page carries its assigned phrases directly (like a word's
        // related formulas), so use that list; otherwise fall back to the formula link cache or,
        // outside the unit tests, an api load - never the full phrase list, which is not the
        // assignment of this formula
        if ($frm::class == formula::class and $frm->phr_lst != null) {
            $phr_lst = $frm->phr_lst;
        } else {
            $lnk_lst = $cac?->frm_lnk_lst;
            $phr_lst = new phrase_list();
            // the default cache is an empty list, so an empty cache triggers the backend call
            if ($lnk_lst != null and !$lnk_lst->is_empty()) {
                $phr_lst = $lnk_lst->get_phrase_list($cac->phr_lst, $msg);
            } elseif (!$test_mode) {
                // TODO Prio 2 decide if and when a reloading via api is done
                $lnk_lst = new formula_link_list();
                $lnk_lst->load_by_formula_id($frm->id(), $msg);
                $phr_lst = $lnk_lst->get_phrase_list($cac?->phr_lst ?? new phrase_list(), $msg);
            }
        }
        // the number of phrases shown at once comes from the frontend config
        // (config.yaml "user > frontend > lists > limit > phrase list")
        if ($ui_sys?->cfg !== null) {
            $row_limit = $ui_sys->cfg->get_by(
                [triples::PHRASE_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_NAME_LIST);
        } else {
            $row_limit = config::LIMIT_NAME_LIST;
        }
        $result .= $phr_lst->name_link('', $row_limit);
        return $result;
    }

    /**
     * the triples that use the given verb as a blank separated list of the triple names with a
     * link to each triple, used by the verb default page and the verb edit and delete pages
     *
     * TODO move to a component exe part class
     *
     * @param db_object|null $dbo the verb whose triples should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @param data_object|null $cfg the request cache with the preloaded triples
     * @return string the linked triple names or the message that the verb is not used for triples
     */
    function triple_list(?db_object $dbo = null, user_message $msg, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the verb is missing to select the triples of a verb', $msg);
        } elseif ($dbo::class == verb::class) {
            // a verb loaded for its page carries all its triples (see verb::load_by_id_with_related),
            // whereas the page cache holds only the triples of the shown phrases, so it would list
            // the triples of the verb that happen to be on the page and not all of them
            $trp_lst = $dbo->trp_lst;
            if ($trp_lst == null and $cfg?->trp_lst != null) {
                $trp_lst = clone $cfg->trp_lst;
                $trp_lst = $trp_lst->get_by_verb($dbo, $msg);
            }
            // without the preloaded triples the list cannot be created, and showing the not-used
            // message would tell the user that the verb has no triples, which is not known here
            if ($trp_lst == null) {
                log_err_msg_ui('the triple cache is missing to select the triples of a verb', $msg);
            } else {
                $result = $trp_lst->display($msg, '', $this->configured_link_limit($msg));
                if ($result == '') {
                    $result = $mtr->txt(msg_id::NOT_USED_FOR_TRIPLES);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for triples', $msg);
        }
        return $result;
    }

    /**
     * the number of links shown at once, e.g. the triples that use a verb on the verb page
     * (config.yaml "user > frontend > lists > limit > link list")
     *
     * @param user_message $msg to report a problem of reading the config
     * @return int the maximum number of links to show
     */
    private function configured_link_limit(user_message $msg): int
    {
        global $ui_sys;

        $result = config::LIMIT_LINK_LIST;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by(
                [triples::LINK_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_LINK_LIST);
        }
        return $result;
    }

    /**
     * the components of the given view as a comma separated list of the component names with a
     * link to each component, sorted by the position in the view, used by the view default page
     *
     * @param db_object|null $dbo the view whose components should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @return string the linked component names or the message that the view has no components
     */
    function view_components(?db_object $dbo, user_message $msg): string
    {
        global $mtr;
        global $ui_sys;

        $result = '';
        // without the view cache the list cannot be created, and showing the no-components
        // message would tell the user that the view is empty, which is not known here
        if ($dbo == null or $ui_sys?->typ_lst_cache == null) {
            log_err_msg_ui('the view or the view cache is missing to list the components of a view', $msg);
        } elseif ($dbo::class == view::class) {
            // the shown view object of the url carries no components, so they are taken from
            // the request cache that also provides the views for the page rendering itself
            $msk = $ui_sys->typ_lst_cache->get_view_by_id($dbo->id());
            $cmp_lst = $msk?->get_component_list();
            if ($cmp_lst == null or $cmp_lst->is_empty()) {
                $result = $mtr->txt(msg_id::INFO_VIEW_HAS_NO_COMPONENTS);
            } else {
                $result = $cmp_lst->name_link('', $this->configured_name_list_limit($msg));
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for components', $msg);
        }
        return $result;
    }

    /**
     * the views that use the given component as a comma separated list of the view names with
     * a link to each view, used by the component default page
     *
     * @param db_object|null $dbo the component whose views should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @return string the linked view names or the message that the component is not used in views
     */
    function component_views(?db_object $dbo, user_message $msg): string
    {
        global $mtr;
        global $ui_sys;

        $result = '';
        // without the view cache the list cannot be created, and showing the not-used
        // message would tell the user that the component is unused, which is not known here
        if ($dbo == null or $ui_sys?->typ_lst_cache?->msk_sys == null) {
            log_err_msg_ui('the component or the view cache is missing to list the views of a component', $msg);
        } elseif ($dbo instanceof component) {
            // instanceof, because the component page passes a component_exe child object
            // collect the views of the request cache that use the component, keyed and sorted
            // by name so the html order never depends on the cache order (see docs/llm/frontend.md)
            $names = [];
            foreach ($ui_sys->typ_lst_cache->msk_sys->lst() as $msk) {
                foreach ($msk->get_component_list()->lst() as $cmp) {
                    if ($cmp->id() == $dbo->id()) {
                        $names[$msk->name()] = $msk->name_link('', '', views::VIEW_DEFAULT_ID);
                    }
                }
            }
            if ($names == []) {
                $result = $mtr->txt(msg_id::INFO_NOT_USED_IN_VIEWS);
            } else {
                ksort($names, SORT_NATURAL);
                $row_limit = $this->configured_name_list_limit($msg);
                $result = implode(', ', array_slice(array_values($names), 0, $row_limit));
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for views', $msg);
        }
        return $result;
    }

    /**
     * the number of names shown at once in a general name list, from the frontend config
     * (config.yaml "user > frontend > lists > limit > name list"); the shared limit of
     * view_components and component_views
     * @param user_message $msg to report a config read problem
     * @return int the maximum number of names to show
     */
    private function configured_name_list_limit(user_message $msg): int
    {
        global $ui_sys;
        $result = config::LIMIT_NAME_LIST;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by(
                [triples::NAME_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_NAME_LIST);
        }
        return $result;
    }

    /**
     * get a list of formulas related to e.g. a verb
     * @param db_object $dbo e.g. a verb to select only the formulas where the object is used
     * @param user_message $msg to report an unexpected selection object
     * @param data_object|null $cfg the cache values used for a backend independent preselection of the formulas
     * @return string the most relevant formulas related to e.g. a verb
     */
    function formula_list(db_object $dbo, user_message $msg, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        if ($dbo::class == verb::class) {
            // without the preloaded formulas the list cannot be created, and showing the not-used
            // message would tell the user that the verb has no formulas, which is not known here
            if ($cfg?->frm_lst == null) {
                log_err_msg_ui('the formula cache is missing to select the formulas of a verb', $msg);
            } else {
                $frm_lst = clone $cfg->frm_lst;
                $frm_lst = $frm_lst->get_by_verb($dbo, $msg);
                $result = $frm_lst->name_link();
                if ($result == '') {
                    $result = $mtr->txt(msg_id::NOT_USED_FOR_VERB);
                }
            }
        } elseif ($dbo::class == word::class or $dbo::class == triple::class) {
            // the word/triple carries its own related formulas from the INCL_RELATED api message
            if ($dbo->frm_lst != null) {
                $result = $dbo->frm_lst->name_link();
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for formulas', $msg);
        }
        return $result;
    }

    /**
     * the formulas assigned to the ancestor phrases of a word, grouped per ancestor and shown as a
     * small 'assigned to <ancestor>' subheading (the ancestor name links to its word page and shows a
     * tooltip) followed by the ancestor's formulas; empty if the word has no ancestor formulas. the
     * groups come from the word's parent_formulas, filled from the INCL_RELATED api message
     *
     * @param db_object $dbo the word whose ancestor formulas are shown
     * @return string the html code of the ancestor formula groups, e.g. below the direct formulas
     */
    function formulas_of_parents(db_object $dbo): string
    {
        global $mtr;
        $result = '';
        if ($dbo::class == word::class and $dbo->parent_formulas != null) {
            $html = new html_base();
            foreach ($dbo->parent_formulas as $grp) {
                $frm = $grp['formulas'];
                if ($frm != null and !$frm->is_empty()) {
                    // the ancestor name_link already carries the description as the title (tooltip)
                    $head = $mtr->txt(msg_id::ASSIGNED_TO) . ' ' . $grp['phrase']->name_link();
                    $result .= $html->text_h3($head);
                    $result .= $frm->name_link();
                }
            }
        }
        return $result;
    }

    /**
     * TODO Prio 1 review at least the verb part
     * @param phrase $phr
     * @param foaf_direction $dir
     * @param phrase_list|null $phr_lst
     * @return string
     */
    private function phrases(
        phrase         $phr,
        foaf_direction $dir,
        user_message   $msg,
        ?phrase_list   $phr_cac = null
    ): string
    {
        if ($phr_cac == null) {
            $phr_lst = new phrase_list();
            $phr_lst->load_related($phr, $dir);
        } else {
            //$vrb = new verb();
            //$vrb->id = verbs::IS_ID;
            if ($dir == foaf_direction::UP) {
                $phr_lst = $phr_cac->parent_triples($phr, $msg);
            } elseif ($dir == foaf_direction::DOWN) {
                $phr_lst = $phr_cac->children($phr, $msg);
            } else {
                $phr_lst = $phr_cac;
            }
        }
        return $phr_lst->name_link();
    }

    /**
     * show a list of references related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param db_object $dbo the word or triple shown to the user and used to select the related references
     * @param data_object|null $dto the context used to create the view
     * @return string with the html code of the external references
     */
    function ref_list_word(db_object $dbo, user_message $msg, ?data_object $dto): string
    {
        $result = '';
        $phr = null;
        if ($dbo::class == word::class) {
            $phr = $dbo->phrase();
        }
        if ($dbo::class == triple::class) {
            $phr = $dbo->phrase();
        }
        if ($phr != null) {
            // a word or triple loaded for its page carries its references directly (like the
            // related values and formulas); otherwise fall back to the page reference cache
            if (($dbo::class == word::class or $dbo::class == triple::class) and $dbo->ref_lst != null) {
                $ref_lst = $dbo->ref_lst;
            } else {
                $ref_lst = $dto->ref_list_cloned()->get_by_phrase($phr, $msg);
            }
            $phr_lst = new phrase_list();
            $phr_lst->add_phrase($dbo->phrase());
            $result = $ref_lst->list($msg, $phr_lst);
        }
        // wrap the reference list in a block div so each reference name and its refresh icon
        // stay on one line; without it the bare inline elements land directly in the
        // flex-column main container and each is pushed onto its own line (same as the value list)
        if ($result != '') {
            $html = new html_base();
            $result = $html->div($result, view_styles::COL_SM_12);
        }
        return $result;
    }

    /**
     * HTML for the views related to the given word: its own default view plus the default
     * views of its parent words; a word loaded for its page carries the list directly in
     * view_lst (filled from the INCL_RELATED api message)
     *
     * @param db_object $dbo the word that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @return string the html code with the linked names of the related views
     */
    function views_related(db_object $dbo, ?data_object $cfg = null): string
    {
        $result = '';
        if ($dbo::class == word::class and $dbo->view_lst != null) {
            // name_link() renders the views in a deterministic, name-sorted order
            $result = $dbo->view_lst->name_link();
        }
        return $result;
    }

    /**
     * HTML for the col-4 tab box of a sandbox object page: a "Views" tab with the related views
     * (each a preview placeholder plus the open and switch buttons), a "Changes" tab with the
     * change log of the object, latest first, a "My" tab with the session user's own overwrites
     * (the user_ table rows e.g. of user_words), which is only shown if the user is logged in
     * and has created overwrites of this object, and an "Others" tab with the shared overwrites
     * that other users have done on this object; an empty tab is dropped, so a link page
     * without related views shows the same box without the "Views" tab
     * TODO Prio 3 replace the view preview placeholder with a real miniature preview
     *
     * @param db_object $dbo the sandbox object that should be shown to the user
     * @param user_message $msg
     * @param bool $test_mode true to create a reproducible result without a backend call
     * @param array $url_array the parsed url of the current page, carried into the my tab undo links
     * @return string the html code of the tab box or an empty string for an unsupported object
     */
    function view_tab_box(db_object $dbo, user_message $msg, bool $test_mode = false, array $url_array = []): string
    {
        global $mtr;
        $result = '';
        // guarded by class so that a mis-assigned seed component cannot fatal
        if ($dbo instanceof sandbox) {
            $html = new html_base();
            // tab 1: each related view as a preview placeholder with the open and switch buttons
            $views_html = $this->view_previews($dbo);
            // tab 2: the change log of the word as the invisible (borderless, standard grey) table
            // with the three columns when, who and what, latest first (see ui_log)
            $log = new ui_log();
            $log_html = $log->change_log_table_pure($dbo, new change_log_list(), $msg, $test_mode);
            // tab 3: the session user's own overwrites of this object (the current user_ table
            // row values compared to the standard values); an empty string if the user is not
            // logged in or has no overwrites, which drops the tab (tab_box skips empty tabs)
            $preview = new ui_preview();
            $my_html = $preview->user_overwrites_table($dbo, $msg, $url_array);
            // tab 4: the shared overwrites that other users have done on this object
            $others_html = $preview->other_overwrites_table($dbo, $msg, $url_array);
            $result = $html->tab_box([
                $mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS) => $views_html,
                $mtr->txt(msg_id::FORM_SUB_TITLE_LOG) => $log_html,
                $mtr->txt(msg_id::FORM_SUB_TITLE_MY) => $my_html,
                $mtr->txt(msg_id::FORM_SUB_TITLE_OTHERS) => $others_html,
            ]);
        } else {
            log_err($dbo::class . ' is not expected to have a change log and user overwrites');
        }
        return $result;
    }

    /**
     * the "Views" tab of the view tab box; the list stays empty for an object whose api message
     * carries no related views, which drops the tab
     * @param sandbox $dbo the sandbox object that should be shown to the user
     * @return string the html code of the view previews or an empty string if there is none
     */
    private function view_previews(sandbox $dbo): string
    {
        $result = '';
        $html = new html_base();
        foreach ($dbo->view_lst?->lst() ?? [] as $msk) {
            $preview = $html->div('view preview', view_styles::COL_SM_12);
            // the switch button opens the edit view of the shown object, which differs
            // per class, so the edit view id of the object is passed to the link builder
            $buttons = $msk->open_link($dbo->id())
                . ' ' . $msk->switch_link($dbo->id(), $dbo::VIEW_EDIT_ID);
            // escape the view name (div emits its body raw and the name is user input); the
            // preview and buttons around it are already-built html (stored xss via view name)
            $result .= $html->div($preview . $html->esc($msk->name()) . ' ' . $buttons);
        }
        return $result;
    }

    /**
     * @param db_object $dbo the word, triple or formula object that should be shown to the user
     * @param data_object|null $cfg the context used to create the view
     * @return string with the html code of links that can be changes
     */
    function link_list_word(db_object $dbo, ?data_object $cfg): string
    {
        // TODO review
        // escape the object name (user input rendered raw by the component arm; stored xss)
        $html = new html_base();
        return 'list of phrases related to ' . $html->esc($dbo->name()) . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return 'num_list component';
    }

    /**
     * HTML for the formulas assigned to the given word, triple or phrase
     * e.g. for the word "minute" the formula "scale minute to sec"
     *
     * @param word|phrase|db_object $wrd the object shown to the user e.g. the word "minute"
     * @param data_object|null $cac the cached lists for initial display without backend call
     * @param bool $test_mode true to create a reproducible result without a backend call
     * @return string the html code with the linked names of the assigned formulas
     */
    function formulas(word|phrase|db_object $wrd, user_message $msg, ?data_object $cac = null, bool $test_mode = false): string
    {
        global $ui_sys;

        // TODO Prio 3 on the word page this formula column should also show the most relevant
        //      results and, on top, a result chart (mirroring value_chart() for the value
        //      column); a result_chart component plus a word-carried results_related list are
        //      still missing

        // a word loaded for its page carries its related formulas directly (like the
        // related values), so use that list; otherwise fall back to the formula link
        // cache or, outside the unit tests, an api load
        if ($wrd::class == word::class and $wrd->frm_lst != null) {
            $frm_lst = $wrd->frm_lst;
        } else {
            if ($wrd::class == phrase::class) {
                $phr = $wrd;
            } else {
                $phr = $wrd->phrase();
            }
            $lnk_lst = $cac?->frm_lnk_lst;
            $frm_lst = new formula_list();
            // the default cache is an empty list, so an empty cache triggers the backend call
            if ($lnk_lst != null and !$lnk_lst->is_empty()) {
                $frm_lst = $lnk_lst->get_formula_list($phr, $msg, $cac->frm_lst);
            } elseif (!$test_mode) {
                // TODO Prio 2 decide if and when a reloading via api is done
                $frm_lst->load_by_phr_id($phr->id(), $msg);
            }
        }
        // the number of formulas shown at once comes from the frontend config
        // (config.yaml "user > frontend > lists > limit > formula list")
        if ($ui_sys?->cfg !== null) {
            $row_limit = $ui_sys->cfg->get_by(
                [triples::FORMULA_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                $msg, config::LIMIT_NAME_LIST);
        } else {
            $row_limit = config::LIMIT_NAME_LIST;
        }
        return $frm_lst->name_link('', $row_limit);
    }

    /**
     * HTML for a chart of the most relevant values of the given word, shown on top of the
     * value list; only rendered if the word actually has a related value
     * TODO Prio 3 replace the placeholder with a real chart of the most relevant values by
     *      impact (e.g. a bar chart rendered client side)
     *
     * @param db_object $dbo the word that should be shown to the user
     * @param data_object|null $cfg the cached lists for initial display without backend call
     * @return string the html code of the value chart or an empty string if there is no value
     */
    function value_chart(db_object $dbo, user_message $msg, ?data_object $cfg = null): string
    {
        $result = '';
        if ($dbo::class == word::class) {
            $val_lst = $this->value_related_list($dbo, $msg, $cfg);
            if ($val_lst != null and !$val_lst->is_empty()) {
                $html = new html_base();
                $result = $html->div('value chart', view_styles::COL_SM_12);
            }
        }
        return $result;
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param word|db_object|type_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function values_by_word(
        word|db_object|type_object|null $dbo,
        user_message                    $msg,
        ?data_object                    $dto = null,
        ?int                            $style_id = null
    ): string
    {
        $val_lst = $this->value_related_list($dbo, $msg, $dto);
        // show the grouped list (value_list::list_most_relevant) so the newest time period and the
        // phrases shared by several values are highlighted, matching this "most relevant and related
        // values" component that the default word view uses
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $msg, $style_id, true);
    }

    /**
     * show the values related to the given object grouped for a quick overview: the newest time period
     * first, then the phrases used by several values, then the remaining values by impact (see
     * value_list::list_most_relevant and docs/llm/pending_next_launch.md)
     *
     * @param word|db_object|type_object|null $dbo the object the values are related to
     * @param data_object|null $dto the data cache used to fill the value list until the backend answers
     * @param int|null $style_id the optional list column style
     * @return string the html code to show the grouped list of values
     */
    function values_most_relevant(
        word|db_object|type_object|null $dbo,
        user_message                    $msg,
        ?data_object                    $dto = null,
        ?int                            $style_id = null
    ): string
    {
        $val_lst = $this->value_related_list($dbo, $msg, $dto);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $msg, $style_id, true);
    }

    /**
     * show the values related to the given phrase in up to four columns that are shown side by side
     * on wide screens and wrap onto fewer columns as the screen gets narrower; each column is headed
     * by one of the phrases used most often within the values (e.g. inhabitants for a city)
     *
     * @param word|db_object|type_object|null $dbo the phrase the values are related to
     * @param data_object|null $dto the data cache used until the backend has returned the values
     * @return string the html code of the value columns or '' if the phrase has no values
     */
    function values_in_columns(
        word|db_object|type_object|null $dbo,
        user_message                    $msg,
        ?data_object                    $dto = null
    ): string
    {
        $result = '';
        // guard the phrase before the value load, because value_related_list reads $dbo::class
        if ($dbo != null) {
            $val_lst = $this->value_related_list($dbo, $msg, $dto);
            // a phrase without any value shows no column at all instead of an empty row
            if ($val_lst != null) {
                $phr_lst = new phrase_list();
                $phr_lst->add_phrase($dbo->phrase());
                $result = $val_lst->columns_by_phrase($msg, $phr_lst);
            }
        }
        return $result;
    }

    /**
     * show the values related to the given phrase as a table with one column per phrase used most
     * often within the values (e.g. inhabitants and area for a city) and one row per remaining
     * phrase combination (e.g. per year), so that the values of one row can be compared
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase the values are related to
     * @param data_object|null $dto the data cache used until the backend has returned the values
     * @param bool $with_header true to name the phrase of the page centred above the table, false
     *                          where the page already says which phrase the table is about
     * @param bool $with_border true for the bordered standard table, false for a table without
     *                          the lines between the cells e.g. below a title that groups tables
     * @return string the html code of the value table or '' if the phrase has no values
     */
    function table_with_related_columns(
        word|triple|db_object|type_object|null $dbo,
        user_message                          $msg,
        ?data_object                          $dto = null,
        bool                                  $with_header = false,
        bool                                  $with_border = true,
        array                                 $url_array = []
    ): string
    {
        $result = '';
        // guard the phrase before the value load, because value_related_list reads $dbo::class
        if ($dbo != null) {
            // only a phrase has the related values this table is built from; for any other
            // class say so instead of rendering an empty table, because the caller is a
            // component type that a view may assign to any object
            if ($dbo::class != word::class and $dbo::class != triple::class) {
                $msg->add_warning_with_vars(msg_id::TABLE_COLUMNS_NOT_IMPLEMENTED, [
                    msg_id::VAR_CLASS_NAME => library::class_to_name_translated($dbo::class),
                ]);
                return $result;
            }
            $val_lst = $this->value_related_list($dbo, $msg, $dto, $dto?->phr_lst);
            // a phrase without any value shows no table at all instead of an empty header row
            if ($val_lst != null) {
                $phr_lst = new phrase_list();
                $phr_lst->add_phrase($dbo->phrase());
                // the system column tiers decide which phrase heads a column and in which order;
                // an empty list falls back to the impact ranking of the values themselves
                if ($dto != null) {
                    $this->add_column_definitions($dto, $msg);
                }
                $col_order = $dto?->phr_lst?->column_names() ?? [];
                // the url of the page is handed over, so that the "... more" tail can call the
                // same page with the next list size
                $result = $val_lst->table_by_related_columns(
                    $msg, $phr_lst, '', $col_order, $with_header, $with_border, $dto?->phr_lst,
                    null, $url_array);
            }
        }
        return $result;
    }

    /**
     * the values shown by values_by_word: a phrase loaded with its related values carries them
     * directly (e.g. the default word view), otherwise they are taken from the data cache
     *
     * @param word|triple|db_object|type_object|null $dbo the object the values are related to
     * @param data_object|null $dto the data cache used until the backend has returned the values
     * @return value_list|null the values related to the given object or null if there are none
     */
    private function value_related_list(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto,
        ?phrase_list                           $ctx_lst = null
    ): ?value_list
    {
        $val_lst = $dto?->val_lst?->filter($msg, $dbo, $ctx_lst);
        // a word and a triple are both loaded with their values, so both carry them directly
        if (($dbo::class == word::class or $dbo::class == triple::class) and $dbo->val_lst != null) {
            $val_lst = $dbo->val_lst;
        }
        return $val_lst;
    }

    /**
     * show a list of values related to the given triple
     *
     * @param triple|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function values_by_triple(
        triple|db_object|null $dbo,
        user_message          $msg,
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($msg, $dbo);
        // the triple carries its own related values from the INCL_RELATED api message
        if ($dbo::class == triple::class and $dbo->val_lst != null) {
            $val_lst = $dbo->val_lst;
        }
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        // show the grouped list (list_most_relevant) like the default word view
        return $this->value_list($val_lst, $phr_lst, $msg, $style_id, true);
    }

    /**
     * the values that name the given source with the unit and the phrases of each value,
     * used by the source default page
     *
     * @param source|db_object|null $dbo the source whose values should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @param int|null $style_id the optional list column style
     * @return string the values of the source or the message that the source is not used for values
     */
    function values_by_source(
        source|db_object|null $dbo,
        user_message          $msg,
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the source is missing to select the values of a source', $msg);
        } elseif ($dbo::class == source::class) {
            // a source loaded for its page carries all its values (see source::load_by_id_with_related),
            // whereas the page cache holds only the values of the shown phrases, so it would list the
            // values of the source that happen to be on the page and not all of them
            $val_lst = $dbo->val_lst;
            if ($val_lst == null and $dto?->val_lst != null) {
                $val_lst = $dto->val_lst->filter($msg, $dbo);
            }
            // without the preloaded values the list cannot be created, and showing the not-used
            // message would tell the user that the source has no values, which is not known here
            if ($val_lst == null) {
                log_err_msg_ui('the value cache is missing to select the values of a source', $msg);
            } else {
                $result = $this->value_list_unit($val_lst, $msg, $style_id);
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NOT_USED_FOR_VALUES);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for values', $msg);
        }
        return $result;
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param formula|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function results_by_word(
        formula|db_object|null $dbo,
        user_message           $msg,
        ?data_object           $dto = null,
        ?int                   $style_id = null
    ): string
    {
        $res_lst = $dto->res_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->result_list_by($res_lst, $phr_lst, $msg, $style_id);
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param value_list $val_lst
     * @param phrase_list $phr_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function value_list(
        value_list   $val_lst,
        phrase_list  $phr_lst,
        user_message $msg,
        ?int         $style_id = null,
        bool         $most_relevant = false
    ): string
    {
        global $ui_sys;
        $html = new html_base();
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        // the "most relevant" component groups the values, the plain one just sorts them by impact
        if ($most_relevant) {
            $result = $val_lst->list_most_relevant($msg, $phr_lst, '', $style_txt);
        } else {
            $result = $val_lst->list($msg, $phr_lst, '', $style_txt);
        }
        // wrap the value lines in a block div so each value stays on one line;
        // as a LIST_GROUP component the related-value list is emitted without an
        // auto row, so without this block the bare inline phrases land directly
        // in the flex-column main container and every phrase is pushed onto its
        // own line
        if ($result != '') {
            $result = $html->div($result, view_styles::COL_SM_12);
        }
        return $result;
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param value_list $val_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function value_list_unit(
        value_list   $val_lst,
        user_message $msg,
        ?int         $style_id = null
    ): string
    {
        global $ui_sys;
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        return $val_lst->list_unit($msg);
    }

    /**
     * show a list of values related to the given object
     * the list is first created based on the given data object
     * but additional an update of the list is request via api
     * if the updated list is returned from the backend the list is updated
     *
     * @param result_list $res_lst
     * @param phrase_list $phr_lst
     * @param int|null $style_id id
     * @return string the html code to show the list of values
     */
    private function result_list_by(
        result_list  $res_lst,
        phrase_list  $phr_lst,
        user_message $msg,
        ?int         $style_id = null
    ): string
    {
        global $ui_sys;
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        return $res_lst->list($msg, $phr_lst, '', $style_txt);
    }

    /**
     * the results of the given formula as a comma separated list of the result names with a
     * link to each result
     *
     * @param db_object|null $dbo the formula whose results should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @param data_object|null $cfg the request cache with the preloaded results
     * @return string the linked result names or the message that the formula has no results
     */
    function result_list(?db_object $dbo = null, user_message $msg, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        // without the formula or the preloaded results the list cannot be created, and showing the
        // not-used message would tell the user that the formula has no results, which is not known here
        if ($dbo == null or $cfg?->res_lst == null) {
            log_err_msg_ui('the formula or the result cache is missing to select the results of a formula', $msg);
        } elseif ($dbo::class == formula::class) {
            $res_lst = clone $cfg->res_lst;
            $res_lst = $res_lst->get_by_formula($dbo);
            $result = $res_lst->name_link();
            if ($result == '') {
                $result = $mtr->txt(msg_id::INFO_NOT_USED_FOR_FORMULAS);
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for results', $msg);
        }
        return $result;
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return 'results component';
    }

    /**
     * TODO Prio 0 fill with real code
     * @param db_object|combine_named|null $dbo the term whose related results should be listed
     * @param data_object|null $cfg the context used to create the view
     * @return string the html code listing all results related to $dbo
     */
    function results_related(db_object|combine_named|null $dbo = null, ?data_object $cfg = null): string
    {
        // the results of e.g. a formula are loaded into the data object result list and shown
        // as a table of the result phrases and their value
        $result = '';
        if ($cfg != null and !$cfg->res_lst->is_empty()) {
            $result = $cfg->res_lst->table();
        }
        return $result;
    }

    /**
     * TODO Prio 0 fill with real code
     * @param db_object|combine_named|null $dbo the term whose related phrases should be listed
     * @param data_object|null $cfg the context used to create the view
     * @return string the html code listing the related phrases with details
     */
    function phrases_related(user_message $msg, db_object|combine_named|null $dbo = null, ?data_object $cfg = null): string
    {
        $result = '';
        if ($dbo != null) {
            $result = $this->phrases_related_ex_verbs($dbo, $cfg?->phrase_list(), [], $msg);
        }
        return $result;
    }

    /**
     * @return string the html code of a sortable list
     * @deprecated the fixed start page rows are replaced by start_list, see web/html/list_sort.php
     */
    function list_sort(
        phrase       $phr,
        user_message $msg,
        data_object  $dto
    ): string
    {
        $lst = new list_sort();
        return $lst->list_sort($phr, $msg, $dto);
    }

    /**
     * the table of the start view: the values of the "global problem" phrase with one column per
     * phrase that the column tiers define, e.g. the problem, its loss, the solution and its gain
     *
     * the request cache is asked first, because it carries the phrase with its from, verb and to
     * phrases, which the table needs to head the row column by the phrase the page phrase is
     * built from ("problem" for "global problem")
     *
     * @param data_object $dto the data cache used to reduce the backend traffic
     * @param user_message $msg to collect the load warnings for the user
     * @param array $url_array the url parameters of the start page, which name the list size
     *                         that a "... more" click has raised
     * @return string the html code for the start view as a table
     */
    function start_list(
        data_object  $dto,
        user_message $msg,
        array        $url_array = []
    ): string
    {
        $phr = $this->start_page_phrase($dto, $msg);
        $this->add_start_page_cache($dto, $phr, $msg);
        // the page already says what the table is about, so it is shown without the border
        return $this->table_with_related_columns($phr->obj(), $msg, $dto, true, false, $url_array);
    }

    /**
     * the page phrase of the start view, which is "global problem", with the phrases it is
     * built from
     *
     * the row column is headed by the phrase that the page phrase is built from ("problem"), and
     * a load by name does not carry that phrase; the triples that link a problem to the page
     * phrase do, so on a cold cache the page phrase is taken from them, and it is kept in the
     * cache, so that every later component of the page finds it by name
     *
     * @param data_object $dto the request cache, which gets the page phrase and its links
     * @param user_message $msg to report a problem of an api message to the user
     * @return phrase the page phrase of the start view
     */
    private function start_page_phrase(data_object $dto, user_message $msg): phrase
    {
        $result = $dto->phr_lst->get_by_name(triple_names::GLOBAL_PROBLEM, $msg);
        if ($result == null) {
            $child_lst = new phrase_list();
            if ($child_lst->load_related_by_name(
                triple_names::GLOBAL_PROBLEM, foaf_direction::DOWN, $msg)) {
                $dto->add_phrases($child_lst, $msg);
                // every child is a triple that links to the page phrase, so the first one
                // carries it; the sort makes the pick deterministic
                $child_lst->sort_by_impact();
                $first = $child_lst->lst()[0];
                if ($first->is_triple()) {
                    $result = $first->obj()->get_to();
                }
            }
            if ($result != null) {
                // the page phrase is no link, so it is added to the cache on its own
                $page_lst = new phrase_list();
                $page_lst->add_phrase($result);
                $dto->add_phrases($page_lst, $msg);
            }
        }
        if ($result == null) {
            // no problem is linked to the page phrase, so the table has no row, but the page
            // phrase itself is still needed to head the empty table
            $result = new phrase();
            $result->load_by_name(triple_names::GLOBAL_PROBLEM, $msg);
        }
        return $result;
    }

    /**
     * TODO Prio 1 avoid this exception
     * add the phrases and the values that the table of the start view needs to the request cache
     *
     * each step is skipped if the cache already has that data, so a unit test that fills the
     * cache upfront needs no api call at all
     *
     * @param data_object $dto the request cache to fill
     * @param phrase $phr the page phrase of the start view, which is "global problem"
     * @param user_message $msg to report a problem of an api message to the user
     * @return void
     */
    private function add_start_page_cache(data_object $dto, phrase $phr, user_message $msg): void
    {
        // "global problem" itself is in no value group, so the rows are the phrases that a
        // triple links to it; without those triples the table finds no value at all
        if ($dto->phr_lst->child_phrases($phr)->is_empty()) {
            $child_lst = new phrase_list();
            if ($child_lst->load_related_by_name(
                triple_names::GLOBAL_PROBLEM, foaf_direction::DOWN, $msg)) {
                $dto->add_phrases($child_lst, $msg);
            }
        }
        $this->add_column_definitions($dto, $msg);
        // the values belong to the problems and not to "global problem", so they are loaded for
        // the children
        if ($dto->val_lst->is_empty()) {
            $val_lst = new value_list();
            if ($val_lst->load_by_phr_lst($dto->phr_lst->child_phrases($phr), $msg)) {
                $dto->val_lst = $val_lst;
            }
        }
        // a defined column that no value carries names a phrase of the row instead of a number,
        // e.g. the solution column shows the solution of the problem row; the table recognises
        // that phrase by the triple that links it to the column, e.g. "reduce climate gas
        // emissions is a solution", so the links of every phrase of the values are loaded once
        $val_phr_lst = $dto->val_lst->phrase_list();
        if (!$val_phr_lst->is_empty() and !$this->column_links_loaded($dto)) {
            $lnk_lst = new phrase_list();
            if ($lnk_lst->load_related_by_ids($val_phr_lst, foaf_direction::UP, $msg)) {
                $dto->add_phrases($lnk_lst, $msg);
            }
        }
    }

    /**
     * add the column definitions of the system column tiers to the request cache, so that a
     * value table shows the defined columns in the defined order instead of falling back to
     * the impact ranking; skipped if the cache already has a definition, so a unit test that
     * fills the cache upfront needs no api call, and skipped offline, because the request
     * cache of a unit test has no api to ask
     *
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of an api message to the user
     * @return void
     */
    private function add_column_definitions(data_object $dto, user_message $msg): void
    {
        if ($dto->online and $dto->phr_lst->column_names() == []) {
            $col_lst = new phrase_list();
            if ($col_lst->load_column_definitions($msg)) {
                $dto->add_phrases($col_lst, $msg);
            }
        }
    }

    /**
     * true if the request cache already links a phrase to a defined table column
     *
     * e.g. the triple "reduce climate gas emissions is a solution" links a solution to the
     * solution column; one such link is enough, because they are loaded for all columns at once
     *
     * @param data_object $dto the request cache to check
     * @return bool true if the links of the column phrases are already cached
     */
    private function column_links_loaded(data_object $dto): bool
    {
        $result = false;
        foreach ($dto->phr_lst->column_names() as $name) {
            $col_phr = $dto->phr_lst->column_phrase($name);
            if ($col_phr != null and !$dto->phr_lst->child_phrases($col_phr)->is_empty()) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return string the html code of the results that changes if the pending user change is confirmed
     */
    function result_changes(
        result_list|db_object $dbo
    ): string
    {
        return $dbo->display();
    }

}
