<?php

/*

    cfg/element/element_type_list.php - to link coded functionality to a formula element type
    ---------------------------------

    TODO review and merge with parameter_type because this will never change, so a fixed enum can be used

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

namespace cfg;

include_once MODEL_ELEMENT_PATH . 'element_type.php';

use cfg\db\sql_db;
use test\create_test_objects;

global $element_types;

class element_type_list extends type_list
{

    /**
     * adding the view component types used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $t = new create_test_objects();
        $t->read_from_config_csv($this);
    }

    /**
     * return the database id of the default formula element type
     */
    function default_id(): int
    {
        return parent::id(element_type::WORD_SELECTOR);
    }

}
