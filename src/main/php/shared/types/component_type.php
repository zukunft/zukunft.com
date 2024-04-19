<?php

/*

    cfg/component/component_type.php - db based ENUM of the component types
    --------------------------------

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

    // list of the view component types that have a coded functionality
    // until the initial import csv is not yet created based on these const the resources/db_code_links/component_types needs to be inline

    // just to display a fixed text
    const TEXT = "text";
    // show the user specific name of a word or triple with the description on mouseover without allowing to change it
    const PHRASE = "phrase";
    // show the word or triple name and give the user the possibility to change the name
    const PHRASE_NAME = "phrase_name";
    const PHRASE_SELECT = "phrase_select";
    // show all word that this words is based on
    const WORDS_UP = "word_list_up";
    // show all words that are based on the given start word
    const WORDS_DOWN = "word_list_down";
    // a word list with some key numbers e.g. all companies with the PE ratio
    const NUMERIC_VALUE = "word_value_list";
    // shows all: all words that link to the given word and all values related to the given word
    const VALUES_ALL = "values_all";
    // display a table (e.g. ABB as first word, Cash Flow Statement as second word)
    const VALUES_RELATED = "values_related";
    // display all formulas related to the given word
    const FORMULAS = "formula_list";
    // show a list of formula results related to a word
    const FORMULA_RESULTS = "formula_results"; // TODO add to csv
    // offer to configure and create an JSON file
    const JSON_EXPORT = "json_export";
    // offer to configure and create an XML file
    const XML_EXPORT = "xml_export";
    // offer to configure and create an CSV file
    const CSV_EXPORT = "csv_export";
    const VIEW_SELECT = "view_select"; // TODO add to csv
    const LINK = "link";
    const FORM_TITLE = "system_form_title";
    const FORM_BACK = "system_form_back_stack";
    const FORM_CONFIRM = "system_form_confirm_status";
    const FORM_NAME = "system_form_field_name";
    const FORM_DESCRIPTION = "system_form_field_description";
    const FORM_PHRASE = "system_form_select_phrase";
    const FORM_VERB_SELECTOR = "system_form_select_verb";
    const FORM_SHARE_TYPE = "system_form_select_share";
    const FORM_PROTECTION_TYPE = "system_form_select_protection";
    const FORM_CANCEL = "system_button_cancel";
    const FORM_SAVE = "system_button_save";
    const FORM_DEL = "system_button_del";
    // simple close the form section
    const FORM_END = "form_end";
    // internal components used for formatting
    const ROW_START = "row_start";
    const ROW_RIGHT = "row_right";
    const ROW_END = "row_end";

    // list of component types that should not be used for non system views
    const SYSTEM_TYPES = array(
        self::FORM_TITLE,
        self::FORM_BACK,
        self::FORM_CONFIRM,
        self::FORM_NAME,
        self::FORM_DESCRIPTION,
        self::FORM_PHRASE,
        self::FORM_VERB_SELECTOR,
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

    // list of the component types used for unit testing
    const TEST_TYPES = array(
        [self::TEXT, 2],
        [self::PHRASE_NAME, 8],
        [self::FORM_TITLE, 17],
        [self::FORM_BACK, 18],
        [self::FORM_CONFIRM, 19],
        [self::FORM_NAME, 20],
        [self::FORM_DESCRIPTION, 21],
        [self::FORM_PHRASE, 22],
        [self::FORM_VERB_SELECTOR, 23],
        [self::FORM_SHARE_TYPE, 24],
        [self::FORM_PROTECTION_TYPE, 25],
        [self::FORM_CANCEL, 26],
        [self::FORM_SAVE, 27],
        [self::FORM_DEL, 28],
        [self::FORM_END, 29],
        [self::ROW_START, 30],
        [self::ROW_RIGHT, 31],
        [self::ROW_END, 32]
    );

}
