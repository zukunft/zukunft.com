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
function db_check($db_con, $debug)
{

    global $usr;

    $result = ''; // the message that should be shown to the user immediately

    // get the db version
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con, $debug);
    if ($db_version != PRG_VERSION) {
        if (prg_version_is_newer($db_version)) {
            switch ($db_version) {
                case NEXT_VERSION:
                    $result = db_upgrade_0_0_4($db_con, $debug);
                    break;
                default:
                    $result = db_upgrade_0_0_3($db_con, $debug);
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
function db_upgrade_0_0_3($db_con, $debug)
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
    // TODO create table user_value_time_series
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con, $debug);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.3 has failed';
    }

    return $result;
}

// upgrade the database from any version prior of 0.0.4
function db_upgrade_0_0_4($db_con, $debug)
{
    global $usr;

    $result = ''; // if empty everything has been fine; if not the message that should be shown to the user
    $db_version = cfg_get(CFG_VERSION_DB, $usr, $db_con, $debug);
    if ($db_version != PRG_VERSION) {
        $result = 'Database upgrade to 0.0.3 has failed';
    }

    return $result;
}

// create the database and fill it with the base configuration data
//function db_create($debug) {}