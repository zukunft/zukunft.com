<?php

/*

    cfg/element/element_group_list.php - simply a list of formula element groups to place the name function
    ----------------------------------

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

namespace cfg;

class element_group_list extends sandbox_list
{

    // array $lst is the list of formula element groups

    /*
     * debug
     */

    /**
     * @return string to display the unique id fields
     */
    function dsp_id(?term_list $trm_lst = null): string
    {
        global $debug;
        $result = '';

        // show at least 4 elements by name
        $min_names = $debug;
        if ($min_names < LIST_MIN_NAMES) {
            $min_names = LIST_MIN_NAMES;
        }

        if ($this->lst() != null) {
            $pos = 0;
            foreach ($this->lst() as $sbx_obj) {
                if ($min_names > $pos) {
                    if ($result <> '') $result .= ' / ';
                    $result .= $sbx_obj->dsp_id();
                    $pos++;
                }
            }
            $result .= parent::dsp_id_remaining($pos);
        }
        return $result;
    }

}