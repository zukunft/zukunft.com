<?php

/*

  view_component_type_list.php - to link coded functionality to a view component
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

global $view_component_types;

class view_component_type_list extends user_type_list
{
    // list of the view component types that have a coded functionality
    const DBL_TEXT = "text";
    const DBL_WORD = "fixed";
    const DBL_WORD_SELECT = "word_select";
    const DBL_WORDS_UP = "word_list_up";
    const DBL_WORDS_DOWN = "word_list_down";
    const DBL_WORD_NAME = "word_name";
    const DBL_WORD_VALUE = "word_value_list";
    const DBL_VALUES_ALL = "values_all";
    const DBL_VALUES_RELATED = "values_related";
    const DBL_FORMULAS = "formula_list";
    const DBL_FORMULA_RESULTS = "formula_results";
    const DBL_JSON_EXPORT = "json_export";
    const DBL_XML_EXPORT = "xml_export";
    const DBL_CSV_EXPORT = "csv_export";
    const DBL_VIEW_SELECT = "view_select";
    const DBL_LINK = "link";

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = DB_TYPE_VIEW_COMPONENT_TYPE): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy() {
        parent::load_dummy();
        $type = new user_type();
        $type->name = view_component_type_list::DBL_TEXT;
        $type->code_id = view_component_type_list::DBL_TEXT;
        $this->lst[2] = $type;
        $this->hash[view_component_type_list::DBL_TEXT] = 2;
    }

    /**
     * return the database id of the default view component type
     */
    function default_id(): int
    {
        return parent::id(view_component_type_list::DBL_TEXT);
    }

}
