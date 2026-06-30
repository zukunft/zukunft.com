<?php

/*

    model/ref/ref_db.php - the database const for reference tables
    --------------------

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
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'source_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'ref_fields.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
//include_once paths::MODEL_REF . 'source_db.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\source_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\ref_fields;

class ref_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for references
    // the field names and their descriptions are defined in ref_fields
    // *_SQL_TYP is the sql data type used for the field
    // TODO Prio 2 add a status and use it to show an easy switch off button
    // TODO Prio 2 an update methode and frequency for push updates or daily weekly or idle update retries
    const sql_field_type FLD_EX_KEY_SQL_TYP = sql_field_type::NAME;
    const sql_field_type FLD_URL_SQL_TYP = sql_field_type::TEXT;
    const string FLD_URL_COM = 'the concrete url for the entry including the item id';
    const string FLD_LAST_UPDATE_COM = 'timestamp of the last successful update of the reference used to trigger the next refresh job';

    // field names that cannot be user-specific
    const array FLD_NAMES = array(
        phrase::FLD_ID,
        ref_fields::FLD_TYPE,
        fields::FLD_IMPACT,
        fields::FLD_LAST_UPDATE
    );
    // list of user-specific text field names
    const array FLD_NAMES_USR = array(
        ref_fields::FLD_EX_KEY,
        fields::FLD_URL,
        fields::FLD_DESCRIPTION
    );
    // list of user-specific numeric field names
    const array FLD_NAMES_NUM_USR = array(
        source_fields::FLD_ID,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in ref_fields::ALL_NAMES
    // list of fields that must be set
    const array FLD_LST_MUST_BUT_STD_ONLY = array(
        [ref_fields::FLD_EX_KEY, self::FLD_EX_KEY_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, '', ref_fields::FLD_EX_KEY_COM],
    );
    // list of fields that must be set, but CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [ref_fields::FLD_EX_KEY, self::FLD_EX_KEY_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', ref_fields::FLD_EX_KEY_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [fields::FLD_URL, self::FLD_URL_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [source_fields::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, ref_fields::FLD_SOURCE_COM],
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', ref_fields::FLD_PHRASE_COM],
        [ref_type::FLD_ID, sql_field_type::INT_SMALL, sql_field_default::NOT_NULL, sql::INDEX, ref_type::class, ref_type::TBL_COMMENT],
        [fields::FLD_IMPACT, sql_db::FLD_IMPACT_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_IMPACT_COM],
        [fields::FLD_LAST_UPDATE, sql_field_type::TIME, sql_field_default::NULL, '', '', self::FLD_LAST_UPDATE_COM],
    );

}
