<?php

/*

    test/php/unit_write/formula_link_tests.php - write test FORMULA LINKS to the database and check the results
    ------------------------------------------
  

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

namespace unit_write;

use api\formula\formula as formula_api;
use api\word\word as word_api;
use cfg\user\user;
use cfg\word\word;
use html\formula\formula as formula_dsp;
use cfg\log\change_link;
use cfg\log\change_table_list;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_link_list;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use test\test_cleanup;

class formula_link_write_tests
{

    function run(test_cleanup $t): void
    {

        $t->header('formula link db write tests');

        $t->subheader('formula link write sandbox tests for ' . formula_api::TN_ADD);
        $t->assert_write_link($t->formula_link_filled_add());

        $t->subheader('prepare formula link specific write tests');
        $frm = $t->test_formula(formula_api::TN_ADD, formula_api::TF_INCREASE);
        $wrd = $t->test_word(word_api::TN_ADD);


        $t->test_formula_link(formula_api::TN_ADD, word_api::TN_ADD);

        // link the test formula to another word
        $test_name = 'link phrase "' . $wrd->name() . '" to a formula "' . $frm->name() . '" using the formula function link_phr';
        $result = $frm->link_phr($wrd->phrase());
        $t->assert($test_name, $result, '', $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check the correct logging
        $phr = new phrase($t->usr1);
        $phr->load_by_name(word_api::TN_ADD);
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::FORMULA_LINK);
        $log->new_from_id = $frm->id();
        $log->new_to_id = $phr->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' linked System Test Formula to ' . word_api::TN_ADD;
        $t->display('formula_link->link_phr logged for "' . $phr->name() . '" to "' . $frm->name() . '"', $target, $result);

        // ... check if the link can be loaded by formula and phrase id and base on the id the correct formula and phrase objects are loaded
        $frm_lnk = new formula_link($t->usr1);
        $frm_lnk->load_by_link($frm, $phr);

        $frm_lnk2 = new formula_link($t->usr1);
        $frm_lnk2->load_by_id($frm_lnk->id(), formula_link::class);
        $frm_lnk2->load_objects();

        // ... if form name is correct the chain of load via object, reload via id and load of the objects has worked
        if ($frm_lnk2->formula() != null) {
            if ($frm_lnk2->formula()::class == formula::class) {
                $fop_dsp = new formula_dsp($frm_lnk2->formula()->api_json());
                $result = $fop_dsp->name();
            } else {
                log_err('unexpected class in formula link test');
            }
        }
        $frm_html = new formula_dsp($frm->api_json());
        $target = $frm_html->name();
        $t->display('formula_link->load by formula id and link id "' . $frm_html->name(), $target, $result);

        $result = '';
        if ($frm_lnk2->phrase() != null) {
            $result = $frm_lnk2->phrase()->name();
        }
        $target = $phr->name();
        $t->display('formula_link->load by phrase id and link id "' . $phr->dsp_name(), $target, $result);

        // ... check if the link is shown correctly
        $frm = $t->load_formula(formula_api::TN_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        echo $phr_lst->dsp_id() . '<br>';
        $result = $phr_lst->does_contain($phr);
        $target = true;
        $t->display('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr1->name . '"', $target, $result);

        // ... check if the link is shown correctly also for the second user
        // ... the second user has excluded the word at this point,
        //     so even if the word is linked the word link is nevertheless false
        // TODO add a check that the word is linked if the second user activates the word
        $frm = new formula($t->usr2);
        $frm->load_by_name(formula_api::TN_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        // TODO fix it
        //$t->display('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '"', $target, $result);

        // ... check if the value update has been triggered

        // if second user removes the new link
        $frm = new formula($t->usr2);
        $frm->load_by_name(formula_api::TN_ADD);
        $phr = new phrase($t->usr2);
        $phr->load_by_name(word_api::TN_ADD);
        $result = $frm->unlink_phr($phr);
        $target = '';
        $t->display('formula_link->unlink_phr "' . $phr->name() . '" from "' . $frm->name() . '" by user "' . $t->usr2->name . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_link($t->usr2);
        $log->set_table(change_table_list::FORMULA_LINK);
        $log->old_from_id = $frm->id();
        $log->old_to_id = $phr->id();
        $result = $log->dsp_last(true);
        // TODO fix it
        $target = user::SYSTEM_TEST_PARTNER_NAME . ' unlinked System Test Formula Renamed from ' . word_api::TN_ADD . '';
        $target = user::SYSTEM_TEST_PARTNER_NAME . ' ';
        $t->display('formula_link->unlink_phr logged for "' . $phr->name() . '" to "' . $frm->name() . '" and user "' . $t->usr2->name . '"', $target, $result);


        // ... check if the link is really not used any more for the second user
        $frm = new formula($t->usr2);
        $frm->load_by_name(formula_api::TN_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->display('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '" not any more', $target, $result);


        // ... check if the value update for the second user has been triggered

        // ... check if the link is still used for the first user
        $frm = $t->load_formula(formula_api::TN_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = true;
        // TODO activate Prio 1
        // $t->display('formula->assign_phr_ulst still contains "' . $phr->name() . '" for user "' . $t->usr1->name . '"', $target, $result);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $result = $frm->unlink_phr($phr);
        $target = '';
        $t->display('formula_link->unlink_phr "' . $phr->name() . '" from "' . $frm->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::FORMULA_LINK);
        $log->old_from_id = $frm->id();
        $log->old_to_id = $phr->id();
        $result = $log->dsp_last(true);
        $target = user::SYSTEM_TEST_NAME . ' unlinked System Test Formula from ' . word_api::TN_ADD;
        $t->display('formula_link->unlink_phr logged of "' . $phr->name() . '" from "' . $frm->name() . '"', $target, $result);

        // check if the formula is not used any more for both users
        $frm = $t->load_formula(formula_api::TN_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->display('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr1->name . '" not any more', $target, $result);


        // ... and the values have been updated

        // insert the link again for the first user
        /*
        $frm = $t->load_formula(formula_api::TN_ADD);
        $phr = New phrase($t->usr2);
        $phr->load_by_name(word::TEST_NAME_CHANGED);
        $result = $frm->link_phr($phr);
        $target = '1';
        $t->display('formula_link->link_phr "'.$phr->name().'" to "'.$frm->name.'"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
        */

        // ... if the second user changes the link

        // ... and the first user removes the link

        // ... the link should still be active for the second user

        // ... but not for the first user

        // ... and the owner should now be the second user

        // the code changes and tests for formula link should be moved the component_link

        $t->subheader('cleanup formula link write');
        $frm = new formula($t->usr1);
        $frm->load_by_name(formula_api::TN_ADD);
        $wrd = new word($t->usr1);
        $wrd->load_by_name(word_api::TN_ADD);
        $lnk = new formula_link($t->usr1);
        $lnk->load_by_link($frm, $wrd->phrase());
        $lnk->del();
        foreach (formula_api::TEST_FORMULAS as $frm_name) {
            $t->write_named_cleanup($frm, $frm_name);
        }
        foreach (word_api::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

        $frm->del();
        $wrd->del();

    }

    function run_list(test_cleanup $t): void
    {

        $t->header('Test the formula link list class (classes/formula_link_list.php)');

        // prepare
        $frm = $t->add_formula(formula_api::TN_INCREASE, formula_api::TF_INCREASE);
        $phr = $t->add_word(word_api::TN_YEAR)->phrase();
        $frm->link_phr($phr);
        $t->test_formula_link(formula_api::TN_INCREASE, word_api::TN_YEAR);

        // test
        $frm_lnk_lst = new formula_link_list($t->usr1);
        $frm_lnk_lst->load_by_frm_id($frm->id());
        $phr_ids = $frm_lnk_lst->phrase_ids(false);
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_names_by_ids($phr_ids);
        $result = $phr_lst->dsp_id();
        $target = word_api::TN_YEAR;
        // TODO fix it
        // $t->dsp_contains(', formula_link_list->load phrase linked to ' . $frm->dsp_id() . '', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);

    }

    function create_test_formula_links(test_cleanup $t): void
    {
        $t->header('Check if all base formulas link correctly');

        $t->test_formula_link(formula_api::TN_RATIO, word_api::TN_SHARE);
        $t->test_formula_link(formula_api::TN_SECTOR, word_api::TN_SHARE);
        $t->test_formula_link(formula_api::TN_ADD, word_api::TN_YEAR);
        $t->test_formula_link(formula_api::TN_SCALE_K, word_api::TN_IN_K);
        $t->test_formula_link(formula_api::TN_SCALE_TO_K, word_api::TN_ONE);
        $t->test_formula_link(formula_api::TN_SCALE_MIO, word_api::TN_MIO);
        $t->test_formula_link(formula_api::TN_SCALE_BIL, word_api::TN_BIL);
        $t->test_formula_link(formula_api::TN_INCREASE, word_api::TN_YEAR);

    }

}