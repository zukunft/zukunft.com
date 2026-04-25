<?php

/*

    model/group/group_db.php - the database const for group tables
    ------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\group;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_type.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;

class group_db
{

    /*
     * db const
     */

    // object specific database and JSON object field names
    // *_COM: the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const string FLD_ID_COM = 'the 64-bit prime index to find the -=class=-';
    const string FLD_ID_COM_USER = 'the 64-bit prime index to find the user -=class=-';
    const string FLD_ID = 'group_id';
    const string FLD_NAME_COM = 'the user-specific group name which can contain the phrase names in a different order to display the group (does not need to be unique)';
    const string FLD_NAME = 'group_name';
    const sql_field_type FLD_NAME_SQL_TYP = sql_field_type::TEXT;

    // comments used for the database creation
    const string TBL_COMMENT = 'to add a user given name using a 512-bit group id index for up to 16 32-bit phrase ids including the order';
    const string TBL_COMMENT_PRIME = 'to add a user given name using a 64-bit group id index for up to four 16-bit phrase ids including the order';
    const string TBL_COMMENT_INDEX = 'to add a user given name using a 64-bit group id index for one 32-bit and two 16-bit phrase ids including the order';
    const string TBL_COMMENT_BIG = 'to add a user given name using a group id index with a variable length for more than 16 32-bit phrase ids including the order';
    const string TBL_COMMENT_INDEX_BIG = 'to add a user given name using a 64-bit group id index for one 48-bit and one 16-bit phrase id including the order';

    // list of fields with parameters used for the database creation
    // the fields that can be changed by the user
    const array FLD_KEY_PRIME = array(
        [group_db::FLD_ID, sql_field_type::KEY_INT_NO_AUTO, sql_field_default::NOT_NULL, '', '', self::FLD_ID_COM],
    );
    const array FLD_KEY_PRIME_USER = array(
        [group_db::FLD_ID, sql_field_type::KEY_PART_INT, sql_field_default::NOT_NULL, '', '', self::FLD_ID_COM_USER],
    );
    const array FLD_LST_USER_CAN_CHANGE = array(
        [self::FLD_NAME, self::FLD_NAME_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_NAME_COM],
        [sql_db::FLD_DESCRIPTION, sql_db::FLD_DESCRIPTION_SQL_TYP, sql_field_default::NULL, '', '', sql_db::FLD_DESCRIPTION_COM],
    );

    // all database field names excluding the id
    const array FLD_NAMES = array(
        sql_db::FLD_DESCRIPTION
    );
    // list of fixed tables where a group name overwrite might be stored
    // TODO check if this can be used somewhere else means if there are unwanted repeating
    const array TBL_LIST = array(
        [sql_type::MOST],
        [sql_type::PRIME],
        [sql_type::BIG]
    );

}
