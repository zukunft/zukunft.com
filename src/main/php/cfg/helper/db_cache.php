<?php

/*

    model/helper/db_cache.php - the database based cached e.g. for faster system configuration loading
    -------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: set the vars of this word object to the initial value or based on a db row, api or import object
    - load:              database access object (DAO) functions
    - save:              manage to update the database
    - save helper:       helpers for updating the database
    - del:               manage to remove from the database
    - sql write fields:  field list for writing to the database
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

use DateTime;
use DateTimeInterface;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_HELPER . 'db_cache_db.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'db_cache_statuum.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_statuum;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class db_cache extends db_object_seq_id_user
{

    // object specific database object field names and comments
    const string TBL_COMMENT = 'precollected data for faster response times in the json format';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = db_cache_db::FLD_ID;
    const array FLD_NAMES = db_cache_db::FLD_NAMES;
    const array FLD_LST_ALL = db_cache_db::FLD_LST_ALL;


    /*
     * debug
     */

    function dsp_id(): string
    {
        $lib = new library();
        $class = $lib->class_to_name($this::class);
        return $class . ' with ' . $this->id_field() . '=' . $this->id();
    }


    /*
     * object vars
     */

    // database fields
    public ?int $type_id = null;           // id of the db_cache type e.g. "system config", "frontend config", ... because getting the type is fast from the preloaded type list
    public array|string|null $data = null; // the object data as an json array
    public ?user $usr = null;              // the complete user object or null for fast handling
    public ?int $status_id = null;         // id of the db_cache status e.g. "clean", "dirty", ...
    public ?DateTime $last_update = null;  // time when the db_cache has last been refreshed


    /*
     * construct and map
     */

    /**
     * always set the status
     */
    function __construct(?user $usr)
    {
        parent::__construct($usr);
        $this->status_id = db_cache_statuum::CLEAN_ID;
    }

    /**
     * clear all cache object values e.g. to detect the changed fields
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->type_id = null;
        $this->data = null;
        $this->usr = null;
        $this->status_id = null;
        $this->last_update = new DateTime();
    }

    /**
     * map the database fields to one change log entry to this log object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a db_cache is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $lib = new library();
        $result = parent::row_mapper($db_row, db_cache_db::FLD_ID);
        if ($result) {
            if (array_key_exists(db_cache_db::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[db_cache_db::FLD_TYPE];
            }
            if (array_key_exists(db_cache_db::FLD_DATA, $db_row)) {
                $this->data = json_decode($db_row[db_cache_db::FLD_DATA], true);
            }
            if (array_key_exists(user_db::FLD_ID, $db_row)) {
                if ($this->usr === null) {
                    $this->usr = new user();
                }
                $this->usr->id = $db_row[user_db::FLD_ID];
                // TODO Prio 2 load user from cache
            }
            if (array_key_exists(db_cache_db::FLD_STATUS, $db_row)) {
                $this->status_id = $db_row[db_cache_db::FLD_STATUS];
            }
            if (array_key_exists(db_cache_db::FLD_LAST_UPDATE, $db_row)) {
                $this->last_update = $lib->get_datetime($db_row[db_cache_db::FLD_LAST_UPDATE], $this->dsp_id());
            }
            log_debug('Batch db_cache ' . $this->id() . ' loaded');
        }
        return $result;
    }


    /*
     * set and get
     */

    function set_type(string $code_id): void
    {
        global $sys;
        $lst = $sys->typ_lst->dbc_typ;
        $this->type_id = $lst->get_by_code_id($code_id);
    }


    /*
     * load
     */

    /**
     * load the cache of the given type
     *
     * @param string $typ_code_id the id of the user sandbox object
     * @return int the id of the data cache entry
     */
    function load_by_type(string $typ_code_id): int
    {
        global $sys;
        $typ_lst = $sys->typ_lst->cac_typ;
        $id = $typ_lst->id($typ_code_id);
        return $this->load_by_type_id($id);
    }

    /**
     * load the cache of the given type
     *
     * @param int $id the id of the user sandbox object
     * @return int the id of the data cache entry
     */
    function load_by_type_id(int $id): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_type_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * create an SQL statement to retrieve a batch db_cache by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the cache type
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_type_id(sql_creator $sc, int $id): sql_par
    {
        $qp = $this->load_sql($sc, sql_db::FLD_ID);
        $sc->add_where(db_cache_db::FLD_TYPE, $id);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the common part of an SQL statement to retrieve the parameters of a batch db_cache from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name);
        $sc->set_class(db_cache::class);

        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(db_cache_db::FLD_NAMES);

        return $qp;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = [];

        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::TYPE] = $this->type_id;
        $vars[json_fields::CACHE_DATA] = $this->data;
        $vars[json_fields::USER_NAME] = $this->get_user()->name();
        $vars[json_fields::STATUS] = $this->status_id;
        $vars[json_fields::LAST_UPDATE] = $this->last_update?->format(DateTimeInterface::ATOM);

        return $vars;
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
                db_cache_db::FLD_TYPE,
                db_cache_db::FLD_DATA,
                user_db::FLD_ID,
                db_cache_db::FLD_STATUS,
                db_cache_db::FLD_LAST_UPDATE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param db_cache|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        db_cache|db_object_seq_id $obj,
        user_message              $msg,
        sql_type_list             $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id !== $this->type_id) {
            if ($this->type_id <= 0 or $this->type_id == null) {
                $msg->add(msg_id::CACHE_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->type_id,
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                db_cache_db::FLD_TYPE,
                type_object::FLD_NAME,
                $this->type_id,
                $obj->type_id,
                $sys->typ_lst->cac_typ);
        }
        if ($obj->data != $this->data) {
            if (is_string($this->data)) {
                $lst->add_field(
                    db_cache_db::FLD_DATA,
                    $this->data,
                    sql_field_type::TEXT,
                    $obj->data
                );
            } else {
                $lst->add_field(
                    db_cache_db::FLD_DATA,
                    json_encode($this->data),
                    sql_field_type::TEXT,
                    json_encode($obj->data)
                );
            }
        }
        if ($obj->status_id !== $this->status_id) {
            if ($this->status_id <= 0 or $this->status_id == null) {
                $msg->add(msg_id::CACHE_STATUS_MISSING, [
                    msg_id::VAR_TYPE => $this->status_id,
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                db_cache_db::FLD_STATUS,
                db_cache_status::FLD_NAME,
                $this->status_id,
                $obj->status_id,
                $sys->typ_lst->cac_sta);
        }
        if ($obj->usr !== $this->usr) {
            $lst->add_field(
                user_db::FLD_ID,
                $this->usr?->name,
                sql_field_type::INT,
                $obj->usr?->name,
                user_db::FLD_NAME,
                $this->usr?->id,
                $obj->usr?->id,
                sql_field_type::INT
            );
        }
        if ($obj->last_update != $this->last_update) {
            $lst->add_field(
                db_cache_db::FLD_LAST_UPDATE,
                $this->last_update?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->last_update?->format(sql_db::DATE_FORMAT)
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return db_cache_db::FLD_TYPE;
    }


    /*
     * db helper
     */

    /**
     * check if the database cache can be added to the database
     * e.g. reject if a reserved name is used and the user is not a system test user or an admin user
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check(user_message $msg): bool
    {
        // the cache type and status must be valid
        // TODO Prio 3 add other checks e.g. the update time
        if ($this->type_id <= 0) {
            $msg->add_err(msg_id::CACHE_TYPE_INVALID, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        } elseif ($this->status_id <= 0) {
            $msg->add_err(msg_id::CACHE_STATUS_INVALID, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        }
        return $msg->is_ok();
    }


    /*
     * debug
     */

    function name(): string|null
    {
        global $sys;

        $lst = $sys->typ_lst->cac_typ;
        return $lst->name_or_null($this->type_id);
    }

}
