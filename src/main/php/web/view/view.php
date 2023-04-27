<?php

/*

    /web/view/view.php - the display extension of the api view object
    ------------------

    to creat the HTML code to display a view


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

namespace html\view;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';

use controller\controller;
use html\api;
use html\html_base;
use html\phrase\term as term_dsp;
use html\sandbox_typed_dsp;

class view extends sandbox_typed_dsp
{

    /*
     * object vars
     */

    // used for system views
    private string $code_id;


    /*
     * set and get
     */

    /**
     * repeat here the sandbox object function to force to include all view object fields
     *
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * repeat here the sandbox object function to force to include all view object fields
     * @param array $json_array an api single object json message
     * @return void
     */
    function set_obj_from_json_array(array $json_array): void
    {
        $wrd = new view();
        $wrd->set_from_json_array($json_array);
    }

    /**
     * set the vars this view bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(controller::API_FLD_CODE_ID, $json_array)) {
            $this->code_id = $json_array[controller::API_FLD_CODE_ID];
        }
    }


    /*
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[controller::API_FLD_CODE_ID] = $this->code_id;

        return array_filter($vars, fn($value) => !is_null($value));
    }

}
