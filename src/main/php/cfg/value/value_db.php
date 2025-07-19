<?php

/*

    model/value/value_db.php - the database const for value tables
    ------------------------

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

namespace cfg\value;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_type.php';
//include_once MODEL_REF_PATH . 'source_db.php';
//include_once MODEL_USER_PATH . 'user.php';
//include_once MODEL_LANGUAGE_PATH . 'language.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_multi.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_type;
use cfg\ref\source_db;
use cfg\user\user;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_multi;

class value_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'group_id';
    const FLD_VALUE = 'numeric_value';
    // TODO move the sandbox value object
    const FLD_VALUE_TEXT = 'text_value';
    const FLD_VALUE_TIME = 'time_value';
    const FLD_VALUE_GEO = 'geo_value';
    const FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const FLD_VALUE_TS_ID = 'value_time_series_id';
    const FLD_ALL_TIME_SERIES = array(
        [self::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM],
    );
    const FLD_ALL_TIME_SERIES_USER = array(
        [self::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM_USER],
    );

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array();
    const FLD_NAMES_STD = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
    );
    // fields that are not part of the standard result table, but that needs to be included for a correct union field match
    const FLD_NAMES_STD_DUMMY = array(
        user::FLD_ID,
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR_EX_STD = array(
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific datetime database field names
    const FLD_NAMES_DATE_USR_EX_STD = array(
        sandbox_multi::FLD_LAST_UPDATE
    );
    // list of the user specific database text field names for numeric tables and queries
    const FLD_NAMES_USR = array();
    // list of the user specific database text field names for text tables and queries
    const FLD_NAMES_USR_TEXT = array(
        self::FLD_VALUE_TEXT,
    );
    // list of the user specific database text field names for geo point tables and queries
    const FLD_NAMES_USR_GEO = array(
        self::FLD_VALUE_GEO,
    );
    // list of the user specific numeric database field names for numeric tables and queries
    const FLD_NAMES_NUM_USR = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific numeric database field names for text tables and queries
    const FLD_NAMES_NUM_USR_TEXT = array(
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific numeric database field names for timetables and queries
    const FLD_NAMES_NUM_USR_TIME = array(
        self::FLD_VALUE_TIME,
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of the user specific numeric database field names for geo point tables and queries
    const FLD_NAMES_NUM_USR_GEO = array(
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_VALUE,
        source_db::FLD_ID,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_CHANGE_USER,
        sandbox::FLD_SHARE
    );
    // list of fixed tables where a value might be stored
    const TBL_LIST = array(
        [sql_type::PRIME, sql_type::STANDARD],
        [sql_type::MOST, sql_type::STANDARD],
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );

}
