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
include_once test_paths::CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
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
        $sc->reset(sql_db::POSTGRES);
        $qp = $sc->del_sql_list_without_log(element::class, element::FLD_ID, [1, 2, 3]);
        $test_name = ' delete elements by id list postgres';
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
        $qp = $sc->del_sql_list_without_log(element::class, element::FLD_ID, [1, 2, 3]);
        $test_name = ' delete elements by id list mysql';
        $t->assert_sql($test_name, $qp->sql,
            "PREPARE element_delete_by_ids FROM 'DELETE FROM elements WHERE element_id IN (?)';");

    }

}