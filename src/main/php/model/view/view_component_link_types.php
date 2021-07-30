<?php

/*

  view_component_link_types.php - to define the behaviour if a component is linked to a view
  -----------------------------
  
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

$view_component_link_types = [];
$view_component_link_types_hash = [];

class view_component_link_types extends user_type
{
    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test View Component Link Type';
    const TEST_TYPE = DBL_VIEW_COMP_TYPE_TEXT;
}

/**
 * reload the global view_component_link_types array from the database e.g. because a translation has changed
 */
function init_view_component_link_types($db_con): bool
{
    global $view_component_link_types;
    global $view_component_link_types_hash;

    $result = false;
    $typ_lst = new user_type_list();
    $view_component_link_types = $typ_lst->load_types(DB_TYPE_VIEW_COMPONENT_LINK_TYPE, $db_con);
    $view_component_link_types_hash = $typ_lst->get_hash($view_component_link_types);
    if (count($view_component_link_types_hash) > 0) {
        $result = true;
    }
    return $result;

}

/**
 * create view component link type array for the unit tests without database connection
 */
function unit_text_init_view_component_link_types()
{
    global $view_component_link_types;
    global $view_component_link_types_hash;

    $view_component_link_types = array();
    $view_component_link_types_hash = array();
    $dsp_lnk_type = new view_component_link_types();
    $dsp_lnk_type->name = view_component_link_types::TEST_NAME;
    $dsp_lnk_type->code_id = view_component_link_types::TEST_TYPE;
    $view_component_link_types[1] = $dsp_lnk_type;
    $view_component_link_types_hash[view_component_link_types::TEST_TYPE] = 1;

}
