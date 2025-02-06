<?php

/*

    shared/const/views.php - system view const with name and id
    ----------------------


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

namespace shared\const;

class components
{

    // code_id, database id and name of internal view components used by the system
    // the components used by the system for testing are expected to be never changed
    // *_CODE or * is the code id that is expected never to change
    // *_ID is the mask id that is expected never to change
    // *_NAME is the name of the view if it differs from the code id
    // *_COM is the comment or description used for the tooltip
    const WORD_NAME = 'Word';
    const WORD_COM = 'simply show the word or triple name';
    const WORD_ID = 1;
    const MATRIX_NAME = 'spreadsheet';
    const MATRIX_COM = 'sheet with words, number and formulas';
    const MATRIX_ID = 2;

    // persevered view component names for unit and integration tests
    const TEST_ADD_NAME = 'System Test View Component';
    const TEST_ADD_VIA_FUNC_NAME = 'System Test Component added via sql function';
    const TEST_ADD_VIA_SQL_NAME = 'System Test Component added via sql insert';
    const TEST_RENAMED_NAME = 'System Test View Component Renamed';
    const TEST_ADD_2_NAME = 'System Test View Component Two';
    const TEST_TITLE_NAME = 'System Test View Component Title';
    const TEST_VALUES_NAME = 'System Test View Component Values';
    const TEST_RESULTS_NAME = 'System Test View Component Results';
    const TEST_EXCLUDED_NAME = 'System Test View Component Excluded';
    const TEST_TABLE_NAME = 'System Test View Component Table';

    // for system views
    const FORM_TITLE = 'form_title';
    const FORM_TITLE_NAME = 'form title';
    const FORM_TITLE_COM = 'show the language specific title of a add, change or delete form';
    const FORM_BACK = 'form_back_stack';
    const FORM_BACK_NAME = 'system form hidden back stack';
    const FORM_BACK_COM = 'field that contains the stack for the undo actions';
    const FORM_CONFIRM = 'form_confirm_button';
    const FORM_CONFIRM_NAME = 'confirm button';
    const FORM_CONFIRM_COM = 'switch on that the form saving needs an extra confirm by the user';
    const FORM_NAME = 'form_field_name';
    const FORM_NAME_NAME = 'system form field name';
    const FORM_NAME_COM = 'the name field in a form';
    const FORM_DESCRIPTION = 'form_field_description';
    const FORM_DESCRIPTION_NAME = 'system form field description';
    const FORM_DESCRIPTION_COM = 'the description field in a form';
    const FORM_PHRASE_TYPE = 'form_field_phrase_type';
    const FORM_PHRASE_TYPE_NAME = 'form field phrase type';
    const FORM_PHRASE_TYPE_COM = 'the phrase type field in a form';
    const FORM_SHARE_TYPE = 'form_field_share_type';
    const FORM_SHARE_TYPE_NAME = 'form field share type';
    const FORM_SHARE_TYPE_COM = 'the share type field in a form';
    const FORM_PROTECTION_TYPE = 'form_field_protection_type';
    const FORM_PROTECTION_TYPE_NAME = 'form field protection type';
    const FORM_PROTECTION_TYPE_COM = 'the protection type field in a form';
    const FORM_CANCEL = 'form_cancel_button';
    const FORM_CANCEL_NAME = '"system form button cancel"';
    const FORM_CANCEL_COM = 'button to cancel the form action and go back to the previous view';
    const FORM_SAVE = 'form_save_button';
    const FORM_SAVE_NAME = 'save button';
    const FORM_SAVE_COM = 'button to save the form field into the database';
    const FORM_END = 'form_end';
    const FORM_END_NAME = 'form end';
    const FORM_END_COM = 'just to indicate the end of the form';
    const TN_SHOW_NAME = 'system show field name';

    // code id of the view components
    const VIEW_SELECTOR_WORD = "view_selector_word";
    const REF_LIST_WORD = "ref_list_word";
    const LINK_LIST_WORD = "link_list_word";
    const USAGE_WORD = "usage_word";
    const CHANGE_LOG_WORD = "change_log_word";


    // array of component names that used for testing and remove them after the test
    const RESERVED_COMPONENTS = array(
        self::WORD_NAME,
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_SQL_NAME,
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
    const FIXED_NAMES = array(
        self::WORD_NAME
    );

    // array of test component names used for testing and removed after the testing is completed
    const TEST_COMPONENTS = array(
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_SQL_NAME,
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
