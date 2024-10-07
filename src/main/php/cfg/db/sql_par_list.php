<?php

/*

    cfg/db/sql_par_list.php - a list of sql parameters and calls
    -----------------------

    The list of sql calls with the related parameters are used for block writes to the database


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

use cfg\combine_named;
use cfg\db_object_seq_id;
use cfg\log\change;
use cfg\sandbox;
use cfg\sandbox_link_named;
use cfg\sandbox_multi;
use cfg\sandbox_named;
use cfg\type_list;
use cfg\type_object;
use cfg\user;
use cfg\user_message;
use DateTime;
use DateTimeInterface;
use shared\library;

class sql_par_list
{

    public array $lst = [];  // a list of sql parameters and calls

    /**
     * add a sql call with the parameters to the list
     *
     * @param sql_par|null $par the sql call with the parameters for the sql function call
     * @return void
     */
    function add(?sql_par $par): void
    {
        $this->lst[] = $par;
    }

    /**
     * @return array with the field names of the list
     */
    function names(): array
    {
        $result = [];
        foreach ($this->lst as $fld) {
            $result[] = $fld->name;
        }
        return $result;
    }

    /**
     * @return int get the number of named parameters (excluding the const like Now())
     */
    function count(): int
    {
        return count($this->names());
    }

    /**
     * @return user_message with the parameter names formatted for sql
     */
    function exe(): user_message
    {
        $lib = new library();
        return $lib->sql_array($this->names_or_const(), ' ', ' ');
    }

}

