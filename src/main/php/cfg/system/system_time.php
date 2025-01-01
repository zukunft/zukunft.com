<?php

/*

    cfg/system/system_time.php - object to log and optimize the execution times of the system
    --------------------------

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

namespace cfg\system;

include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_SYSTEM_PATH . 'system_time_type.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\db_object_seq_id;
use cfg\helper\type_object;

class system_time extends db_object_seq_id
{

    /*
     * database link
     */

    // database and export JSON object field names and comments
    const TBL_COMMENT = 'for system execution time tracking';
    const FLD_ID = 'system_time_id';
    const FLD_TIME_START_COM = 'start time of the monitoring period';
    const FLD_TIME_START = 'start_time';
    const FLD_TIME_END_COM = 'end time of the monitoring period';
    const FLD_TIME_END = 'end_time';
    const FLD_GROUP_COM = 'the area of the execution time e.g. db write';
    const FLD_GROUP = 'system_time_type_id';
    const FLD_MILLISECONDS_COM = 'the execution time in milliseconds';
    const FLD_MILLISECONDS = 'milliseconds';

    // all database field names excluding the id
    const FLD_NAMES = array(
        self::FLD_TIME_START,
        self::FLD_TIME_END,
        self::FLD_GROUP,
        self::FLD_MILLISECONDS
    );

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_TIME_START, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_START_COM],
        [self::FLD_TIME_END, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_END_COM],
        [self::FLD_GROUP, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, system_time_type::class, self::FLD_GROUP_COM],
        [self::FLD_MILLISECONDS, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_MILLISECONDS_COM],
    );

}