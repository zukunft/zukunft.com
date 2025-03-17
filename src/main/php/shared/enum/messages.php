<?php

/*

    shared/enum/messages.php - enum of the user message ids and the text in the default language
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\enum;

enum messages: string
{

    // start and end maker for message id within a text to allow changing the order of vars within a message
    const VAR_START = '<!mid';
    const VAR_END = '!>';

    // unique message keys
    // *_txt sample translation to test the English mapping
    case IS_RESERVED = 'is_reserved';
    case IS_RESERVED_TXT = 'is a reserved';
    case RESERVED_NAME = 'reserved_name';
    case NOT_SIMILAR = 'not_similar';
    case RELOAD = 'reload';
    case OF_DEFAULT = 'of_default';
    case FAILED = 'failed';
    case DONE = 'done';

    // for the change log
    case LOG_ADD = 'added';
    case LOG_UPDATE = 'changed';
    case LOG_DEL = 'deleted';
    case LOG_LINK = 'linked';
    case LOG_TO = 'to';

    // IP filter
    case IP_BLOCK_PRE_ADDR = 'ip_block_pre_addr';
    case IP_BLOCK_POST_ADDR = 'ip_block_post_addr';
    case IP_BLOCK_SOLUTION = 'ip_block_solution';


    /**
     * @return string with the text for the user in the default language
     */
    public function text(string $lan = ''): string
    {
        global $mtr;
        if ($lan == language_codes::SYS) {
            if ($mtr->has($this)) {
                return $mtr->txt($this);
            } else {
                return $this->value;
            }
        } else {
            return $mtr->txt($this);
        }

    }

}