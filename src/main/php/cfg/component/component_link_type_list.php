<?php

/*

    model/view/component_link_type_list.php - to define the behaviour if a component is linked to a view
    ---------------------------------------

    TODO check if really needed

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

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::DB . 'sql_db.php';

use cfg\helper\type_list;

class component_link_type_list extends type_list
{

    /**
     * adding the view component position types used for unit tests to the dummy list
     */
    function load_dummy(): void {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
    }

}
