<?php

/*

  protection_type_list.php - a database based enum list for the data protection types
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $protection_types;

use cfg\protection_type;

class protection_type_list extends user_type_list
{

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_PROTECTION): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->lst = array();
        $this->hash = array();
        $type = new user_type(protection_type::NO_PROTECT, protection_type::NO_PROTECT);
        $this->lst[2] = $type;
        $this->hash[protection_type::NO_PROTECT] = 2;
        $type = new user_type(protection_type::ADMIN, protection_type::ADMIN);
        $this->lst[3] = $type;
        $this->hash[protection_type::ADMIN] = 3;

    }

    /**
     * return the database id of the default protection type
     */
    function default_id(): int
    {
        return parent::id(protection_type::NO_PROTECT);
    }

}