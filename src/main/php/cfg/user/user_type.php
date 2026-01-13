<?php

/*

    model/user/user_type.php - the superclass for word, formula and view types
    ------------------------

    the user type is the basic thrust level of a user similar to https://en.wikipedia.org/wiki/Wikipedia:User_groups#Registered_user_accounts

    the official type is used for the external thrust level of a user
    the user profile is used to define the coded functionality of a user similar to https://en.wikipedia.org/wiki/Wikipedia:User_groups#Table


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

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class user_type extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'for the user types e.g. to set the confirmation level of a user';

}