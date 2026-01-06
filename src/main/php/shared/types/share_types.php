<?php

/*

    shared/types/share_types.php - to define if an object can be shared between the users
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

namespace Zukunft\ZukunftCom\main\php\shared\types;

class share_types
{

    // list of the ref types that have a coded functionality
    const string PUBLIC = "public";
    const int PUBLIC_ID = 1;
    const string PERSONAL = "personal";
    const string GROUP = "group";
    const string PRIVATE = "private";
    const string PERSONAL_LOG = "personal_log";
    const string GROUP_LOG = "group_log";
    const string PRIVATE_LOG = "private_log";

}
