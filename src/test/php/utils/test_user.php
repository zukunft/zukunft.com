<?php

/*

  test_user.php - TESTing of the USER display functions
  ---------------
  

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

// -----------------------------------------------
// start testing the user permission functionality
// -----------------------------------------------

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_CONST . 'rest_ctrl.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\utils\all_tests;

function run_user_test(all_tests $t): void
{


    $back = 0;
    $msg = new user_message();

    // test the user display after the word changes to have a sample case
    // start the test section (ts)
    $ts = 'db write user ';
    $t->header($ts);

    $usr_ui = new user_ui($t->usr1->api_json());
    $result = $usr_ui->form_edit($back);
    $target = users::SYSTEM_TEST_NAME;
    $t->dsp_contains(', user_display->dsp_edit', $target, $result);

    // display system usernames (each line timestamped via the log writer)
    echo_timestamped('based on<br>');
    if (isset($_SERVER)) {
        if (in_array(rest_ctrl::PHP_AUTH_USER, $_SERVER)) {
            echo_timestamped('php user: ' . $_SERVER[rest_ctrl::PHP_AUTH_USER] . '<br>');
            echo_timestamped('remote user: ' . $_SERVER[rest_ctrl::REMOTE_USER] . '<br>');
        }
    }
    echo_timestamped('user id: ' . $t->usr1->id . '<br>');

    $t->subheader($ts . 'permission');

    $ip_addr = '2.204.210.217';
    $result = $t->usr1->ip_check($ip_addr, $msg);
    $target = '';
    $t->assert(', usr->ip_check', $result, $target);

    // TODO add a test signup process to

}