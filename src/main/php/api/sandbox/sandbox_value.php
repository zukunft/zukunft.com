<?php

/*

    api/user/sandbox_value.php - the minimal superclass for the frontend API
    --------------------------

    This superclass should be used by the classes word_min, formula_min, ... to enable user specific values and links


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

namespace api\sandbox;

include_once API_SANDBOX_PATH . 'sandbox.php';
include_once API_PHRASE_PATH . 'phrase_list.php';
include_once WEB_PHRASE_PATH . 'phrase_group.php';

use api\sandbox\sandbox as sandbox_api;

class sandbox_value extends sandbox_api
{

    private ?float $number; // the number calculated by the system


    /*
     * set and get
     */

    function id(): int|string
    {
        return $this->id;
    }

    function set_number(?float $number): void
    {
        $this->number = $number;
    }

    function number(): ?float
    {
        return $this->number;
    }

}


