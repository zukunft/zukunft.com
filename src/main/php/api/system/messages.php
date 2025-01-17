<?php

/*

    /api/system/messages.php - enum of the backend user message ids and the text in the default language
    ------------------------

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

namespace api\system;

enum messages: string
{

    // unique message key
    case IS_RESERVED = 'is_reserved';
    case RESERVED_NAME = 'reserved_name';
    case NOT_SIMILAR = 'not_similar';
    case RELOAD = 'reload';
    case OF_DEFAULT = 'of_default';
    case FAILED = 'failed';

    /**
     * @return string with the text for the user in the default language
     */
    public function text(): string
    {
        return match($this) {
            self::IS_RESERVED => 'is a reserved',
            self::RESERVED_NAME => 'name for system testing. Please use another name',
            self::NOT_SIMILAR => 'seems to be not similar to',
            self::RELOAD => 'reloading',
            self::OF_DEFAULT => 'of default',
            self::FAILED => 'failed',
            default => 'message id missing',
        };
    }

}