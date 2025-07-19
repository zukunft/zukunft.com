<?php

/*

    model/formula/formula_db.php - the database const for formula tables
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

namespace cfg\formula;

include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SANDBOX_PATH . 'sandbox_named.php';
//include_once MODEL_VIEW_PATH . 'view.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\view\view;

class formula_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'formula_id';
    const FLD_ID_SQL_TYP = sql_field_type::INT;
    const FLD_NAME_COM = 'the text used to search for formulas that must also be unique for all terms (words, triples, verbs and formulas)';
    const FLD_NAME = 'formula_name';
    const FLD_TYPE_COM = 'the id of the formula type';
    const FLD_TYPE = 'formula_type_id';
    const FLD_TYPE_SQL_TYP = sql_field_type::INT;
    const FLD_FORMULA_TEXT_COM = 'the internal formula expression with the database references e.g. {f1} for formula with id 1';
    const FLD_FORMULA_TEXT = 'formula_text';
    const FLD_FORMULA_TEXT_SQL_TYP = sql_field_type::TEXT;
    const FLD_FORMULA_USER_TEXT_COM = 'the formula expression in user readable format as shown to the user which can include formatting for better readability';
    const FLD_FORMULA_USER_TEXT = 'resolved_text';
    const FLD_FORMULA_USER_TEXT_SQL_TYP = sql_field_type::TEXT;
    //const FLD_REF_TEXT = "ref_text";             // the formula field "ref_txt" is a more internal field, which should not be shown to the user (only to an admin for debugging)
    const FLD_DESCRIPTION_COM = 'text to be shown to the user for mouse over; to be replaced by a language form entry';
    const FLD_ALL_NEEDED_COM = 'the "calculate only if all values used in the formula exist" flag should be converted to "all needed for calculation" instead of just displaying "1"';
    const FLD_ALL_NEEDED = 'all_values_needed';
    const FLD_ALL_NEEDED_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_LAST_UPDATE_COM = 'time of the last calculation relevant update';
    const FLD_LAST_UPDATE = 'last_update';
    const FLD_LAST_UPDATE_SQL_TYP = sql_field_type::TIME;
    const FLD_VIEW_COM = 'the default mask for this formula';
    const FLD_VIEW = 'view_id';
    const FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const FLD_USAGE_COM = 'number of results linked to this formula';
    const FLD_USAGE = 'usage'; // TODO convert to a percent value of relative importance e.g. is 100% if all results, words and triples use this formula; should be possible to adjust the weight of e.g. values and views with the user specific system settings
    const FLD_USAGE_SQL_TYP = sql_field_type::INT;

    // list of fields that MUST be set by one user
    // TODO add foreign key for share and protection type?
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::UNIQUE, '', self::FLD_NAME_COM],
        [self::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_TEXT_COM],
        [self::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [self::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_TEXT_COM],
        [self::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, self::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, formula_type::class, self::FLD_TYPE_COM],
        [self::FLD_ALL_NEEDED, self::FLD_ALL_NEEDED_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_ALL_NEEDED_COM],
        [self::FLD_LAST_UPDATE, self::FLD_LAST_UPDATE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_LAST_UPDATE_COM],
        [self::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [self::FLD_USAGE, self::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_USAGE_COM],
    );

    // all database field names excluding the id
    // actually empty because all formula fields are user specific
    // TODO check if last_update must be user specific
    const FLD_NAMES = array();
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        self::FLD_FORMULA_TEXT,
        self::FLD_FORMULA_USER_TEXT,
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific numeric database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_ALL_NEEDED,
        self::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        self::FLD_FORMULA_TEXT,
        self::FLD_FORMULA_USER_TEXT,
        sql_db::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_ALL_NEEDED,
        self::FLD_LAST_UPDATE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

}
