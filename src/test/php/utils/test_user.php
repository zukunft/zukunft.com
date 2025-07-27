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

use cfg\user\user;
use html\user\user as user_dsp;
use shared\const\users;
use test\all_tests;

function run_user_test(all_tests $t): void
{

    global $usr;

    $back = 0;

    // test the user display after the word changes to have a sample case
    $t->header('Test the user display class (classes/user_display.php)');

    $usr_dsp = new user_dsp($usr->api_json());
    $result = $usr_dsp->form_edit($back);
    $target = users::SYSTEM_TEST_NAME;
    $t->dsp_contains(', user_display->dsp_edit', $target, $result);

    // display system usernames
    echo "based on<br>";
    if (isset($_SERVER)) {
        if (in_array('PHP_AUTH_USER', $_SERVER)) {
            echo 'php user: ' . $_SERVER['PHP_AUTH_USER'] . '<br>';
            echo 'remote user: ' . $_SERVER['REMOTE_USER'] . '<br>';
        }
    }
    echo 'user id: ' . $usr->id() . '<br>';

    $t->header('user permission tests');

    $ip_addr = '2.204.210.217';
    $result = $usr->ip_check($ip_addr);
    $target = '';
    $t->display(', usr->ip_check', $target, $result);

    // TODO add a test signup process to

}