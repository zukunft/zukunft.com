<?php

/*

    model/helper/db_cache_db.php - the database const for the database cache tables
    ----------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;

class db_cache_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for the database cache
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'db_cache_id';
    const string FLD_TYPE = 'type_id';
    const string FLD_TYPE_COM = 'to separate the system, user and frontend configuration';
    const string FLD_DATA = 'data';
    const string FLD_DATA_COM = 'the cached data as text';
    const string FLD_USER_COM = 'to link coded functionality to words e.g. to exclude measure words from a percent result';
    const string FLD_STATUS = 'status_id';
    const string FLD_STATUS_COM = 'clean, dirty, updating, outdated or unused';
    const string FLD_LAST_UPDATE_COM = 'timestamp of the last update of the cache';


    // all database field names excluding the id, standard name and user-specific fields
    const array FLD_NAMES = array(
        self::FLD_TYPE,
        self::FLD_DATA,
        user_db::FLD_ID,
        self::FLD_STATUS,
        fields::FLD_LAST_UPDATE,
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [db_cache_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, db_cache_type::class, self::FLD_TYPE_COM],
        [self::FLD_DATA, sql_field_type::TEXT, sql_field_default::NULL, '', '', self::FLD_DATA_COM],
        [user_db::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [db_cache_status::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, db_cache_status::class, self::FLD_STATUS_COM],
        [fields::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_LAST_UPDATE_COM],
    );

}
