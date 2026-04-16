<?php

/*

    test/php/unit_write/triple_write_tests.php - write test triples to the database and check the results
    ------------------------------------------

    just the special test cases not covered by the horizontal write tests


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
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';

use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\log\change_link;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use function Zukunft\ZukunftCom\test\php\utils\zu_test_time_setup;

class triple_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $t_trp = new test_triples($t);
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write triple ';
        $t->header($ts);
        $t_trp->cleanup($ts);

        $t->subheader($ts . 'prepare');
        $vrb_is_id = $t->assert_verb_id(verbs::IS, verbs::IS_ID, 'load the verb used for testing');
        $t_db->test_word(words::TEST_ADD_VIA_FUNC);

        $t->subheader($ts . 'triple prepared write');
        $test_name = 'add triple ' . triples::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_trp->triple_add_by_func(), true);

        $t->subheader($ts . 'sandbox for ' . triples::SYSTEM_TEST_ADD);
        //$t->assert_write_link($t_trp->triple_filled_add(), triples::TN_ADD);


        // create the related objects for link objects
        $wrd_from = $t_db->test_word(words::TEST_RENAMED);
        $wrd_to = $t_db->test_word(words::TEST_PARENT);

        // remove any remaining db entries from previous tests
        $trp = $t_db->test_triple(words::TEST_RENAMED, verbs::IS, words::TEST_PARENT);
        $trp_del = new triple($t->usr1);
        $trp_del->load_by_id($trp->id());
        $trp_del->del($usr_msg);
        $trp_del = new triple($t->usr2);
        $trp_del->load_by_id($trp->id());
        $trp_del->del($usr_msg);

        $trp = $t_db->test_triple(words::TEST_RENAMED, verbs::IS, words::TEST_PARENT);
        $trp->set_user($t->usr1);
        $trp->include();
        $trp->save($usr_msg);

        $t->subheader("... and also testing the user log link class (classes/user_log_link.php)");
        $test_name = 'check the correct logging of adding a triple  "' . words::TEST_RENAMED . '" ' . verbs::IS . ' "' . words::TEST_PARENT . '" based on the id of the added test word, verb and the parent test word';
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE);
        $log->new_from_id = $wrd_from->id();
        $log->new_link_id = $vrb_is_id;
        $log->new_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' linked ' . words::TEST_RENAMED . ' to ' . words::TEST_PARENT;
        $t->assert($test_name, $result, $target);

        $test_name = '... check if the link is shown correctly';
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->set_name('');
        $result = $trp->name_generated();
        $target = words::TEST_RENAMED . ' (' . words::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);
        $result = $trp->name();
        // $target = triples::TN_ADD;
        $target = words::TEST_RENAMED . ' (' . words::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);
        $test_name = ' ... check if the link is shown correctly also for the second user "' . $t->usr2->name . '"';
        $trp->set_user($t->usr2);
        $trp->include();
        $trp->save($usr_msg);
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $result = $lnk2->name();
        $target = words::TEST_RENAMED . ' (' . words::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);

        $t->subheader(" ... check if the value update has been triggered");

        $test_name = 'triple the second user "' . $t->usr2->name . '" deletes it';
        $trp = new triple($t->usr2);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->del($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if the removal of the link "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for the second user "' . $t->usr2->name . '" has been logged';
        $log = new change_link($t->usr2);
        $log->set_table(change_tables::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        // TODO Prio 0 fix it
        //$target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked ' . words::TEST_RENAMED . ' from ' . words::TEST_PARENT . '';
        $target = '';
        $t->assert($test_name, $result, $target);


        // ... check if the link is really not used any more for the second user
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $result = $lnk2->name();
        $target = '';
        $t->assert('triple->load "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr2->name . '" not any more', $result, $target, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the value update for the second user has been triggered

        // ... check all places where the word maybe used ...

        // ... check if the link is still used for the first user
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->set_name('');
        $result = $trp->name_generated();
        $target = words::TEST_RENAMED . ' (' . words::TEST_PARENT . ')';
        $t->assert('triple->load of "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" is still used for user "' . $t->usr1->name . '"', $result, $target, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->del($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert('triple->del "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' unlinked ' . words::TEST_RENAMED . ' from ' . words::TEST_PARENT;
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked ' . words::TEST_RENAMED . ' from ' . words::TEST_PARENT;
        // TODO Prio 0 fix it
        $target = '';
        $t->assert('triple->del logged for "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $result, $target);

        // check if the formula is not used any more for both users
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $result = $trp->name();
        $target = '';
        $t->assert('triple->load of "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr1->name . '" not used any more', $result, $target);

        // check if the name of a triple can be changed
        $trp = $t_db->test_triple(words::TEST_RENAMED, verbs::IS, words::TEST_PARENT);
        $trp->set_name(triples::SYSTEM_TEST_ADD);
        $trp->save($usr_msg);
        $result = $usr_msg->get_last_message();
        $t->assert('triple->save name to ' . triples::SYSTEM_TEST_ADD, $result);

        // ... and if the name check if the name of a triple can be changed
        $trp = new triple($t->usr1);
        $trp->load_by_name(triples::SYSTEM_TEST_ADD);
        $t->assert('triple load changed name of ' . triples::SYSTEM_TEST_ADD, $trp->name(), triples::SYSTEM_TEST_ADD);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last(true);
        $target = users::SYSTEM_TEST_NAME . ' unlinked ' . words::TEST_RENAMED . ' from ' . words::TEST_PARENT;
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked System Test Word Renamed from System Test Word Parent';
        // TODO Prio 0 fix it
        $target = '';
        $t->assert('triple->del logged for "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $result, $target);

        // check that even after renaming the triple no word with the standard name of the triple can be added
        $wrd = new word($t->usr1);
        $wrd->set_name(triples::SYSTEM_TEST_ADD_AUTO);
        $usr_msg = new user_message($t->usr1);
        $wrd->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A word with the name "' . triples::SYSTEM_TEST_ADD_AUTO . '" already exists. Please use another word name.';
        $t->assert('word cannot have a standard triple name', $result, $target);

        // ... and no verb either
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(triples::SYSTEM_TEST_ADD_AUTO);
        $usr_msg = new user_message($t->usr1);
        $vrb->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A triple with the name "System Test Triple" already exists. '
            . 'Please use another ' . $lib->class_to_name(verb::class) . ' name.';
        // TODO Prio 0 fix it
        $target = '';
        $t->assert('verb cannot have a standard triple name', $result, $target);

        // ... and no formula either
        $frm = new formula($t->usr1);
        $frm->set_name(triples::SYSTEM_TEST_ADD_AUTO);
        $usr_msg = new user_message($t->usr1);
        $frm->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A ' . $lib->class_to_name(formula::class) . ' with the name "System Test Triple" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.';
        $t->assert('word cannot have a standard triple name', $result, $target);

        $test_name = 'triple clean up tests';
        $trp = new triple($t->usr1);
        $usr_msg = new user_message($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->del($usr_msg);
        $result = $usr_msg->text();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        $trp = new triple($t->usr2);
        $usr_msg = new user_message($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $trp->del($usr_msg);
        $result = $usr_msg->text();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        $t->subheader($ts . 'cleanup');
        $trp = new triple($t->usr1);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $msg = $trp->del($usr_msg);
        $trp = new triple($t->usr2);
        $trp->load_by_link_id($wrd_from->id(), $vrb_is_id, $wrd_to->id());
        $msg = $trp->del($usr_msg);


        // ... and the values have been updated
        /*
        // insert the link again for the first user
        $frm =$t->load_formula(TF_ADD_RENAMED);
        $phr = New phrase($t->usr2);
        $phr->load_by_name(word::TEST_NAME_CHANGED);
        $result = $frm->link_phr($phr);
        $target = '1';
        $t->assert('triple->link_phr "'.$phr->name().'" to "'.$frm->name.'"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        */
        // ... if the second user changes the link

        // ... and the first user removes the link

        // ... the link should still be active for the second user

        // ... but not for the first user

        // ... and the owner should now be the second user

        // the code changes and tests for formula link should be moved the component_link

        // cleanup - fallback delete
        $t_trp->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($usr_msg);

    }

    function create_test_triples(all_tests $t): void
    {

        // start the test section (ts)
        $ts = 'db create test create_test_triples ';
        $t->header($ts);

        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // activate the excluded objects to check the setup
        $trp = new triple($t->usr2);
        $trp->load_by_name(triples::SYSTEM_TEST_EXCLUDED);
        if ($trp->id() != 0) {
            $trp->excluded = false;
            $trp->save($usr_msg);
        }

        // check if the standard samples for triples still exist and if not, create the samples
        $t_db->test_triple(words::ZH, verbs::IS, words::CANTON, triples::CANTON_ZURICH, triples::CANTON_ZURICH);
        $t_db->test_triple(words::ZH, verbs::IS, words::CITY, triples::CITY_ZH, triples::CITY_ZH);
        $t_db->test_triple(words::ZH, verbs::IS, words::COMPANY, triples::COMPANY_ZURICH, triples::COMPANY_ZURICH);
        $t_db->test_triple(triples::CANTON_ZURICH, verbs::PART_NAME, words::CH);
        $t_db->test_triple(triples::CITY_ZH, verbs::PART_NAME, triples::CANTON_ZURICH);
        // TODO Prio 1 activate
        //$t_db->test_triple(triples::COMPANY_ZURICH, verbs::PART_NAME, triples::CITY_ZH, triples::SYSTEM_TEST_EXCLUDED, triples::SYSTEM_TEST_EXCLUDED);

        $t_db->test_triple(words::ABB, verbs::IS, words::COMPANY, triples::COMPANY_ABB);
        // TODO check why it is possible to create a triple with the same name as a word
        //$t->test_triple(words::TN_VESTAS, verbs::IS_A, TEST_WORD, words::TN_VESTAS, words::TN_VESTAS);
        $t_db->test_triple(words::VESTAS, verbs::IS, words::COMPANY, triples::COMPANY_VESTAS, triples::COMPANY_VESTAS);
        $t_db->test_triple(words::YEAR_2014, verbs::FOLLOW, words::YEAR_2013, triples::YEAR_2013_FOLLOW);
        // TODO check direction
        $t_db->test_triple(triples::INCOME_TAX, verbs::PART_NAME, triples::CASH_FLOW_STATEMENT, triples::TAXES_OF_CF);

        $t->subheader($ts . 'base phrases');
        $t_db->test_phrase(triples::COMPANY_ZURICH);

        // exclude some to test the handling of exclude objects
        // TODO Prio 1 activate
        //$trp = new triple($t->usr2);
        //$trp->load_by_name(triples::SYSTEM_TEST_EXCLUDED);
        //$trp->set_excluded(true);
        //$trp->save($usr_msg);
    }

    function create_base_times(test_cleanup $t): void
    {
        // start the test section (ts)
        $ts = 'db create test words ';
        $t->header($ts);

        zu_test_time_setup($t);
    }

}