<?php

/*

    shared/types/component_types.php - db based ENUM of the component types
    --------------------------------

    list of the view component types that have a coded functionality
    where *_COM is the description for the tooltip
    until the initial import csv is not yet created based on these const the resources/db_code_links/component_types needs to be inline


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

namespace Zukunft\ZukunftCom\main\php\shared\types;

class component_types
{

    // const names in thematic order
    // *_ID is the fixed database id as defined by the component_list.csv
    // *_COM is the description of the field shown as a tooltip to the user to help selecting the right field


    /*
     * start page
     */


    // the components used for the default start page
    const string PHRASE_NAME = "phrase_name";
    const int PHRASE_NAME_ID = 8;
    const string PHRASE_NAME_COM = "show the name of a phrase fixed when designing the view";

    const string CALC_SHEET = "calc_sheet";
    const string CALC_SHEET_NAME = "spreadsheet";
    const int CALC_SHEET_ID = 35;
    const string CALC_SHEET_COM = "changeable spreadsheet with words, number and formulas that allow changes";


    /*
     * system forms
     */

    // internal fields used in system forms that should not be used for user views
    // general fields used in several views
    const string FORM_TITLE = "system_form_title";
    const int FORM_TITLE_ID = 17;
    // like FORM_TITLE but for non-form display views: shows the object name as the page title
    // plus a fas fa-edit link to the object's edit view; the title text is the object name and
    // is rendered in the user's frontend language by loading the same object in that language
    // (will be done via the sandbox language field once it has been added to all sandbox objects)
    const string TITLE_NAMED_EDIT = "title_of_named_with_edit_link";
    const int TITLE_NAMED_EDIT_ID = 192;
    const string FORM_FIELD_NAME = "system_form_field_name";
    const int FORM_FIELD_NAME_ID = 21;
    const string FORM_FIELD_DESCRIPTION = "system_form_field_description";
    const int FORM_FIELD_DESCRIPTION_ID = 22;

    // select object fields
    const string FORM_SELECT_PHRASE = "form_select_phrase";
    const int FORM_SELECT_PHRASE_ID = 23;
    const string FORM_SELECT_PHRASES = "form_select_multi_phrases";
    const int FORM_SELECT_PHRASES_ID = 75;
    const string FORM_SELECT_VERB = "form_select_verb";
    const int FORM_SELECT_VERB_ID = 24;
    const string FORM_SELECT_VERBS = "form_select_multi_verbs";
    const int FORM_SELECT_VERBS_ID = 76;
    const string FORM_SELECT_SOURCE = "form_select_source";
    const int FORM_SELECT_SOURCE_ID = 118;
    const string FORM_SELECT_SOURCES = "form_select_multi_sources";
    const int FORM_SELECT_SOURCES_ID = 119;
    const string FORM_SELECT_REF = "form_select_ref";
    const int FORM_SELECT_REF_ID = 120;
    const string FORM_SELECT_REFS = "form_select_multi_refs";
    const int FORM_SELECT_REFS_ID = 121;
    const string FORM_FIELD_VALUE = "system_form_field_value";
    const int FORM_FIELD_VALUE_ID = 139;
    const string FORM_SELECT_VALUE = "form_select_value";
    const int FORM_SELECT_VALUE_ID = 81;
    const string FORM_SELECT_VALUES = "form_select_multi_values";
    const int FORM_SELECT_VALUES_ID = 82;
    const string FORM_SELECT_FORMULA = "form_select_formula";
    const int FORM_SELECT_FORMULA_ID = 77;
    const string FORM_SELECT_FORMULAS = "form_select_multi_formulas";
    const int FORM_SELECT_FORMULAS_ID = 78;
    const string FORM_SELECT_TERM = "form_select_term";
    const int FORM_SELECT_TERM_ID = 79;
    const string FORM_SELECT_TERMS = "form_select_multi_terms";
    const int FORM_SELECT_TERMS_ID = 80;
    const string FORM_SELECT_RESULT = "form_select_result";
    const int FORM_SELECT_RESULT_ID = 83;
    const string FORM_SELECT_RESULTS = "form_select_multi_results";
    const int FORM_SELECT_RESULTS_ID = 84;
    const string FORM_SELECT_VIEW = "form_select_view";
    const int FORM_SELECT_VIEW_ID = 85;
    const string FORM_SELECT_VIEWS = "form_select_multi_views";
    const int FORM_SELECT_VIEWS_ID = 86;
    const string FORM_SELECT_PARENT_VIEW = "form_select_parent_view";
    const int FORM_SELECT_PARENT_VIEW_ID = 171;
    const string FORM_SELECT_CHILD_VIEW = "form_select_child_view";
    const int FORM_SELECT_CHILD_VIEW_ID = 172;
    const string FORM_SELECT_COMPONENT = "form_select_component";
    const int FORM_SELECT_COMPONENT_ID = 87;
    const string FORM_SELECT_COMPONENTS = "form_select_multi_components";
    const int FORM_SELECT_COMPONENTS_ID = 88;

    // select access and protection
    const string FORM_SHARE_TYPE = "form_select_share";
    const int FORM_SHARE_TYPE_ID = 26;
    const string FORM_PROTECTION_TYPE = "form_select_protection";
    const int FORM_PROTECTION_TYPE_ID = 27;

    // select object types
    const string FORM_SELECT_PHRASE_TYPE = "form_select_phrase_type";
    const int FORM_SELECT_PHRASE_TYPE_ID = 25;
    const string LIST_VIEWS = "view_list";
    const int LIST_VIEWS_ID = 122;
    const string FORM_SELECT_SOURCE_TYPE = "form_select_source_type";
    const int FORM_SELECT_SOURCE_TYPE_ID = 39;
    const string FORM_SELECT_REF_TYPE = "form_select_ref_type";
    const int FORM_SELECT_REF_TYPE_ID = 48;
    const string FORM_SELECT_VALUE_TYPE = "form_select_value_type";
    const int FORM_SELECT_VALUE_TYPE_ID = 123;
    const string FORM_SELECT_FORMULA_TYPE = "form_select_formula_type";
    const int FORM_SELECT_FORMULA_TYPE_ID = 49;
    const string FORM_SELECT_VIEW_TYPE = "form_select_view_type";
    const int FORM_SELECT_VIEW_TYPE_ID = 50;
    const string FORM_SELECT_VIEW_STYLE = "form_select_view_style";
    const int FORM_SELECT_VIEW_STYLE_ID = 51;
    const string FORM_SELECT_COMPONENT_TYPE = "form_select_component_type";
    const int FORM_SELECT_COMPONENT_TYPE_ID = 52;
    const string FORM_SELECT_COMPONENT_STYLE = "form_select_component_style";
    const int FORM_SELECT_COMPONENT_STYLE_ID = 124;
    const string FORM_SELECT_VIEW_RELATION_TYPE = "form_select_view_relation_type";
    const int FORM_SELECT_VIEW_RELATION_TYPE_ID = 173;
    const string FORM_FIELD_VIEW_RELATION_START_POS = "system_form_field_view_relation_start_pos";
    const int FORM_FIELD_VIEW_RELATION_START_POS_ID = 174;

    // select link types and priority
    const string FORM_SELECT_FORMULA_LINK_TYPE = "form_select_formula_link_type";
    const int FORM_SELECT_FORMULA_LINK_TYPE_ID = 100;
    const string FORM_SELECT_FORMULA_LINK_PRIORITY = "form_select_formula_link_priority";
    const int FORM_SELECT_FORMULA_LINK_PRIORITY_ID = 127;
    const string FORM_SELECT_VIEW_LINK_TYPE = "form_select_view_link_type";
    const int FORM_SELECT_VIEW_LINK_TYPE_ID = 101;
    const string FORM_SELECT_VIEW_LINK_PRIORITY = "form_select_view_link_priority";
    const int FORM_SELECT_VIEW_LINK_PRIORITY_ID = 125;
    const string FORM_SELECT_COMPONENT_LINK_TYPE = "form_select_component_link_type";
    const int FORM_SELECT_COMPONENT_LINK_TYPE_ID = 99;
    const string FORM_SELECT_COMPONENT_POS_TYPE = "form_select_component_position_type";
    const int FORM_SELECT_COMPONENT_POS_TYPE_ID = 153;
    const string FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER = "form_select_component_link_order_number";
    const int FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER_ID = 126;

    // other select fields
    const string FORM_SELECT_VIEW_DEFAULT = "form_select_view_default";
    const int FORM_SELECT_VIEW_DEFAULT_ID = 90;
    const string FORM_SELECT_FILE = "form_select_file";
    const int FORM_SELECT_FILE_ID = 91;
    const string FORM_SELECT_FORMAT_EXPORT = "form_select_export_format";
    const int FORM_SELECT_FORMAT_EXPORT_ID = 92;

    // verb only fields
    const string FORM_FIELD_PLURAL = "system_form_field_plural";
    const int FORM_FIELD_PLURAL_ID = 54;
    const string FORM_FIELD_REVERSE = "system_form_field_reverse";
    const int FORM_FIELD_REVERSE_ID = 135;
    const string FORM_FIELD_PLURAL_REVERSE = "system_form_field_plural_reverse";
    const int FORM_FIELD_PLURAL_REVERSE_ID = 136;
    const string FORM_FIELD_NAME_IN_FORMULAS = "system_form_field_name_in_formulas";
    const int FORM_FIELD_NAME_IN_FORMULAS_ID = 149;

    // verb only fields
    const string SYSTEM_SHOW_REF_TYPE = "system_show_field_ref_type";
    const int FORM_FIELD_REF_TYPE_ID = 163;
    const string SYSTEM_SHOW_REF_KEY = "system_show_field_ref_key";
    const int FORM_FIELD_REF_KEY_ID = 164;
    const string SYSTEM_SHOW_REF_SOURCE = "system_show_field_ref_source";
    const int FORM_FIELD_REF_SOURCE_ID = 165;
    const string SYSTEM_SHOW_REF_URL = "system_show_field_ref_url";
    const int FORM_FIELD_REF_URL_ID = 166;
    const string FORM_FIELD_EXTERNAL_KEY = "system_form_field_external_key";
    const int FORM_FIELD_EXTERNAL_KEY_ID = 167;

    // triple only fields
    const string TRIPLE_NAME = "triple_name";
    const int TRIPLE_NAME_ID = 57;
    const string FORM_FIELD_WEIGHT = "system_form_field_weight";
    const int FORM_FIELD_WEIGHT_ID = 150;

    // value only fields
    const string FORM_FIELD_GROUP = "system_form_field_group";
    const int FORM_FIELD_GROUP_ID = 69;
    const string FORM_FIELD_GROUP_OR_PHRASES = "system_form_field_group_or_phrase_list";
    const int FORM_FIELD_GROUP_OR_PHRASES_ID = 70;

    // result only fields
    const string FORM_FIELD_SOURCE_GROUP = "system_form_field_source_group";
    const int FORM_FIELD_SOURCE_GROUP_ID = 85;
    const string FORM_FIELD_SOURCE_GROUP_OR_PHRASES = "system_form_field_source_group_or_phrase_list";
    const int FORM_FIELD_SOURCE_GROUP_OR_PHRASES_ID = 117;

    // formulas only fields
    const string FORM_FIELD_FORMULA_EXPRESSION = "system_form_field_formula_expression";
    const int FORM_FIELD_FORMULA_EXPRESSION_ID = 53;
    const string EXPRESSION = "expression";
    const int EXPRESSION_ID = 62;
    const string FORM_FIELD_FORMULA_ALL_VAR_NEEDED = "system_form_field_formula_all_vars";
    const int FORM_FIELD_FORMULA_ALL_VAR_NEEDED_ID = 54;
    const string FORM_LIST_FORMULAS = "formula_list_changeable";
    const int FORM_LIST_FORMULAS_ID = 98;

    // for export
    const string FORM_FIELD_SELECTION_NAME = "system_form_selection_name";
    const int FORM_FIELD_SELECTION_NAME_ID = 72;
    const string FORM_FIELD_SELECTION_DESCRIPTION = "system_form_selection_description";
    const int FORM_FIELD_SELECTION_DESCRIPTION_ID = 73;
    const string FORM_FIELD_SELECTION_TEXT = "system_form_selection_text";
    const int FORM_FIELD_SELECTION_TEXT_ID = 74;

    // for external links
    const string FORM_FIELD_URL = "system_form_field_url";
    const int FORM_FIELD_URL_ID = 71;

    // preview of the changes if confirmed
    const string FORM_PREVIEW = "system_form_preview";
    const int FORM_PREVIEW_ID = 89;

    // hidden form fields
    const string FORM_HIDDEN_BACK = "system_form_back_stack";
    const int FORM_HIDDEN_BACK_ID = 18;
    const string FORM_HIDDEN_STEP = "system_form_confirm_status";
    const int FORM_HIDDEN_STEP_ID = 19;

    // admin user edit form fields
    const string ADMIN_FORM_FIELD_USER_NAME = "admin_form_field_username";
    const int ADMIN_FORM_FIELD_USER_NAME_ID = 168;
    const string ADMIN_FORM_FIELD_USER_EMAIL = "admin_form_field_user_email";
    const int ADMIN_FORM_FIELD_USER_EMAIL_ID = 168;
    const string ADMIN_FORM_FIELD_USER_PASSWORD = "admin_form_field_user_password";
    const int ADMIN_FORM_FIELD_USER_PASSWORD_ID = 170;
    const string ADMIN_FORM_FIELD_LANGUAGE_SYMBOL = "admin_form_field_language_symbol";
    const int ADMIN_FORM_FIELD_LANGUAGE_SYMBOL_ID = 179;
    const string FIELD_LANGUAGE_SYMBOL = "field_language_symbol";
    const int FIELD_LANGUAGE_SYMBOL_ID = 180;

    // admin-only system status panels
    const string SYSTEM_ADMIN_URL_DELAY = "system_admin_url_delay";
    const int SYSTEM_ADMIN_URL_DELAY_ID = 181;
    const string SYSTEM_ADMIN_LOGIN_FAILS = "system_admin_login_fails";
    const int SYSTEM_ADMIN_LOGIN_FAILS_ID = 182;
    const string SYSTEM_ADMIN_ERRORS_UNASSIGNED = "system_admin_errors_unassigned";
    const int SYSTEM_ADMIN_ERRORS_UNASSIGNED_ID = 183;
    const string SYSTEM_ADMIN_ERRORS_DELAYED_FIX = "system_admin_errors_delayed_fix";
    const int SYSTEM_ADMIN_ERRORS_DELAYED_FIX_ID = 184;
    const string SYSTEM_ADMIN_JOBS_DELAYED = "system_admin_jobs_delayed";
    const int SYSTEM_ADMIN_JOBS_DELAYED_ID = 185;

    // generic list selector (e.g. languages)
    const string SELECT_LIST = "select_list";
    const int SELECT_LIST_ID = 186;

    // buttons
    const string FORM_BUTTON_CANCEL = "system_button_cancel";
    const int FORM_BUTTON_CANCEL_ID = 29;
    const string FORM_BUTTON_SAVE = "system_button_save";
    const int FORM_BUTTON_SAVE_ID = 30;
    const string FORM_BUTTON_DEL = "system_button_del";
    const int FORM_BUTTON_DEL_ID = 31;
    const string FORM_BUTTON_IMPORT = "system_button_import";
    const int FORM_BUTTON_IMPORT_ID = 94;
    const string FORM_BUTTON_EXPORT = "system_button_export";
    const int FORM_BUTTON_EXPORT_ID = 95;

    // simple close the form section
    const string FORM_END = "form_end";
    const int FORM_END_ID = 32;

    // show changes if confirmed
    const string SYSTEM_SHOW_RESULT_DIFF = "system_show_result_diff";
    const int SYSTEM_SHOW_RESULT_DIFF_ID = 154;

    // TODO Prio 0 group and move the lines below
    const string SYSTEM_PASTE_TABLE_CONTEXT = "system_paste_table_context";
    const int SYSTEM_PASTE_TABLE_CONTEXT_ID = 155;
    const string SYSTEM_PASTE_TABLE_BODY = "system_paste_table_body";
    const int SYSTEM_PASTE_TABLE_BODY_ID = 156;
    const string SYSTEM_SELECTION_TEXT = "system_selection_text";
    const int SYSTEM_SELECTION_TEXT_ID = 157;
    const string SYSTEM_POPUP_TITLE = "system_popup_title";
    const int SYSTEM_POPUP_TITLE_ID = 158;
    const string FORM_CLASS = "form_class";
    const int FORM_CLASS_ID = 159;
    const string FORM_CHANGES = "form_changes";
    const int FORM_CHANGES_ID = 160;
    const string FORM_IMPACT = "form_impact";
    const int FORM_IMPACT_ID = 161;
    const string SYSTEM_SHOW_VIEW_DIFF = "system_show_view_diff";
    const int SYSTEM_SHOW_VIEW_DIFF_ID = 162;

    /*
     * fixed system pages
     */

    const string SYSTEM_TITLE = "system_title";
    const int SYSTEM_TITLE_ID = 63;
    const string SYSTEM_SUB_TITLE = "system_sub_title";
    const int SYSTEM_SUB_TITLE_ID = 145;
    const string SYSTEM_SUB_TITLE_VAR = "system_sub_title_var";
    const int SYSTEM_SUB_TITLE_VAR_ID = 146;
    const string SYSTEM_BODY_ABOUT = "system_body_about";
    const int SYSTEM_BODY_ABOUT_ID = 64;
    const string SYSTEM_BODY_SETUP = "system_body_setup";
    const int SYSTEM_BODY_SETUP_ID = 102;
    const string SYSTEM_BODY_SIGNUP = "system_body_signup";
    const int SYSTEM_BODY_SIGNUP_ID = 103;
    const string SYSTEM_BODY_LOGIN = "system_body_login";
    const int SYSTEM_BODY_LOGIN_ID = 104;
    const string SYSTEM_BODY_LOGIN_ACTIVATE = "system_body_login_activate";
    const int SYSTEM_BODY_LOGIN_ACTIVATE_ID = 105;
    const string SYSTEM_BODY_LOGIN_RESET = "system_body_login_reset";
    const int SYSTEM_BODY_LOGIN_RESET_ID = 106;
    const string SYSTEM_BODY_LOGOUT = "system_body_logout";
    const int SYSTEM_BODY_LOGOUT_ID = 107;
    const string SYSTEM_BODY_SEARCH = "system_body_search";
    const int SYSTEM_BODY_SEARCH_ID = 108;
    const string SYSTEM_BODY_SEARCH_FULL = "system_body_search_full";
    const int SYSTEM_BODY_SEARCH_FULL_ID = 109;
    const string SYSTEM_BODY_VALUE_DETAIL = "system_body_value_detail";
    const int SYSTEM_BODY_VALUE_DETAIL_ID = 110;
    const string SYSTEM_BODY_RESULT_EXPLAIN = "system_body_result_explain";
    const int SYSTEM_BODY_RESULT_EXPLAIN_ID = 111;
    const string SYSTEM_BODY_FORMULA_TEST = "system_body_formula_test";
    const int SYSTEM_BODY_FORMULA_TEST_ID = 112;
    const string SYSTEM_BODY_SANDBOX = "system_body_sandbox";
    const int SYSTEM_BODY_SANDBOX_ID = 113;
    const string SYSTEM_BODY_UNDO = "system_body_undo";
    const int SYSTEM_BODY_UNDO_ID = 114;
    const string SYSTEM_BODY_USER_SETTINGS = "system_body_user_settings";
    const int SYSTEM_BODY_USER_SETTINGS_ID = 115;
    const string SYSTEM_BODY_PROCESS = "system_body_process";
    const int SYSTEM_BODY_PROCESS_ID = 116;
    const string SYSTEM_BODY_PROCESS_LIST = "system_body_process_list";
    const int SYSTEM_BODY_PROCESS_LIST_ID = 68;
    const string SYSTEM_BODY_PROCESS_PROGRESS = "system_body_process_progress";
    const int SYSTEM_BODY_PROCESS_PROGRESS_ID = 67;
    const string SYSTEM_BODY_ERROR_LOG = "system_body_error_log";
    const int SYSTEM_BODY_ERROR_LOG_ID = 65;
    const string SYSTEM_BODY_ERROR_UPDATE = "system_body_error_update";
    const int SYSTEM_BODY_ERROR_UPDATE_ID = 66;


    /*
     * hidden formatting
     */

    // internal components used for formatting
    const string ROW_START = "row_start";
    const int ROW_START_ID = 33;
    const string ROW_RIGHT = "row_right";
    const int ROW_RIGHT_ID = 34;
    const string ROW_END = "row_end";
    const int ROW_END_ID = 35;


    /*
     * components for user view
     */

    // show the word or triple name and give the user the possibility to change the name
    const string SELECT_PHRASE = "select_phrase";
    const int SELECT_PHRASE_ID = 1;
    // select a view
    const string SELECT_VIEW = "select_view";
    const int SELECT_VIEW_ID = 2;

    // show a list of related objects that cannot be changed (e.g. Zurich: is part of Switzerland or Zurich: is a city)
    const string LIST_PARENTS_OF_WORD = "list_parents_of_word";
    const int LIST_PARENTS_OF_WORD_ID = 96;
    const string LIST_CHILDREN_OF_WORD = "list_children_of_word";
    const int LIST_CHILDREN_OF_WORD_ID = 48;
    const string PHRASE_ALIASES = "phrase_aliases";
    const int PHRASE_ALIASES_ID = 194;
    const string PHRASE_ALIASES_COM = "show the phrases that are an alias of the given phrase e.g. 'has aliases: $, U.S. dollar' for 'US dollar'";
    const string PHRASE_SYMBOLS = "phrase_symbols";
    const int PHRASE_SYMBOLS_ID = 195;
    const string PHRASE_SYMBOLS_COM = "show the symbols of the given phrase e.g. 'has symbol: USD' for 'US dollar'";
    const string LIST_PHRASES_RELATED_EX_SYMBOLS = "list_phrases_related_ex_symbols";
    const int LIST_PHRASES_RELATED_EX_SYMBOLS_ID = 196;
    const string LIST_PHRASES_RELATED_EX_SYMBOLS_COM = "list of phrases related to the given phrase excluding the alias and symbol entries";
    const string LIST_TRIPLES_OF_VERB = "triples_by_verb";
    const int LIST_TRIPLES_OF_VERB_ID = 137;
    const string LIST_VALUES_BY_TRIPLE = "values_by_triple";
    const int LIST_VALUES_BY_TRIPLE_ID = 151;
    const string LIST_VALUES_BY_SOURCE = "values_by_source";
    const int LIST_VALUES_BY_SOURCE_ID = 152;
    const string LIST_FORMULAS_OF_VERB = "formulas_by_verb";
    const int LIST_FORMULAS_OF_VERB_ID = 148;
    const string LIST_PHRASES_OF_FORMULA = "list_phrases_of_formula";
    const int LIST_PHRASES_OF_FORMULA_ID = 48;
    const string LIST_REF = "ref_list";
    const int LIST_REF_ID = 42;
    const string LIST_FORMULAS = "formula_list";
    const int LIST_FORMULAS_ID = 12;
    const string LIST_RESULTS = "result_list";
    const int LIST_RESULTS_ID = 97;
    const string LINK_LIST_WORD = "link_list";
    const int LINK_LIST_WORD_ID = 43;
    const string RANK_PHRASE = "usage";
    const int USAGE_WORD_ID = 44;
    const string RANKING_PARAMETERS = "ranking_parameters";
    const int RANKING_PARAMETERS_ID = 45;
    const string RANKING_LIST = "ranking_list";
    const int RANKING_LIST_ID = 46;

    // user components for value
    const string VALUE_NAME = "group_or_phrase_list";
    const int VALUE_NAME_ID = 140;
    const string GROUP_NAME = "group_name";
    const int GROUP_NAME_ID = 141;
    const string VALUE_NUMERIC = "value_numeric";
    const int VALUE_NUMERIC_ID = 142;
    const string MAIN_VALUE = "main_value";
    const int MAIN_VALUE_ID = 38;

    // show the user-specific name of a word or triple with the description on mouseover without allowing to change it
    const string PHRASE = "phrase";
    const int PHRASE_ID = 4;
    const string VERB_NAME = "verb_name";
    const int VERB_NAME_ID = 37;
    const string SOURCE_NAME = "source_name";
    const int SOURCE_NAME_ID = 58;
    const string REFERENCE_NAME = "reference_name";
    const int REFERENCE_NAME_ID = 59;
    const string LANGUAGE_NAME = "language_name";
    const int LANGUAGE_NAME_ID = 60;
    const string RESULTS_RELATED = "results_related";
    const int RESULTS_RELATED_ID = 61;
    const string BUTTON_REQUEST = "button_request";
    const int BUTTON_REQUEST_ID = 187;
    const string PHRASES_RELATED = "phrases_related";
    const int PHRASES_RELATED_ID = 188;

    /*
     * related
     */

    // display a changeable list as a table (e.g. ABB as first word, Cash Flow Statement as second word)
    const string VALUES_RELATED = "values_related";
    const int VALUES_RELATED_ID = 11;
    const string FORM_TABLE_LINKED_VIEWS = "system_form_link_table";
    const int FORM_TABLE_LINKED_VIEWS_ID = 93;

    const string SHOW_NAME = "system_show_field_name";
    const int SHOW_NAME_ID = 20;
    const string SHOW_DESCRIPTION = "system_show_field_description";
    const int SHOW_DESCRIPTION_ID = 147;
    const string SHOW_PLURAL = "system_show_field_plural";
    const int SHOW_PLURAL_ID = 190;
    const string SHOW_PHRASE_TYPE = "system_show_field_phrase_type";
    const int SHOW_PHRASE_TYPE_ID = 191;
    const string SHOW_FIELD_USAGE = "system_show_field_usage";
    const int SHOW_FIELD_USAGE_ID = 128;
    const string SYSTEM_FIELD_PARENT_VIEW = "system_show_parent_view";
    const int SYSTEM_FIELD_PARENT_VIEW_ID = 175;
    const string SYSTEM_FIELD_CHILD_VIEW = "system_show_child_view";
    const int SYSTEM_FIELD_CHILD_VIEW_ID = 176;
    const string SHOW_FIELD_RELATION_TYPE = "system_show_view_relation_type";
    const int SHOW_FIELD_RELATION_TYPE_ID = 177;
    const string SHOW_FIELD_START_POS = "system_show_view_start_pos";
    const int SHOW_FIELD_START_POS_ID = 178;
    const string VIEW_AFTER_CHANGE = "system_show_after_change";
    const int VIEW_AFTER_CHANGE_ID = 143;
    const string VIEW_BEFORE_CHANGE = "system_show_before_change";
    const int VIEW_BEFORE_CHANGE_ID = 144;
    const string WORD_RESULTS = "word_results";
    const int WORD_RESULTS_ID = 85;
    const string USED_IN_AS_TEXT = "used_in_as_text";
    const int USED_IN_AS_TEXT_ID = 129;
    const string USED_IN_AS_TEXT_WITH_LINK = "used_in_as_text_with_link";
    const int USED_IN_AS_TEXT_WITH_LINK_ID = 130;

    const string TEXT = "text";
    const int TEXT_ID = 3;
    const string TEXT_COM = "simply to display a variable text";

    const string SYSTEM_CHANGE_LOG = "change_log";
    const int SYSTEM_CHANGE_LOG_ID = 45;

    const string USER_SYSTEM_ERRORS = "user_system_errors";
    const int USER_SYSTEM_ERRORS_ID = 193;
    const string USER_SYSTEM_ERRORS_COM = "show the most relevant open system errors linked to the user";

    // show all word that this words is based on
    const string WORDS_UP = "word_list_up";
    // show all words that are based on the given start word
    const string WORDS_DOWN = "word_list_down";
    // a word list with some key numbers e.g. all companies with the PE ratio
    const string NUMERIC_VALUE = "word_value_list";
    // shows all: all words that link to the given word and all values related to the given word
    const string VALUES_ALL = "values_all";
    // display all formulas related to the given word
    const string FORMULAS = "formula_list";
    // show a list of formula results related to a word
    const string FORMULA_RESULTS = "formula_results";
    // offer to configure and create an JSON file
    const string JSON_EXPORT = "export_json";
    const int JSON_EXPORT_ID = 132;
    // offer to configure and create an XML file
    const string XML_EXPORT = "export_xml";
    const int XML_EXPORT_ID = 133;
    // offer to configure and create an CSV file
    const string CSV_EXPORT = "export_csv";
    const int CSV_EXPORT_ID = 134;
    // offer to configure and create an CSV file
    const string ODS_EXPORT = "export_ods";
    const int ODS_EXPORT_ID = 131;
    // show a list of words and triples with a link type selector
    const string LINK = "link";



    // a list with all types for the initial load with name, code_id and description
    const array ALL_TYPES = [
        self::PHRASE_NAME,self::PHRASE_NAME_COM,
        self::CALC_SHEET,self::CALC_SHEET_COM
    ];

    // list of component types that should not be used for non system views
    const array SYSTEM_TYPES = array(
        self::FORM_TITLE,
        self::FORM_FIELD_NAME,
        self::FORM_FIELD_DESCRIPTION,
        self::FORM_FIELD_URL,
        self::FORM_FIELD_PLURAL,
        self::FORM_FIELD_FORMULA_EXPRESSION,
        self::EXPRESSION,
        self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED,
        self::FORM_FIELD_WEIGHT,
        self::FORM_FIELD_GROUP,
        self::FORM_FIELD_GROUP_OR_PHRASES,
        self::FORM_FIELD_SOURCE_GROUP,
        self::FORM_FIELD_SOURCE_GROUP_OR_PHRASES,
        self::FORM_FIELD_VALUE,
        self::FORM_FIELD_SELECTION_NAME,
        self::FORM_FIELD_SELECTION_DESCRIPTION,
        self::FORM_FIELD_SELECTION_TEXT,
        self::FORM_SELECT_PHRASE,
        self::FORM_SELECT_PHRASES,
        self::FORM_SELECT_VERB,
        self::FORM_SELECT_VERBS,
        self::FORM_SELECT_SOURCE,
        self::FORM_SELECT_SOURCES,
        self::FORM_SELECT_REF,
        self::FORM_SELECT_REFS,
        self::FORM_SELECT_FORMULA,
        self::FORM_SELECT_FORMULAS,
        self::FORM_SELECT_TERM,
        self::FORM_SELECT_TERMS,
        self::FORM_SELECT_VALUE,
        self::FORM_SELECT_VALUES,
        self::FORM_SELECT_RESULT,
        self::FORM_SELECT_RESULTS,
        self::FORM_SELECT_VIEW,
        self::FORM_SELECT_VIEWS,
        self::FORM_SELECT_PARENT_VIEW,
        self::FORM_SELECT_CHILD_VIEW,
        self::FORM_SELECT_COMPONENT,
        self::FORM_SELECT_COMPONENTS,
        self::FORM_SELECT_VIEW_DEFAULT,
        self::FORM_SELECT_FILE,
        self::FORM_SELECT_FORMAT_EXPORT,
        self::FORM_SELECT_PHRASE_TYPE,
        self::LIST_VIEWS,
        self::FORM_SELECT_SOURCE_TYPE,
        self::FORM_SELECT_REF_TYPE,
        self::FORM_SELECT_VALUE_TYPE,
        self::FORM_SELECT_FORMULA_TYPE,
        self::FORM_SELECT_VIEW_TYPE,
        self::FORM_SELECT_VIEW_STYLE,
        self::FORM_SELECT_COMPONENT_TYPE,
        self::FORM_SELECT_COMPONENT_STYLE,
        self::FORM_SELECT_VIEW_RELATION_TYPE,
        self::FORM_FIELD_VIEW_RELATION_START_POS,
        self::FORM_SELECT_FORMULA_LINK_TYPE,
        self::FORM_SELECT_FORMULA_LINK_PRIORITY,
        self::FORM_SELECT_VIEW_LINK_TYPE,
        self::FORM_SELECT_VIEW_LINK_PRIORITY,
        self::FORM_SELECT_COMPONENT_LINK_TYPE,
        self::FORM_SELECT_COMPONENT_POS_TYPE,
        self::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER,
        self::FORM_SHARE_TYPE,
        self::FORM_PROTECTION_TYPE,
        self::FORM_TABLE_LINKED_VIEWS,
        self::FORM_BUTTON_CANCEL,
        self::FORM_BUTTON_SAVE,
        self::FORM_BUTTON_DEL,
        self::FORM_BUTTON_IMPORT,
        self::FORM_BUTTON_EXPORT,
        self::FORM_END,
        self::ROW_START,
        self::ROW_RIGHT,
        self::ROW_END,
        self::FORM_HIDDEN_BACK,
        self::FORM_HIDDEN_STEP,
        self::ADMIN_FORM_FIELD_USER_NAME,
        self::ADMIN_FORM_FIELD_USER_EMAIL,
        self::ADMIN_FORM_FIELD_USER_PASSWORD,
        self::ADMIN_FORM_FIELD_LANGUAGE_SYMBOL,
        self::FIELD_LANGUAGE_SYMBOL,
        self::SYSTEM_ADMIN_URL_DELAY,
        self::SYSTEM_ADMIN_LOGIN_FAILS,
        self::SYSTEM_ADMIN_ERRORS_UNASSIGNED,
        self::SYSTEM_ADMIN_ERRORS_DELAYED_FIX,
        self::SYSTEM_ADMIN_JOBS_DELAYED,
        self::SELECT_LIST,
        self::SHOW_NAME,
        self::SHOW_DESCRIPTION,
        self::SHOW_FIELD_USAGE,
        self::SYSTEM_FIELD_PARENT_VIEW,
        self::SYSTEM_FIELD_CHILD_VIEW,
        self::SHOW_FIELD_RELATION_TYPE,
        self::SHOW_FIELD_START_POS,
        self::SYSTEM_SHOW_RESULT_DIFF,
        self::SYSTEM_PASTE_TABLE_CONTEXT,
        self::SYSTEM_PASTE_TABLE_BODY,
        self::SYSTEM_SELECTION_TEXT,
        self::SYSTEM_POPUP_TITLE,
        self::FORM_CLASS,
        self::FORM_CHANGES,
        self::FORM_IMPACT,
        self::SYSTEM_SHOW_VIEW_DIFF,
        self::VIEW_AFTER_CHANGE,
        self::VIEW_BEFORE_CHANGE,
        self::WORD_RESULTS,
        self::USED_IN_AS_TEXT,
        self::USED_IN_AS_TEXT_WITH_LINK,
        self::SYSTEM_TITLE
    );

    // list of component types that are a button
    const array BUTTON_TYPES = array(
        self::FORM_BUTTON_CANCEL,
        self::FORM_BUTTON_SAVE,
        self::FORM_BUTTON_DEL,
        self::FORM_BUTTON_IMPORT,
        self::FORM_BUTTON_EXPORT,
        self::BUTTON_REQUEST
    );

    // list of component types that are hidden
    const array HIDDEN_TYPES = array(
        self::ROW_START,
        self::FORM_TITLE,
        self::FORM_HIDDEN_BACK,
        self::FORM_HIDDEN_STEP,
        self::FORM_END,
        self::ROW_START,
        self::ROW_RIGHT,
        self::ROW_END
    );

    // list of component types that are used to combine lists
    const array LIST_GROUP = array(
        self::SYSTEM_SUB_TITLE,
        self::SYSTEM_SUB_TITLE_VAR,
        self::VALUES_RELATED,
        self::LIST_REF,
    );

    // list of component types that are used to combine lists
    const array LIST_HAS_STYLE = array(
        self::FORM_FIELD_NAME,
        self::FORM_FIELD_PLURAL,
        self::FORM_FIELD_REVERSE,
        self::FORM_FIELD_PLURAL_REVERSE,
        self::FORM_FIELD_NAME_IN_FORMULAS,
        self::FORM_FIELD_WEIGHT,
        self::FORM_FIELD_VALUE,
        self::ROW_START,
        self::ROW_RIGHT,
        self::ROW_END,
    );

    // list of the component types used for unit testing
    // in order of const definition which should be also the thematic order
    // TODO align with component_types.csv
    const array TEST_TYPES = array(
        [self::PHRASE_NAME, self::PHRASE_NAME_ID],
        [self::CALC_SHEET, self::CALC_SHEET_ID],
        [self::FORM_TITLE, self::FORM_TITLE_ID],
        [self::TITLE_NAMED_EDIT, self::TITLE_NAMED_EDIT_ID],
        [self::FORM_FIELD_NAME, self::FORM_FIELD_NAME_ID],
        [self::FORM_FIELD_DESCRIPTION, self::FORM_FIELD_DESCRIPTION_ID],
        [self::FORM_FIELD_URL, self::FORM_FIELD_URL_ID],
        [self::FORM_FIELD_PLURAL, self::FORM_FIELD_PLURAL_ID],
        [self::FORM_FIELD_REVERSE, self::FORM_FIELD_REVERSE_ID],
        [self::FORM_FIELD_PLURAL_REVERSE, self::FORM_FIELD_PLURAL_REVERSE_ID],
        [self::FORM_FIELD_NAME_IN_FORMULAS, self::FORM_FIELD_NAME_IN_FORMULAS_ID],
        [self::SYSTEM_SHOW_REF_TYPE, self::FORM_FIELD_REF_TYPE_ID],
        [self::SYSTEM_SHOW_REF_KEY, self::FORM_FIELD_REF_KEY_ID],
        [self::SYSTEM_SHOW_REF_SOURCE, self::FORM_FIELD_REF_SOURCE_ID],
        [self::SYSTEM_SHOW_REF_URL, self::FORM_FIELD_REF_URL_ID],
        [self::FORM_FIELD_EXTERNAL_KEY, self::FORM_FIELD_EXTERNAL_KEY_ID],
        [self::FORM_FIELD_WEIGHT, self::FORM_FIELD_WEIGHT_ID],
        [self::FORM_FIELD_FORMULA_EXPRESSION, self::FORM_FIELD_FORMULA_EXPRESSION_ID],
        [self::EXPRESSION, self::EXPRESSION_ID],
        [self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED, self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED_ID],
        [self::FORM_LIST_FORMULAS, self::FORM_LIST_FORMULAS_ID],
        [self::FORM_FIELD_GROUP, self::FORM_FIELD_GROUP_ID],
        [self::FORM_FIELD_GROUP_OR_PHRASES, self::FORM_FIELD_GROUP_OR_PHRASES_ID],
        [self::FORM_FIELD_SOURCE_GROUP, self::FORM_FIELD_SOURCE_GROUP_ID],
        [self::FORM_FIELD_SOURCE_GROUP_OR_PHRASES, self::FORM_FIELD_SOURCE_GROUP_OR_PHRASES_ID],
        [self::FORM_FIELD_VALUE, self::FORM_FIELD_VALUE_ID],
        [self::FORM_FIELD_SELECTION_NAME, self::FORM_FIELD_SELECTION_NAME_ID],
        [self::FORM_FIELD_SELECTION_DESCRIPTION, self::FORM_FIELD_SELECTION_DESCRIPTION_ID],
        [self::FORM_FIELD_SELECTION_TEXT, self::FORM_FIELD_SELECTION_TEXT_ID],
        [self::FORM_SELECT_PHRASE, self::FORM_SELECT_PHRASE_ID],
        [self::FORM_SELECT_PHRASES, self::FORM_SELECT_PHRASES_ID],
        [self::FORM_SELECT_VERB, self::FORM_SELECT_VERB_ID],
        [self::FORM_SELECT_VERBS, self::FORM_SELECT_VERBS_ID],
        [self::FORM_SELECT_SOURCE, self::FORM_SELECT_SOURCE_ID],
        [self::FORM_SELECT_SOURCES, self::FORM_SELECT_SOURCES_ID],
        [self::FORM_SELECT_REF, self::FORM_SELECT_REF_ID],
        [self::FORM_SELECT_REFS, self::FORM_SELECT_REFS_ID],
        [self::FORM_SELECT_FORMULA, self::FORM_SELECT_FORMULA_ID],
        [self::FORM_SELECT_FORMULAS, self::FORM_SELECT_FORMULAS_ID],
        [self::FORM_SELECT_TERM, self::FORM_SELECT_TERM_ID],
        [self::FORM_SELECT_TERMS, self::FORM_SELECT_TERMS_ID],
        [self::FORM_SELECT_VALUE, self::FORM_SELECT_VALUE_ID],
        [self::FORM_SELECT_VALUES, self::FORM_SELECT_VALUES_ID],
        [self::FORM_SELECT_RESULT, self::FORM_SELECT_RESULT_ID],
        [self::FORM_SELECT_RESULTS, self::FORM_SELECT_RESULTS_ID],
        [self::FORM_SELECT_VIEW, self::FORM_SELECT_VIEW_ID],
        [self::FORM_SELECT_VIEWS, self::FORM_SELECT_VIEWS_ID],
        [self::FORM_SELECT_PARENT_VIEW, self::FORM_SELECT_PARENT_VIEW_ID],
        [self::FORM_SELECT_CHILD_VIEW, self::FORM_SELECT_CHILD_VIEW_ID],
        [self::FORM_SELECT_COMPONENT, self::FORM_SELECT_COMPONENT_ID],
        [self::FORM_SELECT_COMPONENTS, self::FORM_SELECT_COMPONENTS_ID],
        [self::FORM_SELECT_VIEW_DEFAULT, self::FORM_SELECT_VIEW_DEFAULT_ID],
        [self::FORM_SELECT_FILE, self::FORM_SELECT_FILE_ID],
        [self::FORM_SELECT_FORMAT_EXPORT, self::FORM_SELECT_FORMAT_EXPORT_ID],
        [self::FORM_SELECT_PHRASE_TYPE, self::FORM_SELECT_PHRASE_TYPE_ID],
        [self::FORM_SELECT_SOURCE_TYPE, self::FORM_SELECT_SOURCE_TYPE_ID],
        [self::FORM_SELECT_REF_TYPE, self::FORM_SELECT_REF_TYPE_ID],
        [self::FORM_SELECT_VALUE_TYPE, self::FORM_SELECT_VALUE_TYPE_ID],
        [self::FORM_SELECT_FORMULA_TYPE, self::FORM_SELECT_FORMULA_TYPE_ID],
        [self::FORM_SELECT_VIEW_TYPE, self::FORM_SELECT_VIEW_TYPE_ID],
        [self::FORM_SELECT_VIEW_STYLE, self::FORM_SELECT_VIEW_STYLE_ID],
        [self::FORM_SELECT_COMPONENT_TYPE, self::FORM_SELECT_COMPONENT_TYPE_ID],
        [self::FORM_SELECT_COMPONENT_STYLE, self::FORM_SELECT_COMPONENT_STYLE_ID],
        [self::FORM_SELECT_VIEW_RELATION_TYPE, self::FORM_SELECT_VIEW_RELATION_TYPE_ID],
        [self::FORM_FIELD_VIEW_RELATION_START_POS, self::FORM_FIELD_VIEW_RELATION_START_POS_ID],
        [self::FORM_SELECT_FORMULA_LINK_TYPE, self::FORM_SELECT_FORMULA_LINK_TYPE_ID],
        [self::FORM_SELECT_FORMULA_LINK_PRIORITY, self::FORM_SELECT_FORMULA_LINK_PRIORITY_ID],
        [self::FORM_SELECT_VIEW_LINK_TYPE, self::FORM_SELECT_VIEW_LINK_TYPE_ID],
        [self::FORM_SELECT_VIEW_LINK_PRIORITY, self::FORM_SELECT_VIEW_LINK_PRIORITY_ID],
        [self::FORM_SELECT_COMPONENT_LINK_TYPE, self::FORM_SELECT_COMPONENT_LINK_TYPE_ID],
        [self::FORM_SELECT_COMPONENT_POS_TYPE, self::FORM_SELECT_COMPONENT_POS_TYPE_ID],
        [self::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER, self::FORM_FIELD_COMPONENT_LINK_ORDER_NUMBER_ID],
        [self::FORM_SHARE_TYPE, self::FORM_SHARE_TYPE_ID],
        [self::FORM_PROTECTION_TYPE, self::FORM_PROTECTION_TYPE_ID],
        [self::FORM_TABLE_LINKED_VIEWS, self::FORM_TABLE_LINKED_VIEWS_ID],
        [self::FORM_BUTTON_CANCEL, self::FORM_BUTTON_CANCEL_ID],
        [self::FORM_BUTTON_SAVE, self::FORM_BUTTON_SAVE_ID],
        [self::FORM_BUTTON_DEL, self::FORM_BUTTON_DEL_ID],
        [self::FORM_BUTTON_IMPORT, self::FORM_BUTTON_IMPORT_ID],
        [self::FORM_BUTTON_EXPORT, self::FORM_BUTTON_EXPORT_ID],
        [self::FORM_PREVIEW, self::FORM_PREVIEW_ID],
        [self::FORM_HIDDEN_BACK, self::FORM_HIDDEN_BACK_ID],
        [self::FORM_HIDDEN_STEP, self::FORM_HIDDEN_STEP_ID],
        [self::ADMIN_FORM_FIELD_USER_NAME, self::ADMIN_FORM_FIELD_USER_NAME_ID],
        [self::ADMIN_FORM_FIELD_USER_EMAIL, self::ADMIN_FORM_FIELD_USER_EMAIL_ID],
        [self::ADMIN_FORM_FIELD_USER_PASSWORD, self::ADMIN_FORM_FIELD_USER_PASSWORD_ID],
        [self::ADMIN_FORM_FIELD_LANGUAGE_SYMBOL, self::ADMIN_FORM_FIELD_LANGUAGE_SYMBOL_ID],
        [self::FIELD_LANGUAGE_SYMBOL, self::FIELD_LANGUAGE_SYMBOL_ID],
        [self::SYSTEM_ADMIN_URL_DELAY, self::SYSTEM_ADMIN_URL_DELAY_ID],
        [self::SYSTEM_ADMIN_LOGIN_FAILS, self::SYSTEM_ADMIN_LOGIN_FAILS_ID],
        [self::SYSTEM_ADMIN_ERRORS_UNASSIGNED, self::SYSTEM_ADMIN_ERRORS_UNASSIGNED_ID],
        [self::SYSTEM_ADMIN_ERRORS_DELAYED_FIX, self::SYSTEM_ADMIN_ERRORS_DELAYED_FIX_ID],
        [self::SYSTEM_ADMIN_JOBS_DELAYED, self::SYSTEM_ADMIN_JOBS_DELAYED_ID],
        [self::SELECT_LIST, self::SELECT_LIST_ID],
        [self::FORM_END, self::FORM_END_ID],
        [self::ROW_START, self::ROW_START_ID],
        [self::ROW_RIGHT, self::ROW_RIGHT_ID],
        [self::ROW_END, self::ROW_END_ID],
        [self::SYSTEM_TITLE, self::SYSTEM_TITLE_ID],
        [self::SYSTEM_SUB_TITLE, self::SYSTEM_SUB_TITLE_ID],
        [self::SYSTEM_SUB_TITLE_VAR, self::SYSTEM_SUB_TITLE_VAR_ID],
        [self::SYSTEM_BODY_ABOUT, self::SYSTEM_BODY_ABOUT_ID],
        [self::SYSTEM_BODY_SETUP, self::SYSTEM_BODY_SETUP_ID],
        [self::SYSTEM_BODY_SIGNUP, self::SYSTEM_BODY_SIGNUP_ID],
        [self::SYSTEM_BODY_LOGIN, self::SYSTEM_BODY_LOGIN_ID],
        [self::SYSTEM_BODY_LOGIN_ACTIVATE, self::SYSTEM_BODY_LOGIN_ACTIVATE_ID],
        [self::SYSTEM_BODY_LOGIN_RESET, self::SYSTEM_BODY_LOGIN_RESET_ID],
        [self::SYSTEM_BODY_LOGOUT, self::SYSTEM_BODY_LOGOUT_ID],
        [self::SYSTEM_BODY_SEARCH, self::SYSTEM_BODY_SEARCH_ID],
        [self::SYSTEM_BODY_SEARCH_FULL, self::SYSTEM_BODY_SEARCH_FULL_ID],
        [self::SYSTEM_BODY_VALUE_DETAIL, self::SYSTEM_BODY_VALUE_DETAIL_ID],
        [self::SYSTEM_BODY_RESULT_EXPLAIN, self::SYSTEM_BODY_RESULT_EXPLAIN_ID],
        [self::SYSTEM_BODY_FORMULA_TEST, self::SYSTEM_BODY_FORMULA_TEST_ID],
        [self::SYSTEM_BODY_SANDBOX, self::SYSTEM_BODY_SANDBOX_ID],
        [self::SYSTEM_BODY_UNDO, self::SYSTEM_BODY_UNDO_ID],
        [self::SYSTEM_BODY_USER_SETTINGS, self::SYSTEM_BODY_USER_SETTINGS_ID],
        [self::SYSTEM_BODY_PROCESS, self::SYSTEM_BODY_PROCESS_ID],
        [self::SYSTEM_BODY_ERROR_LOG, self::SYSTEM_BODY_ERROR_LOG_ID],
        [self::SYSTEM_BODY_ERROR_UPDATE, self::SYSTEM_BODY_ERROR_UPDATE_ID],
        [self::SYSTEM_BODY_PROCESS_PROGRESS, self::SYSTEM_BODY_PROCESS_PROGRESS_ID],
        [self::SYSTEM_BODY_PROCESS_LIST, self::SYSTEM_BODY_PROCESS_LIST_ID],
        [self::SELECT_PHRASE, self::SELECT_PHRASE_ID],
        [self::SELECT_VIEW, self::SELECT_VIEW_ID],
        [self::LIST_PARENTS_OF_WORD, self::LIST_PARENTS_OF_WORD_ID],
        [self::LIST_CHILDREN_OF_WORD, self::LIST_CHILDREN_OF_WORD_ID],
        [self::PHRASE_ALIASES, self::PHRASE_ALIASES_ID],
        [self::PHRASE_SYMBOLS, self::PHRASE_SYMBOLS_ID],
        [self::LIST_PHRASES_RELATED_EX_SYMBOLS, self::LIST_PHRASES_RELATED_EX_SYMBOLS_ID],
        [self::LIST_TRIPLES_OF_VERB, self::LIST_TRIPLES_OF_VERB_ID],
        [self::LIST_FORMULAS_OF_VERB, self::LIST_FORMULAS_OF_VERB_ID],
        [self::LIST_VALUES_BY_TRIPLE, self::LIST_VALUES_BY_TRIPLE_ID],
        [self::LIST_VALUES_BY_SOURCE, self::LIST_VALUES_BY_SOURCE_ID],
        [self::LIST_PHRASES_OF_FORMULA, self::LIST_PHRASES_OF_FORMULA_ID],
        [self::LIST_REF, self::LIST_REF_ID],
        [self::LIST_FORMULAS, self::LIST_FORMULAS_ID],
        [self::LIST_RESULTS, self::LIST_RESULTS_ID],
        [self::LIST_VIEWS, self::LIST_VIEWS_ID],
        [self::LINK_LIST_WORD, self::LINK_LIST_WORD_ID],
        [self::RANK_PHRASE, self::USAGE_WORD_ID],
        [self::RANKING_PARAMETERS, self::RANKING_PARAMETERS_ID],
        [self::RANKING_LIST, self::RANKING_LIST_ID],
        [self::SOURCE_NAME, self::SOURCE_NAME_ID],
        [self::REFERENCE_NAME, self::REFERENCE_NAME_ID],
        [self::LANGUAGE_NAME, self::LANGUAGE_NAME_ID],
        [self::RESULTS_RELATED, self::RESULTS_RELATED_ID],
        [self::BUTTON_REQUEST, self::BUTTON_REQUEST_ID],
        [self::PHRASES_RELATED, self::PHRASES_RELATED_ID],
        [self::TRIPLE_NAME, self::TRIPLE_NAME_ID],
        [self::VALUE_NAME, self::VALUE_NAME_ID],
        [self::GROUP_NAME, self::GROUP_NAME_ID],
        [self::VALUE_NUMERIC, self::VALUE_NUMERIC_ID],
        [self::MAIN_VALUE, self::MAIN_VALUE_ID],
        [self::PHRASE, self::PHRASE_ID],
        [self::VERB_NAME, self::VERB_NAME_ID],
        [self::VALUES_RELATED, self::VALUES_RELATED_ID],
        [self::SHOW_NAME, self::SHOW_NAME_ID],
        [self::SHOW_DESCRIPTION, self::SHOW_DESCRIPTION_ID],
        [self::SHOW_PLURAL, self::SHOW_PLURAL_ID],
        [self::SHOW_PHRASE_TYPE, self::SHOW_PHRASE_TYPE_ID],
        [self::SHOW_FIELD_USAGE, self::SHOW_FIELD_USAGE_ID],
        [self::SYSTEM_FIELD_PARENT_VIEW, self::SYSTEM_FIELD_PARENT_VIEW_ID],
        [self::SYSTEM_FIELD_CHILD_VIEW, self::SYSTEM_FIELD_CHILD_VIEW_ID],
        [self::SHOW_FIELD_RELATION_TYPE, self::SHOW_FIELD_RELATION_TYPE_ID],
        [self::SHOW_FIELD_START_POS, self::SHOW_FIELD_START_POS_ID],
        [self::SYSTEM_SHOW_RESULT_DIFF, self::SYSTEM_SHOW_RESULT_DIFF_ID],
        [self::SYSTEM_PASTE_TABLE_CONTEXT, self::SYSTEM_PASTE_TABLE_CONTEXT_ID],
        [self::SYSTEM_PASTE_TABLE_BODY, self::SYSTEM_PASTE_TABLE_BODY_ID],
        [self::SYSTEM_SELECTION_TEXT, self::SYSTEM_SELECTION_TEXT_ID],
        [self::SYSTEM_POPUP_TITLE, self::SYSTEM_POPUP_TITLE_ID],
        [self::FORM_CLASS, self::FORM_CLASS_ID],
        [self::FORM_CHANGES, self::FORM_CHANGES_ID],
        [self::FORM_IMPACT, self::FORM_IMPACT_ID],
        [self::SYSTEM_SHOW_VIEW_DIFF, self::SYSTEM_SHOW_VIEW_DIFF_ID],
        [self::VIEW_AFTER_CHANGE, self::VIEW_AFTER_CHANGE_ID],
        [self::VIEW_BEFORE_CHANGE, self::VIEW_BEFORE_CHANGE_ID],
        [self::WORD_RESULTS, self::WORD_RESULTS_ID],
        [self::USED_IN_AS_TEXT, self::USED_IN_AS_TEXT_ID],
        [self::USED_IN_AS_TEXT_WITH_LINK, self::USED_IN_AS_TEXT_WITH_LINK_ID],
        [self::TEXT, self::TEXT_ID],
        [self::JSON_EXPORT, self::JSON_EXPORT_ID],
        [self::XML_EXPORT, self::XML_EXPORT_ID],
        [self::CSV_EXPORT, self::CSV_EXPORT_ID],
        [self::ODS_EXPORT, self::ODS_EXPORT_ID],
        [self::SYSTEM_CHANGE_LOG, self::SYSTEM_CHANGE_LOG_ID],
        [self::USER_SYSTEM_ERRORS, self::USER_SYSTEM_ERRORS_ID]
    );

}
