<?php

/*

    model/user/user_profile.php - a database based enum for the user profiles
    ---------------------------


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

namespace cfg\user;

include_once MODEL_HELPER_PATH . 'type_object.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';

use cfg\helper\type_object;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class user_profile extends type_object
{
    // list of the user profiles that have a coded functionality
    const NORMAL = "normal";
    const ADMIN = "admin";
    const DEV = "dev";       // reserved for developers which are supposed to code the verb functionality
    const TEST = "test";     // reserved for the system test user e.g. for internal unit and integration tests
    const SYSTEM = "system"; // reserved for the system user which is executing cleanup tasks


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to define the user roles and read and write rights';

    // database fields additional to the standard field names
    const FLD_ID = 'user_profile_id';
    const FLD_LEVEL_COM = 'the access right level to prevent unpermitted right gaining';
    const FLD_LEVEL = 'right_level';

    // additional fieldss for the table creation of user profiles
    const FLD_LST_EXTRA = array(
        [self::FLD_LEVEL, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_LEVEL_COM],
    );

}