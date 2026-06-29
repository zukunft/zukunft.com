<?php

/*

    model/word/word_db.php - the database const for word tables
    ----------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\word;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_type.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
//include_once paths::MODEL_VIEW . 'view.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;

class word_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for words
    // the field names and their descriptions are defined in word_fields
    // *_SQL_TYP is the sql data type used for the field
    const sql_field_type FLD_PLURAL_SQL_TYP = sql_field_type::NAME;
    const sql_field_type FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const sql_field_type FLD_INACTIVE_SQL_TYP = sql_field_type::INT_SMALL;
    const string FLD_CODE_ID_COM = 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const string FLD_VIEW_COM = 'the default mask for this word';
    const string FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';


    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array(
        [word_fields::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', word_fields::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ONE, sql::INDEX, language::class, word_fields::FLD_NAME_COM],
        [word_fields::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', word_fields::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [word_fields::FLD_PLURAL, self::FLD_PLURAL_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', word_fields::FLD_PLURAL_COM],
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', word_fields::FLD_DESCRIPTION_COM],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, phrase_type::class, word_fields::FLD_TYPE_COM],
        [fields::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [fields::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_USAGE_COM],
        [fields::FLD_IMPACT, sql_db::FLD_IMPACT_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_IMPACT_COM],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [fields::FLD_INACTIVE, self::FLD_INACTIVE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [fields::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );


    // all database field names excluding the id, standard name and user-specific fields
    const array FLD_NAMES = array(
        fields::FLD_CODE_ID,
        fields::FLD_USAGE
    );
    // list of the user-specific database field names
    const array FLD_NAMES_USR = array(
        word_fields::FLD_PLURAL,
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        phrase::FLD_TYPE,
        fields::FLD_VIEW,
        fields::FLD_IMPACT,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );

}
