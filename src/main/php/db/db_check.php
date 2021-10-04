<?php

/*

  db_check.php - test if the database exists and start the creation or upgrade process
  ------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// read the version number from the database and compare it with the backend version
// if the database has a lower version than the backend program start the upgrade process
function db_check($db_con): string
{

    global $usr;

    $result = ''; // the message that should be shown to the user immediately
    $do_consistency_check = false;

    // get the db version and start the upgrade process if needed
    $db_version = cfg_get(CFG_VERSION_DB, $db_con);
    if ($db_version != PRG_VERSION) {
        $do_consistency_check = true;
        if (prg_version_is_newer($db_version)) {
            switch ($db_version) {
                case NEXT_VERSION:
                    $result = db_upgrade_0_0_4($db_con);
                    break;
                default:
                    $result = db_upgrade_0_0_3($db_con);
                    break;
            }
        } else {
            log_warning('The zukunft.com backend is older than the database used. This may cause damage on the database. Please upgrade the backend program', 'db_check');
        }
    } else {
        $last_consistency_check = cfg_get(CFG_LAST_CONSISTENCY_CHECK, $db_con);
        // run a database consistency check once every 24h if the database is the least busy
        if (strtotime($last_consistency_check) < strtotime("now") - 1) {
            $do_consistency_check = true;
        }
    }

    // run a database consistency check now and remember the time
    if ($do_consistency_check) {
        db_fill_code_links($db_con);
        cfg_set(CFG_LAST_CONSISTENCY_CHECK, strtotime("now"), $db_con);
    }

    return $result;

}

// upgrade the database from any version prior of 0.0.3
// the version 0.0.3 is the first version, which has a build in upgrade process
function db_upgrade_0_0_3(sql_db $db_con): string
{
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $process_name = 'db_upgrade_0_0_3'; // the info text that is written to the database execution log
    $db_con->add_column('user_profiles', 'right_level', 'smallint;');
    $db_con->add_column('user_words', 'share_type_id', 'smallint;');
    $db_con->add_column('user_words', 'protection_type_id', 'smallint;');
    $db_con->add_column('words', 'share_type_id', 'smallint;');
    $db_con->add_column('words', 'protection_type_id', 'smallint;');
    $db_con->add_column('user_word_links', 'share_type_id', 'smallint;');
    $db_con->add_column('user_word_links', 'protection_type_id', 'smallint;');
    $db_con->add_column('word_links', 'share_type_id', 'smallint;');
    $db_con->add_column('word_links', 'protection_type_id', 'smallint;');
    $db_con->change_column_name('user_values', 'user_value', 'word_value');
    $db_con->change_column_name('user_value', 'user_value', 'word_value;');
    $db_con->change_column_name('word_links', 'name', 'word_link_name');
    $db_con->change_column_name('user_word_links', 'name', 'word_link_name');
    $db_con->change_column_name('value_time_series', 'value_time_serie_id', 'value_time_series_id;');
    $db_con->change_column_name('user_blocked_ips', 'isactive', 'is_active;');
    $db_con->change_column_name('users', 'isactive', 'is_active;');
    $db_con->change_column_name('users', 'email_alternativ', 'email_alternative;');
    $db_con->change_column_name('view_component_types', 'view_component_type_name', 'type_name;');
    $db_con->change_column_name('formula_types', 'name', 'type_name;');
    $db_con->change_column_name('ref_types', 'ref_type_name', 'type_name;');
    $db_con->change_column_name('share_types', 'share_type_name', 'type_name;');
    $db_con->change_column_name('protection_types', 'protection_type_name', 'type_name;');
    $db_con->change_column_name('user_profiles', 'user_profile_name', 'type_name;');
    $db_con->change_column_name('user_profiles', 'commen', 'description;');
    $db_con->change_column_name('public.sys_log_status', 'comment', 'description;');
    $db_con->change_column_name('public.sys_log_status', 'sys_log_status_name', 'type_name;');
    $db_con->change_column_name('calc_and_cleanup_task_types', 'calc_and_cleanup_task_type_name', 'type_name;');
    $db_con->remove_prefix('sys_log_status', 'code_id', 'log_status_');
    $db_con->remove_prefix('calc_and_cleanup_task_types', 'code_id', 'job_');
    $db_con->remove_prefix('view_component_types', 'code_id', 'dsp_comp_type_');
    $db_con->remove_prefix('verbs', 'code_id', 'vrb_');
    $db_con->change_code_id('verbs', 'vrb_contains', 'is_part_of');
    $db_con->column_allow_null('word_types', 'word_symbol');
    $db_con->column_allow_null('values', 'exclude');
    $db_con->column_allow_null('change_tables', 'description');
    $db_con->column_allow_null('views', 'comment');
    $db_con->column_allow_null('view_component_types', 'description');
    $db_con->column_allow_null('user_values', 'protection_type_id');

    // Change code_id in verbs from contains to is_part_of

    // TODO create table user_value_time_series
    $db_version = cfg_get(CFG_VERSION_DB, $db_con);
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
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $db_version = cfg_get(CFG_VERSION_DB, $db_con);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.4 has failed';
    }

    return $result;
}

// create the database and fill it with the base configuration data
//function db_create() {}

/**
 * fill the database with all rows that have a code id and code linked
 */
function db_fill_code_links(sql_db $db_con)
{
    log_debug('Refresh code links for ...');

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
        log_debug($table_name);
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
                        $sql = "select * from " . $table_name . " where " . $id_col_name . " = " . $id . ";";
                        $db_row = $db_con->get1($sql);
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
                            // check, which values needs to be updates
                            for ($i = 1; $i < count($data); $i++) {
                                $col_name = $col_names[$i];
                                if (array_key_exists($col_name,$db_row)) {
                                    $db_value = $db_row[$col_name];
                                } else {
                                    log_err('Column check did not work for ' . $col_name);
                                }
                                if ($db_value != trim($data[$i]) and trim($data[$i]) != 'NULL') {
                                    $update_col_names[] = $col_name;
                                    $update_col_values[] = trim($data[$i]);
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
            log_debug('Refresh of code links done');
        }
    }

}