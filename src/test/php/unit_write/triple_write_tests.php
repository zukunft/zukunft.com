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
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_write_tests
{
    // the number of the value that uses the triple of the delete only if not used test
    const float USED_TRIPLE_NUMBER = 3.5;
    // the description that the second user gives the triple of the delete by the owner test
    const string OWNER_CHANGE_COM = 'the description of the second user';

    function run(test_cleanup $t): void
    {
        $msg = new user_message(); // a test is an entry point, so it creates the message the log setters report into

        // init
        $msg = new user_message($t->usr1);
        $msg2 = new user_message($t->usr2);
        $lib = new library();
        $t_trp = new test_triples($t);
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db write triple ';
        $t->header($ts);
        $t_trp->cleanup($ts);

        $t->subheader($ts . 'prepare');
        $vrb_is_id = $t->assert_verb_id(verbs::IS, verbs::IS_ID, 'load the verb used for testing');
        $t_db->test_word($msg, word_names::TEST_ADD_VIA_FUNC);

        $t->subheader($ts . 'triple prepared write');
        $test_name = 'add triple ' . triple_names::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_trp->triple_add_by_func($msg), true);

        $t->subheader($ts . 'sandbox for ' . triple_names::SYSTEM_TEST_ADD);
        //$t->assert_write_link($t_trp->triple_filled_add(), triples::TN_ADD);


        // create the related objects for link objects
        $wrd_from = $t_db->test_word($msg, word_names::TEST_RENAMED);
        $wrd_to = $t_db->test_word($msg, word_names::TEST_PARENT);

        // remove any remaining db entries from previous tests
        $trp = $t_db->test_triple($msg, word_names::TEST_RENAMED, verbs::IS, word_names::TEST_PARENT);
        $trp_del = new triple($t->usr1);
        $trp_del->load_by_id($trp->id(), $msg);
        $trp_del->del($msg);
        $trp_del = new triple($t->usr2);
        $cln_msg = new user_message($t->usr2); // not reported, because the second user may have no leftover to remove
        $trp_del->load_by_id($trp->id(), $cln_msg);
        $trp_del->del($cln_msg);

        $trp = $t_db->test_triple($msg, word_names::TEST_RENAMED, verbs::IS, word_names::TEST_PARENT);
        $trp->set_user($t->usr1);
        $trp->include();
        $trp->save($msg);

        $t->subheader("... and also testing the user log link class (classes/user_log_link.php)");
        $test_name = 'check the correct logging of adding a triple  "' . word_names::TEST_RENAMED . '" ' . verbs::IS . ' "' . word_names::TEST_PARENT . '" based on the id of the added test word, verb and the parent test word';
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE, $msg);
        $log->new_from_id = $wrd_from->id();
        $log->new_link_id = $vrb_is_id;
        $log->new_to_id = $wrd_to->id();
        $result = $log->dsp_last($msg, true);
        $target = users::SYSTEM_TEST_NAME . ' linked ' . word_names::TEST_RENAMED . ' to ' . word_names::TEST_PARENT;
        $t->assert($test_name, $result, $target);

        $test_name = '... check if the link is shown correctly';
        $trp = new triple($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $trp->set_name('');
        $result = $trp->name_generated();
        $target = word_names::TEST_RENAMED . ' (' . word_names::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);
        $result = $trp->name();
        // $target = triples::TN_ADD;
        $target = word_names::TEST_RENAMED . ' (' . word_names::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);
        $test_name = ' ... the second user "' . $t->usr2->name . '" can save the link';
        // an own object of the second user, because the object above has the name cleared for the name test
        $trp_usr2 = new triple($t->usr2);
        $trp_usr2->load_by_link_id($wrd_from->id(), $msg2, $vrb_is_id, $wrd_to->id());
        $trp_usr2->include();
        $trp_usr2->save($msg2);
        $t->assert_msg($test_name, $msg2);
        $test_name = ' ... check if the link is shown correctly also for the second user "' . $t->usr2->name . '"';
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id( $wrd_from->id(), $msg2, $vrb_is_id, $wrd_to->id() );
        $result = $lnk2->name();
        $target = word_names::TEST_RENAMED . ' (' . word_names::TEST_PARENT . ')';
        $t->assert($test_name, $result, $target);

        $t->subheader(" ... check if the value update has been triggered");

        $test_name = 'triple the second user "' . $t->usr2->name . '" deletes it';
        $msg2->reset();
        $trp = new triple($t->usr2);
        $trp->load_by_link_id( $wrd_from->id(), $msg2, $vrb_is_id, $wrd_to->id() );
        $trp->del($msg2);
        $result = $msg2->get_last_message();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        $test_name = 'check if the removal of the link "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for the second user "' . $t->usr2->name . '" has been logged';
        $msg2->reset();
        $log = new change_link($t->usr2);
        $log->set_table(change_tables::TRIPLE, $msg);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last($msg, true);
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked ' . word_names::TEST_RENAMED . ' from ' . word_names::TEST_PARENT;
        $t->assert($test_name, $result, $target);


        // ... check if the link is really not used any more for the second user
        $lnk2 = new triple($t->usr2);
        $lnk2->load_by_link_id( $wrd_from->id(), $msg2, $vrb_is_id, $wrd_to->id() );
        $result = $lnk2->name();
        $target = '';
        $t->assert('triple->load "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr2->name . '" not any more', $result, $target, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the value update for the second user has been triggered

        // ... check all places where the word maybe used ...

        // ... check if the link is still used for the first user
        $trp = new triple($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $trp->set_name('');
        $result = $trp->name_generated();
        $target = word_names::TEST_RENAMED . ' (' . word_names::TEST_PARENT . ')';
        $t->assert('triple->load of "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" is still used for user "' . $t->usr1->name . '"', $result, $target, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // ... check if the values for the first user are still the same

        // if the first user also removes the link, both records should be deleted
        $msg->reset();
        $trp = new triple($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $trp->del($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('triple->del "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE, $msg);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last($msg, true);
        // the last unlink is still the one of the second user, because the delete of the first user
        // after the exclusion by the second user writes no unlink entry of its own
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked ' . word_names::TEST_RENAMED . ' from ' . word_names::TEST_PARENT;
        $t->assert('triple->del logged for "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $result, $target);

        // check if the formula is not used any more for both users
        $trp = new triple($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $result = $trp->name();
        $target = '';
        $t->assert('triple->load of "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" for user "' . $t->usr1->name . '" not used any more', $result, $target);

        // check if the name of a triple can be changed
        $trp = $t_db->test_triple($msg, word_names::TEST_RENAMED, verbs::IS, word_names::TEST_PARENT);
        $trp->set_name(triple_names::SYSTEM_TEST_ADD);
        $trp->save($msg);
        $result = $msg->get_last_message();
        $t->assert('triple->save name to ' . triple_names::SYSTEM_TEST_ADD, $result);

        // ... and if the name check if the name of a triple can be changed
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $msg);
        $t->assert('triple load changed name of ' . triple_names::SYSTEM_TEST_ADD, $trp->name(), triple_names::SYSTEM_TEST_ADD);

        // the code id connects the triple to program code, so it must be in the database
        $msg_code = new user_message($t->usr1);
        $trp->set_code_id(triple_names::SYSTEM_TEST_ADD_CODE_ID, $msg_code);
        $trp->save($msg_code);
        $trp_code = new triple($t->usr1);
        $trp_code->load_by_name(triple_names::SYSTEM_TEST_ADD, $msg);
        $t->assert('triple->save stores the code id', $trp_code->get_code_id(), triple_names::SYSTEM_TEST_ADD_CODE_ID);
        // negative: a save without a code id, like an import file that repeats the triple, keeps it
        $trp_code->set_code_id_db(null);
        $trp_code->save($msg_code);
        $trp_code = new triple($t->usr1);
        $trp_code->load_by_name(triple_names::SYSTEM_TEST_ADD, $msg);
        $t->assert('triple->save without a code id keeps it', $trp_code->get_code_id(), triple_names::SYSTEM_TEST_ADD_CODE_ID);

        // check the correct logging
        $log = new change_link($t->usr1);
        $log->set_table(change_tables::TRIPLE, $msg);
        $log->old_from_id = $wrd_from->id();
        $log->old_link_id = $vrb_is_id;
        $log->old_to_id = $wrd_to->id();
        $result = $log->dsp_last($msg, true);
        // the rename and the code id are no link change, so the last unlink is still the one of the second user
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' unlinked ' . word_names::TEST_RENAMED . ' from ' . word_names::TEST_PARENT;
        $t->assert('triple->del logged for "' . $wrd_from->name() . '" ' . verbs::IS . ' "' . $wrd_to->name() . '" and user "' . $t->usr1->name . '"', $result, $target);

        // check that even after renaming the triple no word with the standard name of the triple can be added
        $wrd = new word($t->usr1);
        $wrd->set_name(triple_names::SYSTEM_TEST_ADD_AUTO);
        $msg = new user_message($t->usr1);
        $wrd->save($msg);
        $result = $msg->text();
        $target = 'A word with the name "' . triple_names::SYSTEM_TEST_ADD_AUTO . '" already exists. Please use another word name.';
        $t->assert('word cannot have a standard triple name', $result, $target);

        // ... and no verb either
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(triple_names::SYSTEM_TEST_ADD_AUTO);
        $msg = new user_message($t->usr1);
        $vrb->save($msg);
        $result = $msg->text();
        // the message names the class of the term that already uses the name, like for the word above
        $target = 'A triple with the name "' . triple_names::SYSTEM_TEST_ADD_AUTO . '" already exists. Please use another triple name.';
        $t->assert('verb cannot have a standard triple name', $result, $target);

        // ... and no formula either
        $frm = new formula($t->usr1);
        $frm->set_name(triple_names::SYSTEM_TEST_ADD_AUTO);
        $msg = new user_message($t->usr1);
        $frm->save($msg);
        $result = $msg->text();
        $target = 'A ' . $lib->class_to_name(formula::class) . ' with the name "System Test Triple" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.';
        $t->assert('word cannot have a standard triple name', $result, $target);

        $test_name = 'triple clean up tests';
        $trp = new triple($t->usr1);
        $msg = new user_message($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $trp->del($msg);
        $result = $msg->text();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        $trp = new triple($t->usr2);
        $msg2->reset(true);
        $trp->load_by_link_id( $wrd_from->id(), $msg2, $vrb_is_id, $wrd_to->id() );
        $trp->del($msg2);
        $result = $msg2->text();
        $target = '';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        $t->subheader($ts . 'cleanup');
        $trp = new triple($t->usr1);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $msg_del = $trp->del($msg);
        $trp = new triple($t->usr2);
        $trp->load_by_link_id( $wrd_from->id(), $msg, $vrb_is_id, $wrd_to->id() );
        $msg_del = $trp->del($msg);


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

        $this->run_del_used($t);
        $this->run_del_owner_change($t);

        // cleanup - fallback delete
        $t_trp->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg, library::class_to_name(triple::class));

    }

    /**
     * a removed triple that a value still uses is only excluded for the user, so that the value is not lost,
     * and a triple that is not used any more is really deleted
     * public to run it also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_del_used(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);
        $std_msg = new user_message($t->usr2);
        $t->subheader('db write triple delete only if not used');
        $t_db->test_word($msg, word_names::TEST_ADD_VIA_FUNC);
        $t_db->test_word($msg, word_names::TEST_PARENT);
        $trp = $t_db->test_triple($msg, word_names::TEST_ADD_VIA_FUNC, verbs::IS, word_names::TEST_PARENT);
        $val = $t_db->test_value([$trp->name()], self::USED_TRIPLE_NUMBER);

        $test_name = 'remove a triple that a value uses';
        $trp->del($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = '... is only excluded for the user';
        $trp_chk = new triple($t->usr1);
        $removed = ($trp_chk->load_by_id($trp->id(), $chk_msg) == 0 or $trp_chk->is_excluded() === true);
        $t->assert_true($test_name, $removed);
        $test_name = '... but another user still sees the triple';
        $trp_std = new triple($t->usr2);
        $t->assert_greater_zero($test_name, $trp_std->load_by_id($trp->id(), $std_msg));
        $test_name = '... and the value is kept';
        $val_chk = new value($t->usr1);
        $val_chk->load_by_id($val->id(), $chk_msg);
        $t->assert($test_name, $val_chk->number(), self::USED_TRIPLE_NUMBER, test_base::TIMEOUT_LIMIT_DB);

        // negative: without the value the triple is not used any more
        $val->del($msg);
        $msg->reset();
        $test_name = 'remove the triple that is not used any more';
        $trp->del($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = '... is really deleted';
        $trp_std = new triple($t->usr2);
        $t->assert($test_name, $trp_std->load_by_id($trp->id(), $std_msg), 0, test_base::TIMEOUT_LIMIT_DB);
        $t->cleanup_change_log_of_deleted([$val, $trp]);

        $test_name = 'a triple that only another triple uses is used by data';
        $trp = $t_db->test_triple($msg, word_names::TEST_ADD_VIA_FUNC, verbs::IS, word_names::TEST_PARENT);
        $trp_part = $t_db->test_triple($msg, $trp->name(), verbs::PART_NAME, word_names::TEST_PARENT);
        $is_used = $trp->used_by_data($chk_msg);
        $t->assert_true($test_name, $is_used);
        // negative: without the other triple nothing uses the triple any more
        $test_name = '... but not any more without the other triple';
        $trp_part->del($msg);
        $is_used = $trp->used_by_data($chk_msg);
        $t->assert_false($test_name, $is_used);
        $test_name = 'remove the triple after the other triple';
        $trp->del($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $t->cleanup_change_log_of_deleted([$trp_part, $trp]);
    }

    /**
     * the owner removes a triple that another user has changed, so the other user becomes the owner
     * and set_owner writes the standard row with the linked objects that it has reloaded
     * public to run it also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_del_owner_change(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);
        $usr2_msg = new user_message($t->usr2);
        $t->subheader('db write triple delete by the owner');

        $test_name = 'the new owner is written to the standard row';
        $trp = $t_db->test_triple($msg, word_names::TEST_ADD_VIA_FUNC, verbs::IS, word_names::TEST_PARENT);
        $trp_usr2 = new triple($t->usr2);
        $trp_usr2->load_by_id($trp->id(), $usr2_msg);
        $trp_usr2->description = self::OWNER_CHANGE_COM;
        $trp_usr2->save($usr2_msg);
        $trp_owned = clone $trp;
        $trp_owned->set_owner($t->usr2->id, $usr2_msg);
        $trp_std = new triple($t->usr1);
        $trp_std->load_standard($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_std->owner_id(), $t->usr2->id);
        $test_name = '... and the first owner again';
        $trp_owned->set_owner($t->usr1->id, $usr2_msg);
        $trp_std->load_standard($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_std->owner_id(), $t->usr1->id);
        $test_name = 'the owner removes a triple that another user has changed';
        $trp->del($msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $test_name = '... is removed for the owner';
        $trp_chk = new triple($t->usr1);
        $removed = ($trp_chk->load_by_id($trp->id(), $chk_msg) == 0 or $trp_chk->is_excluded() === true);
        $t->assert_true($test_name, $removed);

        // the second user removes the triple, if the owner change has kept it for them
        $trp_left = new triple($t->usr2);
        if ($trp_left->load_by_id($trp->id(), $usr2_msg) != 0) {
            $trp_left->del($usr2_msg);
        }
        $t->cleanup_change_log_of_deleted([$trp]);
    }

    function create_test_triples(all_tests|a_selected_test $t): void
    {

        // start the test section (ts)
        $ts = 'db create test create_test_triples ';
        $t->header($ts);

        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);

        // activate the excluded objects to check the setup
        $trp = new triple($t->usr2);
        $trp->load_by_name(triple_names::SYSTEM_TEST_EXCLUDED, $msg);
        if ($trp->id() != 0) {
            $trp->excluded = false;
            $trp->save($msg);
        }

        // check if the standard samples for triples still exist and if not, create the samples
        $t_db->test_triple($msg, word_names::ZH, verbs::IS, word_names::CANTON, triple_names::CANTON_ZURICH, triple_names::CANTON_ZURICH);
        $t_db->test_triple($msg, word_names::ZH, verbs::IS, word_names::CITY, triple_names::CITY_ZH, triple_names::CITY_ZH);
        $t_db->test_triple($msg, word_names::ZH, verbs::IS, word_names::COMPANY, triple_names::COMPANY_ZURICH, triple_names::COMPANY_ZURICH);
        $t_db->test_triple($msg, triple_names::CANTON_ZURICH, verbs::PART_NAME, words::CH);
        $t_db->test_triple($msg, triple_names::CITY_ZH, verbs::PART_NAME, triple_names::CANTON_ZURICH);
        // TODO Prio 1 activate
        //$t_db->test_triple($msg, triple_names::COMPANY_ZURICH, verbs::PART_NAME, triple_names::CITY_ZH, triple_names::SYSTEM_TEST_EXCLUDED, triple_names::SYSTEM_TEST_EXCLUDED);

        $t_db->test_triple($msg, word_names::ABB, verbs::IS, word_names::COMPANY, triple_names::COMPANY_ABB);
        // TODO Prio 1 check why it is possible to create a triple with the same name as a word
        //$t->test_triple(w$msg, ords::TN_VESTAS, verbs::IS_A, TEST_WORD, words::TN_VESTAS, words::TN_VESTAS);
        $t_db->test_triple($msg, word_names::VESTAS, verbs::IS, word_names::COMPANY, triple_names::COMPANY_VESTAS, triple_names::COMPANY_VESTAS);
        $t_db->test_triple($msg, word_names::YEAR_2014, verbs::FOLLOW, word_names::YEAR_2013, triple_names::YEAR_2013_FOLLOW);

        $t->subheader($ts . 'base phrases');
        $t_db->test_phrase(triple_names::COMPANY_ZURICH, $msg);

        // exclude some to test the handling of exclude objects
        // TODO Prio 1 activate
        //$trp = new triple($t->usr2);
        //$trp->load_by_name(triple_names::SYSTEM_TEST_EXCLUDED);
        //$trp->set_excluded(true);
        //$trp->save($usr_msg);
    }

    function create_base_times(test_cleanup|a_selected_test $t): void
    {
        // start the test section (ts)
        $ts = 'db create test words ';
        $t->header($ts);

        $this->create_time_setup($t);
    }

    // TODO Prio 0 review
    /**
     * testing functions to create the main time value
     * @param test_cleanup $t
     * @return string
     */
    function create_time_setup(test_cleanup $t): string
    {
        global $db_con;

        $cfg = new config();
        $t_db = new test_db_load($t);
        $msg = new user_message();
        $msg_sys = new user_message($t->usr_system);
        $result = '';
        $this_year = intval(date('Y'));
        $prev_year = '';
        // a missing test years entry is created with the default value by the system user
        $sys_msg = new user_message(user::system());
        $test_years = intval($cfg->get_db(config::TEST_YEARS, $db_con, $sys_msg));
        if ($test_years == '') {
            log_warning('Configuration of test years is missing', 'test_base->zu_test_time_setup');
        } else {
            $start_year = $this_year - $test_years;
            $end_year = $this_year + $test_years;
            for ($year = $start_year; $year <= $end_year; $year++) {
                $this_year = $year;
                // here the system user is used, because the year are not test words that are removed after the test
                $t_db->test_word($msg_sys, strval($this_year));
                $wrd_lnk = $t_db->test_triple($msg, words::YEAR_CAP, verbs::IS, $this_year);
                $result = $wrd_lnk->name();
                if ($prev_year <> '') {
                    $t_db->test_triple($msg, $prev_year, verbs::FOLLOW, $this_year);
                }
                $prev_year = $this_year;
            }
        }

        return $result;
    }
}