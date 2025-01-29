<?php

/*

    api/phrase/phrase_list.php - a list object of minimal/api phrase objects
    --------------------------


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

namespace api\phrase;

include_once API_SANDBOX_PATH . 'list_object.php';
include_once WEB_PHRASE_PATH . 'phrase_list.php';

use api\sandbox\list_object as list_api;
use html\phrase\phrase_list as phrase_list_dsp;
use JsonSerializable;

class phrase_list extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }



    /*
     * interface
     */

    /**
     * @return string the json api message as a text string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the value vars including the protected vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $phr) {
            $vars[] = $phr->jsonSerialize();
        }
        return $vars;
    }


    /*
     * info
     */

    /**
     * @return bool true if one of the phrases is of type percent
     */
    function has_percent(): bool
    {
        $result = false;
        foreach ($this->lst() as $phr) {
            if ($phr->is_percent()) {
                $result = true;
            }
        }
        return $result;
    }


}
