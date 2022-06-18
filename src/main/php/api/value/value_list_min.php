<?php

/*

    value_List_min.php - the minimal value list object
    ------------------


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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use html\value_list_min_display;

class value_list_min extends list_min
{

    function __construct()
    {
        parent::__construct();
    }

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(value_min $val): bool
    {
        $result = false;
        if (!in_array($val->id, $this->id_lst())) {
            $this->lst[] = $val;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * @returns value_list_min_display the cast object with the HTML code generating functions
     */
    function dsp_obj(): value_list_min_display
    {
        $dsp_obj = new value_list_min_display();

        $dsp_obj->lst = $this->lst;
        $dsp_obj->lst_dirty = true;

        return $dsp_obj;
    }

}
