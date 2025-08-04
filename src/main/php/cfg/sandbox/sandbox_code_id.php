<?php

/*

    cfg/sandbox/sandbox_named_code_id.php - superclass for handling objects with a code id
    -------------------------------------

    This superclass adds the code_id handling to named classes words, formula, ...

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - im- and export:    create an export object and set the vars from an import object
    - set and get:       to capsule the vars from unexpected changes
    - load:              database access object (DAO) functions
    - load sql:          create the sql statements for loading from the db
    - info:              functions to make code easier to read
    - sql write fields:  field list for writing to the database


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

namespace cfg\sandbox;

use cfg\const\paths;

include_once paths::MODEL_SANDBOX . 'sandbox_typed.php';

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_type_list;
use cfg\helper\data_object;
use cfg\helper\db_object_seq_id;
use cfg\log\change;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\types\api_type_list;
use shared\json_fields;
use shared\library;

class sandbox_code_id extends sandbox_typed
{

    /*
     * object vars
     */

    // database field to select single object used by the system
    // without using the type that can potentially select more than one object
    private ?string $code_id;


    /*
     * construct and map
     */

    /**
     * reset the object vars e.g. to detect the vars changed by the api versus the db value
     */
    function reset(): void
    {
        parent::reset();
        $this->code_id = null;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child object
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as set in the child class
     * @param string $name_fld the name of the name field as set in the child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if this object is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = '',
        string $name_fld = '',
        string $type_fld = ''
    ): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld, $type_fld);
        if ($result) {
            if (array_key_exists(sql_db::FLD_CODE_ID, $db_row)) {
                $this->set_code_id_db($db_row[sql_db::FLD_CODE_ID]);
            }
        }
        return $result;
    }

    /**
     * set the vars of this object bases on the api json array
     * it is expected that the code id is set via import by an admin not via api
     * so mapping the code id is only allowed if the code id is empty
     *
     * @param array $api_json an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $api_json): user_message
    {
        $usr_msg = parent::api_mapper($api_json);
        if ($this->code_id() == null) {
            if (array_key_exists(json_fields::CODE_ID, $api_json)) {
                $this->set_code_id_db($api_json[json_fields::CODE_ID]);
            }
        }
        return $usr_msg;
    }


    /**
     * to import the user sandbox object from a json string
     * set the code id only if the requesting user is allowed to
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user $usr_req the user who has initiated the import mainly used to add tge code id to the database
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @param object|null $test_obj if not null the unit test object to get a dummy seq id
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_mapper_user(
        array       $in_ex_json,
        user        $usr_req,
        data_object $dto = null,
        object      $test_obj = null
    ): user_message
    {
        $usr_msg = parent::import_mapper_user($in_ex_json, $usr_req, $dto, $test_obj);

        if (key_exists(json_fields::CODE_ID, $in_ex_json)) {
            if ($in_ex_json[json_fields::CODE_ID] <> '') {
                $this->set_code_id($in_ex_json[json_fields::CODE_ID], $usr_req);
            }
        }

        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        // the code id is included in the api message towards the frontend
        // but not overwritten via api message
        $vars[json_fields::CODE_ID] = $this->code_id();
        return $vars;
    }


    /*
     * im- and export
     */

    /**
     * create an array with the export json fields
     * @param bool $do_load true if any missing data should be loaded while creating the array
     * @return array with the json fields
     */
    function export_json(bool $do_load = true): array
    {
        $vars = parent::export_json($do_load);
        // include the code id in the api message so that the frontend can execute some behavior
        if ($this->code_id() != '' and $this->code_id() != null) {
            $vars[json_fields::CODE_ID] = $this->code_id();
        }
        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the unique id to select a single word by the program
     *
     * @param string|null $code_id the unique key to select a word used by the system e.g. for the system or configuration
     * @param user $usr the user who has requested the change
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_code_id(?string $code_id, user $usr): user_message
    {
        $usr_msg = new user_message();
        if ($usr->can_set_code_id()) {
            $this->code_id = $code_id;
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr->name(),
                msg_id::VAR_USER_PROFILE => $usr->profile_code_id(),
                msg_id::VAR_NAME => sql_db::FLD_CODE_ID,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $usr_msg;
    }

    /**
     * set the code id without check
     * should only be called by the database mapper function
     */
    function set_code_id_db(?string $code_id): void
    {
        $this->code_id = $code_id;
    }

    /**
     * @return string|null the unique key or null if the word is not used by the system
     */
    function code_id(): ?string
    {
        return $this->code_id;
    }


    /*
     * load
     */

    /**
     * load this object by code id
     * @param string $code_id the code id of the word, triple, source, formula, view or component
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_code_id(string $code_id): int
    {
        global $db_con;

        log_debug($code_id);
        $qp = $this->load_sql_by_code_id($db_con->sql_creator(), $code_id);
        return parent::load($qp);
    }


    /*
     * load sql
     */

    /**
     * create an SQL statement to retrieve a source by code id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $code_id the code id of the source
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_code_id(sql_creator $sc, string $code_id): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_CODE_ID);
        $sc->add_where(sql_db::FLD_CODE_ID, $code_id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * modify
     */

    /**
     * fill this object based on the given object
     * if the id is set in the given object loaded from the database but this import object does not yet have the db id, set the id
     * if the given description is not set (null) the description is not remove
     * if the given description is an empty string the description is removed
     *
     * @param sandbox|CombineObject|db_object_seq_id $obj word with the values that should have been updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->code_id() != null) {
            $usr_msg->add($this->set_code_id($obj->code_id(), $usr_req));
        }
        return $usr_msg;
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the objects
     * @param sandbox|CombineObject|db_object_seq_id $obj which might be different to this sandbox object
     * @return user_message the human-readable messages of the differences between the sandbox objects
     */
    function diff_msg(sandbox|CombineObject|db_object_seq_id $obj): user_message
    {
        $usr_msg = parent::diff_msg($obj);
        if ($this->code_id() != $obj->code_id()) {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::DIFF_CODE_ID, [
                msg_id::VAR_NAME => $obj->code_id(),
                msg_id::VAR_NAME_CHK => $this->code_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_SANDBOX_NAME => $this->name(),
            ]);
        }
        return $usr_msg;
    }

    /**
     * check if the named object in the database needs to be updated
     * is expected to be similar to the diff_msg function
     * @param sandbox|sandbox_link|CombineObject|db_object_seq_id $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(sandbox|sandbox_link|CombineObject|db_object_seq_id $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->code_id != null) {
            if ($this->code_id() != $db_obj->code_id()) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * sql write fields
     */

    /**
     * add the code id to the list of all database fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                sql_db::FLD_CODE_ID,
            ]
        );
    }

    /**
     * add the code id field to the list of changed fields if the code_id has been changed
     *
     * @param sandbox_code_id|sandbox $sbx the same named sandbox as this to compare which fields have been changed
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list with the field names of the object and any child object
     */
    function db_fields_changed(
        sandbox_code_id|sandbox $sbx,
        sql_type_list           $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $cng_fld_cac;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($sbx, $sc_par_lst);
        if ($sbx->code_id <> $this->code_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . sql_db::FLD_CODE_ID,
                    $cng_fld_cac->id($table_id . sql_db::FLD_CODE_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                sql_db::FLD_CODE_ID,
                $this->code_id,
                sql_field_type::CODE_ID,
                $sbx->code_id
            );
        }
        return $lst;
    }

}


