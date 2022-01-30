<?php

/*

  test_formula_link.php - TESTing of the FORMULA LINK functions
  ---------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function create_test_formula_links(testing $t)
{
    $t->header('Check if all base formulas link correctly');

    $t->test_formula_link(formula::TN_RATIO, word::TN_SHARE);
    $t->test_formula_link(formula::TN_SECTOR, word::TN_SHARE);
    $t->test_formula_link(formula::TN_INCREASE, word::TN_YEAR);
    $t->test_formula_link(formula::TN_SCALE_K, word::TN_IN_K);
    $t->test_formula_link(formula::TN_SCALE_TO_K, word::TN_ONE);
    $t->test_formula_link(formula::TN_SCALE_MIO, word::TN_MIO);
    $t->test_formula_link(formula::TN_SCALE_BIL, word::TN_BIL);

}

function run_formula_link_test(testing $t)
{
    $t->header('Test the formula link class (classes/formula_link.php)');

    // make sure that the word for testing exists even if the word test didn't run before
    $t->test_word(word::TN_RENAMED);

    // link the test formula to another word
    $frm = $t->load_formula(formula::TN_RENAMED);
    $phr = new phrase($t->usr1);
    $phr->name = word::TN_RENAMED;
    $phr->load();
    $result = $frm->link_phr($phr);
    $target = '';
    $t->dsp('formula_link->link_phr "' . $phr->name . '" to "' . $frm->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check the correct logging
    $log = new user_log_link;
    $log->table = 'formula_links';
    $log->new_from_id = $frm->id;
    $log->new_to_id = $phr->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test linked System Test Formula Renamed to ' . word::TN_RENAMED;
    $t->dsp('formula_link->link_phr logged for "' . $phr->name . '" to "' . $frm->name . '"', $target, $result);

    // ... check if the link can be loaded by formula and phrase id and base on the id the correct formula and phrase objects are loaded
    $frm_lnk = new formula_link($t->usr1);
    $frm_lnk->fob = $frm;
    $frm_lnk->tob = $phr;
    $frm_lnk->load();

    $frm_lnk2 = new formula_link($t->usr1);
    $frm_lnk2->id = $frm_lnk->id;
    $frm_lnk2->load();
    $frm_lnk2->load_objects();

    // ... if form name is correct the chain of load via object, reload via id and load of the objects has worked
    if ($frm_lnk2->fob != null) {
        $result = $frm_lnk2->fob->dsp_obj()->name();
    }
    $target = $frm->dsp_obj()->name();
    $t->dsp('formula_link->load by formula id and link id "' . $frm->dsp_obj()->name() . '', $target, $result);

    $result = '';
    if ($frm_lnk2->tob != null) {
        $result = $frm_lnk2->tob->name();
    }
    $target = $phr->name();
    $t->dsp('formula_link->load by phrase id and link id "' . $phr->name() . '', $target, $result);

    // ... check if the link is shown correctly
    $frm = $t->load_formula(formula::TN_RENAMED);
    $phr_lst = $frm->assign_phr_ulst();
    echo $phr_lst->dsp_id() . '<br>';
    $result = $phr_lst->does_contain($phr);
    $target = true;
    $t->dsp('formula->assign_phr_ulst contains "' . $phr->name . '" for user "' . $t->usr1->name . '"', $target, $result);

    // ... check if the link is shown correctly also for the second user
    // ... the second user has excluded the word at this point, so even if the word is linked the word link is nevertheless false
    // TODO check what that the word is linked if the second user activates the word
    $frm = new formula($t->usr2);
    $frm->name = formula::TN_RENAMED;
    $frm->load();
    $phr_lst = $frm->assign_phr_ulst();
    $result = $phr_lst->does_contain($phr);
    $target = false;
    $t->dsp('formula->assign_phr_ulst contains "' . $phr->name . '" for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value update has been triggered

    // if second user removes the new link
    $frm = new formula($t->usr2);
    $frm->name = formula::TN_RENAMED;
    $frm->load();
    $phr = new phrase($t->usr2);
    $phr->name = word::TN_RENAMED;
    $phr->load();
    $result = $frm->unlink_phr($phr);
    $target = '';
    $t->dsp('formula_link->unlink_phr "' . $phr->name . '" from "' . $frm->name . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the removal of the link for the second user has been logged
    $log = new user_log_link;
    $log->table = 'formula_links';
    $log->old_from_id = $frm->id;
    $log->old_to_id = $phr->id;
    $log->usr = $t->usr2;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test partner unlinked System Test Formula Renamed from ' . word::TN_RENAMED . '';
    $t->dsp('formula_link->unlink_phr logged for "' . $phr->name . '" to "' . $frm->name . '" and user "' . $t->usr2->name . '"', $target, $result);


    // ... check if the link is really not used any more for the second user
    $frm = new formula($t->usr2);
    $frm->name = formula::TN_RENAMED;
    $frm->load();
    $phr_lst = $frm->assign_phr_ulst();
    $result = $phr_lst->does_contain($phr);
    $target = false;
    $t->dsp('formula->assign_phr_ulst contains "' . $phr->name . '" for user "' . $t->usr2->name . '" not any more', $target, $result);


    // ... check if the value update for the second user has been triggered

    // ... check if the link is still used for the first user
    $frm = $t->load_formula(formula::TN_RENAMED);
    $phr_lst = $frm->assign_phr_ulst();
    $result = $phr_lst->does_contain($phr);
    $target = true;
    $t->dsp('formula->assign_phr_ulst still contains "' . $phr->name . '" for user "' . $t->usr1->name . '"', $target, $result);

    // ... check if the values for the first user are still the same

    // if the first user also removes the link, both records should be deleted
    $result = $frm->unlink_phr($phr);
    $target = '';
    $t->dsp('formula_link->unlink_phr "' . $phr->name . '" from "' . $frm->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check the correct logging
    $log = new user_log_link;
    $log->table = 'formula_links';
    $log->old_from_id = $frm->id;
    $log->old_to_id = $phr->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test unlinked System Test Formula Renamed from ' . word::TN_RENAMED . '';
    $t->dsp('formula_link->unlink_phr logged of "' . $phr->name . '" from "' . $frm->name . '"', $target, $result);

    // check if the formula is not used any more for both users
    $frm = $t->load_formula(formula::TN_RENAMED);
    $phr_lst = $frm->assign_phr_ulst();
    $result = $phr_lst->does_contain($phr);
    $target = false;
    $t->dsp('formula->assign_phr_ulst contains "' . $phr->name . '" for user "' . $t->usr1->name . '" not any more', $target, $result);


    // ... and the values have been updated

    // insert the link again for the first user
    /*
    $frm = $t->load_formula(formula::TN_RENAMED);
    $phr = New phrase;
    $phr->name = word::TEST_NAME_CHANGED;
    $phr->usr = $t->usr2;
    $phr->load();
    $result = $frm->link_phr($phr);
    $target = '1';
    $t->dsp('formula_link->link_phr "'.$phr->name.'" to "'.$frm->name.'"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */

    // ... if the second user changes the link

    // ... and the first user removes the link

    // ... the link should still be active for the second user

    // ... but not for the first user

    // ... and the owner should now be the second user

    // the code changes and tests for formula link should be moved the view_component_link

}

function run_formula_link_list_test(testing $t)
{

    $t->header('Test the formula link list class (classes/formula_link_list.php)');

    $frm = $t->load_formula(formula::TN_INCREASE);
    $frm_lnk_lst = new formula_link_list($t->usr1);
    $frm_lnk_lst->load_by_frm_id($frm->id);
    $phr_ids = $frm_lnk_lst->phrase_ids(false);
    $phr_lst = new phrase_list($t->usr1);
    $phr_lst->load_by_ids($phr_ids);
    $result = $phr_lst->dsp_id();
    $target = word::TN_YEAR;
    $t->dsp_contains(', formula_link_list->load phrase linked to ' . $frm->dsp_id() . '', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);

}