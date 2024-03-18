<?php

/*

    service/config.php - functions to handle the database based system configuration
    ------------------

    TODO use single words with code_id for the system configuration
    TODO check on system start that the system configuration is complete
    TODO move all possible values to the phrase based configuration

    the values in the config table can only be changed by the system admin
    expose the config class functions as simple functions for simple coding

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_type;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_USER_PATH . 'user.php';

class config extends db_object_seq_id
{

    // reserved word and triple names used for the system configuration
    // *_DSP is the name to be shown to the user if the context makes it unique
    const YEARS_AUTO_CREATE = 'system config automatic created years';
    const YEARS_AUTO_CREATE_DSP = 'years to create';
    const DB_RETRY_MIN = 'system config database retry start delay in sec';
    const DB_RETRY_MAX = 'system config database retry max delay in sec';
    const AVG_CALC_TIME_SEC = 1000; // the default time in milliseconds for updating all results of on formula

    // program configuration names
    const SITE_NAME = 'site_name';                           // the name of the pod
    const VERSION_DB = 'version_database';                   // the version of the database at the moment to trigger an update script if needed
    const LAST_CONSISTENCY_CHECK = 'last_consistency_check'; // datetime of the last database consistency check
    const AVG_CALC_TIME = 'average_calculation_time';        // the average time to calculate and update all results of one formula in milliseconds
    const TEST_YEARS = 'test_years';                         // the number of years around the current year created automatically
    const MIN_PCT_OF_PHRASES_TO_PRESELECT = 0.3;             // if 30% or more of the phrases of a list are the same to probability is high that the next phrase is the same

    /*
     * database link
     */

    // comment used for the database creation
    const TBL_COMMENT = 'for the core configuration of this pod e.g. the program version or pod url';
    const FLD_NAME_COM = 'short name of the configuration entry to be shown to the admin';
    const FLD_NAME = 'config_name';
    const FLD_CODE_ID_COM = 'unique id text to select a configuration value from the code';
    const FLD_VALUE_COM = 'the configuration value as a string';
    const FLD_VALUE = 'value';
    const FLD_DESCRIPTION_COM = 'text to explain the config value to an admin user';
    const FLD_DESCRIPTION = 'description';

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_CODE_ID_COM],
        [sql::FLD_VALUE, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_VALUE_COM],
        [self::FLD_DESCRIPTION, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a config table
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc, false, [], '', false);
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a config table
     *
     * @param sql $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc, false, [],false);
        return $sql;
    }


    /*
     * load
     */

    function get_sql(sql_db $db_con, string $code_id): sql_par
    {
        // check the parameters to capsule this function
        if ($code_id == '') {
            log_err("The code id must be set", "config->get_sql");
        }

        $db_con->set_class(sql_db::TBL_CONFIG);
        $qp = new sql_par(self::class);
        $qp->name .= 'get';
        $db_con->set_name($qp->name);
        $db_con->set_fields(array(sql::FLD_CODE_ID, sql::FLD_VALUE, sandbox_named::FLD_DESCRIPTION));
        $db_con->add_par(sql_par_type::TEXT, $code_id);
        $qp->sql = $db_con->select_by_code_id();
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * get a config value from the preloaded values
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @return string|null the configuration value that is valid at the moment
     */
    function get(string $code_id): ?string
    {
        global $debug;

        // init
        $db_value = '';

        // the config table is existing since 0.0.2, so it does not need to be checked, if the config table itself exists

        log_debug('"' . $code_id . '": ' . $db_value, $debug - 1);
        return $db_value;
    }

    /**
     * get a config value from the database table
     * including $db_con because this is call also from the start, where the global $db_con is not yet set
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @param sql_db $db_con the open database connection that should be used
     * @return string|null the configuration value that is valid at the moment
     */
    function get_db(string $code_id, sql_db $db_con): ?string
    {
        global $debug;

        // init
        $db_value = '';

        // the config table is existing since 0.0.2, so it does not need to be checked, if the config table itself exists
        $qp = $this->get_sql($db_con, $code_id);
        $db_row = $db_con->get1($qp);
        if ($db_row == null) {
            // automatically create the config entry
            if ($this->create($code_id, $db_con)) {
                $db_value = $this->default_value($code_id);
            }
        } else {
            $db_code_id = $db_row[sql::FLD_CODE_ID];
            $db_value = $db_row[sql::FLD_VALUE];
            // if no value exists create it with the default value (a configuration value should never be empty)
            if ($db_code_id == '') {
                if ($this->create($code_id, $db_con)) {
                    $db_value = $this->default_value($code_id);
                }
            }
        }

        log_debug('"' . $code_id . '": ' . $db_value, $debug - 1);
        return $db_value;
    }

    /**
     * save a configuration value in the program configuration table of the database
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @param string $value the value that should be saved in the configuration table
     * @param sql_db $db_con the open database connection that should be used
     */
    function set(string $code_id, string $value, sql_db $db_con, string $description = ''): bool
    {
        global $debug;

        // init
        $result = false;
        log_debug('"' . $code_id . '" to ' . $value, $debug - 1);

        $qp = $this->get_sql($db_con, $code_id);
        $db_row = $db_con->get1($qp);
        if ($db_row == null) {
            // automatically add the config entry
            $result = $this->add($code_id, $value, $description, $db_con);
        } else {
            if ($value != $db_row[sql::FLD_VALUE] or $description != $db_row[sandbox_named::FLD_DESCRIPTION]) {
                $result = $this->update($code_id, $value, $description, $db_con);
            }
        }
        return $result;
    }

    /**
     * test if the config value is set to the expected value and if not set it
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @param string $target_value the value that should be saved in the configuration table
     * @param string $description text that explains the config value to the user or admin
     * @param sql_db $db_con the open database connection that should be used
     */
    function check(string $code_id, string $target_value, sql_db $db_con, string $description = ''): bool
    {
        $result = false;

        $cfg_value = $this->get_db($code_id, $db_con);
        if ($cfg_value != $target_value) {
            $result = $this->set(config::SITE_NAME, POD_NAME, $db_con, $description);
        }
        return $result;
    }

    /**
     * create configuration entry in the database for a new config item
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @param sql_db $db_con the open database connection that should be used
     * @return bool true if adding the config item has been successful
     */
    private function create(string $code_id, sql_db $db_con): bool
    {
        $result = false;
        log_debug('create "' . $code_id . '"');

        $db_value = $this->default_value($code_id);
        $db_description = $this->default_description($code_id);
        $db_id = $db_con->insert_old(
            array(
                sql::FLD_CODE_ID,
                sql::FLD_VALUE,
                sandbox_named::FLD_DESCRIPTION),
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
    private function add(string $code_id, string $value, string $description, sql_db $db_con): bool
    {
        $result = false;
        $db_id = $db_con->insert_old(
            array(
                sql::FLD_CODE_ID,
                sql::FLD_VALUE,
                sandbox_named::FLD_DESCRIPTION),
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
    private function update(string $code_id, string $value, string $description, sql_db $db_con): bool
    {
        $result = false;
        $db_id = $db_con->update_old(
            $code_id,
            array(
                sql::FLD_VALUE,
                sandbox_named::FLD_DESCRIPTION),
            array(
                $value,
                $description),
            sql::FLD_CODE_ID);
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
    private function default_value(string $code_id): string
    {

        // init
        $result = '';
        log_debug('default_value for "' . $code_id . '"');

        // check the parameters to capsule this function
        if ($code_id == '') {
            log_err("The code id must be set", "config->default_value");
        }

        switch ($code_id) {
            case self::VERSION_DB:
                $result = FIRST_VERSION;
                break;
            case self::AVG_CALC_TIME:
                $result = self::AVG_CALC_TIME_SEC;
                break;
        }

        return $result;
    }

    /**
     * get a default description for a configuration value
     * @param string $code_id the identification of the config item that is used in the code that should never be changed
     * @return string the default configuration description
     */
    private function default_description(string $code_id): string
    {

        // init
        $result = '';
        log_debug('default_description for "' . $code_id . '"');

        // check the parameters to capsule this function
        if ($code_id == '') {
            log_err("The code id must be set", "config->default_description");
        }

        switch ($code_id) {
            case self::VERSION_DB:
                $result = 'the program version which has last completed the update';
                break;
        }

        return $result;
    }

}
