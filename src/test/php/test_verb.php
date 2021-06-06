<?php 

/*

  test_verb.php - TESTing of the VERB class
  -------------
  

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

function run_verb_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the verb class (classes/verb.php)');

  // check the loading of the "is a" verb
  $vrb = New verb;
  $vrb->id= cl(SQL_LINK_TYPE_IS);
  $vrb->usr_id = $usr->id;
  $vrb->load($debug-1);
  $target = 'is a';
  $result = $vrb->name;
  $exe_start_time = test_show_result(', verb->load ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  test_header('Test the verb list class (classes/verb_list.php)');

  // check the loading of the "is a" verb
  $wrd_ABB = load_word(TW_ABB, $debug-1);
  $vrb_lst = $wrd_ABB->link_types ('up', $debug-1);
  $target = 'is a';
  $result = '';
  // select the first verb
  foreach ($vrb_lst->lst AS $vrb) {
    if ($result == '') {
      $result = $vrb->name;
    }
  }
  $exe_start_time = test_show_result(', verb_list->load ', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}