<?php

/*

    model/user/user_profile_list.php - a list of possible user profiles with the database id
    --------------------------------


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

namespace cfg;

global $user_profiles;

use cfg\db\sql_db;
use cfg\user\user_profile;

class user_profile_list extends type_list
{

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_USER_PROFILE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->reset();
        $type = new type_object(user_profile::NORMAL, user_profile::NORMAL, '', 2);
        $this->add($type);
        $type = new type_object(user_profile::ADMIN, user_profile::ADMIN, '', 3);
        $this->add($type);
    }

    /**
     * return the database id of the default user profile
     */
    function default_id(): int
    {
        return parent::id(user_profile::NORMAL);
    }

}