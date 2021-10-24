<?php

/*

  test_user.php - TESTing of the USER display functions
  ---------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// -----------------------------------------------
// start testing the user permission functionality
// -----------------------------------------------

function run_user_test()
{

    global $usr;

    $back = 0;

    // test the user display after the word changes to have a sample case
    test_header('Test the user display class (classes/user_display.php)');

    $result = $usr->dsp_obj()->dsp_edit($back);
    $target = TEST_USER_NAME;
    test_dsp_contains(', user_display->dsp_edit', $target, $result);

    // display system usernames
    echo "based on<br>";
    if (isset($_SERVER)) {
        if (in_array('PHP_AUTH_USER', $_SERVER)) {
            echo 'php user: ' . $_SERVER['PHP_AUTH_USER'] . '<br>';
            echo 'remote user: ' . $_SERVER['REMOTE_USER'] . '<br>';
        }
    }
    echo 'user id: ' . $usr->id . '<br>';

    test_header('Test the user permission scripts (e.g. /user/user.php)');

    $ip_addr = '2.204.210.217';
    $result = $usr->ip_check($ip_addr);
    $target = '';
    test_dsp(', usr->ip_check', $target, $result);

    // TODO add a test signup process to

}