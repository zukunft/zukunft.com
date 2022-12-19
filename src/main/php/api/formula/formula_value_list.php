<?php

/*

    api\formula_value_List.php - the minimal result value list object
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

namespace api;

use html\formula_value_list_dsp;

class formula_value_list_api extends list_value_api
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a formula result to the list
     * @returns bool true if the formula result has been added
     */
    function add(formula_value_api $fv): bool
    {
        $result = false;
        if (!in_array($fv->id(), $this->id_lst())) {
            $this->lst[] = $fv;
            $this->set_lst_dirty();
            $result = true;
        }
        return $result;
    }


    /*
     * cast
     */

    /**
     * @returns formula_value_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_value_list_dsp
    {
        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst as $fv) {
            if ($fv != null) {
                $fv_dsp = $fv->dsp_obj();
                $lst_dsp[] = $fv_dsp;
            }
        }
        return new formula_value_list_dsp($lst_dsp);
    }

}
