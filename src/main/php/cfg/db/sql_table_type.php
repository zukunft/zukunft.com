<?php

/*

    /model/dp/sql_table_type.php - enum of the sql table extension type for value and result tables
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\db;

enum sql_table_type: string
{

    // the fixed table types for a value or result
    case PRIME = 'prime'; // up to four 16-bit phrase ids
    case MOST = 'most'; // up to 16 64-bit phrase ids
    case BIG = 'big'; // more than 16 64-bit phrase ids
    case INDEX = 'index'; // one 32-bit and two 16-bit phrase ids
    case LARGE = 'large'; // one 48-bit and one 16-bit phrase ids

    // the fixed table subtypes
    case STANDARD = 'standard'; // value or result that is public and unprotected
    case USER = 'user'; // for user specific values and results

    public function extension(): string
    {
        return match($this) {
            self::PRIME => '_prime',
            self::BIG => '_big',
            self::INDEX => '_index',
            self::LARGE => '_large',
            default => '',
        };
    }
}