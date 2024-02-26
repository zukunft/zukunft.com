<?php

/*

    test/unit/db_setup.php - testing of the db setup scripts
    ----------------------
  

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

namespace unit;

include_once SERVICE_IMPORT_PATH . 'import.php';

use cfg\db\sql_db;
use cfg\library;
use test\test_cleanup;

class db_setup_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $lib = new library();

        $t->subheader('DB setup unit tests');

        $test_name_all = 'Combine the class SQL setup scripts and compare with the final sql setup script';
        $db = new sql_db();
        foreach (sql_db::DB_LIST as $db_type) {
            $sql_fixed = resource_file(DB_RES_PATH . DB_SETUP_PATH . $db->path($db_type) . DB_SETUP_SQL_FILE);
            foreach (sql_db::DB_TABLE_CLASSES as $class) {
                $name = $lib->class_to_name($class);

                $test_name = $name . ' sql create is part of setup sql for ' . $db_type;
                $sql_create = test_resource_file(
                    DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_create' . $db->ext($db_type) . '.sql');
                $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);

                $test_name = $name . ' sql index is part of setup sql for ' . $db_type;
                $sql_create = test_resource_file(
                    DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_index' . $db->ext($db_type) . '.sql');
                $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);

                $filename = DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_foreign_key' . $db->ext($db_type) . '.sql';
                if (has_resource_file($filename)) {
                    $test_name = $name . ' foreign key sql is part of setup sql for ' . $db_type;
                    $sql_create = test_resource_file($filename);
                    $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);
                }
            }
        }
        // TODO check that nothing is remaining in the sql setup statement
        //      or that all sql statements have a created by an sql creator call
        //$t->assert($test_name_all, $result->get_last_message(), $target);
    }

}