<?php

/*

    test/unit_write/db_cache_page_write_tests.php - db write tests for the cached html pages
    ---------------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::WEB . 'frontend.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class db_cache_page_write_tests
{

    // test values for the cached html page write tests
    const string TV_URL = 'http://zukunft.com/test_cache_page';
    const string TV_URL_UNKNOWN = 'http://zukunft.com/test_cache_page_unknown';
    const string TV_URL_IP_USER = 'http://zukunft.com/test_cache_page_ip_user';
    const string TV_HTML = '<html lang="en"><body>cache test</body></html>';
    const string TV_HTML_RENEWED = '<html lang="en"><body>cache test renewed</body></html>';

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write db_cache_page ';
        $t->header($ts);

        // an url that has never been rendered returns null
        // checked via assert_true because the assert function reports a null result as an error
        $cac_page = new db_cache_page();
        $test_name = 'an unknown url returns null instead of a cached html page';
        $html = $cac_page->html_by_url(self::TV_URL_UNKNOWN, $msg);
        $t->assert_true($test_name, $html === null);

        // caching an html page for a new url creates a database row
        $test_name = 'caching an html page for a new url is saved without error';
        $t->assert_true($test_name, $cac_page->save_html(self::TV_URL, self::TV_HTML, $msg));
        $page_id = $cac_page->id();

        // the cached html page can be retrieved by the url
        $cac_check = new db_cache_page();
        $test_name = 'the cached html page is returned for the url';
        $t->assert($test_name, $cac_check->html_by_url(self::TV_URL, $msg), self::TV_HTML);

        // caching the same url again replaces the html page instead of adding a row
        $test_name = 'caching the same url again is saved without error';
        $t->assert_true($test_name, $cac_page->save_html(self::TV_URL, self::TV_HTML_RENEWED, $msg));
        $test_name = 'the renewed html page is returned for the url';
        $t->assert($test_name, $cac_check->html_by_url(self::TV_URL, $msg), self::TV_HTML_RENEWED);
        $test_name = 'the renewed html page has replaced the db row instead of adding one';
        $t->assert($test_name, $cac_check->id(), $page_id);

        // the last rendering time is remembered
        $test_name = 'the last update timestamp of the cached html page is set';
        $t->assert_true($test_name, $cac_check->last_update != null);

        // simulate the request of a user without database change permission (e.g. an ip user):
        // filling the html page cache is a system action done as the system user regardless of
        // the requesting user, so the cache row must be written even during the request of a
        // user that is not allowed to change any data
        $ui = new frontend('db_cache_page_write_tests');
        $ui->save_html_page(new db_cache_page(), self::TV_URL_IP_USER, self::TV_HTML);
        $test_name = 'the html page cache is filled even if the requesting user cannot change data';
        $t->assert($test_name, $cac_check->html_by_url(self::TV_URL_IP_USER, $msg), self::TV_HTML);

        // a change of a user makes the own pages of that user outdated, so they are removed and
        // rendered again on the next request (see frontend::drop_cached_pages)
        $test_name = 'the cached pages of a user are removed when the user changes data';
        $std_url = url_var::MASK . url_var::EQ . views::WORD_ID
            . url_var::ADD_ID . word_names::TEST_ADD_ID;
        $usr_url = $std_url . url_var::ADD . url_var::USER . url_var::EQ . users::SYSTEM_TEST_ID;
        // the id of the other user starts with the same digits, so the key of this page ends with
        // the user key of the test user without naming that user
        $usr_url_other = $std_url . url_var::ADD . url_var::USER . url_var::EQ
            . users::SYSTEM_TEST_ID . '9';
        $cac_page->save_html($std_url, self::TV_HTML, $msg);
        $cac_page->save_html($usr_url, self::TV_HTML, $msg);
        $cac_page->save_html($usr_url_other, self::TV_HTML, $msg);
        $cac_page->del_by_user(users::SYSTEM_TEST_ID, $msg);
        $t->assert_true($test_name, $cac_check->html_by_url($usr_url, $msg) === null);
        $test_name = '... and the page of a user with a similar id is kept';
        $t->assert($test_name, $cac_check->html_by_url($usr_url_other, $msg), self::TV_HTML);
        $test_name = '... like the standard page, which the change of a user does not touch';
        $t->assert($test_name, $cac_check->html_by_url($std_url, $msg), self::TV_HTML);

        // a change of the standard data can be shown by every page, so the complete cache is dropped
        $test_name = 'a change of the standard data removes every cached page';
        $cac_page->del_all($msg);
        $t->assert_true($test_name, $cac_check->html_by_url($std_url, $msg) === null);
        $test_name = '... including the page of a user';
        $t->assert_true($test_name, $cac_check->html_by_url($usr_url_other, $msg) === null);
        $test_name = '... and the pages of the tests above';
        $t->assert_true($test_name, $cac_check->html_by_url(self::TV_URL, $msg) === null);

    }

}
