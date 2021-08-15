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
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE word_links RENAME name TO word_link_name';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_word_links RENAME name TO word_link_name';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE value_time_series RENAME value_time_serie_id TO value_time_series_id;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_blocked_ips RENAME isactive TO is_active;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE users RENAME isactive TO is_active;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE users RENAME email_alternativ TO email_alternative;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE view_component_types RENAME view_component_type_name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE formula_types RENAME name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE ref_types RENAME ref_type_name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE share_types RENAME share_type_name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE protection_types RENAME protection_type_name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_profiles RENAME user_profile_name TO type_name;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_profiles RENAME commen TO description;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_words ADD COLUMN share_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_words ADD COLUMN protection_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE words ADD COLUMN share_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE words ADD COLUMN protection_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_word_links ADD COLUMN share_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE user_word_links ADD COLUMN protection_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE word_links ADD COLUMN share_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
    $sql = 'ALTER TABLE word_links ADD COLUMN protection_type_id smallint;';
    $db_con->exe($sql, DBL_SYSLOG_INFO, 'db_upgrade_0_0_3');
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