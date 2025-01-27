<?php

/*

    api/formula/result_list.php - the minimal result value list object
    ----------------------------------


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

include_once API_SANDBOX_PATH . 'list_value.php';

use api\sandbox\list_value as list_value_api;
use html\result\result_list as result_list_dsp;

class result_list extends list_value_api
{

    /*
     * construct and map
     */

    function __construct(array $lst = array())
    {
        parent::__construct($lst);
    }


    /*
     * cast
     */

    /**
     * @returns result_list_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): result_list_dsp
    {
        // cast the single list objects
        $lst_dsp = new result_list_dsp();
        foreach ($this->lst() as $res) {
            if ($res != null) {
                $res_dsp = $res->dsp_obj();
                $lst_dsp->add($res_dsp);
            }
        }
        return $lst_dsp;
    }

}
