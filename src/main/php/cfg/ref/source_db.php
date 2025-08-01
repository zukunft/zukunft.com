<?php

/*

    model/source/source_db.php - the database const for source tables
    --------------------------

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

namespace cfg\ref;

use cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;

class source_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'source_id';
    const FLD_NAME_COM = 'the unique name of the source used e.g. as the primary search key';
    const FLD_NAME = 'source_name';
    const FLD_DESCRIPTION_COM = 'the user specific description of the source for mouse over helps';
    const FLD_TYPE_COM = 'link to the source type';
    const FLD_TYPE = 'source_type_id';
    const FLD_URL_COM = 'the url of the source';
    const FLD_URL = 'url';
    const FLD_URL_SQL_TYP = sql_field_type::TEXT;
    const FLD_CODE_ID_COM = 'to select sources used by this program';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that can be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, source_type::class, self::FLD_TYPE_COM],
        [self::FLD_URL, self::FLD_URL_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [sql_db::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        self::FLD_NAME,
        sql_db::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_URL,
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        sql_db::FLD_DESCRIPTION,
        self::FLD_TYPE,
        sql_db::FLD_EXCLUDED,
        self::FLD_URL
    );

}
