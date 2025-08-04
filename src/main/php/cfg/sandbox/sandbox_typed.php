<?php

/*

    model/sandbox/sandbox_description.php - adding the description field to the _sandbox superclass
    -------------------------------------

    This superclass should be used by the classes words, formula, ... su that users can link predefined behavior

    The main sections of this object are
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - api:               create an api array for the frontend and set the vars based on a frontend api message
    - set and get:       to capsule the variables from unexpected changes
    - preloaded:         get preloaded information such as the type code id
    - info:              functions to make code easier to read
    - modify:            change potentially all variables of this sandbox object
    - cast:              create an api object and set the vars from an api json
    - save:              manage to update the database


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

include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
//include_once paths::MODEL_HELPER . 'type_list.php';
//include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql_db;
use cfg\helper\data_object;
use cfg\helper\db_object_seq_id;
use cfg\helper\type_list;
use cfg\ref\source;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\types\api_type_list;
use shared\json_fields;
use shared\library;

class sandbox_typed extends sandbox_named
{

    /*
     * object vars
     */

    // database id of the type used for named user sandbox objects with predefined functionality
    // such as words, formulas, values, terms and view component links
    // because all types are preloaded with the database id the name and code id can fast be received
    // the id of the source type, view type, view component type or word type
    // e.g. to classify measure words
    // the id of the source type, view type, view component type or word type e.g. to classify measure words
    // or the formula type to link special behavior to special formulas like "this" or "next"
    public ?int $type_id = null;


    /*
     * construct and map
     */

    function reset(): void
    {
        parent::reset();
        $this->type_id = null;
    }

    /**
     * map the database fields to the object fields
     *
     * @param array|null $db_row with the data directly from the database
     * @param bool $load_std true if only the standard user sandbox object is loaded
     * @param bool $allow_usr_protect false for using the standard protection settings for the default object used for all users
     * @param string $id_fld the name of the id field as defined in this child and given to the parent
     * @param string $name_fld the name of the name field as defined in this child class
     * @param string $type_fld the name of the type field as defined in this child class
     * @return bool true if this object is loaded and valid
     */
    function row_mapper_sandbox(
        ?array $db_row,
        bool   $load_std = false,
        bool   $allow_usr_protect = true,
        string $id_fld = '',
        string $name_fld = '',
        string $type_fld = ''): bool
    {
        $result = parent::row_mapper_sandbox($db_row, $load_std, $allow_usr_protect, $id_fld, $name_fld);
        if ($result) {
            // TODO easy use set_type_by_id function
            if (array_key_exists($type_fld, $db_row)) {
                $this->type_id = $db_row[$type_fld];
            }
        }
        return $result;
    }

    /**
     * set the type based on the api json
     * @param array $api_json the api json array with the values that should be mapped
     */
    function api_mapper(array $api_json): user_message
    {
        global $usr;
        $msg = parent::api_mapper($api_json);

        if (key_exists(json_fields::TYPE, $api_json)) {
            $this->set_type_id($api_json[json_fields::TYPE], $usr);
        }
        return $msg;
    }

    /**
     * function to import the core user sandbox object values from a json string
     * e.g. the share and protection settings
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user $usr_req the user who has initiated the import mainly used to add the type to the database
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
        $usr_msg = parent::import_mapper($in_ex_json, $dto, $test_obj);

        if (key_exists(json_fields::TYPE_CODE_ID, $in_ex_json)) {
            $this->set_type($in_ex_json[json_fields::TYPE_CODE_ID], $usr_req);
        } elseif (key_exists(json_fields::TYPE_NAME, $in_ex_json)) {
            $this->set_type($in_ex_json[json_fields::TYPE_NAME], $usr_req);
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

        $vars[json_fields::TYPE] = $this->type_id();

        return $vars;
    }


    /*
     * set and get
     */

    /**
     * set the database id of the type
     *
     * @param int|null $type_id the database id of the type
     * @param user $usr_req the user who wants to change the type
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_type_id(?int $type_id, user $usr_req = new user()): user_message
    {
        $usr_msg = new user_message();
        if ($usr_req->can_set_type_id()) {
            $this->type_id = $type_id;
        } else {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                msg_id::VAR_USER_NAME => $usr_req->name(),
                msg_id::VAR_USER_PROFILE => $usr_req->profile_code_id(),
                msg_id::VAR_NAME => sql_db::FLD_TYPE_NAME,
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
            ]);
        }
        return $usr_msg;
    }

    /**
     * set the predefined type of this object by the given code id or name
     * must be overwritten by the child objects
     *
     * @param string $code_id_or_name the code id or the name of the type that should be added to this object
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type(string $code_id_or_name, user $usr_req = new user()): user_message
    {
        $usr_msg = new user_message();
        $usr_msg->add_id_with_vars(msg_id::MISSING_OVERWRITE, [
            msg_id::VAR_NAME => 'set_type in sandbox_typed',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg;
    }

    /**
     * set the type based on the given code id and type list
     *
     * @param string|null $code_id the code id that should be added to this view
     * @param type_list $typ_lst the parent object specific preloaded list of types
     * @param msg_id $msg_id the id of the message used to report a missing type
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type_by_code_id(
        ?string   $code_id,
        type_list $typ_lst,
        msg_id    $msg_id,
        user      $usr_req = new user()
    ): user_message
    {
        $usr_msg = new user_message();
        if ($code_id == null) {
            $this->type_id = null;
        } else {
            if ($typ_lst->has_code_id($code_id)) {
                $this->set_type_id($typ_lst->id($code_id), $usr_req);
            } else {
                $usr_msg->add_id_with_vars($msg_id, [
                    msg_id::VAR_NAME => $code_id
                ]);
                $this->type_id = null;
            }
        }
        return $usr_msg;
    }

    /**
     * set the type based on the given name and type list
     * should only be used if the code id is missing
     * TODO Prio 2 if the code id is given and the type name differs rename the type for the user
     *
     * @param string|null $name the code id that should be added to this view
     * @param type_list $typ_lst the parent object specific preloaded list of types
     * @param msg_id $msg_id the id of the message used to report a missing type
     * @param user $usr_req the user who wants to change the type
     * @return user_message a warning if the view type code id is not found
     */
    function set_type_by_name(
        ?string   $name,
        type_list $typ_lst,
        msg_id    $msg_id,
        user      $usr_req = new user()
    ): user_message
    {
        $usr_msg = new user_message();
        if ($name == null) {
            $this->type_id = null;
        } else {
            if ($typ_lst->has_name($name)) {
                $this->set_type_id($typ_lst->id_by_name($name), $usr_req);
            } else {
                $usr_msg->add_id_with_vars($msg_id, [
                    msg_id::VAR_NAME => $name
                ]);
                $this->type_id = null;
            }
        }
        return $usr_msg;
    }

    /**
     * @return int|null the database id of the type
     */
    function type_id(): ?int
    {
        return $this->type_id;
    }


    /*
     * preloaded
     */

    /**
     * the code id of the type for the export json
     * must be overwritten by the child objects
     * @return string|null with the code id of the type
     */
    function type_code_id(): string|null
    {
        $msg = 'the type_code_id() function is not overwritten by the ' . $this::class . ' object';
        return log_err($msg);
    }

    /**
     * dummy function that should be overwritten by the child object
     * @return string the name of the object type
     */
    function type_name(): string
    {
        $msg = 'the type_name() function is not overwritten by the ' . $this::class . ' object';
        return log_err($msg);
    }


    /*
     * cast
     */

    /**
     * @param object $api_obj frontend API objects that should be filled with unique object name
     */
    function fill_api_obj(object $api_obj): void
    {
        parent::fill_api_obj($api_obj);

        $api_obj->set_type_id($this->type_id());
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

        // TODO use the code id additional to the name where ever possible
        if ($this->type_code_id() <> '') {
            $vars[json_fields::TYPE_CODE_ID] = $this->type_code_id();
        }
        if ($this->type_name() <> '') {
            $vars[json_fields::TYPE_NAME] = $this->type_name();
        }
        return $vars;
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the named sandbox objects
     * @param sandbox_typed|CombineObject|db_object_seq_id $obj which might be different to this named sandbox
     * @return user_message the human-readable messages of the differences between the named sandbox objects
     */
    function diff_msg(sandbox_typed|CombineObject|db_object_seq_id $obj): user_message
    {
        $usr_msg = parent::diff_msg($obj);
        if ($this->type_id() != $obj->type_id()) {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::DIFF_TYPE, [
                msg_id::VAR_TYPE => $obj->type_name(),
                msg_id::VAR_TYPE_CHK => $this->type_name(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->name(),
            ]);
        }
        return $usr_msg;
    }

    /**
     * check if the typed object in the database needs to be updated
     *
     * @param sandbox_typed|CombineObject|db_object_seq_id $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(sandbox_typed|CombineObject|db_object_seq_id $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        if ($this->type_id != null) {
            if ($this->type_id != $db_obj->type_id) {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * modify
     */

    /**
     * fill this sandbox object based on the given object
     * if the given type is not set (null) the type is not removed
     * if the given type is zero (not null) the type is removed
     *
     * @param sandbox_typed|CombineObject|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(sandbox_typed|CombineObject|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->type_id() != null) {
            $this->set_type_id($obj->type_id(), $usr_req);
        }
        return $usr_msg;
    }


    /*
     * save - write to database
     */

    /**
     * save all updated source fields excluding the name, because already done when adding a source
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @param source $db_rec the database record before the saving
     * @param source $std_rec the database record defined as standard because it is used by most users
     * @return user_message the message that should be shown to the user in case something went wrong
     */
    function save_fields_typed(sql_db $db_con, sandbox_typed $db_rec, sandbox_typed $std_rec): user_message
    {
        $usr_msg = parent::save_fields_named($db_con, $db_rec, $std_rec);
        $usr_msg->add($this->save_field_type($db_con, $db_rec, $std_rec));
        return $usr_msg;
    }

}