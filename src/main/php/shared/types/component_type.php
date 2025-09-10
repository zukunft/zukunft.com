<?php

/*

    shared/types/component_type.php - db based ENUM of the component types
    -------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class component_type
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

    const string CALC_SHEET = "calc_sheet";
    const int CALC_SHEET_ID = 35;
    const string CALC_SHEET_COM = "changeable spreadsheet with words, number and formulas that allow changes";


    /*
     * system form
     */

    // internal fields used in system forms that should not be used for user views
    const string FORM_TITLE = "system_form_title";
    const int FORM_TITLE_ID = 17;
    const string FORM_FIELD_NAME = "system_form_field_name";
    const int FORM_FIELD_NAME_ID = 21;
    const string FORM_FIELD_DESCRIPTION = "system_form_field_description";
    const int FORM_FIELD_DESCRIPTION_ID = 22;
    const string FORM_FIELD_URL = "system_form_field_url";
    const int FORM_FIELD_URL_ID = 71;
    const string FORM_FIELD_PLURAL = "system_form_field_plural";
    const int FORM_FIELD_PLURAL_ID = 54;
    const string FORM_FIELD_FORMULA_EXPRESSION = "system_form_field_formula_expression";
    const int FORM_FIELD_FORMULA_EXPRESSION_ID = 53;
    const string FORM_FIELD_FORMULA_ALL_VAR_NEEDED = "system_form_field_formula_all_vars";
    const int FORM_FIELD_FORMULA_ALL_VAR_NEEDED_ID = 54;
    const string FORM_FIELD_GROUP = "system_form_field_group";
    const int FORM_FIELD_GROUP_ID = 69;
    const string FORM_FIELD_GROUP_OR_PHRASES = "system_form_field_group_or_phrase_list";
    const int FORM_FIELD_GROUP_OR_PHRASES_ID = 70;
    const string FORM_FIELD_SELECTION_NAME = "system_form_selection_name";
    const int FORM_FIELD_SELECTION_NAME_ID = 72;
    const string FORM_FIELD_SELECTION_DESCRIPTION = "system_form_selection_description";
    const int FORM_FIELD_SELECTION_DESCRIPTION_ID = 73;
    const string FORM_FIELD_SELECTION_TEXT = "system_form_selection_text";
    const int FORM_FIELD_SELECTION_TEXT_ID = 74;
    const string FORM_VIEW_SELECT = "form_view_select";
    const int FORM_VIEW_SELECT_ID = 41;
    const string FORM_SELECT_PHRASE = "system_form_select_phrase";
    const int FORM_SELECT_PHRASE_ID = 23;
    const string FORM_SELECT_PHRASES = "system_form_select_multi_phrases";
    const int FORM_SELECT_PHRASES_ID = 75;
    const string FORM_SELECT_VERB = "system_form_select_verb";
    const int FORM_SELECT_VERB_ID = 24;
    const string FORM_SELECT_VERBS = "system_form_select_multi_verbs";
    const int FORM_SELECT_VERBS_ID = 76;
    const string FORM_SELECT_FORMULA = "system_form_select_formula";
    const int FORM_SELECT_FORMULA_ID = 77;
    const string FORM_SELECT_FORMULAS = "system_form_select_multi_formulas";
    const int FORM_SELECT_FORMULAS_ID = 78;
    const string FORM_SELECT_TERM = "system_form_select_term";
    const int FORM_SELECT_TERM_ID = 79;
    const string FORM_SELECT_TERMS = "system_form_select_multi_terms";
    const int FORM_SELECT_TERMS_ID = 80;
    const string FORM_SELECT_VALUE = "system_form_select_value";
    const int FORM_SELECT_VALUE_ID = 81;
    const string FORM_SELECT_VALUES = "system_form_select_multi_values";
    const int FORM_SELECT_VALUES_ID = 82;
    const string FORM_SELECT_RESULT = "system_form_select_result";
    const int FORM_SELECT_RESULT_ID = 83;
    const string FORM_SELECT_RESULTS = "system_form_select_multi_results";
    const int FORM_SELECT_RESULTS_ID = 84;
    const string FORM_SELECT_VIEW = "system_form_select_view";
    const int FORM_SELECT_VIEW_ID = 85;
    const string FORM_SELECT_VIEWS = "system_form_select_multi_views";
    const int FORM_SELECT_VIEWS_ID = 86;
    const string FORM_SELECT_COMPONENT = "system_form_select_component";
    const int FORM_SELECT_COMPONENT_ID = 87;
    const string FORM_SELECT_COMPONENTS = "system_form_select_multi_components";
    const int FORM_SELECT_COMPONENTS_ID = 88;
    const string FORM_SELECT_VIEW_DEFAULT = "system_form_select_view_default";
    const int FORM_SELECT_VIEW_DEFAULT_ID = 90;
    const string FORM_SELECT_FILE = "system_form_select_file";
    const int FORM_SELECT_FILE_ID = 91;
    const string FORM_SELECT_FORMAT_EXPORT = "system_form_select_export_format";
    const int FORM_SELECT_FORMAT_EXPORT_ID = 92;
    const string FORM_SELECT_PHRASE_TYPE = "system_form_select_phrase_type";
    const int FORM_SELECT_PHRASE_TYPE_ID = 25;
    const string FORM_SELECT_SOURCE_TYPE = "system_form_select_source_type";
    const int FORM_SELECT_SOURCE_TYPE_ID = 39;
    const string FORM_SELECT_REF_TYPE = "system_form_select_ref_type";
    const int FORM_SELECT_REF_TYPE_ID = 48;
    const string FORM_SELECT_FORMULA_TYPE = "system_form_select_formula_type";
    const int FORM_SELECT_FORMULA_TYPE_ID = 49;
    const string FORM_SELECT_VIEW_TYPE = "system_form_select_view_type";
    const int FORM_SELECT_VIEW_TYPE_ID = 50;
    const string FORM_SELECT_VIEW_STYLE = "system_form_select_view_style";
    const int FORM_SELECT_VIEW_STYLE_ID = 51;
    const string FORM_SELECT_COMPONENT_TYPE = "system_form_select_component_type";
    const int FORM_SELECT_COMPONENT_TYPE_ID = 52;
    const string FORM_SELECT_FORMULA_LINK_TYPE = "system_form_select_formula_link_type";
    const int FORM_SELECT_FORMULA_LINK_TYPE_ID = 100;
    const string FORM_SELECT_VIEW_LINK_TYPE = "system_form_select_view_link_type";
    const int FORM_SELECT_VIEW_LINK_TYPE_ID = 101;
    const string FORM_SELECT_COMPONENT_LINK_TYPE = "system_form_select_component_link_type";
    const int FORM_SELECT_VIEW_COMPONENT_TYPE_ID = 99;
    const string FORM_SHARE_TYPE = "system_form_select_share";
    const int FORM_SHARE_TYPE_ID = 26;
    const string FORM_PROTECTION_TYPE = "system_form_select_protection";
    const int FORM_PROTECTION_TYPE_ID = 27;
    const string FORM_TABLE_LINKED_VIEWS = "system_form_link_table";
    const int FORM_TABLE_LINKED_VIEWS_ID = 93;
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

    // preview related to form fields
    const string FORM_PREVIEW = "system_form_preview";
    const int FORM_PREVIEW_ID = 89;

    // hidden form fields
    const string FORM_HIDDEN_BACK = "system_form_back_stack";
    const int FORM_HIDDEN_BACK_ID = 18;
    const string FORM_HIDDEN_STEP = "system_form_confirm_status";
    const int FORM_HIDDEN_STEP_ID = 19;

    // simple close the form section
    const string FORM_END = "form_end";
    const int FORM_END_ID = 32;


    /*
     * hidden
     */

    // internal components used for formatting
    const string ROW_START = "row_start";
    const int ROW_START_ID = 33;
    const string ROW_RIGHT = "row_right";
    const int ROW_RIGHT_ID = 34;
    const string ROW_END = "row_end";
    const int ROW_END_ID = 35;


    /*
     * fixed system pages
     */

    const string SYSTEM_TITLE = "system_title";
    const int SYSTEM_TITLE_ID = 63;
    const string SYSTEM_BODY_ABOUT = "system_body_about";
    const int SYSTEM_BODY_ABOUT_ID = 64;
    const string SYSTEM_BODY_ERROR_LOG = "system_body_error_log";
    const int SYSTEM_BODY_ERROR_LOG_ID = 65;
    const string SYSTEM_BODY_ERROR_UPDATE = "system_body_error_update";
    const int SYSTEM_BODY_ERROR_UPDATE_ID = 66;
    const string SYSTEM_BODY_PROCESS_PROGRESS = "system_body_process_progress";
    const int SYSTEM_BODY_PROCESS_PROGRESS_ID = 67;
    const string SYSTEM_BODY_PROCESS_LIST = "system_body_process_list";
    const int SYSTEM_BODY_PROCESS_LIST_ID = 68;


    /*
     * system components
     */

    // show the word or triple name and give the user the possibility to change the name
    const string SELECT_PHRASE = "phrase_select";
    const int SELECT_PHRASE_ID = 1;
    // select a view
    const string SELECT_VIEW = "view_select";
    const int SELECT_VIEW_ID = 2;

    // show a list of external references
    const string LIST_PHRASES = "phrase_list";
    const int LIST_PHRASES_ID = 96;
    const string LIST_TRIPLES = "triples_related";
    const int LIST_TRIPLES_ID = 48;
    const string LIST_REF = "ref_list";
    const int LIST_REF_ID = 42;
    const string LIST_FORMULAS = "formula_list";
    const int LIST_FORMULAS_ID = 98;
    const string LIST_RESULTS = "result_list";
    const int LIST_RESULTS_ID = 97;
    const string LINK_LIST_WORD = "link_list";
    const int LINK_LIST_WORD_ID = 43;
    const string USAGE_WORD = "usage";
    const int USAGE_WORD_ID = 44;

    // show the user specific name of a word or triple with the description on mouseover without allowing to change it
    const string PHRASE = "phrase";
    const int PHRASE_ID = 4;
    const string VERB_NAME = "verb_name";
    const int VERB_NAME_ID = 37;

    /*
     * related
     */

    // display a changeable list as a table (e.g. ABB as first word, Cash Flow Statement as second word)
    const string VALUES_RELATED = "values_related";
    const int VALUES_RELATED_ID = 11;

    const string SHOW_NAME = "system_show_field_name";
    const int SHOW_NAME_ID = 20;

    const string TEXT = "text";
    const int TEXT_ID = 3;
    const string TEXT_COM = "simply to display a variable text";

    const string SYSTEM_CHANGE_LOG = "change_log";
    const int SYSTEM_CHANGE_LOG_ID = 45;

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
    // offer to configure and create an XML file
    const string XML_EXPORT = "export_xml";
    // offer to configure and create an CSV file
    const string CSV_EXPORT = "export_csv";
    // show a list of words and triples with a link type selector
    const string LINK = "link";



    // a list with all types for the initial load with name, code_id and description
    const array ALL_TYPES = [
        self::TEXT,self::TEXT_COM,
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
        self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED,
        self::FORM_FIELD_GROUP,
        self::FORM_FIELD_GROUP_OR_PHRASES,
        self::FORM_FIELD_SELECTION_NAME,
        self::FORM_FIELD_SELECTION_DESCRIPTION,
        self::FORM_FIELD_SELECTION_TEXT,
        self::FORM_SELECT_PHRASE,
        self::FORM_SELECT_PHRASES,
        self::FORM_SELECT_VERB,
        self::FORM_SELECT_VERBS,
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
        self::FORM_SELECT_COMPONENT,
        self::FORM_SELECT_COMPONENTS,
        self::FORM_SELECT_VIEW_DEFAULT,
        self::FORM_SELECT_FILE,
        self::FORM_SELECT_FORMAT_EXPORT,
        self::FORM_SELECT_PHRASE_TYPE,
        self::FORM_SELECT_SOURCE_TYPE,
        self::FORM_SELECT_REF_TYPE,
        self::FORM_SELECT_FORMULA_TYPE,
        self::FORM_SELECT_VIEW_TYPE,
        self::FORM_SELECT_VIEW_STYLE,
        self::FORM_SELECT_COMPONENT_TYPE,
        self::FORM_SELECT_FORMULA_LINK_TYPE,
        self::FORM_SELECT_VIEW_LINK_TYPE,
        self::FORM_SELECT_COMPONENT_LINK_TYPE,
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
        self::SHOW_NAME,
        self::SYSTEM_TITLE
    );

    // list of component types that are a button
    const array BUTTON_TYPES = array(
        self::FORM_BUTTON_CANCEL,
        self::FORM_BUTTON_SAVE,
        self::FORM_BUTTON_DEL,
        self::FORM_BUTTON_IMPORT,
        self::FORM_BUTTON_EXPORT
    );

    // list of component types that are a e.g. button
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

    // list of the component types used for unit testing
    // in order of const definition which should be also the thematic order
    // TODO align with component_types.csv
    const array TEST_TYPES = array(
        [self::PHRASE_NAME, self::PHRASE_NAME_ID],
        [self::CALC_SHEET, self::CALC_SHEET_ID],
        [self::FORM_TITLE, self::FORM_TITLE_ID],
        [self::FORM_FIELD_NAME, self::FORM_FIELD_NAME_ID],
        [self::FORM_FIELD_DESCRIPTION, self::FORM_FIELD_DESCRIPTION_ID],
        [self::FORM_FIELD_URL, self::FORM_FIELD_URL_ID],
        [self::FORM_FIELD_PLURAL, self::FORM_FIELD_PLURAL_ID],
        [self::FORM_FIELD_FORMULA_EXPRESSION, self::FORM_FIELD_FORMULA_EXPRESSION_ID],
        [self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED, self::FORM_FIELD_FORMULA_ALL_VAR_NEEDED_ID],
        [self::FORM_FIELD_GROUP, self::FORM_FIELD_GROUP_ID],
        [self::FORM_FIELD_GROUP_OR_PHRASES, self::FORM_FIELD_GROUP_OR_PHRASES_ID],
        [self::FORM_FIELD_SELECTION_NAME, self::FORM_FIELD_SELECTION_NAME_ID],
        [self::FORM_FIELD_SELECTION_DESCRIPTION, self::FORM_FIELD_SELECTION_DESCRIPTION_ID],
        [self::FORM_FIELD_SELECTION_TEXT, self::FORM_FIELD_SELECTION_TEXT_ID],
        [self::FORM_VIEW_SELECT, self::FORM_VIEW_SELECT_ID],
        [self::FORM_SELECT_PHRASE, self::FORM_SELECT_PHRASE_ID],
        [self::FORM_SELECT_PHRASES, self::FORM_SELECT_PHRASES_ID],
        [self::FORM_SELECT_VERB, self::FORM_SELECT_VERB_ID],
        [self::FORM_SELECT_VERBS, self::FORM_SELECT_VERBS_ID],
        [self::FORM_SELECT_FORMULA, self::FORM_SELECT_FORMULA_ID],
        [self::FORM_SELECT_FORMULAS, self::FORM_SELECT_FORMULAS_ID],
        [self::FORM_SELECT_TERM, self::FORM_SELECT_TERM_ID],
        [self::FORM_SELECT_TERMS, self::FORM_SELECT_TERMS_ID],
        [self::FORM_SELECT_TERM, self::FORM_SELECT_TERM_ID],
        [self::FORM_SELECT_TERMS, self::FORM_SELECT_TERMS_ID],
        [self::FORM_SELECT_VALUE, self::FORM_SELECT_VALUE_ID],
        [self::FORM_SELECT_VALUES, self::FORM_SELECT_VALUES_ID],
        [self::FORM_SELECT_RESULT, self::FORM_SELECT_RESULT_ID],
        [self::FORM_SELECT_RESULTS, self::FORM_SELECT_RESULTS_ID],
        [self::FORM_SELECT_VIEW, self::FORM_SELECT_VIEW_ID],
        [self::FORM_SELECT_VIEWS, self::FORM_SELECT_VIEWS_ID],
        [self::FORM_SELECT_COMPONENT, self::FORM_SELECT_COMPONENT_ID],
        [self::FORM_SELECT_COMPONENTS, self::FORM_SELECT_COMPONENTS_ID],
        [self::FORM_SELECT_VIEW_DEFAULT, self::FORM_SELECT_VIEW_DEFAULT_ID],
        [self::FORM_SELECT_FILE, self::FORM_SELECT_FILE_ID],
        [self::FORM_SELECT_FORMAT_EXPORT, self::FORM_SELECT_FORMAT_EXPORT_ID],
        [self::FORM_SELECT_PHRASE_TYPE, self::FORM_SELECT_PHRASE_TYPE_ID],
        [self::FORM_SELECT_SOURCE_TYPE, self::FORM_SELECT_SOURCE_TYPE_ID],
        [self::FORM_SELECT_REF_TYPE, self::FORM_SELECT_REF_TYPE_ID],
        [self::FORM_SELECT_FORMULA_TYPE, self::FORM_SELECT_FORMULA_TYPE_ID],
        [self::FORM_SELECT_VIEW_TYPE, self::FORM_SELECT_VIEW_TYPE_ID],
        [self::FORM_SELECT_VIEW_STYLE, self::FORM_SELECT_VIEW_STYLE_ID],
        [self::FORM_SELECT_COMPONENT_TYPE, self::FORM_SELECT_COMPONENT_TYPE_ID],
        [self::FORM_SELECT_FORMULA_LINK_TYPE, self::FORM_SELECT_FORMULA_LINK_TYPE_ID],
        [self::FORM_SELECT_VIEW_LINK_TYPE, self::FORM_SELECT_VIEW_LINK_TYPE_ID],
        [self::FORM_SELECT_COMPONENT_LINK_TYPE, self::FORM_SELECT_VIEW_COMPONENT_TYPE_ID],
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
        [self::FORM_END, self::FORM_END_ID],
        [self::ROW_START, self::ROW_START_ID],
        [self::ROW_RIGHT, self::ROW_RIGHT_ID],
        [self::ROW_END, self::ROW_END_ID],
        [self::SYSTEM_TITLE, self::SYSTEM_TITLE_ID],
        [self::SYSTEM_BODY_ABOUT, self::SYSTEM_BODY_ABOUT_ID],
        [self::SYSTEM_BODY_ERROR_LOG, self::SYSTEM_BODY_ERROR_LOG_ID],
        [self::SYSTEM_BODY_ERROR_UPDATE, self::SYSTEM_BODY_ERROR_UPDATE_ID],
        [self::SYSTEM_BODY_PROCESS_PROGRESS, self::SYSTEM_BODY_PROCESS_PROGRESS_ID],
        [self::SYSTEM_BODY_PROCESS_LIST, self::SYSTEM_BODY_PROCESS_LIST_ID],
        [self::SELECT_PHRASE, self::SELECT_PHRASE_ID],
        [self::SELECT_VIEW, self::SELECT_VIEW_ID],
        [self::LIST_PHRASES, self::LIST_PHRASES_ID],
        [self::LIST_TRIPLES, self::LIST_TRIPLES_ID],
        [self::LIST_REF, self::LIST_REF_ID],
        [self::LIST_FORMULAS, self::LIST_FORMULAS_ID],
        [self::LIST_RESULTS, self::LIST_RESULTS_ID],
        [self::LINK_LIST_WORD, self::LINK_LIST_WORD_ID],
        [self::USAGE_WORD, self::USAGE_WORD_ID],
        [self::PHRASE, self::PHRASE_ID],
        [self::VERB_NAME, self::VERB_NAME_ID],
        [self::VALUES_RELATED, self::VALUES_RELATED_ID],
        [self::SHOW_NAME, self::SHOW_NAME_ID],
        [self::TEXT, self::TEXT_ID],
        [self::SYSTEM_CHANGE_LOG, self::SYSTEM_CHANGE_LOG_ID]
    );

}
