<?php

/*

    api/formula/formula_value.php - the minimal result value object
    ---------------------


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

use controller\controller;
use html\formula_value_dsp;

class formula_value_api extends sandbox_value_api implements \JsonSerializable
{

    /*
     * const for system testing
     */

    const TV_INT = 123456;
    const TV_FLOAT = 12.3456;


    /*
     * object vars
     */

    // true if the user has done no personal overwrites which is the default case
    public bool $is_std;


    /*
     * construct and map
     */

    function __construct(int $id = 0)
    {
        parent::__construct($id);
        $this->is_std = true;
    }


    /*
     * cast
     */

    /**
     * @returns formula_value_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): formula_value_dsp
    {
        $dsp_obj = new formula_value_dsp($this->id);
        $dsp_obj->set_grp($this->grp()->dsp_obj());
        $dsp_obj->set_number($this->number());
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
        $vars = get_object_vars($this);

        // add the var of the parent object
        $vars[sandbox_value_api::FLD_NUMBER] = $this->number();

        // remove vars from the json that have the default value
        if ($this->is_std) {
            if (array_key_exists('is_std', $vars)) {
                unset($vars['is_std']);
            }
        }

        // add the phrase list to the api object because this is always needed to display the value
        // the phrase group is not used in the api because this is always created dynamically based on the phrase
        // and only used to speed up the database and reduce the size
        $vars[controller::API_FLD_PHRASES] = json_decode(json_encode($this->phr_lst()));

        return $vars;
    }

}
