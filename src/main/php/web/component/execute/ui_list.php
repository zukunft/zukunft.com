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

include_once html_paths::CONST . 'icons.php';
include_once html_paths::FORMULA . 'formula.php';
include_once html_paths::FORMULA . 'formula_calc.php';
include_once html_paths::FORMULA . 'formula_link_list.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::EXECUTE . 'calculator.php';
include_once html_paths::EXECUTE . 'ui_log.php';
include_once html_paths::HELPER . 'config.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::HTML . 'styles.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::PHRASE . 'term_list.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::REF . 'source_list.php';
include_once html_paths::RESULT . 'result.php';
include_once html_paths::VALUE . 'value.php';
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
include_once html_paths::SANDBOX . 'sandbox_value.php';
include_once html_paths::SHARED_CONST . 'files.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::SHARED_CONST . 'triples.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_TYPES . 'verbs.php';
include_once html_paths::SHARED_TYPES . 'view_styles.php';
include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED . 'library.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_ENUM . 'foaf_direction.php';
include_once html_paths::SHARED_ENUM . 'table_forms.php';

use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\formula\formula_calc;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\helper\config;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\html_selector;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\result\result;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_value;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\value\value;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\shared\const\files;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config as shared_config;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\enum\table_forms;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;

class ui_list extends ui_base
{

    // put between the html id of a number and the count of a cell that shows the number again,
    // e.g. the year of a detail table, so that every cell id is unique on a page
    const string CALC_CELL_REPEAT = '-';
    // the html id and the keys of the calculation data of a calculator page, which the browser
    // script reads to repeat the calculation (see calculator_script and calculator.js)
    const string CALC_DATA_ID = 'calc-data';
    const string CALC_NUMBERS = 'numbers';
    const string CALC_RESULTS = 'results';
    const string CALC_INPUTS = 'inputs';
    const string CALC_CELLS = 'cells';
    const string CALC_FORMAT = 'format';
    const string CALC_CELL_NUMBER = 'number';
    const string CALC_CELL_UNIT = 'unit';
    const string CALC_CELL_PERCENT = 'percent';
    const string CALC_CELL_SIGNED = 'signed';
    const string CALC_THOUSAND_SEP = 'thousand_sep';
    const string CALC_PERCENT_DECIMALS = 'percent_decimals';
    const string CALC_POSITIVE = 'positive';
    const string CALC_NEGATIVE = 'negative';

    // the results whose context values this renderer has loaded, so a context is loaded once per page
    private array $ctx_loaded = [];
    // per html id of a number how often a calculator cell of this renderer has shown it (see calculator_cell_id)
    private array $cell_counts = [];
    // per calculator cell id that this renderer has written the number and the format of the cell
    private array $cell_plan = [];

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
     * are already shown in the page subtitle (e.g. on the default word page); a phrase without an
     * "is a" parent gets the form to define it as one of the categories first (see define_form)
     *
     * @param word|db_object $wrd the object shown to the user e.g. the word "US dollar"
     * @param phrase_list|null $phr_lst the cached list of phrases for initial display without backend call
     * @param data_object|null $cfg the request cache with the categories that the phrase can be defined as
     * @param bool $test_mode true to build the define form without a backend call
     * @param array $url_arr the url of the shown page as back target of the triple add view
     * @return string the html code with the define form and the remaining related phrases
     */
    function phrases_related_ex_subtitle(
        word|db_object $wrd,
        user_message   $msg,
        ?phrase_list   $phr_lst = null,
        ?data_object   $cfg = null,
        bool           $test_mode = false,
        array          $url_arr = []
    ): string
    {
        $phr_cac = $this->related_list($wrd, $phr_lst);
        $result = $this->define_form($wrd->phrase(), $phr_cac, $msg, $cfg, $test_mode, $url_arr);
        $ex_vrb_lst = [verbs::SYMBOL, verbs::ALIAS, verbs::IS];
        return $result . $this->phrases_related_ex_verbs($wrd, $phr_lst, $ex_vrb_lst, $msg, true);
    }

    /**
     * the plus icon of a word or triple that has no "is a" parent yet, which reveals the form to define
     * it as one of the categories, e.g. "define lugano as [city] add" (like the formula link icon); the
     * button opens the triple add view with the phrase, the verb "is a" and the selected category
     * preselected, so the user checks the new triple before the save
     *
     * @param phrase $phr the shown word or triple
     * @param phrase_list|null $phr_cac the related phrases of the shown phrase with its triples
     * @param user_message $msg to report a problem of the category load
     * @param data_object|null $cfg the request cache with the categories that can be selected
     * @param bool $test_mode true to build the form without a backend call
     * @param array $url_arr the url of the shown page as back target
     * @return string the html code of the icon and the hidden form, empty if the phrase has an "is a" parent
     */
    private function define_form(
        phrase       $phr,
        ?phrase_list $phr_cac,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode,
        array        $url_arr
    ): string
    {
        global $ui_sys;

        $result = '';
        $is_vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id(verbs::IS);
        // without the related phrases or the verb it is unknown whether the phrase has a parent
        if ($phr->id() != 0 and $phr_cac != null and $is_vrb != null) {
            // the "is a" triples with the phrase as from side are its parents
            if ($phr_cac->children($phr, $msg, $is_vrb)->is_empty()) {
                $pane = $this->define_pane($phr, $is_vrb, $msg, $cfg, $test_mode, $url_arr);
                $result = $this->toggle_pane(
                    styles::DEFINE_PHRASE_PANE, icons::ADD, msg_id::DEFINE_PHRASE, msg_id::DEFINE_PHRASE_BLOCKED, $pane);
            }
        }
        return $result;
    }

    /**
     * the pane that the define icon reveals: the text, the category selector and the add button, or the
     * reason why nothing can be defined, i.e. a user without login or no category to select
     *
     * @param phrase $phr the shown word or triple without an "is a" parent
     * @param verb $is_vrb the verb "is a" of the new triple
     * @param user_message $msg to report a problem of the category load
     * @param data_object|null $cfg the request cache with the categories that can be selected
     * @param bool $test_mode true to build the form without a backend call
     * @param array $url_arr the url of the shown page as back target
     * @return string the html code of the pane
     */
    private function define_pane(
        phrase       $phr,
        verb         $is_vrb,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode,
        array        $url_arr
    ): string
    {
        global $mtr;

        $html = new html_base();
        $blocked = html_base::change_blocked();
        $ctg_lst = $cfg?->ctg_lst ?? new phrase_list();
        // a user without login cannot save the triple, so the categories are not even read; the request
        // cache is filled by the unit tests only, so outside of them the categories come from the backend
        if ($ctg_lst->is_empty() and !$test_mode and !$blocked) {
            $ctg_lst->load_by_verb($is_vrb, foaf_direction::UP, $msg);
        }
        if ($blocked) {
            $pane = $html->dsp_notification($mtr->txt(msg_id::CHANGE_BLOCKED_FOR_IP_USER));
        } elseif ($ctg_lst->is_empty()) {
            $pane = $mtr->txt(msg_id::INFO_NO_CATEGORY_TO_SELECT);
        } else {
            $lib = new library();
            $form_name = views::TRIPLE_ADD_DETAIL;
            $fields = $this->hidden_fields([
                url_var::MASK => views::TRIPLE_ADD_DETAIL_ID,
                url_var::PHRASE_FROM => $phr->id(),
                url_var::VERB => $is_vrb->id()
            ], $url_arr);
            $define_txt = $mtr->txt(msg_id::INFO_DEFINE_PHRASE_AS);
            $text = $lib->msg_var_replace($define_txt, msg_id::VAR_PHRASE_NAME, $html->esc($phr->name()));
            $sel = $ctg_lst->selector($form_name, 0, url_var::PHRASE_TO, msg_id::FORM_SELECT_PHRASE_TO);
            $button = $html->form_submit($mtr->txt(msg_id::SYSTEM_BUTTON_ADD));
            $pane = $html->form_start($form_name) . $fields . $text . $sel . $button . $html->form_end();
        }
        return $pane;
    }

    /**
     * the hidden fields of a form that opens another view: the given url vars followed by the shown
     * page as '9'-prefixed back target
     *
     * @param array $url_vars the url vars that the form sends e.g. the mask and the phrase
     * @param array $url_arr the url of the shown page as back target
     * @return string the html code of the hidden fields
     */
    private function hidden_fields(array $url_vars, array $url_arr): string
    {
        $html = new html_base();
        $result = '';
        foreach ($url_vars + html_base::back_url_array($url_arr) as $key => $val) {
            $result .= $html->form_hidden($key, (string)$val);
        }
        return $result;
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
        $result = $page->system_sub_tile($msg, msg_id::FORM_SUB_TITLE_ASSIGNED_PHRASES);

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
        $result .= $phr_lst->name_link([], $row_limit);
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
    function triple_list(
        ?db_object   $dbo = null,
        user_message $msg,
        ?data_object $cfg = null,
        array        $url_arr = [],
    ): string
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
                $result = $trp_lst->display($msg, $url_arr, $this->configured_link_limit($msg));
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
     * the components of the given view as a table with one row per component showing the
     * position number, the component name with a link to the component and the position type,
     * sorted by the position in the view, used by the view default, add and edit pages
     *
     * @param db_object|null $dbo the view whose components should be listed
     * @param user_message $msg to report a missing cache or an unexpected selection object
     * @param array $url_arr the url vars of the calling page for the back link of the action icons
     * @return string the component table or the message that the view has no components
     */
    function view_components(?db_object $dbo, user_message $msg, array $url_arr = []): string
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
            $html = new html_base();
            $rows = '';
            $last_pos = 0;
            if ($cmp_lst == null or $cmp_lst->is_empty()) {
                $result = $mtr->txt(msg_id::INFO_VIEW_HAS_NO_COMPONENTS);
            } else {
                // the table is not cut by the configured name list limit, because the position
                // numbers are only useful if complete and the number of components of one view
                // is bounded by its layout; the position type name comes from the type cache
                $pos_typ_lst = $ui_sys->typ_lst_cache->pos_typ;
                foreach ($cmp_lst->sorted_by_position() as $cmp) {
                    $rows .= $html->tr(
                        $html->td((string)$cmp->position)
                        . $html->td($cmp->name_link([], '', views::COMPONENT_DEFAULT_ID))
                        . $html->td($pos_typ_lst?->name($cmp->pos_type_id) ?? '')
                        . $html->td($this->component_link_icons($cmp, $url_arr)));
                    // a component without a position does not move the end of the list
                    $last_pos = $cmp->position ?? $last_pos;
                }
            }
            // the add row follows the listed components, so the new component is linked at the end
            $rows .= $this->component_add_row($dbo, $last_pos + 1);
            if ($rows != '') {
                $result .= $html->tbl($rows, styles::STYLE_BORDERLESS_GREY);
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for components', $msg);
        }
        return $result;
    }

    /**
     * the edit and the delete icon of one component link of a view, so that the user can change
     * e.g. the position type of the link or remove the component from the view
     *
     * @param component $cmp the component whose link to the shown view can be changed
     * @param array $url_arr the url vars of the calling page for the back link
     * @return string the html code of the two action icons, empty if the link id is not known
     */
    private function component_link_icons(component $cmp, array $url_arr = []): string
    {
        global $mtr;

        $result = '';
        // a component of a view that the api sends without the link id cannot be changed by id
        if ($cmp->link_id != 0) {
            $html = new html_base();
            $result = $html->change_icon($html->url_back(views::COMPONENT_LINK_EDIT_ID, $cmp->link_id, $url_arr),
                    icons::EDIT, $mtr->txt(msg_id::COMPONENT_LINK_EDIT))
                . $html->icon_link(views::COMPONENT_LINK_DEL_ID, $cmp->link_id,
                    icons::DEL, $mtr->txt(msg_id::COMPONENT_UNLINK), $url_arr);
        }
        return $result;
    }

    /**
     * the last row of the component list of a view: a component selector and the add action that
     * opens the confirm page which links the selected component to the view, so that the user can
     * extend a view from the view page instead of opening the component link add form
     *
     * the back target is not set, because the confirm step replaces it by the default page of the
     * created object (see frontend::url_to_action)
     *
     * @param view|db_object $msk the view the selected component should be linked to
     * @param int $pos the position of the new component, which is the end of the view
     * @return string the html code of the add row, empty if no component can be selected
     */
    private function component_add_row(view|db_object $msk, int $pos): string
    {
        global $mtr;
        global $ui_sys;

        $result = '';
        // every component that the frontend cache knows can be added to the view
        $cmp_names = $ui_sys?->typ_lst_cache?->msk_sys?->component_names() ?? [];
        // a view that is not yet saved cannot be linked and an empty cache has nothing to select
        if ($msk->id() != 0 and $cmp_names != []) {
            $html = new html_base();
            $form_name = views::COMPONENT_LINK_ADD;
            $fields = $html->form_hidden(url_var::MASK, (string)views::COMPONENT_LINK_ADD_ID)
                . $html->form_hidden(url_var::STEP, url_var::STEP_CONFIRM)
                . $html->form_hidden(url_var::VIEW, (string)$msk->id())
                . $html->form_hidden(url_var::POSITION, (string)$pos);
            $selector = $this->component_add_selector($form_name, $cmp_names);
            // the add text is in the position column and so outside the form of the next column
            $add_text = $html->button_submit_text($mtr->txt(msg_id::ADD), $form_name);
            $form = $html->form_start($form_name) . $fields . $selector . $html->form_end();
            $result = $html->tr($html->td($add_text) . $html->td($form) . $html->td('') . $html->td(''));
        }
        return $result;
    }

    /**
     * the selector of the components that can be added to the shown view; it is filled here and
     * not by ListBase::selector, because the components come from the view cache as a name list:
     * a component_list in the view list would include the component renderer and with it the
     * view list into itself (see the include block of component_list)
     *
     * the selector fills the table cell, which the table already sizes for the column, and the
     * column and the add text say what is selected, so the field needs no label and shows the
     * short empty entry with the tooltip instead
     *
     * @param string $form_name the form that posts the selected component
     * @param array $cmp_names the name of every selectable component keyed by its database id
     * @return string the html code of the component selector
     */
    private function component_add_selector(string $form_name, array $cmp_names): string
    {
        global $mtr;

        $sel = new html_selector();
        $sel->lst = $cmp_names;
        $sel->name = url_var::COMPONENT;
        $sel->form = $form_name;
        $sel->selected = 0;
        $sel->label_id = msg_id::FORM_SELECT_COMPONENT;
        $sel->style = view_styles::COL_SM_12;
        $sel->with_label = false;
        $sel->dummy_text = $mtr->txt(msg_id::PLEASE_SELECT_SHORT);
        $sel->tooltip = $mtr->txt(msg_id::SELECT_TO_ADD);
        return $sel->display();
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
                        $names[$msk->name()] = $msk->name_link([], '', views::VIEW_DEFAULT_ID);
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
     * the link icon below the formula list of a word or triple and the form that it reveals: a
     * formula selector and the link button that opens the confirm page which assigns the selected
     * formula to the phrase
     *
     * the form is hidden until the icon targets it via the url fragment, so the page stays short
     * and no javascript is needed (see docs/llm/frontend.md); the form sends the shown page as
     * back target, so the user returns to it after the save (see frontend::url_to_action)
     *
     * a user who cannot save a change (see user::is_blocked) gets the icon greyed out and with
     * a tooltip that asks for a login; the icon still opens the pane, which then shows the same
     * reason that the backend would give if the link were sent anyway
     *
     * @param phrase $phr the word or triple the selected formula should be assigned to
     * @param user_message $msg to report a problem of the formula load
     * @param data_object|null $cfg the request cache with the formulas that can be selected
     * @param bool $test_mode true to build the form without a backend call
     * @param array $url_arr the url of the shown page as back target; empty to return to the new link
     * @return string the html code of the link icon and the hidden form, empty for an unsaved phrase
     */
    private function formula_link_form(
        phrase       $phr,
        user_message $msg,
        ?data_object $cfg,
        bool         $test_mode,
        array        $url_arr
    ): string
    {
        global $mtr;

        // a user without login cannot save the link, so the form is replaced by the reason and
        // the formulas are not even read
        $blocked = html_base::change_blocked();

        // the selector offers one selection list of formulas, not the few names that the list
        // above shows; if that list is full more formulas may exist, which the more entry says,
        // because the user would else silently never see them
        $size = config::LIMIT_SEARCH_LIST;
        $frm_lst = $cfg?->formula_list() ?? new formula_list();
        // the request cache is filled by the unit tests only, so outside of them the formulas
        // that can be assigned are requested from the backend (like the assigned formulas above)
        if ($frm_lst->is_empty() and !$test_mode and !$blocked) {
            $frm_lst->load_selectable($size, $msg);
        }

        $result = '';
        // a phrase that is not yet saved cannot be linked; the icon is shown even if no formula
        // can be offered, so that the page looks the same with and without the cache
        if ($phr->id() != 0) {
            $html = new html_base();
            if ($blocked) {
                $pane = $html->dsp_notification($mtr->txt(msg_id::CHANGE_BLOCKED_FOR_IP_USER));
            } elseif ($frm_lst->is_empty()) {
                // an empty selector would post a link without a formula, which can only fail, so
                // the pane says why nothing can be selected instead of a form that never saves
                $pane = $mtr->txt(msg_id::INFO_NO_FORMULA_TO_LINK);
            } else {
                $form_name = views::FORMULA_LINK_ADD;
                $fields = $this->hidden_fields([
                    url_var::MASK => views::FORMULA_LINK_ADD_ID,
                    url_var::STEP => url_var::STEP_CONFIRM,
                    url_var::PHRASE => $phr->id()
                ], $url_arr);
                $sel = $frm_lst->selector_ui($form_name, 0, url_var::FORMULA,
                    msg_id::FORM_SELECT_FORMULA, view_styles::COL_SM_12);
                if ($frm_lst->count() >= $size) {
                    $sel->more_text = $mtr->txt(msg_id::PLEASE_SELECT_SHORT);
                }
                $button = $html->form_submit($mtr->txt(msg_id::SYSTEM_BUTTON_LINK));
                $pane = $html->form_start($form_name) . $fields . $sel->display()
                    . $button . $html->form_end();
            }
            $result = $this->toggle_pane(
                styles::FORMULA_LINK_PANE, icons::LINK, msg_id::FORMULA_LINK, msg_id::FORMULA_LINK_BLOCKED, $pane);
        }
        return $result;
    }

    /**
     * the boxed plus icon behind the formulas subtitle of a word or triple page that opens the formula
     * add view; the phrase travels as '7'-prefixed link var (see url_var::LINK), so the confirm page
     * of the new formula also creates its link to the phrase (see frontend::add_link_of_new)
     *
     * @param phrase $phr the word or triple the new formula should be linked to
     * @param array $url_arr the url of the shown page as back target
     * @return string the html code of the add icon, empty for an unsaved phrase
     */
    function formula_add_link(phrase $phr, array $url_arr = []): string
    {
        $result = '';
        // a phrase without a db id cannot be linked, so there is nothing to add a formula to
        if ($phr->id() != 0) {
            $html = new html_base();
            $link_part = html_base::link_url_part([url_var::PHRASE => $phr->id()]);
            $url = $html->url_back(views::FORMULA_ADD_ID, 0, $url_arr, $link_part);
            $result = $this->change_icon($url, icons::ADD, msg_id::FORMULA_ADD, msg_id::FORMULA_ADD_BLOCKED);
        }
        return $result;
    }

    /**
     * the plus icon behind the values subtitle of a word or triple page that opens the value add view
     * with the shown phrase preset as the first phrase of the new value (see value::url_mapper); the
     * frontend type of the user decides which view adds the value (see config::value_add_view)
     *
     * @param phrase $phr the word or triple that the new value should be assigned to
     * @param user_message $msg to report a problem of reading the frontend type
     * @param array $url_arr the url of the shown page as back target
     * @return string the html code of the add icon, empty for an unsaved phrase
     */
    function value_add_link(phrase $phr, user_message $msg, array $url_arr = []): string
    {
        global $ui_sys;

        $result = '';
        // a phrase without a db id cannot be preset, so there is nothing to add a value to
        if ($phr->id() != 0) {
            $html = new html_base();
            $type = $ui_sys?->cfg?->frontend_type($msg) ?? config::DEFAULT_FRONTEND_TYPE;
            $preset = url_var::PHRASE_LIST . url_var::EQ . $phr->id();
            $url = $html->url_back(config::value_add_view($type), 0, $url_arr, $preset);
            $result = $this->change_icon($url, icons::ADD, msg_id::VALUE_ADD, msg_id::VALUE_ADD_BLOCKED);
        }
        return $result;
    }

    /**
     * an icon link that starts a change e.g. the add or the link of a formula; a user who cannot save a
     * change (see change_blocked) gets the icon greyed out with a tooltip that asks for a login, but the
     * icon stays a link, so the user who presses it gets the reason that the backend would give
     *
     * @param string $url the url or url fragment that the icon opens
     * @param string $icon the icon css class from web/const/icons.php e.g. icons::ADD
     * @param msg_id $tooltip the tooltip for a user who can save the change
     * @param msg_id $tooltip_blocked the tooltip that asks a user without login to log in
     * @return string the html code of the icon link
     */
    private function change_icon(string $url, string $icon, msg_id $tooltip, msg_id $tooltip_blocked): string
    {
        global $mtr;
        $html = new html_base();
        $style = styles::HEADING_ICON_INLINE;
        $txt = $mtr->txt($tooltip);
        if (html_base::change_blocked()) {
            $style .= ' ' . styles::STYLE_GREY;
            $txt = $mtr->txt($tooltip_blocked);
        }
        return $html->ref($url, $html->icon($icon), $txt, $style, true);
    }

    /**
     * a change icon that reveals a hidden pane via the url fragment, e.g. the formula link form or the
     * define form of a phrase, so the page stays short and no javascript is needed (see docs/llm/frontend.md)
     *
     * @param string $pane_id the css id of the pane that the icon reveals e.g. styles::FORMULA_LINK_PANE
     * @param string $icon the icon css class from web/const/icons.php e.g. icons::LINK
     * @param msg_id $tooltip the tooltip for a user who can save the change
     * @param msg_id $tooltip_blocked the tooltip that asks a user without login to log in
     * @param string $pane the html code that is shown after the icon has been pressed
     * @return string the html code of the icon and the hidden pane
     */
    private function toggle_pane(
        string $pane_id,
        string $icon,
        msg_id $tooltip,
        msg_id $tooltip_blocked,
        string $pane
    ): string
    {
        $html = new html_base();
        $icon_html = $this->change_icon('#' . $pane_id, $icon, $tooltip, $tooltip_blocked);
        return $html->div($icon_html) . $html->div($pane, styles::TOGGLE_PANE, $pane_id);
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
            $phr_lst->load_related($phr, $dir, $msg);
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
            // the plus icon at the end of the list is the only way to add a reference to the
            // phrase, so it is also shown if the phrase has no reference yet
            $result .= $ref_lst->add_link($phr);
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
            $my_html = $preview->user_overwrites_table($dbo, $msg, $url_array, $test_mode);
            // tab 4: the shared overwrites that other users have done on this object
            $others_html = $preview->other_overwrites_table($dbo, $msg, $url_array, $test_mode);
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
            // the view name links to the object shown with the view, the switch icon opens the
            // edit view of the shown object with this view preselected as its default view, and
            // the edit icon changes the view itself; the edit view of the object differs per
            // class, so its id is passed to the link builder. the links escape the names (stored
            // xss via a user given name), so the div gets already-built html only
            $links = $msk->open_link($dbo->id(), $dbo->name())
                . ' ' . $msk->switch_link($dbo->id(), $dbo::VIEW_EDIT_ID, $dbo->name())
                . ' ' . $msk->edit_link();
            $result .= $html->div($preview . $links);
        }
        return $result;
    }

    /**
     * the terms that use the given view as links, used by the used by column of the view add and
     * edit pages: the terms come with the view of a page request (see view::api_json_array),
     * because no frontend cache carries the term views
     *
     * @param view|db_object|null $dbo the view whose terms should be listed
     * @param user_message $msg to report an unexpected selection object
     * @return string the term links or the message that no term uses the view
     */
    function view_terms(?db_object $dbo, user_message $msg): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the view is missing to list the terms that use it', $msg);
        } elseif ($dbo::class == view::class) {
            // the backend leaves out an empty term list and a new view of the add form has no
            // terms yet, so a missing list is the normal "not used" state and not an error
            $trm_lst = $dbo->terms_related;
            if ($trm_lst == null or $trm_lst->is_empty()) {
                $result = $mtr->txt(msg_id::INFO_NOT_USED_BY_TERMS);
            } else {
                $result = $trm_lst->name_link([], $this->configured_name_list_limit($msg));
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to be a selection for terms', $msg);
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
     * @param array $url_arr the url of the shown page, to which the link form returns after the save
     * @return string the html code with the linked names of the assigned formulas
     */
    function formulas(
        word|phrase|db_object $wrd,
        user_message          $msg,
        ?data_object          $cac = null,
        bool                  $test_mode = false,
        array                 $url_arr = []
    ): string
    {
        global $ui_sys;

        // TODO Prio 3 on the word page this formula column should also show the most relevant
        //      results and, on top, a result chart (mirroring value_chart() for the value
        //      column); a result_chart component plus a word-carried results_related list are
        //      still missing

        // the page object is a word or a triple, but the formula lookup and the link form below
        // the list need the phrase of it
        if ($wrd::class == phrase::class) {
            $phr = $wrd;
        } else {
            $phr = $wrd->phrase();
        }
        // a word loaded for its page carries its related formulas directly (like the
        // related values), so use that list; otherwise fall back to the formula link
        // cache or, outside the unit tests, an api load
        if ($wrd::class == word::class and $wrd->frm_lst != null) {
            $frm_lst = $wrd->frm_lst;
        } else {
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
        $result = $frm_lst->name_link([], $row_limit);
        // the link icon below the list is the only way to assign a formula to the phrase,
        // so it is also shown if the phrase has no formula yet (like the reference list)
        $result .= $this->formula_link_form($phr, $msg, $cac, $test_mode, $url_arr);
        return $result;
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
     * @param array $url_array the url parameters of the page, so that the "... more" tail can
     *                         call the same page with the next list size
     * @param bool $col_values_only true to leave out the values that share no column phrase, so
     *                              that the table stays a grid of its columns (see value_list)
     * @param int $col_tiers the number of column tiers left out by default (see value_list)
     * @param bool $with_range true to show the probability ranges by default (see value_list)
     * @param bool $value_rows_only true to leave out the rows without a number (see value_list)
     * @param phrase_list|null $add_lst phrases that only the cells need, e.g. the list children
     *                                  that ", ..." counts; null if the cached phrases are enough
     * @param array $col_names the own column phrase names of the table, e.g. lifetime and annual
     *                         of a use case page; empty for the system column tiers
     * @return string the html code of the value table or '' if the phrase has no values
     */
    function table_with_related_columns(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto = null,
        bool                                   $with_header = false,
        bool                                   $with_border = true,
        array                                  $url_array = [],
        bool                                   $col_values_only = false,
        int                                    $col_tiers = value_list::COLUMN_TIERS_ALL,
        bool                                   $with_range = true,
        bool                                   $value_rows_only = false,
        ?phrase_list                           $add_lst = null,
        array                                  $col_names = []
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
            // a phrase whose rows are the phrases linked to it, e.g. "global warming reason", has
            // no value of its own, so the values of the linked phrases are loaded like on the start page
            if (($val_lst == null or $val_lst->is_empty()) and $dto != null and $dto->online) {
                $val_lst = $this->child_values($dbo->phrase(), $dto, $msg) ?? $val_lst;
            }
            // a phrase without any value shows no table at all instead of an empty header row
            if ($val_lst != null) {
                // a calculated number of a row is a result and not a value, e.g. the reward ratio
                // of a problem, and it is shown in the same table as the measured numbers; the
                // results are added to a copy, so that the value cache of the page stays unchanged
                if ($dto != null) {
                    $this->add_phrase_results($dto, $dbo->phrase(), $msg);
                }
                $tbl_lst = clone $val_lst;
                $tbl_lst->add_results(
                    $this->result_related_list($dbo, $dto, $dto?->phr_lst), $msg);
                $this->check_results($tbl_lst, $dto, $msg);
                $phr_lst = new phrase_list();
                $phr_lst->add_phrase($dbo->phrase());
                // the system column tiers decide which phrase heads a column and in which order;
                // an empty list falls back to the impact ranking of the values themselves
                if ($dto != null) {
                    $this->add_column_definitions($dto, $msg);
                    $this->add_sort_definitions($dto, $msg);
                }
                // the time of this message is mainly the merge of the definitions into the request cache
                log_debug('column and sort definitions added', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
                // own columns of the table win, so that they never show up in other tables
                $col_order = $col_names ?: ($dto?->phr_lst?->column_names() ?? []);
                log_debug('column order of ' . count($col_order) . ' columns', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
                // the "as" entry of the "..." menu says whether the rows are shown as a table,
                // as the default charts of the table or as both
                $form = table_forms::by_url($url_array);
                if ($form->with_table()) {
                    // the phrases for the cells only go into a copy taken after the definitions
                    // are loaded, so that the cache feeds no later page with them
                    $rel_lst = $dto?->phr_lst;
                    if ($add_lst != null and $rel_lst != null) {
                        $rel_lst = clone $rel_lst;
                        $rel_lst->merge($add_lst, $msg);
                    }
                    // the url of the page is handed over, so that the "... more" tail can call the
                    // same page with the next list size
                    $result = $tbl_lst->table_by_related_columns(
                        $msg, $phr_lst, $col_order, $with_header, $with_border, $rel_lst,
                        null, $url_array, $col_values_only, $col_tiers, $with_range,
                        $value_rows_only);
                    log_debug('table of ' . $tbl_lst->count() . ' numbers created', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
                } else {
                    // without the table its header says what the charts are about and its "..."
                    // menu is the only way back to the table, so the header is centred above the
                    // charts with the menu in the top right corner
                    $html = new html_base();
                    $head = '';
                    if ($with_header) {
                        $head = $tbl_lst->table_header_by_related_columns($msg, $phr_lst, $col_order,
                            $dto?->phr_lst, null, $url_array, $col_values_only, $value_rows_only);
                    }
                    $head .= $html->div($tbl_lst->columns_menu($url_array), styles::CHART_CORNER);
                    $result = $html->div($head, styles::CHART_HEAD);
                }
                if ($form->with_chart()) {
                    if ($dto != null) {
                        $this->add_chart_definitions($dto, $msg);
                    }
                    log_debug('chart definitions added', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
                    $result .= $this->table_charts($tbl_lst, $msg, $phr_lst, $col_order,
                        $dto?->phr_lst, $url_array, $col_values_only, $value_rows_only, $col_tiers);
                }
            }
        }
        return $result;
    }

    /**
     * the default charts of a value table beside each other, e.g. the range bars of the loss
     * and the scatter plot of the gain against the effort of the start page ranking, or the
     * mayor charts if the table shows the mayor columns only, e.g. the leverage plot of the
     * gain against the loss of the initial start page (phrase_list::chart_definitions)
     *
     * @param value_list $tbl_lst the values of the table
     * @param user_message $msg to report a chart that the table cannot draw
     * @param phrase_list $phr_lst the phrases of the page e.g. "global problem"
     * @param array $col_order the defined column phrase names, the most important column first
     * @param phrase_list|null $rel_lst the phrases related to the page phrase with the definitions
     * @param array $url_array the url parameters of the page that shows the charts
     * @param bool $col_values_only true to leave out the values that share no column phrase
     * @param bool $value_rows_only true to leave out the rows without a number in a shown column
     * @param int $col_tiers the column tiers left out by default, which the url of the page overrides
     * @return string the html code of the charts or '' if the table defines no chart
     */
    private function table_charts(
        value_list   $tbl_lst,
        user_message $msg,
        phrase_list  $phr_lst,
        array        $col_order,
        ?phrase_list $rel_lst,
        array        $url_array,
        bool         $col_values_only,
        bool         $value_rows_only,
        int          $col_tiers
    ): string
    {
        $html = new html_base();
        $charts = '';
        // the url names the column tiers like for the table (see value_list::table_model)
        $mayor_only = ((int)($url_array[url_var::DISPLAY_LIST_COLUMNS] ?? $col_tiers) == value_list::COLUMN_TIERS_EX_MAIN);
        $chart_defs = $rel_lst?->chart_definitions($msg, $mayor_only) ?? [];
        log_debug(count($chart_defs) . ' chart definitions selected', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
        // the table model is the same for every chart of the table and the slow part, so it is created once
        $model = null;
        if ($chart_defs != []) {
            $model = $tbl_lst->chart_model($msg, $phr_lst, $col_order, $rel_lst, null,
                $url_array, $col_values_only, $value_rows_only);
            log_debug('table model of the charts created', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
        }
        if ($model != null) {
            foreach ($chart_defs as [$type, $chart_cols]) {
                $charts .= value_list::model_to_svg($model, $type, $msg, $phr_lst, $chart_cols);
                log_debug('chart ' . $type->value . ' created', url_var::DEBUG_LEVEL_COMPLEX_FUNCTION);
            }
        }
        return ($charts == '') ? '' : $html->div($charts, styles::CHART_ROW);
    }

    /**
     * add the chart definitions of the tables to the request cache if they are not yet cached
     * (like add_column_definitions)
     *
     * @param data_object $dto the request cache that gets the definitions
     * @param user_message $msg to report a problem of the api call
     * @return void
     */
    private function add_chart_definitions(data_object $dto, user_message $msg): void
    {
        if ($dto->online and $dto->phr_lst->get_by_name(triples::SYSTEM_CHART_TYPE_DEFAULT, $msg) == null) {
            $chart_lst = new phrase_list();
            if ($chart_lst->load_chart_definitions($msg)) {
                $dto->add_phrases($chart_lst, $msg);
            }
        }
    }

    /**
     * add the default sort orders of the tables to the request cache if they are not yet cached
     * (like add_chart_definitions)
     *
     * @param data_object $dto the request cache that gets the definitions
     * @param user_message $msg to report a problem of the api call
     * @return void
     */
    private function add_sort_definitions(data_object $dto, user_message $msg): void
    {
        if ($dto->online and $dto->phr_lst->get_by_name(triples::SYSTEM_SORT_ORDER_DEFAULT, $msg) == null) {
            $sort_lst = new phrase_list();
            if ($sort_lst->load_sort_definitions($msg)) {
                $dto->add_phrases($sort_lst, $msg);
            }
        }
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
     * load the phrases linked to the given phrase into the request cache and their values
     *
     * @param phrase $phr the phrase of the page e.g. "global warming reason"
     * @param data_object $dto the request cache that gets the links
     * @param user_message $msg to report a problem of an api call
     * @return value_list|null the values of the linked phrases or null if the phrase has no linked phrase
     */
    private function child_values(phrase $phr, data_object $dto, user_message $msg): ?value_list
    {
        $result = null;
        if ($dto->phr_lst->child_phrases($phr)->is_empty()) {
            $child_lst = new phrase_list();
            if ($child_lst->load_related_by_name($phr->name(), foaf_direction::DOWN, $msg)) {
                $dto->add_phrases($child_lst, $msg);
            }
        }
        $children = $dto->phr_lst->child_phrases($phr);
        if (!$children->is_empty()) {
            $val_lst = new value_list();
            if ($val_lst->load_by_phr_lst($children, $msg)) {
                $result = $val_lst;
            }
        }
        return $result;
    }

    /**
     * add the results of the page phrase to the request cache, e.g. the production and the
     * avoided emission of a PV installation, so that the table shows the calculated numbers
     * next to the measured ones; the stored results are shown until the frontend calculates
     * them itself (docs/llm/frontend.md "The frontend calculates, a save job verifies")
     *
     * an offline cache is never filled from the api, like add_column_definitions, so a unit
     * test shows only the results it has cached upfront
     *
     * @param data_object $dto the request cache to fill
     * @param phrase $phr the phrase of the page
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_phrase_results(data_object $dto, phrase $phr, user_message $msg): void
    {
        // the cache can hold the results of another phrase e.g. of a table on the same page
        if ($dto->online and !$dto->has_results_of($phr->id())) {
            $phr_lst = new phrase_list();
            $phr_lst->add_phrase($phr);
            $res_lst = new result_list();
            if ($res_lst->load_by_phrase_list($phr_lst, $msg)) {
                $dto->add_results_of($phr->id(), $res_lst);
                $this->add_result_formulas($dto, $res_lst, $msg);
            }
        }
    }

    /**
     * add the formulas of the results to the request cache, so that the page can repeat the
     * calculation of the results (formula_calc); only the formulas the cache lacks are loaded
     *
     * @param data_object $dto the request cache to fill
     * @param result_list $res_lst the results of the page with the id of their formula
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_result_formulas(data_object $dto, result_list $res_lst, user_message $msg): void
    {
        $ids = [];
        foreach ($res_lst->lst() as $res) {
            $id = $res->frm?->id() ?? 0;
            if ($id != 0 and !$dto->frm_lst->has_id($id)) {
                $ids[$id] = $id;
            }
        }
        if ($ids != []) {
            $frm_lst = new formula_list();
            if ($frm_lst->load_by_ids(array_values($ids), $msg)) {
                $dto->frm_lst->merge($frm_lst, $msg);
            }
        }
    }

    /**
     * repeat the calculation of each result of a table with the values of the page and show the
     * calculated number in place of a stored number that differs, with a warning to the user, so
     * that a stale stored result is never shown silently (docs/llm/frontend.md "The frontend
     * calculates, a save job verifies"); a result whose formula or values the page does not have
     * keeps its stored number
     *
     * @param value_list $tbl_lst the values and results of the table
     * @param data_object|null $dto the data cache of the page with the formulas of the results
     * @param user_message $msg to report a stored result that differs from the calculation
     * @return void
     */
    private function check_results(value_list $tbl_lst, ?data_object $dto, user_message $msg): void
    {
        $calc = new formula_calc();
        $frm_lst = $dto?->formula_list() ?? new formula_list();
        foreach ($tbl_lst->lst() as $res) {
            if ($res instanceof result) {
                $num = $calc->result($res, $tbl_lst, $frm_lst);
                if ($num !== null and round($num, formula_calc::DECIMALS) != round($res->number(), formula_calc::DECIMALS)) {
                    $msg->add_warning_with_vars(msg_id::RESULT_DIFFERS_FROM_PAGE, [
                        msg_id::VAR_NAME => $res->name(),
                        msg_id::VAR_VALUE => $res->number(),
                        msg_id::VAR_VALUE_CHK => $num
                    ]);
                    $res->number = $num;
                }
            }
        }
    }

    /**
     * the values of the page phrase as number input fields, e.g. the inputs of a use case page: the
     * other phrases of the value are the label, so the user sees which number is asked for, and the
     * fields are sorted by the label, because the value list has no order of its own
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the value load
     * @param data_object|null $dto the data cache of the page with the values
     * @return string the html code of the input fields or an empty string if the phrase has no value
     */
    function value_input_fields(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto = null
    ): string
    {
        $html = new html_base();
        $fields = [];
        foreach ($this->phrase_values($dbo, $msg, $dto) as $val) {
            $label = implode(' ', array_diff($val->phr_lst()->names(), [$dbo->phrase()->name()]));
            $fields[$label . $val->id()] = $html->form_input(
                html_base::INPUT_NUMBER, url_var::NUMERIC_VALUE . $val->id(), (string)$val->number(), $label);
        }
        ksort($fields);
        return implode('', $fields);
    }

    /**
     * the input fields of a page (process_catch_fields.json): a phrase of the values of the page
     * phrase that is assigned a field type is a field of the page; a number field shows the number
     * of the page with the limits and the step that the values of the field phrase state, and a
     * phrase selector offers the phrases of the values of the field phrase with the one selected
     * that the page phrase uses; a number or phrase of the url wins, so the page shows the input
     * again after the refresh, and every input joins the refresh form by its form attribute
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object|null $dto the data cache of the page with the values and the field triples
     * @param string $form_name the name of the refresh form that the fields join
     * @param array $url_arr the url parameters of the page with the entered numbers and phrases
     * @return string the html code of the fields sorted by name or an empty string if the page has no field
     */
    function input_fields(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto,
        string                                 $form_name,
        array                                  $url_arr
    ): string
    {
        $fields = [];
        if ($dto != null and $dbo != null) {
            $this->add_field_definitions($dto, $msg);
            $field_phr = [];
            $field_val = [];
            $linked = [];
            foreach ($this->phrase_values($dbo, $msg, $dto) as $val) {
                foreach ($val->phr_lst()->lst() as $phr) {
                    $name = $phr->name();
                    if ($this->field_type($phr, $dto, $msg) == triples::FIELD_NUMBER_WITH_LIMITS) {
                        if (!in_array($name, $linked)) {
                            $this->add_field_links($phr, $dto, $msg);
                            $linked[] = $name;
                        }
                        if ($this->has_field_value_phrases($phr, $val, $dto, $msg)
                            and (!array_key_exists($name, $field_val)
                                or count($val->phr_lst()->lst()) < count($field_val[$name]->phr_lst()->lst()))) {
                            $field_phr[$name] = $phr;
                            $field_val[$name] = $val;
                        }
                    }
                }
            }
            foreach ($field_val as $name => $val) {
                $this->add_field_values($field_phr[$name], $dto, $msg);
                $fields[$name] = $this->number_field($field_phr[$name], $val, $dto, $form_name, $url_arr, $msg);
            }
            foreach ($this->selector_fields($dto, $msg) as $phr) {
                $this->add_field_values($phr, $dto, $msg);
                $field = $this->selector_field($phr, $dbo, $dto, $form_name, $url_arr, $msg);
                if ($field != '') {
                    $fields[$phr->name()] = $field;
                }
            }
        }
        ksort($fields);
        return implode('', $fields);
    }

    /**
     * add the field types and properties of process_catch_fields.json to the request cache, so
     * that the page knows which of its phrases are fields; an offline cache has them from its fixture
     *
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_field_definitions(data_object $dto, user_message $msg): void
    {
        if ($dto->online and $dto->phr_lst->get_by_name(triples::FIELD_NUMBER_WITH_LIMITS, $msg) == null) {
            $lst = new phrase_list();
            if ($lst->load_related_by_name(triples::SYSTEM_PROCESS_CATCH_FIELD, foaf_direction::BOTH, $msg, 3)) {
                $dto->add_phrases($lst, $msg);
            }
        }
    }

    /**
     * add the triples of a field phrase and the triples of these triples to the request cache,
     * so that the field value phrases are known e.g. "household consumption input period"
     * (household consumption has a annual) that "can be field value phrases"
     *
     * @param phrase $phr the field phrase e.g. "household consumption"
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_field_links(phrase $phr, data_object $dto, user_message $msg): void
    {
        if ($dto->online) {
            $lst = new phrase_list();
            if ($lst->load_related_by_name($phr->name(), foaf_direction::UP, $msg, 2)) {
                $dto->add_phrases($lst, $msg);
            }
        }
    }

    /**
     * add the values of a field phrase to the request cache, which state the limits and the step
     * of a number field or the phrases of a selector; loaded once per field phrase
     *
     * @param phrase $phr the field phrase e.g. "PV capacity"
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_field_values(phrase $phr, data_object $dto, user_message $msg): void
    {
        if ($dto->online and $dto->val_lst->get_by_names([$phr->name()]) == null) {
            $phr_lst = new phrase_list();
            $phr_lst->add_phrase($phr);
            $val_lst = new value_list();
            if ($val_lst->load_by_phr_lst($phr_lst, $msg)) {
                $dto->val_lst->merge($val_lst, $msg);
            }
        }
    }

    /**
     * @param phrase $phr a phrase of a value of the page
     * @param data_object $dto the request cache with the field triples
     * @param user_message $msg to report a problem of the cache
     * @return string the name of the field type that the phrase is assigned to, or '' if it is no field
     */
    private function field_type(phrase $phr, data_object $dto, user_message $msg): string
    {
        $result = '';
        $types = [triples::FIELD_NUMBER_WITH_LIMITS, triples::FIELD_PHRASE_SELECTOR_TYPE];
        foreach ($dto->phr_lst->children($phr, $msg)->lst() as $trp) {
            $to_name = $trp->obj()?->get_to()?->name() ?? '';
            if (in_array($to_name, $types)) {
                $result = $to_name;
            }
        }
        return $result;
    }

    /**
     * true if the value has all field value phrases of the field phrase, so that a field shows
     * the input value e.g. 4500 kWh annual and not a monthly value of the same field phrase;
     * a field value phrase is the "to" of a triple of the field phrase that "can be" a field value phrase
     *
     * @param phrase $phr the field phrase e.g. "household consumption"
     * @param sandbox_value $val a value of the page with the field phrase
     * @param data_object $dto the request cache with the field triples
     * @param user_message $msg to report a problem of the cache
     * @return bool true if the value has every field value phrase or if the field has none
     */
    private function has_field_value_phrases(phrase $phr, sandbox_value $val, data_object $dto, user_message $msg): bool
    {
        $result = true;
        foreach ($dto->phr_lst->children($phr, $msg)->lst() as $trp) {
            foreach ($dto->phr_lst->children($trp, $msg)->lst() as $prop) {
                $to_name = $prop->obj()?->get_to()?->name() ?? '';
                $val_phr = $trp->obj()?->get_to();
                if ($to_name == triples::FIELD_VALUE_PHRASES and $val_phr != null and !$val->has_phrase($val_phr, $msg)) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    /**
     * @param data_object $dto the request cache with the field triples
     * @param user_message $msg to report a problem of the cache
     * @return array the phrases that are assigned the phrase selector type e.g. "yield factor"
     */
    private function selector_fields(data_object $dto, user_message $msg): array
    {
        $result = [];
        $type = $dto->phr_lst->get_by_name(triples::FIELD_PHRASE_SELECTOR_TYPE, $msg);
        if ($type != null) {
            foreach ($dto->phr_lst->parents($type, $msg)->lst() as $phr) {
                $result[] = $phr;
            }
        }
        return $result;
    }

    /**
     * the number of a property of a field phrase, which a value with the field phrase and the
     * property phrase states e.g. 0.5 with "PV capacity" and "field step"
     *
     * @param phrase $phr the field phrase
     * @param string $prop_name the name of the property triple e.g. triples::FIELD_STEP
     * @param data_object $dto the request cache with the values of the field phrase
     * @return float|null the number or null if the field has no such property
     */
    private function field_property(phrase $phr, string $prop_name, data_object $dto): ?float
    {
        return $dto->val_lst->get_by_names([$phr->name(), $prop_name])?->number();
    }

    /**
     * a number field of the page with its label, the number of the page or of the url, the limits
     * and the step of the field and the unit of the number behind the input
     *
     * @param phrase $phr the field phrase e.g. "PV capacity"
     * @param sandbox_value $val the value of the page phrase with the field phrase
     * @param data_object $dto the request cache with the property values of the field
     * @param string $form_name the refresh form that the input joins
     * @param array $url_arr the url parameters with the entered number
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the field
     */
    private function number_field(
        phrase        $phr,
        sandbox_value $val,
        data_object   $dto,
        string        $form_name,
        array         $url_arr,
        user_message  $msg
    ): string
    {
        $html = new html_base();
        $name = url_var::NUMERIC_VALUE . $phr->id();
        $num = array_key_exists($name, $url_arr) ? (float)$url_arr[$name] : $val->number();
        $input = $html->input_number($name, $num,
            $this->field_property($phr, triples::FIELD_LIMIT_DOWN, $dto),
            $this->field_property($phr, triples::FIELD_LIMIT_UP, $dto),
            $this->field_property($phr, triples::FIELD_STEP, $dto), $form_name);
        $unit = $this->unit_name($val, $msg);
        if ($unit != '') {
            $input .= $html->span($unit, styles::INPUT_UNIT);
        }
        return $html->div($html->label($phr->name(), $name) . $input, styles::INPUT_FIELD);
    }

    /**
     * @param sandbox_value $val a value of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the symbol of the unit phrase of the value e.g. "kWh/a", else its name, or '' if the value has no unit
     */
    function unit_name(sandbox_value $val, user_message $msg): string
    {
        $result = '';
        foreach ($val->phr_lst()->lst() as $phr) {
            if ($result == '' and ($phr->is_measure($msg) or $phr->is_ratio_unit($msg))) {
                $result = $phr->unit_text($msg);
            } elseif ($result == '' and $phr->is_percent($msg)) {
                // not the symbol "%" of the percent word, because a field shows the stored ratio e.g. 0.35 and not 35
                $result = $phr->name();
            }
        }
        return $result;
    }

    /**
     * a phrase selector field of the page: the phrases of the values of the field phrase are the
     * options e.g. the orientations of the yield factor, and the one the page phrase uses or the
     * one of the url is selected; a selector none of whose options the page phrase uses is no
     * field of this page
     *
     * @param phrase $phr the field phrase e.g. "yield factor"
     * @param word|triple|db_object|type_object $dbo the phrase of the page
     * @param data_object $dto the request cache with the values of the field phrase and the triples of the page phrase
     * @param string $form_name the refresh form that the selector joins
     * @param array $url_arr the url parameters with the selected phrase
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the field or an empty string if the page phrase uses no option
     */
    private function selector_field(
        phrase                            $phr,
        word|triple|db_object|type_object $dbo,
        data_object                       $dto,
        string                            $form_name,
        array                             $url_arr,
        user_message                      $msg
    ): string
    {
        $result = '';
        $options = $this->selector_options($phr, $dto, $msg);
        $used = [];
        foreach ($dto->phr_lst->children($dbo->phrase(), $msg)->lst() as $trp) {
            $used[] = $trp->obj()?->get_to()?->id();
        }
        $selected = array_values(array_intersect(array_keys($options), $used))[0] ?? null;
        if ($selected !== null) {
            $html = new html_base();
            $sel = new html_selector();
            $sel->name = url_var::PHRASE . $phr->id();
            $sel->form = $form_name;
            $sel->lst = $options;
            $sel->selected = $url_arr[$sel->name] ?? $selected;
            $sel->with_label = false;
            // the label points to the id that the selector builds from its name and its label message
            $dom_id = $sel->name . '_' . strtolower($sel->label_id->name);
            $result = $html->div($html->label($phr->name(), $dom_id) . $sel->display(), styles::INPUT_FIELD);
        }
        return $result;
    }

    /**
     * @param phrase $phr the field phrase e.g. "yield factor"
     * @param data_object $dto the request cache with the values of the field phrase
     * @param user_message $msg to report a problem of the phrase types
     * @return array the option names by phrase id: the phrases of the values of the field phrase
     *               without the field phrase and the units, sorted by name
     */
    private function selector_options(phrase $phr, data_object $dto, user_message $msg): array
    {
        $result = [];
        foreach ($dto->val_lst->lst() as $val) {
            $names = $val->phr_lst()->names();
            if (in_array($phr->name(), $names)) {
                foreach ($val->phr_lst()->lst() as $opt) {
                    $is_unit = ($opt->is_measure($msg) or $opt->is_percent($msg) or $opt->is_scaling($msg));
                    if ($opt->id() != $phr->id() and !$is_unit) {
                        $result[$opt->id()] = $opt->name();
                    }
                }
            }
        }
        asort($result);
        return $result;
    }

    /**
     * the values that the calculation of the results of the page uses, e.g. for the assumptions
     * and the sources of a use case page; the results of the page and their formulas are loaded
     * if the cache lacks them, and so are the values of the context of each result
     *
     * @param word|triple|db_object|type_object $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object $dto the data cache of the page with the values, the results and the formulas
     * @return array the values and results that the results of the page use, each once
     */
    private function operand_values(
        word|triple|db_object|type_object $dbo,
        user_message                      $msg,
        data_object                       $dto
    ): array
    {
        $result = [];
        $this->add_phrase_results($dto, $dbo->phrase(), $msg);
        $calc = new formula_calc();
        // in a fixed order, because the order of the operands is the order of their sources on the page
        foreach ($this->result_related_list($dbo, $dto, $dto->phr_lst)->sorted_by_impact($msg) as $res) {
            $this->add_context_values($res, $dto, $msg);
            $operands = $calc->operands($res, $this->values_and_results($dto, $msg), $dto->formula_list());
            foreach ($operands as $val) {
                $result[$val->id()] = $val;
            }
        }
        return $result;
    }

    /**
     * complete the source of each value that knows its source only by the id (the value api
     * message carries the source id, not the name), from the source cache of the page or else
     * loaded from the backend once for all values, so that the page can show the source names
     *
     * @param array $vals the values of the page
     * @param data_object $dto the data cache of the page with the sources
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_sources(array $vals, data_object $dto, user_message $msg): void
    {
        $ids = [];
        foreach ($vals as $val) {
            $id = $this->source_id_without_name($val);
            if ($id != 0 and !$dto->src_lst->has_id($id)) {
                $ids[$id] = $id;
            }
        }
        if ($ids != [] and $dto->online) {
            $src_lst = new source_list();
            if ($src_lst->load_by_ids(array_values($ids), $msg)) {
                $dto->src_lst->merge($src_lst, $msg);
            }
        }
        foreach ($vals as $val) {
            $id = $this->source_id_without_name($val);
            if ($id != 0 and $dto->src_lst->has_id($id)) {
                $val->src = $dto->src_lst->get($id);
            }
        }
    }

    /**
     * @param value|result $val a value of the page
     * @return int the id of the source of the value if the value lacks the source name, else zero
     */
    private function source_id_without_name(value|result $val): int
    {
        $id = 0;
        if ($val instanceof value and $val->src != null and ($val->src->name() ?? '') == '') {
            $id = $val->src->id() ?? 0;
        }
        return $id;
    }

    /**
     * the notes of a page: the descriptions of the formulas that calculate the results of the
     * page, each as a paragraph with the formula name in front, sorted by the name, so the notes
     * are the formula descriptions of the use case file and not a text of their own
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object|null $dto the data cache of the page with the results and the formulas
     * @return string the html code of the notes or an empty string if no formula of the page has a description
     */
    function formula_notes(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto = null
    ): string
    {
        $html = new html_base();
        $notes = [];
        if ($dto != null and $dbo != null and ($dbo::class == word::class or $dbo::class == triple::class)) {
            $this->add_phrase_results($dto, $dbo->phrase(), $msg);
            foreach ($this->result_related_list($dbo, $dto, $dto->phr_lst)->lst() as $res) {
                $id = $res->frm?->id() ?? 0;
                if ($id != 0 and $dto->frm_lst->has_id($id)) {
                    $frm = $dto->frm_lst->get($id);
                    if ($frm->get_description() != '') {
                        $notes[$frm->name()] = $html->p($html->bold($frm->name()) . ' ' . $html->esc($frm->get_description()));
                    }
                }
            }
        }
        ksort($notes);
        return implode('', $notes);
    }

    /**
     * the assumptions of a page: the values that the calculation of the results of the page uses
     * and that the page phrase does not carry, e.g. the yield, the lifetime and the reference mixes
     * of the PV balance, each as a number field with the tag "measured" for a value with a source,
     * with the source as the hint, or "assumed" with the confidence that a value with the same
     * phrases and the confidence word states; an entered number of the url is shown again
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object|null $dto the data cache of the page with the values, the results and the formulas
     * @param string $form_name the name of the refresh form that the fields join
     * @param array $url_arr the url parameters of the page with the entered numbers
     * @param array $skip the names of the phrases whose values are shown already e.g. the
     *                    assumptions that the calculator lists
     * @return string the html code of the fields sorted by label or an empty string if the page has no assumption
     */
    function assumption_fields(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto,
        string                                 $form_name,
        array                                  $url_arr,
        array                                  $skip = []
    ): string
    {
        $fields = [];
        if ($dto != null and $dbo != null and ($dbo::class == word::class or $dbo::class == triple::class)) {
            $vals = $this->operand_values($dbo, $msg, $dto);
            $this->add_sources($vals, $dto, $msg);
            foreach ($vals as $val) {
                $names = $val->phr_lst()->names();
                if ($val instanceof value and !in_array($dbo->phrase()->name(), $names)
                    and array_intersect($skip, $names) == []) {
                    $label = $this->assumption_label($val, $msg);
                    $fields[$label . $val->id()] = $this->assumption_field($val, $label, $dto, $form_name, $url_arr, $msg);
                }
            }
        }
        ksort($fields);
        return implode('', $fields);
    }

    /**
     * add the values that a result may use to the request cache: the values of the phrases of
     * the context of the result, without the units, the times and the quality words, whose values
     * are too many to load and never an operand on their own; each context is loaded once
     *
     * @param result $res a result of the page with the phrases of the values used
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of the api message to the user
     * @return void
     */
    private function add_context_values(result $res, data_object $dto, user_message $msg): void
    {
        $ctx = $res->src_grp?->phr_lst();
        if ($dto->online and $ctx != null and !in_array($res->id(), $this->ctx_loaded)) {
            $this->ctx_loaded[] = $res->id();
            $phr_lst = new phrase_list();
            foreach ($ctx->lst() as $phr) {
                if (!$this->is_marker($phr, $msg)) {
                    $phr_lst->add_phrase($phr);
                }
            }
            $val_lst = new value_list();
            if (!$phr_lst->is_empty() and $val_lst->load_by_phr_lst($phr_lst, $msg)) {
                $dto->val_lst->merge($val_lst, $msg);
            }
        }
    }

    /**
     * @param data_object $dto the request cache of the page
     * @param user_message $msg to report a problem of the merge
     * @return value_list the values of the page followed by its results, as the calculation resolves its operands
     */
    private function values_and_results(data_object $dto, user_message $msg): value_list
    {
        $lst = clone $dto->val_lst;
        $lst->add_results($dto->res_lst, $msg);
        return $lst;
    }

    /**
     * @param phrase $phr a phrase of a value
     * @param user_message $msg to report a problem of the phrase types
     * @return bool true if the phrase is a unit, a scaling, a time or a quality word, which says
     *              how a number is stated but not what it measures
     */
    private function is_marker(phrase $phr, user_message $msg): bool
    {
        return $phr->is_measure($msg) or $phr->is_percent($msg) or $phr->is_scaling($msg)
            or $phr->is_time($msg) or $phr->is_quality($msg) or in_array($phr->name(), words::VALUE_MARKERS);
    }

    /**
     * @param value $val a value used by the results of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the label of the assumption: the phrases of the value that say what it measures
     */
    private function assumption_label(value $val, user_message $msg): string
    {
        $names = [];
        foreach ($val->phr_lst()->lst() as $phr) {
            if (!$this->is_marker($phr, $msg)) {
                $names[] = $phr->name();
            }
        }
        return implode(' ', $names);
    }

    /**
     * an assumption as a number field with the label, the measured or assumed tag, the unit of
     * the number and the source as the hint
     *
     * @param value $val the value used by a result of the page
     * @param string $label the label of the assumption
     * @param data_object $dto the request cache with the confidence values
     * @param string $form_name the refresh form that the input joins
     * @param array $url_arr the url parameters with the entered number
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the field
     */
    private function assumption_field(
        value        $val,
        string       $label,
        data_object  $dto,
        string       $form_name,
        array        $url_arr,
        user_message $msg
    ): string
    {
        $html = new html_base();
        $name = url_var::NUMERIC_VALUE . $val->id();
        $num = array_key_exists($name, $url_arr) ? (float)$url_arr[$name] : $val->number();
        $text = $label . ' ' . $html->span($this->assumption_tag($val, $dto), styles::INPUT_TAG);
        $input = $html->input_number($name, $num, null, null, null, $form_name);
        $unit = $this->unit_name($val, $msg);
        if ($unit != '') {
            $input .= $html->span($unit, styles::INPUT_UNIT);
        }
        $hint = $val->src?->name() ?? '';
        if ($hint != '') {
            $input .= $html->span($hint, styles::INPUT_HINT);
        }
        return $html->div($html->label($text, $name) . $input, styles::INPUT_FIELD);
    }

    /**
     * @param value $val the value used by a result of the page
     * @param data_object $dto the request cache with the confidence values
     * @return string "measured" for a value with a source, else "assumed" with the confidence if one is stated
     */
    private function assumption_tag(value $val, data_object $dto): string
    {
        global $mtr;
        $result = $mtr->txt(msg_id::FIELD_TAG_MEASURED);
        if ($val->source_id() == null) {
            $result = $mtr->txt(msg_id::FIELD_TAG_ASSUMED);
            $conf = $dto->val_lst->get_by_names([...$val->phr_lst()->names(), words::CONFIDENCE]);
            if ($conf != null) {
                $result .= ' ' . $conf->number();
            }
        }
        return $result;
    }

    /**
     * the fields of a calculator list (docs/llm/new_view_sample_pv_balance.md section 3.2): one
     * field per item in the order of the calculator, a phrase selector if the phrase of the item
     * is one, else a number field with the number of the item phrase; the label and the hint are
     * the calculator texts of the item or of its phrase (rule 6) and an assumption gets the
     * measured or assumed tag; an entered number of the url is shown again
     *
     * @param calculator $calc the calculator reader of the page
     * @param phrase[] $items the items of the list e.g. the inputs
     * @param word|triple $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object $dto the request cache with the field triples and the values
     * @param string $form_name the refresh form that the fields join
     * @param array $url_arr the url parameters with the entered numbers
     * @param bool $assumption true for the assumptions, which show the measured or assumed tag
     * @return string the html code of the fields
     */
    function calculator_fields(
        calculator   $calc,
        array        $items,
        word|triple  $dbo,
        user_message $msg,
        data_object  $dto,
        string       $form_name,
        array        $url_arr,
        bool         $assumption = false
    ): string
    {
        $result = '';
        // the numbers of the sources of the whole page, so that a hint names the same [n] as the source list
        $src_ids = $this->calculator_source_ids($calc, $dbo, $dto, $msg);
        foreach ($items as $item) {
            $phr = $calc->item_phrase($item);
            if ($phr != null) {
                if ($this->field_type($phr, $dto, $msg) == triples::FIELD_PHRASE_SELECTOR_TYPE) {
                    $result .= $this->calculator_selector($calc, $item, $phr, $dbo, $dto, $form_name, $url_arr, $msg);
                } else {
                    $result .= $this->calculator_number($calc, $item, $phr, $dbo, $dto, $form_name, $url_arr, $assumption, $src_ids, $msg);
                }
            }
        }
        return $result;
    }

    /**
     * a number field of a calculator with the input and its unit on one line
     *
     * @param calculator $calc the calculator reader of the page
     * @param phrase $item the item of the field e.g. PV installation input PV capacity
     * @param phrase $phr the phrase of the item e.g. PV capacity
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the values
     * @param string $form_name the refresh form that the input joins
     * @param array $url_arr the url parameters with the entered number
     * @param bool $assumption true to add the measured or assumed tag and the description of the value as the hint
     * @param array $src_ids the sources of the page in the order of their number
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the field
     */
    private function calculator_number(
        calculator   $calc,
        phrase       $item,
        phrase       $phr,
        word|triple  $dbo,
        data_object  $dto,
        string       $form_name,
        array        $url_arr,
        bool         $assumption,
        array        $src_ids,
        user_message $msg
    ): string
    {
        $html = new html_base();
        $name = url_var::NUMERIC_VALUE . $phr->id();
        $val = $this->calculator_value($phr, $dbo, $dto, $msg);
        $num = array_key_exists($name, $url_arr) ? (float)$url_arr[$name] : $val?->number();
        $text = $html->esc($calc->label($item));
        $hint = $calc->hint($item);
        if ($assumption and $val instanceof value) {
            $tag_style = $val->source_id() == null ? styles::INPUT_TAG_ASSUMED : styles::INPUT_TAG_MEASURED;
            $text .= ' ' . $html->span($this->assumption_tag($val, $dto), $tag_style);
            if ($hint == '') {
                $hint = $val->get_description();
            }
        }
        $input = $html->input_number($name, $num,
            $this->field_property($phr, triples::FIELD_LIMIT_DOWN, $dto),
            $this->field_property($phr, triples::FIELD_LIMIT_UP, $dto),
            $this->field_property($phr, triples::FIELD_STEP, $dto), $form_name,
            $val == null ? null : $this->field_decimals($val, $msg));
        // the save writes the entered number to the value that the field shows
        if ($val != null) {
            $input .= $html->form_hidden(url_var::GROUP . $phr->id(), (string)$val->id(), $form_name);
        }
        $unit = $val == null ? '' : $this->unit_name($val, $msg);
        if ($unit != '') {
            $input .= $html->span($unit, styles::INPUT_UNIT);
        }
        return $html->div($html->label($text, $name) . $html->div($input, styles::INPUT_ROW)
            . $this->calculator_hint($hint, $this->source_ref($val, $src_ids)), styles::INPUT_FIELD);
    }

    /**
     * @param sandbox_value $val the value that a field shows
     * @param user_message $msg to report a problem of the phrase types
     * @return int|null the minimum decimals of the number in the field: the percent decimals of the
     *                  user for a share e.g. 0.90, null for another number, which is shown as it is
     */
    function field_decimals(sandbox_value $val, user_message $msg): ?int
    {
        global $ui_sys;
        $result = null;
        if ($val->is_percent($msg)) {
            $result = $ui_sys?->cfg?->percent_decimals() ?? shared_config::DEFAULT_PERCENT_DECIMALS;
        }
        return $result;
    }

    /**
     * a phrase selector of a calculator: the options of the field phrase (calculator::options)
     * with the number of each option as the value e.g. 0.85 for east-west, so the entered number
     * is the input of the calculation, and the option that the page phrase uses is selected; the
     * hint names no source, because an option is chosen and not measured on the page
     *
     * @param calculator $calc the calculator reader of the page
     * @param phrase $item the item of the field e.g. PV installation input yield factor
     * @param phrase $phr the field phrase e.g. yield factor
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the values of the options
     * @param string $form_name the refresh form that the selector joins
     * @param array $url_arr the url parameters with the selected number
     * @param user_message $msg to report a problem of the cache
     * @return string the html code of the field
     */
    private function calculator_selector(
        calculator   $calc,
        phrase       $item,
        phrase       $phr,
        word|triple  $dbo,
        data_object  $dto,
        string       $form_name,
        array        $url_arr,
        user_message $msg
    ): string
    {
        $html = new html_base();
        $opts = $calc->options($phr);
        $used = $calc->used(array_column($opts, 0));
        $sel = new html_selector();
        $sel->name = url_var::NUMERIC_VALUE . $phr->id();
        $sel->form = $form_name;
        $sel->with_label = false;
        // the field gives the selector its layout like the row of a number field
        $sel->with_group = false;
        $sel->lst = [];
        foreach ($opts as [$opt, $opt_label]) {
            $val = $this->calculator_option_value($phr, $opt, $dbo, $dto, $msg);
            if ($val?->number() !== null) {
                $key = $html->number_value($val->number(), $this->field_decimals($val, $msg));
                $sel->lst[$key] = $opt_label;
                if ($used?->id() == $opt->id()) {
                    $sel->selected = $key;
                }
            }
        }
        $sel->selected = $url_arr[$sel->name] ?? $sel->selected;
        // the label points to the id that the selector builds from its name and its label message
        $dom_id = $sel->name . '_' . strtolower($sel->label_id->name);
        return $html->div($html->label($html->esc($calc->label($item)), $dom_id) . $sel->display()
            . $this->calculator_hint($calc->hint($item)), styles::INPUT_FIELD);
    }

    /**
     * @param phrase $phr the field phrase e.g. yield factor
     * @param phrase $opt an option of the field e.g. south
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the values of the options
     * @param user_message $msg to report a problem of the cache
     * @return sandbox_value|null the value of the field phrase for the option e.g. (yield factor,
     *                            south) = 1.00, else the value of the option itself e.g. (residual mix) = 523
     */
    private function calculator_option_value(
        phrase       $phr,
        phrase       $opt,
        word|triple  $dbo,
        data_object  $dto,
        user_message $msg
    ): ?sandbox_value
    {
        $names = [$phr->name(), $opt->name()];
        $matches = array_values(array_filter($dto->val_lst->lst(), fn($val) => $val->match_all($names)));
        // in the preferred order of numbers, because the backend returns the values in any order
        $result = sandbox_value::sorted($matches, $msg)[0] ?? null;
        return $result ?? $this->calculator_value($opt, $dbo, $dto, $msg);
    }

    /**
     * the sources of a calculator page in the order of their first use on the page: the values
     * of the number fields of the inputs and of the listed assumptions in their order, then the
     * other values that the results of the page use; a selector names no source, so the source
     * of its options is numbered by its next use
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the field triples, the values and the sources
     * @param user_message $msg to report a problem of the load
     * @return array the sources by id in the order of their number
     */
    private function calculator_source_ids(
        calculator   $calc,
        word|triple  $dbo,
        data_object  $dto,
        user_message $msg
    ): array
    {
        $this->add_field_definitions($dto, $msg);
        $vals = [];
        foreach ([...$calc->items(triples::CALCULATOR_INPUT), ...$calc->items(triples::CALCULATOR_ASSUMPTION)] as $item) {
            $phr = $calc->item_phrase($item);
            if ($phr != null) {
                $this->add_field_links($phr, $dto, $msg);
                $this->add_field_values($phr, $dto, $msg);
                if ($this->field_type($phr, $dto, $msg) != triples::FIELD_PHRASE_SELECTOR_TYPE) {
                    $vals[] = $this->calculator_value($phr, $dbo, $dto, $msg);
                }
            }
        }
        $vals = [...array_filter($vals), ...array_values($this->operand_values($dbo, $msg, $dto))];
        $this->add_sources($vals, $dto, $msg);
        $result = [];
        foreach ($vals as $val) {
            $id = $val instanceof value ? ($val->src?->id() ?? 0) : 0;
            if ($id != 0 and ($val->src->name() ?? '') != '' and !array_key_exists($id, $result)) {
                $result[$id] = $val->src;
            }
        }
        return $result;
    }

    /**
     * @param sandbox_value|null $val the value shown by a field
     * @param array $src_ids the sources of the page in the order of their number
     * @return string the number of the source of the value e.g. [1] or an empty string if it has none
     */
    private function source_ref(?sandbox_value $val, array $src_ids): string
    {
        $id = $val instanceof value ? ($val->src?->id() ?? 0) : 0;
        $pos = array_search($id, array_keys($src_ids));
        return ($id == 0 or $pos === false) ? '' : '[' . ($pos + 1) . ']';
    }

    /**
     * the value that a calculator field shows: a value of the item phrase with the field value
     * phrases, the one with the page phrase first, else the one with the fewest context phrases,
     * so the general value of a use case wins over the value of a place e.g. PV yield of Switzerland
     * over the PV yield of a municipality, and else the one with the fewest phrases, but never a
     * property of the field e.g. its step or the confidence of a value
     *
     * @param phrase $phr the phrase of the field e.g. PV capacity
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the values
     * @param user_message $msg to report a problem of the cache
     * @return sandbox_value|null the value or null if the cache has none
     */
    private function calculator_value(phrase $phr, word|triple $dbo, data_object $dto, user_message $msg): ?sandbox_value
    {
        $properties = [triples::FIELD_LIMIT_DOWN, triples::FIELD_LIMIT_UP, triples::FIELD_STEP, words::CONFIDENCE];
        $best_vals = [];
        $best = null;
        foreach ($dto->val_lst->lst() as $val) {
            $names = $val->phr_lst()->names();
            if ($val->has_phrase($phr, $msg) and array_intersect($names, $properties) == []
                and $this->has_field_value_phrases($phr, $val, $dto, $msg)) {
                $rank = [$val->has_phrase($dbo->phrase(), $msg) ? 0 : 1, $this->context_count($phr, $val, $msg), count($names)];
                if ($best == null or $rank < $best) {
                    $best_vals = [$val];
                    $best = $rank;
                } elseif ($rank == $best) {
                    $best_vals[] = $val;
                }
            }
        }
        // values that rank the same are picked by the preferred order of numbers and not by the
        // order of the list, which the backend returns in any order (docs/llm/frontend.md)
        return sandbox_value::sorted($best_vals, $msg)[0] ?? null;
    }

    /**
     * @param phrase $phr the phrase of the field e.g. PV yield
     * @param sandbox_value $val a value of the field phrase
     * @param user_message $msg to report a problem of the phrase types
     * @return int the number of phrases of the value that narrow its context e.g. a municipality,
     *             so not the field phrase and no unit, percent, scaling or time phrase
     */
    private function context_count(phrase $phr, sandbox_value $val, user_message $msg): int
    {
        $result = 0;
        foreach ($val->phr_lst()->lst() as $val_phr) {
            if ($val_phr->id() != $phr->id() and !$val_phr->is_measure($msg) and !$val_phr->is_percent($msg)
                and !$val_phr->is_scaling($msg) and !$val_phr->is_time($msg)) {
                $result++;
            }
        }
        return $result;
    }

    /**
     * @param string $hint the hint of a calculator field
     * @param string $ref the number of the source of the shown value e.g. [1]
     * @return string the html code of the hint below the field or an empty string if there is none
     */
    private function calculator_hint(string $hint, string $ref = ''): string
    {
        $html = new html_base();
        $txt = trim($html->esc($hint) . ' ' . $ref);
        return $txt == '' ? '' : $html->span($txt, styles::INPUT_HINT);
    }

    /**
     * the main table of a calculation result (docs/llm/new_view_sample_pv_balance.md section
     * 3.2): one column per result column, one section row per row group and one row per item of
     * the group in their order, with the label, the hint or else the side results as sub text,
     * the key row style of a highlight and the sign of the numbers of a signed group; a cell is
     * the result or value with the page phrase, the row phrase, its filter phrase and the column phrase
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param value_list $lst the values and the results of the page (calculator_numbers)
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the table or an empty string if the calculator has no columns and no rows
     */
    function calculation_table(calculator $calc, word|triple $dbo, value_list $lst, user_message $msg): string
    {
        global $mtr;
        $html = new html_base();
        $result = '';
        $cols = $calc->items(triples::CALCULATOR_RESULT_COLUMN);
        $grps = $calc->items(triples::CALCULATOR_ROW_GROUP);
        if ($cols != [] or $grps != []) {
            $head = $html->block(html_base::TH, $html->esc($mtr->txt(msg_id::CALCULATOR_RESULT)));
            foreach ($cols as $col) {
                $head .= $html->block(html_base::TH, $html->esc($calc->label($col)), styles::CALC_NUMBER);
            }
            $rows = '';
            foreach ($grps as $grp) {
                $sec = '<' . html_base::TD . ' colspan="' . (count($cols) + 1) . '">'
                    . $html->esc($calc->label($grp)) . '</' . html_base::TD . '>';
                $rows .= $html->block(html_base::TR, $sec, styles::CALC_SECTION);
                $signed = $calc->has_flag($grp, triples::CALCULATOR_SIGNED);
                foreach ($calc->sub_items($grp, verbs::HAS_ID) as $row) {
                    $ctx = [$dbo->phrase(), $calc->item_phrase($row), $calc->filter($row)];
                    $cells = '';
                    foreach ($cols as $col) {
                        $val = $this->calculator_result([...$ctx, $calc->item_phrase($col)], $lst, $msg);
                        $cells .= $this->calculator_cell($val, $signed, true, $msg);
                    }
                    $first = $html->esc($calc->label($row, $grp)) . $this->calculator_sub($calc, $row, $ctx, $lst, $msg);
                    $rows .= $this->calculator_row($first, $cells, $calc->has_flag($row, triples::CALCULATOR_HIGHLIGHT));
                }
            }
            $result = $this->calculator_table($head, $rows);
        }
        return $result;
    }

    /**
     * the detail tables of a calculation result: per table its label as the heading, the table
     * phrase as the first header, one column per sub-item, one row per phrase that is a table
     * phrase in their order e.g. January to December and an extra row per "with" sub-item e.g.
     * the year; a cell is the result or value with the page phrase, the row and the column phrase
     * and the "with" filter of the column, e.g. the unit gram per kWh
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param value_list $lst the values and the results of the page (calculator_numbers)
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the tables or an empty string if the calculator has none
     */
    function calculation_detail_tables(calculator $calc, word|triple $dbo, value_list $lst, user_message $msg): string
    {
        $html = new html_base();
        $result = '';
        $tbls = $calc->items(triples::CALCULATOR_DETAIL_TABLE);
        if ($tbls != []) {
            foreach ($tbls as $tbl) {
                $tbl_phr = $calc->item_phrase($tbl);
                if ($tbl_phr != null) {
                    $cols = $calc->sub_items($tbl, verbs::HAS_ID);
                    $head = $html->block(html_base::TH, $html->esc($calc->phrase_label($tbl_phr)));
                    foreach ($cols as $col) {
                        $head .= $html->block(html_base::TH, $html->esc($calc->label($col)), styles::CALC_NUMBER);
                    }
                    $rows = '';
                    foreach ($calc->kinds($tbl_phr) as $row_phr) {
                        $rows .= $this->calculator_detail_row($calc, $calc->phrase_label($row_phr),
                            [$dbo->phrase(), $row_phr], $cols, false, $lst, $msg);
                    }
                    foreach ($calc->sub_items($tbl, verbs::WITH_ID) as $extra) {
                        $rows .= $this->calculator_detail_row($calc, $calc->label($extra),
                            [$dbo->phrase(), $calc->item_phrase($extra)], $cols,
                            $calc->has_flag($extra, triples::CALCULATOR_HIGHLIGHT), $lst, $msg);
                    }
                    $result .= $html->block(html_base::H2, $html->esc($calc->label($tbl)))
                        . $this->calculator_table($head, $rows);
                }
            }
        }
        return $result;
    }

    /**
     * the sources of a calculator page as a numbered list in the order of their first use, so
     * that the [n] of a hint names the entry of the list
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param user_message $msg to report a problem of the load
     * @param data_object $dto the request cache with the values and the sources of the page
     * @return string the html code of the sources with the heading or an empty string if no value has a source
     */
    function calculator_sources(calculator $calc, word|triple $dbo, user_message $msg, data_object $dto): string
    {
        global $mtr;
        $html = new html_base();
        $items = '';
        foreach ($this->calculator_source_ids($calc, $dbo, $dto, $msg) as $src) {
            $name = $html->esc($src->name());
            $items .= $html->list_item($src->url() == null ? $name : $html->ref($src->url(), $name));
        }
        $result = '';
        if ($items != '') {
            $result = $html->div($html->block(html_base::H2, $html->esc($mtr->txt(msg_id::CALCULATOR_SOURCES)))
                . $html->block(html_base::OL, $items), styles::CALC_SOURCES);
        }
        return $result;
    }

    /**
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache of the page
     * @param user_message $msg to report a problem of the load or a stored result that differs
     * @return value_list the values and the results of the page, each result checked once by the
     *                    calculation of the page, so that a stored result that differs is reported once
     */
    function calculator_numbers(word|triple $dbo, data_object $dto, user_message $msg): value_list
    {
        $this->add_phrase_results($dto, $dbo->phrase(), $msg);
        $lst = $this->values_and_results($dto, $msg);
        $this->check_results($lst, $dto, $msg);
        return $lst;
    }

    /**
     * the number of a calculator cell: the value or result with all the phrases of the cell and
     * the fewest other phrases; if the phrases select two different numbers equally, the shown
     * number would depend on the order of the list, so the cell stays empty and the user is told
     *
     * @param array $phrases the phrases that the number must have; null entries are ignored
     * @param value_list $lst the values and results of the page
     * @param user_message $msg to report a problem of the phrase compare or an ambiguous cell
     * @return sandbox_value|null the value or result of the cell or null if the phrases select none or not one number
     */
    function calculator_result(array $phrases, value_list $lst, user_message $msg): ?sandbox_value
    {
        $phrases = array_filter($phrases);
        $best = [];
        foreach ($lst->lst() as $val) {
            if ($this->has_all_phrases($val, $phrases, $msg)) {
                $count = count($val->phr_lst()->names());
                $best_count = count(($best[0] ?? $val)->phr_lst()->names());
                if ($best == [] or $count < $best_count) {
                    $best = [$val];
                } elseif ($count == $best_count) {
                    $best[] = $val;
                }
            }
        }
        $result = $best[0] ?? null;
        if ($result != null and !$this->is_one_number($best)) {
            $msg->add_warning_with_vars(msg_id::CALCULATOR_CELL_AMBIGUOUS, [
                msg_id::VAR_NAME_LIST => implode(', ', array_map(fn($phr) => $phr->name(), $phrases)),
                msg_id::VAR_VALUE_LIST => implode(', ', array_map(fn($val) => $val->number(), $best))]);
            $result = null;
        }
        return $result;
    }

    /**
     * @param sandbox_value $val a value or result of the page
     * @param array $phrases the phrases of a calculator cell
     * @param user_message $msg to report a problem of the phrase compare
     * @return bool true if the number has all the phrases of the cell
     */
    private function has_all_phrases(sandbox_value $val, array $phrases, user_message $msg): bool
    {
        $result = true;
        foreach ($phrases as $phr) {
            if (!$val->has_phrase($phr, $msg)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param array $vals the values and results that a calculator cell selects equally
     * @return bool true if they all have the same number, so the cell shows the same number whichever is taken
     */
    private function is_one_number(array $vals): bool
    {
        $numbers = array_map(fn($val) => $val->number(), $vals);
        return count(array_unique($numbers, SORT_REGULAR)) == 1;
    }

    /**
     * @param sandbox_value $val a number of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the formatted number with its unit e.g. 248'880 kWh
     */
    private function calculator_number_text(sandbox_value $val, user_message $msg): string
    {
        $txt = $val->val_formatted($msg);
        // a percent is formatted with its symbol already
        $unit = $val->is_percent($msg) ? '' : $this->unit_name($val, $msg);
        return $unit == '' ? $txt : $txt . ' ' . $unit;
    }

    /**
     * @param sandbox_value|null $val the number of the cell or null if the page has none
     * @param bool $signed true to show the sign of the number as the positive or negative style
     * @param bool $with_unit true to add the unit e.g. in the main table, but not in a detail table whose header names the unit
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the number cell, empty if the page has no number
     */
    private function calculator_cell(?sandbox_value $val, bool $signed, bool $with_unit, user_message $msg): string
    {
        $html = new html_base();
        $txt = '';
        $id = '';
        if ($val != null and !$val->is_null()) {
            $txt = $html->esc($with_unit ? $this->calculator_number_text($val, $msg) : $val->val_formatted($msg));
            if ($signed) {
                $txt = $html->span($txt, $val->number() < 0 ? styles::CALC_NEGATIVE : styles::CALC_POSITIVE);
            }
            $id = $this->calculator_cell_id($val);
            $this->cell_plan[$id] = $this->calculator_cell_format($val, $signed, $with_unit, $msg);
        }
        return $html->block(html_base::TD, $txt, styles::CALC_NUMBER, $id);
    }

    /**
     * the id of a number cell, so that the save can compare the results of the page by their
     * group id and the browser can update the cell; an id is unique on a page, so a number that
     * a detail table shows again gets its count behind the id
     *
     * @param sandbox_value $val the value or result of the cell
     * @return string the html id e.g. "res-5-0M-06+" or "res-5-0M-06+-2" for the second cell of the number
     */
    private function calculator_cell_id(sandbox_value $val): string
    {
        $result = $val->html_id();
        $count = ($this->cell_counts[$result] ?? 0) + 1;
        $this->cell_counts[$val->html_id()] = $count;
        if ($count > 1) {
            $result .= self::CALC_CELL_REPEAT . $count;
        }
        return $result;
    }

    /**
     * @param sandbox_value $val the value or result of a cell
     * @param bool $signed true if the cell shows the sign of the number as the positive or negative style
     * @param bool $with_unit true if the cell shows the unit behind the number
     * @param user_message $msg to report a problem of the phrase types
     * @return array how the browser writes a new number into the cell like calculator_cell does
     */
    private function calculator_cell_format(sandbox_value $val, bool $signed, bool $with_unit, user_message $msg): array
    {
        $is_percent = $val->is_percent($msg);
        $unit = '';
        if ($with_unit and !$is_percent) {
            $unit = $this->unit_name($val, $msg);
        }
        $percent = null;
        if ($is_percent) {
            $percent = $val->percent_symbol($msg);
        }
        return [
            self::CALC_CELL_NUMBER => $val->html_id(),
            self::CALC_CELL_UNIT => $unit,
            self::CALC_CELL_PERCENT => $percent,
            self::CALC_CELL_SIGNED => $signed,
        ];
    }

    /**
     * the calculation of a calculator page as data and the script that repeats it in the browser
     * when an input changes (docs/llm/frontend.md "Javascript only for the calculator"): the
     * numbers of the page by their html id, the results as tokens of their formula, the number
     * that each input field and each cell shows and the number format of the user; called after
     * the tables of the page, because the cells are known only then
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param value_list $lst the values and the results of the page (calculator_numbers)
     * @param data_object $dto the request cache with the formulas and the field triples
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the data and of the script
     */
    function calculator_script(calculator $calc, word|triple $dbo, value_list $lst, data_object $dto, user_message $msg): string
    {
        global $ui_sys;
        $html = new html_base();
        $plan = [
            self::CALC_NUMBERS => $this->calculator_number_plan($lst),
            self::CALC_RESULTS => $this->calculator_result_plan($lst, $dto),
            self::CALC_INPUTS => $this->calculator_input_plan($calc, $dbo, $dto, $msg),
            self::CALC_CELLS => $this->cell_plan,
            self::CALC_FORMAT => [
                // without a loaded config the format of the page cells, the shared defaults
                self::CALC_THOUSAND_SEP => $ui_sys?->cfg?->thousand_sep() ?? shared_config::DEFAULT_THOUSAND_SEP,
                self::CALC_PERCENT_DECIMALS => $ui_sys?->cfg?->percent_decimals() ?? shared_config::DEFAULT_PERCENT_DECIMALS,
                self::CALC_POSITIVE => styles::CALC_POSITIVE,
                self::CALC_NEGATIVE => styles::CALC_NEGATIVE,
            ],
        ];
        return $html->script_data(self::CALC_DATA_ID, $plan) . $html->script_src(files::CALCULATOR_SCRIPT);
    }

    /**
     * @param value_list $lst the values and the results of the page
     * @return array per html id the number of the value or result, without a value that has no number
     */
    private function calculator_number_plan(value_list $lst): array
    {
        $result = [];
        foreach ($lst->lst() as $val) {
            if ($val->number() !== null) {
                $result[$val->html_id()] = $val->number();
            }
        }
        // sorted by the html id, so that the page does not depend on the load order of the values
        ksort($result);
        return $result;
    }

    /**
     * @param value_list $lst the values and the results of the page
     * @param data_object $dto the request cache with the formulas of the results
     * @return array per html id of a result the tokens of its calculation, without a result that the page cannot calculate
     */
    private function calculator_result_plan(value_list $lst, data_object $dto): array
    {
        $calc = new formula_calc();
        $result = [];
        foreach ($lst->lst() as $res) {
            if ($res instanceof result) {
                $tokens = $calc->tokens($res, $lst, $dto->formula_list());
                if ($tokens !== null) {
                    $result[$res->html_id()] = $tokens;
                }
            }
        }
        // sorted by the html id, so that the page does not depend on the load order of the results
        ksort($result);
        return $result;
    }

    /**
     * the number that each input of the page sets: a number field the value that it shows, a
     * phrase selector the value of the option that the calculation of the page uses, and an
     * assumption that a result uses the value itself (see assumption_fields)
     *
     * @param calculator $calc the calculator reader of the page
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the field triples and the values
     * @param user_message $msg to report a problem of the phrase types
     * @return array per name of an input field the html id of its number
     */
    private function calculator_input_plan(calculator $calc, word|triple $dbo, data_object $dto, user_message $msg): array
    {
        $result = [];
        foreach ($this->operand_values($dbo, $msg, $dto) as $val) {
            if ($val instanceof value) {
                $result[url_var::NUMERIC_VALUE . $val->id()] = $val->html_id();
            }
        }
        foreach ([...$calc->items(triples::CALCULATOR_INPUT), ...$calc->items(triples::CALCULATOR_ASSUMPTION)] as $item) {
            $phr = $calc->item_phrase($item);
            $val = null;
            if ($phr != null) {
                $val = $this->calculator_field_value($calc, $phr, $dbo, $dto, $msg);
            }
            if ($val != null) {
                $result[url_var::NUMERIC_VALUE . $phr->id()] = $val->html_id();
            }
        }
        return $result;
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param phrase $phr the field phrase e.g. yield factor
     * @param word|triple $dbo the phrase of the page
     * @param data_object $dto the request cache with the field triples and the values
     * @param user_message $msg to report a problem of the phrase types
     * @return sandbox_value|null the value that the field sets: the shown value of a number field or
     *                            the value of the option that the page phrase uses of a phrase selector
     */
    private function calculator_field_value(calculator $calc, phrase $phr, word|triple $dbo, data_object $dto, user_message $msg): ?sandbox_value
    {
        $result = null;
        if ($this->field_type($phr, $dto, $msg) == triples::FIELD_PHRASE_SELECTOR_TYPE) {
            $used = $calc->used(array_column($calc->options($phr), 0));
            if ($used != null) {
                $result = $this->calculator_option_value($phr, $used, $dbo, $dto, $msg);
            }
        } else {
            $result = $this->calculator_value($phr, $dbo, $dto, $msg);
        }
        return $result;
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param phrase $row a row of the main table
     * @param array $ctx the phrases of the row: the page phrase, the row phrase and the filter phrase
     * @param value_list $lst the values and results of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the sub text: the hint of the row, else its side results with their number
     */
    private function calculator_sub(calculator $calc, phrase $row, array $ctx, value_list $lst, user_message $msg): string
    {
        $html = new html_base();
        $sub = $calc->hint($row);
        if ($sub == '') {
            $parts = [];
            foreach ($calc->side_results($row) as $side) {
                $val = $this->calculator_result([...$ctx, $side], $lst, $msg);
                if ($val != null and !$val->is_null()) {
                    $parts[] = $calc->phrase_label($side) . ' ' . $this->calculator_number_text($val, $msg);
                }
            }
            $sub = implode(', ', $parts);
        }
        return $sub == '' ? '' : $html->span($html->esc($sub), styles::CALC_SUB);
    }

    /**
     * @param calculator $calc the calculator reader of the page
     * @param string $label the label of the row
     * @param array $ctx the phrases of the row: the page phrase and the row phrase
     * @param phrase[] $cols the column items of the detail table
     * @param bool $key true for a key row e.g. the year
     * @param value_list $lst the values and results of the page
     * @param user_message $msg to report a problem of the phrase types
     * @return string the html code of the row of a detail table
     */
    private function calculator_detail_row(
        calculator   $calc,
        string       $label,
        array        $ctx,
        array        $cols,
        bool         $key,
        value_list   $lst,
        user_message $msg
    ): string
    {
        $cells = '';
        foreach ($cols as $col) {
            // the filter of a column e.g. the unit gram per kWh selects its numbers like the filter of a row
            $val = $this->calculator_result([...$ctx, $calc->item_phrase($col), $calc->filter($col)], $lst, $msg);
            $cells .= $this->calculator_cell($val, false, false, $msg);
        }
        return $this->calculator_row(new html_base()->esc($label), $cells, $key);
    }

    /**
     * @param string $first the html code of the first cell e.g. the label with the sub text
     * @param string $cells the html code of the number cells
     * @param bool $key true to show the row as a key row
     * @return string the html code of the table row
     */
    private function calculator_row(string $first, string $cells, bool $key): string
    {
        $html = new html_base();
        return $html->block(html_base::TR, $html->block(html_base::TD, $first) . $cells,
            $key ? styles::CALC_KEY : '');
    }

    /**
     * @param string $head the html code of the header cells
     * @param string $rows the html code of the rows
     * @return string the html code of the table in the frame that scrolls a wide table on a phone
     */
    private function calculator_table(string $head, string $rows): string
    {
        $html = new html_base();
        return $html->div($html->block(html_base::TABLE,
            $html->block(html_base::THEAD, $html->block(html_base::TR, $head))
            . $html->block(html_base::TBODY, $rows)), styles::CALC_SCROLL);
    }

    /**
     * the sources of the values of the page phrase as a list with a link to the source url, e.g. the
     * sources of a use case page; each source once, sorted by name
     *
     * @param word|triple|db_object|type_object|null $dbo the phrase of the page
     * @param user_message $msg to report a problem of the value load
     * @param data_object|null $dto the data cache of the page with the values
     * @return string the html code of the source list or an empty string if no value has a source
     */
    function sources_used(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto = null
    ): string
    {
        $html = new html_base();
        $vals = $this->phrase_values($dbo, $msg, $dto);
        if ($dto != null and $vals != []) {
            $vals = array_merge($vals, $this->operand_values($dbo, $msg, $dto));
            $this->add_sources($vals, $dto, $msg);
        }
        $src_lst = [];
        foreach ($vals as $val) {
            // only a value has a source, a result has the formula instead
            if ($val instanceof value and $val->src != null and ($val->src->name() ?? '') != '') {
                $src_lst[$val->src->name()] = $val->src;
            }
        }
        ksort($src_lst);
        $items = '';
        foreach ($src_lst as $src) {
            $link = $src->url() == null ? $src->name() : $html->ref($src->url(), $src->name());
            $items .= $html->list_item($link);
        }
        $result = '';
        if ($items != '') {
            $result = $html->list_unsorted($items);
        }
        return $result;
    }

    /**
     * @param word|triple|db_object|type_object|null $dbo the object of the page
     * @param user_message $msg to report a problem of the value load
     * @param data_object|null $dto the data cache of the page with the values
     * @return array the values of the page phrase, which only a word or a triple has
     */
    private function phrase_values(
        word|triple|db_object|type_object|null $dbo,
        user_message                           $msg,
        ?data_object                           $dto
    ): array
    {
        $result = [];
        if ($dbo != null and ($dbo::class == word::class or $dbo::class == triple::class)) {
            $result = $this->value_related_list($dbo, $msg, $dto, $dto?->phr_lst)?->lst() ?? [];
        }
        return $result;
    }

    /**
     * the results shown by a value table, e.g. the reward ratio of each global problem
     *
     * a number of a child phrase belongs to the page of the parent too (like in
     * value_list::filter), e.g. a "global warming" result is shown on the "global problem"
     * page, so a result is taken if its group names the page phrase or one of its children
     *
     * @param word|triple|db_object|type_object|null $dbo the object the results are related to
     * @param data_object|null $dto the data cache that carries the results of the page
     * @param phrase_list|null $ctx_lst the phrases of the page cache with their links
     * @return result_list the results to show with the values of the given object
     */
    private function result_related_list(
        word|triple|db_object|type_object|null $dbo,
        ?data_object                           $dto,
        ?phrase_list                           $ctx_lst = null
    ): result_list
    {
        $res_lst = new result_list();
        $row_names = $ctx_lst?->child_names($dbo->phrase()) ?? [];
        $row_names[] = $dbo->phrase()->name();
        foreach ($dto?->res_lst?->lst() ?? [] as $res) {
            if (array_intersect($row_names, $res->grp->phr_lst()->names()) != []) {
                $res_lst->add_result($res);
            }
        }
        return $res_lst;
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
     * the values of the same category as the given value grouped by their phrases like the
     * default word view, used by the related values column of the value default page: for pi
     * these are the other values of the mathematical constants
     *
     * the list is sorted by impact by the grouped renderer (see value_list::list_most_relevant),
     * so the most relevant similar value is shown first
     *
     * @param value|db_object|null $dbo the value whose similar values should be listed
     * @param user_message $msg to report an unexpected selection object
     * @param data_object|null $dto the data cache used if the value has not been loaded for its page
     * @param int|null $style_id the optional list column style
     * @return string the similar values or the message that the value has no similar values
     */
    function values_similar(
        value|db_object|null $dbo,
        user_message         $msg,
        ?data_object         $dto = null,
        ?int                 $style_id = null
    ): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the value is missing to select the similar values', $msg);
        } elseif ($dbo::class == value::class) {
            // a value loaded for its page carries its similar values (see load_values_similar),
            // whereas a value built from an url carries none, so the values of the page cache
            // that share a phrase with it are used (see value_list::filter)
            $val_lst = $dbo->values_similar;
            if ($val_lst == null and $dto?->val_lst != null) {
                $val_lst = $dto->val_lst->filter($msg, $dbo);
            }
            // without the preloaded values the list cannot be created, and showing the none-found
            // message would tell the user that the value has no similar values, which is not known here
            if ($val_lst == null) {
                log_err_msg_ui('the value cache is missing to select the similar values', $msg);
            } else {
                // the value itself is the context of the column, so its phrases are left out
                $result = $this->value_list($val_lst, $dbo->grp->phr_lst(), $msg, $style_id, true);
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NO_SIMILAR_VALUES);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to have similar values', $msg);
        }
        return $result;
    }

    /**
     * the results that use the given value grouped like the values of a phrase, used by the
     * results column of the value default page e.g. the increase that has been calculated from
     * this value
     *
     * @param value|db_object|null $dbo the value whose results should be listed
     * @param user_message $msg to report an unexpected selection object
     * @param data_object|null $dto the data cache used if the value has not been loaded for its page
     * @return string the results of the value or the message that the value is not used for results
     */
    function results_by_value(value|db_object|null $dbo, user_message $msg, ?data_object $dto = null): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the value is missing to select the results', $msg);
        } elseif ($dbo::class == value::class) {
            // like the similar values the results are loaded only for the value page, so a value
            // built from an url falls back to the results of the page cache that are based on
            // all phrases of the value (see result_list::filter)
            $res_lst = $dbo->results_related;
            if ($res_lst == null and $dto?->res_lst != null) {
                $res_lst = $dto->res_lst->filter($msg, $dbo);
            }
            if ($res_lst == null) {
                log_err_msg_ui('the result cache is missing to select the results', $msg);
            } else {
                // the value itself is the context of the column, so its phrases are left out
                $result = $this->result_list_grouped($res_lst, $dbo->grp->phr_lst(), $msg);
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NOT_USED_FOR_RESULTS);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to have results', $msg);
        }
        return $result;
    }

    /**
     * the values used to calculate the given result grouped by their phrases like the default word
     * view, used by the values column of the result default page e.g. the inhabitants of a country
     * that an increase has been calculated from
     *
     * @param result|db_object|null $dbo the result whose used values should be listed
     * @param user_message $msg to report a missing list or an unexpected selection object
     * @param int|null $style_id the optional list column style
     * @return string the used values or the message that no value has been used
     */
    function values_used(result|db_object|null $dbo, user_message $msg, ?int $style_id = null): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the result is missing to select the used values', $msg);
        } elseif ($dbo::class == result::class) {
            // only a page request loads the used values (see result::load_values_used), so a result
            // built from an url shows no column instead of the none-used message, which would tell
            // the user that no value has been used, which is not known here (like the formula
            // column of a word, see formula_list)
            if ($dbo->values_used != null) {
                // the result itself is the context of the column, so its phrases are left out
                $result = $this->value_list($dbo->values_used, $dbo->grp->phr_lst(), $msg, $style_id, true);
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NO_VALUES_USED);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to use values for a calculation', $msg);
        }
        return $result;
    }

    /**
     * the formulas that have calculated the results used for the given result, used by the formulas
     * column of the result default page; the formula of the result itself is named in the title
     *
     * @param result|db_object|null $dbo the result whose used formulas should be listed
     * @param user_message $msg to report a missing list or an unexpected selection object
     * @return string the linked formula names or the message that no other formula has been used
     */
    function formulas_used(result|db_object|null $dbo, user_message $msg): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the result is missing to select the used formulas', $msg);
        } elseif ($dbo::class == result::class) {
            // like the used values only a page request loads the used formulas
            if ($dbo->formulas_used != null) {
                // name_link sorts the formulas by impact, so the most relevant one is named first
                $result = $dbo->formulas_used->name_link();
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NO_FORMULAS_USED);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to use formulas for a calculation', $msg);
        }
        return $result;
    }

    /**
     * the results used to calculate the given result grouped like the used values, used by the
     * results column of the result default page e.g. the increase that a growth rate has been
     * calculated from
     *
     * @param result|db_object|null $dbo the result whose used results should be listed
     * @param user_message $msg to report a missing list or an unexpected selection object
     * @return string the used results or the message that no other result has been used
     */
    function results_used(result|db_object|null $dbo, user_message $msg): string
    {
        global $mtr;

        $result = '';
        if ($dbo == null) {
            log_err_msg_ui('the result is missing to select the used results', $msg);
        } elseif ($dbo::class == result::class) {
            // like the used values only a page request loads the used results, so an url-built
            // result shows no column instead of a message about a list that is not known here
            if ($dbo->results_used != null) {
                // the result itself is the context of the column, so its phrases are left out
                $result = $this->result_list_grouped($dbo->results_used, $dbo->grp->phr_lst(), $msg);
                if ($result == '') {
                    $result = $mtr->txt(msg_id::INFO_NO_RESULTS_USED);
                }
            }
        } else {
            log_err_msg_ui($dbo::class . ' is not expected to use results for a calculation', $msg);
        }
        return $result;
    }

    /**
     * the values that name the given source grouped by their phrases like the default word view,
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
                // the source is the context of the page, so no phrase is left out of the value lines
                $result = $this->value_list($val_lst, new phrase_list(), $msg, $style_id, true);
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
            $result = $val_lst->list_most_relevant($msg, $phr_lst, [], $style_txt);
        } else {
            $result = $val_lst->list($msg, $phr_lst, [], $style_txt);
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
        return $res_lst->list($msg, $phr_lst, [], $style_txt);
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
            $res_lst = $res_lst->get_by_formula($dbo, $msg);
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
     * show the results related to the given object grouped like the values of a phrase (see
     * values_most_relevant): the newest time period first, then the phrases shared by several
     * results, then the remaining results by impact, each with the unit symbols behind the number
     *
     * @param db_object|combine_named|null $dbo the term whose related results should be listed
     * @param user_message $msg to report a problem of reading the config or a symbol
     * @param data_object|null $cfg the request cache with the preloaded results
     * @return string the html code of the grouped results or '' if the cache has no result
     */
    function results_related(
        db_object|combine_named|null $dbo,
        user_message                 $msg,
        ?data_object                 $cfg = null
    ): string
    {
        $result = '';
        if ($cfg != null and !$cfg->res_lst->is_empty()) {
            // the phrase of the page is assumed by the reader, so it is left out of the result lines
            $phr_lst = new phrase_list();
            if ($dbo::class == word::class or $dbo::class == triple::class) {
                $phr_lst->add_phrase($dbo->phrase());
            }
            $result = $this->result_list_grouped($cfg->res_lst, $phr_lst, $msg);
        }
        return $result;
    }

    /**
     * show a list of results grouped like the values of a phrase (see value_list): the newest time
     * period first, then the phrases shared by several results, then the rest by impact, limited
     * by the configured page size with the "... and n more" tail and the unit symbols behind the
     * number; the shared renderer of the result columns of the phrase, value and result pages
     *
     * @param result_list $res_lst the results to show
     * @param phrase_list $phr_lst the phrases assumed by the reader e.g. the phrases of the page
     * @return string the html code of the grouped results, '' if the list is empty
     */
    private function result_list_grouped(result_list $res_lst, phrase_list $phr_lst, user_message $msg): string
    {
        $html = new html_base();
        $result = $res_lst->list_most_relevant($msg, $phr_lst);
        // the same block div as the value list, so that each result stays on one line
        if ($result != '') {
            $result = $html->div($result, view_styles::COL_SM_12);
        }
        return $result;
    }

    /**
     * the triples that point to the given type, one column per sub type of the type below its name,
     * e.g. for "column (system)" the triple "column problem (high prio)" below "mayor column (system)";
     * the sub types are shown side by side in up to four columns, which wrap on a narrow screen
     *
     * TODO Prio 2 list only the triples related to the phrase of the page again
     *      (phrase_list::related_triples_to); switched off, because the walk of parents_all
     *      reaches most of the graph and the page did not respond (docs/llm/pending_prio_2.md)
     *
     * @param db_object|type_object|combine_named|null $dbo the base phrase e.g. "populism solution"
     * @param int|null $typ_id the phrase id of the type e.g. of "column (system)", which is the column phrase of the component
     * @param user_message $msg to report a component without a known type
     * @param data_object|null $dto the request cache, which is filled from the api if online; null for no list
     * @param array $url_arr the url vars of the shown page as back target of the delete and the add icons
     * @return string the html code of the labelled triple lists or '' if the page shows no phrase
     */
    function triples_of_types(
        db_object|type_object|combine_named|null $dbo,
        ?int                                     $typ_id,
        user_message                             $msg,
        ?data_object                             $dto,
        array                                    $url_arr = []
    ): string
    {
        $result = '';
        // e.g. the editor opened without a phrase has nothing to list yet
        if (!($dbo instanceof word or $dbo instanceof triple) or $dbo->id() == 0 or $dto == null) {
            return $result;
        }
        if ($dto->online and $typ_id != null) {
            $this->add_triple_list_cache($typ_id, $dto, $msg);
        }
        $typ = $typ_id == null ? null : $dto->phr_lst->get($typ_id);
        if ($typ == null) {
            log_err_msg_ui('the type phrase ' . $typ_id . ' of the triple list is missing', $msg);
        } else {
            $col_lst = [];
            foreach ($dto->phr_lst->sub_types($typ, $msg) as $sub) {
                $col_lst[] = $this->triples_of_type($sub, $dto->phr_lst, $msg, $url_arr);
            }
            $result = new html_base()->div_row_wrapping_cols($col_lst, $msg);
        }
        return $result;
    }

    /**
     * the name of the type followed by its triples separated by a comma, each with a delete icon,
     * and in the next line the add icon of a new triple that points to the type
     *
     * @param phrase $typ the type phrase that the listed triples point to e.g. "mayor column (system)"
     * @param phrase_list $phr_lst the request cache with the triples
     * @param user_message $msg to report a problem of reading a triple of the cache
     * @param array $url_arr the url vars of the shown page as back target of the delete and the add icons
     * @return string the html code of the type name followed by the list of its triples
     */
    private function triples_of_type(phrase $typ, phrase_list $phr_lst, user_message $msg, array $url_arr): string
    {
        global $mtr;
        global $ui_sys;

        $html = new html_base();
        $trp_lst = $phr_lst->parent_triples($typ, $msg);
        $trp_lst->sort_by_name();
        $items = [];
        foreach ($trp_lst->lst() as $phr) {
            // the list has phrases, so the delete view gets the triple id and not the negative phrase id
            $items[] = $phr->name_link()
                . $html->icon_link(views::TRIPLE_DEL_ID, $phr->obj_id(), icons::DEL, $mtr->txt(triple::MSG_DEL), $url_arr);
        }
        // the new triple is preset with the type and the verb of the listed triples
        // e.g. "can be" "mayor column (system)", so that the user only selects the from phrase
        $preset = [url_var::PHRASE_TO => $typ->id()];
        $vrb = $ui_sys?->typ_lst_cache?->vrb?->get_by_code_id(verbs::CAN_BE);
        if ($vrb != null) {
            $preset[url_var::VERB] = $vrb->id();
        }
        $url = $html->url_back(views::TRIPLE_ADD_ID, 0, $url_arr, http_build_query($preset));
        $add = $html->change_icon($url, icons::ADD, $mtr->txt(msg_id::TRIPLE_ADD));
        return $html->text_h4($typ->name_link()) . $html->div(implode(', ', $items)) . $html->div($add);
    }

    /**
     * add the type of a triple list, its sub types and the triples that point to them to the request cache
     *
     * @param int $typ_id the phrase id of the type e.g. of "column (system)"
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of an api message to the user
     * @return void
     */
    private function add_triple_list_cache(int $typ_id, data_object $dto, user_message $msg): void
    {
        $typ_lst = new phrase_list();
        $typ_lst->load_by_ids([$typ_id], $msg);
        // a sub type is linked from the type and a listed triple points to the type or a sub type
        $typ_lst->load_related_by_ids($typ_lst, foaf_direction::UP, $msg);
        $typ_lst->load_related_by_ids($typ_lst, foaf_direction::DOWN, $msg);
        $dto->add_phrases($typ_lst, $msg);
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
        // the data is loaded already by the page cache step (see view_exe::add_page_cache), so
        // this only fills what a caller without that step has not loaded
        $phr = $this->add_start_page_data($dto, $msg);
        $child_lst = $dto->start_lst_children ?? new phrase_list();
        // the page already says what the table is about, so it is shown without the border; the
        // ranking is a grid of the defined columns, so a measured figure of a problem that fits
        // no column stays on the page of the problem instead of adding a row here; by default
        // only the mayor columns with the numbers are shown and the "..." header leads to the
        // full table with every column and the range of each number; a row whose numbers are
        // all in a column left out is no part of the ranking either, e.g. the reward ratio row
        // of a problem, so the ranking shows the rows with a number only
        return $this->table_with_related_columns($phr->obj(), $msg, $dto, true, false, $url_array,
            true, value_list::COLUMN_TIERS_EX_MAIN, false, true, $child_lst);
    }

    /**
     * load all data of the start view table into the request cache, so that the creation of the
     * html needs no backend call: the page phrase, the values and results of its children with
     * their links and the children of the list triples; each part is loaded only once per cache
     *
     * @param data_object $dto the request cache to fill
     * @param user_message $msg to report a problem of an api message to the user
     * @return phrase the page phrase of the start view
     */
    function add_start_page_data(data_object $dto, user_message $msg): phrase
    {
        $phr = $this->start_page_phrase($dto, $msg);
        // the children are loaded last, so with them all data of the table is in the cache and
        // the checks of the other parts are not repeated for each render; without the api the
        // children cannot be loaded, so an offline cache keeps none
        if ($dto->start_lst_children == null) {
            $this->add_start_page_cache($dto, $phr, $msg);
            if ($dto->online) {
                $dto->start_lst_children = $this->start_page_children($dto, $msg);
            }
        }
        return $phr;
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
        $result = $dto->phr_lst->get_by_name(triples::GLOBAL_PROBLEM, $msg);
        if ($result == null) {
            $child_lst = new phrase_list();
            if ($child_lst->load_related_by_name(
                triples::GLOBAL_PROBLEM, foaf_direction::DOWN, $msg)) {
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
            $result->load_by_name(triples::GLOBAL_PROBLEM, $msg);
        }
        return $result;
    }

    /**
     * TODO Prio 1 avoid this exception
     * add the phrases and the values that the table of the start view needs to the request cache
     *
     * each step is skipped if the cache already has that data, so a unit test that fills the
     * cache upfront needs no api call at all; an offline cache is never filled from the api,
     * like add_column_definitions, so the empty result list of a unit test is not filled with
     * the results of the pod, which would repeat the groups of the test values
     *
     * @param data_object $dto the request cache to fill
     * @param phrase $phr the page phrase of the start view, which is "global problem"
     * @param user_message $msg to report a problem of an api message to the user
     * @return void
     */
    private function add_start_page_cache(data_object $dto, phrase $phr, user_message $msg): void
    {
        if (!$dto->online) {
            return;
        }
        // "global problem" itself is in no value group, so the rows are the phrases that a
        // triple links to it; without those triples the table finds no value at all
        if ($dto->phr_lst->child_phrases($phr)->is_empty()) {
            $child_lst = new phrase_list();
            if ($child_lst->load_related_by_name(
                triples::GLOBAL_PROBLEM, foaf_direction::DOWN, $msg)) {
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
        // a column of the ranking can be calculated instead of measured, e.g. the reward ratio,
        // and such a number is a result, so the results of the problems are loaded like their
        // values and shown in the same table
        if ($dto->res_lst->is_empty()) {
            $res_lst = new result_list();
            if ($res_lst->load_by_phrase_list($dto->phr_lst->child_phrases($phr), $msg)) {
                $dto->set_result_list($res_lst);
            }
        }
        // a defined column that no number carries names a phrase of the row instead of a number,
        // e.g. the solution column shows the solution of the problem row; the table recognises
        // that phrase by the triple that links it to the column, e.g. "reduce climate gas
        // emissions is a solution", so the links of every phrase of the numbers are loaded once;
        // the results are included, because a row whose numbers are all calculated names its
        // solution only in the phrases of its results
        $num_lst = clone $dto->val_lst;
        $num_lst->add_results($dto->res_lst, $msg);
        $num_phr_lst = $num_lst->phrase_list();
        if (!$num_phr_lst->is_empty() and !$this->column_links_loaded($dto)) {
            $lnk_lst = new phrase_list();
            if ($lnk_lst->load_related_by_ids($num_phr_lst, foaf_direction::UP, $msg)) {
                $dto->add_phrases($lnk_lst, $msg);
            }
        }
    }

    /**
     * the children of the list triples of the start view, e.g. every reason that "populism
     * reason" links, which names more phrases than the numbers of its row carry, so that the
     * ", ..." behind a cell counts all of them; they are kept out of the request cache, because
     * the cache also fills e.g. the phrase selectors of later pages
     *
     * @param data_object $dto the request cache with the links of the numbers of the table
     * @param user_message $msg to report a problem of an api message to the user
     * @return phrase_list the children of the list triples, empty offline
     */
    private function start_page_children(data_object $dto, user_message $msg): phrase_list
    {
        $result = new phrase_list();
        $lst_phr_lst = $dto->phr_lst->column_list_phrases($dto->phr_lst->column_names());
        if ($dto->online and !$lst_phr_lst->is_empty()) {
            $result->load_related_by_ids($lst_phr_lst, foaf_direction::DOWN, $msg);
        }
        return $result;
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
        $col_phr_lst = $dto->phr_lst->column_phrases();
        foreach ($dto->phr_lst->column_names() as $name) {
            $col_phr = $col_phr_lst[$name] ?? null;
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
