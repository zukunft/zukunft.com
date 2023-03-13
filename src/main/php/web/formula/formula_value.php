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

namespace html;

use api\formula_value_api;
use api\phrase_list_api;
use api\user_sandbox_api;
use api\user_sandbox_value_api;

class formula_value_dsp extends formula_value_api
{
    /**
     * @param phrase_list_api|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function name_linked(phrase_list_api $phr_lst_header = null): string
    {
        return $this->grp_dsp()->name_linked($phr_lst_header);
    }


    /*
     * set and get
     */

    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg));
    }

    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(user_sandbox_api::FLD_ID, $json_array)) {
            $this->set_number($json_array[user_sandbox_api::FLD_ID]);
        } else {
            log_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(user_sandbox_value_api::FLD_NUMBER, $json_array)) {
            $this->set_number($json_array[user_sandbox_value_api::FLD_NUMBER]);
        }

    }

}
