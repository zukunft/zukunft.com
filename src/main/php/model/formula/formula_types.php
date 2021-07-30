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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

$formula_types = [];
$formula_types_hash = [];

class formula_types extends user_type
{
    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test Formula Type';
    const TEST_TYPE = DBL_FORMULA_TYPE_CALC;
}

/**
 * reload the global formula_types array from the database e.g. because a translation has changed
 */
function init_formula_types($db_con)
{
    global $formula_types;
    global $formula_types_hash;

    $result = false;
    $typ_lst = new user_type_list();
    $formula_types = $typ_lst->load_types(DB_TYPE_FORMULA_TYPE, $db_con);
    $formula_types_hash = $typ_lst->get_hash($formula_types);
    if (count($formula_types_hash) > 0) {
        $result = true;
    }
    return $result;

}

/**
 * create formula type array for the unit tests without database connection
 */
function unit_text_init_formula_types()
{
    global $formula_types;
    global $formula_types_hash;

    $formula_types = array();
    $formula_types_hash = array();
    $frm_type = new formula_types();
    $frm_type->name = formula_types::TEST_NAME;
    $frm_type->code_id = formula_types::TEST_TYPE;
    $formula_types[1] = $frm_type;
    $formula_types_hash[formula_types::TEST_TYPE] = 1;

}
