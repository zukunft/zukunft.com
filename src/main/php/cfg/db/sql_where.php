<?php

/*

    cfg/db/sql_where.php - structure for one where paramater for an sql statememnt
    --------------------


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

use DateTime;

class sql_where
{

    public string $tbl;  // the name or symbol of the table as used for the where condition
    public string $fld;  // the name of the field as used for the where condition
    public sql_par_type $typ;  // the type of the where condition e.g. =, IN, ...
    public sql_where_type $con;  // the type of the where condition e.g. AND, OR, ...
    public int $pos;  // the position in the parameter list

    function __construct()
    {
        $this->tbl = '';
        $this->fld = '';
        $this->con = sql_where_type::AND;
        $this->pos = 0;
    }

}

