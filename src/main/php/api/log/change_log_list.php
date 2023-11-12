<?php

/*

    api/log/change_log_list.php - a list changes that can be shown in the frontend
    ---------------------------


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

namespace api\log;

use api\sandbox\list_api;
use JsonSerializable;

class change_log_list_api extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a change log entry to the list
     * @param change_log_named_api $chg one change of a user sandbox object
     * @returns bool true if the log entry has been added
     */
    function add(change_log_named_api $chg): bool
    {
        return list_api::add_obj($chg);
    }

    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $chg) {
            $vars[] = json_decode(json_encode($chg));
        }
        return $vars;
    }

}
