<?php

/*

    model/log/change_action_list.php - the const for the change log action table
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\log;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';

use cfg\helper\type_list;
use cfg\helper\type_object;
use shared\enum\change_actions;

class change_action_list extends type_list
{

    /**
     * adding the system log stati used for unit tests to the dummy list
     *  TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(change_actions::ADD, change_actions::ADD, '', 1);
        $this->add($type);
        $type = new type_object(change_actions::UPDATE, change_actions::UPDATE, '', 2);
        $this->add($type);
        $type = new type_object(change_actions::DELETE, change_actions::DELETE, '', 3);
        $this->add($type);
    }

    /**
     * return the database id of the default log type
     */
    function default_id(): int
    {
        return parent::id(change_actions::ADD);
    }

}