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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_link_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::TYPES . 'type_object.php';
//include_once html_paths::RESULT . 'result_list.php';
//include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::VERB . 'verb.php';
include_once html_paths::WORD . 'triple.php';
include_once html_paths::WORD . 'word.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'foaf_direction.php';
//include_once test_paths::CONST . 'triple_names.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
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
    function parents_of_word(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::UP, $this->related_list($wrd, $phr_lst));
    }

    /**
     * HTML for a list of words or triples
     * @param word|db_object $wrd the object that should be used to select the related objects e.g. the triple "canton of Zurich"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function children_of_word(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases($wrd->phrase(), foaf_direction::DOWN, $this->related_list($wrd, $phr_lst));
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
    function phrase_aliases(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_by_verb($wrd, verbs::ALIAS, msg_id::PHRASE_ALIAS, msg_id::PHRASE_ALIASES, $phr_lst);
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
    function phrase_symbols(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_by_verb($wrd, verbs::SYMBOL, msg_id::PHRASE_SYMBOL, msg_id::PHRASE_SYMBOLS, $phr_lst);
    }

    /**
     * HTML for the phrases related to the given phrase excluding the alias and symbol entries
     * because these are already shown by the phrase_aliases and phrase_symbols components
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @return string the html code with the remaining related phrases
     */
    function phrases_related_ex_symbols(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_related_ex_verbs($wrd, $phr_lst, [verbs::SYMBOL, verbs::ALIAS]);
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
    function phrases_related_ex_subtitle(word|db_object $wrd, ?phrase_list $phr_lst = null): string
    {
        return $this->phrases_related_ex_verbs($wrd, $phr_lst, [verbs::SYMBOL, verbs::ALIAS, verbs::IS]);
    }

    /**
     * HTML for the phrases related to the given phrase excluding the triples linked by the
     * verbs in $ex_vrb_lst (an empty list shows all related phrases)
     * sorted with the highest impact first e.g. for stocks the highest market capitalisation
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @param array $ex_vrb_lst the code ids of the verbs whose triples should not be shown
     * @return string the html code with the remaining related phrases
     */
    private function phrases_related_ex_verbs(
        word|phrase|db_object $wrd,
        ?phrase_list          $phr_lst,
        array                 $ex_vrb_lst
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
            $result = $phr_cac->parent_triples_ex_verbs($phr, $vrb_ids)->name_link_by_impact();
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
        ?phrase_list   $phr_lst
    ): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $result = '';
        $vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id($vrb_code_id);
        $phr_cac = $this->related_list($wrd, $phr_lst);
        if ($vrb != null and $phr_cac != null) {
            $lst = $phr_cac->parents($wrd->phrase(), $vrb);
            if (!$lst->is_empty()) {
                $msg = $msg_one;
                if ($lst->count() > 1) {
                    $msg = $msg_many;
                }
                $vrb_lnk = $html->ref($html->url_new(views::VERB_ID, $vrb->id()), $mtr->txt($msg));
                $result = $html->span(
                    $mtr->txt(msg_id::PHRASE_HAS) . ' ' . $vrb_lnk . ': ' . $lst->name_link(),
                    styles::TEXT_NOWRAP
                );
            }
        }
        return $result;
    }

    /**
     * HTML for a list of words or triples linked to the given formula in order of impact
     * @param formula|db_object $frm the object that should be used to select the related objects e.g. the triple "canton of Zurich"
     * @param data_object|null $cac the cached list of phrases for initial display without backend call
     * @return string the html code to start a new form and display the tile
     */
    function phrases_of_formula(formula|db_object $frm, ?data_object $cac = null): string
    {
        global $ui_sys;

        $page = new system_page();

        $result = $page->system_sub_tile(msg_id::FORM_SUB_TITLE_ASSIGNED_PHRASES);
        $lnk_lst = $cac?->frm_lnk_lst;
        // TODO Prio 2 decide if and when a reloading via api is done
        if ($lnk_lst == null) {
            $lnk_lst = new formula_link_list();
            $lnk_lst->load_by_formula_id($frm->id());
        }
        $phr_lst = $lnk_lst->get_phrase_list($cac->phr_lst);
        if ($phr_lst->is_empty()) {
            $phr_lst = new phrase_list();
            $phr_lst->load_by_formula($frm);
        }
        if ($ui_sys?->cfg !== null) {
            $row_limit = $ui_sys->cfg->get_by([triples::LINK_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER], config::LIMIT_NAME_LIST);
        } else {
            $row_limit = config::LIMIT_NAME_LIST;
        }
        $result .= $phr_lst->name_link('', $row_limit);
        return $result;
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_list(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $trp_lst = clone $cfg->trp_lst;
        if ($dbo::class == verb::class) {
            $trp_lst = $trp_lst->get_by_verb($dbo);
            $result = $trp_lst->display();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for triples');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::NOT_USED_FOR_TRIPLES);
        }
        return $result;
    }

    /**
     * get a list of formulas related to e.g. a verb
     * @param db_object $dbo e.g. a verb to select only the formulas where the object is used
     * @param data_object|null $cfg the cache values used for a backend independent preselection of the formulas
     * @return string the most relevant formulas related to e.g. a verb
     */
    function formula_list(db_object $dbo, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $frm_lst = clone $cfg->frm_lst;
        if ($dbo::class == verb::class) {
            $frm_lst = $frm_lst->get_by_verb($dbo);
            $result = $frm_lst->name_link();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for formulas');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::NOT_USED_FOR_VERB);
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
                $phr_lst = $phr_cac->parent_triples($phr);
            } elseif ($dir == foaf_direction::DOWN) {
                $phr_lst = $phr_cac->children($phr);
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
    function ref_list_word(db_object $dbo, ?data_object $dto): string
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
            // a word loaded for its page carries its references directly (like the related
            // values and formulas); otherwise fall back to the page reference cache
            if ($dbo::class == word::class and $dbo->ref_lst != null) {
                $ref_lst = $dbo->ref_lst;
            } else {
                $ref_lst = $dto->ref_list_cloned()->get_by_phrase($phr);
            }
            $phr_lst = new phrase_list();
            $phr_lst->add_phrase($dbo->phrase());
            $result = $ref_lst->list($phr_lst);
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
     * HTML for the col-4 tab box of the word page: a "Views" tab with the related views
     * (each a preview placeholder plus the open and switch buttons) and a "Changes" tab
     * with the change log of the word, latest first
     * TODO Prio 3 replace the view preview placeholder with a real miniature preview
     *
     * @param db_object $dbo the word that should be shown to the user
     * @param bool $test_mode true to create a reproducible result without a backend call
     * @return string the html code of the tab box or an empty string for a non-word object
     */
    function view_tab_box(db_object $dbo, bool $test_mode = false): string
    {
        global $mtr;
        $result = '';
        if ($dbo::class == word::class) {
            $html = new html_base();
            // tab 1: each related view as a preview placeholder with the open and switch buttons
            $views_html = '';
            if ($dbo->view_lst != null) {
                foreach ($dbo->view_lst->lst() as $msk) {
                    $preview = $html->div('view preview', view_styles::COL_SM_12);
                    $buttons = $msk->open_link($dbo->id()) . ' ' . $msk->switch_link($dbo->id());
                    $views_html .= $html->div($preview . $msk->name() . ' ' . $buttons);
                }
            }
            // tab 2: the change log of the word, latest first
            $log_html = '';
            if ($dbo->chg_log != null) {
                $log_html = $dbo->chg_log->filter($dbo)->dsp(null, false, false, $test_mode);
            }
            $result = $html->tab_box([
                $mtr->txt(msg_id::FORM_SUB_TITLE_VIEWS) => $views_html,
                $mtr->txt(msg_id::FORM_SUB_TITLE_LOG) => $log_html,
            ]);
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
        return 'list of phrases related to ' . $dbo->name() . ' ';
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
    function formulas(word|phrase|db_object $wrd, ?data_object $cac = null, bool $test_mode = false): string
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
                $frm_lst = $lnk_lst->get_formula_list($phr, $cac->frm_lst);
            } elseif (!$test_mode) {
                // TODO Prio 2 decide if and when a reloading via api is done
                $frm_lst->load_by_phr_id($phr->id());
            }
        }
        if ($ui_sys?->cfg !== null) {
            $row_limit = $ui_sys->cfg->get_by(
                [triples::LINK_LIST, words::LIMIT, words::LISTS, words::FRONTEND, words::USER],
                config::LIMIT_NAME_LIST);
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
    function value_chart(db_object $dbo, ?data_object $cfg = null): string
    {
        $result = '';
        if ($dbo::class == word::class) {
            $val_lst = $this->value_related_list($dbo, $cfg);
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
        ?data_object                    $dto = null,
        ?int                            $style_id = null
    ): string
    {
        $val_lst = $this->value_related_list($dbo, $dto);
        // value_list::list() sorts by impact and limits the number via the user/config setting
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $style_id);
    }

    /**
     * the values shown by values_by_word: a word loaded with its related values carries them
     * directly (e.g. the default word view), otherwise they are taken from the data cache
     *
     * @param word|db_object|type_object|null $dbo the object the values are related to
     * @param data_object|null $dto the data cache used until the backend has returned the values
     * @return value_list|null the values related to the given object or null if there are none
     */
    private function value_related_list(
        word|db_object|type_object|null $dbo,
        ?data_object                    $dto
    ): ?value_list
    {
        $val_lst = $dto?->val_lst?->filter($dbo);
        if ($dbo::class == word::class and $dbo->val_lst != null) {
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
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->value_list($val_lst, $phr_lst, $style_id);
    }

    /**
     * show a list of values related to the given triple
     *
     * @param source|db_object|null $dbo the selection object for the value list e.g. if mathematics the most often use math const are shown
     * @param data_object|null $dto the data cache used to fill the value list until the backend has returned the updated list
     * @return string the html code to show the list of values
     */
    function values_by_source(
        source|db_object|null $dbo,
        ?data_object          $dto = null,
        ?int                  $style_id = null
    ): string
    {
        $val_lst = $dto->val_lst?->filter($dbo);
        return $this->value_list_unit($val_lst, $style_id);
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
        ?data_object           $dto = null,
        ?int                   $style_id = null
    ): string
    {
        $res_lst = $dto->res_lst?->filter($dbo);
        $phr_lst = new phrase_list();
        $phr_lst->add_phrase($dbo->phrase());
        return $this->result_list_by($res_lst, $phr_lst, $style_id);
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
        value_list  $val_lst,
        phrase_list $phr_lst,
        ?int        $style_id = null
    ): string
    {
        global $ui_sys;
        $html = new html_base();
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        // wrap the value lines in a block div so each value stays on one line;
        // as a LIST_GROUP component the related-value list is emitted without an
        // auto row, so without this block the bare inline phrases land directly
        // in the flex-column main container and every phrase is pushed onto its
        // own line
        $result = $val_lst->list($phr_lst, '', $style_txt);
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
        value_list $val_lst,
        ?int       $style_id = null
    ): string
    {
        global $ui_sys;
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        return $val_lst->list_unit();
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
        result_list $res_lst,
        phrase_list $phr_lst,
        ?int        $style_id = null
    ): string
    {
        global $ui_sys;
        $style_txt = '';
        if ($style_id != null) {
            $style_txt = $ui_sys->typ_lst_cache->msk_sty->get_code_id($style_id);
        }
        return $res_lst->list($phr_lst, '', $style_txt);
    }

    /**
     * @return string a dummy text
     */
    function result_list(?db_object $dbo = null, ?data_object $cfg = null): string
    {
        global $mtr;

        $result = '';
        $res_lst = clone $cfg->res_lst;
        if ($dbo::class == formula::class) {
            $res_lst = $res_lst->get_by_formula($dbo);
            $result = $res_lst->name_link();
        } else {
            log_err($dbo::class . '  is not expected to be a selection for results');
        }
        if ($result == '') {
            $result = $mtr->txt(msg_id::INFO_NOT_USED_FOR_FORMULAS);
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
        return 'results_related placeholder';
    }

    /**
     * TODO Prio 0 fill with real code
     * @param db_object|combine_named|null $dbo the term whose related phrases should be listed
     * @param data_object|null $cfg the context used to create the view
     * @return string the html code listing the related phrases with details
     */
    function phrases_related(db_object|combine_named|null $dbo = null, ?data_object $cfg = null): string
    {
        $result = '';
        if ($dbo != null) {
            $result = $this->phrases_related_ex_verbs($dbo, $cfg?->phrase_list(), []);
        }
        return $result;
    }

    /**
     * @return string the html code of a sortable list
     */
    function list_sort(
        phrase      $phr,
        data_object $dto
    ): string
    {
        $lst = new list_sort();
        return $lst->list_sort($phr, $dto);
    }

    /**
     * @return string the html code for the start view as a sortable list
     */
    function start_list(
        data_object $dto
    ): string
    {
        $phr = new phrase();
        $phr->load_by_name(triple_names::GLOBAL_PROBLEM);
        return $this->list_sort($phr, $dto);
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
