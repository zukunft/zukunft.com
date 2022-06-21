<?php

/*

    user_sandbox_named_min.php - extends the minimal superclass for named objects such as formulas
    --------------------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

class user_sandbox_named_min extends user_sandbox_min
{

    // the unique name of the object that is shown to the user
    // the name must always be set
    protected string $name;

    // all named objects can have a type that links predefineed functionality to it
    // e.g. all value assinged with the percent word are per default shown as percent with two decimals
    protected string $type;


    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id);
        $this->name = '';

        // set also the name if included in new call
        if ($name <> '') {
            $this->name = $name;
        }

    }

    public function set_name(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

}


