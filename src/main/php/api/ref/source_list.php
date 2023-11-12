<?php

/*

    api/ref/source_list.php - a list object of minimal/api source objects
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

namespace api\ref;

include_once API_SANDBOX_PATH . 'list.php';
include_once API_REF_PATH . 'source.php';
include_once API_REF_PATH . 'source_list.php';
include_once WEB_REF_PATH . 'source_list.php';

use api\sandbox\list_api;
use JsonSerializable;

class source_list extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a source to the list
     * @returns bool true if the source has been added
     */
    function add(source_api $src): bool
    {
        return parent::add_obj($src);
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
     * an array of the value vars including the protected vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $src) {
            $vars[] = $src->jsonSerialize();
        }
        return $vars;
    }


}
