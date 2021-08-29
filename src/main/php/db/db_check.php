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
function db_upgrade_0_0_3($db_con): string
{
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $process_name = 'db_upgrade_0_0_3'; // the info text that is written to the database execution log
    $db_con->add_column('user_words', 'share_type_id', 'smallint;');
    $db_con->add_column('user_words', 'protection_type_id', 'smallint;');
    $db_con->add_column('words', 'share_type_id', 'smallint;');
    $db_con->add_column('words', 'protection_type_id', 'smallint;');
    $db_con->add_column('user_word_links', 'share_type_id', 'smallint;');
    $db_con->add_column('user_word_links', 'protection_type_id', 'smallint;');
    $db_con->add_column('word_links', 'share_type_id', 'smallint;');
    $db_con->add_column('word_links', 'protection_type_id', 'smallint;');
    $db_con->change_column_name('user_values','user_value','word_value');
    $db_con->change_column_name('user_value','user_value','word_value;');
    $db_con->change_column_name('word_links','name','word_link_name');
    $db_con->change_column_name('user_word_links','name','word_link_name');
    $db_con->change_column_name('value_time_series','value_time_serie_id','value_time_series_id;');
    $db_con->change_column_name('user_blocked_ips','isactive','is_active;');
    $db_con->change_column_name('users','isactive','is_active;');
    $db_con->change_column_name('users','email_alternativ','email_alternative;');
    $db_con->change_column_name('view_component_types','view_component_type_name','type_name;');
    $db_con->change_column_name('formula_types','name','type_name;');
    $db_con->change_column_name('ref_types','ref_type_name','type_name;');
    $db_con->change_column_name('share_types','share_type_name','type_name;');
    $db_con->change_column_name('protection_types','protection_type_name','type_name;');
    $db_con->change_column_name('user_profiles','user_profile_name','type_name;');
    $db_con->change_column_name('user_profiles','commen','description;');
    $db_con->change_column_name('public.sys_log_status','comment','description;');
    $db_con->change_column_name('public.sys_log_status','sys_log_status_name','type_name;');
    $db_con->change_column_name('calc_and_cleanup_task_types','calc_and_cleanup_task_type_name','type_name;');
    $db_con->remove_prefix('sys_log_status', 'code_id', 'log_status_');
    $db_con->remove_prefix('calc_and_cleanup_task_types', 'code_id', 'job_');
    $db_con->remove_prefix('view_component_types', 'code_id', 'dsp_comp_type_');
    // TODO create table user_value_time_series
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.3 has failed';
    }

    return $result;
}

// upgrade the database from any version prior of 0.0.4
function db_upgrade_0_0_4($db_con): string
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
function db_fill_code_links()
{
    // load the csv
    // check if the column names match the table names
    // select the rows where the code id is missing
    // add the missing rows
}