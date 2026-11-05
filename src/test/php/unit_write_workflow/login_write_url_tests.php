<?php

/*

    test/php/unit_write_workflow/login_write_url_tests.php - check the real login from the static start page
    ------------------------------------------------------

    runs the login_start_page workflow of login_url_tests with do_it true, so the system test user
    really logs in and the redirect to the static start page is checked

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
include_once test_paths::UNIT_WORKFLOW . 'login_url_tests.php';

use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\unit_workflow\login_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class login_write_url_tests extends login_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'login url write->', 'url write login ');

        // the login of the workflow uses the fixed test password
        $this->ensure_test_password($t, users::SYSTEM_TEST_NAME);

        // run the same workflow as login_url_tests but with do_it true
        // so the user really logs in and returns to the static start page
        $this->login_start_page_workflow(workflows::WF_LOGIN_START_PAGE_NBR, true);

    }

}
