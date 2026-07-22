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
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::WORD . 'word.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CONST . 'workflows.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
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

        // the same change wrapped in real logins: the first save of a user flips uses_sandbox, which
        // once nulled the stored password hash (a user rebuilt from the api json carried no hash),
        // so the re-login after the change is the regression check for that data loss
        $this->change_word_by_other_user_with_login($t);

        // a rename by a user that does not own the word: the view shown after the rename must
        // never be empty, so the ui object must end up with an id that resolves to the renamed word
        $this->rename_word_by_other_user($t);


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
        $t->subheader($this->ts . 'other user rename');

        // start from a clean state so the base word has the known original name
        $this->cleanup_test_words($t);

        // usr1 creates and owns the base word
        $owner = new user();
        $owner->load_by_name(users::SYSTEM_TEST_NAME);
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
        $changer->load_by_id($t->usr2->id());
        $msg_ui = new user_message_ui();
        $changer_ui = new user_ui();
        $changer_ui->set_from_json($changer->api_json(), $msg_ui);
        $renamed = word_names::TEST_ADD . test_cleanup::EXT_RENAME;
        $wrd_ui = new word_ui();
        $wrd_ui->set_id($base_id);
        $wrd_ui->set_name($renamed);
        $upd_msg = $wrd_ui->update($changer_ui, $msg_ui);
        $test_name = 'the rename by user 2 is saved';
        $t->assert_msg($test_name, $upd_msg);

        // the rename of a named object never changes the database id, because the id carries
        // all relations: the values, formulas and links of the word would be lost with a new id
        $test_name = 'the word id is unchanged by the rename';
        $t->assert($test_name, $wrd_ui->id(), $base_id);

        // the id of the ui object must resolve to the renamed word for user 2
        $test_name = 'the ui object id shows the renamed word for user 2';
        $wrd_chk = new word($changer);
        $wrd_chk->load_by_id($wrd_ui->id());
        $t->assert($test_name, $wrd_chk->name(), $renamed);

        // the rename request carries only the changed name (the '8'-prefixed edit baseline drops
        // the unchanged fields), so the values of the original word must survive the rename,
        // e.g. the description (see the fill on a key update in sandbox::save)
        $test_name = 'the description survives the rename';
        $t->assert($test_name, $wrd_chk->get_description(), word_names::TEST_ADD_COM);

        // the owner keeps the original word under the original id
        $test_name = 'the owner keeps the original word name';
        $wrd_owner = new word($owner);
        $wrd_owner->load_by_id($base_id);
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
        $back_msg = $wrd_back->update($changer_ui, $msg_ui);
        $test_name = 'the rename back to the standard name is saved without a duplicate message';
        $t->assert_msg($test_name, $back_msg);
        $test_name = 'the rename back removes the user overlay row';
        $wrd_undo_chk = new word($changer);
        $wrd_undo_chk->load_by_id($base_id);
        $t->assert_false($test_name, $wrd_undo_chk->has_usr_cfg());
        $test_name = 'after the rename back user 2 sees the standard name again';
        $t->assert($test_name, $wrd_undo_chk->name(), word_names::TEST_ADD);

        // remove the created rows (the cleanup also covers the renamed variant of the test name)
        $this->cleanup_test_words($t);
    }

    private function change_word_by_other_user_with_login(test_cleanup $t): void
    {
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
        $owner->load_by_name(users::SYSTEM_TEST_NAME);
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
        $wrd->load_by_name(word_names::TEST_ADD);
        $test_name = 'the logged in user 2 can load the base word';
        $t->assert_true($test_name, $wrd->id() > 0);
        $wrd->set_description(word_names::TEST_CHANGE_COM);
        $change_msg = new user_message($changer);
        $wrd->save($change_msg);
        $test_name = 'the user 2 change is saved';
        $t->assert_msg($test_name, $change_msg);
        $test_name = 'user 2 now uses the sandbox';
        $usr_chk = new user();
        $usr_chk->load_by_id($changer->id());
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
        $wrd_changer->load_by_name(word_names::TEST_ADD);
        $test_name = 'user 2 sees the changed description after the re-login';
        $t->assert($test_name, $wrd_changer->get_description(), word_names::TEST_CHANGE_COM);

        // usr2 logs out and the owner logs in and still sees the unchanged original
        $this->logout();
        $owner_in = $this->login_as($t, 'the owner can log in', users::SYSTEM_TEST_NAME);
        $wrd_owner = new word($owner_in);
        $wrd_owner->load_by_name(word_names::TEST_ADD);
        $test_name = 'the owner sees the unchanged description';
        $t->assert($test_name, $wrd_owner->get_description(), word_names::TEST_ADD_COM);

        // undo the usr2 overlay so the cleanup can hard delete the base word (see change_word_by_other_user)
        $wrd_undo = new word($changer);
        $wrd_undo->load_by_name(word_names::TEST_ADD);
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
        $db_usr = new user();
        $db_usr->load_by_name($usr_name);
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
        $chk_usr->load_by_name($usr_name);
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
            // write_named_cleanup removes the usr1 / usr2 sandbox rows; the reserved test word is owned
            // by the system user (it is added with the system message user), so remove that row too -
            // otherwise it survives between runs and a later add keeps its old (changed) description
            $t->write_named_cleanup($wrd, $wrd_name);
            $t->write_named_cleanup_one($wrd, $t->usr_system, $wrd_name);
        }
    }

}