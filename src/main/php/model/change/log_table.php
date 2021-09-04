<?php

/*

  model/change/log_table.php - to link coded functionality to a change log table
  --------------------------
  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $change_log_tables;

class change_log_table extends user_type_list
{
    // list of change log table with linked functionalities
    const USR = "users";
    const VALUE = "values";
    const VALUE_USR = "user_values";
    const VALUE_LINK = "value_links";
    const WORD = "words";
    const WORD_USR = "user_words";
    const WORD_LINK = "word_links";
    const WORD_LINK_USR = "user_word_links";
    const FORMULA = "formulas";
    const FORMULA_USR = "user_formulas";
    const FORMULA_LINK = "formula_links";
    const FORMULA_LINK_USR = "user_formula_links";
    const VIEW = "views";
    const VIEW_USR = "user_views";
    const VIEW_LINK = "view_component_links";
    const VIEW_LINK_USR = "user_view_component_links";
    const VIEW_COMPONENT = "view_components";
    const VIEW_COMPONENT_USR = "user_view_components";

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_CHANGE_LOG_TABLE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy() {
        parent::load_dummy();
        $type = new user_type();
        $type->name = change_log_table::VALUE;
        $type->code_id = change_log_table::VALUE;
        $this->lst[2] = $type;
        $this->hash[change_log_table::VALUE] = 2;
    }

    /**
     * return the database id of the default view type
     */
    function default_id(): int
    {
        return parent::id(change_log_table::VALUE);
    }

}