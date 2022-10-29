<?php

/*

    phrase_group_link.php - the parent object for phrase_group_triple and phrase_group_triple_link
    ---------------------

    contains the common parts for the two phrase group link objects

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

class phrase_group_link
{

    // database fields
    public int $id;        // the primary database id of the numeric value, which is the same for the standard and the user specific value
    public int $grp_id;    // the phrase group id and not the object to reduce the memory usage

    function __construct()
    {
        $this->id = 0;
        $this->grp_id = 0;
    }

}
