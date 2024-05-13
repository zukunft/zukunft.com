<?php

/*

    cfg/db/sql_field_value.php - combine a sql parameter field name with the value and the parameter type
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

class sql_par_field
{

    public string $name;   // the name of the sql field
    public string|int|float|null $value;  // the value that should be used e.g. for a function
    public string|int|float|null $old;    // the value that is in the database until now
    public sql_par_type|string $type;   // the type of the sql field
    public string $par_name = '';   // the parameter name of the field used from the change log e.g. for the sql field name "new_from_id" the parameter name is "_new_phrase_id"

}

