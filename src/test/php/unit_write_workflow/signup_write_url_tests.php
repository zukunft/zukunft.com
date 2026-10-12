<?php

/*

    test/php/unit_write_workflow/signup_write_url_tests.php - check the signup and email confirmation incl. db write
    -------------------------------------------------------

    runs the same signup_confirm workflow as signup_url_tests but with do_it true, so the new user is
    written with the reserved name profile and the activation link confirms its email in the database;
    the session of the test run and the mail are never touched (see frontend::live_request) and the
    snapshots go into the parallel workflow_write folder (see docs/llm/testing.md)

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

include_once paths::SHARED_CONST . 'users.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::UNIT_WORKFLOW . 'signup_url_tests.php';

use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\unit_workflow\signup_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class signup_write_url_tests extends signup_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'signup url write->', 'url write signup ');

        // remove the test user left over from a previous run, because the signup refuses an existing name
        $t->cleanup_test_user(users::TEST_SIGNUP_NAME);

        // run the same workflow as signup_url_tests but with do_it true
        // so the signup and the confirmation are persisted and checked in the database
        $this->signup_confirm_workflow(workflows::WF_SIGNUP_CONFIRM_NBR, true);

        $t->subheader($this->ts . 'cleanup');
        $t->cleanup_test_user(users::TEST_SIGNUP_NAME);

    }

}
