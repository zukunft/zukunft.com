<?php

/*

    test_recreate_db.php - trunk all table and create the database from scratch for testing the setup process:
    --------------------

    NEVER use this in production! Just for the development process

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const PHP_TEST_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;

include_once PHP_PATH . 'zu_lib.php';
include_once SERVICE_IMPORT_PATH . 'import_file.php';

use cfg\component\position_type_list;
use cfg\component\component_type_list;
use cfg\config;
use cfg\db\db_check;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\element_type_list;
use cfg\formula_link_type_list;
use cfg\formula_type_list;
use cfg\group\group;
use cfg\job;
use cfg\job_type_list;
use cfg\language_form_list;
use cfg\language_list;
use cfg\library;
use cfg\log\change_action_list;
use cfg\log\change_field_list;
use cfg\log\change_table_list;
use cfg\phrase_types;
use cfg\protection_type_list;
use cfg\ref_type_list;
use cfg\share_type_list;
use cfg\source_type_list;
use cfg\sys_log_function;
use cfg\user;
use cfg\value\value;
use cfg\view_type_list;
use html\html_base;
use unit_read\all_unit_read_tests;

global $errors;

// open database and display header
$db_con = prg_start("test_recreate_db");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($usr->is_admin()) {

        $html = new html_base();
        $html->echo( 'Recreate database');
        $html->echo("\n");

        // load the testing base functions
        include_once '../src/test/php/utils/test_base.php';

        // use the system user for the database updates
        global $usr;
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);
        $sys_usr = $usr;

        // drop all old database tables
        foreach (DB_TABLE_LIST as $table_name) {
            $db_con->drop_table($table_name);
        }
        $db_con->setup_db();
        $html->echo( 'Database recreated');
        $html->echo("\n");

    }
}

//prg_end($db_con);

