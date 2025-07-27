<?php

/*

    model/user/user_type.php - the superclass for word, formula and view types
    ------------------------

    types are used to assign coded functionality to a word, formula or view
    a user can create a new type to group words, formulas or views and request new functionality for the group
    types can be renamed by a user and the user change the comment
    it should be possible to translate types on the fly
    on each program start the types are loaded once into an array, because they are not supposed to change during execution


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

namespace cfg\user;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use cfg\helper\type_object;

class user_type extends type_object
{

    // list of the user types that have a coded functionality
    const GUEST = "Guest"; // a read only access
    const IP_ADDR = "IP address"; // identified only by IP address
    const VERIFIED = "Verified"; // verified by email or mobile
    const SECURED = "Secured"; // verified with a high security e.g. via passport of a trusted country

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the user types e.g. to set the confirmation level of a user';

}