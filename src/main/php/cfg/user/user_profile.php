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

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';

use cfg\helper\type_object;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use shared\enum\user_profiles;

class user_profile extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to define the user roles and read and write rights';

    // database fields additional to the standard field names
    const FLD_ID = 'user_profile_id';
    const FLD_LEVEL_COM = 'the access right level to prevent not permitted right gaining';
    const FLD_LEVEL = 'right_level';

    // additional fields for the table creation of user profiles
    const FLD_LST_EXTRA = array(
        [self::FLD_LEVEL, sql_field_type::INT_SMALL, sql_field_default::NULL, '', '', self::FLD_LEVEL_COM],
    );


    /*
     * info
     */

    function is_system(): bool
    {
        return $this->is_type(user_profiles::SYSTEM);
    }

}