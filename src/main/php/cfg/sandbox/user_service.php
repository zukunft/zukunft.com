<?php

/*

    cfg\sandbox\user_service.php - that parent object for user specific services
    ----------------------------

    e.g. used for the im- and export processes


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

namespace cfg\sandbox;

use cfg\const\paths;

include_once paths::EXPORT . 'export.php';
include_once paths::MODEL_USER . 'user.php';

use cfg\export\export;
use cfg\user\user;

class user_service
{

    /*
     * object vars
     */

    private user $usr; // the user who has requested the service

    /**
     * set the user that has requested service process
     * @param user $usr who started e.g. the export
     */
    function __construct(user $usr)
    {
        $this->set_user($usr);
    }


    /*
     * set and get
     */

    /**
     * set the user of the user sandbox service
     *
     * @param user $usr the person who wants to access the objects e.g. the word
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

}

