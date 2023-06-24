<?php

/*

    model/user/user_profile.php - a database based enum for the user profiles
    ---------------------------


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

class user_profile
{
    // list of the user profiles that have a coded functionality
    const NORMAL = "normal";
    const ADMIN = "admin";
    const DEV = "dev";       // reserved for developers which are supposed to code the verb functionality
    const TEST = "test";     // reserved for the system test user e.g. for internal unit and integration tests
    const SYSTEM = "system"; // reserved for the system user which is executing cleanup tasks

}