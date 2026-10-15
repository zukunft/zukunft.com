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
include_once html_paths::EXECUTE . 'system_page.php';
include_once html_paths::SYSTEM . 'sys_log_list.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::PHRASE . 'phrase_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_log_sum.php';
include_once html_paths::USER . 'user_log_sum_list.php';
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
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_admin_dashboard.php';
include_once test_paths::UNIT . 'sys_log_tests.php';
include_once test_paths::CREATE . 'test_config_trees.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_mappers.php';
include_once test_paths::CREATE . 'test_results.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CREATE . 'test_words.php';
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
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\cfg\helper\server_guard;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_log_sum;
use Zukunft\ZukunftCom\main\php\web\user\user_log_sum_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\views as view_shared;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_admin_dashboard;
use Zukunft\ZukunftCom\test\php\create\test_config_trees;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use DateTime;
use DateTimeInterface;

class system_view_ui_tests
{

    // the snapshot names of the admin dashboard with the three data sets of test_admin_dashboard
    const string ADMIN_EMPTY_FILE = test_paths::HTML . test_paths::VIEWS_ADMIN . 'admin_main_empty';
    const string ADMIN_MIXED_FILE = test_paths::HTML . test_paths::VIEWS_ADMIN . 'admin_main_mixed';
    const string ADMIN_CRITICAL_FILE = test_paths::HTML . test_paths::VIEWS_ADMIN . 'admin_main_critical';

    // the snapshot folder and name of the start page
    const string START_PAGE_FILE_NAME = 'start_page';
    // added to the snapshot name of a view rendered with the more version of its list
    const string MORE_LIST_FILE_POSTFIX = '_more';
    // added to the snapshot name of the start page with the main columns and the ranges of its table
    const string DETAILS_LIST_FILE_POSTFIX = '_details';
    // the start page details list shows the real ranking data of the pod, which the unit fixtures
    // do not carry, so localhost_ui_tests fetches its snapshot from the pod after the db recreate
    // and the cleanup of the snapshots below keeps it
    const string START_PAGE_DETAILS_FILE = test_paths::VIEWS_BY_ID . self::START_PAGE_FILE_NAME . DIRECTORY_SEPARATOR
        . views::START_ID . '_' . self::START_PAGE_FILE_NAME . self::DETAILS_LIST_FILE_POSTFIX;

    function run(test_cleanup $t): void
    {
        global $mtr;

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
        // nor inject a script and the link text is escaped by default;
        // rendered for an anonymous user, because a link of this pod carries the id of the logged-in
        // user of the request cache (see html_base::url_with_user), which a previous test may have left
        global $ui_sys;
        $usr_keep = $ui_sys->usr ?? null;
        $ui_sys->usr = new user_ui();
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
        if ($usr_keep == null) {
            unset($ui_sys->usr);
        } else {
            $ui_sys->usr = $usr_keep;
        }

        // the body of a system view whose data and actions are not yet implemented shows a hint
        $t->subheader($ts . 'not yet available');
        $test_name = 'the body of a not yet implemented system view shows the hint';
        $page = new system_page();
        $t->assert_text_contains($test_name, $page->not_yet_available(), $mtr->txt(msg_id::INFO_VIEW_NOT_YET_AVAILABLE));

        // the "..." below the title of a default view opens the menu with the other three versions
        $t->subheader($ts . 'related view selector');
        $test_name = 'the default word view links to the detail version';
        $wrd_ui = new test_words($t)->chf_ui();
        $sel_html = $page->related_view_selector(views::WORD_ID, $wrd_ui);
        $t->assert_text_contains($test_name, $sel_html, url_var::MASK . '=' . views::WORD_DEFAULT_DETAIL_ID);
        $test_name = '... to the range version';
        $t->assert_text_contains($test_name, $sel_html, url_var::MASK . '=' . views::WORD_DEFAULT_RANGE_ID);
        $test_name = '... to the detail with range version';
        $t->assert_text_contains($test_name, $sel_html, url_var::MASK . '=' . views::WORD_DEFAULT_DETAIL_RANGE_ID);
        $test_name = '... for the same word';
        $t->assert_text_contains($test_name, $sel_html, url_var::ID . '=' . words::CHF_ID);
        $test_name = '... and offers only the three other versions';
        $t->assert($test_name, substr_count($sel_html, '<' . html_base::LI . '>'), 3);
        $test_name = 'the detail with range version links back to the default version';
        $sel_html = $page->related_view_selector(views::WORD_DEFAULT_DETAIL_RANGE_ID, $wrd_ui);
        $t->assert_text_contains($test_name, $sel_html, '>' . msg_id::VIEW_VERSION_DEFAULT->text() . '<');
        $test_name = '... but not to itself';
        $t->assert_text_not_contains($test_name, $sel_html, '>' . msg_id::VIEW_VERSION_DETAIL_RANGE->text() . '<');
        // negative: a view that is not a version of a default view e.g. an edit view has no menu
        $test_name = 'the word edit view shows no view version menu';
        $t->assert($test_name, $page->related_view_selector(views::WORD_EDIT_ID, $wrd_ui), '');
        $test_name = 'without the object shown no view version menu is created';
        $t->assert($test_name, $page->related_view_selector(views::WORD_ID, null), '');
        // switch usr1 to the system test profile user (needed for the ui cache imports)
        // and remember the normal usr1 so the end of this run can restore it - otherwise every
        // later test would see a system-tier usr1 instead of the normal email profile user
        $usr1_saved = $t->usr1;
        $t->usr1 = $t_usr->user_sys_test();
        $msg = new user_message();
        $usr_ui = $map_ui->convertToUi($t->usr1, $msg);
        $msg->usr = $usr_ui;


        // shared frontend instance for all page tests
        $ui = new frontend('unit test');
        $cac_msg = new user_message();
        // the cache is created by the dev user, because the system views set a code id,
        // which the normal test user is not permitted to do (see user::can_set_code_id)
        // TODO Prio 2 check if a user with less permissions can be used
        $dto = $tl->ui_test_cache($t->usr_dev, $t, $cac_msg);
        $ui->set_cache($dto);
        // TODO Prio 1 deprecate
        $ui->load_dummy_cache_from_test_resources($msg, file_get_contents(test_files::TYPE_LISTS_CACHE));
        $usr_sys_ui = $tl->cast_user($t->usr1);

        // the anti-csrf gate must fail closed for every form submit, not only the crud masks, so a
        // forged login/signup/import submit is rejected as well (see frontend::request_token_valid)
        $t->subheader($ts . 'anti-csrf token');
        $token = test_const::DUMMY_SESSION_TOKEN;

        $submit_ok = [url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => $token];
        $test_name = 'a crud submit with the correct token is accepted';
        $t->assert_true($test_name, frontend::request_token_valid($submit_ok, $token));

        $submit_no_token = [url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::POST_SUBMIT => ''];
        $test_name = 'a crud submit without a token is rejected';
        $t->assert_false($test_name, frontend::request_token_valid($submit_no_token, $token));

        $login_submit = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => ''];
        $test_name = 'a login submit without a token is rejected (previously fail-open)';
        $t->assert_false($test_name, frontend::request_token_valid($login_submit, $token));

        $login_ok = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => $token];
        $test_name = 'a login submit with the correct token is accepted';
        $t->assert_true($test_name, frontend::request_token_valid($login_ok, $token));

        $submit_wrong = [url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::POST_SUBMIT => '', url_var::SESSION_TOKEN => 'wrong'];
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
        $t->assert_true($test_name, in_array(views::WORD_ADD_DETAIL_ID, views::IP_BLOCKED_MASKS_IDS));
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
        $test_name = 'a blocked view edit shows the view default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::VIEW_EDIT_ID) == views::VIEW_DEFAULT_ID);
        $test_name = 'a blocked component edit shows the component default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::COMPONENT_EDIT_ID) == views::COMPONENT_DEFAULT_ID);
        $test_name = 'a blocked formula link edit shows the formula link default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::FORMULA_LINK_EDIT_ID) == views::FORMULA_LINK_DEFAULT_ID);
        $test_name = 'a blocked view link edit shows the term view default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::VIEW_LINK_EDIT_ID) == views::TERM_VIEW_DEFAULT_ID);
        $test_name = 'a blocked component link edit shows the component link default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::COMPONENT_LINK_EDIT_ID) == views::COMPONENT_LINK_DEFAULT_ID);
        $test_name = 'a blocked view relation edit shows the view relation default view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::VIEW_RELATION_EDIT_ID) == views::VIEW_RELATION_DEFAULT_ID);
        $test_name = 'a blocked mask without an object view falls back to the start view';
        $t->assert_true($test_name, $msk->change_to_show_id(views::UNDO_ID) == views::START_ID);

        // the edit form's cancel button returns to the object's default view, so the base view
        // of the view and the component change masks is their new default view
        $test_name = 'the base view of the view edit mask is the view default view';
        $t->assert($test_name, $msk->system_to_base(views::VIEW_EDIT), views::VIEW);
        $test_name = 'the base view of the component edit mask is the component default view';
        $t->assert($test_name, $msk->system_to_base(views::COMPONENT_EDIT), views::COMPONENT);
        $test_name = 'the base view of the formula link edit mask is the formula link default view';
        $t->assert($test_name, $msk->system_to_base(views::FORMULA_LINK_EDIT), views::FORMULA_LINK_DEFAULT);
        $test_name = 'the base view of the view link edit mask is the term view default view';
        $t->assert($test_name, $msk->system_to_base(views::VIEW_LINK_EDIT), views::TERM_VIEW_DEFAULT);
        $test_name = 'the base view of the component link edit mask is the component link default view';
        $t->assert($test_name, $msk->system_to_base(views::COMPONENT_LINK_EDIT), views::COMPONENT_LINK_DEFAULT);
        $test_name = 'the base view of the view relation edit mask is the view relation default view';
        $t->assert($test_name, $msk->system_to_base(views::VIEW_RELATION_EDIT), views::VIEW_RELATION_DEFAULT);
        $test_name = 'a mask without a base view returns an empty code id';
        $t->assert($test_name, $msk->system_to_base(views::UNDO), '');

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

        // the data user of an own pod call is only taken from a url parameter that is a valid id
        $t->subheader($ts . 'requested user id');
        $test_name = 'a positive number is the requested user id';
        $t->assert($test_name, server_guard::valid_user_id('7'), 7);
        $test_name = 'a request without user parameter names no user';
        $t->assert($test_name, server_guard::valid_user_id(0), 0);
        $test_name = 'a negative number is no user id';
        $t->assert($test_name, server_guard::valid_user_id('-7'), 0);
        $test_name = 'a text is no user id';
        $t->assert($test_name, server_guard::valid_user_id('7x'), 0);
        $test_name = 'an array parameter e.g. u[]=7 is no user id';
        $t->assert($test_name, server_guard::valid_user_id(['7']), 0);

        // test the notification component standalone
        $t->subheader($ts . 'notification');
        $html_base = new html_base();
        $test_name = 'dsp_notification renders warning div';
        $t->assert_html(
            $test_name,
            $html_base->dsp_notification('Forgot password?'),
            '<div class="alert alert-warning notification-bar">Forgot password?</div>'
        );
        $saved_txt = $ui->setting_saved_text([
            url_var::NUMERIC_VALUE => (string)test_config_trees::MAX_DELAY_NEW,
            url_var::PRE . url_var::NUMERIC_VALUE => (string)test_config_trees::MAX_DELAY_VALUE,
            url_var::CONFIG_KEY => test_config_trees::MAX_DELAY,
        ]);
        $test_name = 'a done action without an issue is confirmed in green';
        $saved_msg = new user_message();
        $saved_msg->add_info($saved_txt);
        $t->assert_text_contains($test_name, $ui->user_msg_html($saved_msg), html_base::CLASS_NOTIFICATION_SUCCESS);
        // negative: an issue of the same request is never shown as a success
        $test_name = '... but in orange if the request has an issue';
        $saved_msg->add_id(msg_id::CONFIG_NOT_LOADED);
        $t->assert_text_contains($test_name, $ui->user_msg_html($saved_msg), html_base::CLASS_NOTIFICATION . '"');
        // e.g. the refused save of a setting of the pod, which the backend reports without vars
        $test_name = 'a message without vars of a write is kept when merged into the message of the request';
        $refused_msg = new user_message();
        $refused_msg->add_id(msg_id::CONFIG_SYSTEM_ADMIN_ONLY);
        $request_msg = new user_message();
        $request_msg->merge($refused_msg);
        // escaped like dsp_notification, because the text quotes the key 'system'
        $refused_html = htmlspecialchars($mtr->txt(msg_id::CONFIG_SYSTEM_ADMIN_ONLY));
        $t->assert_text_contains($test_name, $ui->user_msg_html($request_msg), $refused_html);
        // negative: e.g. the done of a successful write is an info without vars and never an error
        $test_name = '... but an info without vars keeps the request ok';
        $done_msg = new user_message();
        $done_msg->add_info_id(msg_id::DONE);
        $ok_msg = new user_message();
        $ok_msg->merge($done_msg);
        $t->assert_true($test_name, $ok_msg->is_ok());

        // test that a failed login renders the notification in the full page
        $_SESSION[url_var::SESSION_TOKEN] = test_const::DUMMY_SESSION_TOKEN;
        $err_msg = new user_message();
        $err_msg->add(msg_id::PASSWORD_WRONG, []);
        $url_array = [url_var::MASK => views::LOGIN_ID];
        $login_html = $ui->url_to_html($url_array, $err_msg, $ui->dto, true);

        $notification_div = '<div class="alert alert-warning notification-bar">';
        $test_name = 'login page with failed login shows notification bar';
        $t->assert_text_contains($test_name, $login_html, $notification_div);

        $expected_msg = msg_id::PASSWORD_WRONG->value;
        $test_name = 'login page notification contains password wrong message';
        $t->assert_text_contains($test_name, $login_html, $expected_msg);
        // the label of a login field is linked to the field, so a click on the label focuses the field
        $test_name = 'login page links the username label to its field';
        $t->assert_text_contains($test_name, $login_html, 'for="username_or_email"');
        $t->assert_text_contains($test_name, $login_html, 'id="username_or_email"');

        // optional: only active when the login password field uses the css-only show-password toggle
        // (form_input_password) instead of the native type=password field that keeps the browser auto fill
        //$test_name = 'login page password field can be revealed';
        //$t->assert_text_contains($test_name, $login_html, html_base::CLASS_SHOW_PASSWORD);
        //$test_name = 'login page password field is masked by the show-password css class';
        //$t->assert_text_contains($test_name, $login_html, html_base::CLASS_INPUT . ' ' . html_base::CLASS_PASSWORD);
        //$test_name = 'login page show-password toggle uses the eye icon';
        //$t->assert_text_contains($test_name, $login_html, icons::PASSWORD_SHOW);

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
        $fail_msg->usr = $usr_sys_ui;
        $result_url = $ui->url_to_action($url_with_back, $t->usr1, $fail_msg, $ui->dto, false);

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
        $signup_html = $ui->url_to_html($url_array, $err_msg, $ui->dto, true);

        $test_name = 'signup page with duplicate name shows notification bar';
        $t->assert_text_contains($test_name, $signup_html, $notification_div);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'signup_notification';
        $test_name = 'signup page with name exists notification matches snapshot';
        $t->assert_html_page($test_name, $signup_html, $file_path);

        // test that url_to_action on logout resets both user objects to anonymous state:
        // the backend user by reference and the frontend user via the message (docs/llm/state-and-messages.md)
        $logout_backend = clone $t->usr1;
        $logout_msg = new user_message();
        $logout_msg->usr = $tl->cast_user($logout_backend);
        $logout_result_url = $ui->url_to_action(
            [url_var::MASK => views::LOGOUT_ID],
            $logout_backend,
            $logout_msg,
            $ui->dto,
            false
        );

        $test_name = 'logout action returns logout view url';
        $t->assert($test_name, $logout_result_url[url_var::MASK] ?? 0, views::LOGOUT_ID);

        $test_name = 'logout action resets backend user to anonymous';
        $t->assert($test_name, $logout_backend->has_db_id(), false);

        $test_name = 'logout action resets the message user to ip-only';
        $t->assert($test_name, $logout_msg->usr->is_ip_only(), true);

        // test that the logout page shows the success message
        global $mtr;
        $url_array = [url_var::MASK => views::LOGOUT_ID];
        $logout_html = $ui->url_to_html($url_array, $msg, $ui->dto, true);

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
        $activate_html = $ui->url_to_html($url_array, $err_msg, $ui->dto, true);

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

        // the mail link names the user by user_to_edit, because u is always the logged-in user
        $url_array = [url_var::MASK => views::LOGIN_ACTIVATE_ID, url_var::USER_TO_EDIT => 1];
        $reset_sent_html = $ui->url_to_html($url_array, $msg, $ui->dto, true);

        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'activate page after reset email shows activation key label';
        $t->assert_text_contains($test_name, $reset_sent_html, $mtr->txt(msg_id::ACTIVATE_SUBMIT), $t::TIMEOUT_LIMIT_PAGE);
        $test_name = '... and says that the password is optional, because the page also confirms the email of a signup';
        $t->assert_text_contains($test_name, $reset_sent_html, $mtr->txt(msg_id::ACTIVATE_PASSWORD_OPTIONAL));
        $test_name = '... and posts the user of the mail link as user_to_edit';
        $ue_field = $html->form_hidden(url_var::USER_TO_EDIT, '1');
        $t->assert_text_contains($test_name, $reset_sent_html, $ue_field);
        $test_name = 'the activate page of an old mail link with the id posts the user as user_to_edit too';
        $t->assert_text_contains($test_name, $activate_html, $ue_field);

        $file_path = test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'reset_email_sent';
        $test_name = 'activate page after reset email matches snapshot';
        $t->assert_html_page($test_name, $reset_sent_html, $file_path);

        // test that the login_reset form renders with a cancel and go back link when no back params are given
        $url_array = [url_var::MASK => views::LOGIN_RESET_ID];
        $reset_form_html = $ui->url_to_html($url_array, $msg, $ui->dto, true);

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
        $html = $ui->url_to_html($url_array, $usr_msg, $ui->dto, true);
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
        $add_html = $ui->url_to_html($add_array, $msg, $ui->dto, true);
        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'add view keeps the hidden id field at 0';
        $t->assert_text_contains($test_name, $add_html, 'name="id" id="id" value="0"', $t::TIMEOUT_LIMIT_PAGE);
        $test_name = 'add view does not stamp the url id onto the new object';
        $t->assert_text_not_contains($test_name, $add_html, 'name="id" id="id" value="1"');

        // the phrase selector of an editor sends the name of the selected phrase, so that the editor is
        // shown again for that phrase (see frontend::url_with_selected_phrase)
        $t->subheader($ts . 'editor phrase selector');
        $t_trp = new test_triples($t);
        $phr = $t_trp->populism_solution_ui()->phrase();
        $dto = new data_object();
        $dto->phr_lst->add($phr, $msg);
        $sel_url = [url_var::MASK => views::TABLE_EDITOR, url_var::ID => -1, url_var::PHRASE => $phr->name()];
        $test_name = 'the selected phrase replaces the phrase of the editor';
        $sel_url_new = $ui->url_with_selected_phrase($sel_url, views::TABLE_EDITOR, $dto, $msg);
        $t->assert($test_name, $sel_url_new[url_var::ID] ?? 0, $phr->id());
        $test_name = '... and the name is not kept as an object value';
        $t->assert($test_name, array_key_exists(url_var::PHRASE, $sel_url_new), false);
        $msg->reset();
        // negative: e.g. the phrase of a reference is not the selection of the page
        $test_name = 'the phrase of another view does not switch the page';
        $t->assert($test_name, $ui->url_with_selected_phrase($sel_url, views::START_CODE, $dto, $msg), $sel_url);
        $msg->reset();
        $test_name = 'an empty selection keeps the phrase of the editor';
        $empty_url = [url_var::MASK => views::TABLE_EDITOR, url_var::ID => -1, url_var::PHRASE => ''];
        $t->assert($test_name, $ui->url_with_selected_phrase($empty_url, views::TABLE_EDITOR, $dto, $msg), $empty_url);
        $msg->reset();

        // the admin only masks (views::ADMIN_MASK_IDS) must be authorized centrally so the admin
        // content is not rendered to just anyone (see frontend::admin_mask_denied)
        $t->subheader($ts . 'admin mask authorization');
        $admin_url = [url_var::MASK => views::ADMIN_MAIN_ID];
        // the translated title of the admin dashboard, which only the admin view shows
        $admin_title = $html->text_h2($mtr->txt(msg_id::FORM_TITLE_ADMIN));

        // negative: an anonymous user is sent to the start view with a permission message and never
        // sees the admin content
        $anon_msg = new user_message();
        $anon_html = $ui->url_to_html($admin_url, $anon_msg, $ui->dto, true);
        // the first assert after a page render carries the render time, so a page timeout is used
        $test_name = 'the admin main view is not rendered for an anonymous user';
        $t->assert_text_not_contains($test_name, $anon_html, $admin_title, $t::TIMEOUT_LIMIT_PAGE);
        $test_name = 'the anonymous user is told that the admin view needs an administrator';
        $t->assert_text_contains($test_name, $anon_html, msg_id::ADMIN_MASK_DENIED->value);

        // positive: an admin (here the system user, see admin_mask_denied) may render the admin view
        $adm_msg = new user_message();
        $adm_msg->usr = $usr_sys_ui;
        $adm_html = $ui->url_to_html($admin_url, $adm_msg, $ui->dto, true);
        $test_name = 'the admin main view is rendered for a system user';
        $t->assert_text_contains($test_name, $adm_html, $admin_title);

        // negative: url_to_action refuses an admin mask action for a non-admin user and returns the
        // start view instead of acting on it (a fresh frontend user has the ip-only profile)
        $act_msg = new user_message();
        $act_backend = clone $t->usr1;
        $act_msg->usr = new user_ui();
        $act_url = $ui->url_to_action($admin_url, $act_backend, $act_msg, $ui->dto, false);
        $test_name = 'url_to_action sends a non-admin admin mask request to the start view';
        $t->assert($test_name, $act_url[url_var::MASK] ?? 0, views::START_ID);

        // the admin dashboard with the critical entries shown directly and the others behind a plus sign
        $this->assert_admin_dashboard($t, $ui, $usr_sys_ui);

        // loop over the system views
        $this->assert_views_by_id($t, $t_map, $ui, $usr_sys_ui, $msg, $lib);

        // restore the normal usr1 so the following tests run with the email profile user again
        $t->usr1 = $usr1_saved;

    }

    /**
     * the admin dashboard with an empty, a mixed and a critical data set: each column shows its critical
     * entries directly and the other entries behind a plus sign, and each data set is a page snapshot
     *
     * @param test_cleanup $t test runner for assertions and user fixtures
     * @param frontend $ui renders HTML from a URL array
     * @param user_ui $usr_sys_ui the admin (here the system user, see frontend::admin_mask_denied)
     */
    private function assert_admin_dashboard(test_cleanup $t, frontend $ui, user_ui $usr_sys_ui): void
    {
        global $mtr;
        $ts = 'unit ui system views ';
        $t->subheader($ts . 'admin dashboard');
        $t_adm = new test_admin_dashboard($t);
        $page = new system_page();
        $msg = new user_message();
        $msg->usr = $usr_sys_ui;
        $now = new DateTime(test_admin_dashboard::SNAP_TIME);
        $no_critical = $mtr->txt(msg_id::ADMIN_NO_CRITICAL);

        // the subtitle names the admin, the pod and the creation time of the page
        $subtitle = $page->admin_subtitle($msg, $now);
        $t->assert_text_contains('the admin dashboard subtitle names the admin', $subtitle, $usr_sys_ui->name());
        $t->assert_text_contains('... the pod', $subtitle, POD_NAME);
        $t->assert_text_contains('... and the time of the data snap', $subtitle, $now->format(Config::DEFAULT_DATE_TIME_FORMAT));
        // negative: another time or a request without a user is not shown as the snap
        $later = new DateTime(test_admin_dashboard::SNAP_TIME)->modify('+1 day');
        $test_name = '... but not another time';
        $t->assert_text_not_contains($test_name, $subtitle, $later->format(Config::DEFAULT_DATE_TIME_FORMAT));
        $test_name = 'the subtitle of a request without a user names no admin';
        $t->assert_text_not_contains($test_name, $page->admin_subtitle(new user_message(), $now), $usr_sys_ui->name());

        // empty: every column says that it has no entry and has no plus sign
        $dto = $t_adm->dto_empty($ui->dto);
        $this->assert_admin_page($t, $ui, $msg, $dto, self::ADMIN_EMPTY_FILE, 'empty');
        $col = $page->admin_url_delay($msg, true, $dto->adm_sys_tim_lst);
        $t->assert_text_contains('an empty response time column says so', $col, $mtr->txt(msg_id::ADMIN_NO_SLOW_REQUESTS));
        $t->assert_text_not_contains('... and has no plus sign', $col, '<details');
        $col = $page->admin_login_fails($msg, true, $dto->adm_sys_log);
        $t->assert_text_contains('an empty failed login column says so', $col, $mtr->txt(msg_id::ADMIN_NO_LOGIN_FAILS));
        $col = $page->admin_errors_unassigned($msg, true, $dto->adm_sys_log);
        $t->assert_text_contains('an empty error column says so', $col, $mtr->txt(msg_id::ADMIN_NO_ERRORS));
        $t->assert_text_not_contains('... and has no plus sign', $col, '<details');
        $col = $page->admin_jobs_delayed($msg, true, $dto->adm_job_lst, $now);
        $t->assert_text_contains('an empty job column says so', $col, $mtr->txt(msg_id::ADMIN_NO_DELAYED_JOBS));
        $t->assert_text_not_contains('... without a table like the other columns', $col, '<table');
        $col = $page->admin_user_load($msg, true, $dto->adm_usr_log_sum_lst);
        $t->assert_text_contains('an empty server time column says so', $col, $mtr->txt(msg_id::ADMIN_NO_USER_LOAD));
        $t->assert_text_not_contains('... and has no plus sign', $col, '<details');

        // mixed: a column without a critical entry says so and shows its entries only behind the plus sign
        $dto = $t_adm->dto_mixed($ui->dto, $msg);
        $this->assert_admin_page($t, $ui, $msg, $dto, self::ADMIN_MIXED_FILE, 'mixed');
        [$direct, $behind] = $this->admin_split($page->admin_errors_unassigned($msg, true, $dto->adm_sys_log));
        $t->assert_text_contains('the unassigned error is shown directly', $direct, test_admin_dashboard::ERR_ERROR_TEXT);
        $t->assert_text_contains('... but the info is behind the plus sign', $behind, test_admin_dashboard::ERR_INFO_TEXT);
        $t->assert_text_not_contains('... and not shown directly', $direct, test_admin_dashboard::ERR_INFO_TEXT);
        [$direct, $behind] = $this->admin_split($page->admin_errors_delayed_fix($msg, true, $dto->adm_sys_log));
        $t->assert_text_contains('a pending fix column without an error says nothing critical', $direct, $no_critical);
        $t->assert_text_contains('... and has the warning behind the plus sign', $behind, test_admin_dashboard::FIX_WARNING_TEXT);
        [$direct, $behind] = $this->admin_split($page->admin_login_fails($msg, true, $dto->adm_sys_log));
        $t->assert_text_contains('a single failed login is not critical', $direct, $no_critical);
        $t->assert_text_contains('... and is behind the plus sign', $behind, test_admin_dashboard::LOGIN_TYPO);
        [$direct, $behind] = $this->admin_split($page->admin_url_delay($msg, true, $dto->adm_sys_tim_lst));
        $t->assert_text_contains('a slow request is shown directly', $direct, test_admin_dashboard::URL_SLOW);
        $t->assert_text_contains('... but a fast one is behind the plus sign', $behind, test_admin_dashboard::URL_FAST);
        [$direct, $behind] = $this->admin_split($page->admin_jobs_delayed($msg, true, $dto->adm_job_lst, $now));
        $t->assert_text_contains('a job that waits less than the max delay is not critical', $direct, $no_critical);
        $fresh_job = new DateTime(test_admin_dashboard::SNAP_TIME)->modify('-1 minute')->format(DateTimeInterface::ATOM);
        $t->assert_text_contains('... and is behind the plus sign', $behind, $fresh_job);
        [$direct, $behind] = $this->admin_split($page->admin_user_load($msg, true, $dto->adm_usr_log_sum_lst));
        $t->assert_text_contains('a user below all thresholds is not critical', $direct, $no_critical);
        $t->assert_text_contains('... and is behind the plus sign', $behind, test_admin_dashboard::LOAD_LIGHT_NAME);
        $test_name = 'the failed logins are not listed as unassigned errors';
        $col = $page->admin_errors_unassigned($msg, true, $dto->adm_sys_log);
        $t->assert_text_not_contains($test_name, $col, test_admin_dashboard::LOGIN_TYPO);

        // critical: every column shows its critical entries directly, the most critical first
        $dto = $t_adm->dto_critical($ui->dto, $msg);
        $this->assert_admin_page($t, $ui, $msg, $dto, self::ADMIN_CRITICAL_FILE, 'critical');
        [$direct, $behind] = $this->admin_split($page->admin_errors_unassigned($msg, true, $dto->adm_sys_log));
        $test_name = 'the fatal error is shown above the error';
        $fatal_pos = strpos($direct, test_admin_dashboard::ERR_FATAL_TEXT);
        $error_pos = strpos($direct, test_admin_dashboard::ERR_ERROR_TEXT);
        $t->assert_true($test_name, $fatal_pos !== false and $error_pos !== false and $fatal_pos < $error_pos);
        $t->assert_text_contains('the warning is behind the plus sign', $behind, test_admin_dashboard::ERR_WARNING_TEXT);
        $t->assert_text_contains('... which names the number of the not critical entries', $behind,
            '2 ' . $mtr->txt(msg_id::ADMIN_NOT_CRITICAL));
        [$direct, $behind] = $this->admin_split($page->admin_errors_delayed_fix($msg, true, $dto->adm_sys_log));
        $t->assert_text_contains('the assigned error is shown directly', $direct, test_admin_dashboard::FIX_ERROR_TEXT);
        $t->assert_text_contains('... but the resolved warning is behind the plus sign', $behind, test_admin_dashboard::FIX_WARNING_TEXT);
        $groups = $dto->adm_sys_log->login_fail_groups();
        $t->assert('the failed logins are grouped by the typed name', count($groups), 2);
        $t->assert('... with the most often tried name first', $groups[0][sys_log_list::GROUP_NAME],
            test_admin_dashboard::LOGIN_ATTACKED);
        $t->assert('... which counts each try', $groups[0][sys_log_list::GROUP_COUNT], sys_log_list::login_fail_limit($msg));
        $t->assert('the other entries are not failed logins', $dto->adm_sys_log->login_fails(false)->count(), 6);
        [$direct, $behind] = $this->admin_split($page->admin_login_fails($msg, true, $dto->adm_sys_log));
        $t->assert_text_contains('the name with the limit of failed logins is shown directly', $direct, test_admin_dashboard::LOGIN_ATTACKED);
        $t->assert_text_not_contains('... but not the typo', $direct, test_admin_dashboard::LOGIN_TYPO);
        $t->assert_text_contains('... which is behind the plus sign', $behind, test_admin_dashboard::LOGIN_TYPO);
        [$direct, $behind] = $this->admin_split($page->admin_url_delay($msg, true, $dto->adm_sys_tim_lst));
        $test_name = 'the slowest request is shown above the slow request';
        $slower_pos = strpos($direct, test_admin_dashboard::URL_SLOWER);
        $slow_pos = strpos($direct, test_admin_dashboard::URL_SLOW);
        $t->assert_true($test_name, $slower_pos !== false and $slow_pos !== false and $slower_pos < $slow_pos);
        $t->assert_text_contains('the fast request is behind the plus sign', $behind, test_admin_dashboard::URL_FAST);
        [$direct, $behind] = $this->admin_split($page->admin_jobs_delayed($msg, true, $dto->adm_job_lst, $now));
        $delayed_job = new DateTime(sys_log_tests::TV_TIME)->format(DateTimeInterface::ATOM);
        $t->assert_text_contains('the job that waits longer than the max delay is shown directly', $direct, $delayed_job);
        $t->assert_text_contains('... but the fresh job is behind the plus sign', $behind, $fresh_job);
        $t->assert_text_not_contains('... and not shown directly', $direct, $fresh_job);
        [$direct, $behind] = $this->admin_split($page->admin_user_load($msg, true, $dto->adm_usr_log_sum_lst));
        $t->assert_text_contains('the user with the request threshold is shown directly', $direct,
            test_admin_dashboard::LOAD_HEAVY_NAME);
        $t->assert_text_contains('... but the user below all thresholds is behind the plus sign', $behind,
            test_admin_dashboard::LOAD_LIGHT_NAME);
        $t->assert_text_not_contains('... and not shown directly', $direct, test_admin_dashboard::LOAD_LIGHT_NAME);
        // one request less than the threshold is not critical, and a single threshold is enough
        $limits = user_log_sum_list::critical_limits($msg);
        $usr_log_sum = new user_log_sum();
        $usr_log_sum->requests = $limits[json_fields::REQUESTS] - 1;
        $t->assert_false('a user one request below the threshold is not critical', $usr_log_sum->is_critical($limits));
        $usr_log_sum->update_ms = $limits[json_fields::UPDATE_MS];
        $t->assert_true('... but a user with the update time threshold is', $usr_log_sum->is_critical($limits));
    }

    /**
     * render the admin dashboard with the given data set and compare it with the page snapshot
     *
     * @param test_cleanup $t test runner for assertions and the list of the written snapshots
     * @param frontend $ui renders HTML from a URL array
     * @param user_message $msg with the admin who requests the page
     * @param data_object $dto the data set of the admin dashboard
     * @param string $file_path the snapshot without the extension relative to the test resources
     * @param string $case_name the name of the data set for the test name
     */
    private function assert_admin_page(
        test_cleanup $t,
        frontend     $ui,
        user_message $msg,
        data_object  $dto,
        string       $file_path,
        string       $case_name
    ): void
    {
        $admin_url = [url_var::MASK => views::ADMIN_MAIN_ID];
        $html = $ui->url_to_html($admin_url, $msg, $dto, true);
        // remember the snapshot, so that the cleanup of the views_by_object snapshots keeps it
        $t->updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
        // link to the pod like the views_by_object snapshots (see test_base::link_to_pod)
        $test_name = 'the admin dashboard ' . $case_name . ' matches the snapshot';
        $t->assert_html_page($test_name, $t->link_to_pod($html, THIS_URL), $file_path);
    }

    /**
     * @param string $col the html code of one admin dashboard column
     * @return array the html code shown directly and the html code behind the plus sign, which is empty if there is none
     */
    private function admin_split(string $col): array
    {
        $parts = explode('<details', $col, 2);
        return [$parts[0], $parts[1] ?? ''];
    }

    /**
     * iterate over all system view ids and assert each rendered page matches its HTML snapshot
     * @param test_cleanup $t test runner for assertions and user fixtures
     * @param test_mappers $t_map builds filled test URLs per class and action
     * @param frontend $ui renders HTML from a URL array
     * @param user_ui $usr_sys_ui logged-in user used for views that require a session
     * @param user_message $msg collects any messages produced during rendering
     * @param library $lib converts class names to file-path segments
     */
    private function assert_views_by_id(
        test_cleanup $t,
        test_mappers $t_map,
        frontend     $ui,
        user_ui      $usr_sys_ui,
        user_message $msg,
        library      $lib
    ): void
    {
        $updated_files = [];
        // the views of the frontend cache come from the api/type_lists/type_lists.json fixture, which reset_db
        // regenerates only after the unit tests; if a view has been added to the seed views since, the cached ids
        // are shifted and a view would be rendered with the object of another view (a fatal type error), so the
        // loop and the snapshot cleanup are skipped until the fixture matches views::TEST_VIEW_IDS again
        $test_name = 'the views of type_lists.json have the ids of views::TEST_VIEW_IDS (else reset the database)';
        $stale = '';
        foreach (views::TEST_VIEW_IDS as $id => $code_id) {
            $cached_code_id = $ui->dto->typ_lst_cache->get_view_by_id($id)?->code_id;
            if ($stale == '' and $cached_code_id != $code_id) {
                $stale = 'view ' . $id . ' is ' . ($cached_code_id ?? 'missing') . ' instead of ' . $code_id;
            }
        }
        $t->assert($test_name, $stale, '');
        if ($stale != '') {
            return;
        }
        // the start view shows the global problems as a table, which the frontend fills from the
        // api; a unit test has no api, so the cache of that view is filled from the factories,
        // and only of that view, so the snapshots of the other views stay unchanged
        $t_phr = new test_phrases($t);
        $t_val = new test_values($t);
        $dto_start = new data_object();
        $dto_start->online = false;
        $dto_start->typ_lst_cache = $ui->dto->typ_lst_cache;
        $dto_start->msk_lst = $ui->dto->msk_lst;
        $dto_start->add_phrases($t_phr->list_global_problems_ui(), $msg);
        // the calculator shows the triple "global warming", whose from and to are named from the cache
        $t_wrd = new test_words($t);
        $gw_parts = new phrase_list($t->usr1);
        $gw_parts->add($t_wrd->word_climate()->phrase());
        $gw_parts->add($t_wrd->word_warmer()->phrase());
        $dto_start->add_phrases(new phrase_list_ui($gw_parts->api_json()), $msg);
        $dto_start->val_lst = $t_val->value_list_solution_prio_ui();
        // a value or a result page names the phrases of its sample object, which a unit test can
        // only take from the request cache, so a copy of that cache also holds these phrases; the
        // phrase list is copied too, so that the other views keep the unchanged cache
        $t_res = new test_results($t);
        $smp_lst = $t_val->value_16_filled()->phrase_list();
        $smp_lst->merge($t_res->result_main_filled()->phrase_list());
        $dto_smp = clone $ui->dto;
        $dto_smp->phr_lst = clone $ui->dto->phr_lst;
        $dto_smp->add_phrases(new phrase_list_ui($smp_lst->api_json()), $msg);
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
                    $msg->usr = $usr_sys_ui;
                } else {
                    $msg->usr = null;
                }
                // the start page, the table and the calculator show the values of the global
                // problems, so they render with the start page cache that carries them
                $dto = $ui->dto;
                if (in_array($id, [views::START_ID, views::TABLE_ID, views::CALCULATOR_ID])) {
                    $dto = $dto_start;
                } elseif ($dbo instanceof value or $dbo instanceof result) {
                    $dto = $dto_smp;
                } elseif ($id == views::ADMIN_MAIN_ID) {
                    // the subtitle of the admin dashboard shows the creation time, so the snapshot uses a fixed one
                    $dto = clone $ui->dto;
                    $dto->snap_time = new DateTime(test_admin_dashboard::SNAP_TIME);
                }
                $html = $ui->url_to_html($url_array, $msg, $dto, true);
                [$folder, $dbo_name, $test_name] = $this->view_id_to_file_info($id, $dbo::class, $action, $url_array, $lib);
                $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
                $updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
                // link to the pod like the views_by_object snapshots, so that a snapshot opened in
                // the ide does not link to the ide preview server port (see test_base::link_to_pod)
                $t->assert_html_page($test_name, $t->link_to_pod($html, THIS_URL), $file_path);
                // each view is an own request, so a message of one view must not stop the next
                // e.g. the value url mapper maps the number only if the message is ok
                $msg->reset();
            }
        }

        // the "... more" link of a list changes only how much of the same page is shown, so the
        // start page renders with the longer list and reports nothing; a list size counted as an
        // object field value would instead report the missing id of the page object, because the
        // start page shows no object (see url_var::CONTROL_VARS)
        $test_name = 'the more list of the start page reports no missing id';
        $msg->usr = null;
        $more_url = [url_var::MASK => views::START_ID, url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST];
        $more_html = $ui->url_to_html($more_url, $msg, $dto_start, true);
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        $test_name = '... and matches the snapshot of the more list';
        $start_dbo = $this->view_id_to_dbo(views::START_ID, $t->usr1);
        [$folder, $dbo_name,] = $this->view_id_to_file_info(views::START_ID, $start_dbo::class,
            $this->view_id_to_url_action(views::START_ID), $more_url, $lib);
        $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name . self::MORE_LIST_FILE_POSTFIX;
        $updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
        $t->assert_html_page($test_name, $t->link_to_pod($more_html, THIS_URL), $file_path);
        $msg->reset();

        // the ", ..." of the reason and the solution cell of the start page link to the table page
        // of "global warming reason" and "global warming solution", which show the reasons or the
        // solutions of global warming as rows; per page the name, the page phrase, the phrase that
        // names its rows, the related phrases and the values
        $t_trp = new test_triples($t);
        $list_pages = [
            ['reasons', $t_trp->global_warming_reason(), $t_wrd->word_reason(),
                $t_phr->list_global_warming_reasons_ui(), $t_val->value_list_solution_prio_reason_solution_ui()],
            ['solutions', $t_trp->global_warming_solution(), $t_wrd->solution(),
                $t_phr->list_global_warming_solutions_with_reasons_ui(), $t_val->value_list_global_warming_solutions_ui()],
        ];
        foreach ($list_pages as [$page_name, $page_trp, $row_wrd, $rel_lst, $val_lst]) {
            // the url names the triple by its phrase id, so a triple mapped with that id would name
            // another phrase and find none of the values
            $test_name = 'the table page of the global warming ' . $page_name . ' shows the table';
            $dto_page = clone $dto_start;
            $dto_page->phr_lst = clone $dto_start->phr_lst;
            $dto_page->add_phrases($rel_lst, $msg);
            // like for the calculator, the from of the page triple is named from the cache, and
            // the table heads its row column by it
            $row_parts = new phrase_list($t->usr1);
            $row_parts->add($row_wrd->phrase());
            $dto_page->add_phrases(new phrase_list_ui($row_parts->api_json()), $msg);
            // the live page loads the default sort order, so the rows are sorted by the loss
            $dto_page->add_phrases($t_phr->list_sort_definitions_ui(), $msg);
            $dto_page->val_lst = $val_lst;
            // the url carries the fields of the phrase, because the page is rendered without a backend call
            $url = $t_map->phrase_page_url(views::TABLE_ID, $page_trp->phrase(), $msg);
            parse_str(parse_url($url)['query'], $page_url);
            $page_html = $ui->url_to_html($page_url, $msg, $dto_page, true);
            $t->assert_text_contains($test_name, $page_html, '<' . html_base::TABLE);
            $test_name = 'the table page of the global warming ' . $page_name . ' matches the snapshot';
            $tbl_dbo = $this->view_id_to_dbo(views::TABLE_ID, $t->usr1);
            [$folder, $dbo_name,] = $this->view_id_to_file_info(views::TABLE_ID, $tbl_dbo::class,
                $this->view_id_to_url_action(views::TABLE_ID), $page_url, $lib);
            $file_path = test_paths::VIEWS_BY_ID . $folder . $dbo_name;
            $updated_files[] = test_paths::RESOURCE . $file_path . test_files::HTML;
            $t->assert_html_page($test_name, $t->link_to_pod($page_html, THIS_URL), $file_path);
            $msg->reset();
        }

        // negative: a field of the object is not a render mode, so a page without an object
        // still reports that the id of the object to change is missing
        $test_name = 'an object field on a page without an object reports the missing id';
        $name_url = [url_var::MASK => views::START_ID, url_var::NAME => word_names::MATH];
        $ui->url_to_html($name_url, $msg, $dto_start, true);
        $t->assert_false($test_name, $msg->is_ok());
        $msg->reset();

        // remove test files not used any more, except the details list fetched from the pod
        $updated_files[] = test_paths::RESOURCE . self::START_PAGE_DETAILS_FILE . test_files::HTML;
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
            $result = [self::START_PAGE_FILE_NAME . DIRECTORY_SEPARATOR,
                $prefix . self::START_PAGE_FILE_NAME, self::START_PAGE_FILE_NAME . ' view'];
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
        } elseif (in_array($view_id, view_shared::CHANGE_LOG_VIEW_IDS)) {
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