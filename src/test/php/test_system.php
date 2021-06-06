<?php 

/*

  test_system.php - TESTing of the basic system functions
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

function run_system_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  echo "<h1>Consistency check of the \"zukunft.com\" code</h1><br>";

  if (TEST_EMAIL == TRUE) {
    test_header('est mail sending');
    $mail_to      = 'timon@zukunft.com';
    $mail_subject = 'Test mailto';
    $mail_body    = 'Hello';
    $mail_header  = 'From: heang@zukunft.com' . "\r\n" .
                    'Reply-To: heang@zukunft.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

    mail($mail_to, $mail_subject, $mail_body, $mail_header);
  }


  // check if the owner is always setting
  //$sbx = New user_sandbox;
  //$chk_txt = $sbx->chk_owner('word_link', False, $debug-1); if ($chk_txt <> '') { echo $chk_txt."<br>"; }

  test_header('Test the blocked IP addresses');

  // check the first predefined word "Company"
  // load by id
  $usr_test = New user;
  $usr_test->ip_addr = TEST_USER_IP;
  $target = 'Your IP '.$usr_test->ip_addr.' is blocked at the moment because too much damage from this IP. If you think, this should not be the case, please request the unblocking with an email to admin@zukunft.com.';
  $result = $usr_test->get($debug-1);
  if ($usr_test->id > 0) {
    $result = 'permitted!';
  }
  $exe_start_time = test_show_result(', IP blocking for '.$usr_test->ip_addr, $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  test_header('Test the user class (classes/user.php)');

  $target = '<a href="/http/user.php?id='.TEST_USER_ID.'">zukunft.com system batch job</a>';
  $result = $usr->display();
  $exe_start_time = test_show_result(', user->load for id '.TEST_WORD_ID, $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  test_header('Test the user list class (classes/user_list.php)');

  $usr_lst = New user_list;
  $usr_lst->load_active($debug-1);
  $result = $usr_lst->name();
  $target = TEST_USER_DESCRIPTION;
  $exe_start_time = test_show_contains(', user_list->load_active', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}