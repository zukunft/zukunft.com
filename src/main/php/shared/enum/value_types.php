<?php

/*

    shared/enum/value_types.php - enum of the value types seperated in different tables
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\enum;

use cfg\db\sql_type;

enum value_types: string
{
    case NUMBER = 'number';
    case TEXT = 'text';
    case TIME = 'time';
    case GEO = 'geo';

    // get the table extension
    public function table_extension(): string
    {
        return match($this) {
            value_types::NUMBER => '',
            default => $this->value,
        };
    }

    // get the extension to make queries unique
    public function query_extension(): string
    {
        return match($this) {
            value_types::NUMBER => 'n',
            value_types::TEXT => 't',
            value_types::TIME => 'i',
            value_types::GEO => 'g',
        };
    }

    public function sql_type(): sql_type
    {
        return match($this) {
            value_types::NUMBER => sql_type::NUMERIC,
            value_types::TEXT => sql_type::TEXT,
            value_types::TIME => sql_type::TIME,
            value_types::GEO => sql_type::GEO,
        };
    }

}