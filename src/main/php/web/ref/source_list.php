<?php

/*

    web/ref/source_list.php - create the HTML code to display a source list
    -----------------------

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

namespace Zukunft\ZukunftCom\main\php\web\ref;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SANDBOX . 'ListBase.php';
include_once html_paths::REF . 'source.php';
include_once html_paths::USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\web\sandbox\ListBase;
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user_message;

class source_list extends ListBase
{

    /*
     * set and get
     */

    /**
     * set the vars of a source object based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return parent::api_mapper_list($json_array, new source());
    }


    /*
     * load
     */

    /**
     * fill the list for a source selector: the sources that match the typed chars, or all sources of the
     * database if nothing is typed and the request cache has none, so that the selector is never empty
     * @param string $pattern the chars typed by the user to filter the sources, empty to offer all sources
     * @param user_message $msg with the requesting user for whom the sources are selected
     * @param bool $test_mode true to use only the cached sources, because a snapshot is created without a backend call
     */
    function load_for_selector(string $pattern, user_message $msg, bool $test_mode = false): void
    {
        if ($pattern != '') {
            $this->load_like($pattern, $msg);
        } elseif ($this->is_empty() and !$test_mode) {
            $this->load_like(self::PATTERN_ALL, $msg);
        }
    }

}
