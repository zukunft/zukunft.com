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
    - sql create:        to create the database objects
    - load:              database access object (DAO) functions
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - modify:            change potentially all variables of this word object
    - info:              functions to make code easier to read
    - to overwrite:      functions that should always be overwritten by the child objects
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

use cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
//include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type_list;
use cfg\sandbox\sandbox;
use cfg\user\user;
use cfg\user\user_message;
use controller\api_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\types\api_type_list;
use shared\json_fields;
use shared\library;

class db_object_seq_id extends db_object
{

    /*
     * db const
     */

    // database fields and comments
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID_SQL_TYP = sql_field_type::INT; // this default type is changed e.g. if the id is part of and index


    /*
     * construct and map
     */

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
                // TODO check that $this->reset() is removed from all load function and only this reset is used
                $this->reset();
                if ($db_row[$id_fld] != 0) {
                    $this->set_id($db_row[$id_fld]);
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * fill the vars with this database id object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @return user_message
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = new user_message();

        if (array_key_exists(json_fields::ID, $api_json)) {
            $this->set_id($api_json[json_fields::ID]);
        }

        return $usr_msg;
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
     * load one database row e.g. word, triple, value, formula, result, view, component or log entry from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        parent::load_without_id_return($qp);
        return $this->id();
    }

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


    /*
     * api
     */

    /**
     * create the api json message string of this database object for the frontend
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns string the api json message for the object as a string
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        // null values are not needed in the api message to the frontend
        // but in the api message to the backend null values are relevant
        // e.g. to remove empty string overwrites
        $vars = $this->api_json_array($typ_lst, $usr);
        $vars = array_filter($vars, fn($value) => !is_null($value) && $value !== '');

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con, $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = [];
        $vars[json_fields::ID] = $this->id();
        return $vars;
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
     * info
     */

    /**
     * create human-readable messages of the differences between the db id objects
     * @param CombineObject|db_object_seq_id $obj which might be different to this db id object
     * @return user_message the human-readable messages of the differences between the db id objects
     */
    function diff_msg(CombineObject|db_object_seq_id $obj): user_message
    {
        $usr_msg = new user_message();
        if ($this->id() != $obj->id()) {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::DIFF_ID, [
                msg_id::VAR_ID => $obj->id(),
                msg_id::VAR_ID_CHK => $this->id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $usr_msg;
    }


    /*
     * modify
     */

    /**
     * fill this seq id object based on the given object
     * if the given id is zero the id is never overwritten
     * if the given id is not zero the id is set if not yet done
     * similar to db_object_multi->fill
     *
     * @param CombineObject|db_object_seq_id $obj object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = new user_message();
        if ($obj->id() != 0) {
            if ($this->id() == 0) {
                $this->set_id($obj->id());
            } elseif ($obj->id() != $this->id()) {
                $usr_msg->add_id_with_vars(msg_id::CONFLICT_DB_ID, [msg_id::VAR_ID => $this->dsp_id()]);
            }
        }
        return $usr_msg;
    }


    /*
     * info
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
        return 'ERROR: name function not overwritten by child object ' . $this::class;
    }

    /**
     * get the name of the database object which can be null if db object does not yet exist (only used by named objects)
     *
     * @return string|null the name from the object e.g. word using the same function as the phrase and term
     */
    function name_or_null(): ?string
    {
        return 'ERROR: name_or_null function not overwritten by child object ' . $this::class;
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


    /*
     * overwrite
     */

    /**
     * add or update an object to the database
     * to be overwritten by the child object
     *
     * @param bool|null $use_func if true a predefined function is used that also creates the log entries
     * @return user_message the message that should be shown to the user in case something went wrong
     */

    function save(?bool $use_func = null): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add_id_with_vars(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'save in db_object_seq_id',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg;
    }

    /**
     * delete or exclude an object from or in the database
     * to be overwritten by the child object
     *
     * @return user_message the message that should be shown to the user in case something went wrong
     */

    function del(): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add_id_with_vars(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'del in db_object_seq_id',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg;
    }

}
