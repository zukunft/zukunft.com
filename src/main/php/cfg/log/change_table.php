<?php

/*

    cfg/log/change_table.php - the table where a user has done a change including deprecated table names
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

class change_table extends type_object
{


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to keep the original table name even if a table name has changed and to avoid log changes in case a table is renamed';
    const FLD_ID = 'change_table_id';
    const FLD_NAME_COM = 'the real name';
    const FLD_NAME = 'change_table_name';
    const FLD_CODE_ID_COM = 'with this field tables can be combined in case of renaming';
    const FLD_DESCRIPTION_COM = 'the user readable name';

    // field lists for the table creation
    const FLD_LST_NAME = array(
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const FLD_LST_ALL = array(
        [sql::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [self::FLD_DESCRIPTION, self::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_DESCRIPTION_COM],
    );

}