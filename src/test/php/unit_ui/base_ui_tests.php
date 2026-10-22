<?php

/*

    test/unit/html.php - unit testing of the html code generating functions
    ------------------


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
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\group_id_url;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\value\value_list as value_list_ui;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::API_OBJECT . 'controller.php';
include_once paths::SHARED_TYPES . 'component_types.php';
include_once paths::SHARED_TYPES . 'formula_types.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_HELPER . 'Config.php';
include_once html_paths::VALUE . 'value_list.php';
include_once html_paths::COMPONENT . 'component_exe.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::RESULT . 'result_list.php';
include_once html_paths::VERB . 'verb_list.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'table_orders.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\api\controller;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\helper\url_mapper;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\table_orders;
use Zukunft\ZukunftCom\main\php\shared\enum\user_profiles;
use Zukunft\ZukunftCom\main\php\shared\types\component_types;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\component_types as comp_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\formula_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_mappers;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class base_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $mtr;
        global $ui_sys;

        $lib = new library();
        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $t_src = new test_sources($t);
        $t_frm = new test_formulas($t);
        $msg = new user_message();
        $msg_ui = new user_message_ui();

        // start the test section (ts)
        $ts = 'unit ui html base ';
        $t->header($ts);

        $t->subheader($ts . 'cached page routing');

        // a view-only request is cached by the canonical view and object key
        $ui = new frontend();
        $test_name = 'a view-only request is cached by the view and object key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the language is part of the cache key if set
        $test_name = 'the language is part of the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::LANGUAGE => 'de'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2&' . url_var::LANGUAGE . '=de');

        // the user page names its user by user_to_edit (see url_var::id_var), so both urls share one cached page
        $test_name = 'the user page is cached by the user to edit';
        $url_array = [url_var::MASK => views::USER_ID, url_var::USER_TO_EDIT => users::SYSTEM_TEST_NORMAL_ID];
        $usr_key = 'm=' . views::USER_ID . '&id=' . users::SYSTEM_TEST_NORMAL_ID;
        $t->assert($test_name, $ui->url_cache_key($url_array), $usr_key);
        $test_name = '... like by the id of an old link';
        $url_array = [url_var::MASK => views::USER_ID, url_var::ID => users::SYSTEM_TEST_NORMAL_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), $usr_key);
        $test_name = 'the user to edit does not select the object of another view';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::USER_TO_EDIT => users::SYSTEM_TEST_NORMAL_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');
        $test_name = 'the logged-in user of a shared link is ignored for the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::USER => users::SYSTEM_TEST_NORMAL_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the page of a logged in user is personal (the user name, the black add and edit icons and
        // the user values), so it is cached per user: the session user, not the user of the url,
        // names the page, so the personal page is never handed to somebody else
        $test_name = 'the page of a logged in user is cached under the id of that user';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array, users::SYSTEM_TEST_NORMAL_ID),
            'm=' . views::WORD_ID . '&id=2&' . url_var::USER . '=' . users::SYSTEM_TEST_NORMAL_ID);
        $test_name = '... and the page of a request without login names no user';
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');
        $test_name = '... so the two users of one url have two cached pages';
        $t->assert_false($test_name, $ui->url_cache_key($url_array, users::SYSTEM_TEST_NORMAL_ID)
            == $ui->url_cache_key($url_array, users::SYSTEM_TEST_ID));
        $test_name = 'a request that is never cached is not cached for a logged in user either';
        $chg_url = [url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($chg_url, users::SYSTEM_TEST_NORMAL_ID), '');

        // a request of a view that changes data is never cached
        $test_name = 'a change mask request is not cached';
        $url_array = [url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // the admin dashboard shows the live state of the pod, so it is always created again
        $test_name = 'the admin dashboard is not cached';
        $url_array = [url_var::MASK => views::ADMIN_MAIN_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array, users::SYSTEM_TEST_ID), '');
        // negative: another system view is still cached
        $test_name = '... but the settings page is cached';
        $url_array = [url_var::MASK => views::SYSTEM_CONFIG_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::SYSTEM_CONFIG_ID . '&id=0');

        // a request with a form submission is never cached
        $test_name = 'a post request is not cached';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::POST_SUBMIT => 'Save'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a process step of 0 (no action started) does not change a view-only page, so it is ignored
        // and the request still hits the same cache key as the bare view (e.g. view.php?m=1&z=0)
        $test_name = 'a show step (z=0) is ignored for the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::STEP => url_var::STEP_BASE];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the anti-csrf token is per session and does not change a view-only page, so it too is
        // ignored and the request still hits the same cache key (e.g. view.php?m=1&z=0&token=...)
        $test_name = 'the anti-csrf token is ignored for the cache key';
        $url_array = [
            url_var::MASK => views::WORD_ID, url_var::ID => 2,
            url_var::STEP => url_var::STEP_BASE, url_var::SESSION_TOKEN => 'abc'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the back part only names the page to return to, so a page is cached once for every page
        // that links to it, e.g. the result page called from the start page with the main columns
        $test_name = 'the back part is ignored for the cache key';
        $url_array = [
            url_var::MASK => views::WORD_ID, url_var::ID => 2,
            url_var::BACK . url_var::MASK => views::START_ID,
            url_var::BACK . url_var::DISPLAY_LIST_COLUMNS => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');
        // negative: a back part does not make a change request cacheable
        $test_name = '... but a change mask request with a back part is still not cached';
        $url_array = [
            url_var::MASK => views::WORD_ADD_DETAIL_ID, url_var::ID => 2,
            url_var::BACK . url_var::MASK => views::START_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a non-zero process step is an action step, so the request is rendered live and not cached
        $test_name = 'a non-zero step request is not cached';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::STEP => url_var::STEP_CONFIRM];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // a logged in (non-ip) user gets a personalised page (e.g. the dark blue person icon, the
        // logout link and the my tab), which is cached under the id of that user, so the shared
        // page of a request without login is never served to a logged in user; the session and
        // never the url names the user, because the cache fast path runs before the type cache
        // needed for a profile based check is loaded
        $test_name = 'a logged in user never gets the shared cached page';
        $_SESSION[url_var::SESSION_LOGGED] = true;
        $_SESSION[url_var::SESSION_USER_ID] = users::SYSTEM_TEST_NORMAL_ID;
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $t->assert_false($test_name, $ui->url_cache_key($url_array) == $ui->url_cache_key($url_array,
                users::SYSTEM_TEST_NORMAL_ID));
        $test_name = '... but the personal page of that user is cached';
        $t->assert($test_name, $ui->url_cache_key($url_array, users::SYSTEM_TEST_NORMAL_ID),
            'm=' . views::WORD_ID . '&id=2&' . url_var::USER . '=' . users::SYSTEM_TEST_NORMAL_ID);
        unset($_SESSION[url_var::SESSION_LOGGED], $_SESSION[url_var::SESSION_USER_ID]);

        // the debug level only controls out-of-band debug output, not the cached html, so it is
        // ignored and ?m=2&debug=6 takes the same cached path as ?m=2 (same cache key)
        $test_name = 'the debug level is ignored for the cache key';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2,
            url_var::DEBUG => url_var::DEBUG_LEVEL_DB_READ];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // 'nc=1' switches the html page cache off, so the page is rendered live and the
        // result is not written to the cache (e.g. view.php?m=1&id=2&nc=1)
        $test_name = 'nc=1 bypasses the cache read and write';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::NO_CACHE => url_var::NO_CACHE_ON];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // any other value keeps the cache on, so the request hits the same cache key as the bare url
        $test_name = 'nc=0 keeps the cache on';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::NO_CACHE => '0'];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the human-readable 'nocache' is mapped to the short 'nc' before the cache key is created,
        // so view.php?mask_id=word&id=2&nocache=1 bypasses the cache just like the short url
        $test_name = 'nocache is mapped to the short nc';
        $url_map = new url_mapper();
        $url_msg = new user_message_ui();
        $url_array = [url_var::MASK_HUMAN => views::WORD, url_var::ID => 2,
            url_var::NO_CACHE_HUMAN => url_var::NO_CACHE_ON];
        $url_std = $url_map->url_to_standard($url_array, $url_msg);
        $t->assert($test_name, $url_std[url_var::NO_CACHE] ?? '', url_var::NO_CACHE_ON);
        $t->assert($test_name . ' and bypasses the cache', $ui->url_cache_key($url_std), '');

        // the login and signup form pages are served from the page cache even though they are process
        // masks, because the plain form is static (the per-session token is restored per request)
        $test_name = 'the login form is cached';
        $url_array = [url_var::MASK => views::LOGIN_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::LOGIN_ID . '&id=0');

        $test_name = 'the signup form is cached';
        $url_array = [url_var::MASK => views::SIGNUP_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::SIGNUP_ID . '&id=0');

        // the form carries the back part as hidden fields to return to the calling page after the login
        $test_name = 'the login form with a back part is not cached';
        $url_array = [url_var::MASK => views::LOGIN_ID,
            url_var::BACK . url_var::MASK => views::WORD_ID, url_var::BACK . url_var::ID => 2];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');
        $test_name = 'the signup form with a back part is not cached';
        $url_array = [url_var::MASK => views::SIGNUP_ID, url_var::BACK . url_var::MASK => views::WORD_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');
        $test_name = 'a word view with a back part is still cached';
        $url_array = [url_var::MASK => views::WORD_ID, url_var::ID => 2, url_var::BACK . url_var::MASK => views::START_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::WORD_ID . '&id=2');

        // the login submit is a post action, so only the form GET is cached, never the submission
        $test_name = 'the login submit is not cached';
        $url_array = [url_var::MASK => views::LOGIN_ID, url_var::POST_SUBMIT => 'Login'];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // another process step mask that is not a login or signup form stays excluded from the cache
        $test_name = 'a non-login process step view is still not cached';
        $url_array = [url_var::MASK => views::EXPORT_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), '');

        // the start page is cached; the bare landing page (no view) and an explicit start request
        // share the same start view cache key, because a request without a view shows the start view
        $test_name = 'the explicit start view is cached';
        $url_array = [url_var::MASK => views::START_ID];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0');

        $test_name = 'the bare landing page is cached under the start view key';
        $url_array = [];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0');

        // the more version of a list is a view-only state of the same page, so it is cached under
        // its own key (e.g. view.php?m=1&dls=20) instead of being rendered live on every request
        $test_name = 'the more list of the start page is cached under its own key';
        $url_array = [url_var::MASK => views::START_ID, url_var::DISPLAY_LIST_SIZE => Config::LIMIT_MORE_LIST];
        $t->assert($test_name, $ui->url_cache_key($url_array),
            'm=' . views::START_ID . '&id=0&' . url_var::DISPLAY_LIST_SIZE . '=' . Config::LIMIT_MORE_LIST);
        $test_name = 'a page of the all list is cached under its own key';
        $url_array = [url_var::MASK => views::START_ID, url_var::DISPLAY_LIST_PAGE => 1];
        $t->assert($test_name, $ui->url_cache_key($url_array),
            'm=' . views::START_ID . '&id=0&' . url_var::DISPLAY_LIST_PAGE . '=1');
        // the full table with every column and the variance is a view-only state like the list size
        $test_name = 'the full table of the start page is cached under its own key';
        $url_array = [url_var::MASK => views::START_ID,
            url_var::DISPLAY_LIST_COLUMNS => value_list_ui::COLUMN_TIERS_ALL,
            url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0&'
            . url_var::DISPLAY_LIST_COLUMNS . '=' . value_list_ui::COLUMN_TIERS_ALL . '&'
            . url_var::DISPLAY_LIST_VARIANCE . '=' . url_var::TRUE);
        // the order of the rows is a view-only state as well, so each order is cached on its own
        $test_name = 'the sorted table of the start page is cached under its own key';
        $prime_order = table_orders::NUMERIC_ASC->url_value(12);
        $sub_order = table_orders::ALPHA_DESC->url_value(-13);
        $url_array = [url_var::MASK => views::START_ID,
            url_var::DISPLAY_LIST_ORDER => $prime_order,
            url_var::DISPLAY_LIST_ORDER_SUB => $sub_order];
        $t->assert($test_name, $ui->url_cache_key($url_array), 'm=' . views::START_ID . '&id=0&'
            . url_var::DISPLAY_LIST_ORDER . '=' . $prime_order . '&'
            . url_var::DISPLAY_LIST_ORDER_SUB . '=' . $sub_order);

        // how much of a list is shown is a render mode, so it is a control var: else the "... more"
        // link of a page without an object (e.g. view.php?m=1&dls=20) would look like a form submit
        // and the mapping of the page object would report the missing id (see frontend::url_object_values)
        $test_name = 'the list size is a control var';
        $t->assert_true($test_name, in_array(url_var::DISPLAY_LIST_SIZE, url_var::CONTROL_VARS));
        $test_name = '... like the list page, the columns and the variance';
        $t->assert_true($test_name, in_array(url_var::DISPLAY_LIST_PAGE, url_var::CONTROL_VARS)
            and in_array(url_var::DISPLAY_LIST_COLUMNS, url_var::CONTROL_VARS)
            and in_array(url_var::DISPLAY_LIST_VARIANCE, url_var::CONTROL_VARS));
        $test_name = '... and the three row orders, which the page links repeat as page vars';
        $t->assert($test_name, array_diff(table_orders::URL_VARS, url_var::CONTROL_VARS), []);
        $t->assert($test_name, array_diff(table_orders::URL_VARS, url_var::PAGE_VARS), []);
        $test_name = 'a url with dlv=1 shows the variance';
        $variance_on = [url_var::DISPLAY_LIST_VARIANCE => url_var::TRUE];
        $t->assert_true($test_name, url_var::show_variance($variance_on));
        $test_name = 'a url with dlv=0 hides the variance although the default shows it';
        $variance_off = [url_var::DISPLAY_LIST_VARIANCE => url_var::FALSE];
        $t->assert_false($test_name, url_var::show_variance($variance_off, true));
        $test_name = 'a url without dlv returns the default';
        $t->assert_false($test_name, url_var::show_variance([]));
        $t->assert_true($test_name, url_var::show_variance([], true));
        // e.g. view.php?m=126&1=calculation shows one top level key of the settings and no object
        $test_name = 'the search and the scope of a page are control vars';
        $t->assert_true($test_name, in_array(url_var::PATTERN, url_var::CONTROL_VARS)
            and in_array(url_var::CONFIG_PART, url_var::CONTROL_VARS));
        $test_name = 'a field of the object is never a control var';
        $t->assert_false($test_name, in_array(url_var::NAME, url_var::CONTROL_VARS));

        $t->subheader($ts . 'tab box');

        // the tab box switches via the url fragment with pure css (:target) and no javascript: the
        // 'Changes' label gives the pane id 'changes' and the nav link href '#changes'
        $test_name = 'tab_box switches tabs via the url fragment';
        $two_tabs = $html->tab_box(['View' => 'view content', 'Changes' => 'changes content']);
        $t->assert_text_contains($test_name, $two_tabs, html_base::HREF . '="#changes"');
        $t->assert_text_contains($test_name, $two_tabs, html_base::ID . '="changes"');

        // the target is a pure html frontend, so the tab box must not contain javascript
        $test_name = 'tab_box contains no javascript';
        $t->assert_text_not_contains($test_name, $two_tabs, '<script');
        // negative: an empty change log or view list shows no empty tab
        $test_name = 'a tab box without content shows no tab';
        $t->assert($test_name, $html->tab_box(['View' => '', 'Changes' => '']), '');

        $t->subheader($ts . 'navbar');

        // the person icon in the top right corner is shown in dark blue (styles::USER_LOGGED)
        // if a non-ip user is logged in, i.e. if the navbar gets a user name
        $test_name = 'the person icon shows the logged in state in dark blue';
        $navbar_logged = $html->navbar(0, [], 'test user');
        $t->assert_text_contains($test_name, $navbar_logged, icons::USER_CIRCLE . ' ' . styles::USER_LOGGED);

        $test_name = 'without a logged in user the person icon keeps the default color';
        $navbar_anon = $html->navbar(0, []);
        $t->assert_text_not_contains($test_name, $navbar_anon, styles::USER_LOGGED);

        // the user menu of an admin offers the admin dashboard
        $dashboard_par = url_var::MASK . '=' . views::ADMIN_MAIN_ID;
        $test_name = 'the user menu of an admin links to the admin dashboard';
        $navbar_admin = $html->navbar(0, [], 'test admin', null, true);
        $t->assert_text_contains($test_name, $navbar_admin, $dashboard_par);
        // negative: a normal user cannot open the dashboard, so the link is not offered
        $test_name = '... but not the user menu of a normal user';
        $t->assert_text_not_contains($test_name, $navbar_logged, $dashboard_par);

        // the edit menu of an admin offers the server admin page as last entry
        $test_name = 'the edit menu of an admin links to the server admin page';
        $t->assert_text_contains($test_name, $navbar_admin, api::SERVER_ADMIN_SCRIPT);
        // negative: the server admin page is not offered to a normal user or a user without login
        $test_name = '... but not the edit menu of a normal user';
        $t->assert_text_not_contains($test_name, $navbar_logged, api::SERVER_ADMIN_SCRIPT);
        $test_name = '... and not the edit menu without login';
        $t->assert_text_not_contains($test_name, $navbar_anon, api::SERVER_ADMIN_SCRIPT);

        // the settings are the read only system configuration, which an ip user without login can see too
        $test_name = 'the settings link of a request without login is the short settings path';
        $t->assert_text_contains($test_name, $navbar_anon, api::SETTINGS_REL . '"');
        $test_name = '... whose forward page opens the system configuration';
        $t->assert($test_name, api::FORWARD_VIEWS[api::SETTINGS_REL], views::SYSTEM_CONFIG_ID);
        // negative: the link does not name the view id, which changes if the views are re-pinned
        $test_name = '... without the view id in the navbar';
        $t->assert_text_not_contains($test_name, $navbar_anon, url_var::MASK . '=' . views::SYSTEM_CONFIG_ID . '"');

        // the static forward pages in the docroot open the view of api::FORWARD_VIEWS, so a changed
        // view id is written to the page by the next run with AUTO_UPDATE_TEST_FILES
        $test_name = 'each forward page with a view is listed as a file';
        $t->assert($test_name, array_keys(test_files::FORWARD_PAGES), array_keys(api::FORWARD_VIEWS));
        foreach (test_files::FORWARD_PAGES as $forward => $file) {
            $msk_id = api::FORWARD_VIEWS[$forward] ?? 0;
            $test_name = 'the forward page ' . $forward . ' opens the view ' . $msk_id . ' with the url vars of the request';
            $t->assert_file($test_name, $html->forward_page($msk_id), $file);
        }
        // negative: the session decides who the user is, so the static page never names a user
        $test_name = 'a forward page names no user';
        $t->assert_text_not_contains($test_name, $html->forward_page(views::SYSTEM_CONFIG_ID), url_var::USER . url_var::EQ);

        // the pinned views of config.yaml are linked on the static start page and each has a static
        // forward page by its url name, which the next run with AUTO_UPDATE_TEST_FILES writes
        $yaml = yaml_parse_file(files_shared::CONFIG_YAML);
        $pin_lst = $yaml[words::THIS_SYSTEM][triples::SYSTEM_CONFIG][words::SYSTEM][words::VIEW]
            [words::STARTUP][triples::PINNED_VIEWS] ?? [];
        unset($pin_lst[words::TOOLTIP_COMMENT]);
        // e.g. a moved yaml key would else skip every test of the pinned views silently
        $test_name = 'config.yaml has pinned views';
        $t->assert_greater_zero($test_name, count($pin_lst));
        foreach ($pin_lst as $url_name => $pin) {
            $url = $pin[words::URL] ?? '';
            $test_name = 'the short url ' . $url_name . ' opens ' . $url;
            $t->assert_file($test_name, $html->forward_url_page($url), test_files::PINNED_PAGE_PATH . $url_name);
        }
        $test_name = 'the start page links the pinned views of config.yaml';
        $start_page = file_get_contents(test_files::START_PAGE);
        $t->assert_file($test_name, $html->start_page($start_page, $pin_lst), test_files::START_PAGE);
        $test_name = 'a pinned view is linked by its name with the tooltip';
        $pin = array_slice($pin_lst, 0, 1, true);
        $pin_fields = reset($pin) ?: [];
        $page_one = $html->start_page($html::PINNED_VIEWS_BEGIN . $html::PINNED_VIEWS_END, $pin);
        $t->assert_text_contains($test_name, $page_one, '>' . ($pin_fields[words::NAME] ?? '') . '<');
        $t->assert_text_contains($test_name, $page_one, 'title="' . ($pin_fields[words::TOOLTIP] ?? '') . '"');
        // negative: the static page is the same for every user, so a link never names the user
        $test_name = '... and without the user';
        $t->assert_text_not_contains($test_name, $page_one, url_var::USER . url_var::EQ);
        $test_name = 'the short url page opens the url with the escaped url vars';
        $url_one = $pin_fields[words::URL] ?? '';
        $forward_one = $html->forward_url_page($url_one);
        $t->assert_text_contains($test_name, $forward_one, htmlspecialchars($url_one, ENT_QUOTES));
        // negative: the short url opens a fixed url, so it needs no javascript to keep the url vars
        $test_name = '... without javascript';
        $t->assert_text_not_contains($test_name, $forward_one, '<script');

        // the docroot serves an extensionless file only if its name is in the allow-list of the .htaccess
        $htaccess = file_get_contents(test_files::HTACCESS);
        preg_match('/<FilesMatch "\^\(([^)]*)\)\$">/', $htaccess, $allowed);
        $allow_lst = explode('|', $allowed[1] ?? '');
        $forward_lst = array_map(fn($forward) => ltrim($forward, DIRECTORY_SEPARATOR), array_keys(api::FORWARD_VIEWS));
        $test_name = 'the .htaccess allows each forward page';
        $t->assert($test_name, array_values(array_diff($forward_lst, $allow_lst)), []);
        $test_name = '... and each short url of a pinned view';
        $t->assert($test_name, array_values(array_diff(array_keys($pin_lst), $allow_lst)), []);

        // the login link forwards a '9'-prefixed back target of the current page (the logout
        // page carries the original page as back target, see frontend::action_logout), so after
        // the login the original page is shown again and not the logout page
        $test_name = 'the login link forwards the back target of the logout page';
        $logout_page_url = [
            url_var::MASK => (string)views::LOGOUT_ID,
            url_var::BACK . url_var::MASK => (string)views::WORD_ID,
            url_var::BACK . url_var::ID => '347',
        ];
        $navbar_logout_page = $html->navbar(views::LOGOUT_ID, $logout_page_url);
        $t->assert_text_contains($test_name, $navbar_logout_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID);
        $t->assert_text_contains($test_name, $navbar_logout_page, url_var::BACK . url_var::ID . '=347');

        $test_name = '... and never the logout page as its own back target';
        $t->assert_text_not_contains($test_name, $navbar_logout_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::LOGOUT_ID);

        // the back button of the logout page returns to the last normal page and never to a change form
        $test_name = 'the last normal page of a normal page is the page itself';
        $val_page = [url_var::MASK => views::VALUE_DEFAULT_ID, url_var::ID => values::PI_ID];
        $val_url = $html->page_url($val_page);
        $t->assert($test_name, $html->page_url(html_base::normal_page_array($val_page)), $val_url);
        $test_name = '... of a confirm page is the page it has been opened from';
        $cfm_page = [
            url_var::MASK => views::CONFIRM_EDIT_ID,
            url_var::ID => values::PI_ID,
            url_var::ORIGIN_MASK => views::VALUE_EDIT_ID,
            url_var::BACK . url_var::MASK => views::VALUE_DEFAULT_ID,
            url_var::BACK . url_var::ID => values::PI_ID
        ];
        $t->assert($test_name, $html->page_url(html_base::normal_page_array($cfm_page)), $val_url);
        $test_name = '... and of a change form without a back target the default view of its object';
        $edit_page = [url_var::MASK => views::VALUE_EDIT_ID, url_var::ID => values::PI_ID];
        $t->assert($test_name, $html->page_url(html_base::normal_page_array($edit_page)), $val_url);
        $test_name = 'the logout link of a confirm page points back to the last normal page';
        $navbar_cfm = $html->navbar(views::CONFIRM_EDIT_ID, $cfm_page, 'test user');
        // only the logout link is checked, because e.g. the settings link returns to the confirm page itself
        $logout_back = api::LOGOUT_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=';
        $t->assert_text_contains($test_name, $navbar_cfm, $logout_back . views::VALUE_DEFAULT_ID);
        $t->assert_text_not_contains($test_name, $navbar_cfm, $logout_back . views::CONFIRM_EDIT_ID);
        $test_name = 'the logout page shows a back button to the last normal page';
        $logout_body = new system_page()->logout_body([
            url_var::MASK => views::LOGOUT_ID,
            url_var::BACK . url_var::MASK => views::VALUE_EDIT_ID,
            url_var::BACK . url_var::ID => values::PI_ID
        ]);
        $t->assert_text_contains($test_name, $logout_body, htmlspecialchars($val_url));
        $t->assert_text_contains($test_name, $logout_body, $mtr->txt(msg_id::FORM_FIELD_BACK));
        // negative: without a back target the back button leads to the start page, which names no view
        $test_name = '... and without a back target to the start page';
        $t->assert_text_not_contains($test_name, new system_page()->logout_body(), url_var::PAR . url_var::MASK . url_var::EQ);

        $test_name = 'on a normal page the login link uses the page as the back target';
        $navbar_normal_page = $html->navbar(views::WORD_ID, [
            url_var::MASK => (string)views::WORD_ID,
            url_var::ID => '347',
        ]);
        $t->assert_text_contains($test_name, $navbar_normal_page,
            api::LOGIN_SCRIPT . '&amp;' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID);

        // the edit menu of a view with an editor (a view relation of the editor type) opens the
        // editor with the same object, e.g. the table editor for the table of a phrase
        $test_name = 'the edit menu of a view with an editor opens the editor with the same object';
        $table_page = [url_var::MASK => (string)views::TABLE_ID, url_var::ID => (string)word_names::MATH_ID];
        $navbar_table = $html->navbar(views::TABLE_ID, $table_page, null, null, false,
            views::TABLE_EDITOR_ID, views::TABLE_EDITOR_NAME);
        $t->assert_text_contains($test_name, $navbar_table,
            url_var::MASK . '=' . views::TABLE_EDITOR_ID . '&amp;' . url_var::ID . '=' . word_names::MATH_ID);
        $t->assert_text_contains($test_name, $navbar_table, '>' . views::TABLE_EDITOR_NAME . '<');
        $test_name = '... and returns to the table';
        $t->assert_text_contains($test_name, $navbar_table, url_var::BACK . url_var::MASK . '=' . views::TABLE_ID);
        $alternative = '>' . $mtr->txt(msg_id::NAVBAR_ALTERNATIVE_VIEW) . '<';
        $test_name = '... instead of the alternative views';
        $t->assert_text_not_contains($test_name, $navbar_table, $alternative);
        $test_name = 'the edit menu of a view without an editor offers the alternative views';
        $t->assert_text_contains($test_name, $navbar_normal_page, $alternative);
        // like the edit icons behind the title the editor of a user without login is grey and asks for a login
        $usr_known = $ui_sys->usr;
        $ip_ui = new user_ui();
        $ip_ui->api_mapper([
            json_fields::ID => users::TEST_USER_ID,
            json_fields::PROFILE_ID => $ui_sys->typ_lst_cache->usr_pro->id(user_profiles::IP_ONLY)], $msg_ui);
        $ui_sys->usr = $ip_ui;
        $navbar_table_ip = $html->navbar(views::TABLE_ID, $table_page, null, null, false,
            views::TABLE_EDITOR_ID, views::TABLE_EDITOR_NAME);
        $ui_sys->usr = $usr_known;
        $test_name = 'the editor in the edit menu of a user without login is grey';
        $t->assert_text_contains($test_name, $navbar_table_ip, styles::STYLE_GREY);
        $test_name = '... and asks for a login';
        $t->assert_text_contains($test_name, $navbar_table_ip, $mtr->txt(msg_id::CHANGE_LOGIN_REQUIRED));
        $test_name = 'the editor in the edit menu of a known user is not grey and asks for nothing';
        $t->assert_text_not_contains($test_name, $navbar_table, styles::STYLE_GREY);
        $t->assert_text_not_contains($test_name, $navbar_table, $mtr->txt(msg_id::CHANGE_LOGIN_REQUIRED));

        $t->subheader($ts . 'login');

        $created_html = $html->about_page($msg_ui);
        $expected_html = $t->file(test_paths::HTML . test_paths::VIEW_FUNCTIONS . 'about.html');
        $t->assert('about', $lib->trim_html($created_html), $lib->trim_html($expected_html));
        // the about view shows the about text inside the main element, so only the page closes the body
        $test_name = 'the about text does not close the page';
        $t->assert_text_not_contains($test_name, $html->about_body(), '</' . html_base::BODY . '>');
        $test_name = '... but the about page does';
        $t->assert_text_contains($test_name, $created_html, '</' . html_base::BODY . '>');


        $t->subheader($ts . 'selector');

        // TODO Prio 2 test the creation of a phrase list API JSON
        // TODO Prio 2 create a selector using a list an with a simple test page header an footer
        /*
        $sel = new html_selector();
        $sel->label = 'Test:';
        $sel->name = 'test_selector';
        $sel->form = 'test_form';
        $sel_lst = array();
        $sel_lst[1] = 'First';
        $sel_lst[2] = 'Second';
        $sel_lst[3] = 'Third (selected)';
        $sel_lst[4] = 'Fourth';
        $sel->lst = $sel_lst;
        $sel->selected = 3;
        $body = $html->form_start($sel->form);
        $body .= $sel->display_old();
        $body .= $html->form_end_with_submit($sel->name, []);
        $t->html_test($body, '', 'selector', $t);
        */

        // ... and check if the prepared sql name is unique
        //$t->assert_sql_name_unique($log_ui->dsp_hist_links_sql($db_con, true));

        // button add
        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $t->html_page_test(new button($url)->add(msg_id::WORD_ADD), '', 'button_add', $msg_ui);

        $t->subheader($ts . 'form field name and id');

        // the field name is the url var (the submitted key), the id is the user-readable
        // label; the label text must never become the submitted name (see field_id / url_mapper)
        global $mtr;
        $name_label = $mtr->txt(msg_id::FORM_FIELD_NAME);
        $name_id = strtolower($name_label);

        $test_name = 'text field submits the url var as name';
        $field = $html->input(url_var::NAME, msg_id::FORM_FIELD_NAME, 'math', html_base::INPUT_TEXT);
        $t->assert_text_contains($test_name, $field, 'name="' . url_var::NAME . '"');

        $test_name = '... and uses the readable label as the id';
        $t->assert_text_contains($test_name, $field, 'id="' . $name_id . '"');

        $test_name = '... never the label as the name nor the url var as the id';
        $t->assert_text_not_contains($test_name, $field, 'name="' . $name_label . '"');
        $t->assert_text_not_contains($test_name, $field, 'id="' . url_var::NAME . '"');

        // a second form on the same page suffixes the url var (e.g. '_add'); the id must
        // carry the same suffix so it stays unique next to the un-suffixed edit form field
        $test_name = 'suffixed url var keeps the id unique';
        $field_add = $html->input(url_var::NAME . '_add', msg_id::FORM_FIELD_NAME, '', html_base::INPUT_TEXT);
        $t->assert_text_contains($test_name, $field_add, 'name="' . url_var::NAME . '_add"');
        $t->assert_text_contains($test_name, $field_add, 'id="' . $name_id . '_add"');

        $test_name = 'add and edit field ids differ on the same page';
        $t->assert_text_not_contains($test_name, $field_add, 'id="' . $name_id . '"');

        // form_field pairs a <label for> with the input id; both must use the same field_id
        $test_name = 'form_field links label for to the input id';
        $labelled = $html->form_field(url_var::NAME, msg_id::FORM_FIELD_NAME, 'math');
        $t->assert_text_contains($test_name, $labelled, 'for="' . $name_id . '"');
        $t->assert_text_contains($test_name, $labelled, 'id="' . $name_id . '"');

        $test_name = '... and keeps the pair unique for a suffixed field';
        $labelled_add = $html->form_field(url_var::NAME . '_add', msg_id::FORM_FIELD_NAME, '');
        $t->assert_text_contains($test_name, $labelled_add, 'for="' . $name_id . '_add"');
        $t->assert_text_contains($test_name, $labelled_add, 'id="' . $name_id . '_add"');

        // the plain input of the simple forms (login, signup, ...) links its label to the field the same way
        $test_name = 'form_input links the label to the input id';
        $plain = $html->form_input(html_base::INPUT_TEXT, url_var::USERNAME_HUMAN, '', 'username or email');
        $t->assert_text_contains($test_name, $plain, '<label for="username_or_email">username or email</label>');
        $t->assert_text_contains($test_name, $plain, 'id="username_or_email"');
        $t->assert_text_contains($test_name, $plain, 'name="' . url_var::USERNAME_HUMAN . '"');
        // negative: a field without a label gets neither a label nor an id
        $test_name = '... and a field without a label has no label tag and no id';
        $plain_no_label = $html->form_input(html_base::INPUT_NUMBER, url_var::NUMERIC_VALUE, '3');
        $t->assert_text_not_contains($test_name, $plain_no_label, '<label');
        $t->assert_text_not_contains($test_name, $plain_no_label, ' id="');

        // an error text is an inline text with the danger style that keeps its html e.g. the <b> of a message
        $test_name = 'an error text is a red inline text';
        $err_html = $html->dsp_err('data <b>may get lost</b>');
        $t->assert($test_name, $err_html, '<span class="' . styles::TEXT_DANGER . '">data <b>may get lost</b></span>');
        // negative: a <style> tag is a stylesheet and the browser does not show its text
        $test_name = '... and never a style tag';
        $t->assert_text_not_contains($test_name, $err_html, '<style');
        // negative: the text is shown as it is, so no tag is added to a plain text
        $test_name = '... and a plain error text gets no tag added';
        $plain_err_html = $html->dsp_err('plain text');
        $t->assert_text_contains($test_name, $plain_err_html, '>plain text<');
        $t->assert_text_not_contains($test_name, $plain_err_html, '<b>');

        $t->subheader($ts . 'html elements');

        // a named button posts its name as a url key and the label is the accessible name of an icon button
        $test_name = 'a named button posts its name';
        $post_btn = $html->button_bs('add', '', '', url_var::POST_SUBMIT, 'add a value');
        $t->assert_text_contains($test_name, $post_btn, html_base::NAME . '="' . url_var::POST_SUBMIT . '"');
        $t->assert_text_contains($test_name, $post_btn, html_base::ARIA_LABEL . '="add a value"');
        $test_name = '... and a plain button neither posts a name nor has an accessible name';
        $plain_btn = $html->button_bs('add');
        $t->assert_text_not_contains($test_name, $plain_btn, html_base::NAME . '="');
        $t->assert_text_not_contains($test_name, $plain_btn, html_base::ARIA_LABEL);

        // the submit button posts the submit url var; the shown text is the value and never the name
        $test_name = 'the submit button posts the submit url var';
        $t->assert_text_contains($test_name, $html->form_submit('Link'), html_base::NAME . '="' . url_var::POST_SUBMIT . '"');
        $test_name = '... and never the shown text as the name';
        $t->assert_text_not_contains($test_name, $html->form_submit('Link'), html_base::NAME . '="Link"');

        // a number input writes only the limits that are given, so the browser enforces no limit by default
        $test_name = 'a number input writes the given limits';
        $num_input = $html->input_number(url_var::NUMERIC_VALUE, 0.5, 0, 1, 0.01, 'value_form');
        $t->assert_text_contains($test_name, $num_input, 'min="0" max="1" step="0.01"');
        $t->assert_text_contains($test_name, $num_input, html_base::FORM . '="value_form"');
        $test_name = '... and an input without limits and form has neither';
        $free_input = $html->input_number(url_var::NUMERIC_VALUE, null, null, null, null);
        $t->assert_text_not_contains($test_name, $free_input, html_base::VALUE . '=');
        $t->assert_text_not_contains($test_name, $free_input, 'min=');
        $t->assert_text_not_contains($test_name, $free_input, html_base::FORM . '=');

        // the link to an object escapes the name, so a name with a tag cannot inject html
        $test_name = 'the link to an object shows the name';
        $obj_link = $html->ref_view(views::WORD_ID, word_names::MATH_ID, word_names::MATH);
        $t->assert_text_contains($test_name, $obj_link, '>' . word_names::MATH . '</' . html_base::A . '>');
        $test_name = '... and a name with a tag is shown as text, not as html';
        $tag_link = $html->ref_view(views::WORD_ID, word_names::MATH_ID, '<b>' . word_names::MATH . '</b>');
        $t->assert_text_not_contains($test_name, $tag_link, '<b>');

        // a heading shows the title and gets a style class only if a style is given
        $test_name = 'a heading shows the title';
        $t->assert_text_contains($test_name, $html->text_h2('names'), '>names</');
        $test_name = '... without a style class if none is given';
        $t->assert_text_not_contains($test_name, $html->text_h2('names'), html_base::CLASS_HTML . '=');


        $t->subheader($ts . 'loading start');

        // the start of a slow page shows the loading text with the footer only after the delay
        $test_name = 'the loading start is shown after the delay';
        $loading = $html->loading_start(1.5, '');
        $t->assert_text_contains($test_name, $loading, html_base::LOADING_ANIMATION . ' 0s 1.5s forwards');
        $test_name = '... with the loading text';
        $t->assert_text_contains($test_name, $loading, $mtr->txt(msg_id::PAGE_LOADING));
        $test_name = '... and the footer';
        $t->assert_text_contains($test_name, $loading, html_base::CLASS_FOOTER);
        // negative: the page that follows names the document and closes it
        $test_name = 'the loading start has no title';
        $t->assert_text_not_contains($test_name, $loading, '<' . html_base::TITLE);
        $test_name = '... and does not close the body';
        $t->assert_text_not_contains($test_name, $loading, '</' . html_base::BODY);
        $test_name = 'the loading end hides the loading start';
        $t->assert_text_contains($test_name, $html->loading_end(), html_base::CLASS_LOADING . ' {display: none;}');


        $t->subheader($ts . 'unit html table tests');

        // create a test set of phrase groups
        $t_phr->phrase_list_zh_mio();
        $grp_city = new group($t->usr1);
        $grp_city->set_phrase_list($t_phr->phrase_list_zh_city_2019());
        $grp_canton = new group($t->usr1);
        $grp_canton->set_phrase_list($t_phr->phrase_list_canton_mio());
        $grp_ch = new group($t->usr1);
        $grp_ch->set_phrase_list($t_phr->ch_inhabitants_in_mio_2019());
        $grp_city_pct = new group($t->usr1);
        $grp_city_pct->set_phrase_list($t_phr->phrase_list_zh_city_pct());
        $grp_canton_pct = new group($t->usr1);
        $grp_canton_pct->set_phrase_list($t_phr->phrase_list_canton_pct());
        $phr_lst_context = new phrase_list($t->usr1);
        $phr_lst_context->add($t_wrd->word_inhabitant()->phrase());

        // create the value for the inhabitants of the city of zurich
        $val_city = new value($t->usr1);
        $val_city->set_grp($grp_city);
        $val_city->set_number(values::CITY_ZH_INHABITANTS_2019);
        $val_city_ui = new value_ui($val_city->api_json([api_types::INCL_PHRASES]));
        $val_city_html = $val_city_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_city_html, word_names::CITY);

        // create the value for the inhabitants of the city of zurich
        $val_canton = new value($t->usr1);
        $val_canton->set_grp($grp_canton);
        $val_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO);
        $val_canton_ui = new value_ui($val_canton->api_json([api_types::INCL_PHRASES]));
        $val_canton_html = $val_canton_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_canton_html, word_names::CANTON);

        // create the value for the inhabitants of Switzerland
        $val_ch = new value($t->usr1);
        $val_ch->set_grp($grp_ch);
        $val_ch->set_number(values::CH_INHABITANTS_2019_IN_MIO);
        $val_ch_ui = new value_ui($val_ch->api_json([api_types::INCL_PHRASES]));
        $val_ch_html = $val_ch_ui->name_link($msg_ui);
        $t->assert_text_contains('', $val_ch_html, round(values::CH_INHABITANTS_2019_IN_MIO, 2));

        // create the formula result for the inhabitants of the city of zurich
        $res_city = new result($t->usr1);
        $res_city->set_grp($grp_city_pct);
        $ch_val_scaled = values::CH_INHABITANTS_2019_IN_MIO * 1000000;
        $res_city->set_number(values::CITY_ZH_INHABITANTS_2019 / $ch_val_scaled);
        $res_city_ui = new value_ui($res_city->api_json([api_types::INCL_PHRASES]));
        $res_city_html = $res_city_ui->name_link($msg_ui);
        $t->assert_text_contains('', $res_city_html, word_names::CITY);

        // create the formula result for the inhabitants of the canton of zurich
        $res_canton = new result($t->usr1);
        $res_canton->set_grp($grp_canton_pct);
        $res_canton->set_number(values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO);
        $res_canton_ui = new value_ui($res_canton->api_json([api_types::INCL_PHRASES]));
        // the percent sign is not coded but given by the triple "% is symbol for percent" of
        // scaling.json, which normally reaches the page with the initial cache load; it is set here
        // as a phrase of the page, so that this test does not depend on the type_lists.json fixture
        // that reset_db regenerates only after the unit tests (see phrase::symbol_name)
        $phr_lst_keep = $ui_sys->phr_lst;
        $ui_sys->phr_lst = $t_phr->list_percent_symbol_cache_ui();
        $res_canton_html = $res_canton_ui->value_edit($msg_ui);
        $res_canton_number = round((values::CANTON_ZH_INHABITANTS_2020_IN_MIO / values::CH_INHABITANTS_2019_IN_MIO) * 100, 2)
            . word_names::PERCENT_SYMBOL;
        $t->assert_text_contains('', $res_canton_html, $res_canton_number);
        // the phrases of the test setup are used again for the snapshots below
        $ui_sys->phr_lst = $phr_lst_keep;

        // create the formula result list and the table to display the results
        $res_lst = new result_list_ui();
        $res_lst->add_result(new result_ui($res_city->api_json([api_types::INCL_PHRASES])));
        $res_lst->add_result(new result_ui($res_canton->api_json([api_types::INCL_PHRASES])));
        $t->html_page_test($res_lst->table($msg_ui), '', 'table_result', $msg_ui);

        // create the same table as above, but within a context
        $phr_lst_context_ui = new phrase_list_ui($phr_lst_context->api_json([api_types::INCL_PHRASES]));
        $t->html_page_test($res_lst->table($msg_ui, $phr_lst_context_ui), '', 'table_result_context', $msg_ui);


        $t->subheader($ts . 'unit html view component tests');

        $cmp = new component($t->usr1);
        $cmp->set(components::WORD_ID, components::TEST_ADD_NAME);
        $cmp->set_type(comp_type_shared::TEXT, new user_message($t->usr1));
        $cmp_ui = new component_ui($cmp->api_json());
        $t->html_page_test($cmp_ui->html($msg_ui), '', 'component_text', $msg_ui);


        $t->subheader($ts . 'list');

        // TODO Prio 2 create and set the model objects and
        //      create the api object using the api_obj() function
        //      create and set the dsp object based on the api json

        $lst = new verb_list($t->usr1);
        $lst->add_verb(new verb(1, verbs::IS));
        $lst->add_verb(new verb(2, verbs::PART_NAME));
        // TODO Prio 2 use set_from_json to set the display object
        $vrb_lst_ui = new verb_list_ui();
        $vrb_lst_ui->set_from_json_array($lst->api_json_array([], $msg), $msg_ui);
        $t->html_page_test($vrb_lst_ui->list(verb_ui::class, 'Verbs'), '', 'list_verbs', $msg_ui);

        $test_name = 'sort a named list by the name';
        $lst = $t_phr->phrase_list_zh_mio();
        $names_unsorted = $lst->names();
        $lst->sort_by_name();
        $names = $lst->names();
        $names_sorted = $names;
        natcasesort($names_sorted);
        $t->assert($test_name, implode(',', $names), implode(',', $names_sorted));
        $test_name = 'unsorted named list';
        $t->assert_not($test_name, implode(',', $names), implode(',', $names_unsorted));




        // TODO Prio 2 review

        global $sys;
        $html = new html_base();

        $is_connected = true; // assumes that the test is done with an internet connection, but if not connected, just show the warning once

        $t->subheader($ts . 'unit html view tests');

        // test the usage of a view to create the HTML code
        /*
        $wrd = $t->load_word(words::TN_READ);
        $msk = new view($t->usr1);
        $msk->load_by_name(views::TN_READ_RATIO);
        //$result = $msk->display($wrd, $url_arr);
        $target = true;
        //$t->dsp_contains(', view_dsp->display is "'.$result.'" which should contain '.$wrd_abb->name.'', $target, $result);
        */


        $t->subheader($ts . 'component');

        // test if a simple text component can be created
        $cmp = new component($t->usr1);
        $msg = new user_message_ui();
        $cmp->type_id = $sys->typ_lst->cmp_typ->id(comp_type_shared::TEXT);
        $cmp->id = 1;
        $cmp->set_name(views::NESN_2016_FS_NAME);
        $cmp_ui = new component_ui($cmp->api_json());
        $result = $cmp_ui->html($msg);
        $target = views::NESN_2016_FS_NAME;
        $t->assert('component_dsp->text', $result, $target);


        $t->subheader($ts . 'button tests');
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        $test_name = 'a sandbox object e.g. word add button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '" title="add new word"><i class="far fa-plus-square"></i></a>';
        $wrd = new word();
        $t->assert($test_name, $wrd->btn_add(), $target);

        $test_name = 'a sandbox object e.g. source change button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::SOURCE_EDIT_ID . '&amp;id=1" title="change source"><i class="far fa-edit"></i></a>';
        $src = new source();
        $src->set_from_json($t_src->source_reserved()->api_json(), $msg);
        $t->assert($test_name, $src->btn_edit(), $target);

        // the validate button of the formula form fields is limited to one twelfth of the width
        // like the 'find and next' button of the add value view (see html_base::button_refresh_text)
        $test_name = 'the validate button of a form field is limited in size';
        $validate_btn = $html->button_refresh_text($mtr->txt(msg_id::FORM_BUTTON_VALIDATE), url_var::REFRESH_LATEX);
        $t->assert_text_contains($test_name, $validate_btn, html_base::BS_BTN_FIELD_COL);
        $test_name = '... and it is a submit, because only a submit sends the entered values';
        $t->assert_text_contains($test_name, $validate_btn,
            'name="' . url_var::REFRESH . '" value="' . url_var::REFRESH_LATEX . '"');
        $test_name = '... named by the text that tells what it does';
        $t->assert_text_contains($test_name, $validate_btn, $mtr->txt(msg_id::FORM_BUTTON_VALIDATE));

        $test_name = 'a sandbox object e.g. formula delete button html code';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::FORMULA_DEL_ID . '&amp;id=1" title="delete this formula"><i class="far fa-times-circle"></i></a>';
        $frm = new formula();
        $frm->set_from_json($t_frm->formula()->api_json(), $msg);
        $t->assert($test_name, $frm->btn_del(), $target);


        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        // this block tests the icon and the title of each button, so no calling page is named;
        // that the buttons carry the page vars of the url array is tested in the back url section
        $url_arr = [];
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '" title="add new word">';
        $result = (new button($url, $url_arr))->add(msg_id::WORD_ADD);
        $t->dsp_contains(", btn_add", $target, $result);

        // the explanation is shown in the tooltip of the add button
        $test_name = 'the add button shows the explanation';
        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $btn = new button($url, $url_arr);
        $btn_html = $btn->add(msg_id::WORD_ADD, word_names::MATH_COM);
        $t->assert_text_contains($test_name, $btn_html, word_names::MATH_COM);

        // negative: without an explanation the add button shows only the message text
        $test_name = 'the add button without explanation shows only the message';
        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $btn = new button($url, $url_arr);
        $btn_html = $btn->add(msg_id::WORD_ADD);
        $t->assert_text_not_contains($test_name, $btn_html, word_names::MATH_COM);

        // TODO Prio 2 move e.g. because the edit word button is tested already in the unit tests of the object

        $url = $html->url_back(views::WORD_DEL_ID);
        $target = '<a href="/http/view.php" title="Del test"><img src="/images/button_del.svg" alt="Del test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_DEL_ID . '" title="delete word"><i class="far fa-times-circle"></i></a>';
        $result = (new button($url, $url_arr))->del(msg_id::WORD_DEL);
        $t->dsp_contains(", btn_del", $target, $result);

        $url = $html->url_back(views::WORD_NAME);
        $target = '<a href="/http/view.php" title="Undo test"><img src="/images/button_undo.svg" alt="Undo test"></a>';
        $target = '<a href="/http/word.php" title="undo"><img src="/images/button_undo.svg" alt="undo"></a>';
        $result = (new button($url, $url_arr))->undo(msg_id::UNDO);
        //$t->assert(", btn_undo", $result, $target);

        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $target = '<a href="/http/view.php" title="Find test"><img src="/images/button_find.svg" alt="Find test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '" title=""><img src="/images/button_find.svg" alt=""></a>';
        $result = (new button($url, $url_arr))->find(msg_id::FIND);
        //$t->assert(", btn_find", $result, $target);

        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $target = '<a href="/http/view.php" title="Show all test"><img src="/images/button_filter_off.svg" alt="Show all test"></a>';
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '" title=""><img src="/images/button_filter_off.svg" alt=""></a>';
        $result = (new button($url, $url_arr))->un_filter(msg_id::REMOVE_FILTER);
        //$t->assert(", btn_unfilter", $result, $target);

        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $target = '<h6>YesNo test</h6><a href="/http/view.php&confirm=1" title="Yes">Yes</a>/<a href="/http/view.php&confirm=-1" title="No">No</a>';
        $target = '<h6></h6><a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '&amp;confirm=1">yes</a>/<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '&amp;confirm=-1">no</a>';
        $result = (new button($url, $url_arr))->yes_no();
        $t->assert(", btn_yesno", $result, $target);

        $url = $html->url_back(views::WORD_ADD_DETAIL_ID);
        $target = '<a href="' . api::MAIN_SCRIPT . '?words=1" title="back"><img src="/images/button_back.svg" alt="back"></a>';
        $result = (new button($url, $url_arr))->back();
        //$t->assert(", btn_back", $result, $target);

        $t->subheader($ts . 'xss escaping');
        // a user-settable name is escaped at the display sink so a crafted name cannot inject script
        // into the page shown to other users incl. an admin (stored xss); ui_base and system_form
        // route the display names / descriptions through html_base::esc (element-text context)
        $xss = '<script>alert(1)</script>';
        $xss_esc = htmlspecialchars($xss, ENT_NOQUOTES);
        $wrd_xss = new word();
        $wrd_xss->name = $xss;
        $base = new ui_base();
        $form = new system_form();
        $test_name = 'ui_base->phrase_name escapes an injected script tag';
        $t->assert_text_contains($test_name, $base->phrase_name($wrd_xss), $xss_esc);
        $test_name = 'ui_base->phrase_name does not echo the raw script tag';
        $t->assert_text_not_contains($test_name, $base->phrase_name($wrd_xss), $xss);
        $test_name = 'system_form->show_name escapes an injected script tag';
        $t->assert_text_contains($test_name, $form->show_name($wrd_xss), $xss_esc);
        $test_name = 'system_form->show_name does not echo the raw script tag';
        $t->assert_text_not_contains($test_name, $form->show_name($wrd_xss), $xss);

        $t->subheader($ts . 'back url');

        $test_name = 'back url part while editing word 123';
        $url_part = parse_url('?m=3&id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=3&9id=123');

        $test_name = 'back url part is empty if there is no query string';
        $url_array = [];
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '');

        // only the params that name the page are prefixed, because the form state does not
        // name the page and an already prefixed param would get a second prefix
        $test_name = 'the back part keeps the page vars and drops the form state';
        $url_part = parse_url('?m=3&id=123&dls=20&dlp=1&z=0&a=r&k=USD&o=the%20dollar');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=3&9id=123&9dls=20&9dlp=1');

        $test_name = '... and never prefixes a prefixed param a second time';
        // TODO Prio 2 why? actually this would allow a two and more back steps
        $url_part = parse_url('?m=3&id=123&9m=1&9id=0&8k=USD');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert_text_not_contains($test_name, $result, '99');
        $test_name = '... neither the back part nor the pre values of the calling page';
        $t->assert($test_name, $result, '9m=3&9id=123');

        // a page var without a value names nothing, so it is left out of the back part
        $test_name = 'an empty page var is dropped from the back part';
        $url_part = parse_url('?m=1&id=0&dlp=0&pattern=');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=1');

        $test_name = '... but the list size zero is kept, because it shows every row';
        $url_part = parse_url('?m=1&id=0&dls=0');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=1&9dls=0');

        $test_name = '... and a url array without any page value leads to the start page';
        $t->assert($test_name, $html->page_url([url_var::ID => '0', url_var::STEP => '0']),
            api::MAIN_SCRIPT);

        $test_name = '... and a search pattern that is no number is kept';
        $url_part = parse_url('?m=1&id=0&pattern=zh');
        parse_str($url_part["query"], $url_array);
        $result = $html->back_url_part($url_array);
        $t->assert($test_name, $result, '9m=1&9pattern=zh');

        $test_name = 'the hidden back fields of a form name the page only';
        $url_part = parse_url('?m=3&id=123&z=0&8k=USD');
        parse_str($url_part["query"], $url_array);
        $t->assert($test_name, html_base::back_url_array($url_array),
            ['9m' => '3', '9id' => '123']);
        // negative: the id 0 of a page without an object names nothing
        $test_name = '... and a form without a calling page has no hidden back field';
        $t->assert($test_name, html_base::back_url_array([url_var::ID => '0']), []);

        $test_name = 'login url with back part while editing word 123';
        $url_part = parse_url('?m=3&id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_with_back(api::LOGIN_SCRIPT, $url_array);
        $t->assert($test_name, $result, rest_ctrl::PATH_FIXED .'view.php?m=' . views::LOGIN_ID . '&9m=3&9id=123');

        $test_name = 'url from back part while editing word 123';
        $url_part = parse_url('?m=2&9m=3&9id=123');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_par_from_back_part($url_array);
        $t->assert($test_name, $result, ['m' => '3', 'id' => '123']);

        $test_name = 'url from back part if array is empty';
        $url_array = [];
        $result = $html->url_par_from_back_part($url_array);
        $t->assert($test_name, $result, []);

        $test_name = 'add word url with back part from main page';
        $url_part = parse_url('?m=1');
        parse_str($url_part["query"], $url_array);
        $result = $html->url_with_back(api::MAIN_SCRIPT . '?m=3&id=123', $url_array);
        $t->assert($test_name, $result, rest_ctrl::PATH_FIXED .'view.php?m=3&id=123&9m=1');

        // the link builders take the url parameters of the calling page and add them as the
        // '9'-prefixed back part, so that the called page can return to the calling page
        $page_arr = [url_var::MASK => '3', url_var::ID => '123'];
        $test_name = 'a view url carries the calling page as the back part';
        $t->assert_text_contains($test_name, $html->url_back(views::WORD_ADD_DETAIL_ID, 0, $page_arr), '9m=3&9id=123');
        $test_name = '... and an unknown calling page adds no back part';
        $t->assert_text_not_contains($test_name, $html->url_back(views::WORD_ADD_DETAIL_ID, 0, []), '9');
        $test_name = 'an old style script url carries the calling page as the back part';
        $t->assert_text_contains($test_name, $html->url_old(rest_ctrl::VIEW, 5, $page_arr), '9m=3&9id=123');
        $test_name = '... and without an object and a calling page it has no parameter';
        $t->assert($test_name, $html->url_old(rest_ctrl::VIEW), rest_ctrl::PATH_FIXED . rest_ctrl::VIEW . rest_ctrl::EXT);
        // the group id of a value with more than four phrases contains a '+', which a url reads as a
        // space, so a link writes the id in its short url form, which has a '_' instead of the '+'
        // (see group_id_url), because else the value page finds no value
        $t_val = new test_values($t);
        $grp_id = $t_val->value_16()->id();
        $test_name = 'the group id of a non prime value contains a plus';
        $t->assert_text_contains($test_name, (string)$grp_id, '+');
        $test_name = 'a view url names a value group id in its short form without a plus';
        $val_url = $html->url_back(views::VALUE_DEFAULT_ID, $grp_id);
        $t->assert_text_contains($test_name, $val_url,
            url_var::ID . url_var::EQ . rawurlencode(group_id_url::to_url($grp_id)));
        $t->assert_text_not_contains($test_name, $val_url, '+');
        $test_name = '... and does not contain the raw group id';
        $t->assert_text_not_contains($test_name, $val_url, url_var::ID . url_var::EQ . $grp_id);
        $test_name = 'the static view url names a value group id in its short form too';
        $static_url = html_base::url(views::VALUE_DEFAULT_ID, $grp_id);
        $t->assert_text_contains($test_name, $static_url,
            url_var::ID . url_var::EQ . rawurlencode(group_id_url::to_url($grp_id)));
        $test_name = 'a view url keeps a numeric value id unchanged';
        $prime_url = $html->url_back(views::VALUE_DEFAULT_ID, values::PI_MATH_ID);
        $t->assert_text_contains($test_name, $prime_url, url_var::ID . url_var::EQ . values::PI_MATH_ID);
        $test_name = 'the page url of a url array names the page by its page vars only';
        $t->assert($test_name, $html->page_url($page_arr + [url_var::STEP => '0']),
            api::MAIN_SCRIPT . '?m=3&id=123');
        $test_name = 'the form end links the cancel button to the calling page';
        $t->assert_text_contains($test_name, $html->dsp_form_end('', $page_arr), 'm=3&amp;id=123');
        $test_name = '... and shows no cancel button if no calling page is known';
        $t->assert_text_not_contains($test_name, $html->dsp_form_end('', []), 'btn-outline-secondary');
        $test_name = 'the back button leads to the calling page';
        $t->assert_text_contains($test_name, (new button())->back($page_arr), 'm=3&amp;id=123');
        $test_name = '... and to the start page if no calling page is known';
        $t->assert_text_contains($test_name, (new button())->back([]), api::MAIN_SCRIPT . '"');
        $lib = new library();
        $msg = new user_message_ui();
        $url_test = new test_mappers($t);

        $t->subheader($ts . 'url mapper');
        $url_map = new url_mapper();
        $test_name = 'add default value of view';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?id=1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        $test_name = 'add default value of step';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=3&id=1&debug=-1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $step = $url_array[url_var::STEP];
        $t->assert($test_name, $step, 0);
        $test_name = 'add default value of view for human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=&verb_id=3';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $view = $url_array[url_var::MASK];
        $t->assert($test_name, $view, views::START_ID);
        // the human url uses the view code id (the name) for the mask, not the numeric view id, for
        // every view that is in the loaded cache (url_mapper::map_std_mask_to)
        $test_name = 'convert the standard url to human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '&id=1&debug=-1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_human);
        $view = $url_array[url_var::MASK_HUMAN];
        $t->assert($test_name, $view, views::WORD_ADD_DETAIL);
        // the list size and the list page of a "... more" click are url state like every other
        // frontend state, so both have a human-readable name that maps to the short name and back
        $test_name = 'the human-readable list size and page map to the short url vars';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=' . views::START_CODE
            . '&' . url_var::DISPLAY_LIST_SIZE_HUMAN . '=20&' . url_var::DISPLAY_LIST_PAGE_HUMAN . '=1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::DISPLAY_LIST_SIZE] ?? '', '20');
        $t->assert($test_name . ' (page)', $url_array[url_var::DISPLAY_LIST_PAGE] ?? '', '1');
        $test_name = '... and the short url vars map back to the human-readable names';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&'
            . url_var::DISPLAY_LIST_SIZE . '=20&' . url_var::DISPLAY_LIST_PAGE . '=1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_human);
        $t->assert($test_name, $url_array[url_var::DISPLAY_LIST_SIZE_HUMAN] ?? '', '20');
        $t->assert($test_name . ' (page)', $url_array[url_var::DISPLAY_LIST_PAGE_HUMAN] ?? '', '1');
        // the three row orders are url state like the list size, so they map the same way
        $test_name = 'the human-readable row orders map to the short url vars';
        $prime_order = table_orders::NUMERIC_ASC->url_value(12);
        $sub_order = table_orders::ALPHA_DESC->url_value(-13);
        $sub_sub_order = table_orders::ALPHA_PARENT_ASC->url_value(14);
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=' . views::START_CODE
            . '&' . url_var::DISPLAY_LIST_ORDER_HUMAN . '=' . $prime_order
            . '&' . url_var::DISPLAY_LIST_ORDER_SUB_HUMAN . '=' . $sub_order
            . '&' . url_var::DISPLAY_LIST_ORDER_SUB_SUB_HUMAN . '=' . $sub_sub_order;
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::DISPLAY_LIST_ORDER] ?? '', $prime_order);
        $t->assert($test_name . ' (sub)', $url_array[url_var::DISPLAY_LIST_ORDER_SUB] ?? '', $sub_order);
        $t->assert($test_name . ' (sub sub)', $url_array[url_var::DISPLAY_LIST_ORDER_SUB_SUB] ?? '', $sub_sub_order);
        $test_name = '... and the short url vars map back to the human-readable names';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&'
            . url_var::DISPLAY_LIST_ORDER . '=' . $prime_order . '&'
            . url_var::DISPLAY_LIST_ORDER_SUB . '=' . $sub_order . '&'
            . url_var::DISPLAY_LIST_ORDER_SUB_SUB . '=' . $sub_sub_order;
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_human);
        $t->assert($test_name, $url_array[url_var::DISPLAY_LIST_ORDER_HUMAN] ?? '', $prime_order);
        $t->assert($test_name . ' (sub)', $url_array[url_var::DISPLAY_LIST_ORDER_SUB_HUMAN] ?? '', $sub_order);
        $t->assert($test_name . ' (sub sub)', $url_array[url_var::DISPLAY_LIST_ORDER_SUB_SUB_HUMAN] ?? '', $sub_sub_order);

        // TODO Prio 1 review
        // url_mapper::to_row_format: the flat standard url array (as produced by url_to_standard) is
        // accepted directly now, not only the [key, value] row format produced by url_array_with
        $test_name = 'convert a flat standard url to human-readable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '&id=1&debug=-1';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array($url), $msg));
        $url_array = $lib->url_array($url_human);
        $view = $url_array[url_var::MASK_HUMAN];
        $t->assert($test_name, $view, views::WORD_ADD_DETAIL);
        // an '8'-prefixed pre value (and '9'-prefixed back target) is mapped to its human key with the
        // prefix kept (e.g. 8name), so it is not reported as missing
        $test_name = 'human url conversion maps an 8-prefixed pre value';
        $ok_msg = new user_message_ui();
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&' . url_var::PRE . url_var::NAME . '=x';
        $url_human = $url_test->test_url($url_map->standard_url_to_human($lib->url_array($url), $ok_msg));
        $t->assert_false($test_name, $ok_msg->has_msg_id(msg_id::URL_MAP_MISSING));
        $t->assert_text_contains($test_name, $url_human, url_var::PRE . url_var::NAME_HUMAN);
        // negative: a url key without any human mapping is still reported as missing
        $test_name = 'human url conversion reports a url key without a human mapping';
        $err_msg = new user_message_ui();
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=2&zzz=x';
        $url_map->standard_url_to_human($lib->url_array($url), $err_msg);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_MAP_MISSING));

        // a value or result url names the group by its short form: without the zero chars and the
        // empty slots of the database key and with "_" for the "+" of a word (see group_id_url)
        $grp_key = $t_phr->phrase_list()->get_grp_id(false)->id();
        $url_key = group_id_url::to_url($grp_key);
        $test_name = 'a value url names the group in its short form';
        $t->assert_text_contains($test_name, html_base::url(views::VALUE_DEFAULT_ID, $grp_key),
            url_var::ID . url_var::EQ . rawurlencode($url_key));
        $t->assert_text_not_contains($test_name, html_base::url(views::VALUE_DEFAULT_ID, $grp_key), '%2B');
        $test_name = '... also as the back target of a page opened from the value page';
        $t->assert_text_contains($test_name,
            $html->url_back(views::VALUE_EDIT_ID, $grp_key, [url_var::MASK => views::VALUE_DEFAULT_ID, url_var::ID => $grp_key]),
            url_var::BACK . url_var::ID . url_var::EQ . rawurlencode($url_key));
        $test_name = '... and the human-readable url too';
        $url_human = $url_map->standard_url_to_human([url_var::MASK => views::VALUE_DEFAULT_ID, url_var::ID => $grp_key], $msg);
        $t->assert_text_contains($test_name, $url_human, urlencode($url_key));
        $test_name = 'the short group id of a url is mapped to the database key';
        // a back target names its page by the mask and the id, so the back id is a group id only
        // if the back mask shows a value or a result (see views::GROUP_ID_MASKS_IDS)
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::VALUE_EDIT_ID
            . '&' . url_var::ID . '=' . rawurlencode($url_key)
            . '&' . url_var::BACK . url_var::MASK . '=' . views::VALUE_DEFAULT_ID
            . '&' . url_var::BACK . url_var::ID . '=' . rawurlencode($url_key);
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::ID], $grp_key);
        $t->assert($test_name . ' (back target)', $url_array[url_var::BACK . url_var::ID], $grp_key);
        // negative: the back id of a page that is not selected by a group id is left as it is
        $test_name = '... but not the back id of a word page';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::VALUE_EDIT_ID
            . '&' . url_var::ID . '=' . rawurlencode($url_key)
            . '&' . url_var::BACK . url_var::MASK . '=' . views::WORD_ID
            . '&' . url_var::BACK . url_var::ID . '=' . rawurlencode($url_key);
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::BACK . url_var::ID], $url_key);
        // the internal api call names a group by its short form too, so that the api url of a
        // value or result page is the same short id as the page url (see rest_call::id_data)
        $test_name = 'an api call names the group in its short form';
        $t->assert($test_name, new rest_call()->id_data($grp_key)[url_var::ID], $url_key);
        // negative: an integer id e.g. of a word or of a prime group is not changed
        $test_name = '... but leaves an integer id as it is';
        $t->assert($test_name, (string)new rest_call()->id_data(word_names::MATH_ID)[url_var::ID],
            (string)word_names::MATH_ID);
        // the backend names in a header if an api answer comes from the cache (see def::DEBUG_API_CACHE)
        $test_name = 'the cache header of an api answer is read';
        $line = api::CACHE_HEADER . ': ' . api::CACHE_HIT . "\r\n";
        $t->assert($test_name, rest_call::cache_state_of($line), api::CACHE_HIT);
        $test_name = '... also in lower case as http/2 sends it';
        $line = strtolower(api::CACHE_HEADER) . ': ' . api::CACHE_MISS . "\r\n";
        $t->assert($test_name, rest_call::cache_state_of($line), api::CACHE_MISS);
        $test_name = '... but not from another header';
        $line = api::CSRF_TOKEN_HEADER . ': ' . api::CACHE_HIT . "\r\n";
        $t->assert($test_name, rest_call::cache_state_of($line), '');
        // the backend names the seconds of each step of a slow answer in its json, which the
        // frontend reads and removes, so that the answer is passed on like a fast one
        $steps = [system_time_type::DB_READ => 1.5, system_time_type::SQL_CREATE => 0.25];
        $api_json = json_encode([json_fields::NAME => word_names::MATH]);
        $slow_json = controller::json_with_time($api_json, $steps);
        $test_name = 'the steps of a slow api answer are the first field of its json';
        $t->assert_text_contains($test_name, $slow_json, rest_call::TIME_START);
        $test_name = '... which the frontend reads';
        $t->assert($test_name, rest_call::time_steps_of($slow_json), $steps);
        $test_name = '... and removes from the answer';
        $t->assert($test_name, rest_call::without_time($slow_json), $api_json);
        // negative: a fast answer, an empty object and a json list stay as they are
        $test_name = 'a fast api answer gets no steps';
        $t->assert($test_name, controller::json_with_time($api_json, []), $api_json);
        $test_name = '... and the frontend reads none';
        $t->assert($test_name, rest_call::time_steps_of($api_json), []);
        $test_name = '... and leaves the answer as it is';
        $t->assert($test_name, rest_call::without_time($api_json), $api_json);
        $test_name = 'an empty json object gets no steps';
        $t->assert($test_name, controller::json_with_time('{}', $steps), '{}');
        $test_name = 'a json list gets no steps, because it has no field for them';
        $t->assert($test_name, controller::json_with_time('[]', $steps), '[]');
        // the frontend sums the wait and the steps of the api calls until they are booked to the time report
        $dto_ui = new data_object();
        $dto_ui->api_wait += 1.5;
        $dto_ui->add_api_steps([system_time_type::DB_READ => 1.0]);
        $dto_ui->add_api_steps([system_time_type::DB_READ => 0.25, system_time_type::SQL_CREATE => 0.5]);
        $test_name = 'the api wait not yet booked is taken for the time report';
        $t->assert($test_name, (string)$dto_ui->take_api_wait(), '1.5');
        $test_name = '... and the backend steps summed per step';
        $t->assert($test_name, $dto_ui->take_api_steps(),
            [system_time_type::DB_READ => 1.25, system_time_type::SQL_CREATE => 0.5]);
        // negative: a booked time is not booked again
        $test_name = 'a booked api wait is not taken again';
        $t->assert($test_name, (string)$dto_ui->take_api_wait(), '0');
        $test_name = '... and neither are the booked steps';
        $t->assert($test_name, $dto_ui->take_api_steps(), []);
        // the frontend splits the rest of its wait into the connect and the transfer of the answer
        $test_name = 'the wait for an api answer is split into connect and transfer';
        $t->assert($test_name, rest_call::transfer_steps(0.25, 0.5, 0.75),
            [system_time_type::API_CONNECT => 0.25, system_time_type::API_TRANSFER => 0.25]);
        // negative: a reused connection and an answer in one package add no step
        $test_name = '... but not without connect and transfer time';
        $t->assert($test_name, rest_call::transfer_steps(0, 0.5, 0.5), []);
        // the time from the open connection to the first byte is the server time, named by the kind of answer
        // a slow answer always names its steps, so an object without them has been fast
        $test_name = 'the server time of an api object without steps is a fast api response';
        $t->assert($test_name, rest_call::server_steps($api_json, 0.5), [system_time_type::API_FAST => 0.5]);
        $test_name = '... of a json list, which has no field for the steps, a list response';
        $t->assert($test_name, rest_call::server_steps('[]', 0.5), [system_time_type::API_LIST => 0.5]);
        $test_name = '... of an answer that is no json e.g. an error page a no json response';
        $t->assert($test_name, rest_call::server_steps('', 0.5), [system_time_type::API_NO_JSON => 0.5]);
        $test_name = '... and of a slow answer its steps and the part that the backend has not measured';
        $t->assert($test_name, rest_call::server_steps($slow_json, 2.0),
            [system_time_type::DB_READ => 1.5, system_time_type::SQL_CREATE => 0.25, system_time_type::API_NOT_MEASURED => 0.25]);
        // negative: a slow answer whose steps cover the whole server time has no unmeasured part
        $test_name = '... but without an unmeasured part if the steps cover the server time';
        $t->assert($test_name, rest_call::server_steps($slow_json, 1.75),
            [system_time_type::DB_READ => 1.5, system_time_type::SQL_CREATE => 0.25]);
        // the wait around the call that curl itself does not count e.g. the session cookie file
        $test_name = 'the wait that curl does not count is the curl time';
        $t->assert($test_name, rest_call::curl_step(1.0, 0.75), [system_time_type::API_CURL => 0.25]);
        $test_name = '... but not if curl has counted the whole wait';
        $t->assert($test_name, rest_call::curl_step(0.75, 0.75), []);
        // the render time of each component without its api wait and without the components within it
        $dto_ui = new data_object();
        $dto_ui->start_html_step(10.0);
        $dto_ui->start_html_step(11.0);
        $dto_ui->end_html_step(component_types::TEXT, 12.0);
        $dto_ui->api_wait += 0.5;
        $dto_ui->end_html_step(component_types::FORM_TITLE, 14.0);
        $test_name = 'a component is timed without the components within it';
        $t->assert($test_name, $dto_ui->take_html_steps(),
            [component_types::TEXT => 1.0, component_types::FORM_TITLE => 2.5]);
        $test_name = '... and after the booking only the components rendered since';
        $dto_ui->start_html_step(20.0);
        $dto_ui->end_html_step(component_types::TEXT, 20.25);
        $t->assert($test_name, $dto_ui->take_html_steps(), [component_types::TEXT => 0.25]);
        // negative: an end without a start e.g. after an error in the render adds no time
        $test_name = 'an end of a component without its start adds no time';
        $dto_ui->end_html_step(component_types::TEXT, 30.0);
        $t->assert($test_name, $dto_ui->take_html_steps(), []);
        // negative: the database key of an old link and an integer id are still accepted
        $test_name = 'the database key of an old link is still accepted';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::VALUE_DEFAULT_ID
            . '&' . url_var::ID . '=' . rawurlencode($grp_key);
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::ID], $grp_key);
        $test_name = 'an integer id is not changed by the url mapping';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ID . '&id=1';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::ID], '1');
        $test_name = 'a short name of a word that looks like a short group id is not changed';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ID . '&id=A-';
        $url_array = $url_map->url_to_standard($lib->url_array($url), $msg);
        $t->assert($test_name, $url_array[url_var::ID], 'A-');

        // frontend::url_to_back_url returns the previous page from the '9'-prefixed back targets
        $test_name = 'url_to_back_url returns the back target view and id';
        $ui = new frontend();
        $back_url = $ui->url_to_back_url([
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => 1
        ]);
        $t->assert($test_name, $back_url[url_var::MASK], views::WORD_ID);
        // negative: a url without a back target falls back to the start view
        $test_name = 'url_to_back_url without a back target returns the start view';
        $back_url = $ui->url_to_back_url([url_var::MASK => views::WORD_ID]);
        $t->assert($test_name, $back_url[url_var::MASK], views::START_ID);

        // a change opened from another page returns there, so the confirm view carries the mask of the
        // change as origin mask for the object type instead of overwriting the back target
        $test_name = 'a confirm keeps the calling page as back target';
        $cfm_msg = new user_message_ui();
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::WORD_ADD_DETAIL_ID,
            url_var::STEP => url_var::STEP_CONFIRM,
            url_var::NAME => word_names::TEST_ADD,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::ZH_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::BACK . url_var::ID] ?? 0, word_names::ZH_ID);
        $cfm_msg->reset();
        $test_name = 'a confirm carries the mask of the change as origin mask';
        $t->assert($test_name, $cfm_url[url_var::ORIGIN_MASK] ?? 0, views::WORD_ADD_DETAIL_ID);
        $test_name = 'a confirm without a calling page returns to the own view of the object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::WORD_ADD_DETAIL_ID,
            url_var::STEP => url_var::STEP_CONFIRM,
            url_var::NAME => word_names::TEST_ADD
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::BACK . url_var::MASK] ?? 0, views::WORD_ID);
        $cfm_msg->reset();
        $test_name = 'a change from the start page returns to the own view of the object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::WORD_EDIT_ID,
            url_var::STEP => url_var::STEP_CONFIRM,
            url_var::ID => word_names::MINUTE_ID,
            url_var::NAME => word_names::MINUTE,
            url_var::BACK . url_var::MASK => views::START_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::BACK . url_var::MASK] ?? 0, views::WORD_ID);
        $cfm_msg->reset();
        $test_name = 'a change from the start page returns to the id of the object';
        $t->assert($test_name, $cfm_url[url_var::BACK . url_var::ID] ?? 0, word_names::MINUTE_ID);
        // the id of the written object replaces the back id only if the back target is its own page
        $test_name = 'a confirmed change returns to the calling page of another object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_EDIT_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ID => word_names::MINUTE_ID,
            url_var::NAME => word_names::MINUTE,
            url_var::ORIGIN_MASK => views::WORD_EDIT_ID,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::ZH_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::ID] ?? 0, word_names::ZH_ID);
        $cfm_msg->reset();
        $test_name = 'a confirmed change from the own page shows the changed object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_EDIT_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ID => word_names::MINUTE_ID,
            url_var::NAME => word_names::MINUTE,
            url_var::ORIGIN_MASK => views::WORD_EDIT_ID,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::MINUTE_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::ID] ?? 0, word_names::MINUTE_ID);
        $cfm_msg->reset();
        // a url without origin mask (e.g. of a workflow test) takes the back mask as the own view
        $test_name = 'a confirmed change without origin mask shows the changed object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_EDIT_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ID => word_names::MINUTE_ID,
            url_var::NAME => word_names::MINUTE,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::MINUTE_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::ID] ?? 0, word_names::MINUTE_ID);
        $cfm_msg->reset();
        $test_name = 'a confirmed change without origin mask keeps the id of another object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_EDIT_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ID => word_names::MINUTE_ID,
            url_var::NAME => word_names::MINUTE,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::ZH_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::ID] ?? 0, word_names::ZH_ID);
        $cfm_msg->reset();
        // the add form that has opened the detailed add form of the same object is done with the add
        $test_name = 'a confirmed add opened from another add form of the same object shows the new object';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_ADD_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ORIGIN_MASK => views::VALUE_ADD_DETAIL_ID,
            url_var::PHRASE_LIST => (string)word_names::ZH_ID,
            url_var::NUMERIC_VALUE => values::SAMPLE_INT,
            url_var::BACK . url_var::MASK => views::VALUE_ADD_NO_JS_ID,
            url_var::BACK . url_var::PATTERN => word_names::ZH
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::MASK] ?? 0, views::VALUE_DEFAULT_ID);
        $test_name = '... without the vars of the add form';
        $t->assert($test_name, $cfm_url[url_var::PATTERN] ?? '', '');
        $cfm_msg->reset();
        $test_name = 'a confirmed add opened from the add form of another object returns to that form';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_ADD_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ORIGIN_MASK => views::FORMULA_ADD_ID,
            url_var::BACK . url_var::MASK => views::VALUE_ADD_NO_JS_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::MASK] ?? 0, views::VALUE_ADD_NO_JS_ID);
        $cfm_msg->reset();

        // url_mapper::human_url_to_json groups the 8-prefixed vars into 'original_data' and the
        // 9-prefixed vars into 'back', and converts the view id to the code id
        $test_name = 'human_url_to_json groups the pre values and back targets into subarrays';
        $json = $url_map->human_url_to_json([
            url_var::MASK => views::WORD_EDIT_ID,
            url_var::NAME => 'x',
            url_var::PRE . url_var::NAME => 'old',
            url_var::BACK . url_var::MASK => views::WORD_ID
        ], $msg);
        $t->assert_text_contains($test_name, $json, json_fields::URL_ORIGINAL_DATA);
        $t->assert_text_contains($test_name, $json, json_fields::URL_PART_BACK);
        $t->assert_text_contains($test_name, $json, views::WORD_EDIT);
        // negative: a top-level url key without a human mapping is reported as missing
        $test_name = 'human_url_to_json reports a url key without a human mapping';
        $err_msg = new user_message_ui();
        $url_map->human_url_to_json([url_var::MASK => views::WORD_EDIT_ID, 'zzz' => '1'], $err_msg);
        $t->assert_true($test_name, $err_msg->has_msg_id(msg_id::URL_MAP_MISSING));
        $test_name = 'human_url_to_json groups the link vars into a subarray';
        $link_json = $url_map->human_url_to_json([
            url_var::MASK => views::FORMULA_ADD_ID,
            url_var::LINK . url_var::PHRASE => word_names::MINUTE_ID
        ], $msg);
        $t->assert_text_contains($test_name, $link_json, json_fields::URL_PART_LINK);
        $test_name = 'human_url_to_json without link vars has no link subarray';
        $t->assert_text_not_contains($test_name, $json, json_fields::URL_PART_LINK);

        // the '7'-prefixed link vars name the link that an add view creates together with the new object
        $test_name = 'the link vars are prefixed with the link char';
        $link_part = html_base::link_url_part([url_var::PHRASE => word_names::MINUTE_ID]);
        $link_expected = url_var::LINK . url_var::PHRASE . url_var::EQ . word_names::MINUTE_ID;
        $t->assert($test_name, $link_part, $link_expected);
        $test_name = 'the link vars are read from the url without the prefix';
        $link_vars = html_base::link_vars([
            url_var::LINK . url_var::PHRASE => word_names::MINUTE_ID,
            url_var::BACK . url_var::MASK => views::WORD_ID
        ]);
        $t->assert($test_name, $link_vars[url_var::PHRASE] ?? 0, word_names::MINUTE_ID);
        $test_name = 'the back targets are no link vars';
        $t->assert($test_name, count($link_vars), 1);
        $test_name = 'a link var that a link may not name is dropped';
        $id_vars = html_base::link_vars([url_var::LINK . url_var::ID => word_names::MINUTE_ID]);
        $t->assert($test_name, count($id_vars), 0);
        $test_name = 'a prefixed url key is split into the prefix and the url key';
        $key_parts = url_var::split_prefix(url_var::BACK . url_var::MASK);
        $t->assert($test_name, $key_parts[0] . '|' . $key_parts[1], url_var::BACK . '|' . url_var::MASK);
        $test_name = 'a url key without prefix has no prefix';
        $key_parts = url_var::split_prefix(url_var::MASK);
        $t->assert($test_name, $key_parts[0], '');
        // the confirm view keeps the link vars for the confirm submit, the page after the add does not;
        // a valid formula of the factory, so that the url reaches the confirm view
        $test_name = 'a confirm keeps the link vars of the new object';
        $frm_url = [
            url_var::MASK => views::FORMULA_ADD_ID,
            url_var::NAME => formula_names::JOULE_DEF,
            url_var::DESCRIPTION => formula_names::JOULE_DEF_COM,
            url_var::TYPE => formula_types::CALC_ID,
            url_var::USER_EXPRESSION => formula_names::JOULE_DEF_EXP,
            url_var::LATEX => formula_names::JOULE_DEF_LATEX,
            url_var::STEP => url_var::STEP_CONFIRM,
            url_var::LINK . url_var::PHRASE => word_names::MINUTE_ID,
        ];
        $cfm_url = $ui->url_to_action($frm_url, $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::LINK . url_var::PHRASE] ?? 0, word_names::MINUTE_ID);
        $test_name = '... on the confirm view';
        $t->assert($test_name, $cfm_url[url_var::MASK] ?? 0, views::CONFIRM_ADD_ID);
        $cfm_msg->reset();
        $test_name = 'the page after a confirmed add does not repeat the link vars';
        $cfm_url = $ui->url_to_action([
            url_var::MASK => views::CONFIRM_ADD_ID,
            url_var::STEP => url_var::STEP_CONFIRMED,
            url_var::ORIGIN_MASK => views::FORMULA_ADD_ID,
            url_var::LINK . url_var::PHRASE => word_names::MINUTE_ID,
            url_var::BACK . url_var::MASK => views::WORD_ID,
            url_var::BACK . url_var::ID => word_names::MINUTE_ID
        ], $t->usr1, $cfm_msg, $ui->dto, false);
        $t->assert($test_name, $cfm_url[url_var::LINK . url_var::PHRASE] ?? '', '');
        $cfm_msg->reset();

        // url_var::action_step maps a confirmed action to the confirmed process step (which triggers the
        // db write), and a plain navigation action to the base step
        $test_name = 'action_step maps update_confirmed to the confirmed step';
        $t->assert($test_name, url_var::action_step(url_var::ACTION_CONFIRMED), url_var::STEP_CONFIRMED);
        // negative: a navigation action does not advance the process step
        $test_name = 'action_step maps a navigation action to the base step';
        $t->assert($test_name, url_var::action_step(url_var::ACTION_SHOW), url_var::STEP_BASE);

        // without_secrets masks the unhashed password of a login post so it is never logged (http/view.php);
        // a non-secret field like the username is kept unchanged so the log stays useful
        $dummy_pw = 'dummy unhashed password for the redaction unit test';
        $post = [url_var::USERNAME_HUMAN => users::TEST_USER_NAME, url_var::USER_PASSWORD_HUMAN => $dummy_pw];
        $redacted = url_var::without_secrets($post);
        $test_name = 'without_secrets masks the unhashed password';
        $t->assert($test_name, $redacted[url_var::USER_PASSWORD_HUMAN], url_var::SECRET_MASK);
        $test_name = 'without_secrets keeps a non-secret field unchanged';
        $t->assert($test_name, $redacted[url_var::USERNAME_HUMAN], users::TEST_USER_NAME);

        // session_recovery_url: when the session token is not valid any more a logged-in (non-ip)
        // user is sent to the login page with the requested page kept as the '9'-prefixed back
        // target; a valid token or an anonymous / ip user needs no recovery (null)
        $req_url = [url_var::MASK => views::WORD_ID, url_var::ID => 2];
        $recovery = frontend::session_recovery_url(false, true, $req_url);
        $test_name = 'an expired token of a logged-in user shows the login page';
        $t->assert($test_name, $recovery[url_var::MASK], views::LOGIN_ID);
        $test_name = 'the login page keeps the requested page as the back target';
        $t->assert($test_name, $recovery[url_var::BACK . url_var::MASK], views::WORD_ID);
        // redirect_url: after an action the browser gets the next page as a plain get, so the url
        // must not repeat the action, show the session token or carry a typed password
        $act_url = [
            url_var::MASK => views::WORD_ID,
            url_var::ID => word_names::ZH_ID,
            url_var::BACK . url_var::MASK => views::START_ID,
            url_var::POST_SUBMIT => '',
            url_var::SESSION_TOKEN => 'dummy session token for the redirect unit test',
            url_var::USER_PASSWORD => 'dummy unhashed password for the redirect unit test'
        ];
        $redirect = frontend::redirect_url($act_url, $msg);
        $test_name = 'the redirect after an action shows the next page';
        $t->assert_text_contains($test_name, $redirect,
            api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . views::WORD_ID);
        $test_name = 'the redirect after an action keeps the back target';
        $t->assert_text_contains($test_name, $redirect, url_var::BACK . url_var::MASK . url_var::EQ);
        $test_name = 'the redirect after an action does not repeat the action';
        $t->assert_text_not_contains($test_name, $redirect, url_var::POST_SUBMIT . url_var::EQ);
        $test_name = 'the redirect after an action does not show the session token';
        $t->assert_text_not_contains($test_name, $redirect, url_var::SESSION_TOKEN . url_var::EQ);
        $test_name = 'the redirect after an action does not show a password';
        $t->assert_text_not_contains($test_name, $redirect, url_var::USER_PASSWORD . url_var::EQ);
        // a get action mask (e.g. logout) would need the token again, so its page is not redirected
        $test_name = 'a next page that is itself an action is not redirected';
        $t->assert($test_name, frontend::redirect_url([url_var::MASK => views::LOGOUT_ID], $msg), '');

        $test_name = 'a valid token needs no session recovery';
        $t->assert_true($test_name, frontend::session_recovery_url(true, true, $req_url) === null);
        $test_name = 'an anonymous or ip user with an expired token just gets the page again';
        $t->assert_true($test_name, frontend::session_recovery_url(false, false, $req_url) === null);

        $test_name = 'convert the standard url to pod interchangeable url';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ADD_DETAIL_ID . '&id=1&debug=-1';
        $url_pod = $url_test->test_url($url_map->standard_url_to_pod($lib->url_array_with($url), $msg));
        $url_array = $lib->url_array($url_pod);
        // TODO Prio 2 activate
        //$view = $url_array[url_var::MASK_POD];
        //$t->assert($test_name, $view, views::WORD_ADD_ID);
        //$test_name = 'convert human-readable url keys to standard url keys';
        //$verb = $url_array[url_var::VERB];
        //$t->assert($test_name, $verb, 3);
        //$test_name = 'add default value of view for pod independent url';
        //$url = 'http://localhost' . api::MAIN_SCRIPT_REL . '?mask=';
        //$url_array = $url_map->url_to_standard($lib->url_array($url), $usr_msg);
        //$view = $url_array[url_var::MASK];
        //$t->assert($test_name, $view, views::START_CODE);
        $test_name = 'error message if mapping is missing';
        $url = 'http://localhost' . api::MAIN_SCRIPT . '?mask_id=&mapping_missing=3';
        $url_map->url_to_standard($lib->url_array($url), $msg);
        $err_msg = $msg->var_message_text();
        $t->assert($test_name, $err_msg, 'url mapper for "debug" is missing, url mapper for "id" is missing, url mapper for "mapping_missing" is missing');
        // negative: a url key without a pod mapping is reported as missing
        $test_name = 'pod url conversion reports a url key without a pod mapping';
        $pod_msg = new user_message_ui();
        $no_map_url = [url_var::MASK => views::WORD_ID, test_const::URL_KEY_UNKNOWN => '1'];
        $url_map->standard_url_to_pod($no_map_url, $pod_msg);
        $t->assert_true($test_name, $pod_msg->has_msg_id(msg_id::URL_MAP_MISSING));

    }

}
