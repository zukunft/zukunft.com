<?php

/*

  test_system.php - TESTing of the basic system functions like ip blocking
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

function run_system_test()
{

    global $usr;

    echo "<h1>Consistency check of the \"zukunft.com\" code</h1><br>";

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    if (TEST_EMAIL == TRUE) {
        test_header('est mail sending');
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
    //$chk_txt = $sbx->chk_owner(DB_TYPE_WORD_LINK, False); if ($chk_txt <> '') { echo $chk_txt."<br>"; }

    test_header('Test the blocked IP addresses');

    // check the first predefined word "Company"
    // load by id
    $usr_test = new user;
    $usr_test->ip_addr = TEST_USER_IP;
    $target = 'Your IP ' . $usr_test->ip_addr . ' is blocked at the moment because too much damage from this IP. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
    $result = $usr_test->get();
    if ($usr_test->id > 0) {
        $result = 'permitted!';
    }
    test_dsp('IP blocking for ' . $usr_test->ip_addr, $target, $result);


    test_header('Test the user class (classes/user.php)');

    $target = '<a href="/http/user.php?id=' . TEST_USER_ID . '">zukunft.com system batch job</a>';
    $result = $usr->display();
    test_dsp('user->load for id ' . $wrd_company->id, $target, $result);


    test_header('Test the user list class (classes/user_list.php)');

    $usr_lst = new user_list;
    $usr_lst->load_active();
    $result = $usr_lst->name();
    $target = TEST_USER_DESCRIPTION;
    test_dsp_contains(', user_list->load_active', $target, $result);

}