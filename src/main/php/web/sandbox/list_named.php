<?php

/*

    web/sandbox/list_value.php - add name function to the frontend list object
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

namespace html\sandbox;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::SANDBOX . 'list_dsp.php';
include_once html_paths::PHRASE . 'phrase_list.php';

use html\phrase\phrase_list as phrase_list_dsp;

class list_named extends list_dsp
{

    /*
     * base
     */

    /**
     * @param string $back the back trace url for the undo functionality
     * @return string with a list of the word names with html links
     * ex. names_linked
     */
    function name_link(string $back = ''): string
    {
        return implode(', ', $this->names_linked($back));
    }

    /**
     * @param string $back the back trace url for the undo functionality
     * @return array with a list of the word names with html links
     */
    function names_linked(string $back = ''): array
    {
        $result = array();
        foreach ($this->lst() as $wrd) {
            if (!$wrd->is_hidden()) {
                $result[] = $wrd->name_link($back);
            }
        }
        return $result;
    }

    /**
     * to show the list name to the user in the most simple form (without any ids)
     * this function is called from dsp_id, so no other call is allowed
     *
     * @param ?int $limit the max number of ids to show
     * @return string a simple name of the list
     */
    function name_tip(int $limit = null): string
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
        foreach ($this->lst() as $sbx) {
            if ($pos <= $limit or $limit == null) {
                $result[] = $sbx->name_tip();
                $pos++;
            }
        }
        return $result;
    }

}
