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

namespace cfg\user;

include_once MODEL_HELPER_PATH . 'type_list.php';

use cfg\helper\type_list;

class user_profile_list extends type_list
{

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
    }

    /**
     * return the database id of the default user profile
     */
    function default_id(): int
    {
        return parent::id(user_profile::NORMAL);
    }

}