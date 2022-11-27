<?php

/*

    object_type.php - a base type object that can be used to link program code to single objects
    ---------------

    e.g. if a value is classified by a phrase of type percent the value by default is formatted in percent


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

namespace cfg;

class object_type
{

    public int $id;
    public string $name;
    public string $code_id;

    /*
     * construct and map
     */

    function __construct(?string $code_id, int $id = 0, string $name = '')
    {
        $this->id = $id;
        $this->name = $name;
        $this->code_id = $code_id;
    }

    /*
     * set and get
     */

    function id(): int
    {
        return $this->id;
    }

    function name(): string
    {
        return $this->name;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    /*
     * information
     */

    function is_type(string $type_to_check): bool
    {
        if ($this->code_id == $type_to_check) {
            return true;
        } else {
            return false;
        }
    }

}
