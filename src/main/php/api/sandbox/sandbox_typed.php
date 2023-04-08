<?php

/*

    api/sandbox/user_sandbox_named_with_type_api.php - extends the superclass for named api objects with the type id
    ------------------------------------------------


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

include_once API_SANDBOX_PATH . 'sandbox_named.php';

class sandbox_typed_api extends sandbox_named_api
{

    // all named objects can have a type that links predefined functionality to it
    // e.g. all value assigned with the percent word are per default shown as percent with two decimals
    // the frontend object just contains the id of the type
    // because the type can be fast selected from the preloaded type list
    public ?int $type_id;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null, ?int $type_id = null)
    {
        parent::__construct($id, $name);
        $this->set_type_id($type_id);
    }


    /*
     * set and get
     */

    function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }

}


