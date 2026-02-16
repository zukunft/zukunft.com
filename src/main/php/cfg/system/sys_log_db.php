<?php

/*

    model/system/sys_log_db.php - the database const for the system log table
    ---------------------------

    The main sections of this object are
    - db const:          const for the database link


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

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;

class sys_log_db
{

    /*
     * db const
     */

    // database fields only used for formula elements
    const string FLD_ID = 'sys_log_id';
    const string FLD_TIME = 'sys_log_time';
    const string FLD_TIME_COM = 'timestamp of the creation';
    const string FLD_USER_COM = 'the id of the user who has caused the log entry';
    const string FLD_FUNCTION_COM = 'the function or function group for the entry e.g. db_write to measure the db write times';
    const string FLD_TRACE = 'sys_log_trace';
    const string FLD_TRACE_COM = 'the generated code trace to local the path to the error cause';
    const string FLD_LEVEL_COM = 'the level e.g. debug, info, warning, error or fatal';
    const string FLD_TIME_UPDATE = 'sys_log_update_time';
    const string FLD_TIME_UPDATE_COM = 'timestamp of the last update of this system error';
    const string FLD_TEXT = 'sys_log_text';
    const string FLD_TEXT_COM = 'the short text of the log entry to identify the error and to reduce the number of double entries';
    const string FLD_DESCRIPTION = 'sys_log_description';
    const string FLD_DESCRIPTION_COM = 'the long description with all details of the log entry to solve ti issue';
    const sql_field_type FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
    const string FLD_SOLVER = 'solver_id';
    const string FLD_SOLVER_COM = 'user id of the user that is trying to solve the problem';

    // join database and export JSON object field names
    const string FLD_TIME_JSON = 'time';
    const string FLD_TIMESTAMP_JSON = 'timestamp';
    const string FLD_SOLVER_NAME = 'solver_name';

    // all database field names excluding the id
    // the extra user field is needed because it is common to check the log entries of others users e.g. for admin users
    const array FLD_NAMES = array(
        self::FLD_TIME,
        user_db::FLD_ID,
        sys_log_function::FLD_ID,
        self::FLD_TRACE,
        sys_log_level::FLD_ID,
        self::FLD_TIME_UPDATE,
        self::FLD_TEXT,
        self::FLD_DESCRIPTION,
        self::FLD_SOLVER,
        sys_log_status::FLD_ID
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [self::FLD_TIME, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_COM],
        [user_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [sys_log_function::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, sys_log_function::class, self::FLD_FUNCTION_COM],
        [self::FLD_TRACE, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TRACE_COM],
        [sys_log_level::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, sys_log_level::class, self::FLD_LEVEL_COM],
        [self::FLD_TIME_UPDATE, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_UPDATE_COM],
        [self::FLD_TEXT, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_TEXT_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_SOLVER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, self::FLD_SOLVER_COM, user_db::FLD_ID],
        [sys_log_status::FLD_ID, sql_field_type::INT_SMALL, sql_field_default::ONE, sql::INDEX, sys_log_status::class, ''],
    );

}
