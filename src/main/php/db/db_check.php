<?php

/*

  db_check.php - test if the DataBase exists and start the creation or upgrade process
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
function db_check($db_con)
{

    global $usr;

    $result = ''; // the message that should be shown to the user immediately

    // get the db version
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con);
    if ($db_version != PRG_VERSION) {
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
    }

    return $result;

}

// upgrade the database from any version prior of 0.0.3
// the version 0.0.3 is the first version, which has an build in upgrade process
function db_upgrade_0_0_3($db_con)
{
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $sql = 'ALTER TABLE user_values RENAME user_value TO word_value;';
    $sql .= 'ALTER TABLE word_links RENAME name TO word_link_name';
    $sql .= 'ALTER TABLE user_word_links RENAME name TO word_link_name';
    $sql .= 'ALTER TABLE value_time_series RENAME value_time_serie_id TO value_time_series_id;';
    $sql .= 'ALTER TABLE user_blocked_ips RENAME isactive TO is_active;';
    $sql .= 'ALTER TABLE users RENAME isactive TO is_active;';
    $sql .= 'ALTER TABLE users RENAME email_alternativ TO email_alternative;';
    $sql .= 'ALTER TABLE view_component_types RENAME view_component_type_name TO type_name;';
    $sql .= 'ALTER TABLE formula_types RENAME name TO type_name;';
    $sql .= 'ALTER TABLE ref_types RENAME ref_type_name TO type_name;';
    $sql .= 'ALTER TABLE share_types RENAME share_type_name TO type_name;';
    $sql .= 'ALTER TABLE protection_types RENAME protection_type_name TO type_name;';
    $sql .= 'ALTER TABLE user_profiles RENAME user_profile_name TO type_name;';
    $sql .= 'ALTER TABLE user_profiles RENAME commen TO description;';
    $sql .= 'ALTER TABLE user_words ADD COLUMN share_type_id smallint;';
    $sql .= 'ALTER TABLE user_words ADD COLUMN protection_type_id smallint;';
    $sql .= 'ALTER TABLE words ADD COLUMN share_type_id smallint;';
    $sql .= 'ALTER TABLE words ADD COLUMN protection_type_id smallint;';
    $sql .= 'ALTER TABLE user_word_links ADD COLUMN share_type_id smallint;';
    $sql .= 'ALTER TABLE user_word_links ADD COLUMN protection_type_id smallint;';
    $sql .= 'ALTER TABLE word_links ADD COLUMN share_type_id smallint;';
    $sql .= 'ALTER TABLE word_links ADD COLUMN protection_type_id smallint;';
    $sql .= "UPDATE sys_log_status SET code_id = 'new' WHERE code_id = 'log_status_new'; UPDATE sys_log_status SET code_id = 'assigned' WHERE code_id = 'log_status_assigned'; UPDATE sys_log_status SET code_id = 'resolved' WHERE code_id = 'log_status_resolved'; UPDATE sys_log_status SET code_id = 'closed' WHERE code_id = 'log_status_closed';";
    $sql .= 'ALTER TABLE public.sys_log_status RENAME comment TO description;';
    $sql .= 'ALTER TABLE public.sys_log_status RENAME sys_log_status_name TO type_name;';
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'value_update'::character varying WHERE calc_and_cleanup_task_type_id = '1';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'value_add'::character varying WHERE calc_and_cleanup_task_type_id = '2';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'value_del'::character varying WHERE calc_and_cleanup_task_type_id = '3';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'formula_update'::character varying WHERE calc_and_cleanup_task_type_id = '4';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'formula_add'::character varying WHERE calc_and_cleanup_task_type_id = '5';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'formula_del'::character varying WHERE calc_and_cleanup_task_type_id = '6';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'formula_link'::character varying WHERE calc_and_cleanup_task_type_id = '7';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'formula_unlink'::character varying WHERE calc_and_cleanup_task_type_id = '8';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'word_link'::character varying WHERE calc_and_cleanup_task_type_id = '9';";
    $sql .= "UPDATE calc_and_cleanup_task_types SET code_id = 'word_unlink'::character varying WHERE calc_and_cleanup_task_type_id = '10';";    $db_con->exe($sql, sys_log_level::INFO, 'db_upgrade_0_0_3');
    $sql .= 'ALTER TABLE calc_and_cleanup_task_types RENAME calc_and_cleanup_task_type_name TO type_name;';
    // TODO create table user_value_time_series
    // TODO check and change view component type code ids
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.3 has failed';
    }

    return $result;
}

// upgrade the database from any version prior of 0.0.4
function db_upgrade_0_0_4($db_con)
{
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con);
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
function db_fill_code_links() {
    // load the csv
    // check if the column names match the table names
    // select the rows where the code id is missing
    // add the missing rows
}