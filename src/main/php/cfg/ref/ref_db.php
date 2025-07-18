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

namespace cfg\ref;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
//include_once MODEL_REF_PATH . 'source_db.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\phrase\phrase;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;

class ref_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'ref_id';
    const FLD_USER_COM = 'the user who has created or adjusted the reference';
    const FLD_EX_KEY_COM = 'the unique external key used in the other system';
    const FLD_EX_KEY = 'external_key';
    const FLD_EX_KEY_SQL_TYP = sql_field_type::NAME;
    const FLD_TYPE = 'ref_type_id';
    const FLD_URL_COM = 'the concrete url for the entry including the item id';
    const FLD_URL = 'url';
    const FLD_URL_SQL_TYP = sql_field_type::TEXT;
    const FLD_SOURCE_COM = 'if the reference does not allow a full automatic bidirectional update use the source to define an as good as possible import or at least a check if the reference is still valid';
    const FLD_SOURCE = 'source_id';
    const FLD_PHRASE_COM = 'the phrase for which the external data should be synchronised';

    // field names that cannot be user specific
    const FLD_NAMES = array(
        phrase::FLD_ID,
        self::FLD_TYPE
    );
    // list of user specific text field names
    const FLD_NAMES_USR = array(
        self::FLD_EX_KEY,
        self::FLD_URL,
        sql_db::FLD_DESCRIPTION
    );
    // list of user specific numeric field names
    const FLD_NAMES_NUM_USR = array(
        source_db::FLD_ID,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_EX_KEY,
        self::FLD_URL,
        sql_db::FLD_DESCRIPTION,
        sandbox::FLD_EXCLUDED
    );
    // list of fields that must be set
    const FLD_LST_MUST_BUT_STD_ONLY = array(
        [self::FLD_EX_KEY, self::FLD_EX_KEY_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_EX_KEY_COM],
    );
    // list of fields that must be set, but CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_EX_KEY, self::FLD_EX_KEY_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_EX_KEY_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_URL, self::FLD_URL_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_URL_COM],
        [source_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, self::FLD_SOURCE_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [phrase::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_PHRASE_COM],
        [ref_type::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, ref_type::class, ref_type::TBL_COMMENT],
    );

}
