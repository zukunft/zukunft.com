<?php

/*

    test/php/unit_write_workflow/value_write_url_tests.php - the add value workflows incl. db write
    -----------------------------------------------------

    runs the same add value workflows as value_url_tests, but with do_it true so that the confirmed
    add really writes the value; the snapshots go into the parallel workflow_write folder
    (see docs/llm/testing.md)

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

namespace Zukunft\ZukunftCom\test\php\unit_write_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user_message.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::UNIT_WORKFLOW . 'value_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\unit_workflow\value_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class value_write_url_tests extends value_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'value url write->', 'url write value ');

        // the add value views select the phrases of the new value by id, so the reserved test words
        // must be in the database; the word and triple write workflows add and delete them again,
        // so this run creates them itself instead of depending on the order of the other workflows
        $this->create_test_phrases($t);

        // run the same workflows as value_url_tests but with do_it true so each add is persisted
        $this->add_value_workflow(workflows::WF_ADD_VALUE_NBR, true);
        $this->add_value_with_phrase_workflow(workflows::WF_ADD_VALUE_WITH_PHRASE_NBR, true);
        $this->add_value_remove_phrase_workflow(workflows::WF_ADD_VALUE_REMOVE_PHRASE_NBR, true);
        $this->add_value_details_workflow(workflows::WF_ADD_VALUE_DETAILS_NBR, true);
        // names the group of the value that the add_value workflow has written above
        $this->change_value_group_workflow(workflows::WF_CHANGE_VALUE_GROUP_NBR, true);

    }

    /**
     * make sure that the reserved test words used as the phrases of the new values exist
     *
     * @param test_cleanup $t the test environment
     */
    private function create_test_phrases(test_cleanup $t): void
    {
        $msg = new user_message($t->usr1); // a buffer for the test setup, asserted below
        $t_db = new test_db_load($t);
        $t_db->test_word($msg, word_names::TEST_ADD);
        $t_db->test_word($msg, word_names::TEST_ADD_TO);
        $t->assert_msg('the phrases of the new value exist', $msg);
    }

}
