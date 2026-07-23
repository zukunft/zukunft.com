<?php

/*

    test/php/unit_write_workflow/triple_write_url_tests.php - persist the url based triple user workflows
    ------------------------------------------------------

    runs the same add, change and delete triple workflows as triple_url_tests, but with the do_it flag
    set to true so each confirmed step is actually written to the database; the steps snapshot into
    src/test/resources/web/html/workflow_write/ (see docs/llm/testing.md)


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_write_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UNIT_WORKFLOW . 'triple_url_tests.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'triple.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_db_load.php';
include_once test_paths::CREATE . 'test_verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\unit_workflow\triple_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_write_url_tests extends triple_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'triple url write->', 'url write triple ');

        // remove any test triple left over from a previous run (including the user sandbox rows) so the
        // add workflow starts from a clean state; an add to an already existing triple keeps its old
        // description, which would fail the 'has written the triple' check
        $this->cleanup_test_triples($t);

        // create the from and to test words of the workflow test triple so the confirmed add can link
        // them; only test words are used so the workflows never change seeded data (e.g. the usage of
        // the math words)
        $t_db = new test_db_load($t);
        $t_db->test_word(word_names::TEST_ADD);
        $t_db->test_word(word_names::TEST_ADD_TO);

        // run the same three workflows as triple_url_tests but with do_it true so each confirmed step is
        // persisted: add creates the test triple, change modifies it and del removes it again - the add
        // must run first because the change and delete workflows load the triple it created by name
        $this->add_triple_workflow(workflows::WF_ADD_TRIPLE_NBR, true);
        $this->change_triple_workflow(workflows::WF_CHANGE_TRIPLE_NBR, true);
        $this->del_triple_workflow(workflows::WF_DEL_TRIPLE_NBR, true);

        // a rename by a user that does not own the triple: the name-only change must route to the
        // user overlay (not delete and recreate the row), so the triple id and its related values,
        // formulas and links stay stable (see is_id_key_updated and the unit asserts in triple_tests)
        $this->rename_triple_by_other_user($t);


        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $this->cleanup_test_triples($t);

    }

    /**
     * a rename of a triple by a user who does not own it must keep the database id and with it all
     * related values, formulas and links: for a named link object only a change of the link fields
     * (from, verb, to) is a row identity change, a name-only change is written to the name of the
     * user overlay row (user_triples has its own name columns, see is_id_key_updated and sandbox::save);
     * the contract checked here is that the triple id is unchanged, the renaming user sees the new
     * name and the original description, the owner keeps the original name, and a rename back to the
     * standard name removes the user overlay row again (like the word rename test)
     *
     * @param test_cleanup $t the test environment
     */
    private function rename_triple_by_other_user(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'other user rename');

        // start from a clean state and make sure the from and to test words of the link exist
        $this->cleanup_test_triples($t);
        $t_db = new test_db_load($t);
        $t_db->test_word(word_names::TEST_ADD);
        $t_db->test_word(word_names::TEST_ADD_TO);

        // usr1 creates and owns the base triple linking the two test words with a given name that
        // differs from the generated '<from> <verb> <to>' name, so the rename changes a real name
        $owner = new user();
        $owner->load_by_name(users::SYSTEM_TEST_NAME);
        $from = new word($owner);
        $from->load_by_name(word_names::TEST_ADD);
        $to = new word($owner);
        $to->load_by_name(word_names::TEST_ADD_TO);
        $base = new triple($owner);
        $base->set_from($from->phrase());
        $base->set_verb(test_verbs::verb_part());
        $base->set_to($to->phrase());
        $base->set_name(triple_names::SYSTEM_TEST_ADD);
        $base->set_description(triple_names::SYSTEM_TEST_ADD_COM);
        $owner_msg = new user_message($owner);
        $base->save($owner_msg);
        $test_name = 'the base triple is created for the owner';
        $t->assert_msg($test_name, $owner_msg);
        $base_id = $base->id();
        $test_name = 'the base triple has a database id';
        $t->assert_true($test_name, $base_id > 0);

        // usr2 renames the triple via the same frontend bridge that the confirmed edit uses;
        // the triple is loaded fresh so the ui object carries the from, verb and to of the link
        // (a triple rename request keeps the link as the '8'-prefixed baseline, only the name changes)
        $changer = new user();
        $changer->load_by_id($t->usr2->id());
        $trp_load = new triple($changer);
        $trp_load->load_by_id($base_id);
        // TODO Prio 2 create $t->usr_ui2 and use it
        $msg_ui = new user_message_ui();
        $changer_ui = new user_ui();
        $changer_ui->set_from_json($changer->api_json(), $msg_ui);
        $msg_ui->usr = $changer_ui;
        $renamed = triple_names::SYSTEM_TEST_ADD . test_cleanup::EXT_RENAME;
        $trp_ui = new triple_ui($trp_load->api_json());
        $trp_ui->set_name($renamed);
        $upd_msg = $trp_ui->update($changer_ui, $msg_ui);
        $test_name = 'the rename by user 2 is saved';
        $t->assert_msg($test_name, $upd_msg);

        // the rename of a link object never changes the database id, because the id carries all
        // relations: the values, formulas and links of the triple would be lost with a new id
        $test_name = 'the triple id is unchanged by the rename';
        $t->assert($test_name, $trp_ui->id(), $base_id);

        // user 2 sees the renamed triple at the unchanged id
        $test_name = 'user 2 sees the renamed triple';
        $trp_chk = new triple($changer);
        $trp_chk->load_by_id($base_id);
        $t->assert($test_name, $trp_chk->name(), $renamed);

        // the rename request carries only the changed name, so the description of the original
        // triple must survive the rename (see the fill on a key update in sandbox::save)
        $test_name = 'the description survives the rename';
        $t->assert($test_name, $trp_chk->get_description(), triple_names::SYSTEM_TEST_ADD_COM);

        // the owner keeps the original triple name under the original id
        $test_name = 'the owner keeps the original triple name';
        $trp_owner = new triple($owner);
        $trp_owner->load_by_id($base_id);
        $t->assert($test_name, $trp_owner->name(), triple_names::SYSTEM_TEST_ADD);

        // renaming back to the standard name must not report the name as already used (the similar
        // object found by the duplicate check is the same database row) and must remove the now
        // empty user overlay row
        $trp_back = new triple_ui($trp_load->api_json());
        $trp_back->set_name(triple_names::SYSTEM_TEST_ADD);
        $back_msg = $trp_back->update($changer_ui, $msg_ui);
        $test_name = 'the rename back to the standard name is saved without a duplicate message';
        $t->assert_msg($test_name, $back_msg);
        $test_name = 'the rename back removes the user overlay row';
        $trp_undo_chk = new triple($changer);
        $trp_undo_chk->load_by_id($base_id);
        $t->assert_false($test_name, $trp_undo_chk->has_usr_cfg());
        $test_name = 'after the rename back user 2 sees the standard name again';
        $t->assert($test_name, $trp_undo_chk->name(), triple_names::SYSTEM_TEST_ADD);

        // remove the created rows (the cleanup also covers the renamed variant of the test name) via url
        // the owner deletes the base triple through the same frontend bridge that the confirmed delete
        // uses, so the url based delete path is covered end to end like the rename above; usr2 has no
        // overlay left after the rename back, so the sole owner delete removes the row completely
        $owner_ui = new user_ui();
        $owner_ui->set_from_json($owner->api_json(), $msg_ui);
        $trp_owner_load = new triple($owner);
        $trp_owner_load->load_by_id($base_id);
        $trp_del = new triple_ui($trp_owner_load->api_json());
        $del_msg = $trp_del->del($owner_ui, $msg_ui);
        $test_name = 'the owner deletes the triple via the frontend bridge';
        $t->assert_msg($test_name, $del_msg);
        $test_name = 'the triple is removed after the url delete';
        $trp_gone = new triple($owner);
        $trp_gone->load_by_id($base_id);
        $t->assert_true($test_name, $trp_gone->id() <= 0);

        // check that all have been removed
        $this->cleanup_test_triples($t);
    }

    /**
     * TODO Prio 2 review
     * delete the workflow test triples and their from and to test words (including the user sandbox
     * rows) from the database so a run is not affected by leftovers of a previous run; the triples are
     * removed first so the words are no longer linked when they are deleted
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_triples(test_cleanup $t): void
    {
        $trp = new triple($t->usr1);
        foreach (triple_names::TEST_TRIPLES as $trp_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows including the usr1 owned base
            // triple (the workflows add it with the usr1 message user, see url_test_base::init); the
            // system user cleanup stays to also remove a system owned row left over from a run
            // before the usr1 message user - otherwise it survives between runs and a later add
            // resurrects it with its old (system authored) change log entries
            $t->write_named_cleanup($trp, $trp_name);
            $t->write_named_cleanup_one($trp, $t->usr_system, $trp_name);
        }
        $wrd = new word($t->usr1);
        foreach ([word_names::TEST_ADD, word_names::TEST_ADD_TO] as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}
