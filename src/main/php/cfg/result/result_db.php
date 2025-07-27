<?php

/*

    model/word/triple_db.php - the database const for triple tables
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

namespace cfg\result;

use cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_multi.php';
include_once paths::MODEL_SANDBOX . 'sandbox_value.php';
include_once paths::MODEL_USER . 'user.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_type;
use cfg\formula\formula;
use cfg\formula\formula_db;
use cfg\group\group;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_multi;
use cfg\sandbox\sandbox_value;
use cfg\user\user;

class result_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'group_id';
    const FLD_SOURCE = 'source_';
    // TODO replace with result_db::FLD_SOURCE . group::FLD_ID
    const FLD_SOURCE_GRP = 'source_group_id';
    // TODO replace with group::FLD_ID
    const FLD_GRP = 'group_id';
    const FLD_TS_ID_COM = 'the id of the time series as a 64 bit integer value because the number of time series is not expected to be too high';
    const FLD_TS_ID_COM_USER = 'the 64 bit integer which is unique for the standard and the user series';
    const FLD_RESULT_TS_ID = 'result_time_series_id';
    // TODO maybe use the dirty flag for faster dirty selection
    //const FLD_DIRTY = 'dirty';
    const FLD_ALL_TIME_SERIES = array(
        [self::FLD_RESULT_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM],
    );
    const FLD_ALL_TIME_SERIES_USER = array(
        [self::FLD_RESULT_TS_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TS_ID_COM_USER],
    );

    // all database field names excluding the id and excluding the user specific fields
    const FLD_NAMES = array(
        formula_db::FLD_ID,
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        sandbox_multi::FLD_VALUE,
        sandbox_multi::FLD_LAST_UPDATE
    );
    const FLD_NAMES_ALL = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula_db::FLD_ID,
        sandbox_multi::FLD_VALUE,
    );
    const FLD_NAMES_NON_STD = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula_db::FLD_ID,
    );
    const FLD_NAMES_STD = array(
        self::FLD_SOURCE_GRP,
        formula_db::FLD_ID,
        sandbox_multi::FLD_VALUE,
    );
    // fields that are not part of the standard result table, but that needs to be included for a correct union field match
    const FLD_NAMES_STD_DUMMY = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
    );
    const FLD_NAMES_STD_NON_DUMMY = array(
        formula_db::FLD_ID,
    );
    const FLD_NAMES_DUMMY = array(
        user::FLD_ID,
        self::FLD_SOURCE_GRP,
        formula_db::FLD_ID,
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
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        sandbox_multi::FLD_VALUE,
        sandbox_multi::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_PROTECT
    );
    // list of field names that are only on the user sandbox row
    // e.g. the standard result does not need the share type, because it is by definition public
    // (even if share types within a group of users needs to be defined,
    // the value for the user group are also user sandbox table)
    const FLD_NAMES_USR_ONLY = array(
        sandbox::FLD_CHANGE_USER,
        sandbox::FLD_SHARE
    );

    // database table extensions used
    // TODO add a similar list to the value class
    const TBL_EXT_LST = array(
        sql_type::PRIME,
        sql_type::MAIN,
        sql_type::MOST,
        sql_type::BIG
    );
    // list of fixed tables where a value might be stored
    const TBL_LIST = array(
        [sql_type::PRIME, sql_type::STANDARD],
        [sql_type::MAIN, sql_type::STANDARD],
        [sql_type::MOST, sql_type::STANDARD],
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::MAIN],
        [sql_type::BIG]
    );
    // list of fixed tables without the pure key value tables
    const TBL_LIST_EX_STD = array(
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::MAIN],
        [sql_type::BIG]
    );

    const FLD_KEY_PRIME = array(
        [formula_db::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_MAIN_STD = array(
        [formula_db::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_MAIN = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '8', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is part of the prime key for a'],
    );
    const FLD_KEY_PRIME_USER = array(
        [formula_db::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'formula id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_KEY_MAIN_USER = array(
        [sandbox_value::FLD_ID_PREFIX . '1', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '2', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '3', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '4', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '5', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '6', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '7', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
        [sandbox_value::FLD_ID_PREFIX . '8', sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ZERO, sql::INDEX, '', 'phrase id that is with the user id part of the prime key for a'],
    );
    const FLD_ALL_CHANGED = array(
        [sandbox_multi::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', 'timestamp of the last update used also to trigger updates of depending values for fast recalculation for fast recalculation'],
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, formula::class, 'the id of the formula which has been used to calculate this result'],
    );
    const FLD_ALL_SOURCE = array();
    const FLD_ALL_SOURCE_GROUP = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::REF_512, sql_field_default::NULL, sql::INDEX, '', '512-bit reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_SOURCE_GROUP_PRIME = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', '64-bit reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_SOURCE_GROUP_BIG = array(
        [self::FLD_SOURCE . group::FLD_ID, sql_field_type::TEXT, sql_field_default::NULL, sql::INDEX, '', 'text reference to the sorted phrase list used to calculate this result'],
    );
    const FLD_ALL_OWNER = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, user::class, 'the id of the user who has requested the calculation'],
    );
    const FLD_ALL_CHANGER = array(
        [user::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, 'the id of the user who has requested the change of the '],
    );

}
