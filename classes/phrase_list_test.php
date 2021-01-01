<?php 

/*

  phrase_list_test.php - PHRASE LIST function  unit TESTs
  --------------------
  

zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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

function run_phrase_list_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the phrase list class (classes/phrase_list.php)</h2><br>";

  // test the phrase loading via id
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->add_name(TW_VESTAS);
  $wrd_lst->load($debug-1);
  $id_lst = $wrd_lst->ids;
  $id_lst[] = $triple_sample_id * -1;
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->ids = $id_lst;
  $phr_lst->load($debug-1);
  $target = '"'.TW_ABB.'","'.TW_VESTAS.'","'.TP_ZH_INS.'"';
  $result = $phr_lst->name($debug-1);
  $exe_start_time = test_show_result(', phrase->load via id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... the complete word list, which means split the triples into single words
  $wrd_lst_all = $phr_lst->wrd_lst_all($debug-1);
  $target = '"'.TW_ABB.'","'.TW_VESTAS.'","'.TW_ZH.'","'.TEST_WORD.'"';
  $result = $wrd_lst_all->name($debug-1);
  $exe_start_time = test_show_result(', phrase->wrd_lst_all of list above', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // test getting the parent for phrase list with ABB
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_ABB);
  $wrd_lst->load($debug-1);
  $phr_lst = $wrd_lst->phrase_lst($debug-1);
  $lst_parents = $phr_lst->foaf_parents(cl(SQL_LINK_TYPE_IS), $debug-1);
  $result = implode(',',$lst_parents->names($debug-1));
  $target = TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result(', phrase_list->foaf_parents for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... same using is
  $phr_lst = $wrd_lst->phrase_lst($debug-1);
  $lst_is = $phr_lst->is($debug-1);
  $result = implode(',',$lst_is->names($debug-1));
  $target = TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result(', phrase_list->is for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... same with Coca Cola
  $wrd_lst = New word_list;
  $wrd_lst->usr = $usr;
  $wrd_lst->add_name(TW_VESTAS);
  $wrd_lst->load($debug-1);
  $phr_lst = $wrd_lst->phrase_lst($debug-1);
  $lst_is = $phr_lst->is($debug-1);
  $result = implode(',',$lst_is->names($debug-1));
  $target = TEST_WORD; // order adjusted based on the number of usage
  $exe_start_time = test_show_result(', phrase_list->is for '.$phr_lst->name().' up', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // test the excluding function
  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TW_ABB);
  $phr_lst->add_name(TW_SALES);
  $phr_lst->add_name(TW_CHF);
  $phr_lst->add_name(TW_MIO);
  $phr_lst->add_name(TW_2017);
  $phr_lst->load($debug-1);
  $phr_lst_ex = clone $phr_lst;
  $phr_lst_ex->ex_time($debug-1);
  $target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_CHF.'","'.TW_MIO.'"';
  $result = $phr_lst_ex->name($debug-1);
  $exe_start_time = test_show_result(', phrase_list->ex_time of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $phr_lst_ex = clone $phr_lst;
  $phr_lst_ex->ex_measure($debug-1);
  $target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_MIO.'","'.TW_2017.'"';
  $result = $phr_lst_ex->name($debug-1);
  $exe_start_time = test_show_result(', phrase_list->ex_measure of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  $phr_lst_ex = clone $phr_lst;
  $phr_lst_ex->ex_scaling($debug-1);
  $target = '"'.TW_ABB.'","'.TW_SALES.'","'.TW_CHF.'","'.TW_2017.'"';
  $result = $phr_lst_ex->name($debug-1);
  $exe_start_time = test_show_result(', phrase_list->ex_scaling of '.$phr_lst->name($debug-1), $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}

?>
