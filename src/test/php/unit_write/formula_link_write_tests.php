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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_ENUM . 'change_tables.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_link_write_tests
{

    function run(test_cleanup $t): void
    {
        // init
        $t_db = new test_db_load($t);
        $t_frm = new test_formulas($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula link ';
        $t->header($ts);

        $t->subheader($ts . 'for ' . formulas::SYSTEM_TEST_ADD);
        $t->assert_write_link($t_frm->formula_link_filled_add());

        $t->subheader($ts . 'specific');
        $frm = $t_db->test_formula(formulas::SYSTEM_TEST_ADD, formulas::INCREASE_EXP, $usr_msg);
        $wrd = $t_db->test_word(words::TEST_ADD);


        $t_db->test_formula_link(formulas::SYSTEM_TEST_ADD, words::TEST_ADD);

        $test_name = 'link phrase "' . $wrd->name() . '" to a formula "' . $frm->name() . '" using the formula function link_phr';
        $result = $frm->link_phrase_and_save($wrd->phrase(), $usr_msg);
        $t->assert_true($test_name, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check the correct logging
        $phr = new phrase($t->usr1);
        $phr->load_by_name(words::TEST_ADD);
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::FORMULA_LINK);
        $log->new_from_id = $frm->id();
        $log->new_to_id = $phr->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' linked System Test Formula to ' . words::TEST_ADD;
        $t->assert('formula_link->link_phr logged for "' . $phr->name() . '" to "' . $frm->name() . '"', $result, $target);

        // ... check if the link can be loaded by formula and phrase id and base on the id the correct formula and phrase objects are loaded
        $frm_lnk = new formula_link($t->usr1);
        $frm_lnk->load_by_link($frm, $phr);

        $frm_lnk2 = new formula_link($t->usr1);
        $frm_lnk2->load_by_id($frm_lnk->id(), formula_link::class);
        $frm_lnk2->reload_objects($usr_msg);

        // ... if form name is correct the chain of load via object, reload via id and load of the objects has worked
        if ($frm_lnk2->formula() != null) {
            if ($frm_lnk2->formula()::class == formula::class) {
                $fop_dsp = new formula_ui($frm_lnk2->formula()->api_json());
                $result = $fop_dsp->name();
            } else {
                log_err('unexpected class in formula link test');
            }
        }
        $frm_html = new formula_ui($frm->api_json());
        $target = $frm_html->name();
        $t->assert('formula_link->load by formula id and link id "' . $frm_html->name(), $result, $target);

        $result = '';
        if ($frm_lnk2->phrase() != null) {
            $result = $frm_lnk2->phrase()->name();
        }
        $target = $phr->name();
        $t->assert('formula_link->load by phrase id and link id "' . $phr->dsp_name(), $result, $target);

        // ... check if the link is shown correctly
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        echo $phr_lst->dsp_id() . '<br>';
        $result = $phr_lst->does_contain($phr);
        $target = true;
        $t->assert('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr1->name . '"', $result, $target);

        // ... check if the link is shown correctly also for the second user
        // ... the second user has excluded the word at this point,
        //     so even if the word is linked the word link is nevertheless false
        // TODO add a check that the word is linked if the second user activates the word
        $frm = new formula($t->usr2);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        // TODO fix it
        //$t->assert('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '"', $result, $target);

        // ... check if the value update has been triggered

        $test_name = 'if second user removes the new link to ' . $phr->name()
            . ' from the formula ' . $frm->name() . '" by user "' . $t->usr2->name . '"';
        // if second user removes the new link
        $frm = new formula($t->usr2);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $phr = new phrase($t->usr2);
        $phr->load_by_name(words::TEST_ADD);
        $t->assert_true($test_name, $frm->unlink_phrase($phr, $usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_link($t->usr2);
        $log->set_table(change_tables::FORMULA_LINK);
        $log->old_from_id = $frm->id();
        $log->old_to_id = $phr->id();
        $result = $log->dsp_last(true);
        // TODO fix it
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked System Test Formula Renamed from ' . words::TEST_ADD . '';
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' ';
        $t->assert('formula_link->unlink_phr logged for "' . $phr->name() . '" to "' . $frm->name() . '" and user "' . $t->usr2->name . '"', $result, $target);


        // ... check if the link is really not used any more for the second user
        $frm = new formula($t->usr2);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->assert('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '" not any more', $result, $target);


        // ... check if the value update for the second user has been triggered

        // ... check if the link is still used for the first user
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = true;
        // TODO Prio 1 activate
        // $t->assert('formula->assign_phr_ulst still contains "' . $phr->name() . '" for user "' . $t->usr1->name . '"', $result, $target);

        // ... check if the values for the first user are still the same

        $test_name = 'if the first user also removes the link to ' . $phr->name()
            . ', both links of formula ' . $frm->name() . ' should be deleted';
        $t->assert_true($test_name, $frm->unlink_phrase($phr, $usr_msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::FORMULA_LINK);
        $log->old_from_id = $frm->id();
        $log->old_to_id = $phr->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' unlinked System Test Formula from ' . words::TEST_ADD;
        $t->assert('formula_link->unlink_phr logged of "' . $phr->name() . '" from "' . $frm->name() . '"', $result, $target);

        // check if the formula is not used any more for both users
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->assert('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr1->name . '" not any more', $result, $target);


        // ... and the values have been updated

        // insert the link again for the first user
        /*
        $frm = $t->load_formula(formulas::TN_ADD);
        $phr = New phrase($t->usr2);
        $phr->load_by_name(word::TEST_NAME_CHANGED);
        $result = $frm->link_phr($phr);
        $target = '1';
        $t->assert('formula_link->link_phr "'.$phr->name().'" to "'.$frm->name.'"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        */

        // ... if the second user changes the link

        // ... and the first user removes the link

        // ... the link should still be active for the second user

        // ... but not for the first user

        // ... and the owner should now be the second user

        // the code changes and tests for formula link should be moved the component_link

        $t->subheader($ts . 'cleanup formula link write');
        $usr_msg->reset(true);
        $frm = new formula($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD);
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::TEST_ADD);
        $lnk = new formula_link($t->usr1);
        $lnk->load_by_link($frm, $wrd->phrase());
        $lnk->del($usr_msg);
        foreach (formulas::TEST_FORMULAS as $frm_name) {
            $t->write_named_cleanup($frm, $frm_name);
        }
        foreach (words::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

        $usr_msg->reset(true);
        $frm->del($usr_msg);
        $usr_msg->reset(true);
        $wrd->del($usr_msg);

    }

    function run_list(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula link list ';
        $t->header($ts);

        // prepare
        $frm = $t_db->add_formula(formulas::INCREASE, formulas::INCREASE_EXP, $usr_msg);
        $phr = $t_db->add_word(words::YEAR_CAP)->phrase();
        $frm->link_phrase_and_save($phr, $usr_msg);
        $t_db->test_formula_link(formulas::INCREASE, words::YEAR_CAP);

        // test
        $frm_lnk_lst = new formula_link_list($t->usr1);
        $frm_lnk_lst->load_by_frm_id($frm->id());
        $phr_ids = $frm_lnk_lst->phrase_ids(false);
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_names_by_ids($phr_ids);
        $result = $phr_lst->dsp_id();
        $target = words::YEAR_CAP;
        // TODO fix it
        // $t->dsp_contains(', formula_link_list->load phrase linked to ' . $frm->dsp_id() . '', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);

    }

    function create_test_formula_links(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db create formula links ';
        $t->header($ts);

        $t_db->test_formula_link(formulas::SYSTEM_TEST_RATIO, words::TEST_SHARE);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_SECTOR, words::TEST_SHARE);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_ADD, words::YEAR_CAP);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_SCALE_K, words::TEST_IN_K);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_SCALE_TO_K, words::ONE);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_SCALE_MIO, words::MIO);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_SCALE_BIL, words::TEST_BIL);
        $t_db->test_formula_link(formulas::INCREASE, words::YEAR_CAP);

    }

}