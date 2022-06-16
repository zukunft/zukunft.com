<?php

/*

    formula_value_List_min.php - the minimal result value list object
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace api;

use api\user_sandbox_min;
use html\formula_value_list_min_display;

class formula_value_list_min
{
    // the protected main var
    private array $lst;

    // memory vs speed optimize vars
    private array $id_lst;
    private bool $lst_dirty;

    function __construct()
    {
        $this->lst = array();

        $this->id_lst = array();
        $this->lst_dirty = false;
    }

    /**
     * @returns array with all unique result ids og this list
     */
    private function id_lst(): array
    {
        $result = array();
        if ($this->lst_dirty) {
            foreach ($this->lst as $fv) {
                if (!in_array($fv->id, $result)) {
                    $result[] = $fv->id;
                }
            }
            $this->lst_dirty = false;
        } else {
            $result = $this->id_lst;
        }
        return $result;
    }

    /**
     * add a formula result to the list
     * @returns bool true if the formula result has been added
     */
    function add(formula_value_min $fv): bool
    {
        $result = false;
        if (!in_array($fv->id, $this->id_lst())) {
            $this->lst[] = $fv;
            $this->lst_dirty = true;
            $result = true;
        }
        return $result;
    }

    /**
     * @returns array the protected list of formula results
     */
    function lst(): array
    {
        return $this->lst;
    }

    /**
     * @returns formula_value_list_min_display the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_value_list_min_display
    {
        $dsp_obj = new formula_value_list_min_display();

        $dsp_obj->lst = $this->lst;

        $dsp_obj->id_lst = $this->id_lst;
        $dsp_obj->lst_dirty = $this->lst_dirty;

        return $dsp_obj;
    }

}
