<?php

/*

    /web/result_dsp.php - the display extension of the api result object
    -------------------

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

namespace html\result;

use api\api;
use html\phrase\phrase_list as phrase_list_dsp;
use html\sandbox\sandbox_value;
use html\figure\figure as figure_dsp;
use html\user\user_message;

include_once WEB_SANDBOX_PATH . 'sandbox_value.php';

class result extends sandbox_value
{

    /*
     * set and get
     */

    /**
     * set the vars of this result bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        /* TODO add all result fields that are not part of the sandbox value object
        if (array_key_exists(api::FLD_USER_TEXT, $json_array)) {
            $this->set_usr_text($json_array[api::FLD_USER_TEXT]);
        } else {
            $this->set_usr_text(null);
        }
        */
        return $usr_msg;
    }


    /*
     * display
     */

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function display(phrase_list_dsp $phr_lst_header = null): string
    {
        return $this->grp()->display($phr_lst_header);
    }

    /**
     * @param phrase_list_dsp|null $phr_lst_header list of phrases that are shown already in the context e.g. the table header and that should not be shown again
     * @returns string the html code to display the phrase group with reference links
     */
    function display_linked(phrase_list_dsp $phr_lst_header = null): string
    {
        return $this->grp()->display_linked($phr_lst_header);
    }



    /*
     * cast
     */

    /**
     * @returns figure_dsp the figure display object base on this value object
     */
    function figure(): figure_dsp
    {
        $fig = new figure_dsp();
        $fig->set_obj($this);
        return $fig;
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
        $vars[api::FLD_PHRASES] = $this->grp()->phr_lst()->api_array();
        $vars[api::FLD_NUMBER] = $this->number();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
