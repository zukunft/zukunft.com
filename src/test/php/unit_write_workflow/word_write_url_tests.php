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
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::UNIT_WORKFLOW . 'word_url_tests.php';
include_once html_paths::HELPER . 'user_request.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'word.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\helper\user_request;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
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

        // write: url_to_action executes a confirmed create and delete against the database;
        // the read (url_to_html) and routing-only (do_it false) tests are in word_url_tests
        $this->url_to_action_write_tests($t);

        // run the same three workflows as word_url_tests but with do_it true so each confirmed step is
        // persisted: add creates the test word, change modifies it, del removes it again - the add must
        // run first because the change and delete workflows load the word it created by name
        $this->add_word_workflow(workflows::WF_ADD_WORD_NBR, true);
        $this->change_word_workflow(workflows::WF_CHANGE_WORD_NBR, true);
        $this->del_word_workflow(workflows::WF_DEL_WORD_NBR, true);

        // a change by a user that does not own the word: unlike change_word_workflow (usr1 changes its
        // own word) this creates a base word owned by usr1 and lets usr2 change it in a linear
        // confirm-write without the back / cancel excursions, and it checks the per-user sandbox side
        // effect - the changing user (usr2) gets the change as an overlay, the owner (usr1) does not
        $this->change_word_by_other_user($t);

        // the same change wrapped in real logins: the first save of a user flips uses_sandbox, which
        // once nulled the stored password hash (a user rebuilt from the api json carried no hash),
        // so the re-login after the change is the regression check for that data loss
        $this->change_word_by_other_user_with_login($t);

        // a rename by a user that does not own the word: the view shown after the rename must
        // never be empty, so the ui object must end up with an id that resolves to the renamed word
        $this->rename_word_by_other_user($t);

        // a non-owner (usr2) fills almost all sandbox fields of the word owned by usr1 in one edit
        // round, snapshotted as a workflow; the final page snapshot shows the change log entries of
        // the confirmed change (the read-only twin renders the same steps in word_url_tests)
        $this->change_word_all_sandbox_fields_write($t);

        // the login - logout - login flow: the back target of the original page survives the
        // login of user 1, the logout and the login of user 2
        $this->login_logout_login($t);


        $t->subheader($this->ts . 'cleanup');

        // cleanup - fallback delete in case a workflow did not persist as expected
        $this->cleanup_test_words($t);

    }

    /**
     * the write tests of url_to_action: a create or delete request is executed by url_to_action
     * (not url_to_html, which only renders), so use the combined execute and render call and
     * check the database result; never use read test objects e.g. like math in this function
     *
     * @param test_cleanup $t the test environment
     */
    private function url_to_action_write_tests(test_cleanup $t): void
    {
        $msg = new user_message();
        $t->subheader($this->ts . 'url_to_action & next url');

        $req = new user_request($t->usr1, $this->msg, $this->ui->dto, true, true);

        $test_name = 'a confirmed add url writes the word to the database';
        $url_arr = [];
        $url_arr[url_var::MASK] = views::WORD_ADD_ID;
        $url_arr[url_var::NAME] = word_names::TEST_ADD;
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRMED;
        $this->ui->execute_and_next($url_arr, $req);
        $wrd_chk = new word($t->usr1);
        $t->assert_true($test_name, $wrd_chk->load_by_name(word_names::TEST_ADD, $msg) > 0);

        $test_name = '... so it can be deleted';
        $url_arr[url_var::ACTION] = url_var::CRUD_DELETE;
        $this->ui->execute_and_next($url_arr, $req);
        $wrd_chk = new word($t->usr1);
        $wrd_chk->load_by_name(word_names::TEST_ADD, $msg);
        // the assert follows a create/delete executed via url and a reload, so a long page timeout is used
        $t->assert($test_name, $wrd_chk->id(), 0, $t::TIMEOUT_LIMIT_PAGE_LONG);

        // the delete of the just added word can leave an excluded row, so clean up again to
        // guarantee the add workflow that follows the same clean start as the cleanup before
        $this->cleanup_test_words($t);
    }

    /**
     * run the change_word_all_sandbox_fields workflow with do_it true: usr1 creates and owns the
     * base word with only the name and the original description, then usr2 - who does not own it -
     * fills almost all sandbox fields via the edit form, confirms and changes the description two
     * more times, so the writes land in a usr2 user sandbox overlay and the final page snapshot
     * shows the change log of all confirmed changes (the step sequence and the overlay checks are
     * in word_url_tests); a third user overwrites the description before the workflow, so the
     * pages rendered for usr2 also show the 'others' tab with that shared overwrite
     *
     * @param test_cleanup $t the test environment
     */
    private function change_word_all_sandbox_fields_write(test_cleanup $t): void
    {
        // start from a clean state so the base word has no leftover overlay of a previous run
        $this->cleanup_test_words($t);

        // usr1 creates and owns the base word with the original description
        $base = test_words::add_owned($t->usr1, word_names::TEST_ADD_COM);
        $owner_msg = new user_message($t->usr1);
        $base->save($owner_msg);
        $test_name = 'the base word for the all sandbox fields change is created';
        $t->assert_msg($test_name, $owner_msg);

        // a third user (the normal test user, loaded fresh like the changer in
        // change_word_by_other_user) overwrites the description, so the workflow pages
        // rendered for usr2 also show the 'others' tab with this shared overwrite
        $other = new user();
        $other->load_by_id($t->usr_normal->id());
        $wrd_other = new word($other);
        $wrd_other->load_by_name(word_names::TEST_ADD);
        $wrd_other->set_description(word_names::TEST_OTHER_COM);
        $other_msg = new user_message($other);
        $wrd_other->save($other_msg);
        $test_name = 'the other user overwrite for the others tab is created';
        $t->assert_msg($test_name, $other_msg);

        $this->change_word_all_sandbox_fields_workflow(workflows::WF_CHANGE_WORD_ALL_SANDBOX_FIELDS_NBR, true);

        // the page data of the changing user must list the other user's overwrite
        $test_name = 'the others tab data lists the description overwrite of the other user';
        $wrd_chk = new word($t->usr2);
        $wrd_chk->load_by_name(word_names::TEST_ADD);
        $oth_ovr = $wrd_chk->other_overwrites_api_array(new user_message($t->usr2));
        $oth_found = false;
        foreach ($oth_ovr as $oth_row) {
            if (($oth_row[json_fields::USER_NAME] ?? '') == users::SYSTEM_TEST_NORMAL_NAME
                and ($oth_row[json_fields::USR_VALUE] ?? '') == word_names::TEST_OTHER_COM) {
                $oth_found = true;
            }
        }
        $t->assert_true($test_name, $oth_found);

        // remove the third user overwrite by setting the description back to the base value,
        // because the shared cleanup only covers usr1 and usr2 (see change_word_by_other_user)
        $wrd_undo = new word($other);
        $wrd_undo->load_by_name(word_names::TEST_ADD);
        $wrd_undo->set_description(word_names::TEST_ADD_COM);
        $undo_msg = new user_message($other);
        $wrd_undo->save($undo_msg);
        $test_name = 'the other user overwrite is removed again';
        $t->assert_msg($test_name, $undo_msg);
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
        $msg = new user_message();
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
        $changer->load_by_id($t->usr2->id(), $msg);
        $wrd = new word($changer);
        $wrd->load_by_name(word_names::TEST_ADD, $msg);
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
        $usr_chk->load_by_id($changer->id(), $msg);
        $t->assert_true($test_name, $usr_chk->uses_sandbox);

        // a user sandbox row exists for the changing user and that user sees the changed description
        $wrd_changer = new word($changer);
        $wrd_changer->load_by_name(word_names::TEST_ADD, $msg);
        $test_name = 'a user sandbox row is created for the changing user';
        $t->assert_true($test_name, $wrd_changer->has_usr_cfg());
        $test_name = 'the changing user sees the changed description';
        $t->assert($test_name, $wrd_changer->get_description(), word_names::TEST_CHANGE_COM);

        // the owner still sees the unchanged original description and has no overlay of his own
        $wrd_owner = new word($owner);
        $wrd_owner->load_by_name(word_names::TEST_ADD, $msg);
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
        $wrd_undo->load_by_name(word_names::TEST_ADD, $msg);
        $wrd_undo->set_description(word_names::TEST_ADD_COM);
        $undo_msg = new user_message($changer);
        $wrd_undo->save($undo_msg);
        $wrd_undo_chk = new word($changer);
        $wrd_undo_chk->load_by_name(word_names::TEST_ADD, $msg);
        $test_name = 'the changer overlay is removed again';
        $t->assert_false($test_name, $wrd_undo_chk->has_usr_cfg());

        // remove the created rows so a re-run starts clean and the following cleanup finds nothing
        $this->cleanup_test_words($t);
    }

    /**
     * the change_word_by_other_user scenario wrapped in real logins and logouts: usr2 logs in with the
     * fixed test password, changes the word owned by usr1 (which flips and persists the usr2
     * uses_sandbox flag), and must still be able to log in again afterwards - the re-login is the
     * regression check that the sandbox flag save never touches the stored password hash (a user
     * rebuilt from the api json carried no hash and the save once nulled it, see user::db_fields_changed);
     * after the re-login usr2 sees the changed description, and the owner login still sees the original
     *
     * @param test_cleanup $t the test environment
     */
    /**
     * a rename by a user who does not own the word must never lead to an empty view afterwards
     * and must never lose the values related to the word: the rename of a named object by a user
     * that cannot change the standard row is written to the name of the user overlay row (see the
     * key update handling in sandbox::save), so the database id stays stable and with it all
     * related values, formulas and links; the contract checked here is that the word id is
     * unchanged, the renaming user sees the new name and the original description, and the owner
     * keeps the original word
     *
     * @param test_cleanup $t the test environment
     */
    private function rename_word_by_other_user(test_cleanup $t): void
    {
        $msg = new user_message();
        $t->subheader($this->ts . 'other user rename');

        // start from a clean state so the base word has the known original name
        $this->cleanup_test_words($t);

        // usr1 creates and owns the base word
        $owner = new user();
        $owner->load_by_name(users::SYSTEM_TEST_NAME, $msg);
        $base = test_words::add_owned($owner, word_names::TEST_ADD_COM);
        $owner_msg = new user_message($owner);
        $base->save($owner_msg);
        $test_name = 'the base word is created for the owner';
        $t->assert_msg($test_name, $owner_msg);
        $base_id = $base->id();
        $test_name = 'the base word has a database id';
        $t->assert_true($test_name, $base_id > 0);

        // usr2 renames the word via the same frontend bridge that the confirmed edit uses
        $changer = new user();
        $changer->load_by_id($t->usr2->id(), $msg);
        $msg_ui = new user_message_ui();
        $changer_ui = new user_ui();
        $changer_ui->set_from_json($changer->api_json(), $msg_ui);
        // the requesting user of the change is carried by the message (docs/llm/state-and-messages.md)
        $msg_ui->usr = $changer_ui;
        $renamed = word_names::TEST_ADD . test_cleanup::EXT_RENAME;
        $wrd_ui = new word_ui();
        $wrd_ui->set_id($base_id);
        $wrd_ui->set_name($renamed);
        $upd_msg = $wrd_ui->update($msg_ui);
        $test_name = 'the rename by user 2 is saved';
        $t->assert_msg($test_name, $upd_msg);

        // the rename of a named object never changes the database id, because the id carries
        // all relations: the values, formulas and links of the word would be lost with a new id
        $test_name = 'the word id is unchanged by the rename';
        $t->assert($test_name, $wrd_ui->id(), $base_id);

        // the id of the ui object must resolve to the renamed word for user 2
        $test_name = 'the ui object id shows the renamed word for user 2';
        $wrd_chk = new word($changer);
        $wrd_chk->load_by_id($wrd_ui->id(), $msg);
        $t->assert($test_name, $wrd_chk->name(), $renamed);

        // the rename request carries only the changed name (the '8'-prefixed edit baseline drops
        // the unchanged fields), so the values of the original word must survive the rename,
        // e.g. the description (see the fill on a key update in sandbox::save)
        $test_name = 'the description survives the rename';
        $t->assert($test_name, $wrd_chk->get_description(), word_names::TEST_ADD_COM);

        // the owner keeps the original word under the original id
        $test_name = 'the owner keeps the original word name';
        $wrd_owner = new word($owner);
        $wrd_owner->load_by_id($base_id, $msg);
        $t->assert($test_name, $wrd_owner->name(), word_names::TEST_ADD);

        // renaming back to the standard name must not report the name as already used, because
        // the similar object found by the duplicate check is the same database row; the name
        // overlay then matches the standard row again, so the user overlay row is removed
        // use $debug for better error detection like this
        //global $debug;
        // $debug = 11;
        $wrd_back = new word_ui();
        $wrd_back->set_id($base_id);
        $wrd_back->set_name(word_names::TEST_ADD);
        $back_msg = $wrd_back->update($msg_ui);
        $test_name = 'the rename back to the standard name is saved without a duplicate message';
        $t->assert_msg($test_name, $back_msg);
        $test_name = 'the rename back removes the user overlay row';
        $wrd_undo_chk = new word($changer);
        $wrd_undo_chk->load_by_id($base_id, $msg);
        $t->assert_false($test_name, $wrd_undo_chk->has_usr_cfg());
        $test_name = 'after the rename back user 2 sees the standard name again';
        $t->assert($test_name, $wrd_undo_chk->name(), word_names::TEST_ADD);

        // remove the created rows (the cleanup also covers the renamed variant of the test name)
        $this->cleanup_test_words($t);
    }

    private function change_word_by_other_user_with_login(test_cleanup $t): void
    {
        $msg = new user_message();
        $t->subheader($this->ts . 'other user change with login');

        // start from a clean word state before the password setup, because the cleanup can
        // trigger a save of the shared $t->usr1 object (word del -> check_sandbox_usage) and a
        // stale in-memory password hash of the shared object would overwrite a just written one
        $this->cleanup_test_words($t);
        $this->logout();

        // make sure both test users can log in with the fixed test password;
        // the users are loaded fresh by name - the same key user::login uses -
        // so the hash is checked and set on the row that the login reads
        $this->ensure_test_password($t, users::SYSTEM_TEST_NAME);
        $this->ensure_test_password($t, users::SYSTEM_TEST_PARTNER_NAME);

        // usr1 creates and owns the base word (the owner login is checked at the end);
        // loaded fresh by name so the word save never writes back stale shared object fields
        $owner = new user();
        $owner->load_by_name(users::SYSTEM_TEST_NAME, $msg);
        $base = test_words::add_owned($owner, word_names::TEST_ADD_COM);
        $owner_msg = new user_message($owner);
        $base->save($owner_msg);
        $test_name = 'the base word is created for the owner';
        $t->assert_msg($test_name, $owner_msg);

        // a wrong password is rejected and reported before any change
        $test_name = 'a wrong password is rejected';
        $fail_usr = new user();
        $fail_msg = new user_message();
        $failed_login = $fail_usr->login(
            users::SYSTEM_TEST_PARTNER_NAME, users::TEST_USER_PASSWORD . ' wrong', $fail_msg);
        // the bcrypt password check is intentionally slow, so it is not charged to the assert timing
        $t->reset_section_timer();
        $t->assert_false($test_name, $failed_login);
        $this->logout();

        // usr2 logs in and changes the word owned by usr1, which creates the usr2 sandbox
        // overlay and flips and persists the usr2 uses_sandbox flag (see sandbox::add_usr_cfg)
        $changer = $this->login_as($t, 'user 2 can log in before the change', users::SYSTEM_TEST_PARTNER_NAME);
        $wrd = new word($changer);
        $wrd->load_by_name(word_names::TEST_ADD, $msg);
        $test_name = 'the logged in user 2 can load the base word';
        $t->assert_true($test_name, $wrd->id() > 0);
        $wrd->set_description(word_names::TEST_CHANGE_COM);
        $change_msg = new user_message($changer);
        $wrd->save($change_msg);
        $test_name = 'the user 2 change is saved';
        $t->assert_msg($test_name, $change_msg);
        $test_name = 'user 2 now uses the sandbox';
        $usr_chk = new user();
        $usr_chk->load_by_id($changer->id(), $msg);
        $t->assert_true($test_name, $usr_chk->uses_sandbox);

        // the sandbox flag save must not touch the stored password hash
        $test_name = 'the password hash of user 2 is unchanged after the sandbox flag update';
        $pw_kept = password_verify(users::TEST_USER_PASSWORD, $usr_chk->get_password() ?? '');
        // the bcrypt password check is intentionally slow, so it is not charged to the assert timing
        $t->reset_section_timer();
        $t->assert_true($test_name, $pw_kept);

        // usr2 logs out and in again, which fails if the password hash has been overwritten,
        // and still sees the changed description
        $this->logout();
        $changer = $this->login_as($t, 'user 2 can log in again after the change', users::SYSTEM_TEST_PARTNER_NAME);
        $wrd_changer = new word($changer);
        $wrd_changer->load_by_name(word_names::TEST_ADD, $msg);
        $test_name = 'user 2 sees the changed description after the re-login';
        $t->assert($test_name, $wrd_changer->get_description(), word_names::TEST_CHANGE_COM);

        // usr2 logs out and the owner logs in and still sees the unchanged original
        $this->logout();
        $owner_in = $this->login_as($t, 'the owner can log in', users::SYSTEM_TEST_NAME);
        $wrd_owner = new word($owner_in);
        $wrd_owner->load_by_name(word_names::TEST_ADD, $msg);
        $test_name = 'the owner sees the unchanged description';
        $t->assert($test_name, $wrd_owner->get_description(), word_names::TEST_ADD_COM);

        // undo the usr2 overlay so the cleanup can hard delete the base word (see change_word_by_other_user)
        $wrd_undo = new word($changer);
        $wrd_undo->load_by_name(word_names::TEST_ADD, $msg);
        $wrd_undo->set_description(word_names::TEST_ADD_COM);
        $undo_msg = new user_message($changer);
        $wrd_undo->save($undo_msg);
        $test_name = 'the user 2 overlay is removed again';
        $t->assert_msg($test_name, $undo_msg);

        // end the login so the following tests run without a logged in session user;
        // the fixed test session token of test_app is never touched by the logins
        // (user::login only sets a token if none is set), so the snapshots stay deterministic
        $this->logout();
        $this->cleanup_test_words($t);
    }

    /**
     * the login - logout - login workflow starting from a normal page: the login link of the
     * page carries the page as the '9'-prefixed back target, the logout action forwards that
     * target to the logout page url (see frontend::action_logout), and the login link of the
     * logout page carries the original page again, so after every step of the flow the user
     * can return to the page the flow started from; ends with the login of a second user
     *
     * @param test_cleanup $t the test environment
     */
    private function login_logout_login(test_cleanup $t): void
    {
        $t->subheader($this->ts . 'login logout login');

        // start without a login and make sure both test users can log in
        $this->logout();
        $this->ensure_test_password($t, users::SYSTEM_TEST_NAME);
        $this->ensure_test_password($t, users::SYSTEM_TEST_PARTNER_NAME);

        // the 'normal page' the flow starts from: the default word view of the math word
        $page_url = [
            url_var::MASK => views::WORD_ID,
            url_var::ID => (string)word_names::MATH_ID,
        ];
        $login_to_page = api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID;

        $test_name = 'the login link of a normal page carries the page as back target';
        $html_page = $this->ui->url_to_html($page_url, new user_message_ui(), $this->ui->dto, true);
        $t->assert_text_contains($test_name, $html_page, $login_to_page, $t::TIMEOUT_LIMIT_PAGE_LONG);

        // user 1 logs in and the page then shows the logout link with the same back target
        $usr1 = $this->login_as($t, 'user 1 can log in from the normal page', users::SYSTEM_TEST_NAME);
        $msg1 = new user_message_ui();
        $usr1_ui = new user_ui();
        $usr1_ui->set_from_json($usr1->api_json(), $msg1);
        $msg1->usr = $usr1_ui;
        $test_name = '... and after the login the logout link keeps the page as back target';
        $html_logged = $this->ui->url_to_html($page_url, $msg1, $this->ui->dto, true);
        $t->assert_text_contains($test_name, $html_logged,
            api::LOGOUT_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID,
            $t::TIMEOUT_LIMIT_PAGE_LONG);

        // the logout action forwards the back target to the logout page url; routing only,
        // because a real session destroy is not possible in the streamed test run (see logout())
        $test_name = 'the logout action keeps the back target of the original page';
        $logout_request = [
            url_var::MASK => views::LOGOUT_ID,
            url_var::BACK . url_var::MASK => (string)views::WORD_ID,
            url_var::BACK . url_var::ID => (string)word_names::MATH_ID,
        ];
        $next_url = $this->ui->url_to_action($logout_request, $usr1, $msg1, $this->ui->dto, false);
        $t->assert($test_name, (string)($next_url[url_var::BACK . url_var::MASK] ?? ''), (string)views::WORD_ID);
        $t->assert($test_name . ' id', (string)($next_url[url_var::BACK . url_var::ID] ?? ''), (string)word_names::MATH_ID);
        $this->logout();

        // the login link of the logout page carries the original page and not the logout page
        $test_name = 'the login link of the logout page returns to the original page';
        $html_logout_page = $this->ui->url_to_html($next_url, new user_message_ui(), $this->ui->dto, true);
        $t->assert_text_contains($test_name, $html_logout_page, $login_to_page, $t::TIMEOUT_LIMIT_PAGE_LONG);
        $t->assert_text_not_contains($test_name, $html_logout_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::LOGOUT_ID);

        // the second user can log in after the logout
        $this->login_as($t, 'user 2 can log in after the logout', users::SYSTEM_TEST_PARTNER_NAME);
        $this->logout();
    }

    /**
     * log in the given user with the fixed test password and report the result
     *
     * @param test_cleanup $t the test environment
     * @param string $test_name the name of the login check shown in the test log
     * @param string $usr_name the name of the user to log in
     * @return user the logged in user e.g. to load the word as this user
     */
    private function login_as(test_cleanup $t, string $test_name, string $usr_name): user
    {
        $login_msg = new user_message();
        $db_usr = new user();
        $db_usr->login($usr_name, users::TEST_USER_PASSWORD, $login_msg);
        // the bcrypt password check is intentionally slow, so it is not charged to the assert timing
        $t->reset_section_timer();
        // assert via the login message so a failed login shows the reason, not just a false
        $t->assert_msg($test_name, $login_msg);
        return $db_usr;
    }

    /**
     * end the login like frontend::action_logout but without destroying the php session:
     * the http streamed test run has already sent output, so a destroyed session could never be
     * started again (headers already sent) and the fixed session token of test_app would be lost;
     * removing the login vars is enough to end the login for the next login check
     * @return void
     */
    private function logout(): void
    {
        unset($_SESSION[url_var::SESSION_LOGGED]);
        unset($_SESSION[url_var::SESSION_USER_ID]);
        unset($_SESSION[url_var::USERNAME_HUMAN]);
    }

    /**
     * make sure the user with the given name can log in with the fixed test password
     * (users::TEST_USER_PASSWORD) without rewriting an already matching hash on every run;
     * the user is loaded fresh by name - the same key user::login uses - so the check and the
     * login always read the same database row, independent of the shared test user objects
     *
     * @param test_cleanup $t the test environment
     * @param string $usr_name the name of the test user that needs the fixed test password
     * @return void
     */
    private function ensure_test_password(test_cleanup $t, string $usr_name): void
    {
        $msg = new user_message();
        $db_usr = new user();
        $db_usr->load_by_name($usr_name, $msg);
        $pw_hash = $db_usr->get_password();
        if ($pw_hash == null or !password_verify(users::TEST_USER_PASSWORD, $pw_hash)) {
            $pw_msg = new user_message($db_usr);
            $db_usr->set_password(users::TEST_USER_PASSWORD, $pw_msg);
            $db_usr->save($pw_msg);
            $test_name = 'the test password is set for ' . $usr_name;
            $t->assert_msg($test_name, $pw_msg);
        }
        // check the written state the way the login will read it, so a later login failure
        // can be separated into a setup problem (this fails too) or a change in between
        $chk_usr = new user();
        $chk_usr->load_by_name($usr_name, $msg);
        $pw_ok = password_verify(users::TEST_USER_PASSWORD, $chk_usr->get_password() ?? '');
        // the bcrypt hash checks and creation are intentionally slow,
        // so they are not charged to the assert timing
        $t->reset_section_timer();
        $test_name = 'the test password verifies for ' . $usr_name;
        $t->assert_true($test_name, $pw_ok);
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
            // write_named_cleanup removes the usr1 / usr2 sandbox rows including the usr1 owned base
            // word (the workflows add it with the usr1 message user, see url_test_base::init); the
            // system user cleanup stays to also remove a system owned row left over from a run
            // before the usr1 message user - otherwise it survives between runs and a later add
            // resurrects it with its old (system authored) change log entries
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}