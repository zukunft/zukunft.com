<?php

/*

    api/formula/formula_list.php - a list of minimal/api formula objects
    ----------------------------


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

namespace api\formula;

use api\sandbox\list_object as list_api;
use html\formula\formula_list as formula_list_dsp;
use JsonSerializable;

class formula_list extends list_api implements JsonSerializable
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }

    /**
     * add a formula to the list
     * @returns bool true if the formula has been added
     */
    function add(formula $frm): bool
    {
        return parent::add_obj($frm);
    }


    /*
     * cast
     */

    /**
     * @returns formula_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_list_dsp
    {
        $dsp_obj = new formula_list_dsp();

        // cast the single list objects
        $lst_dsp = array();
        foreach ($this->lst() as $frm) {
            if ($frm != null) {
                $frm_dsp = $frm->dsp_obj();
                $lst_dsp[] = $frm_dsp;
            }
        }

        $dsp_obj->set_lst($lst_dsp);
        $dsp_obj->set_lst_dirty();

        return $dsp_obj;
    }


    /*
     * interface
     */

    /**
     * an array of the value vars including the private vars
     */
    function jsonSerialize(): array
    {
        $vars = [];
        foreach ($this->lst() as $frm) {
            $vars[] = json_decode(json_encode($frm));
        }
        return $vars;
    }


    /*
     * selection functions
     */

    /**
     * diff as a function, because the array_diff does not seem to work for an object list
     *
     * e.g. for "2014", "2015", "2016", "2017"
     * and delete list of "2016", "2017","2018"
     * the result is "2014", "2015"
     *
     * @param formula_list $del_lst is the list of phrases that should be removed from this list object
     */
    private function diff(formula_list $del_lst): void
    {
        if (!$this->is_empty()) {
            $result = array();
            $lst_ids = $del_lst->id_lst();
            foreach ($this->lst() as $frm) {
                if (!in_array($frm->id(), $lst_ids)) {
                    $result[] = $frm;
                }
            }
            $this->set_lst($result);
        }
    }

    /**
     * merge as a function, because the array_merge does not create an object
     * @param formula_list $new_wrd_lst with the formulas that should be added
     */
    function merge(formula_list $new_wrd_lst): void
    {
        foreach ($new_wrd_lst->lst() as $new_wrd) {
            $this->add($new_wrd);
        }
    }

    /**
     * @param string $type the ENUM string of the fixed type
     * @return formula_list with the all formulas of the give type
     */
    private function filter(string $type): formula_list
    {
        $result = new formula_list();
        foreach ($this->lst() as $frm) {
            if ($frm->is_type($type)) {
                $result->add($frm);
            }
        }
        return $result;
    }

}
