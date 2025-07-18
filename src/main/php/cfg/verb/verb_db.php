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

namespace cfg\verb;

//include_once MODEL_COMPONENT_PATH . 'view_style.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
//include_once MODEL_HELPER_PATH . 'type_object.php';
//include_once MODEL_LANGUAGE_PATH . 'language.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';

use cfg\component\view_style;
use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\helper\type_object;
use cfg\language\language;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;

class verb_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'verb_id';
    const FLD_NAME = 'verb_name';
    const FLD_CODE_ID_COM = 'id text to link coded functionality to a specific verb';
    const FLD_CONDITION = 'condition_type';
    const FLD_FORMULA_COM = 'naming used in formulas';
    const FLD_FORMULA = 'formula_name';
    const FLD_PLURAL = 'name_plural';
    const FLD_REVERSE = 'name_reverse';
    const FLD_PLURAL_REVERSE_COM = 'english description for the reverse list, e.g. Companies are ... TODO move to language forms';
    const FLD_PLURAL_REVERSE = 'name_plural_reverse';
    const FLD_WORDS_COM = 'used for how many phrases or formulas';
    const FLD_WORDS = 'words';

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        sql_db::FLD_DESCRIPTION,
        self::FLD_PLURAL,
        self::FLD_REVERSE,
        self::FLD_PLURAL_REVERSE,
        self::FLD_FORMULA,
        self::FLD_WORDS
    );

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', type_object::FLD_NAME_COM],
    );
    const FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', type_object::FLD_DESCRIPTION_COM],
        [self::FLD_CONDITION, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [self::FLD_FORMULA, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_FORMULA_COM],
        [self::FLD_PLURAL_REVERSE, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_PLURAL_REVERSE_COM],
        [self::FLD_PLURAL, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_REVERSE, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_WORDS, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_WORDS_COM],
    );


}
