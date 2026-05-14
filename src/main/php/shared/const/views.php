<?php

/*

    shared/const/views.php - system views with name and id
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

namespace Zukunft\ZukunftCom\main\php\shared\const;

class views
{

    // code_id, database id and name of internal views used by the system
    // these views used by the program that are never supposed to be changed
    // *_CODE or * is the view / mask code id that is expected never to change
    // *_ID is the view / mask id that is expected never to change
    // *_NAME is the name of the view if it differs from the code id
    // *_COM is the comment or description used for the tooltip
    const string START = 'start';
    const string START_CODE = 'entry_view'; // TODO combine
    const string START_NAME = 'Start view';
    const string START_COM = 'A dynamic entry mask that initially shows a table for calculations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.';
    const int START_ID = 1;

    // curl views for main objects
    const string WORD_ADD = 'word_add';
    const int WORD_ADD_ID = 2;
    const string WORD_EDIT = 'word_edit';
    const int WORD_EDIT_ID = 3;
    const string WORD_DEL = 'word_del';
    const int WORD_DEL_ID = 4;
    const string WORD_LOG = 'word_usage';
    const int WORD_LOG_ID = 5;
    const string WORD_LOG_COM = 'child view that groups the usage and log components of a word so that it can be added to the word edit and word del view without repeating the list';
    const string VERB_ADD = 'verb_add';
    const int VERB_ADD_ID = 6;
    const string VERB_EDIT = 'verb_edit';
    const int VERB_EDIT_ID = 7;
    const string VERB_DEL = 'verb_del';
    const int VERB_DEL_ID = 8;
    const string TRIPLE_ADD = 'triple_add';
    const int TRIPLE_ADD_ID = 9;
    const string TRIPLE_EDIT = 'triple_edit';
    const int TRIPLE_EDIT_ID = 10;
    const string TRIPLE_DEL = 'triple_del';
    const int TRIPLE_DEL_ID = 11;
    const string SOURCE_ADD = 'source_add';
    const int SOURCE_ADD_ID = 12;
    const string SOURCE_EDIT = 'source_edit';
    const int SOURCE_EDIT_ID = 13;
    const string SOURCE_DEL = 'source_del';
    const int SOURCE_DEL_ID = 14;
    const string REF_ADD = 'ref_add';
    const int REF_ADD_ID = 15;
    const string REF_EDIT = 'ref_edit';
    const int REF_EDIT_ID = 16;
    const string REF_DEL = 'ref_del';
    const int REF_DEL_ID = 17;
    const string VALUE_ADD = 'value_add';
    const int VALUE_ADD_ID = 18;
    const string VALUE_EDIT = 'value_edit';
    const int VALUE_EDIT_ID = 19;
    const string VALUE_DEL = 'value_del';
    const int VALUE_DEL_ID = 20;
    const string GROUP_ADD = 'group_add';
    const int GROUP_ADD_ID = 21;
    const string GROUP_EDIT = 'group_edit';
    const int GROUP_EDIT_ID = 22;
    const string GROUP_DEL = 'group_del';
    const int GROUP_DEL_ID = 23;
    const string FORMULA_ADD = 'formula_add';
    const int FORMULA_ADD_ID = 24;
    const string FORMULA_EDIT = 'formula_edit';
    const int FORMULA_EDIT_ID = 25;
    const string FORMULA_DEL = 'formula_del';
    const int FORMULA_DEL_ID = 26;
    const string RESULT_ADD = 'result_add';
    const int RESULT_ADD_ID = 27;
    const string RESULT_EDIT = 'result_edit';
    const int RESULT_EDIT_ID = 28;
    const string RESULT_DEL = 'result_del';
    const int RESULT_DEL_ID = 29;

    // views to edit views
    const string VIEW_ADD = 'view_add';
    const int VIEW_ADD_ID = 30;
    const string VIEW_EDIT = 'view_edit';
    const int VIEW_EDIT_ID = 31;
    const string VIEW_DEL = 'view_del';
    const int VIEW_DEL_ID = 32;
    const string COMPONENT_ADD = 'component_add';
    const int COMPONENT_ADD_ID = 33;
    const string COMPONENT_EDIT = 'component_edit';
    const int COMPONENT_EDIT_ID = 34;
    const string COMPONENT_DEL = 'component_del';
    const int COMPONENT_DEL_ID = 35;
    const string VIEW_LINK_ADD = 'view_link_add';
    const int VIEW_LINK_ADD_ID = 36;
    const string VIEW_LINK_EDIT = 'view_link_edit';
    const int VIEW_LINK_EDIT_ID = 37;
    const string VIEW_LINK_DEL = 'view_link_del';
    const int VIEW_LINK_DEL_ID = 38;
    const string COMPONENT_LINK_ADD = 'component_link_add';
    const int COMPONENT_LINK_ADD_ID = 39;
    const string COMPONENT_LINK_EDIT = 'component_link_edit';
    const int COMPONENT_LINK_EDIT_ID = 40;
    const string COMPONENT_LINK_DEL = 'component_link_del';
    const int COMPONENT_LINK_DEL_ID = 41;
    const string VIEW_RELATION_ADD = 'view_relation_add';
    const int VIEW_RELATION_ADD_ID = 42;
    const string VIEW_RELATION_EDIT = 'view_relation_edit';
    const int VIEW_RELATION_EDIT_ID = 43;
    const string VIEW_RELATION_DEL = 'view_relation_del';
    const int VIEW_RELATION_DEL_ID = 44;

    // formula links
    const string FORMULA_LINK_ADD = 'formula_link_add';
    const int FORMULA_LINK_ADD_ID = 45;
    const string FORMULA_LINK_EDIT = 'formula_link_edit';
    const int FORMULA_LINK_EDIT_ID = 46;
    const string FORMULA_LINK_DEL = 'formula_link_del';
    const int FORMULA_LINK_DEL_ID = 47;

    // admin views
    const string USER_ADMIN_ADD = 'admin_user_add';
    const int USER_ADMIN_ADD_ID = 48;
    const string USER_ADMIN_EDIT = 'admin_user_edit';
    const int USER_ADMIN_EDIT_ID = 49;
    const string USER_ADMIN_DEL = 'admin_user_del';
    const int USER_ADMIN_DEL_ID = 50;
    const string LANGUAGE_ADD = 'language_add';
    const int LANGUAGE_ADD_ID = 51;
    const string LANGUAGE_EDIT = 'language_edit';
    const int LANGUAGE_EDIT_ID = 52;
    const string LANGUAGE_DEL = 'language_del';
    const int LANGUAGE_DEL_ID = 53;

    // confirm
    const string CONFIRM_ADD = 'confirm_add';
    const int CONFIRM_ADD_ID = 54;
    const string CONFIRM_EDIT = 'confirm_update';
    const int CONFIRM_EDIT_ID = 55;
    const string CONFIRM_DEL = 'confirm_delete';
    const int CONFIRM_DEL_ID = 56;
    const string CONFIRM_VIEW = 'view_preview';
    const int CONFIRM_VIEWS_ID = 57;

    // fixed
    const string ABOUT = 'about';
    const int ABOUT_ID = 58;
    const string SETUP = 'setup';
    const int SETUP_ID = 59;
    const string SIGNUP = 'signup';
    const int SIGNUP_ID = 60;
    const string LOGIN = 'login';
    const int LOGIN_ID = 61;
    const string LOGIN_ACTIVATE = 'login_activate';
    const int LOGIN_ACTIVATE_ID = 62;
    const string LOGIN_RESET = 'login_reset';
    const int LOGIN_RESET_ID = 63;
    const string LOGOUT = 'logout';
    const int LOGOUT_ID = 64;

    // error log
    const string ERROR_LOG = 'error_log';
    const int ERROR_LOG_ID = 65;
    const string ERROR_UPDATE = 'error_update';
    const int ERROR_UPDATE_ID = 66;

    // search
    const string WORD_FIND = 'word_find';
    const int WORD_FIND_ID = 67;
    const string SEARCH_FULL = 'search_full';
    const int SEARCH_FULL_ID = 68;

    // explain
    const string VALUE_DETAIL = 'value_detail';
    const int VALUE_DETAIL_ID = 69;
    const string RESULT_EXPLAIN = 'result_explain';
    const int RESULT_EXPLAIN_ID = 70;
    const string FORMULA_TEST = 'formula_test';
    const int FORMULA_TEST_ID = 71;

    const string FORMULA_EXPLAIN = 'formula_explain';

    // sandbox
    const string SANDBOX = 'sandbox';
    const int SANDBOX_ID = 72;
    const string UNDO = 'undo';
    const int UNDO_ID = 73;

    // user
    const string USER = 'user';
    const int USER_ID = 74;

    // import
    const string PASTE_TABLE = 'paste_table';
    const int PASTE_TABLE_ID = 75;
    const string IMPORT = 'import';
    const int IMPORT_ID = 76;

    // export
    const string EXPORT = 'export_in_selected_format';
    const int EXPORT_ID = 77;
    const string EXPORT_JSON = 'export_json';
    const int EXPORT_JSON_ID = 78;
    const string EXPORT_XML = 'export_xml';
    const int EXPORT_XML_ID = 79;
    const string EXPORT_CSV = 'export_csv';
    const int EXPORT_CSV_ID = 80;
    const string EXPORT_ODS = 'export_ods';
    const int EXPORT_ODS_ID = 81;

    // jobs
    const string JOB_ASYNC = 'job_async';
    const int JOB_ASYNC_ID = 82;
    const string JOB_CONTROL = 'job_control';
    const int JOB_CONTROL_ID = 83;
    const string JOB_CHECK = 'job_check';
    const int JOB_CHECK_ID = 84;

    // admin
    const string ADMIN_MAIN = 'admin_main';
    const int ADMIN_MAIN_ID = 85;

    // list views for users
    const string VERBS = 'verbs';
    const int VERBS_ID = 86;
    const string COMPLETE = 'complete';
    const int COMPLETE_ID = 87;
    const string BASE_UNITS = 'base_units';
    const int BASE_UNITS_ID = 89;

    // default views
    // TODO easy add missing default views e.g. for formula
    const string WORD = 'word_default';
    const int WORD_ID = 90;
    const string WORD_NAME = 'Word';
    const string VERB = 'verb_default';
    const int VERB_ID = 91;
    const string VERB_NAME = 'Verb';
    const string TRIPLE = 'triple_default';
    const int TRIPLE_ID = 92;
    const string SOURCE = 'source_default';
    const int SOURCE_ID = 93;
    const string REF = 'ref_default';
    const int REF_ID = 94;
    const string LANGUAGE = 'language_default';
    const int LANGUAGE_ID = 95;
    const string VALUE = 'value_default';
    const int VALUE_ID = 96;
    const string FORMULA = 'formula_default';
    const int FORMULA_ID = 97;
    const string RESULT = 'result_default';
    const int RESULT_ID = 98;

    // base views for users
    const string RANKING = 'ranking';
    const int RANKING_ID = 99;
    const string SCIENCE = 'science';
    const int SCIENCE_ID = 100;
    const string SCIENCE_NAME = 'show mainly related words that are relevant in sciences';
    const string HISTORIC = 'hist';
    const int HISTORIC_ID = 101;
    const string HISTORIC_NAME = 'Historic';
    const string HISTORIC_COM = 'show mainly related words that are relevant in sciences';
    const string BIOLOGICAL = 'bio';
    const int BIOLOGICAL_ID = 102;
    const string BIOLOGICAL_NAME = 'Biological';
    const string BIOLOGICAL_COM = 'show what is relevant from the biological point of view';
    const string EDUCATION = 'edu';
    const int EDUCATION_ID = 103;
    const string EDUCATION_NAME = 'Education';
    const string EDUCATION_COM = 'show mainly related words that are relevant in sciences';
    const string TOURISTIC = 'touristic';
    const int TOURISTIC_ID = 104;
    const string TOURISTIC_NAME = 'Touristic';
    const string TOURISTIC_COM = 'show mainly related words that are relevant in sciences';
    const string GRAPH = 'graph';
    const int GRAPH_ID = 105;
    const string GRAPH_NAME = 'Graph';
    const string GRAPH_COM = 'show mainly related words that are relevant in sciences';
    const string SIMPLE = 'simple';
    const int SIMPLE_ID = 106;
    const string SIMPLE_NAME = 'Simple';
    const string SIMPLE_COM = 'show mainly related words that are relevant in sciences';
    const string MATH_CONST = 'math_const';
    const int MATH_CONST_ID = 107;
    const string MATH_CONST_NAME = 'math const';
    const string MATH_CONST_COM = 'Show a mathematical constance and the related words and formulas';

    // TODO Prio 3 resort the views and group it
    const string SYSTEM_LOG = 'system_log';
    const int SYSTEM_LOG_ID = 109;

    // to sort
    const string LANGUAGE_SELECT = 'language_select';
    const int LANGUAGE_SELECT_ID = 88;
    const string PHRASE = 'phrase_default';
    const int PHRASE_ID = 110;


    /*
     * const string for system testing
     */

    // persevered view names for unit and integration tests
    // TN_* means 'test name'
    // TD_* means 'test description'
    // TC_* means 'test code id'
    // TI_* means 'test id'
    const string TEST_ADD_NAME = 'System Test View';
    const string TEST_ADD_VIA_FUNC_NAME = 'System Test View added via sql function';
    const string TEST_ADD_COM = 'System Test View Description';
    const string TEST_ADD = 'System Test View Code Id';
    const string TEST_RENAMED_NAME = 'System Test View Renamed';
    const string TEST_COMPLETE_NAME = 'System Test View Complete';
    const string TEST_EXCLUDED_NAME = 'System Test View Excluded';
    const string TEST_TABLE_NAME = 'System Test View Table';
    const string TEST_ALL_NAME = 'complete';

    // to test a system view (add word) as unit test without database
    const string TEST_FORM_NAME = 'Add word';
    const string TEST_FORM_NEW_NAME = 'Add new word';
    const string TEST_FORM_COM = 'system form to add a word';
    const string TEST_FORM = 'word_add';
    const int TEST_FORM_ID = 3;

    // TODO to be created
    const string WORD_LIST = 'word_list'; //

    const string USER_ADD = 'user_add';
    const string USER_EDIT = 'user_edit';
    const string USER_DEL = 'user_del';
    const string ERR_LOG = 'error_log';
    const string ERR_UPD = 'error_update';

    // the id of the last system view that should be included in the unit testing
    // TODO Prio 1 set to 1
    const int MIN_TEST_ID = 2;
    // TODO Prio 0 set to 37
    const int MAX_TEST_ID = 35;


    const string COMPANY_RATIO_NAME = 'company ratios';
    const string NESN_2016_FS_NAME = 'Nestlé Financial Statement 2016';
    const string LINK_COM = 'System Test description for a view term link';

    // array of view names that used for testing and remove them after the test
    const array RESERVED_NAMES = array(
        self::START_NAME,
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_FUNC_NAME,
        self::TEST_RENAMED_NAME,
        self::TEST_COMPLETE_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

    // array of view names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        self::START_NAME
    );

    // array of test view names create before the test
    const array TEST_VIEWS = array(
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_FUNC_NAME,
        self::TEST_RENAMED_NAME,
        self::TEST_COMPLETE_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

    const array TEST_VIEWS_AUTO_CREATE = array(
        self::TEST_COMPLETE_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

    // system masks that have a user as the main object
    // TODO add the login views e.g. to detect the correct object for the url mapper
    const array USER_MASKS_IDS = [
        self::USER_ADMIN_ADD_ID,
        self::USER_ADMIN_EDIT_ID,
        self::USER_ADMIN_DEL_ID
    ];

    // system masks that have a word as the main object
    const array WORD_MASKS_IDS = [
        self::WORD_ADD_ID,
        self::WORD_EDIT_ID,
        self::WORD_DEL_ID,
        self::WORD_LOG_ID,
    ];

    // system masks that have a verb as the main object
    const array VERB_MASKS_IDS = [
        self::VERB_ADD_ID,
        self::VERB_EDIT_ID,
        self::VERB_DEL_ID
    ];

    // system masks that have a triple as the main object
    const array TRIPLE_MASKS_IDS = [
        self::TRIPLE_ADD_ID,
        self::TRIPLE_EDIT_ID,
        self::TRIPLE_DEL_ID
    ];

    // system masks that have a source as the main object
    const array SOURCE_MASKS_IDS = [
        self::SOURCE_ADD_ID,
        self::SOURCE_EDIT_ID,
        self::SOURCE_DEL_ID
    ];

    // system masks that have a reference as the main object
    const array REF_MASKS_IDS = [
        self::REF_ADD_ID,
        self::REF_EDIT_ID,
        self::REF_DEL_ID
    ];

    // system masks that have a group as the main object
    const array GROUP_MASKS_IDS = [
        self::GROUP_ADD_ID,
        self::GROUP_EDIT_ID,
        self::GROUP_DEL_ID
    ];

    // system masks that have a value as the main object
    const array VALUE_MASKS_IDS = [
        self::VALUE_ADD_ID,
        self::VALUE_EDIT_ID,
        self::VALUE_DEL_ID
    ];

    // system masks that have a formula as the main object
    const array FORMULA_MASKS_IDS = [
        self::FORMULA_ADD_ID,
        self::FORMULA_EDIT_ID,
        self::FORMULA_DEL_ID
    ];

    // system masks that have a result as the main object
    const array RESULT_MASKS_IDS = [
        self::RESULT_ADD_ID,
        self::RESULT_EDIT_ID,
        self::RESULT_DEL_ID
    ];

    // system masks that have a view as the main object
    const array VIEW_MASKS_IDS = [
        self::VIEW_ADD_ID,
        self::VIEW_EDIT_ID,
        self::VIEW_DEL_ID
    ];

    // system masks that have a component as the main object
    const array COMPONENT_MASKS_IDS = [
        self::COMPONENT_ADD_ID,
        self::COMPONENT_EDIT_ID,
        self::COMPONENT_DEL_ID
    ];

    // system masks that have a term to view link as the main object
    const array VIEW_LINK_MASKS_IDS = [
        self::VIEW_LINK_ADD_ID,
        self::VIEW_LINK_EDIT_ID,
        self::VIEW_LINK_DEL_ID
    ];

    // system masks that have a component to view link as the main object
    const array COMPONENT_LINK_MASKS_IDS = [
        self::COMPONENT_LINK_ADD_ID,
        self::COMPONENT_LINK_EDIT_ID,
        self::COMPONENT_LINK_DEL_ID
    ];

    // system masks that have a phrase to formula link as the main object
    const array FORMULA_LINK_MASKS_IDS = [
        self::FORMULA_LINK_ADD_ID,
        self::FORMULA_LINK_EDIT_ID,
        self::FORMULA_LINK_DEL_ID
    ];

    // system masks that have a view to view link as the main object
    const array VIEW_RELATION_MASKS_IDS = [
        self::VIEW_RELATION_ADD_ID,
        self::VIEW_RELATION_EDIT_ID,
        self::VIEW_RELATION_DEL_ID
    ];

    // system masks that only used to display a sandbox object
    const array SHOW_MASKS_IDS = [
        self::START_ID,
    ];

    // system masks that add a sandbox object
    const array ADD_MASKS_IDS = [
        self::WORD_ADD_ID,
        self::VERB_ADD_ID,
        self::TRIPLE_ADD_ID,
        self::SOURCE_ADD_ID,
        self::REF_ADD_ID,
        self::VALUE_ADD_ID,
        self::GROUP_ADD_ID,
        self::FORMULA_ADD_ID,
        self::RESULT_ADD_ID,
        self::VIEW_ADD_ID,
        self::COMPONENT_ADD_ID,
        self::VIEW_LINK_ADD_ID,
        self::COMPONENT_LINK_ADD_ID,
        self::FORMULA_LINK_ADD_ID,
        self::VIEW_RELATION_ADD_ID,
    ];

    // system masks that change a sandbox object
    const array EDIT_MASKS_IDS = [
        self::WORD_EDIT_ID,
        self::VERB_EDIT_ID,
        self::TRIPLE_EDIT_ID,
        self::SOURCE_EDIT_ID,
        self::REF_EDIT_ID,
        self::VALUE_EDIT_ID,
        self::GROUP_EDIT_ID,
        self::FORMULA_EDIT_ID,
        self::RESULT_EDIT_ID,
        self::VIEW_EDIT_ID,
        self::COMPONENT_EDIT_ID,
        self::VIEW_LINK_EDIT_ID,
        self::COMPONENT_LINK_EDIT_ID,
        self::FORMULA_LINK_EDIT_ID,
        self::VIEW_RELATION_EDIT_ID,
    ];

    // system masks that delete a sandbox object
    const array DEL_MASKS_IDS = [
        self::WORD_DEL_ID,
        self::VERB_DEL_ID,
        self::TRIPLE_DEL_ID,
        self::SOURCE_DEL_ID,
        self::REF_DEL_ID,
        self::VALUE_DEL_ID,
        self::GROUP_DEL_ID,
        self::FORMULA_DEL_ID,
        self::RESULT_DEL_ID,
        self::VIEW_DEL_ID,
        self::COMPONENT_DEL_ID,
        self::VIEW_LINK_DEL_ID,
        self::COMPONENT_LINK_DEL_ID,
        self::FORMULA_LINK_DEL_ID,
        self::VIEW_RELATION_DEL_ID,
    ];

    // system masks that should not have the standard zukunft header
    const array NO_NAVBAR_IDS = [
        self::SIGNUP_ID,
        self::LOGIN_ID,
    ];

    // system masks that are used to modify other system masks
    const array SUB_MASKS_IDS = [
        self::WORD_LOG_ID,
    ];

    // system masks that change or delete a sandbox object (see https://wiki.php.net/rfc/spread_operator_for_array )
    const array EDIT_DEL_MASKS_IDS = [
        self::EDIT_MASKS_IDS,
        self::DEL_MASKS_IDS,
    ];

    // TODO Prio 0 convert to a key value map and use id for code_id_to_id and id_to_code_id
    // list of views where the id or the code is used for system testing
    const array TEST_VIEW_IDS = [
        self::START_ID => self::START_CODE,
        self::WORD_ADD_ID => self::WORD_ADD,
        self::WORD_EDIT_ID => self::WORD_EDIT,
        self::WORD_DEL_ID => self::WORD_DEL,
        self::WORD_LOG_ID => self::WORD_LOG,
        self::VERB_ADD_ID => self::VERB_ADD,
        self::VERB_EDIT_ID => self::VERB_EDIT,
        self::VERB_DEL_ID => self::VERB_DEL,
        self::TRIPLE_ADD_ID => self::TRIPLE_ADD,
        self::TRIPLE_EDIT_ID => self::TRIPLE_EDIT,
        self::TRIPLE_DEL_ID => self::TRIPLE_DEL,
        self::SOURCE_ADD_ID => self::SOURCE_ADD,
        self::SOURCE_EDIT_ID => self::SOURCE_EDIT,
        self::SOURCE_DEL_ID => self::SOURCE_DEL,
        self::REF_ADD_ID => self::REF_ADD,
        self::REF_EDIT_ID => self::REF_EDIT,
        self::REF_DEL_ID => self::REF_DEL,
        self::VALUE_ADD_ID => self::VALUE_ADD,
        self::VALUE_EDIT_ID => self::VALUE_EDIT,
        self::VALUE_DEL_ID => self::VALUE_DEL,
        self::GROUP_ADD_ID => self::GROUP_ADD,
        self::GROUP_EDIT_ID => self::GROUP_EDIT,
        self::GROUP_DEL_ID => self::GROUP_DEL,
        self::FORMULA_ADD_ID => self::FORMULA_ADD,
        self::FORMULA_EDIT_ID => self::FORMULA_EDIT,
        self::FORMULA_DEL_ID => self::FORMULA_DEL,
        self::RESULT_ADD_ID => self::RESULT_ADD,
        self::RESULT_EDIT_ID => self::RESULT_EDIT,
        self::RESULT_DEL_ID => self::RESULT_DEL,
        self::VIEW_ADD_ID => self::VIEW_ADD,
        self::VIEW_EDIT_ID => self::VIEW_EDIT,
        self::VIEW_DEL_ID => self::VIEW_DEL,
        self::COMPONENT_ADD_ID => self::COMPONENT_ADD,
        self::COMPONENT_EDIT_ID => self::COMPONENT_EDIT,
        self::COMPONENT_DEL_ID => self::COMPONENT_DEL,
        self::VIEW_LINK_ADD_ID => self::VIEW_LINK_ADD,
        self::VIEW_LINK_EDIT_ID => self::VIEW_LINK_EDIT,
        self::VIEW_LINK_DEL_ID => self::VIEW_LINK_DEL,
        self::COMPONENT_LINK_ADD_ID => self::COMPONENT_LINK_ADD,
        self::COMPONENT_LINK_EDIT_ID => self::COMPONENT_LINK_EDIT,
        self::COMPONENT_LINK_DEL_ID => self::COMPONENT_LINK_DEL,
        self::FORMULA_LINK_ADD_ID => self::FORMULA_LINK_ADD,
        self::FORMULA_LINK_EDIT_ID => self::FORMULA_LINK_EDIT,
        self::FORMULA_LINK_DEL_ID => self::FORMULA_LINK_DEL,
        self::VIEW_RELATION_ADD_ID => self::VIEW_RELATION_ADD,
        self::VIEW_RELATION_EDIT_ID => self::VIEW_RELATION_EDIT,
        self::VIEW_RELATION_DEL_ID => self::VIEW_RELATION_DEL,
        self::USER_ADMIN_ADD_ID => self::USER_ADMIN_ADD,
        self::USER_ADMIN_EDIT_ID => self::USER_ADMIN_EDIT,
        self::USER_ADMIN_DEL_ID => self::USER_ADMIN_DEL,
        self::LANGUAGE_ADD_ID => self::LANGUAGE_ADD,
        self::LANGUAGE_EDIT_ID => self::LANGUAGE_EDIT,
        self::LANGUAGE_DEL_ID => self::LANGUAGE_DEL,
        self::CONFIRM_ADD_ID => self::CONFIRM_ADD,
        self::CONFIRM_EDIT_ID => self::CONFIRM_EDIT,
        self::CONFIRM_DEL_ID => self::CONFIRM_DEL,
        self::CONFIRM_VIEWS_ID => self::CONFIRM_VIEW,
        self::ABOUT_ID => self::ABOUT,
        self::SETUP_ID => self::SETUP,
        self::SIGNUP_ID => self::SIGNUP,
        self::LOGIN_ID => self::LOGIN,
        self::LOGIN_ACTIVATE_ID => self::LOGIN_ACTIVATE,
        self::LOGIN_RESET_ID => self::LOGIN_RESET,
        self::LOGOUT_ID => self::LOGOUT,
        self::ERROR_LOG_ID => self::ERROR_LOG,
        self::ERROR_UPDATE_ID => self::ERROR_UPDATE,
        self::WORD_FIND_ID => self::WORD_FIND,
        self::SEARCH_FULL_ID => self::SEARCH_FULL,
        self::VALUE_DETAIL_ID => self::VALUE_DETAIL,
        self::RESULT_EXPLAIN_ID => self::RESULT_EXPLAIN,
        self::FORMULA_TEST_ID => self::FORMULA_TEST,
        self::SANDBOX_ID => self::SANDBOX,
        self::UNDO_ID => self::UNDO,
        self::USER_ID => self::USER,
        self::PASTE_TABLE_ID => self::PASTE_TABLE,
        self::IMPORT_ID => self::IMPORT,
        self::EXPORT_ID => self::EXPORT,
        self::EXPORT_JSON_ID => self::EXPORT_JSON,
        self::EXPORT_XML_ID => self::EXPORT_XML,
        self::EXPORT_CSV_ID => self::EXPORT_CSV,
        self::EXPORT_ODS_ID => self::EXPORT_ODS,
        self::JOB_ASYNC_ID => self::JOB_ASYNC,
        self::JOB_CONTROL_ID => self::JOB_CONTROL,
        self::JOB_CHECK_ID => self::JOB_CHECK,
        self::ADMIN_MAIN_ID => self::ADMIN_MAIN,
        self::VERBS_ID => self::VERBS,
        self::COMPLETE_ID => self::COMPLETE,
        self::BASE_UNITS_ID => self::BASE_UNITS,
        self::WORD_ID => self::WORD,
        self::VERB_ID => self::VERB,
        self::TRIPLE_ID => self::TRIPLE,
        self::SOURCE_ID => self::SOURCE,
        self::REF_ID => self::REF,
        self::LANGUAGE_ID => self::LANGUAGE,
        self::VALUE_ID => self::VALUE,
        self::FORMULA_ID => self::FORMULA,
        self::RESULT_ID => self::RESULT,
        self::RANKING_ID => self::RANKING,
        self::SCIENCE_ID => self::SCIENCE,
        self::HISTORIC_ID => self::HISTORIC,
        self::BIOLOGICAL_ID => self::BIOLOGICAL,
        self::EDUCATION_ID => self::EDUCATION,
        self::TOURISTIC_ID => self::TOURISTIC,
        self::GRAPH_ID => self::GRAPH,
        self::SIMPLE_ID => self::SIMPLE,
        self::MATH_CONST_ID => self::MATH_CONST,
        self::SYSTEM_LOG_ID => self::SYSTEM_LOG,
        self::LANGUAGE_SELECT_ID => self::LANGUAGE_SELECT,
        self::PHRASE_ID => self::PHRASE,
    ];

    const array SYSTEM_VIEWS = [
        self::START_CODE,
        self::WORD_ADD,
        self::WORD_EDIT,
        self::WORD_DEL,
        self::WORD_LOG,
        self::VERB_ADD,
        self::VERB_EDIT,
        self::VERB_DEL,
        self::TRIPLE_ADD,
        self::TRIPLE_EDIT,
        self::TRIPLE_DEL,
        self::SOURCE_ADD,
        self::SOURCE_EDIT,
        self::SOURCE_DEL,
        self::REF_ADD,
        self::REF_EDIT,
        self::REF_DEL,
        self::VALUE_ADD,
        self::VALUE_EDIT,
        self::VALUE_DEL,
        self::GROUP_ADD,
        self::GROUP_EDIT,
        self::GROUP_DEL,
        self::FORMULA_ADD,
        self::FORMULA_EDIT,
        self::FORMULA_DEL,
        self::RESULT_ADD,
        self::RESULT_EDIT,
        self::RESULT_DEL,
        self::VIEW_ADD,
        self::VIEW_EDIT,
        self::VIEW_DEL,
        self::COMPONENT_ADD,
        self::COMPONENT_EDIT,
        self::COMPONENT_DEL,
        self::VIEW_LINK_ADD,
        self::VIEW_LINK_EDIT,
        self::VIEW_LINK_DEL,
        self::COMPONENT_LINK_ADD,
        self::COMPONENT_LINK_EDIT,
        self::COMPONENT_LINK_DEL,
        self::FORMULA_LINK_ADD,
        self::FORMULA_LINK_EDIT,
        self::FORMULA_LINK_DEL,
        self::VIEW_RELATION_ADD,
        self::VIEW_RELATION_EDIT,
        self::VIEW_RELATION_DEL,
        self::USER_ADMIN_ADD,
        self::USER_ADMIN_EDIT,
        self::USER_ADMIN_DEL,
        self::LANGUAGE_ADD,
        self::LANGUAGE_EDIT,
        self::LANGUAGE_DEL,
        self::CONFIRM_ADD,
        self::CONFIRM_EDIT,
        self::CONFIRM_DEL,
        self::CONFIRM_VIEW,
        self::ABOUT,
        self::SETUP,
        self::SIGNUP,
        self::LOGIN,
        self::LOGIN_ACTIVATE,
        self::LOGIN_RESET,
        self::LOGOUT,
        self::ERROR_LOG,
        self::ERROR_UPDATE,
        self::WORD_FIND,
        self::SEARCH_FULL,
        self::VALUE_DETAIL,
        self::RESULT_EXPLAIN,
        self::FORMULA_TEST,
        self::SANDBOX,
        self::UNDO,
        self::USER,
        self::PASTE_TABLE,
        self::IMPORT,
        self::EXPORT,
        self::EXPORT_JSON,
        self::EXPORT_XML,
        self::EXPORT_CSV,
        self::EXPORT_ODS,
        self::JOB_ASYNC,
        self::JOB_CONTROL,
        self::JOB_CHECK,
        self::ADMIN_MAIN,
    ];

    /**
     * returns the code id of the base view that is used to show the changeable object
     * e.g. for word_edit the word view is returned
     * TODO easy add missing default views e.g. for component
     *
     * @param string $msk_ci
     * @return string
     */
    function system_to_base(string $msk_ci): string
    {
        return match ($msk_ci) {
            self::WORD_ADD, self::WORD_EDIT, self::WORD_DEL => self::WORD,
            self::VERB_ADD, self::VERB_EDIT, self::VERB_DEL => self::VERB,
            self::TRIPLE_ADD, self::TRIPLE_EDIT, self::TRIPLE_DEL => self::TRIPLE,
            self::SOURCE_ADD, self::SOURCE_EDIT, self::SOURCE_DEL => self::SOURCE,
            self::REF_ADD, self::REF_EDIT, self::REF_DEL => self::REF,
            self::VALUE_ADD, self::VALUE_EDIT, self::VALUE_DEL => self::VALUE,
            //self::GROUP_ADD, self::GROUP_EDIT, self::GROUP_DEL => self::GROUP,
            self::FORMULA_ADD, self::FORMULA_EDIT, self::FORMULA_DEL => self::FORMULA,
            //self::VIEW_ADD, self::VIEW_EDIT, self::VIEW_DEL => self::VIEW,
            //self::COMPONENT_ADD, self::COMPONENT_EDIT, self::COMPONENT_DEL => self::COMPONENT,
            default => ''
        };
    }

    function code_id_to_id(string $code_id): int
    {
        $msk_codes = array_flip(self::TEST_VIEW_IDS);
        if (array_key_exists($code_id, $msk_codes)) {
            return $msk_codes[$code_id];
        } else {
            if ($code_id != '') {
                log_err('id for view code id ' . $code_id . ' not found');
            } else {
                log_warning('view code id is empty');
            }
            return 0;
        }
    }

    function id_to_code_id(int $id): string
    {
        $msk_ids = self::TEST_VIEW_IDS;
        if (array_key_exists($id, $msk_ids)) {
            return $msk_ids[$id];
        } else {
            log_err('code id for view id ' . $id . ' not found');
            return 0;
        }
    }

    /**
     * @param string $class the class name including the path
     * @return int the database id of the view to add the object or -1 if no view is assigned yet
     */
    function class_to_add(string $class): int
    {
        $short = substr(strrchr($class, '\\'), 1) ?: $class;
        return match ($short) {
            'word' => self::WORD_ADD_ID,
            'verb' => self::VERB_ADD_ID,
            'triple' => self::TRIPLE_ADD_ID,
            'source' => self::SOURCE_ADD_ID,
            'ref' => self::REF_ADD_ID,
            'value' => self::VALUE_ADD_ID,
            'group' => self::GROUP_ADD_ID,
            'formula' => self::FORMULA_ADD_ID,
            'formula_link' => self::FORMULA_LINK_ADD_ID,
            'result' => self::RESULT_ADD_ID,
            'view' => self::VIEW_ADD_ID,
            'component' => self::COMPONENT_ADD_ID,
            'view_link' => self::VIEW_LINK_ADD_ID,
            'component_link' => self::COMPONENT_LINK_ADD_ID,
            'view_relation' => self::VIEW_RELATION_ADD_ID,
            'user' => self::USER_ADMIN_ADD_ID,
            'language' => self::LANGUAGE_ADD_ID,
            default => -1
        };
    }

    /**
     * @param string $class the class name including the path
     * @return int the database id of the view to edit the object or -1 if no view is assigned yet
     */
    function class_to_edit(string $class): int
    {
        $short = substr(strrchr($class, '\\'), 1) ?: $class;
        return match ($short) {
            'word' => self::WORD_EDIT_ID,
            'verb' => self::VERB_EDIT_ID,
            'triple' => self::TRIPLE_EDIT_ID,
            'source' => self::SOURCE_EDIT_ID,
            'ref' => self::REF_EDIT_ID,
            'value' => self::VALUE_EDIT_ID,
            'group' => self::GROUP_EDIT_ID,
            'formula' => self::FORMULA_EDIT_ID,
            'formula_link' => self::FORMULA_LINK_EDIT_ID,
            'result' => self::RESULT_EDIT_ID,
            'view' => self::VIEW_EDIT_ID,
            'component' => self::COMPONENT_EDIT_ID,
            'view_link' => self::VIEW_LINK_EDIT_ID,
            'component_link' => self::COMPONENT_LINK_EDIT_ID,
            'view_relation' => self::VIEW_RELATION_EDIT_ID,
            'user' => self::USER_ADMIN_EDIT_ID,
            'language' => self::LANGUAGE_EDIT_ID,
            default => -1
        };
    }

    /**
     * @param string $class the class name including the path
     * @return int the database id of the view to delete the object or -1 if no view is assigned yet
     */
    function class_to_del(string $class): int
    {
        $short = substr(strrchr($class, '\\'), 1) ?: $class;
        return match ($short) {
            'word' => self::WORD_DEL_ID,
            'verb' => self::VERB_DEL_ID,
            'triple' => self::TRIPLE_DEL_ID,
            'source' => self::SOURCE_DEL_ID,
            'ref' => self::REF_DEL_ID,
            'value' => self::VALUE_DEL_ID,
            'group' => self::GROUP_DEL_ID,
            'formula' => self::FORMULA_DEL_ID,
            'formula_link' => self::FORMULA_LINK_DEL_ID,
            'result' => self::RESULT_DEL_ID,
            'view' => self::VIEW_DEL_ID,
            'component' => self::COMPONENT_DEL_ID,
            'view_link' => self::VIEW_LINK_DEL_ID,
            'component_link' => self::COMPONENT_LINK_DEL_ID,
            'view_relation' => self::VIEW_RELATION_DEL_ID,
            'user' => self::USER_ADMIN_DEL_ID,
            'language' => self::LANGUAGE_DEL_ID,
            default => -1
        };
    }

}
