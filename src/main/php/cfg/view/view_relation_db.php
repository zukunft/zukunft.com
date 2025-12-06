<?php

/*

    model/view/view_relation_db.php - the database const for view relation tables
    -------------------------------

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

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
//include_once paths::MODEL_HELPER . 'type_object.php';
//include_once paths::MODEL_SANDBOX . 'sandbox.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;

class view_relation_db
{

    /*
     * db const
     */

    // the database field names used only for the view relation
    // *_COM is the description of the field used for the SQL database
    const string FLD_ID = 'view_relation_id';
    const string FLD_TYPE_COM = '1 = add components, 2 = remove components as defined in view_relation_type';
    const string FLD_PARENT = 'parent_view_id';
    const string FLD_PARENT_COM = 'the parent view that should be modified by the child view for the used view';
    const string FLD_CHILD = 'child_view_id';
    const string FLD_CHILD_COM = 'the child view that should modify the parent view for the used view';
    const string FLD_START_POS = 'start_pos';
    const string FLD_START_POS_COM = 'the staring position in the component chain where the changes should apply';

    // database fields that cannot be user specific excluding the id
    const array FLD_NAMES = array(
        self::FLD_PARENT,
        view_relation_type::FLD_ID,
        self::FLD_CHILD,
    );
    // list of the user specific database field names
    const array FLD_NAMES_USR = array(
        sql_db::FLD_DESCRIPTION,
    );
    // list of the numeric user specific database field names
    const array FLD_NAMES_NUM_USR = array(
        self::FLD_START_POS,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // all database field names, excluding the id, used to identify if there are some user specific changes
    // TODO check if this is used in all relevant objects
    const array ALL_SANDBOX_FLD_NAMES = array(
        self::FLD_START_POS,
        sql_db::FLD_DESCRIPTION,
        sql_db::FLD_EXCLUDED,
        sandbox::FLD_SHARE,
        sandbox::FLD_PROTECT
    );
    // list of fields that select the objects that should be linked
    const array FLD_LST_LINK = array(
        [self::FLD_PARENT, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, self::FLD_PARENT_COM, view_db::FLD_ID],
        [self::FLD_CHILD, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, view::class, self::FLD_CHILD_COM, view_db::FLD_ID],
    );
    // list of MANDATORY fields that CANNOT be CHANGED by the user
    const array FLD_LST_MUST_BUT_STD_ONLY = array(
        [view_relation_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::ONE, sql::INDEX, view_relation_type::class, self::FLD_TYPE_COM],
    );
    // fields that CAN be changed by the user with the parameters for the table creation
    const array FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_START_POS, sql_field_type::INT, sql_field_default::NULL, '', '', ''],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );

}
