<?php

/*

    model/db/sql_par_list.php - a list of sql parameters and calls
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

include_once MODEL_USER_PATH . 'user_message.php';

use cfg\user\user_message;

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
        foreach ($this->lst as $sql_par) {
            if (!in_array($sql_par->name, $result)) {
                $result[] = $sql_par->name;
            }
        }
        return $result;
    }

    /**
     * @return array with the names of the objects that have first requested the sql parameters
     */
    function object_names(): array
    {
        $result = [];
        foreach ($this->lst as $sql_par) {
            if (!in_array($sql_par->obj_name, $result)) {
                $result[] = $sql_par->obj_name;
            }
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
        global $db_con;

        $usr_msg = new user_message();

        foreach ($this->lst as $qp) {
            $usr_msg->add($db_con->insert($qp, 'add word from list'));
        }
        return $usr_msg;
    }


    /*
     * filter
     */

    /**
     * get the sql parameters that are not yet in the database
     *
     * @param array $db_func_names with the function names that are in the database
     * @return sql_par_list with the missing sql function names
     */
    function sql_functions_missing(array $db_func_names): sql_par_list
    {
        $result = new sql_par_list();
        foreach ($this->lst as $qp) {
            if (!in_array($qp->name, $db_func_names)) {
                $result->add($qp);
            }
        }
        return $result;
    }
}

