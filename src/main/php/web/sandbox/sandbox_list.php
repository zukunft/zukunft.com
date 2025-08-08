<?php

/*

    web/sandbox/sandbox_list.php - a base object for a list of user sandbox objects
    ----------------------------

    The main sections of this object are
    - object vars:       the variables of this list object
    - construct and map: including the mapping of the db rows to this list
    - set and get:       to capsule the vars from unexpected changes
    - load:              database access object (DAO) functions
    - im- and export:    create an export object and set the vars from an import object
    - modify:            change potentially all items of this list object
    - debug:             internal support functions for debugging


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

namespace html\sandbox;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'list_dsp.php';

class sandbox_list extends list_dsp
{

    /*
     * debug
     */

    /**
     * to show the list name to the user in the most simple form (without any ids)
     * this function is called from dsp_id, so no other call is allowed
     * e.g. >company Zurich< can be either >"company Zurich"< or >"company" "Zurich"<, means either a triple or two words
     *      but this "short" form probably confuses the user less and
     *      if the user cannot change the tags anyway the saving of a related value is possible
     *
     * @param ?int $limit the max number of ids to show
     * @return string a simple name of the list
     */
    function name(int $limit = null): string
    {
        return '"' . implode('","', $this->names(false, $limit)) . '"';
    }

    /**
     * @param ?int $limit the max number of ids to show
     * @return array with all names of the list
     */
    function names(int $limit = null): array
    {
        $result = [];
        $pos = 0;
        foreach ($this->lst() as $sbx_obj) {
            if ($pos <= $limit or $limit == null) {
                $result[] = $sbx_obj->name();
                $pos++;
            }
        }
        return $result;
    }

}
