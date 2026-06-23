<?php

/*

    test/php/unit_workflow/all_workflow_tests.php - test the user workflows based on the url without change the database
    ---------------------------------------------
    
    the zukunft.com workflows simulates all suggested user workflows and checks if the next view is the expected view


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once test_paths::UTILS . 'test_base.php';
include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once test_paths::UNIT_WORKFLOW . 'triple_url_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\utils\test_base;

class all_workflow_tests
{

    /**
     * check if the url requests by the given user still produces the expected html pages
     *
     * @param test_base $t the test environment including the error counter and execution times
     * @param user $usr the user for whom the workflow should be tested
     * @return bool true if all tests are fine
     */
    static function run(test_base $t, user $usr, user_message $usr_msg): bool
    {

        // start the test section (ts)
        $ts = 'workflow start ';
        $t->header($ts);

        if ($usr->id > 0) {

            // url tests
            new word_url_tests()->run($t);
            new triple_url_tests()->run($t);

            /*
             * TODO Prio 1 easy workflow
             * the easy workflow (without extra confirm of the change) should be change be to
             * to add a number
             * to add a triple, language form or translation
             * and if set in the config:
             * to change a number
             */

        }
        return $usr_msg->is_ok();
    }

}