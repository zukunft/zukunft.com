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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use html\value_list_dsp;

class value_list_api extends list_value_api
{

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a value to the list
     * @returns bool true if the value has been added
     */
    function add(value_api $val): bool
    {
        $result = false;
        if (!in_array($val->id(), $this->id_lst())) {
            $this->lst[] = $val;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }

    /**
     * @returns value_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): value_list_dsp
    {
        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $val) {
            if ($val != null) {
                $val_dsp = $val->dsp_obj();
                $lst_dsp[] = $val_dsp;
            }
        }

        return new value_list_dsp($lst_dsp);
    }

}
