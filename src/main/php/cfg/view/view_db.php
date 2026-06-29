<?php

/*

    model/view/view_db.php - the database const for view tables
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

namespace Zukunft\ZukunftCom\main\php\cfg\view;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

//include_once paths::MODEL_COMPONENT . 'view_style.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'view_fields.php';
//include_once paths::MODEL_HELPER . 'type_object.php';
//include_once paths::MODEL_LANGUAGE . 'language.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_SANDBOX . 'sandbox_named.php';

use Zukunft\ZukunftCom\main\php\cfg\component\view_style;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\language\language;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_named;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\view_fields;

class view_db
{

    /*
     * db const
     */

    // object specific database and JSON object fields
    // means: database fields only used for views
    // the field names and their descriptions are defined in view_fields
    const string FLD_CODE_ID_COM = 'to link coded functionality to a specific view e.g. define the internal system views';
    const string FLD_STYLE_COM = 'the default display style for this view';

    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array(
        [view_fields::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', view_fields::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [language::FLD_ID, sql_field_type::KEY_PART_INT_SMALL, sql_field_default::ONE, sql::INDEX, language::class, view_fields::FLD_NAME_COM],
        [view_fields::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', view_fields::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', view_fields::FLD_DESCRIPTION_COM],
        [view_fields::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_type::class, view_fields::FLD_TYPE_COM],
        [fields::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
        [fields::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_USAGE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [fields::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
    );

    // all database field names excluding the id
    const array FLD_NAMES = array(
        fields::FLD_CODE_ID,
        fields::FLD_USAGE
    );
    // list of the user-specific database field names
    const array FLD_NAMES_USR = array(
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific database field names
    const array FLD_NAMES_USR_ALL = array(
        view_fields::FLD_NAME,
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific database field names
    const array FLD_NAMES_NUM_USR = array(
        view_fields::FLD_TYPE,
        fields::FLD_STYLE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in view_fields::ALL_NAMES

}
