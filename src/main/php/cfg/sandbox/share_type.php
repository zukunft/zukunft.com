<?php

/*

    model/sandbox/share_type.php - to define if an object can be shared between the users
    ----------------------------

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

namespace cfg\sandbox;

include_once MODEL_HELPER_PATH . 'type_object.php';

use cfg\helper\type_object;

class share_type extends type_object
{

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'for the read access control';
    const FLD_NAME_COM = 'the name of the share type as displayed for the user';
    const FLD_DESCRIPTION_COM = 'to explain the code action of the share type';

}
