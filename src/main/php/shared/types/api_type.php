<?php

/*

    shared/types/api_type.php - options of the api messages
    -------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace shared\types;

enum api_type: string
{

    // include the phrases in the value list api message
    case INCL_PHRASES = 'incl_phrases';

    // do not fill up the group id to the full key length
    case NO_KEY_FILL = 'no_key_fill';

    // internal parameter for unit testing to switch off the database loading of missing objects
    case TEST_MODE = 'test_mode';

    // include the message header
    case HEADER = 'header';

}