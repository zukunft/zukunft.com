<?php

/*

  view_component_types.php - to link coded functionality to a view component
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

$view_component_types = [];
$view_component_types_hash = [];

class view_component_type extends user_type
{
    // persevered view name for unit and integration tests
    const TEST_NAME = 'System Test View Component Type';
    const TEST_TYPE = DBL_VIEW_COMP_TYPE_TEXT;
}

/**
 * reload the global view_component_types array from the database e.g. because a translation has changed
 */
function init_view_component_types($db_con)
{
    global $view_component_types;
    global $view_component_types_hash;

    $db_con->set_type(DB_TYPE_VIEW_COMPONENT_TYPE);
    $db_con->set_fields(array('description', 'code_id'));
    $sql = $db_con->select();
    $db_lst = $db_con->get($sql);
    $view_component_types = array();
    $view_component_types_hash = array();
    foreach ($db_lst as $db_entry) {
        $dsp_cmp_type = new view_component_type();
        $dsp_cmp_type->name = $db_entry['view_type_name'];
        $dsp_cmp_type->comment = $db_entry['description'];
        $dsp_cmp_type->code_id = $db_entry['code_id'];
        $view_component_types[$db_entry['view_type_id']] = $dsp_cmp_type;
        $view_component_types_hash[$db_entry['code_id']] = $db_entry['view_type_id'];
    }

}

/**
 * create view component type array for the unit tests without database connection
 */
function unit_text_init_view_component_types()
{
    global $view_component_types;
    global $view_component_types_hash;

    $view_component_types = array();
    $view_component_types_hash = array();
    $dsp_type = new view_component_type();
    $dsp_type->name = view_component_type::TEST_NAME;
    $dsp_type->code_id = view_component_type::TEST_TYPE;
    $view_component_types[1] = $dsp_type;
    $view_component_types_hash[view_component_type::TEST_TYPE] = 1;

}
