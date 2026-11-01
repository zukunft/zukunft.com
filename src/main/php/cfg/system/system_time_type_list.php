<?php

/*

    model/system/system_time_type_list.php - the areas of the execution times e.g. a slow api request or one step of it
    --------------------------------------

    $sys_tim_typ_lst is the suggested var name

    loaded only when a slow request is written, so it is not part of the cached type lists

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;

class system_time_type_list extends type_list
{

    /**
     * fill the list from the code link csv for the unit tests without database
     */
    function load_dummy(): void
    {
        $this->reset();
        $this->read_from_config_csv($this);
    }

    /**
     * @return int the id of the area used for a step that has no own type yet
     */
    function default_id(): int
    {
        return parent::id(system_time_type::DEFAULT);
    }

}
