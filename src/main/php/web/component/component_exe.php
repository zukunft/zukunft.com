<?php

/*

    web/component/component_exe.php - call the functions to execute a view component
    -------------------------------

    to create the HTML code to display a component

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
include_once html_paths::EXECUTE . 'ui_base.php';
include_once html_paths::EXECUTE . 'ui_foaf.php';
include_once html_paths::EXECUTE . 'ui_log.php';
include_once html_paths::EXECUTE . 'ui_preview.php';
include_once html_paths::EXECUTE . 'ui_rank.php';
include_once html_paths::EXECUTE . 'ui_select.php';
include_once html_paths::EXECUTE . 'ui_im_export.php';
include_once html_paths::EXECUTE . 'ui_link.php';
include_once html_paths::EXECUTE . 'ui_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::LOG . 'change_log_list.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::SANDBOX . 'combine_named.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SANDBOX . 'sandbox_list.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'type_object.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'component_types.php';

use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_foaf;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_log;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_preview;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_rank;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_select;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_im_export;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_link;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\sandbox\combine_named;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\web\sandbox\sandbox_list;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\types\type_object;

class component_exe extends component
{

    /*
     * display
     */

    /**
     * create the html code of this component filled with the data from the given database object ($dbo)
     * TODO the html form field name should always be an url var name
     * TODO use the style id of the component instead of having a function parameter
     *
     * @param db_object|type_object|combine_named|sandbox_list|null $dbo the word, triple, formula or ... object that should be shown to the user
     * @param string $form_name the name of the view which is also used for the html form name
     * @param int $msk_id the database id of the calling view
     * @param data_object|null $cfg the context used to create the view
     * @param string $back the backtrace for undo actions
     * @param string $pattern the selection pattern to filter a selection
     * @param bool $test_mode true to create a reproducible result e.g. by using just one phrase
     * @return string the html code of all view components
     */
    function dsp_entries(
        db_object|type_object|combine_named|sandbox_list|null $dbo,
        string                                                $form_name = '',
        int                                                   $msk_id = 0,
        ?data_object                                          $cfg = null,
        ?int                                                  $style_id = null,
        string                                                $back = '',
        string                                                $pattern = '',
        bool                                                  $test_mode = false,
        array                                                 $url_array = [],
        int|string                                            $test_form_unique_id = ''
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
        $log_lst = new change_log_list();
        $log_lst->load_fallback();
        if ($cfg != null) {
            if ($cfg->has_changes()) {
                $log_lst = $cfg->change_log();
            }
        }
        $err_lst = new sys_log_list();
        if ($cfg != null) {
            if ($cfg->has_sys_log()) {
                $err_lst = $cfg->sys_log_list();
            }
        }

        $form = new system_form();
        $page = new system_page();
        $base = new ui_base();
        $select = new ui_select();
        $link = new ui_link();
        $list = new ui_list();
        $foaf = new ui_foaf();
        $rank = new ui_rank();
        $port = new ui_im_export();
        $preview = new ui_preview();
        $log = new ui_log();

        // list of all possible view components
        $t_id = $this->type_id();
        if ($t_id == 17) {
            log_info('');
        }
        $tc_id = $this->type_code_id($cfg->typ_lst_cache);
        // get the style code id from the component
        $style = $this->style_code_id($cfg->typ_lst_cache);

        // get the html code from the component
        $result .= match ($tc_id) {

            // start page - components used for the start page
            component_types::PHRASE_NAME => $base->phrase_name($dbo),
            // TODO Prio 2 use the spreadsheet for the start view
            //component_type::CALC_SHEET => $this->calc_sheet(),
            component_types::CALC_SHEET => $list->start_list($cfg),

            // system form - components that can only be used for internal system forms
            // general form fields
            component_types::FORM_TITLE => $form->form_tile($form_name, $this->ui_msg_code_id),
            component_types::TITLE_NAMED_EDIT => $form->title_named($dbo),
            component_types::TITLE_TRIPLE_EDIT => $form->title_triple($dbo),
            component_types::TITLE_FORMULA_EDIT => $form->title_formula($dbo),
            component_types::TITLE_VALUE_EDIT => $form->title_value($dbo),
            component_types::FORM_FIELD_NAME => $form->form_name($dbo, $style),
            component_types::FORM_FIELD_DESCRIPTION => $form->form_description($dbo),

            // select object fields
            component_types::FORM_SELECT_PHRASE => $form->form_phrase($dbo, $form_name, $this->code_id, $phr_lst, $test_mode),
            component_types::FORM_SELECT_PHRASES => $form->form_phrases($dbo, $form_name, $this->code_id, $phr_lst, $test_mode),
            component_types::FORM_SELECT_VERB => $form->form_verb($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VERBS => $form->form_verbs($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_SOURCE => $form->form_source($dbo, $form_name, $cfg->source_list(), $pattern),
            component_types::FORM_SELECT_SOURCES => $form->form_sources($dbo, $form_name, $cfg->source_list()),
            component_types::FORM_SELECT_REF => $form->form_ref($dbo, $form_name, $cfg->typ_lst_cache, $pattern),
            component_types::FORM_SELECT_REFS => $form->form_refs($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VALUE => $form->form_value($dbo, $form_name, $cfg->value_list()),
            component_types::FORM_SELECT_VALUES => $form->form_values($dbo, $form_name, $cfg->value_list()),
            component_types::FORM_SELECT_FORMULA => $form->form_formula($dbo, $form_name, $cfg->formula_list()),
            component_types::FORM_SELECT_FORMULAS => $form->form_formulas($dbo, $form_name, $cfg->formula_list()),
            component_types::FORM_SELECT_TERM => $form->form_term($dbo, $form_name, $this->code_id, $phr_lst, $test_mode),
            component_types::FORM_SELECT_TERMS => $form->form_terms($dbo, $form_name, $this->code_id, $phr_lst, $test_mode),
            component_types::FORM_SELECT_RESULT => $form->form_result($dbo, $form_name, $cfg->result_list()),
            component_types::FORM_SELECT_RESULTS => $form->form_results($dbo, $form_name, $cfg->result_list()),
            component_types::FORM_SELECT_VIEW => $form->form_view($dbo, $form_name, $cfg->view_list()),
            component_types::FORM_SELECT_VIEWS => $form->form_views($dbo, $form_name, $cfg->view_list()),
            component_types::FORM_SELECT_PARENT_VIEW => $form->form_parent_view($dbo, $form_name, $cfg->view_list()),
            component_types::FORM_SELECT_CHILD_VIEW => $form->form_child_view($dbo, $form_name, $cfg->view_list()),
            component_types::FORM_SELECT_COMPONENT => $form->form_component($dbo, $form_name, '', 1, $cfg->component_list()),
            component_types::FORM_SELECT_COMPONENTS => $form->form_components($dbo, $form_name, '', 1, $cfg->component_list()),

            // select access and protection
            component_types::FORM_SHARE_TYPE => $form->form_share_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_PROTECTION_TYPE => $form->form_protection_type($dbo, $form_name, $cfg->typ_lst_cache),

            // select object types
            component_types::FORM_SELECT_PHRASE_TYPE => $form->form_phrase_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_SOURCE_TYPE => $form->form_source_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_REF_TYPE => $form->form_ref_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VALUE_TYPE => $form->form_value_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_FORMULA_TYPE => $form->form_formula_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VIEW_TYPE => $form->form_view_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VIEW_STYLE => $form->form_view_style($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_COMPONENT_TYPE => $form->form_component_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_COMPONENT_STYLE => $form->form_component_style($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VIEW_RELATION_TYPE => $form->form_view_relation_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_FIELD_VIEW_RELATION_START_POS => $form->form_view_relation_pos($dbo, $form_name, $cfg->typ_lst_cache),

            // select link types and priority
            component_types::FORM_SELECT_FORMULA_LINK_TYPE => $form->form_formula_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_FORMULA_LINK_PRIORITY => $form->form_field_formula_link_priority($dbo),
            component_types::FORM_SELECT_VIEW_LINK_TYPE => $form->form_view_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_VIEW_LINK_PRIORITY => $form->form_field_view_link_priority($dbo),
            component_types::FORM_SELECT_COMPONENT_LINK_TYPE => $form->form_component_link_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_SELECT_COMPONENT_POS_TYPE => $form->form_component_pos_type($dbo, $form_name, $cfg->typ_lst_cache),
            component_types::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER => $form->form_field_component_link_order_number($dbo),

            // other select fields
            component_types::FORM_SELECT_VIEW_DEFAULT => $form->form_view_default($dbo, $form_name, $cfg->view_list()),
            component_types::FORM_SELECT_FILE => $port->select_file($dbo, $form_name, $cfg),
            component_types::FORM_SELECT_FORMAT_EXPORT => $port->select_export_format($dbo, $form_name, $cfg),

            // verb only fields
            component_types::FORM_FIELD_PLURAL => $form->form_field_plural($dbo, $style),
            component_types::FORM_FIELD_REVERSE => $form->form_field_reverse($dbo, $style),
            component_types::FORM_FIELD_PLURAL_REVERSE => $form->form_field_plural_reverse($dbo, $style),
            component_types::FORM_FIELD_NAME_IN_FORMULAS => $form->form_field_name_in_formulas($dbo, $style),

            // ref only fields
            component_types::SYSTEM_SHOW_REF_TYPE => $form->show_ref_type($dbo),
            component_types::SYSTEM_SHOW_REF_KEY => $form->show_ref_key($dbo),
            component_types::SYSTEM_SHOW_REF_SOURCE => $form->show_ref_source($dbo),
            component_types::SYSTEM_SHOW_REF_URL => $form->show_ref_url($dbo),
            component_types::FORM_FIELD_EXTERNAL_KEY => $form->form_field_ref_key($dbo, $style),

            // triple only fields
            component_types::FORM_FIELD_WEIGHT => $form->form_field_weight($dbo),

            // value only fields
            component_types::FORM_FIELD_VALUE => $form->form_num_value($dbo, $style),
            component_types::FORM_FIELD_GROUP => $form->form_field_group_name($dbo),
            component_types::FORM_FIELD_GROUP_OR_PHRASES => $form->form_field_group_or_phrases($dbo),

            // result only fields
            component_types::FORM_FIELD_SOURCE_GROUP => $form->form_field_source_group_name($dbo),
            component_types::FORM_FIELD_SOURCE_GROUP_OR_PHRASES => $form->form_field_source_group_or_phrases($dbo),

            // formulas only fields
            component_types::FORM_FIELD_FORMULA_EXPRESSION => $form->form_formula_expression($dbo, $form_name),
            component_types::FORM_FIELD_FORMULA_ALL_VAR_NEEDED => $form->form_formula_all_fields($dbo, $form_name),

            // for export
            component_types::FORM_FIELD_SELECTION_NAME => $form->form_field_selection_name($dbo),
            component_types::FORM_FIELD_SELECTION_DESCRIPTION => $form->form_field_selection_description($dbo),
            component_types::FORM_FIELD_SELECTION_TEXT => $form->form_field_selection_text($dbo),

            // for external links
            component_types::FORM_FIELD_URL => $form->form_field_url($dbo, $style),

            // preview of the changes if confirmed
            component_types::FORM_PREVIEW => $page->preview(),

            // hidden - only used for formatting without functional behaviour
            component_types::FORM_HIDDEN_BACK => $form->form_back($msk_id, $dbo->id(), $back),
            component_types::FORM_HIDDEN_STEP => $form->form_confirm(),

            // admin - components that only admin user can use
            component_types::ADMIN_FORM_FIELD_USER_NAME => $form->admin_form_username($dbo),
            component_types::ADMIN_FORM_FIELD_USER_EMAIL => $form->admin_form_user_email($dbo),
            component_types::ADMIN_FORM_FIELD_USER_PASSWORD => $form->admin_form_user_password($dbo),
            component_types::ADMIN_FORM_FIELD_LANGUAGE_SYMBOL => $form->admin_form_language_symbol($dbo),
            component_types::FIELD_LANGUAGE_SYMBOL => $form->show_language_symbol($dbo),
            component_types::SYSTEM_ADMIN_URL_DELAY => $page->admin_url_delay(),
            component_types::SYSTEM_ADMIN_LOGIN_FAILS => $page->admin_login_fails(),
            component_types::SYSTEM_ADMIN_ERRORS_UNASSIGNED => $page->admin_errors_unassigned(),
            component_types::SYSTEM_ADMIN_ERRORS_DELAYED_FIX => $page->admin_errors_delayed_fix(),
            component_types::SYSTEM_ADMIN_JOBS_DELAYED => $page->admin_jobs_delayed(),
            component_types::SELECT_LIST => $select->list_select($dbo, $cfg->typ_lst_cache->lan, $form_name),
            component_types::EXPRESSION => $base->expression($dbo),
            component_types::EXPRESSION_LATEX_LINK => $base->expression_latex_link($dbo),

            // buttons
            component_types::FORM_BUTTON_CANCEL => $form->button_cancel($msk_id, $dbo->id()),
            component_types::FORM_BUTTON_SAVE => $form->button_save(),
            component_types::FORM_BUTTON_CONFIRM => $form->button_confirm(),
            component_types::FORM_BUTTON_DEL => $form->button_del(),
            component_types::FORM_BUTTON_IMPORT => $form->button_import(),
            component_types::FORM_BUTTON_EXPORT => $form->button_export(),

            // simple close the form section
            component_types::FORM_END => $form->form_end(),

            // show changes due to a pending user change
            component_types::SYSTEM_SHOW_RESULT_DIFF => $list->result_changes($dbo),

            component_types::SYSTEM_PASTE_TABLE_CONTEXT => $preview->paste_table(),
            component_types::SYSTEM_PASTE_TABLE_BODY => $preview->table_body(),
            component_types::SYSTEM_SELECTION_TEXT => $preview->selection_text(),
            component_types::SYSTEM_POPUP_TITLE => $preview->popup_title($this->ui_msg_code_id, $dbo),
            component_types::FORM_CLASS => $preview->popup_class($dbo),
            component_types::FORM_CHANGES => $preview->popup_changes($url_array),
            component_types::FORM_IMPACT => $preview->popup_impact($url_array),
            component_types::SYSTEM_SHOW_VIEW_DIFF => $preview->view_diff(),

            // fixed system pages - usage only allowed for fixed internal system pages
            component_types::SYSTEM_TITLE => $page->system_tile($this->ui_msg_code_id, $url_array),
            component_types::SYSTEM_BODY_ABOUT => $page->about_body(),
            component_types::SYSTEM_BODY_SETUP => $page->setup_body(),
            component_types::SYSTEM_BODY_SIGNUP => $page->signup_body($url_array),
            component_types::SYSTEM_BODY_LOGIN => $page->login_body($url_array),
            component_types::SYSTEM_BODY_LOGIN_ACTIVATE => $page->activate_body($url_array),
            component_types::SYSTEM_BODY_LOGIN_RESET => $page->reset_body($url_array),
            component_types::SYSTEM_BODY_LOGOUT => $page->logout_body(),
            component_types::SYSTEM_BODY_SEARCH => $page->body_search($url_array),
            component_types::SYSTEM_BODY_SEARCH_FULL => $page->body_search_full(),
            component_types::SYSTEM_BODY_VALUE_DETAIL => $page->value_details(),
            component_types::SYSTEM_BODY_RESULT_EXPLAIN => $page->result_explain(),
            component_types::SYSTEM_BODY_FORMULA_TEST => $page->formula_test(),
            component_types::SYSTEM_BODY_SANDBOX => $page->sandbox(),
            component_types::SYSTEM_BODY_UNDO => $page->undo(),
            component_types::SYSTEM_BODY_USER_SETTINGS => $page->user_setting(),
            component_types::SYSTEM_BODY_PROCESS => $page->process(),
            component_types::SYSTEM_BODY_PROCESS_LIST => $page->process_list(),
            component_types::SYSTEM_BODY_PROCESS_PROGRESS => $page->process_progress(),
            component_types::SYSTEM_BODY_ERROR_LOG => $page->error_log(),
            component_types::SYSTEM_BODY_ERROR_UPDATE => $page->error_update(),

            // internal and hidden components used for formatting
            component_types::ROW_START => $form->row_start(),
            component_types::ROW_RIGHT => $form->row_right(),
            component_types::ROW_CENTER => $form->row_center(),
            component_types::ROW_END => $form->row_end(),

            // components for user views

            // select
            component_types::SELECT_PHRASE => $select->phrase_select($dbo, $form_name, $phr_lst),
            component_types::SELECT_VIEW => $select->view_select($dbo, $form_name, $cfg),

            // related
            component_types::SYSTEM_SUB_TITLE => $page->system_sub_tile($this->ui_msg_code_id),
            component_types::SYSTEM_SUB_TITLE_VAR => $page->system_sub_tile_var($this->ui_msg_code_id, $dbo->usage, $this->ui_msg_code_id_vars, $this->ui_msg_value_exception, $this->ui_msg_code_id_exception),
            component_types::LIST_PARENTS_OF_WORD => $list->parents_of_word($dbo, $cfg->phrase_list()),
            component_types::LIST_CHILDREN_OF_WORD => $list->children_of_word($dbo, $cfg->phrase_list()),
            component_types::PHRASE_ALIASES => $list->phrase_aliases($dbo, $cfg->phrase_list()),
            component_types::PHRASE_SYMBOLS => $list->phrase_symbols($dbo, $cfg->phrase_list()),
            component_types::LIST_PHRASES_RELATED_EX_SYMBOLS => $list->phrases_related_ex_symbols($dbo, $cfg->phrase_list()),
            component_types::LIST_PHRASES_RELATED_EX_SUBTITLE => $list->phrases_related_ex_subtitle($dbo, $cfg->phrase_list()),
            component_types::LIST_TRIPLES_OF_VERB => $list->triple_list($dbo, $cfg),
            component_types::LIST_VALUES_BY_TRIPLE => $list->values_by_triple($dbo, $cfg),
            component_types::LIST_VALUES_BY_SOURCE => $list->values_by_source($dbo, $cfg),
            component_types::LIST_FORMULAS_OF_VERB => $list->formula_list($dbo, $cfg),
            component_types::LIST_PHRASES_OF_FORMULA => $list->phrases_of_formula($dbo, $cfg),

            // TODO Prio 1 review the components below

            // verb only -
            component_types::VERB_NAME => $base->verb_name($dbo),


            // triple only -
            component_types::TRIPLE_NAME => $base->triple_name($dbo),

            // value only -
            component_types::VALUE_NAME => $base->value_name($dbo),
            component_types::GROUP_NAME => $base->group_name($dbo),
            component_types::VALUE_NUMERIC => $base->num_value($dbo),
            component_types::MAIN_VALUE => $base->main_value($dbo),

            // other
            component_types::FORM_TABLE_LINKED_VIEWS => $form->form_table_linked_view($dbo, $form_name, $cfg->view_list()),


            // view only -
            component_types::SHOW_NAME => $form->show_name($dbo, $this->code_id),
            component_types::SHOW_DESCRIPTION => $form->show_description($dbo),
            component_types::SHOW_PLURAL => $form->show_plural($dbo),
            component_types::SHOW_PHRASE_TYPE => $form->show_phrase_type($dbo),
            component_types::SHOW_FIELD_USAGE => $form->show_usage($dbo),
            component_types::WORD_RESULTS => $form->result($dbo),
            component_types::USED_IN_AS_TEXT => $form->used_as_text($dbo),
            component_types::USED_IN_AS_TEXT_WITH_LINK => $form->used_as_text_link($dbo),
            component_types::RANK_PHRASE => $rank->system_phrases($dbo),
            component_types::RANKING_PARAMETERS => $rank->ranking_parameters($dbo),
            component_types::RANKING_LIST => $rank->ranking_list($dbo),

            // name display components for admin-editable system objects
            component_types::SOURCE_NAME => $base->source_name($dbo),
            component_types::REFERENCE_NAME => $base->reference_name($dbo),
            component_types::LANGUAGE_NAME => $base->language_name($dbo),
            component_types::RESULTS_RELATED => $list->results_related($dbo, $cfg),
            component_types::PHRASES_RELATED => $list->phrases_related($dbo, $cfg),
            component_types::BUTTON_REQUEST => $form->button_request(),
            component_types::SYSTEM_CHANGE_LOG => $log->system_change_log($dbo, $log_lst, $test_mode),
            component_types::USER_SYSTEM_ERRORS => $log->user_system_errors($err_lst, $this->ui_msg_code_id),

            // view relation only -
            component_types::SYSTEM_FIELD_PARENT_VIEW => $form->show_parent_view($dbo),
            component_types::SYSTEM_FIELD_CHILD_VIEW => $form->show_child_view($dbo),
            component_types::SHOW_FIELD_RELATION_TYPE => $form->show_relation_type($dbo),
            component_types::SHOW_FIELD_START_POS => $form->show_start_pos($dbo),

            // base
            component_types::PHRASE => $this->name_tip(),
            component_types::LINK => $link->phrase_link($dbo, $form_name, $cfg->phrase_list()),

            // table
            component_types::VALUES_ALL => $base->all($dbo, $back),
            component_types::VALUES_RELATED => $list->values_by_word($dbo, $cfg, $style_id),
            component_types::VALUE_CHART => $list->value_chart($dbo, $cfg),
            component_types::VIEW_TAB_BOX => $list->view_tab_box($dbo, $test_mode),
            component_types::NUMERIC_VALUE => $list->num_list($dbo, $back),

            // related
            component_types::LIST_REF => $list->ref_list_word($dbo, $cfg),
            component_types::LIST_VIEWS => $list->views_related($dbo, $cfg),
            component_types::LIST_RESULTS => $list->result_list($dbo, $cfg),
            component_types::LINK_LIST_WORD => $list->link_list_word($dbo, $cfg),
            component_types::FORMULAS => $list->formulas($dbo, $cfg, $test_mode),
            //component_type::FORMULA_RESULTS => $list->results($dbo),
            component_types::WORDS_DOWN => $foaf->word_children($dbo),
            component_types::WORDS_UP => $foaf->word_parents($dbo),

            // preview
            component_types::VIEW_AFTER_CHANGE => $preview->view_after($dbo),
            component_types::VIEW_BEFORE_CHANGE => $preview->view_before($dbo),

            // export
            component_types::JSON_EXPORT => $port->json_export($dbo, $back),
            component_types::XML_EXPORT => $port->xml_export($dbo, $back),
            component_types::CSV_EXPORT => $port->csv_export($dbo, $back),
            component_types::ODS_EXPORT => $port->ods_export($dbo, $back),

            component_types::TEXT => $this->text(),

            default => 'program code for component ' . $this->dsp_id() . ' of component type "' . $this->type_code_id($cfg->typ_lst_cache) . '" (id ' . $this->type_id() . ') missing<br>'
        };
        $this->log_debug($this->dsp_id() . ' created');

        // TODO review
        if (str_starts_with($result, 'program code for component')) {
            $this->log_err($result);
        }

        // a test page may stack many form parts that never share a real page; suffix this
        // part's field names/ids with the per-part counter so the stacked ids stay unique;
        // empty in production so the real url vars (name="k") are kept, one form per page
        if ($test_form_unique_id !== '') {
            $result = $this->add_test_form_unique_id($result, $test_form_unique_id);
        }

        // finally add the html style if requested
        return $result;
    }

    /**
     * suffix the name / id / for / list attributes of one rendered form part so several
     * parts stacked on one test page keep unique html ids (the for/list references move
     * with their id); used only by the multi-form test pages, never in production
     * @param string $html the rendered form part
     * @param int|string $test_form_unique_id the per-part counter from the test page
     * @return string the form part with disambiguated field identifiers
     */
    private function add_test_form_unique_id(string $html, int|string $test_form_unique_id): string
    {
        $suffix = '_' . $test_form_unique_id;
        return preg_replace('/(\s(?:name|id|for|list)=")([^"]+)(")/', '${1}${2}' . $suffix . '${3}', $html);
    }

}
