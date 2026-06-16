<?php

/*

    shared/const/components.php - all component code id used in the code and some with name, id and comment used by the system
    ---------------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;

class components
{

    // code_id, database id and name of internal view components used by the system
    // the components used by the system for testing are expected to be never changed
    // *_CODE or * is the code id that is expected never to change
    // *_ID is the mask id that is expected never to change
    // *_NAME is the name of the view if it differs from the code id
    // *_COM is the comment or description used for the tooltip

    // general components used several times
    const string WORD_NAME = 'Word';
    const string WORD_COM = 'simply show the word or triple name';
    const int WORD_ID = 1;
    const string MATRIX_NAME = 'spreadsheet';
    const string MATRIX_COM = 'changeable sheet with words, number and formulas';
    const int MATRIX_ID = 2;

    // text components to test the side or below position types
    // with ids that are far above the component ids used in the database
    const string COL_FIRST_NAME = 'first column';
    const int COL_FIRST_ID = 901;
    const string COL_SECOND_NAME = 'second column';
    const int COL_SECOND_ID = 902;
    const string COL_THIRD_NAME = 'third column';
    const int COL_THIRD_ID = 903;
    const string COL_FOURTH_NAME = 'fourth column';
    const int COL_FOURTH_ID = 904;

    // for system views
    const string FORM_TITLE = 'form_title';
    const string FORM_TITLE_NAME = 'form title';
    const string FORM_TITLE_COM = 'show the language specific title of a add, change or delete form';
    const string FORM_NAME = 'form_field_name';
    const string FORM_NAME_NAME = 'system form field name';
    const string FORM_NAME_COM = 'the name field in a form';
    const int FORM_NAME_ID = 4;
    const string FORM_DESCRIPTION = 'form_field_description';
    const string FORM_DESCRIPTION_NAME = 'system form field description';
    const string FORM_DESCRIPTION_COM = 'the description field in a form';

    // select object types
    const string FORM_PHRASE_TYPE = 'form_field_phrase_type';
    const string FORM_PHRASE_TYPE_NAME = 'form field phrase type';
    const string FORM_PHRASE_TYPE_COM = 'the phrase type field in a form';

    // select object fields

    // verb only fields
    const string FORM_PLURAL = 'form_field_plural';
    const string FORM_PLURAL_NAME = 'system form field plural';
    const string FORM_PLURAL_COM = 'the plural language form field in a form (to be move to languages forms)';
    const int FORM_PLURAL_ID = 92;

    // triple only fields
    const string FORM_WEIGHT = 'form_field_weight';
    const string FORM_PHRASE_FROM_CODE_ID = 'form_select_phrase_from';
    const string FORM_PHRASE_TO_CODE_ID = 'form_select_phrase_to';

    // component only fields
    const string FORM_PHRASE_ROW = 'form_field_select_phrase_row';
    const string FORM_PHRASE_COL = 'form_field_select_phrase_col';
    const string FORM_PHRASE_COL_SUB = 'form_field_select_phrase_col_sub';

    // ref only fields
    const string FORM_PHRASE_REF_CODE_ID = 'form_select_phrase_ref';

    const string FORM_SHARE_TYPE = 'form_field_share_type';
    const string FORM_SHARE_TYPE_NAME = 'form field share type';
    const string FORM_SHARE_TYPE_COM = 'the share type field in a form';
    const string FORM_PROTECTION_TYPE = 'form_field_protection_type';
    const string FORM_PROTECTION_TYPE_NAME = 'form field protection type';
    const string FORM_PROTECTION_TYPE_COM = 'the protection type field in a form';
    const string FORM_CANCEL = 'form_cancel_button';
    const string FORM_CANCEL_NAME = '"system form button cancel"';
    const string FORM_CANCEL_COM = 'button to cancel the form action and go back to the previous view';
    const string FORM_SAVE = 'form_save_button';
    const string FORM_SAVE_NAME = 'save button';
    const string FORM_SAVE_COM = 'button to save the form field into the database';
    const string FORM_END = 'form_end';
    const string FORM_END_NAME = 'form end';
    const string FORM_END_COM = 'just to indicate the end of the form';
    const string TN_SHOW_NAME = 'system show field name';

    // hidden form fields
    const string FORM_BACK = 'form_back_stack';
    const string FORM_BACK_NAME = 'system form hidden back stack';
    const string FORM_BACK_COM = 'field that contains the stack for the undo actions';

    // buttons
    const string FORM_CONFIRM = 'form_confirm_button';
    const string FORM_CONFIRM_NAME = 'confirm button';
    const string FORM_CONFIRM_COM = 'switch on that the form saving needs an extra confirm by the user';

    // code id of the view components
    const string VIEW_SELECTOR_WORD = "view_selector_word";
    const string REF_LIST_WORD = "ref_list_word";
    const string LINK_LIST_WORD = "link_list_word";
    const string USAGE_WORD = "usage_word";
    const string CHANGE_LOG_WORD = "change_log_word";
    const string VIEW_LIST_WORD = "view_list_word";

    // persevered view component names for unit and integration tests
    const string TEST_ADD_NAME = 'System Test View Component';
    const string TEST_ADD_VIA_FUNC_NAME = 'System Test Component added via sql function';
    const string TEST_ADD_COM = 'System Test View Component description';
    const string TEST_RENAMED_NAME = 'System Test View Component Renamed';
    const string TEST_ADD_2_NAME = 'System Test View Component Two';
    const string TEST_TITLE_NAME = 'System Test View Component Title';
    const string TEST_VALUES_NAME = 'System Test View Component Values';
    const string TEST_RESULTS_NAME = 'System Test View Component Results';
    const string TEST_EXCLUDED_NAME = 'System Test View Component Excluded';
    const string TEST_TABLE_NAME = 'System Test View Component Table';


    // array of component names that used for testing and remove them after the test
    const array RESERVED_COMPONENTS = array(
        self::WORD_NAME,
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_FUNC_NAME,
        self::TEST_RENAMED_NAME,
        self::TEST_ADD_2_NAME,
        self::TEST_TITLE_NAME,
        self::TEST_VALUES_NAME,
        self::TEST_RESULTS_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

    // array of component names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        self::WORD_NAME
    );

    // array of test component names used for testing and removed after the testing is completed
    const array TEST_COMPONENTS = array(
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_FUNC_NAME,
        self::TEST_RENAMED_NAME,
        self::TEST_ADD_2_NAME,
        self::TEST_TITLE_NAME,
        self::TEST_VALUES_NAME,
        self::TEST_RESULTS_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

}
