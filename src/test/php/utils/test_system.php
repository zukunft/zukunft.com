<?php

/*

  test_system.php - TESTing of the basic system functions like ip blocking
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

function run_system_test(testing $t)
{

    global $usr;

    $t->header('Consistency check of the \"zukunft.com\" code');

    // load the main test word
    $wrd_company = $t->test_word(TEST_WORD);

    if (TEST_EMAIL == TRUE) {
        $t->header('est mail sending');
        $mail_to = 'timon@zukunft.com';
        $mail_subject = 'Test mailto';
        $mail_body = 'Hello';
        $mail_header = 'From: heang@zukunft.com' . "\r\n" .
            'Reply-To: heang@zukunft.com' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($mail_to, $mail_subject, $mail_body, $mail_header);
    }


    // check if the owner is always setting
    //$sbx = New user_sandbox;
    //$chk_txt = $sbx->chk_owner(sql_db::TBL_TRIPLE, False); if ($chk_txt <> '') { echo $chk_txt."<br>"; }

    $t->header('Test the blocked IP addresses');

    // check the first predefined word "Company"
    // load by id
    $usr_test = new user;
    $usr_test->ip_addr = TEST_USER_IP;
    $target = 'Your IP ' . $usr_test->ip_addr . ' is blocked at the moment because too much damage from this IP. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
    $result = $usr_test->get();
    if ($usr_test->id > 0) {
        $result = 'permitted!';
    }
    $t->dsp('IP blocking for ' . $usr_test->ip_addr, $target, $result);


    $t->header('Test the user class (classes/user.php)');

    // load by name
    $usr_test->reset();
    $usr_test = new user;
    $usr_test->name = user::NAME_SYSTEM_TEST;

    $usr_test->load_test_user();
    $target = '<a href="/http/user.php?id=' . $usr_test->id . '">zukunft.com system test</a>';
    $result = $usr->display();
    $t->dsp('user->load for id ' . $wrd_company->id(), $target, $result);


    $t->header('Test the user list class (classes/user_list.php)');

    $usr_lst = new user_list;
    $usr_lst->load_active();
    $result = $usr_lst->name_lst();
    $target = TEST_USER_DESCRIPTION;
    $t->dsp_contains(', user_list->load_active', $target, $result);

}