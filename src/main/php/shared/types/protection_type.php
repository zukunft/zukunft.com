<?php

/*

    model/sandbox/protection_type.php - to define if and how an object can changed
    ---------------------------------

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

namespace shared\types;

class protection_type
{
    // the field name used for the JSON im- and export
    const JSON_FLD = 'protection';

    // list of the protection types that have a coded functionality
    const NO_PROTECT = "no_protection";
    const USER = "user_protection";
    const ADMIN = "admin_protection";
    const ADMIN_ID = 3;
    const NO_CHANGE = "no_change";

}
