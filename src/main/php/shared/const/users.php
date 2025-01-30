<?php

/*

    shared/const/users.php - users used by the system
    ----------------------


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\const;

class users
{

    // users used by the system
    // *_NAME the fixed name of system users
    // *_COM is the tooltip/description of the link to the external reference
    // *_IP the internet protocol address of one user for system testing
    // *_TYPE is the code_id of the user group
    // *_ID the fixed database due to the initial setup

    // system testing
    const TEST_NAME = 'standard user view for all users';
    const TEST_IP = '66.249.64.95'; // used to check the blocking of an IP address

}
