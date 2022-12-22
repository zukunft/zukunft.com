<?php

/*

  db_check.php - test if the database exists and start the creation or upgrade process
  ------------
  

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

/**
 * read the version number from the database and compare it with the backend version
 * if the database has a lower version than the backend program start the upgrade process
 */
function db_check($db_con): string
{

    $result = ''; // the message that should be shown to the user immediately
    $do_consistency_check = false;

    // get the db version and start the upgrade process if needed
    $db_version = cfg_get(config::VERSION_DB, $db_con);
    if ($db_version != PRG_VERSION) {
        $do_consistency_check = true;
        if (prg_version_is_newer($db_version)) {
            log_warning('The zukunft.com backend is older than the database used. This may cause damage on the database. Please upgrade the backend program', 'db_check');
        } else {
            $result = match ($db_version) {
                NEXT_VERSION => db_upgrade_0_0_4($db_con),
                default => db_upgrade_0_0_3($db_con),
            };
        }
    } else {
        $last_consistency_check = cfg_get(config::LAST_CONSISTENCY_CHECK, $db_con);
        // run a database consistency check once every 24h if the database is the least busy
        if (strtotime($last_consistency_check) < strtotime("now") - 1) {
            $do_consistency_check = true;
        }
    }

    // run a database consistency check now and remember the time
    if ($do_consistency_check) {
        db_fill_code_links($db_con);
        db_check_missing_owner($db_con);
        cfg_set(config::LAST_CONSISTENCY_CHECK, gmdate(DATE_ATOM), $db_con);
    }

    return $result;

}

/**
 * @return bool true if all user sandbox objects have an owner
 */
function db_check_missing_owner(sql_db $db_con): bool
{
    $result = true;

    foreach (user_sandbox::DB_TYPES as $db_type) {
        $db_con->set_type($db_type);
        $db_lst = $db_con->missing_owner();
        if ($db_lst != null) {
            $result = $db_con->set_default_owner();
        }
    }

    return $result;
}

// upgrade the database from any version prior of 0.0.3
// the version 0.0.3 is the first version, which has a build in upgrade process
function db_upgrade_0_0_3(sql_db $db_con): string
{
    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $process_name = 'db_upgrade_0_0_3'; // the info text that is written to the database execution log
    // TODO check if change has been successful
    // rename word_link to triple
    $result .= $db_con->change_table_name('phrase_group_word_link', sql_db::TBL_PHRASE_GROUP_WORD_LINK);
    $result .= $db_con->change_table_name(sql_db::TBL_USER_PREFIX . 'phrase_group_word_link', sql_db::TBL_USER_PREFIX . sql_db::TBL_PHRASE_GROUP_WORD_LINK);
    $result .= $db_con->change_table_name('word_link', sql_db::TBL_TRIPLE);
    $result .= $db_con->change_column_name(sql_db::TBL_TRIPLE, 'word_link_id', 'triple_id');
    $result .= $db_con->change_column_name(sql_db::TBL_TRIPLE, 'word_link_condition_id', 'triple_condition_id');
    $result .= $db_con->change_column_name(sql_db::TBL_TRIPLE, 'word_link_condition_type_id', 'triple_condition_type_id');
    $result .= $db_con->change_table_name(sql_db::TBL_USER_PREFIX . 'word_link', sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE);
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'word_link_id', sql_db::TBL_USER_PREFIX . 'triple_id');
    $result .= $db_con->change_table_name('view_word_link', sql_db::TBL_VIEW_TERM_LINK);
    //
    $result .= $db_con->change_table_name('languages_forms', sql_db::TBL_LANGUAGE_FORM);
    $result .= $db_con->add_column(sql_db::TBL_USER_PROFILE, 'right_level', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD, 'values', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_WORD, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'values', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_TRIPLE, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_TRIPLE, 'word_type_id', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA, 'usage', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA, 'usage', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA_LINK, 'order_nbr', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA_LINK, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_FORMULA_LINK, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_FORMULA_LINK, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW_COMPONENT, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW_COMPONENT, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW_COMPONENT_LINK, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW_COMPONENT_LINK, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT_LINK, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT_LINK, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VALUE_TIME_SERIES, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VALUE_TIME_SERIES, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE_TIME_SERIES, 'share_type_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE_TIME_SERIES, 'protect_id', 'smallint');
    $result .= $db_con->add_column(sql_db::TBL_VIEW_COMPONENT_POS_TYPE, 'code_id', 'varchar(50)');
    $result .= $db_con->add_column(sql_db::TBL_SOURCE_TYPE, 'description', 'text');
    $result .= $db_con->change_column_name(sql_db::TBL_LANGUAGE_FORM, 'lanuages_id', 'language_id');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE, 'user_value', 'word_value');
    $result .= $db_con->change_column_name(sql_db::TBL_VALUE_TIME_SERIES, 'value_time_serie_id', 'value_time_series_id');
    $result .= $db_con->change_column_name(sql_db::TBL_IP, 'isactive', 'is_active');
    $result .= $db_con->change_column_name(sql_db::TBL_USER, 'isactive', 'is_active');
    $result .= $db_con->change_column_name(sql_db::TBL_USER, 'email_alternativ', 'email_alternative');
    $result .= $db_con->change_column_name(sql_db::TBL_FORMULA_ELEMENT_TYPE, 'formula_element_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_VIEW, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_VIEW_COMPONENT, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_VIEW_COMPONENT, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_VIEW_COMPONENT_TYPE, 'view_component_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_FORMULA_TYPE, 'name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_REF_TYPE, 'ref_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_REF_TYPE, 'source_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_SOURCE, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE, 'comment', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_SHARE, 'share_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_PROTECTION, 'protection_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PROFILE, 'user_profile_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PROFILE, 'commen', user_sandbox_named::FLD_DESCRIPTION);
    $result .= $db_con->change_column_name(sql_db::TBL_SYS_LOG_STATUS, 'comment', 'description');
    $result .= $db_con->change_column_name(sql_db::TBL_SYS_LOG_STATUS, 'sys_log_status_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_TASK_TYPE, 'calc_and_cleanup_task_type_name', 'type_name');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PROFILE, 'comment', 'description');
    $result .= $db_con->change_column_name(sql_db::TBL_FORMULA, 'protection_type_id', 'protect_id');
    $result .= $db_con->change_column_name(sql_db::TBL_VALUE, 'protection_type_id', 'protect_id');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE, 'protection_type_id', 'protect_id');
    $result .= $db_con->change_column_name(sql_db::TBL_VALUE_TIME_SERIES, 'protection_type_id', 'protect_id');
    $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE_TIME_SERIES, 'protection_type_id', 'protect_id');
    $result .= $db_con->change_column_name(sql_db::TBL_FORMULA_VALUE, 'source_time_word_id', 'source_time_id');
    if (!$db_con->has_column($db_con->get_table_name(sql_db::TBL_TRIPLE), 'name_generated')) {
        $result .= $db_con->change_column_name(sql_db::TBL_TRIPLE, 'name', 'triple_name');
        $result .= $db_con->change_column_name(sql_db::TBL_TRIPLE, 'description', 'name_given');
        $result .= $db_con->add_column(sql_db::TBL_TRIPLE, 'name_generated', 'text');
        $result .= $db_con->add_column(sql_db::TBL_TRIPLE, 'description', 'text');
    }
    $result .= $db_con->add_column(sql_db::TBL_TRIPLE, 'values', 'bigint');
    if (!$db_con->has_column($db_con->get_table_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE), 'name_generated')) {
        $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'name', 'triple_name');
        $result .= $db_con->change_column_name(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'description', 'name_given');
        $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'name_generated', 'text');
        $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'description', 'text');
    }
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE, 'values', 'bigint');
    $result .= $db_con->add_column(sql_db::TBL_USER_PREFIX . sql_db::TBL_WORD, 'values', 'bigint');
    $result .= $db_con->remove_prefix(sql_db::TBL_USER_PROFILE, 'code_id', 'usr_role_');
    $result .= $db_con->remove_prefix(sql_db::TBL_SYS_LOG_STATUS, 'code_id', 'log_status_');
    $result .= $db_con->remove_prefix(sql_db::TBL_TASK_TYPE, 'code_id', 'job_');
    $result .= $db_con->remove_prefix(sql_db::TBL_VIEW, 'code_id', 'dsp_');
    $result .= $db_con->remove_prefix(sql_db::TBL_VIEW_COMPONENT_TYPE, 'code_id', 'dsp_comp_type_');
    $result .= $db_con->remove_prefix(sql_db::TBL_VERB, 'code_id', 'vrb_');
    $result .= $db_con->change_code_id(sql_db::TBL_VERB, 'vrb_contains', 'is_part_of');
    $result .= $db_con->column_allow_null(sql_db::TBL_WORD, 'plural');
    $result .= $db_con->column_allow_null(sql_db::TBL_WORD_TYPE, 'word_symbol');
    $result .= $db_con->column_allow_null(sql_db::TBL_CHANGE_TABLE, 'description');
    $result .= $db_con->column_allow_null(sql_db::TBL_CHANGE_FIELD, 'code_id');
    $result .= $db_con->column_allow_null(sql_db::TBL_VIEW, 'comment');
    $result .= $db_con->column_allow_null(sql_db::TBL_VIEW_COMPONENT_TYPE, 'description');
    $result .= $db_con->column_allow_null(sql_db::TBL_VALUE, user_sandbox::FLD_EXCLUDED);
    $result .= $db_con->column_allow_null(sql_db::TBL_VALUE, 'protect_id');
    $result .= $db_con->column_allow_null(sql_db::TBL_FORMULA_LINK, 'link_type_id');
    $result .= $db_con->column_allow_null(sql_db::TBL_USER_PREFIX . sql_db::TBL_VALUE, 'protect_id');
    $result .= $db_con->column_allow_null(sql_db::TBL_VALUE_TIME_SERIES, 'protect_id');
    $result .= $db_con->column_allow_null(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE, 'source_name');
    $result .= $db_con->column_allow_null(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE, 'url');
    $result .= $db_con->column_allow_null(sql_db::TBL_SYS_LOG_FUNCTION, 'sys_log_function_name');
    $result .= $db_con->column_allow_null(sql_db::TBL_TASK, 'start_time');
    $result .= $db_con->column_allow_null(sql_db::TBL_TASK, 'end_time');
    $result .= $db_con->column_force_not_null(sql_db::TBL_USER_PREFIX . sql_db::TBL_SOURCE, 'user_id');
    // TODO set default profile_id in users to 1
    if ($db_con->db_type == sql_db::MYSQL) {
        $sql = 'UPDATE' . ' `users` SET `user_profile_id` = 1 WHERE `user_profile_id`= NULL';
        $result .= $db_con->exe_try('Setting missing user profiles', $sql);
        $sql = 'UPDATE' . ' `users` SET `dt` = CURRENT_TIMESTAMP WHERE `users`.`dt` = 0';
        $result .= $db_con->exe_try('Filling missing timestamps for users', $sql);
        $sql = 'UPDATE' . ' `users` SET `last_logoff` = CURRENT_TIMESTAMP WHERE `users`.`last_logoff` = 0';
        $result .= $db_con->exe_try('Filling missing logoff timestamps for users', $sql);
        $sql = 'UPDATE' . ' `users` SET `activation_key_timeout` = CURRENT_TIMESTAMP WHERE `users`.`activation_key_timeout` = 0';
        $result .= $db_con->exe_try('Filling missing activation timestamps for users', $sql);

        $sql = file_get_contents(PATH_BASE_CONFIG_FILES . 'db/upgrade/v0.0.3/upgrade_mysql.sql');
        $result .= $db_con->exe_try('Finally add the new views', $sql);
    }
    if ($db_con->db_type == sql_db::POSTGRES) {
        $sql = file_get_contents(PATH_BASE_CONFIG_FILES . 'db/upgrade/v0.0.3/upgrade_postgres.sql');
        //src/main/resources/db/upgrade/v0.0.3/upgrade_postgres.sql
        //$result .= $db_con->exe_try('Finally add the new views', $sql);
    }
    $result .= $db_con->add_foreign_key('users_fk_2', sql_db::TBL_USER, 'user_profile_id', sql_db::TBL_USER_PROFILE, 'profile_id');
    // TODO change prime key for postgres user_sources, user_values, user_view, user_view_components and user_view_component_links

    if ($db_con->db_type == sql_db::MYSQL) {

        global $user_profiles;
        $user_profiles = new user_profile_list();
        $user_profiles->load($db_con);

        // add missing system users if needed
        $sys_usr = new user();
        if (!$sys_usr->has_any_user_this_profile(user_profile::SYSTEM, $db_con)) {
            $sys_usr->name = user::SYSTEM;
            $sys_usr->load($db_con);
            $sys_usr->set_profile(user_profile::SYSTEM);
            $sys_usr->save($db_con);
        }

        // add missing system test users if needed
        $test_usr = new user();
        if (!$test_usr->has_any_user_this_profile(user_profile::TEST, $db_con)) {
            $test_usr->name = user::NAME_SYSTEM_TEST;
            $test_usr->load($db_con);
            $test_usr->set_profile(user_profile::TEST);
            $test_usr->save($db_con);
            $test_usr2 = new user();
            $test_usr2->name = user::NAME_SYSTEM_TEST_PARTNER;
            $test_usr2->load($db_con);
            $test_usr2->set_profile(user_profile::TEST);
            $test_usr2->save($db_con);
        }
    }

    // prepare the high level upgrade
    $sys_usr = new user();
    $sys_usr->name = user::SYSTEM;
    $sys_usr->load($db_con);

    // refresh the formula ref_text, because the coding has changed (use "{w" instead of "{t")
    $frm_lst = new formula_list($sys_usr);
    $frm_lst->db_ref_refresh($db_con);

    // Change code_id in verbs from contains to is_part_of

    // update the database version number in the config
    cfg_set(config::VERSION_DB, PRG_VERSION, $db_con);




    // TODO create table user_value_time_series
    // check if the config save has been successful
    $db_version = cfg_get(config::VERSION_DB, $db_con);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.3 has failed';
    }

    return $result;
}


/**
 * upgrade the database from any version prior of 0.0.4
 */
function db_upgrade_0_0_4($db_con): string
{
    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $db_version = cfg_get(config::VERSION_DB, $db_con);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.4 has failed';
    }

    return $result;
}

function db_fill_code_link_sql(string $table_name, string $id_col_name, int $id): sql_par
{
    $qp = new sql_par('db_check');
    $qp->name .= 'fill_' . $id_col_name;
    $qp->sql = "PREPARE " . $qp->name . " (int) AS select * from " . $table_name . " where " . $id_col_name . " = $1;";
    $qp->par = array($id);
    return $qp;
}

// create the database and fill it with the base configuration data
//function db_create() {}

/**
 * fill the database with all rows that have a code id and code linked
 */
function db_fill_code_links(sql_db $db_con)
{
    global $debug;

    // first of all set the database version if not jet done
    $cfg = new config();
    $cfg->set(config::VERSION_DB, PRG_VERSION, $db_con);

    // get the list of CSV and loop
    $csv_file_list = unserialize(BASE_CODE_LINK_FILES);
    foreach ($csv_file_list as $csv_file_name) {
        // load the csv
        $csv_path = PATH_BASE_CODE_LINK_FILES . $csv_file_name . BASE_CODE_LINK_FILE_TYPE;

        $row = 1;
        $table_name = $csv_file_name;
        // TODO change table names to singular form
        if ($table_name == 'sys_log_status') {
            $db_type = $table_name;
        } else {
            $db_type = substr($table_name, 0, -1);
        }
        log_debug('load "' . $table_name . '"', $debug - 6);
        if (($handle = fopen($csv_path, "r")) !== FALSE) {
            $continue = true;
            $id_col_name = '';
            $col_names = array();
            while (($data = fgetcsv($handle, 0, ",", "'")) !== FALSE) {
                if ($continue) {
                    if ($row == 1) {
                        // check if the csv column names match the table names
                        if (!$db_con->check_column_names($table_name, array_trim($data))) {
                            $continue = false;
                        } else {
                            $col_names = array_trim($data);
                        }
                        // check if the first column name is the id col
                        $id_col_name = $data[0];
                        if (!str_ends_with($id_col_name, 'id')) {
                            $continue = false;
                        }
                    } else {
                        // init row update
                        $update_col_names = array();
                        $update_col_values = array();
                        // get the row id which is expected to be always in the first column
                        $id = $data[0];
                        // check if the row id exists
                        $qp = db_fill_code_link_sql($table_name, $id_col_name, $id);
                        $db_row = $db_con->get1($qp);
                        // check if the db row needs to be added
                        if ($db_row == null) {
                            // add the row
                            for ($i = 0; $i < count($data); $i++) {
                                $update_col_names[] = $col_names[$i];
                                $update_col_values[] = trim($data[$i]);
                            }
                            $db_con->set_type($db_type);
                            $db_con->insert($update_col_names, $update_col_values);
                        } else {
                            // check, which values need to be updates
                            for ($i = 1; $i < count($data); $i++) {
                                $col_name = $col_names[$i];
                                if (array_key_exists($col_name, $db_row)) {
                                    $db_value = $db_row[$col_name];
                                    if ($db_value != trim($data[$i]) and trim($data[$i]) != 'NULL') {
                                        $update_col_names[] = $col_name;
                                        $update_col_values[] = trim($data[$i]);
                                    }
                                } else {
                                    log_err('Column check did not work for ' . $col_name);
                                }
                            }
                            // update the values is needed
                            if (count($update_col_names) > 0) {
                                $db_con->set_type($db_type);
                                $db_con->update($id, $update_col_names, $update_col_values);
                            }
                        }
                    }
                }
                $row++;
            }
            fclose($handle);
        }

    }

    // set the seq number if needed
    $db_con->seq_reset(sql_db::TBL_CHANGE_TABLE);
    $db_con->seq_reset(sql_db::TBL_CHANGE_ACTION);
}