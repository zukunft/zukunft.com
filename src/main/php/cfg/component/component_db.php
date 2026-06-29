<?php

/*

    model/component/component.php - a single display object like a headline or a table
    ---------------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this component object
    - construct and map: including the mapping of the db row to this component object
    - set and get:       to capsule the vars from unexpected changes
    - preloaded:         select e.g. types from cache
    - load:              database access object (DAO) functions
    - sql fields:        field names for sql and other load helper functions
    - related:           load related objects assigned to this component from the database
    - cast:              create an api object and set the vars from an api json
    - im- and export:    create an export object and set the vars from an import object
    - info:              functions to make code easier to read
    - log:               write the changes to the log
    - link:              link and release the component to and from a view
    - save:              manage to update the database
    - del:               manage to remove from the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - debug:             internal support functions for debugging


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\component;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'component_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';
//include_once paths::MODEL_COMPONENT . 'view_style.php';
//include_once paths::MODEL_FORMULA . 'formula.php';
//include_once paths::MODEL_FORMULA . 'formula_db.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';
//include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\component_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;

class component_db
{

    /*
     * db const
     */

    // the database and JSON object fields used only for view components
    // the field names and their descriptions are defined in component_fields
    // *_SQL_TYP: the sql field type used for this field
    const sql_field_type FLD_UI_MSG_ID_SQL_TYP = sql_field_type::CODE_ID;
    const sql_field_type FLD_LINK_TYPE_SQL_TYP = sql_field_type::INT_SMALL;
    const string FLD_CODE_ID_COM = 'used for system components to select the component by the program code';
    const string FLD_STYLE_COM = 'the default display style for this component';

    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array(
        [component_fields::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', component_fields::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [component_fields::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [fields::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', component_fields::FLD_DESCRIPTION_COM],
        [component_fields::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, component_type::class, component_fields::FLD_TYPE_COM],
        [fields::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
        // TODO link with a foreign key to phrases (or terms?) if link to a view is allowed
        [component_fields::FLD_ROW_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_ROW_PHRASE_COM],
        [formula_fields::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, formula::class, component_fields::FLD_FORMULA_COM],
        [component_fields::FLD_COL_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_COL_PHRASE_COM],
        [component_fields::FLD_COL2_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_COL2_PHRASE_COM],
        [component_fields::FLD_LINK_COMP, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_LINK_COMP_COM],
        [component_fields::FLD_LINK_COMP_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_LINK_COMP_TYPE_COM],
        [component_fields::FLD_LINK_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', component_fields::FLD_LINK_TYPE_COM],
        [fields::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', fields::FLD_USAGE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [fields::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [component_fields::FLD_UI_MSG_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', component_fields::FLD_UI_MSG_ID_COM],
        [component_fields::FLD_UI_MSG_ID_VARS, sql_field_type::NAME, sql_field_default::NULL, '', '', component_fields::FLD_UI_MSG_ID_VARS_COM],
        [component_fields::FLD_UI_MSG_ID_EXCEPTION, sql_field_type::NAME, sql_field_default::NULL, '', '', component_fields::FLD_UI_MSG_ID_EXCEPTION_COM],
        [component_fields::FLD_UI_MSG_VAL_EXCEPTION, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', component_fields::FLD_UI_MSG_VAL_EXCEPTION_COM],
    );

    // all database field names excluding the id
    const array FLD_NAMES = array(
        fields::FLD_CODE_ID,
        fields::FLD_USAGE,
        component_fields::FLD_UI_MSG_ID,
        component_fields::FLD_UI_MSG_ID_VARS,
        component_fields::FLD_UI_MSG_ID_EXCEPTION,
        component_fields::FLD_UI_MSG_VAL_EXCEPTION
    );
    // list of the user-specific database field names
    const array FLD_NAMES_USR = array(
        fields::FLD_DESCRIPTION
    );
    // list of the user-specific database field names
    const array FLD_NAMES_NUM_USR = array(
        component_fields::FLD_TYPE,
        fields::FLD_STYLE,
        component_fields::FLD_ROW_PHRASE,
        component_fields::FLD_LINK_TYPE,
        formula_fields::FLD_ID,
        component_fields::FLD_COL_PHRASE,
        component_fields::FLD_COL2_PHRASE,
        fields::FLD_EXCLUDED,
        fields::FLD_SHARE,
        fields::FLD_PROTECT
    );
    // the ordered field names used to detect user-specific changes are defined in component_fields::ALL_NAMES

}

