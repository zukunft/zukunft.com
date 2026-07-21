<?php

/*

    test/unit_ui/system_view_ui_tests.php - test if the system view still look the same without using the api
    -------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::API_OBJECT . 'controller.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::MODEL_HELPER . 'combine_object.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_PHRASE . 'term.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_RESULT . 'result.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_VIEW . 'view_relation.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::UTILS . 'test_cleanup.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\helper\combine_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term;
use Zukunft\ZukunftCom\main\php\cfg\system\job;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\system\sys_log;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\cfg\view\term_view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class system_view_ui_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $tl = new test_lib();
        $t_usr = new test_users($t);
        $t_map = new test_mappers($t);
        $map_ui = new MapObject();

        // start the test section (ts)
        $ts = 'unit ui system views ';
        $t->header($ts);

        $t->subheader($ts . 'user message placeholder');
        // every page footer carries an invisible placeholder, so that a text replace
        // can add a user message to a html page loaded from the page cache
        $html = new html_base();
        $test_name = 'the page footer contains the user message placeholder exactly once';
        $t->assert($test_name, substr_count($html->footer(), api::USER_MSG_PLACEHOLDER), 1);
        $test_name = 'the about page footer also contains the user message placeholder';
        $t->assert_text_contains($test_name, $html->footer(true), api::USER_MSG_PLACEHOLDER);
        $test_name = 'the placeholder is an invisible html comment';
        $t->assert_true($test_name, str_starts_with(api::USER_MSG_PLACEHOLDER, '<!--')
            and str_ends_with(api::USER_MSG_PLACEHOLDER, '-->'));

        $t->subheader($ts . 'link escaping');
        // a user supplied url e.g. of a source can neither break the href attribute
        // nor inject a script and the link text is escaped by default
        $test_name = 'the href attribute of a link is escaped';
        $t->assert($test_name,
            $html->ref('https://example.org/?a=1&b="x"', 'a name'),
            '<a href="https://example.org/?a=1&amp;b=&quot;x&quot;">a name</a>');
        $test_name = 'the link text is escaped by default';
        $t->assert_text_contains($test_name,
            $html->ref('https://example.org/', 'a<b>name'), 'a&lt;b&gt;name');
        $test_name = 'a javascript url is not linked and only the text is shown';
        $t->assert($test_name,
            $html->ref('javascript:alert(1)', 'a name'), 'a name');
        $test_name = 'a relative url is linked';
        $t->assert_text_contains($test_name,
            $html->ref('/http/view.php?m=1', 'start'), '<a href="/http/view.php?m=1">');
        $t->usr1 = $t_usr->user_sys_test();
        $usr_msg = new user_message();
        $usr_ui = $map_ui->convertToUi($t->usr1, $usr_msg);
        $usr_msg->usr = $usr_ui;


        // shared frontend instance for all page tests
        $ui = new frontend('unit test');
        $dto = $tl->ui_test_cache($t->usr1, $t);
        $ui->set_cache($dto);
        // TODO Prio 1 deprecate
        $ui->load_dummy_cache_from_test_resources($t->usr1);
        $usr_sys_ui = $tl->cast_user($t->usr1);

        // the anti-csrf gate must fail closed for every form submit, not only the crud masks, so a
        // forged login/signup/import submit is rejected as well (see frontend::request_token_valid)
        $t->subheader($ts . 'anti-csrf token');
        $token = test_const::DUMMY_SESSION_TOKEN;

        $submit_ok = [url_var::MASK => views::WORD_ADD_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => $token];
        $test_name = 'a crud submit with the correct token is accepted';
        $t->assert_true($test_name, frontend::request_token_valid($submit_ok, $token));

        $submit_no_token = [url_var::MASK => views::WORD_ADD_ID, url_var::POST_SUBMIT => ''];
        $test_name = 'a crud submit without a token is rejected';
        $t->assert_false($test_name, frontend::request_token_valid($submit_no_token, $token));

        $login_submit = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => ''];
        $test_name = 'a login submit without a token is rejected (previously fail-open)';
        $t->assert_false($test_name, frontend::request_token_valid($login_submit, $token));

        $login_ok = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => $token];
        $test_name = 'a login submit with the correct token is accepted';
        $t->assert_true($test_name, frontend::request_token_valid($login_ok, $token));

        $submit_wrong = [url_var::MASK => views::WORD_ADD_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => 'wrong'];
        $test_name = 'a submit with a wrong token is rejected';
        $t->assert_false($test_name, frontend::request_token_valid($submit_wrong, $token));

        $get_nav = [url_var::MASK => views::WORD_ID, url_var::ID => 1];
        $test_name = 'a plain get navigation without a token is allowed';
        $t->assert_true($test_name, frontend::request_token_valid($get_nav, $token));

        // a get action mask (logout, error_update) triggers url_to_action on a plain get, so it must
        // carry the token too - samesite=lax still sends the cookie on a top-level cross-site get
        $logout_no_token = [url_var::MASK => views::LOGOUT_ID];
        $test_name = 'a logout get without a token is rejected';
        $t->assert_false($test_name, frontend::request_token_valid($logout_no_token, $token));

        $logout_ok = [url_var::MASK => views::LOGOUT_ID, url_var::SESSION_TOKEN => $token];
        $test_name = 'a logout get with the correct token is accepted';
        $t->assert_true($test_name, frontend::request_token_valid($logout_ok, $token));

        $error_update_no_token = [url_var::MASK => views::ERROR_UPDATE_ID, url_var::ID => 1];
        $test_name = 'an error_update get without a token is rejected';
        $t->assert_false($test_name, frontend::request_token_valid($error_update_no_token, $token));

        // request_triggers_action is the single predicate shared by the view.php dispatch and the
        // token gate, so an action can never be dispatched without a token having been required
        $test_name = 'a get action mask is recognised as an action';
        $t->assert_true($test_name, frontend::request_triggers_action($logout_no_token));
        $test_name = 'a form submit is recognised as an action';
        $t->assert_true($test_name, frontend::request_triggers_action($submit_no_token));
        $test_name = 'a plain get navigation is not an action';
        $t->assert_false($test_name, frontend::request_triggers_action($get_nav));

        // the data changing masks are blocked for an ip user if the pod does not permit changes,
        // but the login, signup and export masks must always stay open for an ip user
        $test_name = 'the import view is blocked for an ip user';
        $t->assert_true($test_name, in_array(views::IMPORT_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the undo view is blocked for an ip user';
        $t->assert_true($test_name, in_array(views::UNDO_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the paste table view is blocked for an ip user';
        $t->assert_true($test_name, in_array(views::PASTE_TABLE_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the job view is blocked for an ip user';
        $t->assert_true($test_name, in_array(views::JOB_ASYNC_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the word add view is blocked for an ip user';
        $t->assert_true($test_name, in_array(views::WORD_ADD_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the login view is never blocked for an ip user';
        $t->assert_false($test_name, in_array(views::LOGIN_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the signup view is never blocked for an ip user';
        $t->assert_false($test_name, in_array(views::SIGNUP_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the export view is not blocked for an ip user';
        $t->assert_false($test_name, in_array(views::EXPORT_ID, views::IP_BLOCKED_MASKS_IDS));
        $test_name = 'the start view is not blocked for an ip user';
        $t->assert_false($test_name, in_array(views::START_ID, views::IP_BLOCKED_MASKS_IDS));

        // a blocked change mask is answered with the calling page from the '9'-prefixed back
        // params or, if the request has no back e.g. a typed url, with the default view of
        // the target object so the user stays on the object (see /http/view.php)
        $msk = new views();
        $test_name = 'a blocked word edit shows the word default view again';
        $t->assert_true($test_name, $msk->change_to_show_id(views::WORD_EDIT_ID) == views::WORD_ID);
        $test_name = 'a blocked formula test shows the formula default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::FORMULA_TEST_ID) == views::FORMULA_ID);
        $test_name = 'a blocked mask without an object view falls back to the start view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::UNDO_ID) == views::START_ID);

        // tls is enforced (plain http redirected to https) in the prod and test environment so the
        // session cookie is never sent in the clear, but not in dev so the local http docker works;
        // the api entry (application::start_api) and the html frontend share this via server_guard
        $t->subheader($ts . 'tls enforcement');
        $test_name = 'the prod environment enforces tls';
        $t->assert_true($test_name, server_guard::tls_required(ENV_PROD));
        $test_name = 'the test environment enforces tls';
        $t->assert_true($test_name, server_guard::tls_required(ENV_UA));
        $test_name = 'the dev environment does not enforce tls';
        $t->assert_false($test_name, server_guard::tls_required(ENV_DEV));
        $test_name = 'an unknown environment does not enforce tls';
        $t->assert_false($test_name, server_guard::tls_required(''));

        // the api write endpoints (post/put/delete) reject a cross-site request (csrf): a browser
        // sends an Origin/Referer whose host must match this pod's host; a call without any origin
        // hint (server-to-server) is allowed because it carries no ambient session cookie
        $t->subheader($ts . 'api write same-origin');
        $host = 'zukunft.com';
        $test_name = 'a same-origin write is allowed';
        $t->assert_true($test_name, server_guard::origin_allowed('https://zukunft.com', '', $host));
        $test_name = 'a cross-origin write is rejected';
        $t->assert_false($test_name, server_guard::origin_allowed('https://evil.example', '', $host));
        $test_name = 'a cross-origin write is rejected by its referer when no origin is sent';
        $t->assert_false($test_name, server_guard::origin_allowed('', 'https://evil.example/x', $host));
        $test_name = 'a same-origin referer is allowed when no origin is sent';
        $t->assert_true($test_name, server_guard::origin_allowed('', 'https://zukunft.com/x', $host));
        $test_name = 'a call without an origin or referer is allowed (server-to-server)';
        $t->assert_true($test_name, server_guard::origin_allowed('', '', $host));
        $test_name = 'a same host on a non-standard port is allowed';
        $t->assert_true($test_name, server_guard::origin_allowed('http://localhost:8080', '', 'localhost:8080'));

        // a read api call from the pod itself (the html frontend calling its own api) may act for
        // the browsing user (user::data_user), but only a genuine local call is trusted: a request
        // forwarded by a proxy on the same host must never count as the pod itself
        $t->subheader($ts . 'api call from the own pod');
        $test_name = 'a loopback call without a forward header is from the own pod';
        $t->assert_true($test_name, server_guard::is_own_pod_call('127.0.0.1', '', false));
        $test_name = 'an ipv6 loopback call is from the own pod';
        $t->assert_true($test_name, server_guard::is_own_pod_call('::1', '', false));
        $test_name = 'a call from the own server address is from the own pod';
        $t->assert_true($test_name, server_guard::is_own_pod_call('10.0.0.5', '10.0.0.5', false));
        $test_name = 'an external call is not from the own pod';
        $t->assert_false($test_name, server_guard::is_own_pod_call('203.0.113.7', '10.0.0.5', false));
        $test_name = 'a proxied loopback call is not from the own pod';
        $t->assert_false($test_name, server_guard::is_own_pod_call('127.0.0.1', '', true));
        $test_name = 'an unknown remote address is not from the own pod';
        $t->assert_false($test_name, server_guard::is_own_pod_call('', '', false));

        // test the notification component standalone
        $t->subheader($ts . 'notification');
        $html_base = new html_base();
        $test_name = 'dsp_notification renders warning div';
        $t->assert_html(
            $test_name,
            $html_base->dsp_notification('Forgot password?'),
            '<div class="alert alert-warning notification-bar">Forgot password?</div>'
        );

        // test that a failed login renders the notification in the full page
        $_SESSION[url_var::SESSION_TOKEN] = test_const::DUMMY_SESSION_TOKEN;
        $err_msg = new user_message();
        $err_msg->add(msg_id::PASSWORD_WRONG, []);
        $url_array = [url_var::MASK => views::LOGIN_ID];
        $login_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto, true);

        $notification_div = '<div class="alert alert-warning notification-bar">';
        $test_name = 'login page with failed login shows notification bar';
        $t->assert_text_contains($test_name, $login_html, $notification_div);

        $expected_msg = msg_id::PASSWORD_WRONG->value;
        $test_name = 'login page notification contains password wrong message';
        $t->assert_text_contains($test_name, $login_html, $expected_msg);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'login_notification';
        $test_name = 'login page with failed login notification matches snapshot';
        $t->assert_html_page($test_name, $login_html, $file_path);

        // test that url_to_action preserves back params on login failure so "or go back" can be rendered
        $back_mask_key = url_var::BACK . url_var::MASK;
        $back_id_key = url_var::BACK . url_var::ID;
        $url_with_back = [
            url_var::MASK => views::LOGIN_ID,
            $back_mask_key => views::WORD_ID,
            $back_id_key => '123',
        ];
        $fail_msg = new user_message();
        $result_url = $ui->url_to_action($url_with_back, $t->usr1, $usr_sys_ui, $fail_msg, $ui->dto, false);

        $test_name = 'failed login preserves back mask param in returned url';
        $t->assert($test_name, $result_url[$back_mask_key] ?? '', views::WORD_ID);

        $test_name = 'failed login preserves back id param in returned url';
        $t->assert($test_name, $result_url[$back_id_key] ?? '', '123');

        $test_name = 'failed login keeps login mask in returned url';
        $t->assert($test_name, $result_url[url_var::MASK] ?? 0, views::LOGIN_ID);

        // test that a failed signup renders the notification in the full page
        $err_msg = new user_message();
        $err_msg->add(msg_id::SIGNUP_ERR_NAME_EXISTS, []);
        $url_array = [url_var::MASK => views::SIGNUP_ID];
        $signup_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto, true);

        $test_name = 'signup page with duplicate name shows notification bar';
        $t->assert_text_contains($test_name, $signup_html, $notification_div);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'signup_notification';
        $test_name = 'signup page with name exists notification matches snapshot';
        $t->assert_html_page($test_name, $signup_html, $file_path);

        // test that url_to_action on logout resets both user objects to anonymous state
        $logout_backend = clone $t->usr1;
        $logout_frontend = $tl->cast_user($logout_backend);
        $logout_msg = new user_message();
        $logout_result_url = $ui->url_to_action(
            [url_var::MASK => views::LOGOUT_ID],
            $logout_backend,
            $logout_frontend,
            $logout_msg,
            $ui->dto,
            false
        );

        $test_name = 'logout action returns logout view url';
        $t->assert($test_name, $logout_result_url[url_var::MASK] ?? 0, views::LOGOUT_ID);

        $test_name = 'logout action resets backend user to anonymous';
        $t->assert($test_name, $logout_backend->has_db_id(), false);

        $test_name = 'logout action resets frontend user to ip-only';
        $t->assert($test_name, $logout_frontend->is_ip_only(), true);

        // test that the logout page shows the success message
        global $mtr;
        $url_array = [url_var::MASK => views::LOGOUT_ID];
        $logout_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto, true);

        $test_name = 'logout page shows logout notice text';
        $t->assert_text_contains($test_name, $logout_html, $mtr->txt(msg_id::LOGOUT_NOTICE));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'logout_success';
        $test_name = 'logout page matches snapshot';
        $t->assert_html_page($test_name, $logout_html, $file_path);

        // test that a failed activation (key mismatch) renders the notification on the activate page
        $t->subheader($ts . 'login activate');

        $err_msg = new user_message();
        $err_msg->add(msg_id::ACTIVATE_ERR_KEY_MISMATCH, []);
        $url_array = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::ID => 1];
        $activate_html = $ui->url_to_html($url_array, null, $err_msg, $ui->dto, true);

        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'activate page with key mismatch shows notification bar';
        $t->assert_text_contains($test_name, $activate_html, $notification_div, $t::TIMEOUT_LIMIT_PAGE);

        $test_name = 'activate page notification contains key mismatch message';
        $t->assert_text_contains($test_name, $activate_html, $mtr->txt(msg_id::ACTIVATE_ERR_KEY_MISMATCH));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'activate_err_key_mismatch';
        $test_name = 'activate page with key mismatch notification matches snapshot';
        $t->assert_html_page($test_name, $activate_html, $file_path);

        // test that the activate page reached from the reset email link renders correctly; the reset
        // itself returns the login page with a neutral message (see action_login_reset), the real
        // activate link with the user id and key is delivered by email
        $t->subheader($ts . 'login reset');

        $url_array = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::ID => 1];
        $reset_sent_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto, true);

        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'activate page after reset email shows activation key label';
        $t->assert_text_contains($test_name, $reset_sent_html, $mtr->txt(msg_id::ACTIVATE_SUBMIT), $t::TIMEOUT_LIMIT_PAGE);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'reset_email_sent';
        $test_name = 'activate page after reset email matches snapshot';
        $t->assert_html_page($test_name, $reset_sent_html, $file_path);

        // test that the login_reset form renders with a cancel and go back link when no back params are given
        $url_array = [url_var::MASK => views::LOGIN_RESET_ID];
        $reset_form_html = $ui->url_to_html($url_array, null, new user_message(), $ui->dto, true);

        $test_name = 'login reset page shows cancel and go back link';
        $t->assert_text_contains($test_name, $reset_form_html, $mtr->txt(msg_id::CANCEL_AND_GO));

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'login_reset';
        $test_name = 'login reset page matches snapshot';
        $t->assert_html_page($test_name, $reset_form_html, $file_path);

        // test the system views by id
        // similar to horizontal_ui_tests which tests the curl view for the main objects
        $t->subheader($ts . 'by id');

        /*
        $test_name = 'test the start page upfront to have at least the header and footer fine for all pages';
        $url = 'http://localhost/http/view.php';
        $url_part = parse_url($url);
        parse_str($url_part["query"], $url_array);
        $html = $ui->url_to_html($url_array, $usr_sys_ui, $usr_msg, $ui->dto, true);
        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'start_page';
        $t->assert_html_page($test_name, $html, $file_path);
        */

        // regression test for the dbo_for_url path: an add view creates a new object, so a url
        // id must not be stamped onto it - otherwise every sub-object selector (phrase, ref,
        // from/to, ...) reads the object id as a pre-selected entry and drops the
        // "please select ..." default (see frontend::dbo_for_url)
        $t->subheader($ts . 'add view keeps new object id zero');
        $add_url = $t_map->class_to_filled_url(formula_link::class, views::FORMULA_LINK_ADD_ID, change_actions::ADD);
        $add_part = parse_url($add_url);
        parse_str($add_part['query'], $add_array);
        $add_html = $ui->url_to_html($add_array, null, new user_message(), $ui->dto, true);
        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'add view keeps the hidden id field at 0';
        $t->assert_text_contains($test_name, $add_html, 'name="id" id="id" value="0"', $t::TIMEOUT_LIMIT_PAGE);
        $test_name = 'add view does not stamp the url id onto the new object';
        $t->assert_text_not_contains($test_name, $add_html, 'name="id" id="id" value="1"');

        // the admin only masks (views::ADMIN_MASK_IDS) must be authorized centrally so the admin
        // content is not rendered to just anyone (see frontend::admin_mask_denied)
        $t->subheader($ts . 'admin mask authorization');
        $admin_url = [url_var::MASK => views::ADMIN_MAIN_ID];

        // negative: an anonymous user is sent to the start view with a permission message and never
        // sees the admin content
        $anon_msg = new user_message();
        $anon_html = $ui->url_to_html($admin_url, null, $anon_msg, $ui->dto, true);
        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'the admin main view is not rendered for an anonymous user';
        $t->assert_text_not_contains($test_name, $anon_html, 'system_title_admin', $t::TIMEOUT_LIMIT_PAGE);
        $test_name = 'the anonymous user is told that the admin view needs an administrator';
        $t->assert_text_contains($test_name, $anon_html, msg_id::ADMIN_MASK_DENIED->value);

        // positive: an admin (here the system user, see admin_mask_denied) may render the admin view
        $adm_msg = new user_message();
        $adm_html = $ui->url_to_html($admin_url, $usr_sys_ui, $adm_msg, $ui->dto, true);
        $test_name = 'the admin main view is rendered for a system user';
        $t->assert_text_contains($test_name, $adm_html, 'system_title_admin');

        // negative: url_to_action refuses an admin mask action for a non-admin user and returns the
        // start view instead of acting on it (a fresh frontend user has the ip-only profile)
        $act_msg = new user_message();
        $act_backend = clone $t->usr1;
        $act_usr = new user_ui();
        $act_url = $ui->url_to_action($admin_url, $act_backend, $act_usr, $act_msg, $ui->dto, false);
        $test_name = 'url_to_action sends a non-admin admin mask request to the start view';
        $t->assert($test_name, $act_url[url_var::MASK] ?? 0, views::START_ID);

        // loop over the system views
        $this->assert_views_by_id($t, $t_map, $ui, $usr_sys_ui, $usr_msg, $lib);

    }

    /**
     * iterate over all system view ids and assert each rendered page matches its HTML snapshot
     * @param test_cleanup $t test runner for assertions and user fixtures
     * @param test_mappers $t_map builds filled test URLs per class and action
     * @param frontend $ui renders HTML from a URL array
     * @param user_ui $usr_sys_ui logged-in user used for views that require a session
     * @param user_message $usr_msg collects any messages produced during rendering
     * @param library $lib converts class names to file-path segments
     */
    private function assert_views_by_id(
        test_cleanup $t,
        test_mappers $t_map,
        frontend     $ui,
        user_ui      $usr_sys_ui,
        user_message $usr_msg,
        library      $lib
    ): void
    {
        $updated_files = [];
        // TODO Prio 3 review and use random?
        for ($msk_typ = 1; $msk_typ < 2; $msk_typ++) {
            for ($id = views::MIN_TEST_ID; $id <= views::MAX_TEST_ID; $id++) {
                $dbo = $this->view_id_to_dbo($id, $t->usr1);
                $action = $this->view_id_to_url_action($id);
                if ($msk_typ == 1) {
                    $url = $t_map->class_to_filled_url($dbo::class, $id, $action);
                } else {
                    $url = $t_map->class_to_filled_url($dbo::class, $id, $action, url_var::MASK);
                }
                $url_part = parse_url($url);
                parse_str($url_part["query"], $url_array);
                // an admin mask is now rendered only for an admin or system user (see
                // frontend::admin_mask_denied), so render it as the system user like the login views
                // that also need a session; the other views keep rendering as an anonymous user.
                // because of this the admin mask snapshots show the logged-in user menu (logout)
                // instead of the anonymous login/signup menu
                if (in_array($id, views::TEST_LOGIN_VIEW_IDS)
                    or in_array($id, views::ADMIN_MASK_IDS)) {
                    $html = $ui->url_to_html($url_array, $usr_sys_ui, $usr_msg, $ui->dto, true);
                } else {
                    $html = $ui->url_to_html($url_array, null, $usr_msg, $ui->dto, true);
                }
                [$folder, $dbo_name, $test_name] = $this->view_id_to_file_info($id, $dbo::class, $action, $url_array, $lib);
                $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
                $updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
                $t->assert_html_page($test_name, $html, $file_path);
            }
        }
        // remove test files not used any more
        foreach ($lib->dir_files(test_paths::RESOURCE . test_paths::VIEWS_BY_ID) as $path) {
            if (str_ends_with($path, test_files::HTML) && !in_array($path, $updated_files)) {
                $t->delete_path_file($path);
            }
        }
    }

    /**
     * resolve the snapshot folder, filename prefix, and test name for one view id
     * @param int $id the view id
     * @param string $class the backend object class name
     * @param string $action the CRUD action
     * @param array $url_array the parsed URL parameters
     * @param library $lib helper for class-to-name conversion
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function view_id_to_file_info(
        int     $id,
        string  $class,
        string  $action,
        array   $url_array,
        library $lib
    ): array
    {
        $prefix = $id . '_';
        if ($class == db_object::class) {
            $result = $this->db_object_file_info($id, $action, $prefix);
        } elseif (in_array($id, views::SEARCH_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'search';
            $result = ['search' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } elseif (in_array($id, views::IM_EXPORT_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'im_export';
            $result = ['im_export' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } else {
            $domain_class = $lib->class_to_name($class);
            $dbo_name = $prefix . $domain_class;
            $dbo_id = $url_array[url_var::ID] ?? 0;
            // TODO Prio 2 use $lib function to convert the phrase (or term) id to the object id
            if ($class == phrase::class or $class == term::class) {
                if ($dbo_id < 0) {
                    $dbo_id = $dbo_id * -1;
                }
            }
            if ($action != change_actions::SHOW) {
                if (in_array($id, views::PROCESS_STEP_MASKS_IDS)) {
                    $dbo_name .= '_' . (views::TEST_VIEW_IDS[$id] ?? $action);
                } else {
                    $dbo_name .= '_' . $action;
                }
            }
            if ($dbo_id != 0) {
                if ($class == phrase::class or $class == term::class) {
                    $dbo_name .= '_' . views::TEST_VIEW_IDS[$id] ?? 'not_found';
                }
                $dbo_name .= '_' . $lib->str_to_file($dbo_id);
            }
            $result = [$domain_class . DIRECTORY_SEPARATOR, $dbo_name, $action . ' ' . $domain_class . ' view'];
        }
        return $result;
    }

    /**
     * resolve folder, filename prefix, and test name for a db_object view (system/process views)
     * @param int $id the view id
     * @param string $action the CRUD action
     * @param string $prefix the id-based filename prefix e.g. '60_'
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function db_object_file_info(int $id, string $action, string $prefix): array
    {
        $result = ['other' . DIRECTORY_SEPARATOR, $prefix . 'other', 'other view'];
        if ($id == views::START_ID) {
            $result = ['start_page' . DIRECTORY_SEPARATOR, $prefix . 'start_page', 'start_page view'];
        } elseif (in_array($id, views::CONFIRM_MASKS_IDS)) {
            $result = $this->confirm_file_info($id, $action, $prefix);
        } elseif (in_array($id, views::STATIC_VIEW_IDS)) {
            // checked before PROCESS_STEP_MASKS_IDS because SETUP_ID appears in both
            $name = views::TEST_VIEW_IDS[$id] ?? 'static';
            $result = ['static' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        } elseif (in_array($id, views::PROCESS_STEP_MASKS_IDS)) {
            $name = views::TEST_VIEW_IDS[$id] ?? 'process_step';
            $result = ['process' . DIRECTORY_SEPARATOR, $prefix . $name, $name . ' view'];
        }
        return $result;
    }

    /**
     * resolve folder, filename prefix, and test name for a confirm view
     * @param int $id the view id
     * @param string $action the CRUD action
     * @param string $prefix the id-based filename prefix e.g. '55_'
     * @return array [$folder, $dbo_name, $test_name]
     */
    private function confirm_file_info(int $id, string $action, string $prefix): array
    {
        $folder = 'confirm' . DIRECTORY_SEPARATOR;
        $file_name = 'unknown';
        $test_name = 'unknown view';
        if ($action == change_actions::ADD) {
            $file_name = 'confirm_word_add';
            $test_name = 'confirm word add view';
        } elseif ($action == change_actions::UPDATE) {
            if ($id == views::CONFIRM_VIEWS_ID) {
                $file_name = 'confirm_word_view_change';
                $test_name = 'confirm word mask change view';
            } else {
                $file_name = 'confirm_word_edit';
                $test_name = 'confirm word edit view';
            }
        } elseif ($action == change_actions::DELETE) {
            $file_name = 'confirm_word_del';
            $test_name = 'confirm word del view';
        } elseif ($id == views::SANDBOX_ID) {
            $file_name = 'sandbox';
            $test_name = 'confirm user sandbox view';
        } elseif ($id == views::UNDO_ID) {
            $file_name = 'undo';
            $test_name = 'undo change view';
        }
        return [$folder, $prefix . $file_name, $test_name];
    }

    private function view_id_to_dbo(int $view_id, user $usr): sandbox|sandbox_multi|user|db_object|combine_object|phrase_list
    {
        // select the backend object to display
        // TODO add any missing system views like
        //      term_view_links, formula_link, component_links, styles, view_types,
        //      time_series, geo and text values, ip ranges, language, pod,
        //      add types (phrase_type, formula_type, formula_link_types, source_types,
        //                 ref_types, position_types, view_types, view_link_types,
        //                 component_types, component_link_types, pod_types, pod_status)
        //     (at least all curl views)
        if (in_array($view_id, view_shared::WORD_MASKS_IDS)) {
            $dbo = new word($usr);
        } elseif (in_array($view_id, view_shared::VERB_MASKS_IDS)) {
            $dbo = new verb();
        } elseif (in_array($view_id, view_shared::TRIPLE_MASKS_IDS)) {
            $dbo = new triple($usr);
        } elseif (in_array($view_id, view_shared::SOURCE_MASKS_IDS)) {
            $dbo = new source($usr);
        } elseif (in_array($view_id, view_shared::REF_MASKS_IDS)) {
            $dbo = new ref($usr);
        } elseif (in_array($view_id, view_shared::VALUE_MASKS_IDS)) {
            $dbo = new value($usr);
        } elseif (in_array($view_id, view_shared::GROUP_MASKS_IDS)) {
            $dbo = new group($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_MASKS_IDS)) {
            $dbo = new formula($usr);
        } elseif (in_array($view_id, view_shared::RESULT_MASKS_IDS)) {
            $dbo = new result($usr);
        } elseif (in_array($view_id, view_shared::VIEW_MASKS_IDS)) {
            $dbo = new view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_MASKS_IDS)) {
            $dbo = new component($usr);
        } elseif (in_array($view_id, view_shared::VIEW_LINK_MASKS_IDS)) {
            $dbo = new term_view($usr);
        } elseif (in_array($view_id, view_shared::COMPONENT_LINK_MASKS_IDS)) {
            $dbo = new component_link($usr);
        } elseif (in_array($view_id, view_shared::FORMULA_LINK_MASKS_IDS)) {
            $dbo = new formula_link($usr);
        } elseif (in_array($view_id, view_shared::VIEW_RELATION_MASKS_IDS)) {
            $dbo = new view_relation($usr);
        } elseif (in_array($view_id, view_shared::USER_MASKS_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::USER_LOGIN_MASK_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::ADMIN_MASK_IDS)) {
            $dbo = new user();
        } elseif (in_array($view_id, view_shared::LANGUAGE_MASKS_IDS)) {
            $dbo = new language();
        } elseif (in_array($view_id, view_shared::CONFIRM_MASKS_IDS)) {
            $dbo = new db_object();
        } elseif (in_array($view_id, view_shared::STATIC_VIEW_IDS)) {
            $dbo = new db_object();
        } elseif (in_array($view_id, view_shared::SYSTEM_LOG_VIEW_IDS)) {
            $dbo = new sys_log();
        } elseif (in_array($view_id, view_shared::PHRASE_MASKS_IDS)) {
            $dbo = new phrase($usr);
        } elseif (in_array($view_id, view_shared::CHANGEABLE_PHRASE_VIEW_IDS)) {
            $dbo = new phrase($usr);
        } elseif (in_array($view_id, view_shared::CONTEXT_VIEW_IDS)) {
            $dbo = new phrase_list($usr);
        } elseif (in_array($view_id, view_shared::JOB_MASKS_IDS)) {
            $dbo = new job($usr);
        } else {
            $dbo = new db_object();
            if ($view_id != views::START_ID) {
                log_err('no backend object defined for view id ' . $view_id);
            }
        }
        return $dbo;
    }


    private function view_id_to_url_action(int $view_id): string
    {
        // select the backend object to display
        if (in_array($view_id, view_shared::SHOW_MASKS_IDS)) {
            $action = change_actions::SHOW;
        } elseif (in_array($view_id, view_shared::ADD_MASKS_IDS)) {
            $action = change_actions::ADD;
        } elseif (in_array($view_id, view_shared::EDIT_MASKS_IDS)) {
            $action = change_actions::UPDATE;
        } elseif (in_array($view_id, view_shared::DEL_MASKS_IDS)) {
            $action = change_actions::DELETE;
        } elseif (in_array($view_id, view_shared::SUB_MASKS_IDS)) {
            $action = change_actions::SUB;
        } elseif (in_array($view_id, view_shared::PROCESS_STEP_MASKS_IDS)) {
            $action = change_actions::STEP;
        } elseif (in_array($view_id, view_shared::SEARCH_MASKS_IDS)) {
            $action = change_actions::SEARCH;
        } else {
            $action = 'unknown';
        }
        return $action;
    }

}