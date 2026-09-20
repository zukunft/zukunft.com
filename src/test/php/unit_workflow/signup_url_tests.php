<?php

/*

    test/php/unit_workflow/signup_url_tests.php - check the url based signup and email confirmation workflow
    -------------------------------------------

    snapshots the html of each step of the signup_confirm workflow: a new user signs up, gets the
    reserved name profile and confirms the email with the activation link of the signup mail; the
    shared run state, the frontend setup and the snapshot helpers live in url_test_base
    (see docs/llm/testing.md)

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

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'workflows.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT_WORKFLOW . 'url_test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\workflows;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class signup_url_tests extends url_test_base
{

    function run(test_cleanup $t): void
    {
        // load the shared frontend run state and print the section header
        $this->init($t, 'signup url->', 'url signup ');

        $t->subheader($this->ts . 'workflow');

        // the snapshot unit test only renders the steps
        // for the write test the same workflow is used with do_it = true
        $this->signup_confirm_workflow(workflows::WF_SIGNUP_CONFIRM_NBR);
    }

    /**
     * run the signup_confirm workflow and snapshot the html after every user action: open the signup
     * form, fill and send it, open the activation link of the signup mail with a wrong key, which does
     * not confirm the email, and then with the right key, which confirms it without a new password.
     * only a form submit runs the action like in http/view.php, because opening the activation link
     * must not confirm the email yet; the session and the mail are never touched (see
     * frontend::live_request). snapshots go into src/test/resources/web/html/workflow/signup_confirm_wf<nbr>/
     *
     * @param int $wf_nbr the workflow id selecting the snapshot folder and file prefix e.g. 36 for wf36
     * @param bool $do_it false to only render the steps, true to also write the new user and confirm its email
     */
    protected function signup_confirm_workflow(int $wf_nbr, bool $do_it = false): void
    {
        global $mtr;
        global $ui_sys;

        $t = $this->t;
        // the render publishes the signed up user as session user of the request cache (see
        // frontend::url_to_html), so the previous session user is restored at the end of the workflow
        $usr_keep = $ui_sys->usr ?? null;
        $this->ui->live_request = false;
        $this->wf_start($wf_nbr, workflows::WF_SIGNUP_CONFIRM, $t->usr1, users::TEST_SIGNUP_ID, $do_it);
        // the new user signs up without a login, so the pages are rendered like for an anonymous visitor
        $this->msg->usr = null;
        $this->wf_id = users::TEST_SIGNUP_ID;

        // edit: open the empty signup form from the start page
        $url_arr = [url_var::BACK . url_var::MASK => views::START_ID];
        $this->assert_step(workflows::EDIT, $url_arr, views::SIGNUP_ID, true);

        // fill: enter the name, the email and the password and send the signup form
        $html = $this->assert_step(workflows::FILL, test_users::signup_url_array() + $url_arr, views::SIGNUP_ID, true);
        if ($do_it) {
            $test_name = 'the signup tells the new user to confirm the email';
            $t->assert_text_contains($test_name, $html, $mtr->txt(msg_id::SIGNUP_MAIL_SENT));
            $this->wf_id = $this->assert_signed_up($t);
        }
        $this->msg->reset();

        // wrong key: open the activation link with a wrong key and confirm, which does not confirm the email
        $this->step_path .= workflows::NAME_SEP . workflows::STEP_WRONG_KEY;
        $wrong_url = test_users::activation_url_array($this->wf_id);
        $wrong_url[url_var::POST_KEY] = strrev(users::TEST_USER_ACTIVATION_KEY);
        $this->assert_step(workflows::EDIT, $wrong_url, views::LOGIN_ACTIVATE_ID, true);
        $html = $this->assert_step(workflows::CONFIRMED, $wrong_url, views::LOGIN_ACTIVATE_ID, true);
        if ($do_it) {
            $test_name = 'a wrong key of the activation link is refused';
            $t->assert_text_contains($test_name, $html, $mtr->txt(msg_id::ACTIVATE_ERR_KEY_MISMATCH));
            $test_name = '... and the email stays unconfirmed';
            $t->assert($test_name, $this->signup_profile_id(), $this->profile_id(user_profiles::NAME_ONLY));
        }
        // the refused key must not block the confirmation with the right key, which runs only on an ok message
        $this->msg->reset();

        // edit: open the activation link of the signup mail
        $act_url = test_users::activation_url_array($this->wf_id);
        $this->assert_step(workflows::EDIT, $act_url, views::LOGIN_ACTIVATE_ID, true);

        // confirmed: confirm without a new password, which only confirms the email
        $this->assert_step(workflows::CONFIRMED, $act_url, views::LOGIN_ACTIVATE_ID, true);
        if ($do_it) {
            $this->assert_confirmed($t);
        }
        $this->msg->reset();
        if ($usr_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_keep;
        }
    }

    /**
     * check that the signup has written the new user with the reserved name profile and the key of the
     * signup mail, and replace the random key of the mail with the known test key for the next steps
     *
     * @param test_cleanup $t the test environment
     * @return int the database id of the new user
     */
    private function assert_signed_up(test_cleanup $t): int
    {
        $test_name = 'the signup has written the new user';
        $new_usr = new user();
        $new_usr->load_by_name(users::TEST_SIGNUP_NAME, new user_message());
        $t->assert_true($test_name, $new_usr->id() > 0);
        $test_name = '... with the reserved name profile';
        $t->assert($test_name, $new_usr->profile_id, $this->profile_id(user_profiles::NAME_ONLY));
        $test_name = '... and the key of the signup mail';
        $t->assert_true($test_name, $new_usr->has_active_activation_key());

        // the mailed key is random and only its hash is stored, so the test sets a known key
        $test_name = 'the known test key replaces the key of the signup mail';
        $new_usr->set_activation_key(users::TEST_USER_ACTIVATION_KEY, user::SIGNUP_KEY_VALIDITY);
        $key_msg = new user_message($new_usr);
        $new_usr->save($key_msg);
        $t->assert_msg($test_name, $key_msg);
        return $new_usr->id();
    }

    /**
     * check that the activation link has confirmed the email of the new user
     *
     * @param test_cleanup $t the test environment
     */
    private function assert_confirmed(test_cleanup $t): void
    {
        $test_name = 'the activation link has confirmed the email';
        $cfm_usr = new user();
        $cfm_usr->load_by_name(users::TEST_SIGNUP_NAME, new user_message());
        $t->assert($test_name, $cfm_usr->profile_id, $this->profile_id(user_profiles::EMAIL));
        $test_name = '... and has used up the key';
        $t->assert_false($test_name, $cfm_usr->has_active_activation_key());
        $test_name = '... but kept the password of the signup, because no new password has been entered';
        $pw_ok = password_verify(users::TEST_USER_PASSWORD, $cfm_usr->get_password() ?? '');
        // the bcrypt password check is intentionally slow, so it is not charged to the assert timing
        $t->reset_section_timer();
        $t->assert_true($test_name, $pw_ok);
    }

    /**
     * @return int|null the profile id of the signed up test user as stored in the database
     */
    private function signup_profile_id(): ?int
    {
        $usr = new user();
        $usr->load_by_name(users::TEST_SIGNUP_NAME, new user_message());
        return $usr->profile_id;
    }

    /**
     * @param string $code_id the code id of a user profile e.g. user_profiles::EMAIL
     * @return int the database id of the profile
     */
    private function profile_id(string $code_id): int
    {
        global $sys;
        return $sys->typ_lst->usr_pro->id($code_id);
    }

}
