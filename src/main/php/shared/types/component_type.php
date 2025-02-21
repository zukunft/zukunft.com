<?php

/*

    cfg/component/component_type.php - db based ENUM of the component types
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace shared\types;

class component_type
{

    /*
     * start page
     */

    // just to display a fixed text
    // *_ID is the fixed database id as defined by the component_list.csv
    const TEXT_COM = "simply to display a variable text";
    const TEXT = "text";
    const TEXT_ID = 3;
    // show a spreadsheet that allow changes
    const CALC_SHEET_COM = "changeable sheet with words, number and formulas";
    const CALC_SHEET = "calc_sheet";
    const CALC_SHEET_ID = 35;


    /*
     * system form
     */

    const FORM_TITLE = "system_form_title";
    const FORM_TITLE_ID = 17;
    const FORM_BACK = "system_form_back_stack";
    const FORM_BACK_ID = 18;
    const FORM_CONFIRM = "system_form_confirm_status";
    const FORM_CONFIRM_ID = 19;
    const SHOW_NAME = "system_show_field_name";
    const SHOW_NAME_ID = 20;
    const FORM_NAME = "system_form_field_name";
    const FORM_NAME_ID = 21;
    const FORM_DESCRIPTION = "system_form_field_description";
    const FORM_DESCRIPTION_ID = 22;
    const FORM_PHRASE = "system_form_select_phrase";
    const FORM_PHRASE_ID = 23;
    const FORM_VERB_SELECTOR = "system_form_select_verb";
    const FORM_VERB_SELECTOR_ID = 24;
    const FORM_PHRASE_TYPE = "system_form_select_phrase_type";
    const FORM_PHRASE_TYPE_ID = 25;
    const FORM_SOURCE_TYPE = "system_form_select_source_type";
    const FORM_SOURCE_TYPE_ID = 39;
    const FORM_REF_TYPE = "system_form_select_ref_type";
    const FORM_REF_TYPE_ID = 48;
    const FORM_FORMULA_TYPE = "system_form_select_formula_type";
    const FORM_FORMULA_TYPE_ID = 49;
    const FORM_VIEW_TYPE = "system_form_select_view_type";
    const FORM_VIEW_TYPE_ID = 50;
    const FORM_COMPONENT_TYPE = "system_form_select_component_type";
    const FORM_COMPONENT_TYPE_ID = 51;
    const FORM_FORMULA_EXPRESSION = "system_form_field_formula_expression";
    const FORM_FORMULA_EXPRESSION_ID = 52;
    const FORM_FORMULA_ALL_FIELDS = "system_form_field_formula_all_vars";
    const FORM_FORMULA_ALL_FIELDS_ID = 53;
    const FORM_SHARE_TYPE = "system_form_select_share";
    const FORM_SHARE_TYPE_ID = 26;
    const FORM_PROTECTION_TYPE = "system_form_select_protection";
    const FORM_PROTECTION_TYPE_ID = 27;
    const FORM_CANCEL = "system_button_cancel";
    const FORM_CANCEL_ID = 28;
    const FORM_SAVE = "system_button_save";
    const FORM_SAVE_ID = 29;
    const FORM_DEL = "system_button_del";
    const FORM_DEL_ID = 30;
    // simple close the form section
    const FORM_END = "form_end";
    const FORM_END_ID = 31;
    const FORM_VIEW_SELECT = "form_view_select";
    const FORM_VIEW_SELECT_ID = 40;


    /*
     * hidden
     */

    // internal components used for formatting
    const ROW_START = "row_start";
    const ROW_START_ID = 32;
    const ROW_RIGHT = "row_right";
    const ROW_RIGHT_ID = 33;
    const ROW_END = "row_end";
    const ROW_END_ID = 34;


    /*
     * system components
     */

    // select a view
    const VIEW_SELECT = "view_select";
    const VIEW_SELECT_ID = 2;
    // show a list of external references
    const REF_LIST_WORD = "ref_list";
    const REF_LIST_WORD_ID = 41;
    const LINK_LIST_WORD = "link_list";
    const LINK_LIST_WORD_ID = 42;
    const USAGE_WORD = "usage";
    const USAGE_WORD_ID = 43;
    const SYSTEM_CHANGE_LOG = "change_log";
    const SYSTEM_CHANGE_LOG_ID = 44;
    const TRIPLE_LIST = "triples_related";
    const TRIPLE_LIST_ID = 47;

    // show the user specific name of a word or triple with the description on mouseover without allowing to change it
    const PHRASE = "phrase";
    const PHRASE_ID = 4;
    // show the word or triple name and give the user the possibility to change the name
    const PHRASE_NAME = "phrase_name";
    const PHRASE_NAME_ID = 8;
    const PHRASE_SELECT = "phrase_select";
    const PHRASE_SELECT_ID = 1;
    // show all word that this words is based on
    const VERB_NAME = "verb_name";
    const VERB_NAME_ID = 37;
    const WORDS_UP = "word_list_up";
    // show all words that are based on the given start word
    const WORDS_DOWN = "word_list_down";
    // a word list with some key numbers e.g. all companies with the PE ratio
    const NUMERIC_VALUE = "word_value_list";
    // shows all: all words that link to the given word and all values related to the given word
    const VALUES_ALL = "values_all";
    // display all formulas related to the given word
    const FORMULAS = "formula_list";
    // show a list of formula results related to a word
    const FORMULA_RESULTS = "formula_results";
    // offer to configure and create an JSON file
    const JSON_EXPORT = "json_export";
    // offer to configure and create an XML file
    const XML_EXPORT = "xml_export";
    // offer to configure and create an CSV file
    const CSV_EXPORT = "csv_export";
    // show a list of words and triples with a link type selector
    const LINK = "link";


    /*
     * related
     */

    // display a changeable list as a table (e.g. ABB as first word, Cash Flow Statement as second word)
    const VALUES_RELATED = "values_related";
    const VALUES_RELATED_ID = 11;


    // a list with all types for the initial load with name, code_id and description
    const ALL_TYPES = [
        self::TEXT,self::TEXT_COM,
        self::CALC_SHEET,self::CALC_SHEET_COM
    ];

    // list of component types that should not be used for non system views
    const SYSTEM_TYPES = array(
        self::FORM_TITLE,
        self::FORM_BACK,
        self::FORM_CONFIRM,
        self::SHOW_NAME,
        self::FORM_NAME,
        self::FORM_DESCRIPTION,
        self::FORM_PHRASE,
        self::FORM_VERB_SELECTOR,
        self::FORM_PHRASE_TYPE,
        self::FORM_SOURCE_TYPE,
        self::FORM_SHARE_TYPE,
        self::FORM_PROTECTION_TYPE,
        self::FORM_CANCEL,
        self::FORM_SAVE,
        self::FORM_DEL,
        self::FORM_END,
        self::ROW_START,
        self::ROW_RIGHT,
        self::ROW_END
    );

    // list of component types that are a button
    const BUTTON_TYPES = array(
        self::FORM_CANCEL,
        self::FORM_SAVE,
        self::FORM_DEL
    );

    // list of component types that are a button
    const HIDDEN_TYPES = array(
        self::ROW_START,
        self::FORM_TITLE,
        self::FORM_BACK,
        self::FORM_CONFIRM,
        self::FORM_END,
        self::ROW_START,
        self::ROW_RIGHT,
        self::ROW_END
    );

    // list of the component types used for unit testing
    // TODO align with component_types.csv
    const TEST_TYPES = array(
        [self::PHRASE_SELECT, self::PHRASE_SELECT_ID],
        [self::VIEW_SELECT, self::VIEW_SELECT_ID],
        [self::TEXT, self::TEXT_ID],
        [self::PHRASE, self::PHRASE_ID],
        [self::PHRASE_NAME, self::PHRASE_NAME_ID],
        [self::VERB_NAME, self::VERB_NAME_ID],
        [self::VALUES_RELATED, self::VALUES_RELATED_ID],
        [self::FORM_TITLE, self::FORM_TITLE_ID],
        [self::FORM_BACK, self::FORM_BACK_ID],
        [self::FORM_CONFIRM, self::FORM_CONFIRM_ID],
        [self::SHOW_NAME, self::SHOW_NAME_ID],
        [self::FORM_NAME, self::FORM_NAME_ID],
        [self::FORM_DESCRIPTION, self::FORM_DESCRIPTION_ID],
        [self::FORM_PHRASE, self::FORM_PHRASE_ID],
        [self::FORM_VERB_SELECTOR, self::FORM_VERB_SELECTOR_ID],
        [self::FORM_PHRASE_TYPE, self::FORM_PHRASE_TYPE_ID],
        [self::FORM_SHARE_TYPE, self::FORM_SHARE_TYPE_ID],
        [self::FORM_PROTECTION_TYPE, self::FORM_PROTECTION_TYPE_ID],
        [self::FORM_CANCEL, self::FORM_CANCEL_ID],
        [self::FORM_SAVE, self::FORM_SAVE_ID],
        [self::FORM_DEL, self::FORM_DEL_ID],
        [self::FORM_END, self::FORM_END_ID],
        [self::ROW_START, self::ROW_START_ID],
        [self::ROW_RIGHT, self::ROW_RIGHT_ID],
        [self::ROW_END, self::ROW_END_ID],
        [self::CALC_SHEET, self::CALC_SHEET_ID],
        [self::FORM_SOURCE_TYPE, self::FORM_SOURCE_TYPE_ID],
        [self::FORM_REF_TYPE, self::FORM_REF_TYPE_ID],
        [self::FORM_FORMULA_TYPE, self::FORM_FORMULA_TYPE_ID],
        [self::FORM_VIEW_TYPE, self::FORM_VIEW_TYPE_ID],
        [self::FORM_COMPONENT_TYPE, self::FORM_COMPONENT_TYPE_ID],
        [self::FORM_FORMULA_EXPRESSION, self::FORM_FORMULA_EXPRESSION_ID],
        [self::FORM_FORMULA_ALL_FIELDS, self::FORM_FORMULA_ALL_FIELDS_ID],
        [self::VIEW_SELECT, self::VIEW_SELECT_ID],
        [self::FORM_VIEW_SELECT, self::FORM_VIEW_SELECT_ID],
        [self::TRIPLE_LIST, self::TRIPLE_LIST_ID],
        [self::REF_LIST_WORD, self::REF_LIST_WORD_ID],
        [self::LINK_LIST_WORD, self::LINK_LIST_WORD_ID],
        [self::USAGE_WORD, self::USAGE_WORD_ID],
        [self::SYSTEM_CHANGE_LOG, self::SYSTEM_CHANGE_LOG_ID]
    );

}
