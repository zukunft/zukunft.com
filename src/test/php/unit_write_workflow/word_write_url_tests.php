<?php

/*

    test/php/unit_write_workflow/word_write_url_tests.php - persist the url based word user workflows
    ----------------------------------------------------

    runs the same add, change and delete word workflows as word_url_tests, but with the do_it flag set
    to true so each confirmed step is actually written to the database; the steps snapshot into
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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once paths::MODEL_WORD . 'word.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\unit_workflow\word_url_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_write_url_tests extends word_url_tests
{

    function run(test_cleanup $t): void
    {

        // load the shared frontend run state and print the section header
        $this->init($t, 'word url write->', 'url write word ');

        // remove any test word left over from a previous run (including the user sandbox rows) so the
        // add workflow starts from a clean state; an add to an already existing word keeps its old
        // description, which would fail the 'has written the word' check
        $this->cleanup_test_words($t);

        // run the same three workflows as word_url_tests but with do_it true so each confirmed step is
        // persisted: add creates the test word, change modifies it, del removes it again - the add must
        // run first because the change and delete workflows load the word it created by name
        $this->add_word_workflow(workflows::WF_ADD_WORD_NBR, true);
        $this->change_word_workflow(workflows::WF_CHANGE_WORD_NBR, true);
        $this->del_word_workflow(workflows::WF_DEL_WORD_NBR, true);

        // a change by a user that does not own the word: unlike change_word_workflow (usr1 changes the
        // system owned word) this is the linear confirm-write without the back / cancel excursions, and
        // it checks the per-user sandbox side effect - the changing user gets the change, the owner does not
        $this->change_word_by_other_user($t);


        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $this->cleanup_test_words($t);

    }

    /**
     * check that a change by a user who does not own the word creates a per-user sandbox overlay:
     * usr1 owns the base word, usr2 is another user. after usr2 changes the description the usr2
     * uses_sandbox flag is set, a usr2 user sandbox row exists and usr2 sees the changed description,
     * while the owner usr1 still sees the unchanged original. this is the persisted side effect of the
     * change_word confirm step (url_to_action -> sandbox::save) reached directly, so no snapshot fixture
     * is needed; the base word is created and removed by this test, so it is independent of the other
     * workflows (see docs/llm/testing.md for the sandbox model)
     *
     * @param test_cleanup $t the test environment
     */
    private function change_word_by_other_user(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'other user change');

        // start from a clean state so the base has the known original description and no overlay is left
        $this->cleanup_test_words($t);

        // usr1 creates and owns the base word with the original description
        $owner = $t->usr1;
        $base = test_words::add_owned($owner, word_names::TEST_ADD_COM);
        $owner_msg = new user_message($owner);
        $base->save($owner_msg);
        $test_name = 'the base word is created for the owner';
        $t->assert_msg($test_name, $owner_msg);

        // usr2 (another user, not the owner) changes the description; because usr2 does not own the base
        // row the change is routed to a usr2 user sandbox overlay (the same path url_to_action -> save uses).
        // load the changer fresh so its in-memory profile matches the stored one: the shared $t->usr2 object
        // can carry a profile that an earlier test left different from the database, which would make the
        // uses_sandbox user update (set_uses_sandbox -> save_user) misread the flag flip as a profile
        // escalation attempt and block the whole word save (see user::enforce_profile_privilege)
        $changer = new user();
        $changer->load_by_id($t->usr2->id());
        $wrd = new word($changer);
        $wrd->load_by_name(word_names::TEST_ADD);
        // the changer must actually see the base standard row before changing it; if the load misses
        // (e.g. a left over excluded row of the reserved word from the preceding add/change/del workflows)
        // the following save would take the add path and fail on the reserved-name conflict instead of
        // creating the overlay, so check the load first to keep the failure unambiguous
        $test_name = 'the other user can load the base word before the change';
        $t->assert_true($test_name, $wrd->id() > 0);
        $wrd->set_description(word_names::TEST_CHANGE_COM);
        $change_msg = new user_message($changer);
        $wrd->save($change_msg);
        $test_name = 'the other user change is saved';
        $t->assert_msg($test_name, $change_msg);

        // the changing user now uses the sandbox (the flag is set and persisted when the overlay is created)
        $test_name = 'the changing user now uses the sandbox';
        $usr_chk = new user();
        $usr_chk->load_by_id($changer->id());
        $t->assert_true($test_name, $usr_chk->uses_sandbox);

        // a user sandbox row exists for the changing user and that user sees the changed description
        $wrd_changer = new word($changer);
        $wrd_changer->load_by_name(word_names::TEST_ADD);
        $test_name = 'a user sandbox row is created for the changing user';
        $t->assert_true($test_name, $wrd_changer->has_usr_cfg());
        $test_name = 'the changing user sees the changed description';
        $t->assert($test_name, $wrd_changer->get_description(), word_names::TEST_CHANGE_COM);

        // the owner still sees the unchanged original description and has no overlay of his own
        $wrd_owner = new word($owner);
        $wrd_owner->load_by_name(word_names::TEST_ADD);
        $test_name = 'the owner still sees the unchanged description';
        $t->assert($test_name, $wrd_owner->get_description(), word_names::TEST_ADD_COM);
        $test_name = 'the owner has no user sandbox overlay';
        $t->assert_false($test_name, $wrd_owner->has_usr_cfg());

        // undo the changer overlay by setting the description back to the owner's value: this removes the
        // usr2 user sandbox row (no_diff), so the base word is then owned solely by usr1 with no other
        // user using it. without this the owner delete in the cleanup below would not hard delete the word
        // but transfer the ownership to the changer and only exclude the row (see sandbox::del), which
        // leaves the reserved word in the database and breaks the add / delete word workflow on the next run
        $wrd_undo = new word($changer);
        $wrd_undo->load_by_name(word_names::TEST_ADD);
        $wrd_undo->set_description(word_names::TEST_ADD_COM);
        $undo_msg = new user_message($changer);
        $wrd_undo->save($undo_msg);
        $wrd_undo_chk = new word($changer);
        $wrd_undo_chk->load_by_name(word_names::TEST_ADD);
        $test_name = 'the changer overlay is removed again';
        $t->assert_false($test_name, $wrd_undo_chk->has_usr_cfg());

        // remove the created rows so a re-run starts clean and the following cleanup finds nothing
        $this->cleanup_test_words($t);
    }

    /**
     * TODO Prio 2 review
     * delete the workflow test words (and their user sandbox rows) from the database so a run is not
     * affected by leftovers of a previous run
     *
     * @param test_cleanup $t the test environment
     */
    private function cleanup_test_words(test_cleanup $t): void
    {
        $wrd = new word($t->usr1);
        foreach (word_names::TEST_WORDS as $wrd_name) {
            // write_named_cleanup removes the usr1 / usr2 sandbox rows; the reserved test word is owned
            // by the system user (it is added with the system message user), so remove that row too -
            // otherwise it survives between runs and a later add keeps its old (changed) description
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}