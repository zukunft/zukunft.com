<?php

/*

    model/helper/db_object.php - a base object for all model database objects contains the table and index creation
    --------------------------

    The main sections of this object are
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
//include_once paths::DB . 'sql_db.php';
//include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
//include_once paths::MODEL_GROUP . 'group.php';
//include_once paths::MODEL_GROUP . 'group_db.php';
//include_once paths::MODEL_RESULT . 'result.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_message.php';
//include_once paths::MODEL_VALUE . 'value.php';
//include_once paths::MODEL_VALUE . 'value_base.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_db;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\library;

class db_object extends IdObject
{

    // dummy const to be overwritten by the child objects
    // description of the table for the sql table creation
    const string TBL_COMMENT = '';
    // list of the table fields for the standard read query
    const array FLD_NAMES = array();

    // field lists for the table creation overwritten by the child object or grand child for extra fields
    const array FLD_LST_ALL = array();
    const array FLD_LST_NAME = array();
    const array FLD_LST_EXTRA = array();
    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array();
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array();
    // fields that CAN be changed by the user with the parameters for the table creation
    const array FLD_LST_USER_CAN_CHANGE = array();
    // fields that CANNOT be changed by the user with the parameters for the table creation
    const array FLD_LST_NON_CHANGEABLE = array();


    /*
     * sql create
     */

    /**
     * the sql statement to create the table for this (or a child) object
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @param string $tbl_comment if given the comment that should be added to the sql create table statement
     * @return string the sql statement to create the table
     */
    function sql_table_create(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        array         $fields = [],
        string        $tbl_comment = ''
    ): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $sc_par_lst);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($sc_par_lst);
        }
        if ($tbl_comment == '') {
            $tbl_comment = $this::TBL_COMMENT;
        }
        return $sc->table_create($fields, '', $tbl_comment, $this::class, $sc_par_lst->is_usr_tbl());
    }

    /**
     * the name of the sql table for this (or a child) object
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return string the sql statement to create the table
     */
    function sql_truncate_create(
        sql_creator   $sc,
        sql_type_list $sc_par_lst
    ): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $sc_par_lst);
        }
        return sql::TRUNCATE . ' ' . $sc->get_table() . ' ' . sql::CASCADE . '; ';
    }

    /**
     * the sql statement to create the indices for this (or a child) object
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @return string the sql statement to create the table
     */
    function sql_index_create(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        array         $fields = []
    ): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $sc_par_lst);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($sc_par_lst);
        }
        return $sc->index_create($fields);
    }

    /**
     * the sql statement to create the foreign keys for this (or a child) object
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @param array $fields array with all fields and all parameter for the table creation in a two-dimensional array
     * @return string the sql statement to create the table
     */
    function sql_foreign_key_create(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        array         $fields = []
    ): string
    {
        if ($sc->get_table() == '') {
            $sc->set_class($this::class, $sc_par_lst);
        }
        if ($fields == []) {
            $fields = $this->sql_all_field_par($sc_par_lst);
        }
        return $sc->foreign_key_create($fields);
    }

    /**
     * create a list of fields with the parameters for this object
     *
     * @param sql_type_list $sc_par_lst of parameters for the sql creation
     * @return array[] with the parameters of the table fields
     */
    protected function sql_all_field_par(sql_type_list $sc_par_lst): array
    {
        $usr_tbl = $sc_par_lst->is_usr_tbl();
        $use_sandbox = $sc_par_lst->use_sandbox_fields();
        $fields = [];
        if (!$usr_tbl) {
            $fields = array_merge($fields, $this::FLD_LST_NON_CHANGEABLE);
        }
        if (!$usr_tbl) {
            if ($use_sandbox) {
                $fields = array_merge($fields, sandbox::FLD_ALL_OWNER);
                $fields = array_merge($fields, $this::FLD_LST_MUST_BE_IN_STD);
            } else {
                $fields = array_merge($fields, $this::FLD_LST_NAME, $this::FLD_LST_ALL);
                $fields = array_merge($fields, $this::FLD_LST_EXTRA);
            }
        } else {
            $fields = array_merge($fields, sandbox::FLD_ALL_CHANGER);
            $fields = array_merge($fields, $this::FLD_LST_MUST_BUT_USER_CAN_CHANGE);
        }
        $fields = array_merge($fields, $this::FLD_LST_USER_CAN_CHANGE);
        if ($use_sandbox) {
            $fields = array_merge($fields, sandbox::FLD_LST_ALL);
        }
        return $fields;
    }


    /*
     * load
     */

    /**
     * parent function to create the common part of an SQL statement for group, value and result tables
     * child object sets the table and fields in the db sql builder
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension e.g. to differentiate queries based on 1,2, or more phrases
     * @param string $id_ext the query name extension that indicated how many id fields are used e.g. "_p1"
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_multi(
        sql_creator   $sc,
        string        $query_name,
        string        $class,
        sql_type_list $sc_par_lst,
        string        $ext = '',
        string        $id_ext = ''
    ): sql_par
    {
        $lib = new library();
        $tbl_name = $lib->class_to_name($class);
        $qp = new sql_par($tbl_name, $sc_par_lst, $ext, $id_ext);
        $qp->name .= $query_name;
        $sc->set_class($class, $sc_par_lst, $sc_par_lst->ext_ex_user());
        $sc->set_name($qp->name);
        $sc->set_fields($this::FLD_NAMES);
        // TODO generalise this exception
        if ($class == group::class
            and $sc_par_lst->is_prime()
            and $query_name == 'name'
            and !$sc->is_MySQL()) {
            $sc->set_id_field(group_db::FLD_ID . '::text');
        }

        return $qp;
    }

    /**
     * parent function to create the common part of an SQL statement
     * child object sets the table and fields in the db sql builder
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        return $this->load_sql_multi($sc, $query_name, $this::class, new sql_type_list([sql_type::MOST]));
    }

    /**
     * create an SQL statement to retrieve a user sandbox object by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|string $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_id_str(sql_creator $sc, int|string $id): sql_par
    {
        $class = $this::class;
        if ($class == group::class
            or $class == value::class
            or $class == result::class) {
            $grp = new group(new user());
            $grp->set_id($id);
            $sc_par_lst = new sql_type_list([$grp->table_type()]);
            $ext = $grp->table_extension();
            $qp = $this->load_sql_multi($sc, sql_db::FLD_ID, $class, $sc_par_lst, $ext);
        } else {
            $qp = $this->load_sql($sc, sql_db::FLD_ID);
        }

        $sc->add_where($this->id_field(), $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * load one database row e.g. group (where the id might be a string) from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return bool false if no database row has been found
     *                    which means that no user has changed the standard group settings
     */
    protected function load_without_id_return(sql_par $qp): bool
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        if ($db_row != null) {
            return $this->row_mapper($db_row);
        } else {
            return false;
        }
    }


    /*
     * info
     */

    /**
     * name of the prime index field of the table
     * function that can be overwritten by the child object
     * e.g. if the object name does not match the generated id field name
     * e.g. to group_id for values and results
     * @return string|array the field name(s) of the prime database index of the object
     */
    function id_field(): string|array
    {
        $lib = new library();
        return $lib->class_to_name($this::class) . sql_db::FLD_EXT_ID;
    }

    /**
     * name of log entry used shown to the user which entry has been deleted
     * e.g. for the user it can be the name, the ip-address, or as fallback the database id
     * @return string the field name(s) of the prime database index of the object
     */
    function log_name_field(): string
    {
        return '';
    }

    /**
     * TODO Prio 1 make sure that this function is always called before tying to delete a database row
     * prevent critical rows such as user from completely being deleted
     * but allow the system to remove test users
     * and
     * prevent critical rows such as language from completely being deleted
     * but allow the admin users to remove user mistakes
     *
     * @param user_message $msg
     * @return bool
     */
    protected function can_delete(user_message $msg): bool
    {
        $can_del = false;

        if (in_array($this::class, def::NO_DELETE_CLASSES)) {
            if ($msg->usr === null) {
                log_err('user not set in user_message', 'can_delete');
                $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                if ($msg->usr->is_system()) {
                    $can_del = true;
                }
            }
        } elseif (in_array($this::class, def::ONLY_ADMIN_CAN_DELETE_CLASSES)) {
            if ($msg->usr === null) {
                log_err('user not set in user_message', 'can_delete');
                $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                if ($msg->usr->is_admin()) {
                    $can_del = true;
                }
            }
        } else {
            $can_del = true;
        }

        return $can_del;
    }

    /**
     * TODO Prio 1 make sure that this function is always called before tying to update a database row
     * prevent critical rows such as log rows from completely being deleted
     * but allow the system to remove test users
     * and
     * prevent critical rows such as language from completely being deleted
     * but allow the admin users to remove user mistakes
     *
     * @param user_message $msg
     * @return bool
     */
    protected function can_update(user_message $msg): bool
    {
        $can_upd = false;


        if (in_array($this::class, def::NO_UPDATE_CLASSES)) {
            if ($msg->usr === null) {
                log_err('user not set in user_message', 'can_update');
                $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                if ($msg->usr->is_system()) {
                    $can_upd = true;
                }
            }
        } elseif (in_array($this::class, def::ONLY_ADMIN_CAN_UPDATE_CLASSES)) {
            if ($msg->usr === null) {
                log_err('user not set in user_message', 'can_update');
                $msg->add(msg_id::USER_MISSING, [msg_id::VAR_NAME => $this->dsp_id()]);
            } else {
                if ($msg->usr->is_admin()) {
                    $can_upd = true;
                }
            }
        } else {
            $can_upd = true;
        }

        return $can_upd;
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        return $class . ' with ' . $this->id_field() . '=' . $this->id();
    }

}
