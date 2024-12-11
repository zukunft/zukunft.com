<?php

/*

    model/view/view_style_list.php - to define the view or component style e.g. the number of columns to use
    ------------------------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace cfg\component;

include_once SHARED_TYPES_PATH . 'component_type.php';
include_once DB_PATH . 'sql_db.php';
include_once MODEL_COMPONENT_PATH . 'component_type.php';
include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';

use shared\types\view_styles;
use cfg\type_list;
use cfg\type_object;

global $msk_sty_cac;

class view_style_list extends type_list
{

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy(): void {
        parent::load_dummy();
        foreach (view_styles::TEST_TYPES as $cmp_typ) {
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
        return parent::id(view_styles::SM_COL_4);
    }

}
