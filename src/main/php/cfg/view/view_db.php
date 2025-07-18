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

namespace cfg\view;

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

class view_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // means: database fields only used for words
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_ID = 'view_id';
    const FLD_NAME_COM = 'the name of the view used for searching';
    const FLD_NAME = 'view_name';
    const FLD_DESCRIPTION_COM = 'to explain the view to the user with a mouse over text; to be replaced by a language form entry';
    const FLD_TYPE_COM = 'to link coded functionality to views e.g. to use a view for the startup page';
    const FLD_TYPE = 'view_type_id';
    const FLD_STYLE_COM = 'the default display style for this view';
    const FLD_STYLE = 'view_style_id';
    const FLD_CODE_ID_COM = 'to link coded functionality to a specific view e.g. define the internal system views';

    // list of fields that MUST be set by one user
    const FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::ONE, sql::INDEX, language::class, self::FLD_NAME_COM],
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const FLD_LST_USER_CAN_CHANGE = array(
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_type::class, self::FLD_TYPE_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const FLD_LST_NON_CHANGEABLE = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id
    const FLD_NAMES = array(
        sql::FLD_CODE_ID
    );
    // list of the user specific database field names
    const FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const ALL_SANDBOX_FLD_NAMES = array(
        sql_db::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_STYLE,
        sandbox::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

}
