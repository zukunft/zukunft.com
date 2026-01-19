<?php

/*

    model/helper/db_object_seq_id_user.php - a base object for all user-specific database id objects
    --------------------------------------

    same as db_object_user but for database objects that have an auto sequence prime id
    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object

    The main sections of this object are
    - object vars:       the variables of this seq id object
    - construct and map: including the mapping of the db row to this seq id object
    - set and get:       to capsule the single variables from unexpected changes
    - info:              functions to make code easier to read
    - modify:            change potentially all variables of this seq id object with one function


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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\CombineObject;
use Zukunft\ZukunftCom\main\php\shared\library;

class db_object_seq_id_user extends db_object_seq_id
{

    /*
     * object vars
     */

    private user $usr; // the person for whom the object is loaded, so to say the viewer


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->set_user($usr);
    }

    /**
     * reset the vars of this element
     * @param bool $keep_user set to true to keep the original user for sandbox objects
     */
    function reset(bool $keep_user = false): void
    {
        if ($keep_user) {
            $usr = $this->usr;
        } else {
            $usr = new user();
        }
        parent::reset();
        $this->usr = $usr;
    }

    /**
     * clone this object and all linked objects
     * @return $this a complete clone including a clone of all child objects
     */
    function clone_all(): db_object_seq_id_user
    {
        $obj = parent::clone_all();
        $obj->usr = $this->usr->clone_all();
        return $obj;
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the object e.g. the word
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * return the user from a function to enable function overwrite in combine objects like phrase and term
     * @return user the person who wants to see a word, verb, triple, formula, view or result
     */
    function get_user(): user
    {
        return $this->usr;
    }

    /**
     * return the user id from a function to enable function overwrite in combine objects like phrase and term
     * @return int the id of the user or 0 if the user is not set
     */
    function get_user_id(): int
    {
        return $this->usr->id;
    }


    /*
     * info
     */

    /**
     * create human-readable messages of the differences between the db id objects
     * @param CombineObject|db_object_seq_id_user|db_object_seq_id $obj which might be different to this db id object
     * @return user_message the human-readable messages of the differences between the db id objects
     */
    function diff_msg(CombineObject|db_object_seq_id_user|db_object_seq_id $obj): user_message
    {
        $usr_msg = parent::diff_msg($obj);
        if ($this->get_user_id() != $obj->get_user_id()) {
            $lib = new library();
            $usr_msg->add_id_with_vars(msg_id::DIFF_USER, [
                msg_id::VAR_USER => $obj->get_user()->dsp_id(),
                msg_id::VAR_USER_CHK => $this->get_user()->dsp_id(),
                msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class),
                msg_id::VAR_NAME => $this->dsp_id(),
            ]);
        }
        return $usr_msg;
    }

    function has_id(): bool
    {
        if ($this->id() !== null and $this->id() !== 0) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * modify
     */

    /**
     * fill this db user object based on the given object
     * if the given user id is not set (null) the user id is set
     *
     * @param CombineObject|db_object_seq_id_user|db_object_seq_id $obj sandbox object with the values that should be updated e.g. based on the import
     * @param user $usr_req the user who has requested the fill
     * @return user_message a warning in case of a conflict e.g. due to a missing change time
     */
    function fill(CombineObject|db_object_seq_id_user|db_object_seq_id $obj, user $usr_req): user_message
    {
        $usr_msg = parent::fill($obj, $usr_req);
        if ($obj->get_user_id() != null) {
            $this->set_user($obj->get_user());
        }
        return $usr_msg;
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
        return array_merge(
            parent::db_fields_all(),
            [
                user_db::FLD_ID,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param db_object_seq_id_user|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        db_object_seq_id_user|db_object_seq_id $obj,
        user_message                           $usr_msg,
        sql_type_list                          $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $usr_msg, $sc_par_lst);
        if ($sc_par_lst->is_insert()) {
            if ($sc_par_lst->incl_log()) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . user_db::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . user_db::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            if ($obj->get_user_id() == 0) {
                $old_user_id = null;
            } else {
                $old_user_id = $obj->get_user_id();
            }
            $lst->add_field(
                user_db::FLD_ID,
                $this->get_user_id(),
                db_object_seq_id::FLD_ID_SQL_TYP,
                $old_user_id
            );
        }
        return $lst;
    }


    /*
     * debug
     */

    /**
     * @returns string best possible identification for this object mainly used for debugging
     */
    function dsp_id_user(): string
    {
        global $debug;
        $result = '';
        if ($debug > def::DEBUG_SHOW_USER or $debug == 0) {
            if ($this->get_user() != null) {
                $result .= ' for user ' . $this->get_user()->id . ' (' . $this->get_user()->name . ')';
            }
        }
        return $result;
    }

}
