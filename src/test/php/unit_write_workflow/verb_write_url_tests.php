<?php

/*

    test/php/unit_write_workflow/verb_write_url_tests.php - the verb url workflows with a db write
    -----------------------------------------------------

    runs the change_verb workflow of verb_url_tests with do_it true on a test verb that this test
    adds before and removes after the workflow, never on a seed verb, and checks that the confirmed
    change makes the verb the last used verb of the user until the verb is deleted

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
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_verbs.php';
include_once test_paths::UNIT_WORKFLOW . 'verb_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\unit_workflow\verb_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_write_url_tests extends verb_url_tests
{

    function run(test_cleanup $t): void
    {
        $t_vrb = new test_verbs($t);
        $msg = new user_message($t->usr1);

        // load the shared frontend run state and print the section header
        $this->init($t, 'verb url write->', 'url write verb ');

        // remove any test verb left over from a previous run with the logged delete
        $t_vrb->cleanup($this->ts);

        // the change workflow runs on a test verb with a stored reverse, never on a seed verb
        $test_name = 'the test verb of the change_verb workflow is added';
        $vrb = $t_vrb->with_reverse();
        $t->assert_true($test_name, $vrb->save($msg));

        // run the same workflow as verb_url_tests but with do_it true
        // so the confirmed step is persisted and the database is checked
        $this->change_verb_workflow(workflows::WF_CHANGE_VERB_NBR, true);

        // the confirmed change makes the verb the default of the next triple (see user::save_last_used)
        $test_name = 'the changed verb is the last used verb of the user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert($test_name, $usr_db->verb_id(), $vrb->id());
        $test_name = '... but not of another user';
        $usr2_db = $t->user_from_db($t->usr2);
        $t->assert_null($test_name, $usr2_db->verb_id());

        // remove the test verb and its change log with the logged delete
        $t_vrb->cleanup($this->ts);
        $test_name = 'the deleted verb is no longer the last used verb of the user';
        $usr_db = $t->user_from_db($t->usr1);
        $t->assert_null($test_name, $usr_db->verb_id());
        $t->cleanup_last_used();
    }

}
