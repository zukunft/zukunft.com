<?php

/*

  test_triple.php - TESTing of the WORD LINK functions
  ------------------
  

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

// to create the base word links create_base_phrases is used

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

use api\phrase_api;
use api\triple_api;
use api\word_api;
use model\change_log_link;
use model\change_log_table;
use model\triple;
use model\verb;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB;
use const test\TIMEOUT_LIMIT_DB_MULTI;
use const test\TIMEOUT_LIMIT_PAGE_SEMI;

function run_triple_test(test_cleanup $t): void
{

    global $verbs;

    $t->header('Test the word link class (classes/triple.php)');

    // load the main test word and verb
    $is_id = $verbs->id(verb::IS_A);

    // create the group test word
    $wrd_company = $t->test_word(word_api::TN_COMPANY);

    // check if basic triples (Zurich (City) and Zurich (Canton)
    $wrd_zh = $t->load_word(word_api::TN_ZH);
    $wrd_city = $t->load_word(word_api::TN_CITY);
    $wrd_canton = $t->load_word(word_api::TN_CANTON);

    // ... now test the Canton Zurich
    $lnk_canton = new triple($t->usr1);
    $lnk_canton->load_by_link($wrd_zh->id(), $is_id, $wrd_canton->id());
    $target = word_api::TN_ZH . ' (' . word_api::TN_CANTON . ')';
    $result = $lnk_canton->name();
    $t->display('triple->load for Canton Zurich', $target, $result, TIMEOUT_LIMIT_DB);

    // ... now test the Canton Zurich using the name function
    $target = word_api::TN_ZH . ' (' . word_api::TN_CANTON . ')';
    $result = $lnk_canton->name();
    $t->display('triple->load for Canton Zurich using the function', $target, $result);

    // ... now test the Insurance Zurich
    $lnk_company = new triple($t->usr1);
    $lnk_company->load_by_link($wrd_zh->id(), $is_id, $wrd_company->id());
    $target = triple_api::TN_ZH_COMPANY;
    $result = $lnk_company->name();
    $t->display('triple->load for ' . triple_api::TN_ZH_COMPANY, $target, $result);

    // ... now test the Insurance Zurich using the name function
    $target = triple_api::TN_ZH_COMPANY;
    $result = $lnk_company->name();
    $t->display('triple->load for ' . triple_api::TN_ZH_COMPANY . ' using the function', $target, $result);

    // add a triple based on the id of the added test word, verb and the parent test word
    $wrd_from = $t->test_word(word_api::TN_RENAMED);
    $wrd = $t->test_word(word_api::TN_PARENT);
    $trp = new triple($t->usr1);
    $trp->from->set_id($wrd_from->id());
    $trp->verb->set_id($is_id);
    $trp->to->set_id($wrd->id());
    if ($wrd_from->id() <> 0 and $is_id and $wrd->id() <> 0) {
        $result = $trp->save();
    } else {
        $result = 'id missing';
    }
    $target = '';
    $t->display('triple->save "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    $t->subheader("... and also testing the user log link class (classes/user_log_link.php)");

    // ... check the correct logging
    $log = new change_log_link($t->usr1);
    $log->set_table(change_log_table::TRIPLE);
    $log->new_from_id = $wrd_from->id();
    $log->new_link_id = $is_id;
    $log->new_to_id = $wrd->id();
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test linked ' . word_api::TN_RENAMED . ' to ' . word_api::TN_PARENT;
    $t->display('triple->save logged for "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '"', $target, $result);

    // ... check if the link is shown correctly

    $trp = new triple($t->usr1);
    $trp->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $result = $trp->name();
    $target = word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
    $t->display('triple->load', $target, $result);
    // ... check if the link is shown correctly also for the second user
    $lnk2 = new triple($t->usr2);
    $lnk2->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $result = $lnk2->name();
    $target = word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
    $t->display('triple->load for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value update has been triggered

    // if second user removes the new link
    $trp = new triple($t->usr2);
    $trp->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $msg = $trp->del();
    $result = $msg->get_last_message();
    $target = '';
    $t->display('triple->del "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the removal of the link for the second user has been logged
    $log = new change_log_link($t->usr2);
    $log->set_table(change_log_table::TRIPLE);
    $log->old_from_id = $wrd_from->id();
    $log->old_link_id = $is_id ;
    $log->old_to_id = $wrd->id();
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test partner unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT . '';
    $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" and user "' . $t->usr2->name . '"', $target, $result);


    // ... check if the link is really not used any more for the second user
    $lnk2 = new triple($t->usr2);
    $lnk2->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $result = $lnk2->name();
    $target = '';
    $t->display('triple->load "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" for user "' . $t->usr2->name . '" not any more', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // ... check if the value update for the second user has been triggered

    // ... check all places where the word maybe used ...

    // ... check if the link is still used for the first user
    $trp = new triple($t->usr1);
    $trp->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $result = $trp->name();
    $target = '' . word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
    $t->display('triple->load of "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" is still used for user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // ... check if the values for the first user are still the same

    // if the first user also removes the link, both records should be deleted
    $trp = new triple($t->usr1);
    $trp->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $msg = $trp->del();
    $result = $msg->get_last_message();
    $target = '';
    $t->display('triple->del "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check the correct logging
    $log = new change_log_link($t->usr1);
    $log->set_table(change_log_table::TRIPLE);
    $log->old_from_id = $wrd_from->id();
    $log->old_link_id = $is_id ;
    $log->old_to_id = $wrd->id();
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT;
    $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" and user "' . $t->usr1->name . '"', $target, $result);

    // check if the formula is not used any more for both users
    $trp = new triple($t->usr1);
    $trp->load_by_link($wrd_from->id(), $is_id, $wrd->id());
    $result = $trp->name();
    $target = '';
    $t->display('triple->load of "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" for user "' . $t->usr1->name . '" not used any more', $target, $result);

    // check if the name of a triple can be changed
    $trp = $t->test_triple(word_api::TN_RENAMED, verb::IS_A, word_api::TN_PARENT);
    $trp->set_name(triple_api::TN_ADD);
    $result = $trp->save();
    $t->assert('triple->save name to ' . triple_api::TN_ADD, $result);

    // ... and if the name check if the name of a triple can be changed
    $trp = new triple($t->usr1);
    $trp->load_by_name(triple_api::TN_ADD);
    $t->assert('triple load changed name of ' . triple_api::TN_ADD, $trp->name(), triple_api::TN_ADD);

    // check the correct logging
    $log = new change_log_link($t->usr1);
    $log->set_table(change_log_table::TRIPLE);
    $log->old_from_id = $wrd_from->id();
    $log->old_link_id = $is_id ;
    $log->old_to_id = $wrd->id();
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT;
    $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS_A . ' "' . $wrd->name() . '" and user "' . $t->usr1->name . '"', $target, $result);


    // ... and the values have been updated
    /*
    // insert the link again for the first user
    $frm =$t->load_formula(TF_ADD_RENAMED);
    $phr = New phrase($t->usr2);
    $phr->load_by_name(word::TEST_NAME_CHANGED);
    $result = $frm->link_phr($phr);
    $target = '1';
    $t->display('triple->link_phr "'.$phr->name().'" to "'.$frm->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */
    // ... if the second user changes the link

    // ... and the first user removes the link

    // ... the link should still be active for the second user

    // ... but not for the first user

    // ... and the owner should now be the second user

    // the code changes and tests for formula link should be moved the component_link

}