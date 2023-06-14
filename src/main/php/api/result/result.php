<?php

/*

    api/formula/result.php - the minimal result value object
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

include_once API_SANDBOX_PATH . 'sandbox_value.php';
include_once API_PATH . 'controller.php';
include_once WEB_RESULT_PATH . 'result.php';

use api\sandbox_value_api;
use controller\controller;
use JsonSerializable;
use html\result\result as result_dsp;

class result_api extends sandbox_value_api implements JsonSerializable
{

    /*
     * const for system testing
     */

    CONST TV_INT = 123456;
    CONST TV_FLOAT = 12.3456;
    CONST TV_INCREASE_LONG = '0.0078718332961637'; // the increase of the swiss inhabitants from 2019 to 2020

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
     * @returns result_dsp the cast object with the HTML code generating functions
     * should only be used for unit tests
     */
    function dsp_obj(): result_dsp
    {
        $api_json = $this->get_json();
        return new result_dsp($api_json);
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
            if (array_key_exists(controller::API_FLD_IS_STD, $vars)) {
                unset($vars[controller::API_FLD_IS_STD]);
            }
        }

        // add the phrase list to the api object because this is always needed to display the value
        // the phrase group is not used in the api because this is always created dynamically based on the phrase
        // and only used to speed up the database and reduce the size
        $vars[controller::API_FLD_PHRASES] = json_decode(json_encode($this->phr_lst()));

        return $vars;
    }

}
