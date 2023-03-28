<?php

/*

    api/value/value.php - the minimal value object
    -------------------


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
use html\value_dsp;
use JsonSerializable;

class value_api extends sandbox_value_api implements JsonSerializable
{

    /*
     * const for system testing
     */

    // a list of dummy values that are used for system tests
    const TV_READ = 3.14159265358979323846264338327950288419716939937510; // pi
    const TV_READ_SHORT = 3.1415926535898; // pi
    const TV_READ_SHORTEST = 3.1415927; // pi
    const TV_INT = 123456;
    const TV_FLOAT = 123.456;
    const TV_BIG = 123456789;
    const TV_BIGGER = 234567890;
    const TV_USER_HIGH_QUOTE = "123'456";
    const TV_USER_SPACE = "123 456";
    const TV_PCT = 0.182642816772838; // to test the percentage calculation by the percent of Swiss inhabitants living in Canton Zurich
    const TV_INCREASE = 0.007871833296164; // to test the increase calculation by the increase of inhabitants in Switzerland from 2019 to 2020
    const TV_CANTON_ZH_INHABITANTS_2020_IN_MIO = 1.553423;
    const TV_CITY_ZH_INHABITANTS_2019 = 415367;
    const TV_CH_INHABITANTS_2019_IN_MIO = 8.438822;
    const TV_CH_INHABITANTS_2020_IN_MIO = 8.505251;
    const TV_SHARE_PRICE = 17.08;
    const TV_EARNINGS_PER_SHARE = 1.22;

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
     * set and get
     */

    function set_is_std(bool $is_std = true): void
    {
        $this->is_std = $is_std;
    }

    /**
     * @return bool false if the loaded value is user specific
     */
    function is_std(): bool
    {
        return $this->is_std;
    }


    /*
     * cast
     */

    /**
     * @returns value_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): value_dsp
    {
        $dsp_obj = new value_dsp($this->id);
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
