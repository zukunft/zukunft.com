<?php

/*

    test/unit/sql_tests.php - unit testing of the basic sql creation functions
    -----------------------

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'users.php';
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\const\fields\triple_fields;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\const\files as test_files;

class sql_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $t->name = 'sql->';


        // start the test section (ts)
        $ts = 'unit sql ';
        $t->header($ts);

        $t->subheader($ts . 'role');
        $test_name = ' user role postgres';
        $created = $sc->create_db_role(SQL_DB_USER_DEFAULT, SQL_DB_PASSWD_FALLBACK);
        $expected = file_get_contents(files::DB_SETUP_PG_PATH . files::DB_ROLE_FILE);
        $t->assert_sql($test_name, $created, $expected);
        //$test_name = ' user role mysql';

        $t->subheader($ts . 'count');
        $test_name = ' count of formulas';
        $sc->set_class(formula::class);
        $created = $sc->count_sql();
        $expected = file_get_contents(test_files::FORMULA_COUNT);
        $t->assert_sql($test_name, $created, $expected);
        $test_name = ' count of users';
        $sc->set_class(user::class);
        $created = $sc->count_sql();
        $expected = file_get_contents(test_files::USER_COUNT);
        $t->assert_sql($test_name, $created, $expected);

        // del_sql_list_without_log deletes all rows of a class whose id is in the given list, used
        // e.g. to remove the formula elements of a list or the change log during the test cleanup
        $t->subheader($ts . 'delete list without log');

        // TODO Prio 1 use sql files instead of a fixed text
        // the call of the function under test is part of the block, so that the test coverage
        // report counts it, while the db selection above stays out of the block, because it is
        // the setup of the test and not the tested function (see code_test_coverage)
        $sc->reset(sql_db::POSTGRES);
        $test_name = ' delete elements by id list postgres';
        $qp = $sc->del_sql_list_without_log(element::class, element::FLD_ID, [1, 2, 3]);
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE element_delete_by_ids (bigint[]) AS DELETE FROM elements WHERE element_id = ANY ($1);');

        $test_name = ' delete elements by id list query name';
        $t->assert($test_name, $qp->name, 'element_delete_by_ids');

        $test_name = ' delete elements by id list passes the ids as an array parameter';
        $t->assert($test_name, implode(',', $qp->par), '{1,2,3}');

        // postgres uses a numbered array parameter with ANY, not the mysql question mark with IN
        $test_name = ' the postgres delete by id list has no mysql question mark parameter';
        $t->assert_text_not_contains($test_name, $qp->sql, '?');

        $sc->reset(sql_db::MYSQL);
        $test_name = ' delete elements by id list mysql';
        $qp = $sc->del_sql_list_without_log(element::class, element::FLD_ID, [1, 2, 3]);
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE element_delete_by_ids FROM 'DELETE FROM elements WHERE element_id IN (?)';");

        // del_sql_list_by_text deletes the rows whose text field matches the given text, used to
        // remove the cached html pages of one user, whose key ends with the id of that user
        // (see db_cache_page::del_by_user)
        $t->subheader($ts . 'delete list by text');

        $usr_key = url_var::ADD . url_var::USER . url_var::EQ . users::SYSTEM_TEST_ID;

        $sc->reset(sql_db::POSTGRES);
        $test_name = ' delete the cached pages of a user postgres';
        $qp = $sc->del_sql_list_by_text(
            db_cache_page::class, db_cache_page::FLD_URL, $usr_key, sql_par_type::LIKE_L);
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE db_cache_page_delete_by_text (text) AS DELETE FROM db_cache_pages '
            . 'WHERE url ' . sql::LIKE_NO_UP_CASE . ' $1;');
        $test_name = ' delete the cached pages of a user query name';
        $t->assert($test_name, $qp->name, 'db_cache_page_delete_by_text');
        $test_name = ' the pattern of a user matches only the keys that end with the user id';
        $t->assert($test_name, implode(',', $qp->par), '%' . $usr_key);

        $sc->reset(sql_db::MYSQL);
        $test_name = ' delete the cached pages of a user mysql';
        $qp = $sc->del_sql_list_by_text(
            db_cache_page::class, db_cache_page::FLD_URL, $usr_key, sql_par_type::LIKE_L);
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE db_cache_page_delete_by_text FROM 'DELETE FROM db_cache_pages "
            . "WHERE url " . sql::LIKE_LOWER_CASE . " ?';");

        // del_sql_all empties a table that only holds data which can be created again, used to
        // remove every cached html page when the standard data has changed (see db_cache_page::del_all)
        $t->subheader($ts . 'delete all');

        $sc->reset(sql_db::POSTGRES);
        $test_name = ' delete all cached pages postgres';
        $qp = $sc->del_sql_all(db_cache_page::class);
        $t->assert_sql($test_name, $qp->sql,
            'PREPARE db_cache_page_delete_all AS DELETE FROM db_cache_pages;');
        $test_name = ' delete all cached pages needs no parameter';
        $t->assert($test_name, implode(',', $qp->par), '');

        $sc->reset(sql_db::MYSQL);
        $test_name = ' delete all cached pages mysql';
        $qp = $sc->del_sql_all(db_cache_page::class);
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE db_cache_page_delete_all FROM 'DELETE FROM db_cache_pages';");

        // a sub-select selects the rows whose id is used in the not excluded rows of another table,
        // so e.g. a phrase used by many triples of one verb is still selected only once
        $t->subheader($ts . 'where in sub-select');
        $test_name = ' the phrases used as the to side of the triples of a verb postgres';
        $sc->set_db_type(sql_db::POSTGRES);
        $sc->set_class(phrase::class);
        $sc->set_name('phrase_in_sub_test');
        $sc->add_where_in_sub(phrase::FLD_ID, triple::class, triple_fields::FLD_TO, verb_db::FLD_ID, verbs::IS_ID);
        $t->assert_text_contains($test_name, $sc->sql(),
            'phrase_id IN (SELECT to_phrase_id FROM triples WHERE verb_id = $1 AND COALESCE(excluded, 0) = 0)');
        // the sub-select uses a placeholder, so its value must be passed with the other parameters
        $test_name = ' the value of the sub-select filter is passed as parameter';
        $t->assert($test_name, implode(',', $sc->get_par()), (string)verbs::IS_ID);
        $test_name = ' the phrases used as the to side of the triples of a verb mysql';
        $sc->set_db_type(sql_db::MYSQL);
        $sc->set_class(phrase::class);
        $sc->set_name('phrase_in_sub_test');
        $sc->add_where_in_sub(phrase::FLD_ID, triple::class, triple_fields::FLD_TO, verb_db::FLD_ID, verbs::IS_ID);
        $t->assert_text_contains($test_name, $sc->sql(),
            'phrase_id IN (SELECT to_phrase_id FROM triples WHERE verb_id = ? AND COALESCE(excluded, 0) = 0)');
        $test_name = ' a select without a sub-select condition has no sub-select';
        $sc->set_db_type(sql_db::POSTGRES);
        $sc->set_class(phrase::class);
        $sc->set_name('phrase_no_sub_test');
        $sc->add_where(phrase::FLD_ID, verbs::IS_ID);
        $t->assert_text_not_contains($test_name, $sc->sql(), 'IN (SELECT');

        // a pattern search ignores the upper and lower case: postgres needs ILIKE, while the mysql LIKE of the
        // default collation already ignores the case
        $t->subheader($ts . 'case-insensitive pattern');
        $test_name = ' a postgres pattern search ignores the case';
        $sc->set_db_type(sql_db::POSTGRES);
        $sc->set_class(phrase::class);
        $sc->set_name('phrase_like_test');
        $sc->add_where(phrase::FLD_NAME, word_names::MATH, sql_par_type::LIKE_R);
        $t->assert_text_contains($test_name, $sc->sql(), phrase::FLD_NAME . ' ' . sql::LIKE_NO_UP_CASE . ' $1');
        $test_name = ' a mysql pattern search uses the case-insensitive LIKE';
        $sc->set_db_type(sql_db::MYSQL);
        $sc->set_class(phrase::class);
        $sc->set_name('phrase_like_test');
        $sc->add_where(phrase::FLD_NAME, word_names::MATH, sql_par_type::LIKE_R);
        $mysql_like = $sc->sql();
        $t->assert_text_contains($test_name, $mysql_like, phrase::FLD_NAME . ' ' . sql::LIKE_LOWER_CASE . ' ?');
        $test_name = ' ... without the postgres ILIKE';
        $t->assert_text_not_contains($test_name, $mysql_like, sql::LIKE_NO_UP_CASE);

    }

}