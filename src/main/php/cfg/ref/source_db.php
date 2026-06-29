<?php

/*

    model/ref/source_db.php - the database const for source tables
    -----------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\ref;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'source_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;

class source_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for sources
    // the field names and their descriptions are defined in source_fields
    // *_SQL_TYP is the sql data type used for the field
    const sql_field_type FLD_URL_SQL_TYP = sql_field_type::TEXT;
    const string FLD_CODE_ID_COM = 'to select sources used by this program';
    const string FLD_URL_COM = 'the url of the source';

    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array(
        [source_fields::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', source_fields::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [source_fields::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', source_fields::FLD_NAME_COM],
    );
    // list of fields that can be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', source_fields::FLD_DESCRIPTION_COM],
        [source_fields::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, source_type::class, source_fields::FLD_TYPE_COM],
        [fields::FLD_URL, self::FLD_URL_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [fields::FLD_CODE_ID, sql_field_type::CODE_ID, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [fields::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_USAGE_COM],
    );

    // all database field names excluding the id used to identify if there are some user-specific changes
    const array FLD_NAMES = array(
        source_fields::FLD_NAME,
        fields::FLD_CODE_ID,
        fields::FLD_USAGE
    );
    // list of the user-specific database field names
    const array FLD_NAMES_USR = array(
        fields::FLD_URL,
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        source_fields::FLD_TYPE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in source_fields::ALL_NAMES

}
