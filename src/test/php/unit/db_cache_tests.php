<?php

/*

    test/unit/db_cache_tests.php - unit testing of the database cache
    ----------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_HELPER . 'db_cache_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'db_cache_statuum.php';
include_once html_paths::HTML . 'html_base.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_db;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\test\php\create\test_db_caches;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class db_cache_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $t_db_cache = new test_db_caches($t);
        $t->name = 'db_cache->';
        $t->resource_path = 'db/db_cache/';

        $ts = 'unit db_cache ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $cac = new db_cache($t->usr1);
        $t->assert_sql_table_create($cac);
        $t->assert_sql_index_create($cac);
        $t->assert_sql_foreign_key_create($cac);

        // the cached html pages table has no foreign keys,
        // so only the table and index creation are checked
        $t->subheader($ts . 'pages sql setup');
        $cac_page = new db_cache_page();
        $t->assert_sql_table_create($cac_page);
        $t->assert_sql_index_create($cac_page);


        $t->subheader($ts . 'sql read');

        // sql to load one batch db_cache
        $cac = new db_cache($t->usr1);
        $t->assert_sql_by_id($sc, $cac);

        // sql to load one cached html page by id
        $cac_page = new db_cache_page();
        $t->assert_sql_by_id($sc, $cac_page);

        // sql to load the cache of one type e.g. the system types that are the same for all users
        $cac = new db_cache($t->usr1);
        $sc->reset(sql_db::POSTGRES);
        $qp = $cac->load_sql_by_type_id($sc, db_cache_types::SYSTEM_CONFIG_ID);
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $cac->load_sql_by_type_id($sc, db_cache_types::SYSTEM_CONFIG_ID);
        $t->assert_qp($qp, $sc->db_type);

        // sql to load the cache of one type and user e.g. the config values that each user can overwrite
        $sc->reset(sql_db::POSTGRES);
        $qp = $cac->load_sql_by_type_id($sc, db_cache_types::SYSTEM_CONFIG_ID, $t->usr1->id());
        $t->assert_qp($qp, $sc->db_type);
        $sc->reset(sql_db::MYSQL);
        $qp = $cac->load_sql_by_type_id($sc, db_cache_types::SYSTEM_CONFIG_ID, $t->usr1->id());
        $t->assert_qp($qp, $sc->db_type);

        // sql to load a list of open batch db_caches
        $t_usr = new test_users($t);
        $sys_usr = $t_usr->system_user();

        $t->subheader($ts . 'sql write');
        $cac = $t_db_cache->db_cache();
        // for db_cache a log is not needed because the table rows are never expected to be deleted
        $t->assert_sql_insert($sc, $cac);
        $cac = $t_db_cache->db_cache_filled();
        $db_cache_db = $cac->clone_reset();
        $t->assert_sql_update($sc, $cac, $db_cache_db);
        $t->assert_sql_delete($sc, $cac);

        $t->subheader($ts . 'pages sql write');
        $cac_page = $t_db_cache->db_cache_page();
        // like db_cache the cached html pages are never logged and never expected to be deleted
        $t->assert_sql_insert($sc, $cac_page);
        $cac_page = $t_db_cache->db_cache_page_filled();
        $cac_page_db = $cac_page->clone_reset();
        $t->assert_sql_update($sc, $cac_page, $cac_page_db);
        $t->assert_sql_delete($sc, $cac_page);

        $t->subheader($ts . 'api');

        $t_db_cache = new test_db_caches($t);
        $cac = $t_db_cache->db_cache();
        $t->assert_api($cac);

        $cac_page = $t_db_cache->db_cache_page();
        $t->assert_api($cac_page);


        $t->subheader($ts . 'update fields');

        // the data of a loaded cache row is a json array (row_mapper decodes it) and the data to
        // write is the api message text, so both are compared and written as text
        $cac = $t_db_cache->db_cache_up_to_date();
        $cac->data = '{"body":{"lists":[4]}}';
        $cac_db = $t_db_cache->db_cache_up_to_date();
        $cac_db->data = ['body' => ['lists' => [1]]];
        $msg = new user_message($t->usr1);
        $fvt_lst = $cac->db_fields_changed($cac_db, $msg);
        $test_name = 'the new api message replaces the cached json array';
        $t->assert($test_name, $fvt_lst->get(db_cache_db::FLD_DATA, $msg)?->value, $cac->data);
        $test_name = 'the cached json array is the compare value of the update';
        $t->assert($test_name, $fvt_lst->get(db_cache_db::FLD_DATA, $msg)?->old, json_encode($cac_db->data));

        $cac_db->data = json_decode($cac->data, true);
        $msg = new user_message($t->usr1);
        $fvt_lst = $cac->db_fields_changed($cac_db, $msg);
        $test_name = 'an unchanged api message is not written again';
        $t->assert_null($test_name, $fvt_lst->get(db_cache_db::FLD_DATA, $msg, true)?->name);

        $t->subheader($ts . 'age');

        // only a cache entry with data and a last update within the max cache age can be used
        $cac = $t_db_cache->db_cache_up_to_date();
        $test_name = 'a just filled cache entry is used';
        $t->assert_false($test_name, $cac->is_outdated());

        $cac = $t_db_cache->db_cache_filled();
        $test_name = 'a cache entry older than the max cache age is refilled';
        $t->assert_true($test_name, $cac->is_outdated());

        $cac = $t_db_cache->db_cache_up_to_date();
        $cac->data = null;
        $test_name = 'a cache entry without data is refilled';
        $t->assert_true($test_name, $cac->is_outdated());

        $cac = $t_db_cache->db_cache_up_to_date();
        $cac->last_update = null;
        $test_name = 'a cache entry without update time is refilled';
        $t->assert_true($test_name, $cac->is_outdated());


        $t->subheader($ts . 'session token swap');

        // a cached page must not carry the rendering session's anti-csrf token to another session:
        // on save the token is replaced by a placeholder, on read the reading user's token is filled
        // in - covering both the crud form hidden field and the logout / error_update link href
        $token = 'aaaa1111';
        $page =
            '<a href="view.php?m=64&token=' . $token . '">logout</a>'
            . '<input name="token" value="' . $token . '">';
        $stored = db_cache_page::strip_session_token($page, $token);
        $test_name = 'the session token is removed from the stored page';
        $t->assert_false($test_name, str_contains($stored, $token));
        $test_name = 'the stored page keeps the token placeholder for both the link and the form';
        $t->assert($test_name, substr_count($stored, db_cache_page::SESSION_TOKEN_PLACEHOLDER), 2);

        $other = 'bbbb2222';
        $served = db_cache_page::restore_session_token($stored, $other);
        $test_name = 'the reading session token is filled in on read';
        $t->assert($test_name, $served,
            '<a href="view.php?m=64&token=' . $other . '">logout</a>'
            . '<input name="token" value="' . $other . '">');
        $test_name = 'the rendering session token is not served to another session';
        $t->assert_false($test_name, str_contains($served, $token));

        // an empty session token must not corrupt the page (str_replace of '' would be destructive)
        $test_name = 'an empty token leaves the page unchanged on save';
        $t->assert($test_name, db_cache_page::strip_session_token($page, ''), $page);


        $t->subheader($ts . 'user message swap');

        // a user message belongs to one request and must never be cached with the page:
        // on save the notification before the placeholder is removed and on read
        // the message of the current request is added at the placeholder
        $html = new html_base();
        $msg_html = $html->dsp_notification('please log in');
        $clean_page = '<main>content</main>' . api::USER_MSG_PLACEHOLDER . '<footer></footer>';
        $page_with_msg = '<main>content</main>' . $msg_html . api::USER_MSG_PLACEHOLDER . '<footer></footer>';
        $test_name = 'the user message is removed from the page on save';
        $t->assert($test_name, db_cache_page::strip_user_msg($page_with_msg), $clean_page);
        $test_name = 'a page without a message is not changed on save';
        $t->assert($test_name, db_cache_page::strip_user_msg($clean_page), $clean_page);
        // an alert that is part of the page content is not the user message of the request
        $content_alert_page = '<main><div class="' . html_base::CLASS_NOTIFICATION . '">content alert</div></main>'
            . api::USER_MSG_PLACEHOLDER . '<footer></footer>';
        $test_name = 'an alert of the page content is not removed on save';
        $t->assert($test_name, db_cache_page::strip_user_msg($content_alert_page), $content_alert_page);

        $test_name = 'the message of the current request is added to a page loaded from cache';
        $t->assert($test_name, db_cache_page::add_user_msg($clean_page, $msg_html), $page_with_msg);
        $test_name = 'the placeholder is kept, so a message can be added to the same page again';
        $t->assert_text_contains($test_name,
            db_cache_page::add_user_msg($clean_page, $msg_html), api::USER_MSG_PLACEHOLDER);
        $test_name = 'without a message the page from the cache is not changed';
        $t->assert($test_name, db_cache_page::add_user_msg($clean_page, ''), $clean_page);
        $test_name = 'a save after adding a message restores the clean page';
        $t->assert($test_name,
            db_cache_page::strip_user_msg(db_cache_page::add_user_msg($clean_page, $msg_html)), $clean_page);

    }

}
