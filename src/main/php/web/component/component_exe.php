<?php

/*

    web/view/component.php - function to execute a view component
    ----------------------

    to creat the HTML code to display a component

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

namespace Zukunft\ZukunftCom\main\php\web\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::COMPONENT . 'component.php';
include_once html_paths::EXECUTE . 'system_form.php';
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'list_sort.php';
include_once html_paths::HTML . 'sheet.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'component_type.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_dsp;
use Zukunft\ZukunftCom\main\php\web\html\list_sort;
use Zukunft\ZukunftCom\main\php\web\html\sheet;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_dsp;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\types\component_type;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class component_exe extends component
{

    /*
     * display
     */

    /**
     * create the html code of this component filled with the data from the given database object ($dbo)
     * TODO the html form field name should always be an url var name
     *
     * @param db_object_dsp|null $dbo the word, triple, formula or ... object that should be shown to the user
     * @param string $form_name the name of the view which is also used for the html form name
     * @param int $msk_id the database id of the calling view
     * @param data_object_dsp|null $cfg the context used to create the view
     * @param string $back the backtrace for undo actions
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    function dsp_entries(
        ?db_object_dsp   $dbo,
        string           $form_name = '',
        int              $msk_id = 0,
        ?data_object_dsp $cfg = null,
        string           $back = '',
        bool             $test_mode = false
    ): string
    {
        global $mtr;

        if ($dbo == null) {
            // the $dbo check and the message creation has already been done in the view level
            $this->log_debug($this->dsp_id());
        } else {
            $this->log_debug($dbo->dsp_id() . ' with the view ' . $this->dsp_id());
        }

        $result = '';

        // get the default values
        // TODO call only when needed
        $phr_lst = new phrase_list();
        $phr_lst->load_fallback();
        if ($cfg != null) {
            if ($cfg->has_phrases()) {
                $phr_lst = $cfg->phrase_list();
            }
        }

        $form = new system_form();
        $page = new system_page();

        // list of all possible view components
        $result .= match ($this->type_code_id($cfg->typ_lst_cache)) {
            // start page
            component_type::TEXT => $this->text(),
            // TODO Prio 2 use the spreadsheet for the start view
            //component_type::CALC_SHEET => $this->calc_sheet(),
            component_type::CALC_SHEET => $this->start_list($cfg),

            // system form - usage only allowed for internal system forms
            component_type::FORM_TITLE => $form->form_tile($form_name, $this->ui_msg_code_id),
            component_type::FORM_HIDDEN_BACK => $form->form_back($msk_id, $dbo->id(), $back),
            component_type::FORM_HIDDEN_STEP => $form->form_confirm(),
            component_type::FORM_FIELD_NAME => $form->form_name($dbo, $this->style_code_id($cfg->typ_lst_cache)),
            component_type::FORM_FIELD_DESCRIPTION => $form->form_description($dbo),
            component_type::FORM_FIELD_PLURAL => $form->form_field_plural($dbo, $this->style_code_id($cfg->typ_lst_cache)),
            component_type::FORM_FIELD_URL => $form->form_field_url($dbo),
            component_type::FORM_FIELD_GROUP => $form->form_field_group_name($dbo),
            component_type::FORM_FIELD_GROUP_OR_PHRASES => $form->form_field_group_or_phrases($dbo),
            component_type::SYSTEM_FORM_FIELD_SOURCE_GROUP_OR_PHRASES => $form->form_field_source_group_or_phrases($dbo),
            component_type::FORM_FIELD_SELECTION_NAME => $form->form_field_selection_name($dbo),
            component_type::FORM_FIELD_SELECTION_DESCRIPTION => $form->form_field_selection_description($dbo),
            component_type::FORM_FIELD_SELECTION_TEXT => $form->form_field_selection_text($dbo),
            component_type::FORM_SELECT_PHRASE => $form->form_phrase($dbo, $form_name, $this->code_id(), $phr_lst, $test_mode),
            component_type::FORM_SELECT_VERB => $form->form_verb($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_SOURCE => $form->form_source($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_REF => $form->form_ref($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_PHRASE_TYPE => $form->form_phrase_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_VERB_TYPE => $form->form_verb_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_SOURCE_TYPE => $form->form_source_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_REF_TYPE => $form->form_ref_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_VALUE_TYPE => $form->form_value_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_FORMULA_TYPE => $form->form_formula_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_FIELD_FORMULA_EXPRESSION => $form->form_formula_expression($dbo, $form_name),
            component_type::FORM_FIELD_FORMULA_ALL_VAR_NEEDED => $form->form_formula_all_fields($dbo, $form_name),
            component_type::FORM_SELECT_VIEW_TYPE => $form->form_view_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_VIEW_STYLE => $form->form_view_style($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_COMPONENT_TYPE => $form->form_component_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_COMPONENT_STYLE => $form->form_component_style($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_FORMULA_LINK_TYPE => $form->form_formula_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_FORMULA_LINK_PRIORITY => $form->form_field_formula_link_priority($dbo),
            component_type::FORM_SELECT_VIEW_LINK_TYPE => $form->form_view_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_SELECT_VIEW_LINK_PRIORITY => $form->form_field_view_link_priority($dbo),
            component_type::FORM_SELECT_COMPONENT_LINK_TYPE => $form->form_component_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER => $form->form_field_component_link_order_number($dbo),
            component_type::FORM_SHARE_TYPE => $form->form_share_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_PROTECTION_TYPE => $form->form_protection_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_type::FORM_TABLE_LINKED_VIEWS => $form->form_table_linked_view($dbo, $form_name),
            component_type::FORM_BUTTON_CANCEL => $form->form_cancel($msk_id, $dbo->id()),
            component_type::FORM_BUTTON_SAVE => $form->form_save(),
            component_type::FORM_BUTTON_DEL => $form->form_del(),
            component_type::FORM_END => $form->form_end(),

            // hidden - only used for formatting without functional behaviour
            component_type::ROW_START => $form->row_start(),
            component_type::ROW_RIGHT => $form->row_right(),
            component_type::ROW_END => $form->row_end(),

            // fixed system pages - usage only allowed for fixed internal system pages
            component_type::SYSTEM_TITLE => $page->system_tile($this->ui_msg_code_id),
            component_type::SYSTEM_BODY_ABOUT => $page->about_body(),
            component_type::SYSTEM_BODY_SETUP => $page->setup_body(),
            component_type::SYSTEM_BODY_SIGNUP => $page->signup_body(),
            component_type::SYSTEM_BODY_LOGIN => $page->login_body(),
            component_type::SYSTEM_BODY_LOGIN_ACTIVATE => $page->activate_body(),
            component_type::SYSTEM_BODY_LOGIN_RESET => $page->reset_body(),
            component_type::SYSTEM_BODY_LOGOUT => $page->logout_body(),
            component_type::SYSTEM_BODY_SEARCH => $page->body_search(),
            component_type::SYSTEM_BODY_SEARCH_FULL => $page->body_search_full(),
            component_type::SYSTEM_BODY_VALUE_DETAIL => $page->value_details(),
            component_type::SYSTEM_BODY_RESULT_EXPLAIN => $page->result_explain(),
            component_type::SYSTEM_BODY_FORMULA_TEST => $page->formula_test(),
            component_type::SYSTEM_BODY_SANDBOX => $page->sandbox(),
            component_type::SYSTEM_BODY_UNDO => $page->undo(),
            component_type::SYSTEM_BODY_USER_SETTINGS => $page->user_setting(),
            component_type::SYSTEM_BODY_PROCESS => $page->process(),
            component_type::SYSTEM_BODY_ERROR_LOG => $page->error_log(),
            component_type::SYSTEM_BODY_ERROR_UPDATE => $page->error_update(),
            component_type::SYSTEM_BODY_PROCESS_PROGRESS => $page->process_progress(),
            component_type::SYSTEM_BODY_PROCESS_LIST => $page->process_list(),

            // ref only -

            // formula only -

            // view only -
            component_type::SHOW_NAME => $form->show_name($dbo),
            component_type::SHOW_FIELD_USAGE => $form->usage($dbo),
            component_type::WORD_RESULTS => $form->result($dbo),
            component_type::USED_IN_AS_TEXT => $form->used_as_text($dbo),
            component_type::USED_IN_AS_TEXT_WITH_LINK => $form->used_as_text_link($dbo),
            component_type::USAGE_WORD => $this->usage_word($dbo, $form_name),
            component_type::SYSTEM_CHANGE_LOG => $this->system_change_log($dbo, $form_name),

            // verb only -
            component_type::VERB_NAME => $this->verb_name($dbo),
            component_type::LIST_TRIPLES => $this->triple_list($dbo),

            // base
            component_type::PHRASE => $this->name_tip(),
            component_type::PHRASE_NAME => $this->phrase_name($dbo),
            component_type::LINK => $this->phrase_link($dbo, $form_name),

            // select
            component_type::SELECT_VIEW => $this->view_select($dbo, $form_name, $cfg),
            component_type::SELECT_PHRASE => $this->phrase_select($dbo, $form_name, $phr_lst,),

            // table
            component_type::VALUES_ALL => $this->all($dbo, $back),
            component_type::VALUES_RELATED => $this->table($dbo, $cfg),
            component_type::NUMERIC_VALUE => $this->num_list($dbo, $back),

            // related
            component_type::LIST_REF => $this->ref_list_word($dbo, $cfg),
            component_type::LINK_LIST_WORD => $this->link_list_word($dbo, $cfg),
            component_type::FORMULAS => $this->formulas($dbo),
            component_type::FORMULA_RESULTS => $this->results($dbo),
            component_type::WORDS_DOWN => $this->word_children($dbo),
            component_type::WORDS_UP => $this->word_parents($dbo),

            // export
            component_type::JSON_EXPORT => $this->json_export($dbo, $back),
            component_type::XML_EXPORT => $this->xml_export($dbo, $back),
            component_type::CSV_EXPORT => $this->csv_export($dbo, $back),
            component_type::ODS_EXPORT => $this->ods_export($dbo, $back),

            default => 'program code for component ' . $this->dsp_id() . ' missing<br>'
        };
        $this->log_debug($this->dsp_id() . ' created');
        // TODO remove temp
        if (str_starts_with('program code for component', $result)) {
            $this->log_err($result);
        }

        return $result;
    }

    /**
     * @return string a fixed text
     */
    function text(): string
    {
        return $this->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_name(db_object_dsp $phr): string
    {
        return $phr->name();
    }

    /**
     * @return string the name of a phrase and give the user the possibility to change the phrase name
     */
    function phrase_select(
        db_object_dsp $phr,
        string $form_name,
        phrase_list $phr_lst
    ): string
    {
        return $phr->phrase_selector($phr_lst, url_var::PHRASE_LONG, $form_name, $phr->id());
    }

    /**
     * @return string show a list of phrases with a suggested link type that might be linked to the object
     */
    function phrase_link(
        db_object_dsp $phr,
        string $form_name,
        phrase_list $phr_lst
    ): string
    {
        return $phr->phrase_selector($phr_lst, url_var::PHRASE_LONG, $form_name, $phr->id());
    }

    /**
     * the html code to select the view for the given object
     * which can also be the component itself
     * so view_select (for the $obj) can call view_selector of this class if $obj is of class component
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param string $form the name of the view which is also used for the html form name
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code to select a view
     */
    function view_select(db_object_dsp $dbo, string $form, ?data_object_dsp $cfg = null): string
    {
        $msk_lst = null;
        // over
        if ($cfg != null) {
            if ($cfg->has_view_list()) {
                $msk_lst = $cfg->view_list();
            }
        }
        if ($msk_lst == null) {
            $msk_lst = $dbo->view_list();
        }
        return $dbo->view_selector($form, $msk_lst);
    }

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code of the external references
     */
    function ref_list_word(db_object_dsp $dbo, ?data_object_dsp $cfg): string
    {
        // TODO review
        $result = 'list of references to ' . $dbo->name() . ' ';
        if ($cfg != null) {
            $result .= '';
        }
        return $result;
    }

    /**
     * @param db_object_dsp $dbo the word, triple or formula object that should be shown to the user
     * @param data_object_dsp|null $cfg the context used to create the view
     * @return string with the html code of links that can be changes
     */
    function link_list_word(db_object_dsp $dbo, ?data_object_dsp $cfg): string
    {
        // TODO review
        return 'list of phrases related to ' . $dbo->name() . ' ';
    }

    /**
     * @return string with the html code that shows the usage of this word
     */
    function usage_word(db_object_dsp $phr, string $form_name): string
    {
        // TODO review
        return 'usage of ' . $phr->name() . ' ';
    }

    /**
     * @return string with the html code that shows the recent changes of this object
     */
    function system_change_log(db_object_dsp $phr, string $form_name): string
    {
        // TODO review
        return 'change log for ' . $phr->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string the html code to show a list of values
     */
    function table(?db_object_dsp $dbo = null, ?data_object_dsp $cfg = null): string
    {
        return 'values related to ' . $this->name() . ' ';
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function num_list(): string
    {
        return $this->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function verb_name(?db_object_dsp $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move to a component exe part class
     * @return string a dummy text
     */
    function triple_list(?db_object_dsp $dbo = null): string
    {
        return $dbo->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function formulas(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function results(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function word_children(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function word_parents(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function json_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function xml_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function csv_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function ods_export(): string
    {
        return $this->name();
    }

    /**
     * TODO move code from component_dsp_old
     * @return string a dummy text
     */
    function all(): string
    {
        return $this->name();
    }

    /**
     * @return string the html code of a calculation spreadsheet
     */
    function calc_sheet(): string
    {
        $sheet = new sheet();
        return $sheet->calc_sheet();
    }

    /**
     * @return string the html code of a sortable list
     */
    function list_sort(
        phrase $phr,
        data_object $dbo
    ): string
    {
        $lst = new list_sort();
        return $lst->list_sort($phr, $dbo);
    }

    /**
     * @return string the html code for the start view as a sortable list
     */
    function start_list(
        data_object $dbo
    ): string
    {
        $phr = new phrase();
        $phr->load_by_name(triples::GLOBAL_PROBLEM);
        return $this->list_sort($phr, $dbo);
    }

}
