<?php

/*

    model/system/system_time.php - object to log and optimize the execution times of the system
    ----------------------------

    $sys_tim is the suggested var name

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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use DateTime;

class system_time extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names and comments
    const string TBL_COMMENT = 'for system execution time tracking';
    const string FLD_ID = 'system_time_id';
    const string FLD_TIME_START_COM = 'start time of the monitoring period';
    const string FLD_TIME_START = 'start_time';
    const string FLD_TIME_END_COM = 'end time of the monitoring period';
    const string FLD_TIME_END = 'end_time';
    const string FLD_GROUP_COM = 'the area of the execution time e.g. db write';
    const string FLD_GROUP = 'system_time_type_id';
    const string FLD_URL_COM = 'the request url as received from the frontend by a user action';
    const string FLD_URL = 'url';
    const string FLD_MILLISECONDS_COM = 'the execution time in milliseconds';
    const string FLD_MILLISECONDS = 'milliseconds';

    // all database field names excluding the id
    const array FLD_NAMES = array(
        self::FLD_TIME_START,
        self::FLD_TIME_END,
        self::FLD_GROUP,
        self::FLD_URL,
        self::FLD_MILLISECONDS
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [self::FLD_TIME_START, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_START_COM],
        [self::FLD_TIME_END, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_END_COM],
        [self::FLD_GROUP, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, system_time_type::class, self::FLD_GROUP_COM],
        [self::FLD_URL, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [self::FLD_MILLISECONDS, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_MILLISECONDS_COM],
    );


    /*
     * object vars
     */

    public ?DateTime $start_time = null; // when e.g. the api request has been received
    public ?DateTime $end_time = null;   // when e.g. the answer of the api request has been created
    public ?int $type_id = null;         // the area e.g. the whole api request or one step of it
    public ?string $url = null;          // the request url to find the slow call
    public ?int $milliseconds = null;    // the time used by the area


    /*
     * construct and map
     */

    /**
     * reset the vars of this execution time row
     * @param bool $keep_user not used, because a time row has no user
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->start_time = null;
        $this->end_time = null;
        $this->type_id = null;
        $this->url = null;
        $this->milliseconds = null;
    }


    /*
     * save
     */

    /**
     * add the execution time row without permission check and without change log,
     * because the time of every request is written, also the one of a user without login
     * @param user_message $msg to report why the row could not be written
     * @return bool true if the row has been added
     */
    function insert(user_message $msg): bool
    {
        global $db_con;
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $msg);
        $db_con->insert($qp, 'add system time', $msg);
        return $msg->is_ok();
    }


    /*
     * sql write fields
     */

    /**
     * @param sql_type_list $sc_par_lst not used, because the fields are the same for all writes
     * @return array list of all database field names that might be written in the order of the insert
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(parent::db_fields_all(), self::FLD_NAMES);
    }

    /**
     * get the database field names, values and types that differ from the given row
     *
     * @param system_time|db_object_seq_id $obj the row as it is in the database e.g. empty for an insert
     * @param user_message $msg not used, because a time row has no field that the user can set wrong
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list the fields that need to be written in the order of db_fields_all
     */
    function db_fields_changed(
        system_time|db_object_seq_id $obj,
        user_message                 $msg,
        sql_type_list                $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        $start = $this->start_time?->format(sql_db::DATE_FORMAT);
        $start_db = $obj->start_time?->format(sql_db::DATE_FORMAT);
        if ($start !== $start_db) {
            $lst->add_field(self::FLD_TIME_START, $start, sql_field_type::TIME, $start_db);
        }
        $end = $this->end_time?->format(sql_db::DATE_FORMAT);
        $end_db = $obj->end_time?->format(sql_db::DATE_FORMAT);
        if ($end !== $end_db) {
            $lst->add_field(self::FLD_TIME_END, $end, sql_field_type::TIME, $end_db);
        }
        if ($this->type_id !== $obj->type_id) {
            $lst->add_field(self::FLD_GROUP, $this->type_id, type_object::FLD_ID_SQL_TYP, $obj->type_id);
        }
        if ($this->url !== $obj->url) {
            $lst->add_field(self::FLD_URL, $this->url, sql_field_type::TEXT, $obj->url);
        }
        if ($this->milliseconds !== $obj->milliseconds) {
            $lst->add_field(self::FLD_MILLISECONDS, $this->milliseconds, sql_field_type::INT, $obj->milliseconds);
        }
        return $lst;
    }

}