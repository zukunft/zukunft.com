<?php

/*

    model/sandbox/share_type_list.php - a database based enum list for the data share types
    ---------------------------------


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

namespace cfg\sandbox;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::SHARED_TYPES . 'share_type.php';
include_once paths::MODEL_SANDBOX . 'share_type.php';

use cfg\helper\type_list;
use shared\types\share_type as share_type_shared;

class share_type_list extends type_list
{

    /**
     * create dummy type list for the unit tests without database connection
     */
    function load_dummy(): void
    {
        $this->reset();
        // read the corresponding names and description from the internal config csv files
        $this->read_from_config_csv($this);
    }

    /**
     * return the database id of the default share type
     */
    function default_id(): int
    {
        return parent::id(share_type_shared::PUBLIC);
    }

}