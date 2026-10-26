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

    // the "Title solution priority" component of solution_prio.json, the sample object of the
    // component default page, because it has a description and a type and is used by a view;
    // the id is the import position, so re-baseline it from unit/component/list.csv after a reset
    const string SOLUTION_PRIO_TITLE_NAME = 'Title solution priority';
    const int SOLUTION_PRIO_TITLE_ID = 427;

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

    // text components to test the same line position type
    const string LINE_FIRST_NAME = 'first field';
    const int LINE_FIRST_ID = 905;
    const string LINE_SECOND_NAME = 'second field';
    const int LINE_SECOND_ID = 906;

    // for system views
    const string FORM_TITLE = 'form_title';
    const string FORM_TITLE_NAME = 'form title';
    const string FORM_TITLE_COM = 'show the language specific title of a add, change or delete form';
    const string FORM_NAME = 'form_field_name';
    const string FORM_NAME_NAME = 'system form field name';
    const string FORM_NAME_COM = 'the name field in a form';
    const int FORM_NAME_ID = 254;
    const string FORM_DESCRIPTION = 'form_field_description';
    const string FORM_DESCRIPTION_NAME = 'system form field description';
    const string FORM_DESCRIPTION_COM = 'the description field in a form';

    // the tabs of the process catch detail and result page and the input fields of each tab
    // (process_catch_views.json); the code id names the tab number, because the ui message of a
    // component is unique and therefore cannot link a tab to its fields
    const string PROCESS_CATCH_DETAIL_TAB_ONE = 'process_catch_detail_tab_one';
    const string PROCESS_CATCH_DETAIL_TAB_TWO = 'process_catch_detail_tab_two';
    const string PROCESS_CATCH_DETAIL_TAB_THREE = 'process_catch_detail_tab_three';
    const string PROCESS_CATCH_DETAIL_TAB_FOUR = 'process_catch_detail_tab_four';
    const string PROCESS_CATCH_DETAIL_TAB_FIVE = 'process_catch_detail_tab_five';
    const string PROCESS_CATCH_DETAIL_TAB_ONE_FIELDS = 'process_catch_detail_tab_one_fields';
    const string PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS = 'process_catch_detail_tab_two_fields';
    const string PROCESS_CATCH_DETAIL_TAB_THREE_FIELDS = 'process_catch_detail_tab_three_fields';
    const string PROCESS_CATCH_DETAIL_TAB_FOUR_FIELDS = 'process_catch_detail_tab_four_fields';
    const string PROCESS_CATCH_DETAIL_TAB_FIVE_FIELDS = 'process_catch_detail_tab_five_fields';
    const string PROCESS_CATCH_RESULT_TAB_ONE = 'process_catch_result_tab_one';
    const string PROCESS_CATCH_RESULT_TAB_TWO = 'process_catch_result_tab_two';
    const string PROCESS_CATCH_RESULT_TAB_THREE = 'process_catch_result_tab_three';
    const string PROCESS_CATCH_RESULT_TAB_FOUR = 'process_catch_result_tab_four';
    const string PROCESS_CATCH_RESULT_TAB_FIVE = 'process_catch_result_tab_five';
    const string PROCESS_CATCH_RESULT_TAB_ONE_FIELDS = 'process_catch_result_tab_one_fields';
    const string PROCESS_CATCH_RESULT_TAB_TWO_FIELDS = 'process_catch_result_tab_two_fields';
    const string PROCESS_CATCH_RESULT_TAB_THREE_FIELDS = 'process_catch_result_tab_three_fields';
    const string PROCESS_CATCH_RESULT_TAB_FOUR_FIELDS = 'process_catch_result_tab_four_fields';
    const string PROCESS_CATCH_RESULT_TAB_FIVE_FIELDS = 'process_catch_result_tab_five_fields';
    // the tab number of each tab component and of the fields of each tab, starting with 1
    const array PROCESS_CATCH_TAB_NBR = [
        self::PROCESS_CATCH_DETAIL_TAB_ONE => 1,
        self::PROCESS_CATCH_DETAIL_TAB_TWO => 2,
        self::PROCESS_CATCH_DETAIL_TAB_THREE => 3,
        self::PROCESS_CATCH_DETAIL_TAB_FOUR => 4,
        self::PROCESS_CATCH_DETAIL_TAB_FIVE => 5,
        self::PROCESS_CATCH_DETAIL_TAB_ONE_FIELDS => 1,
        self::PROCESS_CATCH_DETAIL_TAB_TWO_FIELDS => 2,
        self::PROCESS_CATCH_DETAIL_TAB_THREE_FIELDS => 3,
        self::PROCESS_CATCH_DETAIL_TAB_FOUR_FIELDS => 4,
        self::PROCESS_CATCH_DETAIL_TAB_FIVE_FIELDS => 5,
        self::PROCESS_CATCH_RESULT_TAB_ONE => 1,
        self::PROCESS_CATCH_RESULT_TAB_TWO => 2,
        self::PROCESS_CATCH_RESULT_TAB_THREE => 3,
        self::PROCESS_CATCH_RESULT_TAB_FOUR => 4,
        self::PROCESS_CATCH_RESULT_TAB_FIVE => 5,
        self::PROCESS_CATCH_RESULT_TAB_ONE_FIELDS => 1,
        self::PROCESS_CATCH_RESULT_TAB_TWO_FIELDS => 2,
        self::PROCESS_CATCH_RESULT_TAB_THREE_FIELDS => 3,
        self::PROCESS_CATCH_RESULT_TAB_FOUR_FIELDS => 4,
        self::PROCESS_CATCH_RESULT_TAB_FIVE_FIELDS => 5,
    ];
    // the refresh button of the process catch detail page and the place input of process_catch_views.json
    const string PROCESS_CATCH_REFRESH_NAME = 'button refresh process catch detail';
    const string GEOLOCATOR_NAME = 'geolocator place of user';
    // the three components of the calculator view, which are the same for every calculator page
    // (calculator_views.json)
    const string CALCULATOR_TITLE = 'title_calculator';
    const string CALCULATOR_TITLE_NAME = 'title calculator';
    const string CALCULATOR_INPUT = 'calculator_input';
    const string CALCULATOR_INPUT_NAME = 'calculator input';
    const string CALCULATION_RESULT = 'calculation_result';
    const string CALCULATION_RESULT_NAME = 'calculation result';

    // select object types
    const string FORM_PHRASE_TYPE = 'form_field_phrase_type';
    const string FORM_PHRASE_TYPE_NAME = 'form field phrase type';
    const string FORM_PHRASE_TYPE_COM = 'the phrase type field in a form';

    // select object fields

    // verb only fields
    const string FORM_PLURAL = 'form_field_plural';
    const string FORM_PLURAL_NAME = 'system form field plural';
    const string FORM_PLURAL_COM = 'the plural language form field in a form (to be move to languages forms)';
    const int FORM_PLURAL_ID = 256;

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
    const string INVISIBLE_CHANGE_LOG_TABLE_WORD = "invisible_change_log_table_word";
    const string VIEW_LIST_WORD = "view_list_word";
    const string VIEW_TAB_BOX_WORD = "view_tab_box_word";

    // persevered view component names for unit and integration tests
    const string TEST_ADD_NAME = 'System Test View Component';
    const int TEST_ADD_ID = 993; // fixed snapshot id of the add workflow component (like word_names::TEST_ADD_ID)
    const string TEST_ADD_VIA_FUNC_NAME = 'System Test Component added via sql function';
    const string TEST_ADD_COM = 'System Test View Component description';
    // the description that the change_component workflow writes in its second round
    const string TEST_DESCRIPTION_CHANGED = 'System Test View Component description changed';
    const string TEST_RENAMED_NAME = 'System Test View Component Renamed';
    const string TEST_ADD_2_NAME = 'System Test View Component Two';
    const string TEST_TITLE_NAME = 'System Test View Component Title';
    const string TEST_VALUES_NAME = 'System Test View Component Values';
    const string TEST_RESULTS_NAME = 'System Test View Component Results';
    const string TEST_EXCLUDED_NAME = 'System Test View Component Excluded';
    const string TEST_TABLE_NAME = 'System Test View Component Table';
    // the components of the use case page test (unit_ui/use_case_ui_tests.php), never saved
    const string TEST_VIEW_TITLE_NAME = 'System Test View Component View Title';
    const string TEST_SUB_TITLE_NAME = 'System Test View Component Sub Title';
    const string TEST_NOTE_NAME = 'System Test View Component Note';
    const string TEST_DETAILS_START_NAME = 'System Test View Component Details Start';
    const string TEST_DETAILS_END_NAME = 'System Test View Component Details End';
    const string TEST_INPUT_FIELDS_NAME = 'System Test View Component Input Fields';
    const string TEST_SOURCES_NAME = 'System Test View Component Sources';
    const string TEST_ASSUMPTIONS_NAME = 'System Test View Component Assumptions';
    const string TEST_NOTES_NAME = 'System Test View Component Notes';
    const string TEST_CALCULATOR_TITLE_NAME = 'System Test View Component Calculator Title';
    const string TEST_CALCULATOR_INPUT_NAME = 'System Test View Component Calculator Input';
    const string TEST_CALCULATION_RESULT_NAME = 'System Test View Component Calculation Result';
    const string TEST_CALCULATOR_EDITOR_NAME = 'System Test View Component Calculator Editor';
    const string TEST_FORMULA_EDITOR_NAME = 'System Test View Component Formula Editor';


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
