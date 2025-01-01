<?php

/*

    web/sandbox/config.php - to cache and manage the user config in the frontend
    ----------------------

    This superclass should be used by the classes word_dsp, formula_dsp, ... to enable user specific values and links


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

namespace html\sandbox;

use html\phrase\phrase_list;
use html\user\user_message;
use shared\words;

class config
{

    // fallback config values e.g. if the backend connection is lost
    const ROW_LIMIT = 20;
    const DEFAULT_DEC_POINT = ".";
    const DEFAULT_PERCENT_DECIMALS = 2;

    function percent_decimals(): int
    {
        return DEFAULT_PERCENT_DECIMALS;
    }

    function dec_point(): string
    {
        return DEFAULT_DEC_POINT;
    }

    function thousand_sep(): string
    {
        return DEFAULT_THOUSAND_SEP;
    }

    /**
     * request the user specific frontend configuration from the backend
     * @return user_message if it fails the reason why
     */
    function load(): user_message
    {
        $usr_msg = new user_message();
        return $usr_msg;
    }

    /**
     * get a frontend config value selected by the phrase names
     * @param array $names with the phrase names to select the config value
     * @return int|float|string|null with the user specific config value
     */
    function get(array $names): int|float|string|null
    {
        $phr_lst = new phrase_list();
        $val = null;
        switch ($names) {
            case [words::PERCENT, words::DECIMAL]:
                $val = self::DEFAULT_PERCENT_DECIMALS;
                break;
            case [words::ROW, words::LIMIT]:
                $val = self::ROW_LIMIT;
                break;
            case [words::DECIMAL, words::POINT]:
                $val = self::DEFAULT_DEC_POINT;
        }
        return $val;
    }

}


