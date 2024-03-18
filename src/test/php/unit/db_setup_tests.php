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

include_once MODEL_IMPORT_PATH . 'import.php';

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
            $db->db_type = $db_type;
            $sql_fixed = resource_file(DB_RES_PATH . DB_SETUP_PATH . $db->path($db_type) . DB_SETUP_SQL_FILE);
            $sql_fixed_trim = $lib->trim_sql($sql_fixed);
            foreach (sql_db::DB_TABLE_CLASSES as $class) {
                $name = $lib->class_to_name($class);

                $test_name = $name . ' sql create is part of setup sql for ' . $db_type;
                $sql_create = test_resource_file(
                    DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_create' . $db->ext($db_type) . '.sql');
                $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);
                $sql_fixed_trim = str_replace($lib->trim_sql($sql_create),'', $sql_fixed_trim);

                $test_name = $name . ' sql index is part of setup sql for ' . $db_type;
                $sql_create = test_resource_file(
                    DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_index' . $db->ext($db_type) . '.sql');
                $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);
                $sql_fixed_trim = str_replace($lib->trim_sql($sql_create),'', $sql_fixed_trim);

                $filename = DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_foreign_key' . $db->ext($db_type) . '.sql';
                if (has_resource_file($filename)) {
                    $test_name = $name . ' foreign key sql is part of setup sql for ' . $db_type;
                    $sql_create = test_resource_file($filename);
                    $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);
                }
                $sql_fixed_trim = str_replace($lib->trim_sql($sql_create),'', $sql_fixed_trim);
            }

            foreach (sql_db::DB_VIEW_CLASSES as $class) {
                $name = $lib->class_to_name($class);

                $test_name = $name . ' sql view is part of setup sql for ' . $db_type;
                $sql_create = test_resource_file(
                    DB_RES_PATH . $lib->class_to_path($name) . DIRECTORY_SEPARATOR .
                    $name . '_view' . $db->ext($db_type) . '.sql');
                $t->assert_sql_contains($test_name, $sql_fixed, $sql_create);
                $sql_fixed_trim = str_replace($lib->trim_sql($sql_create),'', $sql_fixed_trim);
            }

            // check header and footer
            $test_name = 'Check header for ' . $db_type;
            $header = $lib->trim_sql($db->sql_setup_header());
            $t->assert_sql_contains($test_name, $sql_fixed, $header);
            $test_name = 'Check footer for ' . $db_type;
            $footer = $lib->trim_sql($db->sql_setup_footer());
            $t->assert_sql_contains($test_name, $sql_fixed, $footer);
            $sql_fixed_trim = str_replace($header,'', $sql_fixed_trim);
            $sql_fixed_trim = str_replace($lib->trim_sql($db->sql_separator_index()),'', $sql_fixed_trim);
            $sql_fixed_trim = str_replace($lib->trim_sql($db->sql_separator_foreign_key()),'', $sql_fixed_trim);
            $sql_fixed_trim = str_replace($footer,'', $sql_fixed_trim);
            $sql_fixed_trim = trim($sql_fixed_trim);

            // check that nothing is remaining in the sql setup statement
            $test_name = 'sql setup remaining for db ' . $db_type;
            $t->assert($test_name, $sql_fixed_trim, '');

        }
    }

}