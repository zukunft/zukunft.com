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

namespace shared\const;

class views
{

    // code_id, database id and name of internal views used by the system
    // these views used by the program that are never supposed to be changed
    // *_CODE or * is the view / mask code id that is expected never to change
    // *_ID is the view / mask id that is expected never to change
    // *_NAME is the name of the view if it differs from the code id
    // *_COM is the comment or description used for the tooltip
    const START = 'start';
    const START_CODE = 'entry_view'; // TODO combine
    const START_NAME = 'Start view';
    const START_COM = 'A dynamic entry mask that initially shows a table for calculations with the biggest problems from the user point of view and suggestions what the user can do to solve these problems. Used also as fallback view.';
    const START_ID = 1;

    // curl views
    const WORD_ADD = 'word_add';
    const WORD_ADD_ID = 2;
    const WORD_EDIT = 'word_edit';
    const WORD_EDIT_ID = 3;
    const WORD_DEL = 'word_del';
    const WORD_DEL_ID = 4;
    const VERB_ADD = 'verb_add';
    const VERB_ADD_ID = 4;
    const VERB_EDIT = 'verb_edit';
    const VERB_EDIT_ID = 4;
    const VERB_DEL = 'verb_del';
    const VERB_DEL_ID = 4;
    const TRIPLE_ADD = 'triple_add';
    const TRIPLE_ADD_ID = 4;
    const TRIPLE_EDIT = 'triple_edit';
    const TRIPLE_EDIT_ID = 4;
    const TRIPLE_DEL = 'triple_del';
    const TRIPLE_DEL_ID = 4;
    const SOURCE_ADD = 'source_add';
    const SOURCE_ADD_ID = 11;
    const SOURCE_EDIT = 'source_edit';
    const SOURCE_EDIT_ID = 12;
    const SOURCE_DEL = 'source_del';
    const SOURCE_DEL_ID = 13;
    const REF_ADD = 'ref_add';
    const REF_ADD_ID = 14;
    const REF_EDIT = 'ref_edit';
    const REF_EDIT_ID = 15;
    const REF_DEL = 'ref_del';
    const REF_DEL_ID = 16;
    const VALUE_ADD = 'value_add';
    const VALUE_ADD_ID = 17;
    const VALUE_EDIT = 'value_edit';
    const VALUE_EDIT_ID = 18;
    const VALUE_DEL = 'value_del';
    const VALUE_DEL_ID = 19;
    const GROUP_ADD = 'group_add';
    const GROUP_ADD_ID = 20;
    const GROUP_EDIT = 'group_edit';
    const GROUP_EDIT_ID = 21;
    const GROUP_DEL = 'group_del';
    const GROUP_DEL_ID = 22;
    const FORMULA_ADD = 'formula_add';
    const FORMULA_ADD_ID = 23;
    const FORMULA_EDIT = 'formula_edit';
    const FORMULA_EDIT_ID = 24;
    const FORMULA_DEL = 'formula_del';
    const FORMULA_DEL_ID = 25;
    const FORMULA_EXPLAIN = 'formula_explain';
    const FORMULA_TEST = 'formula_test';
    const RESULT_ADD = 'result_add';
    const RESULT_ADD_ID = 26;
    const RESULT_EDIT = 'result_edit';
    const RESULT_EDIT_ID = 26;
    const RESULT_DEL = 'result_del';
    const RESULT_DEL_ID = 27;
    const VERBS = 'verbs';
    const USER = 'user';
    const USER_ADD = 'user_add';
    const USER_EDIT = 'user_edit';
    const USER_DEL = 'user_del';
    const ERR_LOG = 'error_log';
    const ERR_UPD = 'error_update';
    const IMPORT = 'import';
    // views to edit views
    const VIEW_ADD = 'view_add';
    const VIEW_ADD_ID = 28;
    const VIEW_EDIT = 'view_edit';
    const VIEW_EDIT_ID = 29;
    const VIEW_DEL = 'view_del';
    const VIEW_DEL_ID = 30;
    const COMPONENT_ADD = 'component_add';
    const COMPONENT_ADD_ID = 31;
    const COMPONENT_EDIT = 'component_edit';
    const COMPONENT_EDIT_ID = 32;
    const COMPONENT_DEL = 'component_del';
    const COMPONENT_DEL_ID = 33;
    const COMPONENT_LINK = 'component_link';
    const COMPONENT_UNLINK = 'component_unlink';

    // types
    const LANGUAGE_ADD = 'language_add';
    const LANGUAGE_ADD_ID = 29;
    const LANGUAGE_EDIT = 'language_edit';
    const LANGUAGE_EDIT_ID = 29;
    const LANGUAGE_DEL = 'language_del';
    const LANGUAGE_DEL_ID = 29;

    // default views
    // TODO easy add missing default views e.g. for formula
    const WORD = 'word';
    const WORD_ID = 44;
    const WORD_CODE_ID = 'word';
    const VERB = 'verb';
    const VERB_ID = 45;
    const TRIPLE = 'triple';
    const TRIPLE_ID = 46;
    const SOURCE = 'source';
    const SOURCE_ID = 47;
    const VALUE_DISPLAY = 'value';
    const FORMULA = 'source';
    const FORMULA_ID = 48;
    const LANGUAGE = 'language';
    const LANGUAGE_ID = 49;

    // functional views
    const WORD_FIND = 'word_find';

    /*
     * const for system testing
     */

    // persevered view names for unit and integration tests
    // TN_* means 'test name'
    // TD_* means 'test description'
    // TC_* means 'test code id'
    // TI_* means 'test id'
    const TEST_ADD_NAME = 'System Test View';
    const TEST_ADD_VIA_FUNC_NAME = 'System Test View added via sql function';
    const TEST_ADD_VIA_SQL_NAME = 'System Test View added via sql insert';
    const TEST_ADD_COM = 'System Test View Description';
    const TEST_ADD = 'System Test View Code Id';
    const TEST_RENAMED_NAME = 'System Test View Renamed';
    const TEST_COMPLETE_NAME = 'System Test View Complete';
    const TEST_EXCLUDED_NAME = 'System Test View Excluded';
    const TEST_TABLE_NAME = 'System Test View Table';
    const TEST_ALL_NAME = 'complete';

    // to test a system view (add word) as unit test without database
    const TEST_FORM_NAME = 'Add word';
    const TEST_FORM_NEW_NAME = 'Add new word';
    const TEST_FORM_COM = 'system form to add a word';
    const TEST_FORM = 'word_add';
    const TEST_FORM_ID = 3;
    const SCIENCE = 'science';
    const SCIENCE_NAME = 'show mainly related words that are relevant in sciences';
    const SCIENCE_ID = 50;
    const HISTORIC_NAME = 'Historic';
    const HISTORIC_COM = 'show mainly related words that are relevant in sciences';
    const HISTORIC_ID = 51;
    const BIOLOGICAL_NAME = 'Biological';
    const BIOLOGICAL_COM = 'show what is relevant from the biological point of view';
    const BIOLOGICAL_ID = 52;
    const EDUCATION_NAME = 'Education';
    const EDUCATION_COM = 'show mainly related words that are relevant in sciences';
    const EDUCATION_ID = 53;
    const TOURISTIC_NAME = 'Touristic';
    const TOURISTIC_COM = 'show mainly related words that are relevant in sciences';
    const TOURISTIC_ID = 54;
    const GRAPH_NAME = 'Graph';
    const GRAPH_COM = 'show mainly related words that are relevant in sciences';
    const GRAPH_ID = 55;
    const SIMPLE_NAME = 'Simple';
    const SIMPLE_COM = 'show mainly related words that are relevant in sciences';
    const SIMPLE_ID = 56;

    const COMPANY_RATIO_NAME = 'Company ratios';
    const NESN_2016_FS_NAME = 'Nestlé Financial Statement 2016';
    const LINK_COM = 'System Test description for a view term link';

    // array of view names that used for testing and remove them after the test
    const RESERVED_NAMES = array(
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
    const FIXED_NAMES = array(
        self::START_NAME
    );

    // array of test view names create before the test
    const TEST_VIEWS = array(
        self::TEST_ADD_NAME,
        self::TEST_ADD_VIA_SQL_NAME,
        self::TEST_ADD_VIA_FUNC_NAME,
        self::TEST_RENAMED_NAME,
        self::TEST_COMPLETE_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );

    const TEST_VIEWS_AUTO_CREATE = array(
        self::TEST_COMPLETE_NAME,
        self::TEST_EXCLUDED_NAME,
        self::TEST_TABLE_NAME
    );


    // system masks that have a word as the main object
    const WORD_MASKS_IDS = [
        self::WORD_ADD_ID,
        self::WORD_EDIT_ID,
        self::WORD_DEL_ID
    ];

    // system masks that have a verb as the main object
    const VERB_MASKS_IDS = [
        self::VERB_ADD_ID,
        self::VERB_EDIT_ID,
        self::VERB_DEL_ID
    ];

    // system masks that have a triple as the main object
    const TRIPLE_MASKS_IDS = [
        self::TRIPLE_ADD_ID,
        self::TRIPLE_EDIT_ID,
        self::TRIPLE_DEL_ID
    ];

    // system masks that have a source as the main object
    const SOURCE_MASKS_IDS = [
        self::SOURCE_ADD_ID,
        self::SOURCE_EDIT_ID,
        self::SOURCE_DEL_ID
    ];

    // system masks that have a reference as the main object
    const REF_MASKS_IDS = [
        self::REF_ADD_ID,
        self::REF_EDIT_ID,
        self::REF_DEL_ID
    ];

    // system masks that have a value as the main object
    const VALUE_MASKS_IDS = [
        self::VALUE_ADD_ID,
        self::VALUE_EDIT_ID,
        self::VALUE_DEL_ID
    ];

    // system masks that have a formula as the main object
    const FORMULA_MASKS_IDS = [
        self::FORMULA_ADD_ID,
        self::FORMULA_EDIT_ID,
        self::FORMULA_DEL_ID
    ];

    // system masks that have a result as the main object
    const RESULT_MASKS_IDS = [
        self::RESULT_ADD_ID,
        self::RESULT_EDIT_ID,
        self::RESULT_DEL_ID
    ];

    // system masks that have a view as the main object
    const VIEW_MASKS_IDS = [
        self::VIEW_ADD_ID,
        self::VIEW_EDIT_ID,
        self::VIEW_DEL_ID
    ];

    // system masks that have a component as the main object
    const COMPONENT_MASKS_IDS = [
        self::COMPONENT_ADD_ID,
        self::COMPONENT_EDIT_ID,
        self::COMPONENT_DEL_ID
    ];

    // system masks that change or delete a sandbox object
    const EDIT_DEL_MASKS_IDS = [
        self::WORD_EDIT_ID,
        self::WORD_DEL_ID,
        self::SOURCE_EDIT_ID,
        self::SOURCE_DEL_ID,
    ];

    /**
     * returns the code id of the base view that is used to show the changeable object
     * e.g. for word_edit the word view is returned
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
            self::WORD_ID => self::WORD,
            self::VERB_ID => self::VERB,
            self::TRIPLE_ID => self::TRIPLE,
            self::SOURCE_ID => self::SOURCE,
            default => ''
        };
    }

}
