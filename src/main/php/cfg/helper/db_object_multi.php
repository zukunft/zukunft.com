<?php

/*

    model/helper/db_object_multi.php - a base object for all db objects which use more than one table to store the data
    --------------------------------

    like db_object_seq_id but not using an auto sequence as db index


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_key.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
//include_once paths::MODEL_GROUP . 'group_id.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\group\group_id;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;

class db_object_multi extends db_object_key
{

    /*
     * object vars
     */

    // database fields that are used in all model objects
    // the database id is the unique prime key
    // TODO actually not needed on this level, because the id may be generated and remembered in a linked object e.g. the group
    public int|string $id {
        // get @return int the database id which is not 0 if the object has been saved
        // the internal null value is used to detect if database saving has been tried
        get {
            return $this->id;
        }
        // set the unique database id of a database object
        // @param int $id used in the row mapper and to set a dummy database id for unit tests
        set {
            $this->id = $value;
            $this->set_modified();
        }
    }


    /*
     * construct and map
     */

    /**
     * reset the id to null to indicate that the database object has not been loaded
     */
    function __construct()
    {
        parent::__construct();
        $this->id = 0;
    }

    /**
     * map the database fields to the object fields
     * to be extended by the child functions
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $ext the table type e.g. to indicate if the id is int
     * @param string $id_fld the name of the id field as set in the child class
     * @param bool $one_id_fld false if the unique database id is based on more than one field and due to that the database id should not be used for the object id
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper_multi(?array $db_row, string $ext, string $id_fld = '', bool $one_id_fld = true): bool
    {
        $result = false;
        if ($db_row != null) {
            if ($one_id_fld) {
                if (array_key_exists($id_fld, $db_row)) {
                    if ($db_row[$id_fld] != 0 or $db_row[$id_fld] != '') {
                        if (substr($ext, 0, 2) == group_id::TBL_EXT_PHRASE_ID) {
                            $this->id = (int)$db_row[$id_fld];
                        } else {
                            $this->id = $db_row[$id_fld];
                        }
                        $result = true;
                    }
                }
            } else {
                $result = true;
            }
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|string $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id(
        sql_creator $sc,
        int|string  $id
    ): sql_par
    {
        return parent::load_sql_by_id_str($sc, $id);
    }

    /**
     * load one database row e.g. word, triple, value, formula, result, view, component or log entry from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int|string the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int|string
    {
        parent::load_without_id_return($qp);
        return $this->id();
    }


    /*
     * api
     */

    /**
     * create the api json message string of this database object based on more than one table for the frontend
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
     * general part to import a database multi table object from a JSON array object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $msg,
        ?data_object $dto = null
    ): bool
    {
        $msg->start_time = microtime(true);
        return $msg->is_ok();
    }

    /*
     * set and get
     */

    function id(): string|int
    {
        return $this->id;
    }


    /*
     * info
     */

    /**
     * Create an object where only the vars are set
     * where the var of this object differs from the var of the given object.
     * Used to get the database fields that need to be updated in the user sandbox row
     * E.g. if the user has renamed a word and changes the name now back to the standard name,
     *      the name of the user sandbox row is supposed to be null
     * $this is usually the target user object
     * $obj is the norm object as saved in the database
     * $result is the user object that should be used to write the user sandbox db row
     *
     *
     * @param db_object_multi $std_obj the norm object as saved in the database
     * @param db_object_multi $result empty clone of the target user object
     * @return db_object_multi the object where only the vars are set that are changed compared to the given $obj
     */
    function delta(
        db_object_multi $std_obj,
        db_object_multi $result
    ): db_object_multi
    {
        // TODO move to the calling function
        // $result = $this->clone_reset(true);
        // the database id mus always be identical to the original db row
        $result->id = $std_obj->id();
        return $result;
    }

    /*
     * modify
     */

    /**
     * fill this seq id object based on the given object
     * if the given id is zero or an empty string the id is never overwritten
     * if the given id is valid the id of this object is set if not yet done
     * similar to db_object_seq_id->fill
     *
     * @param db_object_multi $obj object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(db_object_multi $obj, user $usr_req): user_message
    {
        $msg = new user_message();
        if ($obj->id() !== 0 and $obj->id() !== '' ) {
            if ($this->id() === 0 or $this->id() === '') {
                $this->set_id($obj->id());
            } elseif ($obj->id() != $this->id()) {
                $msg->add(msg_id::CONFLICT_DB_ID, [msg_id::VAR_ID => $this->dsp_id()]);
            }
        }
        return $msg;
    }


    /*
     * info
     */

    /**
     * @return bool true if the object has a valid database id
     */
    function isset(): bool
    {
        if ($this->id() == null) {
            return false;
        } else {
            if (is_string($this->id())) {
                if ($this->id() != '') {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($this->id() != 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * create human-readable messages of the differences between the db id objects
     * @param db_object_multi $obj which might be different to this db id object
     * @return user_message the human-readable messages of the differences between the db id objects
     */
    function diff_msg(db_object_multi $obj): user_message
    {
        $msg = new user_message();
        $lib = new library();
        if ($this->id() != $obj->id()) {
            $msg->add(msg_id::DIFF_ID, [
                msg_id::VAR_ID => $obj->id(),
                msg_id::VAR_ID_CHK => $this->id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $msg;
    }


    /*
     * dummy functions that should always be overwritten by the child
     */

    /**
     * get the name of the database object (only used by named objects)
     *
     * @return string the name from the object e.g. word using the same function as the phrase and term
     */
    function name(): string
    {
        $msg = 'ERROR: name function not overwritten by child';
        log_err($msg);
        return $msg;
    }

    /**
     * load a row from the database selected by id
     * @param int|string $id the id of the word, triple, formula, verb, view or view component
     * @return int|string the id of the object found and zero if nothing is found
     */
    function load_by_id(
        int|string $id
    ): int|string
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * load a row by id like load_by_id, but also populate the "most often used related
     * objects" view-models that the page-title renderer expects (e.g. the related phrases
     * of a value shown in its title); the base implementation is a no-op beyond load_by_id
     * so a multi-id object without such a view-model can be loaded polymorphically next to
     * the db_object_seq_id types (mirrors db_object_seq_id::load_by_id_with_related, used
     * e.g. by test_base::assert_view)
     *
     * @param int|string $id the id of the row to load
     * @return int|string the id of the object found and zero if nothing is found
     */
    function load_by_id_with_related(int|string $id): int|string
    {
        return $this->load_by_id($id);
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
            $id_fields = $this->id_field();
            if (is_array($id_fields)) {
                $fld_ui = ' (' . implode(', ', $id_fields);
                $fld_ui .= ' = ' . $this->id() . ')';
                return $fld_ui;
            } else {
                return ' (' . $id_fields . ' ' . $this->id() . ')';
            }
        } else {
            return ' (' . $this->id_field() . ' no set)';
        }
    }

}
