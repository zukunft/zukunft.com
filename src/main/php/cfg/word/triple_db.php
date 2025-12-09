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
//include_once paths::MODEL_VERB . 'verb.php';
//include_once paths::MODEL_VERB . 'verb_db.php';
//include_once paths::MODEL_VIEW . 'view.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\view\view;

class triple_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID = 'triple_id';
    const string FLD_FROM_COM = 'the phrase_id that is linked which can be null e.g. if a symbol is assigned to a triple (m/s is symbol for meter per second)';
    const string FLD_FROM = 'from_phrase_id';
    const string FLD_VERB_COM = 'the verb_id that defines how the phrases are linked';
    const string FLD_TO_COM = 'the phrase_id to which the first phrase is linked';
    const string FLD_TO = 'to_phrase_id';
    const string FLD_NAME_COM = 'the name used which must be unique within the terms of the user';
    const string FLD_NAME = 'triple_name';
    const string FLD_NAME_GIVEN_COM = 'the unique name manually set by the user, which can be null if the generated name should be used';
    const string FLD_NAME_GIVEN = 'name_given';
    const sql_field_type FLD_NAME_GIVEN_SQL_TYP = sql_field_type::NAME;
    const string FLD_NAME_AUTO_COM = 'the generated name is saved in the database for database base unique check based on the phrases and verb, which can be overwritten by the given name';
    const string FLD_NAME_AUTO = 'name_generated';
    const sql_field_type FLD_NAME_AUTO_SQL_TYP = sql_field_type::NAME;
    const string FLD_DESCRIPTION_COM = 'text that should be shown to the user in case of mouseover on the triple name';
    const sql_field_type FLD_DESCRIPTION_SQL_TYP = sql_field_type::TEXT;
    const string FLD_WIGHT = 'weight';
    const string FLD_WIGHT_COM = 'the weight of this triple compared to others where 1 represents 100% weight';
    const sql_field_type FLD_WEIGHT_SQL_TYP = sql_field_type::NUMERIC_FLOAT;
    const string FLD_VIEW_COM = 'the default mask for this triple';
    const string FLD_VIEW = 'view_id';
    const string FLD_USAGE_COM = 'number of values, formulas and results linked to this triple, which gives an indication of the importance and is used for sorting if the impact calculation is incomplete or missing';
    const string FLD_INACTIVE_COM = 'true if the word is not yet active e.g. because it is moved to the prime words with a 16 bit id';
    const string FLD_INACTIVE = 'inactive';
    const string FLD_CODE_ID_COM = 'to link coded functionality to a specific triple e.g. to get the values of the system configuration';
    const string FLD_COND_ID_COM = 'formula_id of a formula with a boolean result; the term is only added if formula result is true';
    const string FLD_COND_ID = 'triple_condition_id';

    // list of fields that MUST be set by one user
    const array FLD_LST_LINK = array(
        [self::FLD_FROM, sql_field_type::INT_UNIQUE_PART, sql_field_default::NULL, sql::INDEX, '', self::FLD_FROM_COM],
        [verb_db::FLD_ID, sql_field_type::INT_SMALL_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, verb::class, self::FLD_VERB_COM],
        [self::FLD_TO, sql_field_type::INT_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_TO_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_NAME_GIVEN, self::FLD_NAME_GIVEN_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_GIVEN_COM],
        [self::FLD_NAME_AUTO, self::FLD_NAME_AUTO_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_AUTO_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_WIGHT, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, sql::INDEX, '', self::FLD_WIGHT_COM],
        [self::FLD_COND_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_COND_ID_COM],
        [phrase::FLD_TYPE, phrase::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, phrase_type::class, word_db::FLD_TYPE_COM],
        [self::FLD_VIEW, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [sql_db::FLD_USAGE, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_USAGE_COM],
        [sql_db::FLD_IMPACT, sql_db::FLD_IMPACT_SQL_TYP, sql_field_default::NULL, '', '', sql_db::FLD_IMPACT_COM],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [self::FLD_INACTIVE, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_INACTIVE_COM],
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id and excluding the user specific fields
    const array FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        sql_db::FLD_USAGE,
        self::FLD_COND_ID
    );
    // list of the link database field names
    // TODO use this name for all links
    const array FLD_NAMES_LINK = array(
        self::FLD_FROM,
        verb_db::FLD_ID,
        self::FLD_TO
    );
    // list of the user specific database field names
    const array FLD_NAMES_USR = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        self::FLD_WIGHT,
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sql_db::FLD_IMPACT,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_NAME_GIVEN,
        self::FLD_NAME_AUTO,
        sql_db::FLD_DESCRIPTION,
        self::FLD_WIGHT,
        phrase::FLD_TYPE,
        self::FLD_VIEW,
        sql_db::FLD_USAGE,
        sql_db::FLD_IMPACT,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

}
