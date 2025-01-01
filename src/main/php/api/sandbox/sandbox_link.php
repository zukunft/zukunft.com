<?php

/*

    api/sandbox/sandbox_link.php - extends the superclass for link api objects with the predicate id
    ----------------------------


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

namespace controller\sandbox;

use api\sandbox\sandbox;

include_once API_SANDBOX_PATH . 'sandbox.php';

class sandbox_link extends sandbox
{

    // all link objects can have a connection type for predefined functionality to it
    public ?int $predicate_id;


    /*
     * construct and map
     */

    function __construct(int $id = 0, ?int $predicate_id = null)
    {
        parent::__construct($id);
        $this->set_predicate_id($predicate_id);
    }


    /*
     * set and get
     */

    function set_predicate_id(?int $predicate_id): void
    {
        $this->predicate_id = $predicate_id;
    }

    function predicate_id(): ?int
    {
        return $this->predicate_id;
    }

}


