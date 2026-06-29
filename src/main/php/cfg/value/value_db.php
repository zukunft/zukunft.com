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

namespace Zukunft\ZukunftCom\main\php\cfg\value;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'source_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'value_fields.php';
//include_once paths::MODEL_REF . 'source_db.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_USER . 'user_db.php';
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_multi;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\value_fields;

class value_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for values
    // the field names and their descriptions are defined in value_fields
    // *_SQL_TYP is the sql data type used for the field
    const array FLD_ALL_TIME_SERIES = array(
        [value_fields::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', value_fields::FLD_TS_ID_COM],
    );
    const array FLD_ALL_TIME_SERIES_USER = array(
        [value_fields::FLD_VALUE_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', value_fields::FLD_TS_ID_COM_USER],
    );

    // all database field names excluding the id and excluding the user-specific fields
    const array FLD_NAMES = array();
    const array FLD_NAMES_STD = array(
        value_fields::FLD_VALUE,
        source_fields::FLD_ID,
    );
    // fields that are not part of the standard result table, but that needs to be included for a correct union field match
    const array FLD_NAMES_STD_DUMMY = array(
        user_db::FLD_ID,
    );
    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR_EX_STD = array(
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );
    // list of the user-specific datetime database field names
    const array FLD_NAMES_DATE_USR_EX_STD = array(
        fields::FLD_LAST_UPDATE
    );
    // list of the user-specific database text field names for numeric tables and queries
    const array FLD_NAMES_USR = array();
    // list of the user-specific database text field names for text tables and queries
    const array FLD_NAMES_USR_TEXT = array(
        value_fields::FLD_VALUE_TEXT,
    );
    // list of the user-specific database text field names for geo point tables and queries
    const array FLD_NAMES_USR_GEO = array(
        value_fields::FLD_VALUE_GEO,
    );
    // list of the user-specific numeric database field names for numeric tables and queries
    const array FLD_NAMES_NUM_USR = array(
        value_fields::FLD_VALUE,
        source_fields::FLD_ID,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );
    // list of the user-specific numeric database field names for text tables and queries
    const array FLD_NAMES_NUM_USR_TEXT = array(
        source_fields::FLD_ID,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );
    // list of the user-specific numeric database field names for timetables and queries
    const array FLD_NAMES_NUM_USR_TIME = array(
        value_fields::FLD_VALUE_TIME,
        source_fields::FLD_ID,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );
    // list of the user-specific numeric database field names for geo point tables and queries
    const array FLD_NAMES_NUM_USR_GEO = array(
        source_fields::FLD_ID,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in value_fields::ALL_NAMES
    // list of field names that are only on the user sandbox row
    // e.g. the standard value does not need the share type, because it is by definition public (even if share types within a group of users needs to be defined, the value for the user group are also user sandbox table)
    const array FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_CHANGE_USER,
        fields::FLD_SHARE
    );
    // list of fixed tables where a value might be stored
    const array TBL_LIST = array(
        [sql_type::PRIME, sql_type::STANDARD],
        [sql_type::MOST, sql_type::STANDARD],
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );

}
