<?php

/*

    test/php/unit_write/triple_tests.php - write test triples to the database and check the results
    ------------------------------------
  

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

use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\formula;
use cfg\log\change_link;
use cfg\log\change_table_list;
use cfg\triple;
use cfg\verb;
use cfg\word;
use shared\library;
use test\all_tests;
use test\test_cleanup;
use function test\zu_test_time_setup;

class triple_tests
{

    function run(test_cleanup $t): void
    {

        global $verbs;
        $lib = new library();

        $t->header('triple db write tests');

        $test_name = 'add triple ' . triple_api::TN_ADD_VIA_FUNC . ' via sql function';
        $trp = $t->triple_add_by_func();
        $trp->save(true);
        $trp->reset();
        $trp->load_by_name(triple_api::TN_ADD_VIA_FUNC);
        $t->assert_true($test_name, $trp->isset());

        $test_name = 'add triple ' . triple_api::TN_ADD_VIA_SQL . ' via sql insert';
        $trp = $t->triple_add_by_sql();
        $trp->save(false);
        $trp->reset();
        $trp->load_by_name(triple_api::TN_ADD_VIA_SQL);
        $t->assert_true($test_name, $trp->isset());

        // load the main test word and verb
        $is_id = $verbs->id(verb::IS);

        // create the group test word
        $wrd_company = $t->test_word(word_api::TN_COMPANY);

        // check if basic triples (Zurich (City) and Zurich (Canton)
        $wrd_zh = $t->load_word(word_api::TN_ZH);
        $wrd_city = $t->load_word(word_api::TN_CITY);
        $wrd_canton = $t->load_word(word_api::TN_CANTON);

        // ... now test the Canton Zurich
        $lnk_canton = new triple($t->usr1);
        $lnk_canton->load_by_link_id($wrd_zh->id(), $is_id, $wrd_canton->id());
        $target = word_api::TN_ZH . ' (' . word_api::TN_CANTON . ')';
        $result = $lnk_canton->name();
        $t->display('triple->load for Canton Zurich', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // ... now test the Canton Zurich using the name function
        $target = word_api::TN_ZH . ' (' . word_api::TN_CANTON . ')';
        $result = $lnk_canton->name();
        $t->display('triple->load for Canton Zurich using the function', $target, $result);

        // ... now test the Insurance Zurich
        $lnk_company = new triple($t->usr1);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $target = triple_api::TN_ZH_COMPANY;
        $result = $lnk_company->name();
        $t->display('triple->load for ' . triple_api::TN_ZH_COMPANY, $target, $result);

        // ... now test the Insurance Zurich using the name function
        $target = triple_api::TN_ZH_COMPANY;
        $result = $lnk_company->name();
        $t->display('triple->load for ' . triple_api::TN_ZH_COMPANY . ' using the function', $target, $result);

        // add a triple based on the id of the added test word, verb and the parent test word
        $wrd_from = $t->test_word(word_api::TN_RENAMED);
        $wrd_to = $t->test_word(word_api::TN_PARENT);
        $trp = new triple($t->usr1);
        $trp->fob->set_id($wrd_from->id());
        $trp->verb->set_id($is_id);
        $trp->tob->set_id($wrd_to->id());
        if ($wrd_from->id() <> 0 and $is_id and $wrd_to->id() <> 0) {
            $result = $trp->save();
        } else {
            $result = 'id missing';
        }
        $target = '';
        $t->display('triple->save "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        $t->subheader("... and also testing the user log link class (classes/user_log_link.php)");

        // ... check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::TRIPLE);
        $log->new_from_id = $wrd_from->id();
        $log->new_link_id = $is_id;
        $log->new_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test linked ' . word_api::TN_RENAMED . ' to ' . word_api::TN_PARENT;
        $t->display('triple->save logged for "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '"', $target, $result);

        // ... check if the link is shown correctly

        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $result = $trp->name();
        $target = word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
        $t->display('triple->load', $target, $result);
        // ... check if the link is shown correctly also for the second user
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $result = $lnk2->name();
        $target = word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
        $t->display('triple->load for user "' . $t->usr2->name . '"', $target, $result);

        // ... check if the value update has been triggered

        // if second user removes the new link
        $trp = new triple($t->usr2);
        $trp->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $msg = $trp->del();
        $result = $msg->get_last_message();
        $target = '';
        $t->display('triple->del "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" by user "' . $t->usr2->name . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the removal of the link for the second user has been logged
        $log = new change_link($t->usr2);
        $log->set_table(change_table_list::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test partner unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT . '';
        $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr2->name . '"', $target, $result);


        // ... check if the link is really not used any more for the second user
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $result = $lnk2->name();
        $target = '';
        $t->display('triple->load "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr2->name . '" not any more', $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the value update for the second user has been triggered

        // ... check all places where the word maybe used ...

        // ... check if the link is still used for the first user
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $result = $trp->name();
        $target = '' . word_api::TN_RENAMED . ' (' . word_api::TN_PARENT . ')';
        $t->display('triple->load of "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" is still used for user "' . $t->usr1->name . '"', $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $msg = $trp->del();
        $result = $msg->get_last_message();
        $target = '';
        $t->display('triple->del "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT;
        $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $target, $result);

        // check if the formula is not used any more for both users
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $is_id, $wrd_to->id());
        $result = $trp->name();
        $target = '';
        $t->display('triple->load of "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr1->name . '" not used any more', $target, $result);

        // check if the name of a triple can be changed
        $trp = $t->test_triple(word_api::TN_RENAMED, verb::IS, word_api::TN_PARENT);
        $trp->set_name(triple_api::TN_ADD);
        $result = $trp->save();
        $t->assert('triple->save name to ' . triple_api::TN_ADD, $result);

        // ... and if the name check if the name of a triple can be changed
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_api::TN_ADD);
        $t->assert('triple load changed name of ' . triple_api::TN_ADD, $trp->name(), triple_api::TN_ADD);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_table_list::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test unlinked ' . word_api::TN_RENAMED . ' from ' . word_api::TN_PARENT;
        $t->display('triple->del logged for "' . $wrd_from->name() . '" ' . verb::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $target, $result);

        // check that even after renaming the triple no word with the standard name of the triple can be added
        $wrd = new word($t->usr1);
        $wrd->set_name(triple_api::TN_ADD_AUTO);
        $result = $wrd->save();
        $target = 'A triple with the name "System Test Triple" already exists. ' .
            'Please use another word name.';
        $t->assert('word cannot have a standard triple name', $result, $target);

        // ... and no verb either
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(triple_api::TN_ADD_AUTO);
        $result = $vrb->save();
        $target = '<style class="text-danger">A triple with the name "System Test Triple" already exists. '
            . 'Please use another ' . $lib->class_to_name(verb::class) . ' name.</style>';
        $t->assert('verb cannot have a standard triple name', $result, $target);

        // ... and no formula either
        $frm = new formula($t->usr1);
        $frm->set_name(triple_api::TN_ADD_AUTO);
        $result = $frm->save();
        $target = '<style class="text-danger">A triple with the name "System Test Triple" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.</style>';
        $t->assert('word cannot have a standard triple name', $result, $target);


        // ... and the values have been updated
        /*
        // insert the link again for the first user
        $frm =$t->load_formula(TF_ADD_RENAMED);
        $phr = New phrase($t->usr2);
        $phr->load_by_name(word::TEST_NAME_CHANGED);
        $result = $frm->link_phr($phr);
        $target = '1';
        $t->display('triple->link_phr "'.$phr->name().'" to "'.$frm->name.'"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);
        */
        // ... if the second user changes the link

        // ... and the first user removes the link

        // ... the link should still be active for the second user

        // ... but not for the first user

        // ... and the owner should now be the second user

        // the code changes and tests for formula link should be moved the component_link

    }

    function create_test_triples(all_tests $t): void
    {
        $t->header('Check if all base phrases are correct');

        // activate the excluded objects to check the setup
        $trp = new triple($t->usr2);
        $trp->load_by_name(triple_api::TN_EXCLUDED);
        if ($trp->id() != 0) {
            $trp->set_excluded(false);
            $trp->save();
        }

        // check if the standard samples for triples still exist and if not, create the samples
        $t->test_triple(word_api::TN_ZH, verb::IS, word_api::TN_CANTON, triple_api::TN_ZH_CANTON, triple_api::TN_ZH_CANTON);
        $t->test_triple(word_api::TN_ZH, verb::IS, word_api::TN_CITY, triple_api::TN_ZH_CITY, triple_api::TN_ZH_CITY);
        $t->test_triple(word_api::TN_ZH, verb::IS, word_api::TN_COMPANY, triple_api::TN_ZH_COMPANY, triple_api::TN_ZH_COMPANY);
        $t->test_triple(triple_api::TN_ZH_CANTON, verb::IS_PART_OF, word_api::TN_CH);
        $t->test_triple(triple_api::TN_ZH_CITY, verb::IS_PART_OF, triple_api::TN_ZH_CANTON);
        $t->test_triple(triple_api::TN_ZH_COMPANY, verb::IS_PART_OF, triple_api::TN_ZH_CITY, triple_api::TN_EXCLUDED, triple_api::TN_EXCLUDED);

        $t->test_triple(word_api::TN_ABB, verb::IS, word_api::TN_COMPANY, triple_api::TN_ABB_COMPANY);
        // TODO check why it is possible to create a triple with the same name as a word
        //$t->test_triple(word_api::TN_VESTAS, verb::IS_A, TEST_WORD, word_api::TN_VESTAS, word_api::TN_VESTAS);
        $t->test_triple(word_api::TN_VESTAS, verb::IS, word_api::TN_COMPANY, triple_api::TN_VESTAS_COMPANY, triple_api::TN_VESTAS_COMPANY);
        $t->test_triple(word_api::TN_2014, verb::FOLLOW, word_api::TN_2013, triple_api::TN_2014_FOLLOW);
        // TODO check direction
        $t->test_triple(word_api::TN_TAX, verb::IS_PART_OF, word_api::TN_CASH_FLOW, triple_api::TN_TAXES_OF_CF);

        $t->header('Check if all base phrases are correct');
        $t->test_phrase(triple_api::TN_ZH_COMPANY);

        // exclude some to test the handling of exclude objects
        $trp = new triple($t->usr2);
        $trp->load_by_name(triple_api::TN_EXCLUDED);
        $trp->set_excluded(true);
        $trp->save();
    }

    function create_base_times(test_cleanup $t): void
    {
        $t->header('Check if base time words are correct');

        zu_test_time_setup($t);
    }

}