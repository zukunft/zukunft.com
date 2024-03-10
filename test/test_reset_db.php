<?php

/*

  test_reset_db.php - To reset the main database table: NEVER use this in production! Just for the development process
  -----------------


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

const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
const PHP_TEST_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;

include_once PHP_PATH . 'zu_lib.php';
include_once SERVICE_IMPORT_PATH . 'import_file.php';

use cfg\component\component_pos_type_list;
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
$db_con = prg_start("test_reset_db");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {
    if ($usr->is_admin()) {

        // load the testing base functions
        include_once '../src/test/php/utils/test_base.php';

        // use the system user for the database updates
        global $usr;
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);
        $sys_usr = $usr;

        // run reset the main database tables
        run_db_truncate($sys_usr);
        $db_con->truncate_table_all();
        $db_con->reset_seq_all();
        $db_con->reset_config();
        import_system_users();

        // recreate the code link database rows
        $db_con->db_fill_code_links();
        import_verbs($usr);

        // reopen the database to reload the list cache
        $db_con->close();
        $db_con = prg_restart("test_reset_db");

        // reload the session user parameters
        $usr = new user;
        $result = $usr->get();

        // reopen the database to reload the verb cache
        $db_con->close();
        $db_con = prg_restart("test_reset_db");

        // reload the base configuration
        $job = new job($sys_usr);
        $job_id = $job->add(job_type_list::BASE_IMPORT);

        import_base_config($sys_usr);
        import_config($usr);

        // use the system user again to create the database test datasets
        global $usr;
        $usr = new user;
        $usr->load_by_id(SYSTEM_USER_ID);
        $sys_usr = $usr;

        // create the test dataset to check the basic write functions
        $t = new all_unit_read_tests();
        $t->set_users();
        $t->create_test_db_entries($t);

        // remove the test dataset for a clean database
        // TODO use the user message object instead of a string
        $cleanup_result = $t->cleanup();
        if (!$cleanup_result) {
            log_err('Cleanup not successful, because ...');
        } else {
            if (!$t->cleanup_check()) {
                log_err('Cleanup check not successful.');
            }
        }

        // reload the session user parameters
        $usr = new user;
        $result = $usr->get();

        /*
         * For testing the system setup
         */

        // drop the database

        // create the database from the sql structure file

        // reload the system database rows (all db rows, that have a code id)

        echo "\n";
        echo $errors . ' internal errors';

    }
}

//prg_end($db_con);

/**
 * truncate all tables (use only for system testing)
 */
function run_db_truncate(user $sys_usr): void
{
    $lib = new library();

    // the tables in order to avoid the usage of CASCADE
    $table_names = array(
        sql_db::TBL_USER_PREFIX . value::class,
        value::class,
        sql_db::TBL_RESULT,
        sql_db::TBL_ELEMENT,
        sql_db::TBL_ELEMENT_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK,
        sql_db::TBL_FORMULA_LINK,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA,
        sql_db::TBL_FORMULA,
        sql_db::TBL_FORMULA_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_COMPONENT_LINK,
        sql_db::TBL_COMPONENT_LINK,
        sql_db::TBL_COMPONENT_LINK_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_COMPONENT,
        sql_db::TBL_COMPONENT,
        sql_db::TBL_COMPONENT_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW,
        sql_db::TBL_VIEW,
        sql_db::TBL_VIEW_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_GROUP,
        sql_db::TBL_GROUP,
        sql_db::TBL_VERB,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE,
        sql_db::TBL_TRIPLE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD,
        sql_db::TBL_WORD,
        sql_db::TBL_PHRASE_TYPE,
        sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE,
        sql_db::TBL_SOURCE,
        sql_db::TBL_SOURCE_TYPE,
        sql_db::TBL_REF,
        sql_db::TBL_REF_TYPE,
        sql_db::TBL_CHANGE_LINK,
        sql_db::TBL_CHANGE,
        sql_db::TBL_CHANGE_ACTION,
        sql_db::TBL_CHANGE_FIELD,
        sql_db::TBL_CHANGE_TABLE,
        sql_db::TBL_CONFIG,
        sql_db::TBL_TASK,
        sql_db::TBL_TASK_TYPE,
        //sql_db::TBL_SYS_SCRIPT,
        sql_db::TBL_TASK,
        sql_db::TBL_SYS_LOG,
        sql_db::TBL_SYS_LOG_STATUS,
        sys_log_function::class,
        sql_db::TBL_SHARE,
        sql_db::TBL_PROTECTION,
        sql_db::TBL_USER,
        sql_db::TBL_USER_PROFILE
    );
    $html = new html_base();
    $html->echo("\n");
    $html->echo('truncate ');
    $html->echo("\n");

    // truncate tables that have already a build in truncate statement creation
    $sql = '';
    $sc = new sql();
    $grp = new group($sys_usr);
    $sql .= $grp->sql_truncate($sc);

    global $db_con;

    try {
        $db_con->exe($sql);
    } catch (Exception $e) {
        log_err('Cannot truncate based on sql ' . $sql . '" because: ' . $e->getMessage());
    }

    // truncate the other tables
    foreach ($table_names as $class) {
        $table_name = $lib->class_to_name($class);
        $db_con->truncate_table($table_name);
    }

    // reset the preloaded data
    run_preloaded_truncate();
}

function run_preloaded_truncate(): void
{
    global $system_users;
    global $user_profiles;
    global $phrase_types;
    global $formula_types;
    global $formula_link_types;
    global $element_types;
    global $view_types;
    global $component_types;
    global $component_link_types;
    global $component_position_types;
    global $ref_types;
    global $source_types;
    global $share_types;
    global $protection_types;
    global $languages;
    global $language_forms;
    global $verbs;
    global $system_views;
    global $sys_log_stati;
    global $job_types;
    global $change_action_list;
    global $change_table_list;
    global $change_field_list;

    //$system_users =[];
    //$user_profiles =[];
    $phrase_types = new phrase_types();
    $formula_types = new formula_type_list();
    $formula_link_types = new formula_link_type_list();
    $element_types = new element_type_list();
    $view_types = new view_type_list();
    $component_types = new component_type_list();
    // not yet needed?
    //$component_link_types = new component_link_type_list();
    $component_position_types = new component_pos_type_list();
    $ref_types = new ref_type_list();
    $source_types = new source_type_list();
    $share_types = new share_type_list();
    $protection_types = new protection_type_list();
    $languages = new language_list();
    $language_forms = new language_form_list();
    $job_types = new job_type_list();
    $change_action_list = new change_action_list();
    $change_table_list = new change_table_list();
    $change_field_list = new change_field_list();
}
