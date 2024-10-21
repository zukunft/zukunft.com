<?php

/*

    cfg/log/change_field.php - the field where a user has done a change including deprecated field names
    ------------------------


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

include_once MODEL_HELPER_PATH . 'type_object.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\type_object;

class change_field extends type_object
{


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to keep the original field name even if a table name has changed';
    const FLD_ID = 'change_field_id';
    const FLD_NAME_COM = 'the real name';
    const FLD_NAME = 'change_field_name';
    const FLD_TABLE_COM = 'because every field must only be unique within a table';
    const FLD_TABLE = 'table_id';
    const FLD_CODE_ID_COM = 'to display the change with some linked information';

    // field lists for the field creation
    const FLD_LST_ALL = array(
        [self::FLD_TABLE, sql_field_type::INT_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, change_table::class, self::FLD_TABLE_COM, change_table::FLD_ID],
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );

}