<?php

/*

  view_types.php - to link coded functionality to a view
  --------------
  
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

$view_types = [];
$view_types_hash = [];

class view_types extends user_type
{
    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test View Type';
    const TEST_TYPE = DBL_VIEW_TYPE_DEFAULT;
}

/**
 * reload the global view_types array from the database e.g. because a translation has changed
 */
function init_view_types($db_con): bool
{
    global $view_types;
    global $view_types_hash;

    $result = false;
    $typ_lst = new user_type_list();
    $view_types = $typ_lst->load_types(DB_TYPE_VIEW_TYPE, $db_con);
    $view_types_hash = $typ_lst->get_hash($view_types);

    if (count($view_types_hash) > 0) {
        $result = true;
    }
    return $result;
}

/**
 * create view type array for the unit tests without database connection
 */
function unit_text_init_view_types()
{
    global $view_types;
    global $view_types_hash;

    $view_types = array();
    $view_types_hash = array();
    $dsp_type = new view_types();
    $dsp_type->name = view_types::TEST_NAME;
    $dsp_type->code_id = view_types::TEST_TYPE;
    $view_types[1] = $dsp_type;
    $view_types_hash[view_types::TEST_TYPE] = 1;

}
