<?php

/*

  formula_types.php - to link coded functionality to a formula
  -----------------
  
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

global $formula_types;

class formula_type_list extends user_type_list
{

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_FORMULA_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the formula types used for unit tests to the dummy list
     */
    function load_dummy()
    {
        parent::load_dummy();
        $type = new user_type();
        $type->name = formula::CALC;
        $type->code_id = formula::CALC;
        $this->lst[2] = $type;
        $this->hash[formula::CALC] = 2;
        $type = new user_type();
        $type->name = formula::REV;
        $type->code_id = formula::REV;
        $this->lst[3] = $type;
        $this->hash[formula::REV] = 3;
    }

    /**
     * return the database id of the default formula type
     */
    function default_id(): int
    {
        return parent::id(formula::CALC);
    }

}
