<?php

/*

    /model/dp/sql_field_default.php - enum of the sql default value types used
    -------------------------------

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

enum sql_field_default: string
{

    //
    case NOT_NULL = 'not_null'; //
    case NULL = 'null'; //
    case ZERO = '0'; //
    case ONE = '1'; //
    case TWO = '2'; //
    case TIMESTAMP = 'timestamp'; //
    case TIME_NOT_NULL = 'timestamp_not_null'; //

    public function pg_type(): string
    {
        return match($this) {
            self::NOT_NULL => 'NOT NULL',
            self::NULL => 'DEFAULT NULL',
            self::ZERO => 'DEFAULT 0',
            self::ONE => 'NOT NULL DEFAULT 1',
            self::TWO => 'NOT NULL DEFAULT 2',
            self::TIMESTAMP => 'DEFAULT CURRENT_TIMESTAMP',
            self::TIME_NOT_NULL => 'NOT NULL DEFAULT CURRENT_TIMESTAMP'
        };
    }
}