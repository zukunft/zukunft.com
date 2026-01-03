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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

//include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
//include_once paths::DB . 'sql_par.php';
//include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
//include_once paths::MODEL_LOG . 'change.php';
//include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'db_object.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::SHARED_ENUM . 'change_actions.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\log\change_action;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\shared\enum\change_actions;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class db_object_seq_id extends db_object
{

    /*
     * db const
     */

    // database fields and comments
    // *_SQL_TYP is the sql data type used for the field
    const sql_field_type FLD_ID_SQL_TYP = sql_field_type::INT; // this default type is changed e.g. if the id is part of and index


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
        $result = parent::row_mapper($db_row, $id_fld);
        $this->id = 0;
        if ($db_row != null) {
            if (array_key_exists($id_fld, $db_row)) {
                // TODO check that $this->reset() is removed from all load function and only this reset is used
                $this->reset();
                if ($db_row[$id_fld] != 0) {
                    $this->id = $db_row[$id_fld];
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * fill the vars with this database id object based on the given api json array
     * @param array $api_json the api array with the word values that should be mapped
     * @param user_message $usr_msg if the mapping is incomplete, the human-readable message what happened and how to solve it
     *                              including the user who has requested the mapping e.g. to check permissions to set code id or profiles
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $api_json, user_message $usr_msg): bool
    {
        if (array_key_exists(json_fields::ID, $api_json)) {
            $this->id = $api_json[json_fields::ID];
        }
        return $usr_msg->is_ok();
    }


    /*
     * sql write
     */

    /**
     * the common part of the sql_insert and sql_update functions
     * TODO include the sql statements to log the changes
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension to differ the queries based on the fields changed
     * @return sql_par prepared sql parameter object with the name set
     */
    protected function sql_common(sql_creator $sc, sql_type_list $sc_par_lst = new sql_type_list(), string $ext = ''): sql_par
    {
        $qp = new sql_par($this::class, $sc_par_lst, $ext);

        // update the sql creator settings
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);

        return $qp;
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
        global $db_con;
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        $vars = $this->api_json_array($typ_lst, $usr);
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
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
     * the import_mapper fills the vars with this object based on the given im-/export json array
     * the import_mapper never reads of writes to the database which is done by dto_save() or import_obj()
     * instead the given data object cache is used and filled
     * the data object cache is given as a parameter to be able to test different used cases
     *
     * this is the general part to import a database object from a JSON array object
     * has been the setting of a dummy sequence id
     * kept for future use
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        $usr_msg->start_time = microtime(true);
        return $usr_msg->is_ok();
    }

    /**
     * import a single json object
     *
     * @param array $in_ex_json an array with the data of the json object but without any database ids
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_obj(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        global $db_con;

        // map the json to the object
        $this->import_mapper($in_ex_json, $usr_msg, $dto);;

        // save the object and the related objects in the database
        if ($db_con->is_open()) {
            if ($usr_msg->is_ok()) {
                $this->save($usr_msg);
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::IMPORT_NOT_SAVED, [
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                    msg_id::VAR_ID => $this->dsp_id()
                ]);
            }
        }

        return $usr_msg->is_ok();
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
                $this->id = $obj->id();
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
     * overwrite
     */

    /**
     * get the name of the database object (only used by named objects)
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    function name(): string
    {
        $msg = 'ERROR: name function not overwritten by child object ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * get the name of the database object which can be null if db object does not yet exist (only used by named objects)
     *
     * @return string|null the name from the object e.g. word using the same function as the phrase and term
     */
    function name_or_null(): ?string
    {
        $msg = 'ERROR: name_or_null function not overwritten by child object ' . $this::class;
        log_err($msg);
        return $msg;
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
     * save
     */

    /**
     * add or update a row in the database
     * TODO Prio 2 dismiss $use_func
     *
     * @param user_message $usr_msg to collect the problem messages and solution for the requesting user
     * @param bool|null $use_func false if the prepared SQL statement cannot yet be used
     * @return bool true if everything has been fine
     */
    function save(user_message $usr_msg, ?bool $use_func = true): bool
    {
        global $db_con;

        log_debug($this->dsp_id());

        // check e.g. if another unique key is already exists or a preserved name is used
        $this->check($usr_msg);

        // create a new database row or update an existing
        if ($usr_msg->is_ok()) {
            if (!$this->has_db_id()) {
                $this->db_add($usr_msg, $db_con);
            } else {
                $this->db_update($usr_msg, $db_con);
            }
        }

        return $usr_msg->is_ok();
    }

    protected function db_add(user_message $usr_msg, sql_db $db_con): bool
    {
        log_debug('add ' . $this->dsp_id());
        // if the user has the right to change the database row ...
        if ($this->can_be_added_by($usr_msg)) {
            // ... create the prepared sql function ...
            $sc = $db_con->sql_creator();
            $qp = $this->sql_insert($sc, $usr_msg, new sql_type_list([]));

            // ... and update the database row
            $db_con->update($qp, 'update ' . $this->dsp_id(), $usr_msg);

            log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NO_UPDATE_PRIVILEGES, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr_msg->usr->name()
            ]);
        }

        $usr_msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'db_add',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->is_ok();
    }

    /**
     * updated all changed fields in the database with one sql function
     * and log the changes if needed
     *
     * @param user_message $usr_msg to collect the problem messages and solution for the requesting user
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true is the database row has been updated
     */
    protected function db_update(user_message $usr_msg, sql_db $db_con): bool
    {
        log_debug('update ' . $this->dsp_id());

        // read the database values to be able to check if something has been changed
        // TODO Prio 2 to be added also to the prepared SQL statement
        $db_rec = $this->clone_all();
        $db_rec->reset(true);
        $db_rec->load_by_id($this->id());

        // if the user has the right to change the database row ...
        if ($this->can_be_changed_by($usr_msg, $db_rec)) {
            // ... create the prepared sql function ...
            $sc = $db_con->sql_creator();
            $qp = $this->sql_update($sc, $db_rec, $usr_msg, new sql_type_list([]));

            // ... and update the database row
            $db_con->update($qp, 'update ' . $this->dsp_id(), $usr_msg);

            log_debug('all fields for ' . $this->dsp_id() . ' has been saved');
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NO_UPDATE_PRIVILEGES, [
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->name(),
                msg_id::VAR_USER_PROFILE => $usr_msg->usr->name()
            ]);
        }

        return $usr_msg->is_ok();
    }

    /**
     * delete or exclude an object from or in the database
     * to be overwritten by the child object
     *
     * @param user_message $usr_msg the message that should be shown to the user in case something went wrong
     * @return bool true if everything has been fine
     */

    function del(user_message $usr_msg): bool
    {
        $usr_msg = new user_message();
        $usr_msg->add_id_with_vars(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'del in db_object_seq_id',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->is_ok();
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a new row to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        user_message  $usr_msg,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::INSERT);
        // get the fields and values that are filled and should be written to the db
        $row_empty = $this->clone_all();
        $row_empty->reset(true);
        return $this->sql_write($sc, $row_empty, $usr_msg, $sc_par_lst_used);
    }

    /**
     * create the sql statement to update a row in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param type_object $db_row the sandbox object with the database values before the update
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_update(
        sql_creator      $sc,
        db_object_seq_id $db_row,
        user_message     $usr_msg,
        sql_type_list    $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::UPDATE);
        return $this->sql_write($sc, $db_row, $usr_msg, $sc_par_lst_used);
    }


    /**
     * create the sql statement to add or update an object in the database
     * all fields are always included in the query to be able to remove overwriting with a null value
     *
     * @param sql_creator $sc with the target db_type set
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_write(
        sql_creator      $sc,
        db_object_seq_id $db_row,
        user_message     $usr_msg,
        sql_type_list    $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $sys;

        // get a list of all fields that could potentially be updated
        $fld_lst_all = $this->db_fields_all();
        // get the list of all fields that can be changed by the user
        $fvt_lst = $this->db_fields_changed($db_row, $usr_msg, $sc_par_lst);
        // TODO Prio 1 move the line from here to the end to a sql_write function and move it to the parent object
        // make the query name unique based on the changed fields
        $lib = new library();
        $ext = sql::NAME_SEP . $lib->sql_field_ext($fvt_lst, $fld_lst_all, $usr_msg);
        // create the main query parameter object and set the query name
        $qp = $this->sql_common($sc, $sc_par_lst, $ext);
        // log functions must always use named parameters
        $sc_par_lst->add(sql_type::NAMED_PAR);
        // set some var names to shorten the code lines
        $id_fld = $sc->id_field_name();
        if ($sc_par_lst->is_insert()) {
            $var_name_row_id = $sc->var_name_row_id($sc_par_lst);
        } else {
            $var_name_row_id = '_' . $id_fld;
        }

        // add the change action field to the field list for the log entries
        $fvt_lst->add_field(
            change_action::FLD_ID,
            $sys->typ_lst->cng_act->id(change_actions::ADD),
            type_object::FLD_ID_SQL_TYP
        );

        // list of parameters actually used in order of the function usage
        $par_lst_out = new sql_par_field_list();

        // init the function body
        if ($sc_par_lst->is_insert()) {
            $id_fld_new = $sc->var_name_new_id($sc_par_lst);
        } else {
            $id_fld_new = '';
        }
        $sql = $sc->sql_func_start($id_fld_new, $sc_par_lst);

        // get the data fields and move the unique db key field to the first entry
        $fld_lst_chg = array_intersect($fvt_lst->names(), $fld_lst_all);
        $key_fld_pos = array_search($this->id_field(), $fld_lst_chg);
        unset($fld_lst_chg[$key_fld_pos]);

        // add the user to the field list so that the id can be used for the log
        $fvt_lst->add_field(
            user_db::FLD_ID,
            $usr_msg->usr->id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );

        // update the fields excluding the unique id
        $update_fvt_lst = new sql_par_field_list();
        foreach ($fld_lst_chg as $fld) {
            $update_fvt_lst->add($fvt_lst->get($fld, $usr_msg));
        }
        $sc_update = clone $sc;
        $sc_par_lst_upd = $sc_par_lst;
        $sc_par_lst_upd->add(sql_type::UPDATE);
        $sc_par_lst_upd_ex_log = $sc_par_lst_upd->remove(sql_type::LOG);
        $sc_par_lst_upd_ex_log->add(sql_type::SUB);
        $qp_update = $this->sql_common($sc_update, $sc_par_lst_upd_ex_log);

        $qp_update->sql = $sc_update->create_sql_update(
            $id_fld, $var_name_row_id, $update_fvt_lst, [], $sc_par_lst_upd_ex_log);
        // add the insert row to the function body
        $sql .= ' ' . $qp_update->sql . ' ';

        if ($sc->db_type == sql_db::POSTGRES) {
            if ($id_fld_new != '') {
                $sql .= sql::RETURN . ' ' . $id_fld_new . '; ';
            }
        }

        // create the query parameters for the actual change
        $qp_chg = clone $qp;

        $sql .= $sc->sql_func_end();

        $qp_chg->sql = $sc->create_sql_insert($par_lst_out, $sc_par_lst);

        // merge all together and create the function
        $qp->sql = $qp_chg->sql . $sql . ';';
        $qp->par = $par_lst_out->values();

        // create the call sql statement
        return $sc->sql_call($qp, $qp_chg->name, $par_lst_out);
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return [
            $this->id_field(),
        ];
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        db_object_seq_id $obj,
        user_message     $usr_msg,
        sql_type_list    $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = new sql_par_field_list();
        $lst->add_field(
            $this->id_field(),
            $obj->id(),
            db_object_seq_id::FLD_ID_SQL_TYP
        );
        return $lst;
    }


    /*
     * db helper
     */

    /**
     * check if the user can add this object to the database
     * e.g. reject if a reserved name is used and the user is not a system test user or an admin user
     * to be overwritten by the child objects
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check(user_message $usr_msg): bool
    {
        $usr_msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'check',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->is_ok();
    }

    function has_db_id(): bool
    {
        if ($this->id() != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * true if the requesting user is allowed to add this object
     *
     * @param user_message $usr_msg the user who has requested the update and the object to collect the potential reject messages
     * @return bool true if the is allowed to add the object
     */
    function can_be_added_by(user_message $usr_msg): bool
    {
        $can_change = true;
        $lib = new library();
        $class = $lib->class_to_name($this::class);

        // default is that all user can add data if they are not blocked
        if ($usr_msg->usr->is_blocked()) {
            $can_change = false;
            log_warning('adding of ' . $class . ' ' . $this->dsp_id() . ' by user ' . $usr_msg->usr->dsp_id() . ' is blocked');
        }

        return $can_change;
    }

    /**
     * true if the requesting user is allowed to change this object
     *
     * @param user_message $usr_msg the user who has requested the update and the object to collect the potential reject messages
     * @param db_object_seq_id $db_rec the object that is loaded from the database to project single field changes
     * @return bool true if the is allowed to change the object
     */
    function can_be_changed_by(user_message $usr_msg, db_object_seq_id $db_rec): bool
    {
        $can_change = false;
        $lib = new library();
        $class = $lib->class_to_name($this::class);

        // if the user has a unique id e.g. if at least the email is known
        // the user if potentially allowed to change the object
        if ($usr_msg->usr->is_unique()) {
            $can_change = true;
            log_info($class . ' ' . $this->dsp_id() . ' is change by user ' . $usr_msg->usr->dsp_id());
        }

        return $can_change;
    }


    /*
     * similar
     */

    /**
     * dummy function that is supposed to be overwritten by the child classes for e.g. named or link objects
     *
     * check if an object with the unique key already exists
     * returns null if no similar object is found
     * or returns the object with the same unique key that is not the actual object,
     * any warning or error message needs to be created in the calling function
     * e.g. if the user tries to create a formula named "millions"
     *      but a word with the same name already exists, a term with the word "millions" is returned
     *      in this case the calling function should suggest the user to name the formula "scale millions"
     *      to prevent confusion when writing a formula where all words, phrases, verbs and formulas should be unique
     * @param user_message $usr_msg the user who has requested the update and the object to collect the potential reject messages
     * @returns db_object|null a filled object that has the same name or links the same objects
     *                         or a sandbox object with id() = 0 if nothing similar has been found
     */
    function get_similar(user_message $usr_msg): db_object|null
    {
        $usr_msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'get_similar',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $this;
    }

}
