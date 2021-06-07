<?php 

/*

  test_word_link.php - TESTing of the WORD LINK functions
  ------------------
  

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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------
  
function run_word_link_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  test_header('Test the word link class (classes/word_link.php)');

  // check the triple usage for Zurich (City) and Zurich (Canton)
  $wrd_zh     = load_word(TW_ZH, $debug-1);
  $wrd_city   = load_word(TW_CITY, $debug-1);
  $wrd_canton = load_word(TW_CANTON, $debug-1);

  // test the City of Zurich
  $lnk_city = test_word_link(TW_ZH, DBL_LINK_TYPE_IS, TW_CITY, false, TP_ZH_CITY);

  // ... now test the Canton Zurich
  $lnk_canton = New word_link;
  $lnk_canton->from_id = $wrd_zh->id;
  $lnk_canton->verb_id = cl(DBL_LINK_TYPE_IS);
  $lnk_canton->to_id   = $wrd_canton->id;
  $lnk_canton->usr  = $usr;
  $lnk_canton->load($debug-1);
  $target = TW_ZH.' (Canton)';
  $result = $lnk_canton->name;
  $exe_start_time = test_show_result(', triple->load for Canton Zurich', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB);

  // ... now test the Canton Zurich using the name function
  $target = TW_ZH.' (Canton)';
  $result = $lnk_canton->name();
  $exe_start_time = test_show_result(', triple->load for Canton Zurich using the function', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... now test the Insurance Zurich
  $lnk_company = New word_link;
  $lnk_company->from_id = $wrd_zh->id;
  $lnk_company->verb_id = cl(DBL_LINK_TYPE_IS);
  $lnk_company->to_id   = TEST_WORD_ID;
  $lnk_company->usr  = $usr;
  $lnk_company->load($debug-1);
  $target = TP_ZH_INS;
  $result = $lnk_company->name;
  $exe_start_time = test_show_result(', triple->load for '.TP_ZH_INS.'', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... now test the Insurance Zurich using the name function
  $target = TP_ZH_INS;
  $result = $lnk_company->name();
  $exe_start_time = test_show_result(', triple->load for '.TP_ZH_INS.' using the function', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // link the added word to the test word
  $wrd_added = load_word(TW_ADD_RENAMED, $debug-1);
  $wrd = load_word(TEST_WORD, $debug-1);
  $vrb = New verb;
  $vrb->id= cl(DBL_LINK_TYPE_IS);
  $vrb->usr_id = $usr->id;
  $vrb->load($debug-1);
  $lnk = New word_link;
  $lnk->usr     = $usr;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $result = $lnk->save($debug-1);
  $target = '11';
  $exe_start_time = test_show_result(', triple->save "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  echo "... and also testing the user log link class (classes/user_log_link.php)<br>";

  // ... check the correct logging
  $log = New user_log_link;
  $log->table = 'word_links';
  $log->new_from_id = $wrd_added->id;
  $log->new_link_id = $vrb->id;
  $log->new_to_id = $wrd->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job linked '.TW_ADD_RENAMED.' to '.TEST_WORD.'';
  $exe_start_time = test_show_result(', triple->save logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the link is shown correctly

  $lnk = New word_link;
  $lnk->usr     = $usr;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $lnk->load($debug-1);
  $result = $lnk->name;
  $target = ''.TW_ADD_RENAMED.' ('.TEST_WORD.')'; 
  $exe_start_time = test_show_result(', triple->load', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  // ... check if the link is shown correctly also for the second user
  $lnk2 = New word_link;
  $lnk2->usr     = $usr2;
  $lnk2->from_id = $wrd_added->id;
  $lnk2->verb_id = $vrb->id;
  $lnk2->to_id   = $wrd->id;
  $lnk2->load($debug-1);
  $result = $lnk2->name;
  $target = ''.TW_ADD_RENAMED.' ('.TEST_WORD.')'; 
  $exe_start_time = test_show_result(', triple->load for user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // ... check if the value update has been triggered

  // if second user removes the new link
  $lnk = New word_link;
  $lnk->usr     = $usr2;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $lnk->load($debug-1);
  $result = $lnk->del($debug-1);
  $target = '111';
  $exe_start_time = test_show_result(', triple->del "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" by user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // ... check if the removal of the link for the second user has been logged
  $log = New user_log_link;
  $log->table = 'word_links';
  $log->old_from_id = $wrd_added->id;
  $log->old_link_id = $vrb->id;
  $log->old_to_id = $wrd->id;
  $log->usr = $usr2;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system test unlinked '.TW_ADD_RENAMED.' from '.TEST_WORD.'';
  $exe_start_time = test_show_result(', triple->del logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" and user "'.$usr2->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... check if the link is really not used any more for the second user
  $lnk2 = New word_link;
  $lnk2->usr     = $usr2;
  $lnk2->from_id = $wrd_added->id;
  $lnk2->verb_id = $vrb->id;
  $lnk2->to_id   = $wrd->id;
  $lnk2->load($debug-1);
  $result = $lnk2->name();
  $target = ''; 
  $exe_start_time = test_show_result(', triple->load "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" for user "'.$usr2->name.'" not any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // ... check if the value update for the second user has been triggered

  // ... check all places where the word maybe used ...

  // ... check if the link is still used for the first user
  $lnk = New word_link;
  $lnk->usr     = $usr;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $lnk->load($debug-1);
  $result = $lnk->name;
  $target = ''.TW_ADD_RENAMED.' ('.TEST_WORD.')'; 
  $exe_start_time = test_show_result(', triple->load of "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" is still used for user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_SEMI);

  // ... check if the values for the first user are still the same

  // if the first user also removes the link, both records should be deleted
  $lnk = New word_link;
  $lnk->usr     = $usr;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $lnk->load($debug-1);
  $result = $lnk->del($debug-1);
  $target = '11';
  $exe_start_time = test_show_result(', triple->del "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI);

  // check the correct logging
  $log = New user_log_link;
  $log->table = 'word_links';
  $log->old_from_id = $wrd_added->id;
  $log->old_link_id = $vrb->id;
  $log->old_to_id = $wrd->id;
  $log->usr = $usr;
  $result = $log->dsp_last(true, $debug-1);
  $target = 'zukunft.com system batch job unlinked '.TW_ADD_RENAMED.' from '.TEST_WORD.'';
  $exe_start_time = test_show_result(', triple->del logged for "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" and user "'.$usr->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // check if the formula is not used any more for both users
  $lnk = New word_link;
  $lnk->usr     = $usr;
  $lnk->from_id = $wrd_added->id;
  $lnk->verb_id = $vrb->id;
  $lnk->to_id   = $wrd->id;
  $lnk->load($debug-1);
  $result = $lnk->name;
  $target = ''; 
  $exe_start_time = test_show_result(', triple->load of "'.$wrd_added->name.'" '.$vrb->name.' "'.$wrd->name.'" for user "'.$usr->name.'" not used any more', $target, $result, $exe_start_time, TIMEOUT_LIMIT);


  // ... and the values have been updated
  /*
  // insert the link again for the first user
  $frm = load_formula(TF_ADD_RENAMED, $debug-1);
  $phr = New phrase;
  $phr->name = TW_ADD_RENAMED;
  $phr->usr = $usr2;
  $phr->load($debug-1);
  $result = $frm->link_phr($phr, $debug-1);
  $target = '1';
  $exe_start_time = test_show_result(', triple->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_DB_MULTI); 
  */
  // ... if the second user changes the link

  // ... and the first user removes the link

  // ... the link should still be active for the second user

  // ... but not for the first user

  // ... and the owner should now be the second user

  // the code changes and tests for formula link should be moved the view_component_link

}