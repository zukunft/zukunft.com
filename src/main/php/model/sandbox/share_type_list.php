<?php

/*

  share_type_list.php - a database based enum list for the data share types
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

global $share_types;

class share_type_list extends user_type_list
{
    // list of the ref types that have a coded functionality
    const DBL_PUBLIC = "public";
    const DBL_PERSONAL = "personal";
    const DBL_GROUP = "group";
    const DBL_PRIVATE = "private";

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_SHARE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy()
    {
        $this->lst = array();
        $this->hash = array();
        $type = new user_type();
        $type->name = share_type_list::DBL_PUBLIC;
        $type->code_id = share_type_list::DBL_PUBLIC;
        $this->lst[2] = $type;
        $this->hash[share_type_list::DBL_PUBLIC] = 2;
        $type = new user_type();
        $type->name = share_type_list::DBL_PERSONAL;
        $type->code_id = share_type_list::DBL_PERSONAL;
        $this->lst[3] = $type;
        $this->hash[share_type_list::DBL_PERSONAL] = 3;

    }

    /**
     * return the database id of the default share type
     */
    function default_id(): int
    {
        return parent::id(share_type_list::DBL_PUBLIC);
    }

}