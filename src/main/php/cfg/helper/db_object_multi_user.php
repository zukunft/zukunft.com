<?php

/*

    model/helper/db_object_user.php - a base object for all user specific database objects
    -------------------------------

    same as db_id_object_user but for database object that have custom prime id
    TODO should be merged once php allows aggregating extends e.g. sandbox extends db_object, db_user_object


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

namespace cfg\helper;

include_once MODEL_HELPER_PATH . 'db_object_multi.php';
include_once MODEL_USER_PATH . 'user.php';

use cfg\helper\db_object_multi;
use cfg\user\user;

class db_object_multi_user extends db_object_multi
{

    /*
     * object vars
     */

    private user $usr; // the person for whom the object is loaded, so to say the viewer


    /*
     * construct and map
     */

    /**
     * @param user $usr the user how has requested to see his view on the object
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox object
     *
     * @param user $usr the person who wants to access the object e.g. the word
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user the person who wants to see a word, verb, triple, formula, view or result
     */
    function user(): user
    {
        return $this->usr;
    }

    /**
     * @return int the id of the user or 0 if the user is not set
     */
    function user_id(): int
    {
        return $this->usr->id();
    }


    /*
     * debug
     */

    /**
     * @returns string best possible identification for this object mainly used for debugging
     */
    function dsp_id_user(): string
    {
        global $debug;
        $result = '';
        if ($debug > DEBUG_SHOW_USER or $debug == 0) {
            if ($this->user() != null) {
                $result .= ' for user ' . $this->user()->id() . ' (' . $this->user()->name . ')';
            }
        }
        return $result;
    }

}
