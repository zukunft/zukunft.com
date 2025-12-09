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

class component_db
{

    /*
     * db const
     */

    // the database and JSON object field names used only for view components links
    // *_COM: the description of the field
    // *_SQL_TYP: the sql field type used for this field
    const string FLD_ID = 'component_id';
    const string FLD_NAME_COM = 'the unique name used to select a component by the user';
    const string FLD_NAME = 'component_name';
    const string FLD_DESCRIPTION_COM = 'to explain the view component to the user with a mouse over text; to be replaced by a language form entry';
    const string FLD_TYPE_COM = 'to select the predefined functionality';
    const string FLD_TYPE = 'component_type_id';
    const string FLD_STYLE_COM = 'the default display style for this component';
    const string FLD_STYLE = 'view_style_id';
    const string FLD_CODE_ID_COM = 'used for system components to select the component by the program code';
    const string FLD_UI_MSG_ID_COM = 'used for system components the id to select the language specific user interface message e.g. "add word"';
    const string FLD_UI_MSG_ID = 'ui_msg_code_id';
    const string FLD_UI_MSG_ID_VARS = 'ui_msg_code_id_vars';
    const string FLD_UI_MSG_ID_VARS_COM = 'used for system components the id to select the language specific user interface message where some variable placeholders are replaced with system values';
    const string FLD_UI_MSG_ID_EXCEPTION = 'ui_msg_code_id_exception';
    const string FLD_UI_MSG_ID_EXCEPTION_COM = 'used for system components the id to select the language specific user interface exception message e.g. if the system value is zero';
    const string FLD_UI_MSG_VAL_EXCEPTION = 'ui_msg_value_exception';
    const string FLD_UI_MSG_VAL_EXCEPTION_COM = 'used for system components the value to select the exception message e.g. 0 (zero)';
    const sql_field_type FLD_UI_MSG_ID_SQL_TYP = sql_field_type::CODE_ID;
    // TODO move the lined phrases to a component phrase link table for n:m relation with a type for each link
    const string FLD_ROW_PHRASE_COM = 'for a tree the related value the start node';
    const string FLD_ROW_PHRASE = 'word_id_row';
    const string FLD_COL_PHRASE_COM = 'to define the type for the table columns';
    const string FLD_COL_PHRASE = 'word_id_col';
    const string FLD_COL2_PHRASE_COM = 'e.g. "quarter" to show the quarters between the year columns or the second axis of a chart';
    const string FLD_COL2_PHRASE = 'word_id_col2';
    const string FLD_FORMULA_COM = 'used for type 6';
    const string FLD_LINK_COMP_COM = 'to link this component to another component';
    const string FLD_LINK_COMP = 'linked_component_id';
    const string FLD_LINK_COMP_TYPE_COM = 'to define how this entry links to the other entry';
    const string FLD_LINK_COMP_TYPE = 'component_link_type_id';
    const string FLD_LINK_TYPE_COM = 'e.g. for type 4 to select possible terms';
    const string FLD_LINK_TYPE = 'link_type_id';
    const sql_field_type FLD_LINK_TYPE_SQL_TYP = sql_field_type::INT_SMALL;

    // list of fields that MUST be set by one user
    const array FLD_LST_MUST_BE_IN_STD = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of must fields that CAN be changed by the user
    const array FLD_LST_MUST_BUT_USER_CAN_CHANGE = array(
        [self::FLD_NAME, sql_field_type::NAME, sql_field_default::NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    // list of fields that CAN be changed by the user
    const array FLD_LST_USER_CAN_CHANGE = array(
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
        [self::FLD_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, component_type::class, self::FLD_TYPE_COM],
        [self::FLD_STYLE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, view_style::class, self::FLD_STYLE_COM],
        // TODO link with a foreign key to phrases (or terms?) if link to a view is allowed
        [self::FLD_ROW_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_ROW_PHRASE_COM],
        [formula_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, formula::class, self::FLD_FORMULA_COM],
        [self::FLD_COL_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_COL_PHRASE_COM],
        [self::FLD_COL2_PHRASE, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_COL2_PHRASE_COM],
        [self::FLD_LINK_COMP, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_COMP_COM],
        [self::FLD_LINK_COMP_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_COMP_TYPE_COM],
        [self::FLD_LINK_TYPE, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_LINK_TYPE_COM],
        [sql_db::FLD_USAGE, sql_db::FLD_USAGE_SQL_TYP, sql_field_default::NULL, '', '', sql_db::FLD_USAGE_COM],
    );
    // list of fields that CANNOT be changed by the user
    const array FLD_LST_NON_CHANGEABLE = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [self::FLD_UI_MSG_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_UI_MSG_ID_COM],
        [self::FLD_UI_MSG_ID_VARS, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_UI_MSG_ID_VARS_COM],
        [self::FLD_UI_MSG_ID_EXCEPTION, sql_field_type::NAME, sql_field_default::NULL, '', '', self::FLD_UI_MSG_ID_EXCEPTION_COM],
        [self::FLD_UI_MSG_VAL_EXCEPTION, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', self::FLD_UI_MSG_VAL_EXCEPTION_COM],
    );

    // all database field names excluding the id
    const array FLD_NAMES = array(
        sql_db::FLD_CODE_ID,
        sql_db::FLD_USAGE,
        self::FLD_UI_MSG_ID,
        self::FLD_UI_MSG_ID_VARS,
        self::FLD_UI_MSG_ID_EXCEPTION,
        self::FLD_UI_MSG_VAL_EXCEPTION
    );
    // list of the user specific database field names
    const array FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of the user specific database field names
    const array FLD_NAMES_NUM_USR = array(
        self::FLD_TYPE,
        self::FLD_STYLE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula_db::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names excluding the id used to identify if there are some user specific changes
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_NAME,
        sql_db::FLD_DESCRIPTION,
        self::FLD_TYPE,
        self::FLD_STYLE,
        self::FLD_ROW_PHRASE,
        self::FLD_LINK_TYPE,
        formula_db::FLD_ID,
        self::FLD_COL_PHRASE,
        self::FLD_COL2_PHRASE,
        sql_db::FLD_USAGE,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );

}

