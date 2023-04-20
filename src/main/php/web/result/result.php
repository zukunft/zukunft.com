<?php

/*

    /web/formula_value_dsp.php - the display extension of the api formula value object
    -------------------------

    to creat the HTML code to display a formula


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

namespace result;

use html\phrase_list_dsp;
use html\sandbox_value_dsp;

include_once WEB_SANDBOX_PATH . 'sandbox_value.php';

class result_dsp extends sandbox_value_dsp
{
    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_dsp $phr_lst_header = null): string
    {
        return $this->grp()->name_linked($phr_lst_header);
    }


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json string
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg));
    }

}
