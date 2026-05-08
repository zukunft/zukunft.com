<?php

/*

    service/config.php - functions to handle the core system configuration values
    ------------------

    TODO use single words with code_id for the system configuration
    TODO check on system start that the system configuration is complete
    TODO move all possible values to the phrase based configuration

    the values in the config table can only be changed by the system or the pod admin
    expose the config class functions as simple functions for simple coding

    .env vs config table/class vs config.yaml:
    - the .env contains basic system environment pod and server settings
      -> if e.g. a new postgres version is deployed the .env might need to be updated
    - the .config table/class contains the settings that cannot be changed without code change
      -> if e.g. a new version of the zukunft.com code is deployed the entries of the config table are used to detect if the database needs to be upgraded
    - the config.yaml contains all settings that the admin can adjust without code change
      -> if e.g. an additional connection to other pods is configured that can be done via the admin gui and is stored within the graph database


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

namespace Zukunft\ZukunftCom\main\php\service;

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql.php';

//include_once paths::MODEL_USER . 'user.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

class config extends db_object_seq_id
{

    // reserved word and triple names used for the system configuration
    // *_DSP is the name to be shown to the user if the context makes it unique
    const string YEARS_AUTO_CREATE = 'system config automatic created years';
    const string YEARS_AUTO_CREATE_DSP = 'years to create';
    const string DB_RETRY_MIN = 'system config database retry start delay in sec';
    const string DB_RETRY_MAX = 'system config database retry max delay in sec';
    const int AVG_CALC_TIME_SEC = 1000; // the default time in milliseconds for updating all results of on formula

    // program configuration names
    const string SITE_NAME = 'site_name';                           // the name of the pod
    const string VERSION_DB = 'version_database';                   // the version of the database at the moment to trigger an update script if needed
    const string VERSION_DB_NAME = 'Database version';
    const string VERSION_DB_COM = 'version that the database has now; after the upgrade the new version number is written to the database';
    const string LAST_CONSISTENCY_CHECK = 'last_consistency_check'; // datetime of the last database consistency check
    const string AVG_CALC_TIME = 'average_calculation_time';        // the average time to calculate and update all results of one formula in milliseconds
    const string TEST_YEARS = 'test_years';                         // the number of years around the current year created automatically
    const float MIN_PCT_OF_PHRASES_TO_PRESELECT = 0.3;             // if 30% or more of the phrases of a list are the same to probability is high that the next phrase is the same

    /*
     * database link
     */

    // comment used for the database creation
    const string FLD_ID = 'config_id';
    const string TBL_COMMENT = 'for the core configuration of this pod e.g. the program version or pod url';
    const string FLD_NAME_COM = 'short name of the configuration entry to be shown to the admin';
    const string FLD_NAME = 'config_name';
    const string FLD_CODE_ID_COM = 'unique id text to select a configuration value from the code';
    const string FLD_VALUE_COM = 'the configuration value as a string';
    const string FLD_VALUE = 'value';
    const string FLD_DESCRIPTION_COM = 'text to explain the config value to an admin user';

    // all database field names excluding the id
    // TODO review and sync with FLD_LST_ALL
    const array FLD_NAMES = array(
        self::FLD_NAME,
        sql_db::FLD_CODE_ID,
        sql_db::FLD_VALUE,
        sql_db::FLD_DESCRIPTION,
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_VALUE, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_VALUE_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );


    /*
     * object vars
     */

    // database fields
    public ?string $name = null;                // the short name of the config entry
    public ?string $code_id = null;             // the unique code_id to select the config entry from the program code
    public string|int|float|null $value = null; // the configuration value
    public ?string $description = null;         // the description of the value for the admin


    /*
     * construct and map
     */

    /**
     * reset the vars of this config
     * @param bool $keep_user not used here
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->name = null;
        $this->code_id = null;
        $this->value = null;
        $this->description = null;
    }

    /**
     * map the database fields to the config db row to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->name = $db_row[self::FLD_NAME];
            $this->code_id = $db_row[sql_db::FLD_CODE_ID];
            $this->value = $db_row[sql_db::FLD_VALUE];
            $this->description = $db_row[sql_db::FLD_DESCRIPTION];
        }
        return $result;
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the tables of a config table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc);
        return $sql;
    }

    /**
     * the sql statement to create the database indices of a config table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc);
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

        $db_con->set_class(config::class);
        $qp = new sql_par(self::class);
        $qp->name .= 'get';
        $db_con->set_name($qp->name);
        $db_con->set_fields(array(sql_db::FLD_CODE_ID, sql_db::FLD_VALUE, sql_db::FLD_DESCRIPTION));
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
            $this->set($code_id, $this->default_value($code_id), $db_con, $this->default_description($code_id));
        } else {
            $db_code_id = $db_row[sql_db::FLD_CODE_ID];
            $db_value = $db_row[sql_db::FLD_VALUE];
            // if no value exists create it with the default value (a configuration value should never be empty)
            if ($db_code_id == '') {
                $this->set($code_id, $this->default_value($code_id), $db_con, $this->default_description($code_id));
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
        $msg = new user_message();
        $result = false;
        log_debug('"' . $code_id . '" to ' . $value, $debug - 1);

        // by default all changes are logged
        $sc_par_lst = new sql_type_list([sql_type::LOG]);

        // prepare object (to be moved to calling function
        $cfg = new config();
        $cfg->code_id = $code_id;
        $cfg->value = $value;
        $cfg->description = $description;

        // check the database entry
        $qp = $this->get_sql($db_con, $code_id);
        $db_row = $db_con->get1($qp);

        if ($db_row == null) {
            // automatically add the config entry
            $result = $cfg->db_add($msg, $db_con, $sc_par_lst);
        } else {
            $cfg_db = new config();
            $cfg_db->row_mapper($db_row);
            if ($value != $db_row[sql_db::FLD_VALUE] or $description != $db_row[sql_db::FLD_DESCRIPTION]) {
                $result = $this->db_update_row($cfg_db, $msg, $db_con, $sc_par_lst);
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
    function check_cfg(string $code_id, string $target_value, sql_db $db_con, string $description = ''): bool
    {
        $result = false;

        $cfg_value = $this->get_db($code_id, $db_con);
        if ($cfg_value != $target_value) {
            $result = $this->set(config::SITE_NAME, POD_NAME, $db_con, $description);
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
                $result = def::FIRST_VERSION;
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

    /**
     * get the name of the config
     *
     * @return string the name from the system configuration entry
     */
    function name(): string|null
    {
        if ($this->name == null) {
            return null;
        } else {
            return $this->name;
        }
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return sql_db::FLD_CODE_ID;
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     *
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return self::FLD_NAMES;
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param config|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        config|db_object_seq_id $obj,
        user_message            $msg,
        sql_type_list           $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $lst = new sql_par_field_list();
        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        if ($do_log) {
            $table_id = $sc->table_id($this::class);
        }

        // the short name of the config entry
        if ($obj->name !== $this->name) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . self::FLD_NAME,
                    $sys->typ_lst->cng_fld->id($table_id . self::FLD_NAME),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                self::FLD_NAME,
                $this->name,
                sandbox_named::FLD_NAME_SQL_TYP,
                $obj->name
            );
        }

        // the unique code id that may only change in case of an upgrade
        if ($obj->code_id !== $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_CODE_ID,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_CODE_ID,
                $this->code_id,
                sql_field_type::CODE_ID,
                $obj->code_id
            );
        }

        // the configuration value itself
        if ($obj->value !== $this->value) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_VALUE,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_VALUE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_VALUE,
                $this->value,
                sql_field_type::TEXT,
                $obj->value
            );
        }

        // the description is mainly used for system users
        if ($obj->description !== $this->description) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_DESCRIPTION,
                    $sys->typ_lst->cng_fld->id($table_id . sql_db::FLD_DESCRIPTION),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_DESCRIPTION,
                $this->description,
                sql_db::FLD_DESCRIPTION_SQL_TYP,
                $obj->description
            );
        }

        return $lst;
    }


}
