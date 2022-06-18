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

use api\list_min;
use html\formula_value_list_min_display;

class formula_value_list_min extends list_min
{

    function __construct()
    {
        parent::__construct();
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
     * @returns formula_value_list_min_display the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_value_list_min_display
    {
        $dsp_obj = new formula_value_list_min_display();

        $dsp_obj->lst = $this->lst;
        $dsp_obj->lst_dirty = true;

        return $dsp_obj;
    }

    /**
     * @returns phrase_list_min with the phrases that are used in all values of the list
     */
    protected function common_phrases(): phrase_list_min
    {
        // get common words
        $common_phr_lst = new phrase_list_min();
        foreach ($this->lst as $fv) {
            $val_phr_lst = $fv->phr_lst();
            if ($val_phr_lst->lst != null) {
                $common_phr_lst->intersect($val_phr_lst);
            }
        }
        return $common_phr_lst;
    }

}
