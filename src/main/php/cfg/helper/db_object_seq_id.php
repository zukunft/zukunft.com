<?php

/*

    model/helper/db_object_seq_id.php - a base object for all database objects which have a unique id based on an int sequence
    ---------------------------------

    similar to db_object_seq_id

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this object
    - construct and map: including the mapping of the db row to this word object
    - set and get:       to capsule the vars from unexpected changes
    - cast:              create an api object and set the vars from an api json
    - sql create:        to create the database objects
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - information:       functions to make code easier to read
    - to overwrite:      functions that should always be overwritten by the child objects
    - interface:         to fill api messages
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\helper;

include_once API_SYSTEM_PATH . 'db_object.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
//include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_PATH . 'json_fields.php';

use api\system\db_object as db_object_api;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type_list;
use cfg\sandbox\sandbox;
use cfg\user\user_message;
use shared\json_fields;
use JsonSerializable;

class db_object_seq_id extends db_object implements JsonSerializable
{

    /*
     * db const
     */

    // database fields and comments
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID_SQL_TYP = sql_field_type::INT; // this default type is changed e.g. if the id is part of and index


    /*
     * object vars
     */

    // database fields that are used in all model objects
    // the database id is the unique prime key
    // is private because some objects like group have a complex id which needs a id() function
    private int $id;


    /*
     * construct and map
     */

    /**
     * reset the id to null to indicate that the database object has not been loaded
     */
    function __construct()
    {
        $this->set_id(0);
    }

    /**
     * reset the vars of this object
     * used to search for the standard object, because the search is word, value, formula or ... specific
     */
    function reset(): void
    {
        $this->set_id(0);
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child functions
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = false;
        $this->set_id(0);
        if ($db_row != null) {
            if (array_key_exists($id_fld, $db_row)) {
                if ($db_row[$id_fld] != 0) {
                    $this->set_id($db_row[$id_fld]);
                    $result = true;
                }
            }
        }
        return $result;
    }


    /*
     * set and get
     */

    /**
     * set the unique database id of a database object
     * @param int $id used in the row mapper and to set a dummy database id for unit tests
     */
    function set_id(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int
    {
        return $this->id;
    }


    /*
     * modify
     */

    /**
     * fill this seq id object based on the given object
     * if the given id is zero the id is never overwritten
     * if the given id is not zero the id is set if not yet done
     *
     * @param db_object_seq_id $sbx sandbox object with the values that should be updated e.g. based on the import
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(db_object_seq_id $sbx): user_message
    {
        $usr_msg = new user_message();
        if ($sbx->id() != 0) {
            if ($this->id() == 0) {
                $this->set_id($sbx->id());
            } elseif ($sbx->id() != $this->id()) {
                $usr_msg->add_message(
                    'Unexpected conflict of the database id. '
                    . $this->dsp_id() . ' != ' . $this->dsp_id());
            }
        }
        return $usr_msg;
    }


    /*
     * cast
     */

    /**
     * @return db_object_api the source frontend api object
     */
    function api_db_obj(): db_object_api
    {
        return new db_object_api($this->id());
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_db_obj()->get_json();
    }


    /*
     * sql create
     */

    /**
     * the sql statement to create the table
     * is e.g. overwritten for the user sandbox objects
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
     * the sql statement to create the database indices
     * is e.g. overwritten for the user sandbox objects
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

    /**
     * the sql statements to create all foreign keys
     * is e.g. overwritten for the user sandbox objects
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql_creator $sc): string
    {
        return $this->sql_foreign_key_create($sc, new sql_type_list());
    }

    /**
     *  create a list of fields with the parameters for this object
     *
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $small_key = $sc_par_lst->has_key_int_small();
        $use_sandbox = $sc_par_lst->use_sandbox_fields();
        if (!$usr_tbl) {
            if ($use_sandbox) {
                $fields = array_merge($this->sql_id_field_par(false, $small_key), sandbox::FLD_ALL_OWNER);
                $fields = array_merge($fields, $this::FLD_LST_MUST_BE_IN_STD);
            } else {
                $fields = array_merge($this->sql_id_field_par(false, $small_key), $this::FLD_LST_NAME, $this::FLD_LST_ALL);
                $fields = array_merge($fields, $this::FLD_LST_EXTRA);
            }
        } else {
            $fields = array_merge($this->sql_id_field_par(true, $small_key), sandbox::FLD_ALL_CHANGER);
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
        }
        $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        if (!$usr_tbl) {
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        }
        if ($use_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }

    /**
     * @return array[] with the parameters of the table key field
     */
    protected function sql_id_field_par(bool $usr_table = false, bool $small_key = false): array
    {
        if ($usr_table) {
            $fld_typ = sql_field_type::KEY_PART_INT;
            if ($small_key) {
                $fld_typ = sql_field_type::KEY_PART_INT_SMALL;
            }
            return array([
                $this->id_field(),
                $fld_typ,
                sql_field_default::NOT_NULL,
                sql::INDEX, $this::class,
                'with the user_id the internal unique primary index']);
        } else {
            $fld_typ = sql_field_type::KEY_INT;
            if ($small_key) {
                $fld_typ = sql_field_type::KEY_INT_SMALL;
            }
            return array([
                $this->id_field(),
                $fld_typ,
                sql_field_default::NOT_NULL,
                '', '',
                'the internal unique primary index']);
        }
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        return parent::load_sql_by_id_str($sc, $id);
    }

    /**
     * load one database row e.g. word, triple, value, formula, result, view, component or log entry from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        parent::load_without_id_return($qp);
        return $this->id();
    }


    /*
     * im- and export
     */

    /**
     * general part to import a database object from a JSON array object
     *
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_db_obj(db_object_seq_id $db_obj, object $test_obj = null): user_message
    {
        $usr_msg = new user_message();
        // add a dummy id for unit testing
        if ($test_obj) {
            $db_obj->set_id($test_obj->seq_id());
        }
        return $usr_msg;
    }


    /*
     * information
     */

    /**
     * @return bool true if the object has a valid database id
     */
    function is_loaded(): bool
    {
        if ($this->id() == null) {
            return false;
        } else {
            if ($this->id() != 0) {
                return true;
            } else {
                return false;
            }
        }
    }


    /*
     * to overwrite
     */

    /**
     * get the name of the database object (only used by named objects)
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    function name(): string
    {
        return 'ERROR: name function not overwritten by child object';
    }

    /**
     * load a row from the database selected by id
     * @param int $id the id of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $sc = $db_con->sql_creator();
        $qp = $this->load_sql_by_id($sc, $id);
        return $this->load($qp);
    }

    /**
     * load a row from the database selected by name (only used by named objects)
     * @param string $name the name of the word, triple, formula, verb, view or view component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_name(string $name): int
    {
        return 0;
    }


    /*
     * interface
     */

    /**
     * for the message from the backend to the frontend
     * @return array with the ID
     */
    function jsonSerialize(): array
    {
        $vars = [];
        $vars[json_fields::ID] = $this->id();
        return $vars;
    }

    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {
        if ($this->id() != 0) {
            return ' (' . $this->id_field() . ' ' . $this->id() . ')';
        } else {
            return ' (' . $this->id_field() . ' no set)';
        }
    }

}
