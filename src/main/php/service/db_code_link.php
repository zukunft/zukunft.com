<?php

/*

  db_code_link.php - class that links the upfront loaded type list
  ----------------

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

class db_cl
{
    // list of all user types that are used
    const WORD_TYPE = "word_type";
    const FORMULA_TYPE = "formula_type";
    const VIEW_TYPE = "view_type";
    const VIEW_COMPONENT_TYPE = "view_component_type";

    function word_type_id(string $code_id) {
        global $word_types;
        return $word_types->id($code_id);
    }

    function formula_type_id(string $code_id) {
        global $formula_types;
        return $formula_types->id($code_id);
    }

    function view_type_id(string $code_id) {
        global $view_types;
        return $view_types->id($code_id);
    }

    function view_component_type_id(string $code_id) {
        global $view_component_types;
        return $view_component_types->id($code_id);
    }

}


/**
 * get the database id of a predefined type e.g. word type, formula type, ...
 * shortcut name for db_code_link for better code reading
 *
 * @param string $type e.g. word_type or formulas_type to select the list of unique code ids
 * @param string $code_id the code id that must be unique within the given type
 * @return int the database prime key row id
 */
function cl(string $type, string $code_id): int
{
    $result = 0;
    $db_code_link = new db_cl();
    switch ($type) {
        case db_cl::WORD_TYPE:
            $result = $db_code_link->word_type_id($code_id);
            break;
        case db_cl::FORMULA_TYPE:
            $result = $db_code_link->formula_type_id($code_id);
            break;
        case db_cl::VIEW_TYPE:
            $result = $db_code_link->view_type_id($code_id);
            break;
        case db_cl::VIEW_COMPONENT_TYPE:
            $result = $db_code_link->view_component_type_id($code_id);
            break;
    }
    return $result;
}

