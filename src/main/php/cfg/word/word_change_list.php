<?php

/*

    model/word/word_change_list.php - a list of word where the user has changed the standard
    -------------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

use cfg\db\sql_db;
use cfg\db\sql_par;

class word_change_list
{
    // array of the loaded word objects
    public array $lst;
    private user $usr;    // the user object of the person for whom the word list is loaded, so to say the viewer


    /*
     * set and get
     */

    /**
     * set the user of the word change list
     *
     * @param user $usr the person who wants to access the words
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see the words
     */
    function user(): user
    {
        return $this->usr;
    }

    /*
     * load functions
     */

    /**
     * set the SQL query parameters to load a list of words where
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_class(word::class);
        $qp = new sql_par(self::class);
        $db_con->set_name($qp->name); // assign incomplete name to force the usage of the user as a parameter
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(word::FLD_NAMES);
        $db_con->set_usr_fields(word::FLD_NAMES_USR);
        $db_con->set_usr_num_fields(word::FLD_NAMES_NUM_USR);
        $db_con->set_order_text(sql_db::STD_TBL . '.' . $db_con->name_sql_esc(word::FLD_VALUES) . ' DESC, ' . word::FLD_NAME);
        return $qp;
    }

}
