<?php

/*

    cfg/word/word_db.php - the database const for word tables
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

namespace cfg\word;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once MODEL_LANGUAGE_PATH . 'language.php';
//include_once MODEL_PHRASE_PATH . 'phrase.php';
include_once MODEL_PHRASE_PATH . 'phrase_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
//include_once MODEL_VIEW_PATH . 'view.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\language\language;
use cfg\phrase\phrase;
use cfg\phrase\phrase_type;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\view\view;

class word_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'word_id'; // TODO change the user_id field comment to 'the user who has changed the standard word'
    const FLD_NAME_COM = 'the text used for searching';
    const FLD_NAME = 'word_name';
    const FLD_DESCRIPTION_COM = 'to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to link coded functionality to words e.g. to exclude measure words from a percent result';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific word e.g. to get the values of the system configuration';
    const FLD_PLURAL_COM = 'to be replaced by a language form entry; TODO to be move to language forms';
    const FLD_PLURAL = 'plural'; // TODO move to language types
    const FLD_PLURAL_SQL_TYP = sql_field_type::NAME;
    const FLD_VIEW_COM = 'the default mask for this word';
    const FLD_VIEW = 'view_id';
    const FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const FLD_VALUES_COM = 'number of values linked to the word, which gives an indication of the importance';
    const FLD_VALUES = 'values'; // TODO convert to a percent value of relative importance e.g. is 100% if all values, results, triples, formulas and views use this word; should be possible to adjust the weight of e.g. values and views with the user specific system settings
    const FLD_VALUES_SQL_TYP = sql_field_type::INT;
    const FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
    const FLD_INACTIVE = 'inactive';
    const FLD_INACTIVE_SQL_TYP = sql_field_type::INT_SMALL;
    // the field names used for the im- and export in the json or yaml format
    const FLD_REFS = 'refs';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
        [self::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_PLURAL, self::FLD_PLURAL_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_PLURAL_COM],
        [sandbox_named::FLD_DESCRIPTION, sandbox_named::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, phrase_type::class, self::FLD_TYPE_COM],
        [self::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_VALUES, self::FLD_VALUES_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_VALUES_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [self::FLD_INACTIVE, self::FLD_INACTIVE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );


    // all database field names excluding the id, standard name and user specific fields
    const FLD_NAMES = array(
        self::FLD_VALUES,
        sql::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_PLURAL,
        sandbox_named::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_VALUES,
        self::FLD_PLURAL,
        sandbox_named::FLD_DESCRIPTION,
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

}
