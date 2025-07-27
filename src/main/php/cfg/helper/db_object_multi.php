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

namespace cfg\helper;

use cfg\const\paths;

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

use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\group\group_id;
use cfg\user\user;
use cfg\user\user_message;
use controller\api_message;
use shared\enum\messages as msg_id;
use shared\types\api_type_list;
use shared\json_fields;

class db_object_multi extends db_object_key
{

    /*
     * object vars
     */

    // database fields that are used in all model objects
    // the database id is the unique prime key
    // TODO actually not needed on this level, because the id may be generated and remembered in a linked object e.g. the group
    protected int|string $id;


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
                            $this->set_id((int)$db_row[$id_fld]);
                        } else {
                            $this->set_id($db_row[$id_fld]);
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
     * set and get
     */

    /**
     * set the unique database id of a database object
     * @param int|string $id used in the row mapper and to set a dummy database id for unit tests
     */
    function set_id(int|string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|string the database id which is not 0 if the object has been saved
     * the internal null value is used to detect if database saving has been tried
     */
    function id(): int|string
    {
        return $this->id;
    }


    /*
     * load
     */

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|string $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
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
    function import_db_obj(db_object_multi $db_obj, object $test_obj = null): user_message
    {
        $usr_msg = new user_message();
        // add a dummy id for unit testing
        if ($test_obj) {
            $db_obj->set_id($test_obj->seq_id());
        }
        return $usr_msg;
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
        $usr_msg = new user_message();
        if ($obj->id() !== 0 and $obj->id() !== '' ) {
            if ($this->id() === 0 or $this->id() === '') {
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
        $usr_msg = new user_message();
        if ($this->id() != $obj->id()) {
            $usr_msg->add_id_with_vars(msg_id::DIFF_ID, [
                msg_id::VAR_ID => $obj->id(),
                msg_id::VAR_ID_CHK => $this->id(),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $usr_msg;
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
        return 'ERROR: name function not overwritten by child';
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
                $fld_dsp = ' (' . implode(', ', $id_fields);
                $fld_dsp .= ' = ' . $this->id() . ')';
                return $fld_dsp;
            } else {
                return ' (' . $id_fields . ' ' . $this->id() . ')';
            }
        } else {
            return ' (' . $this->id_field() . ' no set)';
        }
    }

}
