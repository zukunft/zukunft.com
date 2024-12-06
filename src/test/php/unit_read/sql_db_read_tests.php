<?php

/*

    test/php/unit_read/sql_db.php - unit testing of the SQL abstraction layer functions with the current database
    -----------------------------
  

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

namespace unit_read;

use cfg\db\sql_db;
use cfg\user_message;
use shared\library;
use test\test_cleanup;

class sql_db_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;

        // init
        $t->name = 'sql read db->';

        $t->header('Unit database tests of the SQL abstraction layer class (database/sql_db.php)');

        $t->subheader('Database upgrade functions');

        $result = $db_con->has_column('user_values', 'user_value');
        $t->assert('change_column_name', $result, false);

        $result = $db_con->has_column('user_values', 'numeric_value');
        $t->assert('change_column_name', $result, true);

        $result = $db_con->change_column_name(
            'user_values', 'user_value', 'numeric_value'
        );
        $t->assert('change_column_name', $result, '');

        $test_name = 'csv check for the change log ';
        $this->assert_table_field_preload($test_name, $t, $db_con);

    }

    private function assert_table_field_preload(string $test_name, test_cleanup $t, sql_db $db_con): void
    {
        global $cng_tbl_cac;
        global $cng_fld_cac;

        $tbl_msg = new user_message();
        $fld_msg = new user_message();
        $next_tbl_id = $cng_tbl_cac->count() + 1;
        $next_fld_id = $cng_fld_cac->count() + 1;

        $tbl_lst = $db_con->get_tables();
        foreach ($tbl_lst as $tbl) {
            if (!$this->table_no_change_log($tbl)) {
                $tbl_id = $cng_tbl_cac->id($tbl);
                if ($tbl_id == -1) {
                    $tbl_msg->add_message($next_tbl_id . ",'" . $tbl . "',,'" . $tbl . "'");
                    $next_tbl_id++;
                } else {
                    $fld_lst = $db_con->get_fields($tbl);
                    foreach ($fld_lst as $fld) {
                        $fld_id = $cng_fld_cac->id($tbl_id . $fld);
                        if ($fld_id == -1) {
                            $fld_msg->add_message($next_fld_id . "," . $fld . "," . $tbl_id . ",,");
                            $next_fld_id++;
                        } else {
                            log_debug('found field ' . $tbl_id . $tbl);
                        }
                    }
                }
            }
        }
        $t->assert($test_name . 'tables', $tbl_msg->all_message_text(), '');
        $t->assert($test_name . 'fields', $fld_msg->all_message_text(), '');
    }

    private function table_no_change_log(string $tbl_name): bool
    {
        $result = false;
        $lib = new library();
        foreach (CLASSES_NO_CHANGE_LOG as $class) {
            if ($tbl_name == $class) {
                $result = true;
            } else {
                $no_log_name = $lib->class_to_table($class);
                if ($tbl_name == $no_log_name) {
                    $result = true;
                } else {
                    if (str_ends_with($class, '*')) {
                        if (str_starts_with($tbl_name, rtrim($class, "*"))) {
                            $result = true;
                        }
                    }
                }
            }

        }
        return $result;
    }
}

