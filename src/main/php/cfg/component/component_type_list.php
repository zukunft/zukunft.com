<?php

/*

    model/view/component_type_list.php - to link coded functionality to a view component
    ---------------------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\component;

include_once SHARED_TYPES_PATH . 'component_type.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_COMPONENT_PATH . 'component_type.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

use shared\types\component_type as comp_type_shared;
use cfg\helper\type_list;
use cfg\helper\type_object;

class component_type_list extends type_list
{

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        foreach (comp_type_shared::TEST_TYPES as $cmp_typ) {
            $code_id = $cmp_typ[0];
            $id = $cmp_typ[1];
            $type = new type_object($code_id, $code_id, '', $id);
            $this->add($type);
        }
    }

    /**
     * return the database id of the default view component type
     */
    function default_id(): int
    {
        return parent::id(comp_type_shared::TEXT);
    }

}
