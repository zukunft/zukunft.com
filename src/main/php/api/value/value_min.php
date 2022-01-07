<?php

/*

    value_min.php - the minimal value object
    -------------


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

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace value;

use phrase\phrase_group_min;
use sandbox\user_sandbox_min;

class value_min extends user_sandbox_min
{

    public phrase_group_min $grp; // the phrase group with the list of words and triples
    public float $val; // if the number for the phrase group is not set the object is not created, so null is not needed

    function __construct()
    {
        parent::__construct();
        $this->grp = new phrase_group_min();
        $this->val = 0;
    }

}
