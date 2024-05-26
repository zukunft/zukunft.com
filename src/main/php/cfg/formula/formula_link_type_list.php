<?php

/*

    model/formula/formula_link_type_list.php - to link coded functionality to a formula link
    ----------------------------------------

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

namespace cfg;

use test\create_test_objects;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_FORMULA_PATH . 'formula_link.php';

global $formula_link_types;

class formula_link_type_list extends type_list
{

    /**
     * adding the formula link types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $t = new create_test_objects();
        $t->read_from_config_csv($this);
    }

    /**
     * return the database id of the default formula link type
     */
    function default_id(): int
    {
        return parent::id(formula_link::DEFAULT);
    }

}
