<?php

/*

    api/system/type_lists.php - the simple export object to create a json for the frontend API
    -------------------------

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

namespace api;

use api_message;
use sql_db;
use JsonSerializable;

class type_lists_api extends api_message implements JsonSerializable
{

    // parent object for all preloaded types
    public ?array $type_lists = null;      // a list of system error objects

    function __construct(sql_db $db_con)
    {
        parent::__construct($db_con, 'type_lists');
        $this->type = api_message::TYPE_LISTS;
    }

    function add(type_list_api $lst_to_add, string $api_name): void
    {
        $this->type_lists[$api_name] = $lst_to_add;
    }

    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $json = [];
        $json[] = json_decode(json_encode((array)$this));
        return $json;
    }


}
