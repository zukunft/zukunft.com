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

// to create the base word links create_base_phrases is used

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

function run_word_link_test()
{

    global $usr;
    global $usr2;

    test_header('Test the word link class (classes/word_link.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    // check the triple usage for Zurich (City) and Zurich (Canton)
    $wrd_zh = load_word(TW_ZH);
    $wrd_city = load_word(TW_CITY);
    $wrd_canton = load_word(TW_CANTON);

    // test the City of Zurich
    $lnk_city = test_word_link(TW_ZH, DBL_LINK_TYPE_IS, TW_CITY, false, TP_ZH_CITY);

    // ... now test the Canton Zurich
    $lnk_canton = new word_link;
    $lnk_canton->from_id = $wrd_zh->id;
    $lnk_canton->verb_id = clo(DBL_LINK_TYPE_IS);
    $lnk_canton->to_id = $wrd_canton->id;
    $lnk_canton->usr = $usr;
    $lnk_canton->load();
    $target = TW_ZH . ' (Canton)';
    $result = $lnk_canton->name;
    test_dsp('triple->load for Canton Zurich', $target, $result, TIMEOUT_LIMIT_DB);

    // ... now test the Canton Zurich using the name function
    $target = TW_ZH . ' (Canton)';
    $result = $lnk_canton->name();
    test_dsp('triple->load for Canton Zurich using the function', $target, $result);

    // ... now test the Insurance Zurich
    $lnk_company = new word_link;
    $lnk_company->from_id = $wrd_zh->id;
    $lnk_company->verb_id = clo(DBL_LINK_TYPE_IS);
    $lnk_company->to_id = $wrd_company->id;
    $lnk_company->usr = $usr;
    $lnk_company->load();
    $target = TP_ZH_INS;
    $result = $lnk_company->name;
    test_dsp('triple->load for ' . TP_ZH_INS . '', $target, $result);

    // ... now test the Insurance Zurich using the name function
    $target = TP_ZH_INS;
    $result = $lnk_company->name();
    test_dsp('triple->load for ' . TP_ZH_INS . ' using the function', $target, $result);

    // link the added word to the test word
    $wrd_added = load_word(TW_ADD_RENAMED);
    $wrd = load_word(TEST_WORD);
    $vrb = new verb;
    $vrb->id = clo(DBL_LINK_TYPE_IS);
    $vrb->usr = $usr->id;
    $vrb->load();
    $lnk = new word_link;
    $lnk->usr = $usr;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $result = $lnk->save();
    $target = '11';
    test_dsp('triple->save "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    echo "... and also testing the user log link class (classes/user_log_link.php)<br>";

    // ... check the correct logging
    $log = new user_log_link;
    $log->table = 'word_links';
    $log->new_from_id = $wrd_added->id;
    $log->new_link_id = $vrb->id;
    $log->new_to_id = $wrd->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job linked ' . TW_ADD_RENAMED . ' to ' . TEST_WORD . '';
    test_dsp('triple->save logged for "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '"', $target, $result);

    // ... check if the link is shown correctly

    $lnk = new word_link;
    $lnk->usr = $usr;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $lnk->load();
    $result = $lnk->name;
    $target = '' . TW_ADD_RENAMED . ' (' . TEST_WORD . ')';
    test_dsp('triple->load', $target, $result);
    // ... check if the link is shown correctly also for the second user
    $lnk2 = new word_link;
    $lnk2->usr = $usr2;
    $lnk2->from_id = $wrd_added->id;
    $lnk2->verb_id = $vrb->id;
    $lnk2->to_id = $wrd->id;
    $lnk2->load();
    $result = $lnk2->name;
    $target = '' . TW_ADD_RENAMED . ' (' . TEST_WORD . ')';
    test_dsp('triple->load for user "' . $usr2->name . '"', $target, $result);

    // ... check if the value update has been triggered

    // if second user removes the new link
    $lnk = new word_link;
    $lnk->usr = $usr2;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $lnk->load();
    $result = $lnk->del();
    $target = '111';
    test_dsp('triple->del "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" by user "' . $usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the removal of the link for the second user has been logged
    $log = new user_log_link;
    $log->table = 'word_links';
    $log->old_from_id = $wrd_added->id;
    $log->old_link_id = $vrb->id;
    $log->old_to_id = $wrd->id;
    $log->usr = $usr2;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test unlinked ' . TW_ADD_RENAMED . ' from ' . TEST_WORD . '';
    test_dsp('triple->del logged for "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" and user "' . $usr2->name . '"', $target, $result);


    // ... check if the link is really not used any more for the second user
    $lnk2 = new word_link;
    $lnk2->usr = $usr2;
    $lnk2->from_id = $wrd_added->id;
    $lnk2->verb_id = $vrb->id;
    $lnk2->to_id = $wrd->id;
    $lnk2->load();
    $result = $lnk2->name();
    $target = '';
    test_dsp('triple->load "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" for user "' . $usr2->name . '" not any more', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // ... check if the value update for the second user has been triggered

    // ... check all places where the word maybe used ...

    // ... check if the link is still used for the first user
    $lnk = new word_link;
    $lnk->usr = $usr;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $lnk->load();
    $result = $lnk->name;
    $target = '' . TW_ADD_RENAMED . ' (' . TEST_WORD . ')';
    test_dsp('triple->load of "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" is still used for user "' . $usr->name . '"', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // ... check if the values for the first user are still the same

    // if the first user also removes the link, both records should be deleted
    $lnk = new word_link;
    $lnk->usr = $usr;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $lnk->load();
    $result = $lnk->del();
    $target = '11';
    test_dsp('triple->del "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check the correct logging
    $log = new user_log_link;
    $log->table = 'word_links';
    $log->old_from_id = $wrd_added->id;
    $log->old_link_id = $vrb->id;
    $log->old_to_id = $wrd->id;
    $log->usr = $usr;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job unlinked ' . TW_ADD_RENAMED . ' from ' . TEST_WORD . '';
    test_dsp('triple->del logged for "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" and user "' . $usr->name . '"', $target, $result);

    // check if the formula is not used any more for both users
    $lnk = new word_link;
    $lnk->usr = $usr;
    $lnk->from_id = $wrd_added->id;
    $lnk->verb_id = $vrb->id;
    $lnk->to_id = $wrd->id;
    $lnk->load();
    $result = $lnk->name;
    $target = '';
    test_dsp('triple->load of "' . $wrd_added->name . '" ' . $vrb->name . ' "' . $wrd->name . '" for user "' . $usr->name . '" not used any more', $target, $result);


    // ... and the values have been updated
    /*
    // insert the link again for the first user
    $frm = load_formula(TF_ADD_RENAMED);
    $phr = New phrase;
    $phr->name = TW_ADD_RENAMED;
    $phr->usr = $usr2;
    $phr->load();
    $result = $frm->link_phr($phr);
    $target = '1';
    test_dsp('triple->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */
    // ... if the second user changes the link

    // ... and the first user removes the link

    // ... the link should still be active for the second user

    // ... but not for the first user

    // ... and the owner should now be the second user

    // the code changes and tests for formula link should be moved the view_component_link

}