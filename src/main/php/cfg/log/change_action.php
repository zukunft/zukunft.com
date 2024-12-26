<?php

/*

    cfg/log/change_action.php - the change type done by a user
    -------------------------


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

namespace cfg\log;

include_once DB_PATH . 'sql.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\type_object;

class change_action extends type_object
{

    // the basic change types that are logged
    const ADD = 'add';
    const UPDATE = 'update';
    const DELETE = 'del';

    // list of all log actions allowed in this program version
    const ACTION_LIST = array(
        self::ADD,
        self::UPDATE,
        self::DELETE
    );


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for add, change, delete, undo and redo actions';
    const FLD_ID = 'change_action_id';
    const FLD_NAME = 'change_action_name';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
    );
    const FLD_LST_ALL = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, '', '', ''],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );

}