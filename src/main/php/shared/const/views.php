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

    // curl views
    const string WORD_ADD = 'word_add';
    const int WORD_ADD_ID = 2;
    const string WORD_EDIT = 'word_edit';
    const int WORD_EDIT_ID = 3;
    const string WORD_DEL = 'word_del';
    const int WORD_DEL_ID = 4;
    const string VERB_ADD = 'verb_add';
    const int VERB_ADD_ID = 5;
    const string VERB_EDIT = 'verb_edit';
    const int VERB_EDIT_ID = 6;
    const string VERB_DEL = 'verb_del';
    const int VERB_DEL_ID = 7;
    const string TRIPLE_ADD = 'triple_add';
    const int TRIPLE_ADD_ID = 8;
    const string TRIPLE_EDIT = 'triple_edit';
    const int TRIPLE_EDIT_ID = 9;
    const string TRIPLE_DEL = 'triple_del';
    const int TRIPLE_DEL_ID = 10;
    const string SOURCE_ADD = 'source_add';
    const int SOURCE_ADD_ID = 11;
    const string SOURCE_EDIT = 'source_edit';
    const int SOURCE_EDIT_ID = 12;
    const string SOURCE_DEL = 'source_del';
    const int SOURCE_DEL_ID = 13;
    const string REF_ADD = 'ref_add';
    const int REF_ADD_ID = 14;
    const string REF_EDIT = 'ref_edit';
    const int REF_EDIT_ID = 15;
    const string REF_DEL = 'ref_del';
    const int REF_DEL_ID = 16;
    const string VALUE_ADD = 'value_add';
    const int VALUE_ADD_ID = 17;
    const string VALUE_EDIT = 'value_edit';
    const int VALUE_EDIT_ID = 18;
    const string VALUE_DEL = 'value_del';
    const int VALUE_DEL_ID = 19;
    const string GROUP_ADD = 'group_add';
    const int GROUP_ADD_ID = 20;
    const string GROUP_EDIT = 'group_edit';
    const int GROUP_EDIT_ID = 21;
    const string GROUP_DEL = 'group_del';
    const int GROUP_DEL_ID = 22;
    const string FORMULA_ADD = 'formula_add';
    const int FORMULA_ADD_ID = 23;
    const string FORMULA_EDIT = 'formula_edit';
    const int FORMULA_EDIT_ID = 24;
    const string FORMULA_DEL = 'formula_del';
    const int FORMULA_DEL_ID = 25;
    const string RESULT_ADD = 'result_add';
    const int RESULT_ADD_ID = 26;
    const string RESULT_EDIT = 'result_edit';
    const int RESULT_EDIT_ID = 27;
    const string RESULT_DEL = 'result_del';
    const int RESULT_DEL_ID = 28;

    // views to edit views
    const string VIEW_ADD = 'view_add';
    const int VIEW_ADD_ID = 29;
    const string VIEW_EDIT = 'view_edit';
    const int VIEW_EDIT_ID = 30;
    const string VIEW_DEL = 'view_del';
    const int VIEW_DEL_ID = 31;
    const string COMPONENT_ADD = 'component_add';
    const int COMPONENT_ADD_ID = 32;
    const string COMPONENT_EDIT = 'component_edit';
    const int COMPONENT_EDIT_ID = 33;
    const string COMPONENT_DEL = 'component_del';
    const int COMPONENT_DEL_ID = 34;
    const string VIEW_LINK_ADD = 'view_link_add';
    const int VIEW_LINK_ADD_ID = 35;
    const string VIEW_LINK_EDIT = 'view_link_edit';
    const int VIEW_LINK_EDIT_ID = 36;
    const string VIEW_LINK_DEL = 'view_link_del';
    const int VIEW_LINK_DEL_ID = 37;
    const string COMPONENT_LINK_ADD = 'component_link_add';
    const int COMPONENT_LINK_ADD_ID = 38;
    const string COMPONENT_LINK_EDIT = 'component_link_edit';
    const int COMPONENT_LINK_EDIT_ID = 39;
    const string COMPONENT_LINK_DEL = 'component_link_del';
    const int COMPONENT_LINK_DEL_ID = 40;
    const string FORMULA_LINK_ADD = 'formula_link_add';
    const int FORMULA_LINK_ADD_ID = 41;
    const string FORMULA_LINK_EDIT = 'formula_link_edit';
    const int FORMULA_LINK_EDIT_ID = 42;
    const string FORMULA_LINK_DEL = 'formula_link_del';
    const int FORMULA_LINK_DEL_ID = 43;

    const string FORMULA_EXPLAIN = 'formula_explain';
    const string FORMULA_TEST = 'formula_test';

    const string VERBS = 'verbs';
    const string USER = 'user';
    const int USER_ID = 67;
    const string USER_ADD = 'user_add';
    const string USER_EDIT = 'user_edit';
    const string USER_DEL = 'user_del';
    const string USER_ADMIN_ADD = 'admin_user_add';
    const int USER_ADMIN_ADD_ID = 44;
    const string USER_ADMIN_EDIT = 'admin_user_edit';
    const int USER_ADMIN_EDIT_ID = 45;
    const string USER_ADMIN_DEL = 'admin_user_del';
    const int USER_ADMIN_DEL_ID = 46;
    const string ERR_LOG = 'error_log';
    const string ERR_UPD = 'error_update';
    const string IMPORT = 'import';

    // types
    const string LANGUAGE_ADD = 'language_add';
    const int LANGUAGE_ADD_ID = 29;
    const string LANGUAGE_EDIT = 'language_edit';
    const int LANGUAGE_EDIT_ID = 29;
    const string LANGUAGE_DEL = 'language_del';
    const int LANGUAGE_DEL_ID = 29;

    // the id of the last system view that should be included in the unit testing
    // TODO Prio 1 set to 1
    const int MIN_TEST_ID = 2;
    const int MAX_TEST_ID = 34;

    // default views
    // TODO easy add missing default views e.g. for formula
    const string WORD = 'Word';
    const int WORD_ID = 81;
    const string WORD_CODE_ID = 'word';
    const string VERB = 'Verb';
    const int VERB_ID = 82;
    const string VERB_CODE_ID = 'verb';
    const string TRIPLE = 'Triple';
    const int TRIPLE_ID = 83;
    const string SOURCE = 'Source';
    const int SOURCE_ID = 84;
    const string REF = 'Reference';
    const int REF_ID = 85;
    const string VALUE_DISPLAY = 'Display Number';
    const string FORMULA = 'source';
    const int FORMULA_ID = 88;
    const string LANGUAGE = 'Language';
    const int LANGUAGE_ID = 86;

    // functional views
    const string WORD_FIND = 'word_find';

    // TODO to be created
    const string WORD_LIST = 'word_list'; //

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
    const string TEST_ADD_VIA_SQL_NAME = 'System Test View added via sql insert';
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
    const string SCIENCE = 'science';
    const string SCIENCE_NAME = 'show mainly related words that are relevant in sciences';
    const int SCIENCE_ID = 91;
    const string HISTORIC_NAME = 'Historic';
    const string HISTORIC_COM = 'show mainly related words that are relevant in sciences';
    const int HISTORIC_ID = 92;
    const string BIOLOGICAL_NAME = 'Biological';
    const string BIOLOGICAL_COM = 'show what is relevant from the biological point of view';
    const int BIOLOGICAL_ID = 93;
    const string EDUCATION_NAME = 'Education';
    const string EDUCATION_COM = 'show mainly related words that are relevant in sciences';
    const int EDUCATION_ID = 53;
    const string TOURISTIC_NAME = 'Touristic';
    const string TOURISTIC_COM = 'show mainly related words that are relevant in sciences';
    const int TOURISTIC_ID = 95;
    const string GRAPH_NAME = 'Graph';
    const string GRAPH_COM = 'show mainly related words that are relevant in sciences';
    const int GRAPH_ID = 96;
    const string SIMPLE_NAME = 'Simple';
    const string SIMPLE_COM = 'show mainly related words that are relevant in sciences';
    const int SIMPLE_ID = 97;

    const string COMPANY_RATIO_NAME = 'company ratios';
    const string NESN_2016_FS_NAME = 'Nestlé Financial Statement 2016';
    const string LINK_COM = 'System Test description for a view term link';

    // array of view names that used for testing and remove them after the test
    const array RESERVED_NAMES = array(
        self::START_NAME,
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_SQL_NAME,
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
        self::TEST_ADD_VIA_SQL_NAME,
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
        self::WORD_DEL_ID
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
    ];

    // system masks that change or delete a sandbox object (see https://wiki.php.net/rfc/spread_operator_for_array )
    const array EDIT_DEL_MASKS_IDS = [
        self::EDIT_MASKS_IDS,
        self::DEL_MASKS_IDS,
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
            self::VALUE_ADD, self::VALUE_EDIT, self::VALUE_DEL => self::VALUE_DISPLAY,
            //self::GROUP_ADD, self::GROUP_EDIT, self::GROUP_DEL => self::GROUP,
            self::FORMULA_ADD, self::FORMULA_EDIT, self::FORMULA_DEL => self::FORMULA,
            //self::VIEW_ADD, self::VIEW_EDIT, self::VIEW_DEL => self::VIEW,
            //self::COMPONENT_ADD, self::COMPONENT_EDIT, self::COMPONENT_DEL => self::COMPONENT,
            default => ''
        };
    }

    function code_id_to_id(string $code_id): int
    {
        return match ($code_id) {
            self::START => self::START_ID,
            self::WORD_ADD => self::WORD_ADD_ID,
            self::WORD_EDIT => self::WORD_EDIT_ID,
            self::WORD_DEL => self::WORD_DEL_ID,
            self::VERB_ADD => self::VERB_ADD_ID,
            self::VERB_EDIT => self::VERB_EDIT_ID,
            self::VERB_DEL => self::VERB_DEL_ID,
            self::TRIPLE_ADD => self::TRIPLE_ADD_ID,
            self::TRIPLE_EDIT => self::TRIPLE_EDIT_ID,
            self::TRIPLE_DEL => self::TRIPLE_DEL_ID,
            self::SOURCE_ADD => self::SOURCE_ADD_ID,
            self::SOURCE_EDIT => self::SOURCE_EDIT_ID,
            self::SOURCE_DEL => self::SOURCE_DEL_ID,
            self::REF_ADD => self::REF_ADD_ID,
            self::REF_EDIT => self::REF_EDIT_ID,
            self::REF_DEL => self::REF_DEL_ID,
            self::VALUE_ADD => self::VALUE_ADD_ID,
            self::VALUE_EDIT => self::VALUE_EDIT_ID,
            self::VALUE_DEL => self::VALUE_DEL_ID,
            self::GROUP_ADD => self::GROUP_ADD_ID,
            self::GROUP_EDIT => self::GROUP_EDIT_ID,
            self::GROUP_DEL => self::GROUP_DEL_ID,
            self::FORMULA_ADD => self::FORMULA_ADD_ID,
            self::FORMULA_EDIT => self::FORMULA_EDIT_ID,
            self::FORMULA_DEL => self::FORMULA_DEL_ID,
            self::RESULT_ADD => self::RESULT_ADD_ID,
            self::RESULT_EDIT => self::RESULT_EDIT_ID,
            self::RESULT_DEL => self::RESULT_DEL_ID,
            self::VIEW_ADD => self::VIEW_ADD_ID,
            self::VIEW_EDIT => self::VIEW_EDIT_ID,
            self::VIEW_DEL => self::VIEW_DEL_ID,
            self::COMPONENT_ADD => self::COMPONENT_ADD_ID,
            self::COMPONENT_EDIT => self::COMPONENT_EDIT_ID,
            self::COMPONENT_DEL => self::COMPONENT_DEL_ID,
            self::WORD => self::WORD_ID,
            self::VERB => self::VERB_ID,
            self::TRIPLE => self::TRIPLE_ID,
            self::SOURCE => self::SOURCE_ID,
            default => 0
        };
    }

    function id_to_code_id(int $id): string
    {
        return match ($id) {
            self::START_ID => self::START,
            self::WORD_ADD_ID => self::WORD_ADD,
            self::WORD_EDIT_ID => self::WORD_EDIT,
            self::WORD_DEL_ID => self::WORD_DEL,
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
            self::WORD_ID => self::WORD,
            self::VERB_ID => self::VERB,
            self::TRIPLE_ID => self::TRIPLE,
            self::SOURCE_ID => self::SOURCE,
            default => ''
        };
    }

}
