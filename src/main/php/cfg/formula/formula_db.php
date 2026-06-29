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

namespace Zukunft\ZukunftCom\main\php\cfg\formula;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SANDBOX . 'sandbox_named.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';
//include_once paths::MODEL_VIEW . 'view.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;

class formula_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for formulas
    // the field names and their descriptions are defined in formula_fields
    // *_SQL_TYP is the sql data type used for the field
    // TODO Prio 2 add a status with simulate for formulas that are not yet saved and active, but where the results should be simulated
    const sql_field_type FLD_ID_SQL_TYP = sql_field_type::INT;
    const sql_field_type FLD_TYPE_SQL_TYP = sql_field_type::INT_SMALL;
    const sql_field_type FLD_FORMULA_TEXT_SQL_TYP = sql_field_type::TEXT;
    const sql_field_type FLD_FORMULA_USER_TEXT_SQL_TYP = sql_field_type::TEXT;
    const sql_field_type FLD_LATEX_SQL_TYP = sql_field_type::TEXT;
    const sql_field_type FLD_ALL_NEEDED_SQL_TYP = sql_field_type::INT_SMALL;
    const sql_field_type FLD_LAST_UPDATE_SQL_TYP = sql_field_type::TIME;
    const sql_field_type FLD_VIEW_SQL_TYP = sql_field_type::INT;
    const string FLD_USAGE_COM = 'number of results linked to this formula';
    const string FLD_LAST_UPDATE_COM = 'time of the last calculation relevant update';
    const string FLD_VIEW_COM = 'the default mask for this formula';

    // list of fields that MUST be set by one user
    // TODO add foreign key for share and protection type?
    const array FLD_LST_MUST_BE_IN_STD = array(
        [formula_fields::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::UNIQUE, '', formula_fields::FLD_NAME_COM],
        [formula_fields::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_FORMULA_TEXT_COM],
        [formula_fields::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [formula_fields::FLD_NAME, sandbox_named::FLD_NAME_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', formula_fields::FLD_NAME_COM],
        [formula_fields::FLD_FORMULA_TEXT, self::FLD_FORMULA_TEXT_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_FORMULA_TEXT_COM],
        [formula_fields::FLD_FORMULA_USER_TEXT, self::FLD_FORMULA_USER_TEXT_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_FORMULA_USER_TEXT_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [formula_fields::FLD_LATEX, self::FLD_LATEX_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_LATEX_COM],
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_DESCRIPTION_COM],
        [formula_fields::FLD_TYPE, self::FLD_TYPE_SQL_TYP, sql_field_default::NULL, sql::INDEX, formula_type::class, formula_fields::FLD_TYPE_COM],
        [formula_fields::FLD_ALL_NEEDED, self::FLD_ALL_NEEDED_SQL_TYP, sql_field_default::NULL, '', '', formula_fields::FLD_ALL_NEEDED_COM],
        [fields::FLD_LAST_UPDATE, self::FLD_LAST_UPDATE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_LAST_UPDATE_COM],
        [fields::FLD_VIEW, self::FLD_VIEW_SQL_TYP, sql_field_default::NULL, sql::INDEX, view::class, self::FLD_VIEW_COM],
        [fields::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_USAGE_COM],
        [fields::FLD_IMPACT, sql_db::FLD_IMPACT_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_IMPACT_COM],
    );

    // all database field names excluding the id
    // actually empty because all formula fields are user-specific
    // TODO check if last_update must be user-specific
    const array FLD_NAMES = array();
    // list of the user-specific database field names
    const array FLD_NAMES_USR = array(
        formula_fields::FLD_FORMULA_TEXT,
        formula_fields::FLD_FORMULA_USER_TEXT,
        formula_fields::FLD_LATEX,
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific numeric database field names
    const array FLD_NAMES_NUM_USR = array(
        formula_fields::FLD_TYPE,
        formula_fields::FLD_ALL_NEEDED,
        fields::FLD_LAST_UPDATE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in formula_fields::ALL_NAMES

}
