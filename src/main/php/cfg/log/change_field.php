<?php

/*

    model/log/change_field.php - the field where a user has done a change including deprecated field names
    --------------------------

    TODO Prio 1 add a column with the short name that should be shown to the user
                and for the selection the unique name should be "name (word)" instead of 5word_name
                and use the description of the e.g. word_db object for the code_link csv file


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

namespace Zukunft\ZukunftCom\main\php\cfg\log;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class change_field extends type_object
{


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to keep the original field name even if a table name has changed';
    const string FLD_ID = 'change_field_id';
    const string FLD_NAME_COM = 'the real name';
    const string FLD_NAME = 'change_field_name';
    const string FLD_TABLE_COM = 'because every field must only be unique within a table';
    const string FLD_TABLE = 'table_id';
    const string FLD_CODE_ID_COM = 'to display the change with some linked information';

    // field lists for the field creation
    const array FLD_LST_NAME = array(
        [self::FLD_TABLE, sql_field_type::INT_SMALL_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, change_table::class, self::FLD_TABLE_COM, change_table::FLD_ID],
        [self::FLD_NAME, sql_field_type::NAME_UNIQUE_PART, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_NAME_COM],
    );
    const array FLD_LST_ALL = array(
        [sql_db::FLD_CODE_ID, sql_field_type::NAME_UNIQUE, sql_field_default::NULL, '', '', self::FLD_CODE_ID_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', ''],
    );

}