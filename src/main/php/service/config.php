<?php

/*

  /lib/config.php - functions to handle the database based system configuration
  ---------------

  the values in the config table can only be changed by the system admin
  
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com

*/

/**
 * get a config value from the database table
 * including $db_con because this is call also from the start, where the global $db_con is not yet set
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @param user $usr the user which has requested the config item for the first time
 * @param sql_db $db_con the open database connection that should be used
 * @return string the configuration value that is valid at the moment
 */
function cfg_get(string $code_id, sql_db $db_con): ?string
{

    // init
    log_debug('cfg_get for "' . $code_id . '"');
    $db_value = '';

    // check the parameters to capsule this function
    if ($code_id == '') {
        log_err("The code id must be set", "config->cfg_get");
    }

    // the config table is existing since 0.0.2, so it does not need to be checked, if the config table itself exists
    $db_con->set_type(DB_TYPE_CONFIG);
    $db_con->set_fields(array(sql_db::FLD_CODE_ID, sql_db::FLD_VALUE));
    $db_con->where(array(sql_db::FLD_CODE_ID), array($code_id));
    $sql = $db_con->select_by_id(false);
    $db_row = $db_con->get1_old($sql);
    if ($db_row == null) {
        // automatically create the config entry
        if (cfg_create($code_id, $db_con)) {
            $db_value = cfg_default_value($code_id);
        }
    } else {
        $db_code_id = $db_row[sql_db::FLD_CODE_ID];
        $db_value = $db_row[sql_db::FLD_VALUE];
        // if no value exists create it with the default value (a configuration value should never be empty)
        if ($db_code_id == '') {
            if (cfg_create($code_id, $db_con)) {
                $db_value = cfg_default_value($code_id);
            }
        }
    }
    return $db_value;
}

/**
 * save a configuration value in the program configuration table of the database
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @param string $value the value that should be saved in the configuration table
 * @param sql_db $db_con the open database connection that should be used
 */
function cfg_set(string $code_id, string $value, sql_db $db_con, string $description = ''): bool
{
    // init
    log_debug('cfg_get for "' . $code_id . '"');
    $result = false;

    // check the parameters to capsule this function
    if ($code_id == '') {
        log_err("The code id must be set", "config->cfg_get");
    }

    $db_con->set_type(DB_TYPE_CONFIG);
    $db_con->set_fields(array(sql_db::FLD_CODE_ID, sql_db::FLD_VALUE, sql_db::FLD_DESCRIPTION));
    $db_con->where(array(sql_db::FLD_CODE_ID), array($code_id));
    $sql = $db_con->select_by_id(false);
    $db_row = $db_con->get1_old($sql);
    if ($db_row == null) {
        // automatically add the config entry
        $result = cfg_add($code_id, $value, $description, $db_con);
    } else {
        if ($value != $db_row[sql_db::FLD_VALUE] OR $description != $db_row[sql_db::FLD_DESCRIPTION]) {
            $result = cfg_update($code_id, $value, $description, $db_con);
        }
    }
    return $result;
}

/**
 * create configuration entry in the database for a new config item
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @param sql_db $db_con the open database connection that should be used
 * @return bool true if adding the config item has been successful
 */
function cfg_create(string $code_id, sql_db $db_con): bool
{
    $result = false;
    $db_value = cfg_default_value($code_id);
    $db_description = cfg_default_description($code_id);
    $db_id = $db_con->insert(
        array(
            sql_db::FLD_CODE_ID,
            sql_db::FLD_VALUE,
            sql_db::FLD_DESCRIPTION),
        array(
            $code_id,
            $db_value,
            $db_description));
    if ($db_id > 0) {
        $result = true;
    }
    return $result;
}

/**
 * add a configuration value to the database
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @param string $value the value that should be saved in the configuration table
 * @param string $description used for the tooltip of the configuration value
 * @param sql_db $db_con the open database connection that should be used
 * @return bool if adding to the database was successful
 */
function cfg_add(string $code_id, string $value, string $description, sql_db $db_con): bool {
    $result = false;
    $db_id = $db_con->insert(
        array(
            sql_db::FLD_CODE_ID,
            sql_db::FLD_VALUE,
            sql_db::FLD_DESCRIPTION),
        array(
            $code_id,
            $value,
            $description));
    if ($db_id > 0) {
        $result = true;
    }
    return $result;
}

/**
 * update a configuration value to the database
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @param string $value the value that should be saved in the configuration table
 * @param string $description used for the tooltip of the configuration value
 * @param sql_db $db_con the open database connection that should be used
 * @return bool if updating in the database was successful
 */
function cfg_update(string $code_id, string $value, string $description, sql_db $db_con): bool {
    $result = false;
    $db_id = $db_con->update(
        $code_id,
        array(
            sql_db::FLD_VALUE,
            sql_db::FLD_DESCRIPTION),
        array(
            $value,
            $description),
        sql_db::FLD_CODE_ID);
    if ($db_id > 0) {
        $result = true;
    }
    return $result;
}

/**
 * get a default config value based on code CONST values
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @return string the default configuration value
 */
function cfg_default_value(string $code_id): string
{

    // init
    $result = '';
    log_debug('cfg_default_value for "' . $code_id . '"');

    // check the parameters to capsule this function
    if ($code_id == '') {
        log_err("The code id must be set", "config->cfg_default_value");
    }

    switch ($code_id) {
        case CFG_VERSION_DB:
            $result = FIRST_VERSION;
            break;
        case CFG_AVG_CALC_TIME:
            $result = formula::AVG_CALC_TIME;
            break;
    }

    return $result;
}

/**
 * get a default description for a configuration value
 * @param string $code_id the identification of the config item that is used in the code that should never be changed
 * @return string the default configuration description
 */
function cfg_default_description(string $code_id): string
{

    // init
    $result = '';
    log_debug('cfg_default_description for "' . $code_id . '"');

    // check the parameters to capsule this function
    if ($code_id == '') {
        log_err("The code id must be set", "config->cfg_default_description");
    }

    switch ($code_id) {
        case CFG_VERSION_DB:
            $result = 'the program version which has last completed the update';
            break;
    }

    return $result;
}
