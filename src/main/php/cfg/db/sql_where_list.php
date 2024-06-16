<?php

/*

    cfg/db/sql_where_list.php - list to create the sql where condition
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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

class sql_where_list
{

    public array $lst = [];  // a list of sql parameter fields

    /**
     * add a field to the list
     * entries with an empty names are allowed e.g. for the sql function Now() that needs no parameter
     *
     * @param sql_where|null $fld the field to add with at least the name set
     * @return void
     */
    function add(?sql_where $fld): void
    {
        $this->lst[] = $fld;
    }

}
