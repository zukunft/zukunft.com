<?php

/*

    web/helper/list_size.php - the configured number of entries of the short and the more version of a list
    ------------------------

    The number of entries comes from config.yaml via the request cache $ui_sys->cfg; the consts of
    shared/helper/Config.php are only the fallback if the config is not loaded (see docs/llm/frontend.md
    "Short, more and all"). A class of its own, so that every list (e.g. value_list and type_list) reads the
    same keys without including the frontend config, which extends the value list.
    var name: none, because only the static functions are used


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\web\helper;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SHARED_CONST . 'words.php';
include_once html_paths::SHARED_HELPER . 'Config.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\helper\Config;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class list_size
{

    /**
     * the number of entries of the short version of a list e.g. the rows of a value table
     * @param user_message $msg to report a problem of reading the config
     * @return int config.yaml "select > initial > entries" or Config::LIMIT_SHORT_LIST if the config is not loaded
     */
    static function short_list(user_message $msg): int
    {
        return self::configured([words::ENTRIES, words::INITIAL, words::SELECT], Config::LIMIT_SHORT_LIST, $msg);
    }

    /**
     * the number of entries of the more version of a list
     * @param user_message $msg to report a problem of reading the config
     * @return int config.yaml "select > more > entries" or Config::LIMIT_MORE_LIST if the config is not loaded
     */
    static function more_list(user_message $msg): int
    {
        return self::configured([words::ENTRIES, words::MORE, words::SELECT], Config::LIMIT_MORE_LIST, $msg);
    }

    /**
     * @param array $names the config keys from the most specific to the most general e.g. entries, initial, select
     * @param int $fallback the number used if the config of the request is not loaded e.g. in a unit test
     * @param user_message $msg to report a problem of reading the config
     * @return int the configured number of entries
     */
    private static function configured(array $names, int $fallback, user_message $msg): int
    {
        global $ui_sys;
        $result = $fallback;
        if ($ui_sys?->cfg !== null) {
            $result = (int)$ui_sys->cfg->get_by($names, $msg, $fallback);
        }
        return $result;
    }

}
