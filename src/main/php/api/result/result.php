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

namespace api\result;

include_once API_SANDBOX_PATH . 'sandbox_value.php';
include_once API_OBJECT_PATH . 'controller.php';
include_once WEB_RESULT_PATH . 'result.php';
include_once SHARED_PATH . 'json_fields.php';

use api\sandbox\sandbox_value as sandbox_value_api;
use JsonSerializable;
use html\result\result as result_dsp;
use shared\json_fields;

class result extends sandbox_value_api implements JsonSerializable
{

    /*
     * object vars
     */

    // true if the user has done no personal overwrites which is the default case
    public bool $is_std;


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

}
