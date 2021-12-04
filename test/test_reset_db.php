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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

include_once '../src/main/php/zu_lib.php';

// open database and display header
$db_con = prg_start("test_reset_db");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {
    if ($usr->is_admin()) {

        // load the testing base functions
        include_once '../src/test/php/utils/test_base.php';

        // use the system user for the database updates
        $usr = new user;
        $usr->id = SYSTEM_USER_ID;
        $usr->load($db_con);

        // run reset the main database tables
        run_db_truncate();
        run_db_seq_reset();

        // recreate the code link database rows
        db_fill_code_links($db_con);
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
        import_base_config();

        /*
         * For testing the system setup
         */

        // drop the database

        // create the database from the sql structure file

        // reload the system database rows (all db rows, that have a code id)
    }
}

//prg_end($db_con);

/**
 * truncate all tables (use only for system testing)
 */
function run_db_truncate()
{
    // the tables in order to avoid the usage of CASCADE
    $table_names = array(
        DB_TYPE_VALUE_PHRASE_LINK,
        DB_TYPE_VALUE,
        DB_TYPE_FORMULA_VALUE,
        DB_TYPE_FORMULA_ELEMENT,
        DB_TYPE_FORMULA_ELEMENT_TYPE,
        DB_TYPE_FORMULA_LINK,
        DB_TYPE_FORMULA,
        DB_TYPE_FORMULA_TYPE,
        DB_TYPE_VIEW_COMPONENT_LINK,
        DB_TYPE_VIEW_COMPONENT_LINK_TYPE,
        DB_TYPE_VIEW_COMPONENT,
        DB_TYPE_VIEW_COMPONENT_TYPE,
        DB_TYPE_VIEW,
        DB_TYPE_VIEW_TYPE,
        DB_TYPE_PHRASE_GROUP,
        DB_TYPE_VERB,
        DB_TYPE_TRIPLE,
        DB_TYPE_WORD,
        DB_TYPE_WORD_TYPE,
        DB_TYPE_SOURCE,
        DB_TYPE_SOURCE_TYPE,
        DB_TYPE_REF,
        DB_TYPE_REF_TYPE,
        DB_TYPE_CHANGE_LINK,
        DB_TYPE_CHANGE,
        DB_TYPE_CHANGE_ACTION,
        DB_TYPE_CHANGE_FIELD,
        DB_TYPE_CHANGE_TABLE,
        DB_TYPE_CONFIG,
        DB_TYPE_TASK,
        DB_TYPE_TASK_TYPE,
        DB_TYPE_SYS_SCRIPT,
        DB_TYPE_TASK,
        DB_TYPE_SYS_LOG,
        DB_TYPE_SYS_LOG_STATUS,
        DB_TYPE_SYS_LOG_FUNCTION,
        DB_TYPE_SHARE,
        DB_TYPE_PROTECTION,
        DB_TYPE_USER,
        DB_TYPE_USER_PROFILE
    );
    echo "\n";
    ui_echo('truncate ');
    echo "\n";

    foreach ($table_names as $table_name) {
        run_table_truncate($table_name);
    }
}

function run_table_truncate(string $table_name)
{
    global $db_con;

    $sql = 'TRUNCATE ' . $db_con->get_table_name_esc($table_name) . ' CASCADE;';
    try {
        $db_con->exe($sql);
    } catch (Exception $e) {
        log_err('Cannot truncate table ' . $table_name . ' with "' . $sql . '" because: ' . $e->getMessage());
    }
}

function run_db_seq_reset()
{
    // the sequence names of the reseted tables
    $seq_names = array(
        'value_phrase_links_value_phrase_link_id_seq',
        'values_value_id_seq',
        'formula_values_formula_value_id_seq',
        'formula_elements_formula_element_id_seq',
        'formula_element_types_formula_element_type_id_seq',
        'formula_links_formula_link_id_seq',
        'formulas_formula_id_seq',
        'formula_types_formula_type_id_seq',
        'view_component_links_view_component_link_id_seq',
        'view_component_link_types_view_component_link_type_id_seq',
        'view_components_view_component_id_seq',
        'view_component_types_view_component_type_id_seq',
        'views_view_id_seq',
        'view_types_view_type_id_seq',
        'phrase_groups_phrase_group_id_seq',
        'verbs_verb_id_seq',
        'word_links_word_link_id_seq',
        'words_word_id_seq',
        'word_types_word_type_id_seq',
        'sources_source_id_seq',
        'source_types_source_type_id_seq',
        'refs_ref_id_seq',
        'ref_types_ref_type_id_seq',
        'change_links_change_link_id_seq',
        'changes_change_id_seq',
        'change_actions_change_action_id_seq',
        'change_fields_change_field_id_seq',
        'change_tables_change_table_id_seq',
        'config_config_id_seq',
        'calc_and_cleanup_task_types_calc_and_cleanup_task_type_id_seq',
        'calc_and_cleanup_tasks_calc_and_cleanup_task_id_seq',
        'sys_scripts_sys_script_id_seq',
        'sys_log_sys_log_id_seq',
        'sys_log_status_sys_log_status_id_seq',
        'sys_log_functions_sys_log_function_id_seq',
        'share_types_share_type_id_seq',
        'protection_types_protection_type_id_seq',
        'users_user_id_seq',
        'user_profiles_profile_id_seq'
    );
    ui_echo('seq reset ');
    echo "\n";
    foreach ($seq_names as $seq_name) {
        run_seq_reset($seq_name);
    }

}

function run_seq_reset(string $seq_name)
{
    global $db_con;

    $sql = 'ALTER SEQUENCE ' . $seq_name . ' RESTART 1;';
    try {
        $db_con->exe($sql);
    } catch (Exception $e) {
        log_err('Cannot do sequence reset with "' . $sql . '" because: ' . $e->getMessage());
    }
}

