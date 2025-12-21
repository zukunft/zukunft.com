<?php

/*

    test/php/unit_api/all_api_tests.php - test all api calls and the results
    -----------------------------------
    
    call all zukunft.com api endpoints at least once and check the results


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

namespace Zukunft\ZukunftCom\test\php\unit_api;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once test_paths::UTILS . 'test_api.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\types\type_lists as type_list_ui;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class all_api_tests
{

    /**
     * check if the url requests by the given user still produces the expected html pages
     *
     * @param test_cleanup $t the test environment including the error counter and execution times
     * @param user $usr the user for whom the workflow should be tested
     * @return bool true if all tests are fine
     */
    function run_api_tests(test_cleanup $t, user $usr, user_message $usr_msg): bool
    {

        // start the test section (ts)
        $ts = 'api start ';
        $t->header($ts);

        if ($usr->id > 0) {

            // url tests
            new api_tests()->run($t);

            // load the types from the api message
            $t_typ = new test_types($t);
            $api_msg = $t_typ->type_lists_api($t->usr1);
            new type_list_ui($api_msg);

        }
        return $usr_msg->is_ok();
    }

}