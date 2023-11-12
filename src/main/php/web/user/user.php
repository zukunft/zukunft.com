<?php

/*

    user_dsp.php - functions to create the HTML code to display a the user setup and log information
    ------------

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

namespace html\user;

use api\api;
use api\user\user_api;
use html\html_base;
use html\phrase\phrase_group as phrase_group_dsp;

class user extends user_api
{

    const FORM_EDIT = 'user_edit';


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
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        if (array_key_exists(api::FLD_ID, $json_array)) {
            $this->set_id($json_array[api::FLD_ID]);
        } else {
            $this->set_id(0);
            log_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->name = $json_array[api::FLD_NAME];
        } else {
            $this->name = null;
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->description = $json_array[api::FLD_NAME];
        } else {
            $this->description = null;
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->profile = $json_array[api::FLD_NAME];
        } else {
            $this->profile = null;
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->email = $json_array[api::FLD_NAME];
        } else {
            $this->email = null;
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->first_name = $json_array[api::FLD_NAME];
        } else {
            $this->first_name = null;
        }
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->last_name = $json_array[api::FLD_NAME];
        } else {
            $this->last_name = null;
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
        $vars[api::FLD_NAME] = $this->name;
        $vars[api::FLD_DESCRIPTION] = $this->description;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * to review
     */

    /**
     * display a form with the user parameters such as name or email
     */
    function form_edit($back): string
    {
        $html = new html_base();
        $result = ''; // reset the html code var

        if ($this->id > 0) {
            // display the user fields using a table and not using px in css to be independent of any screen solution
            $header = $html->text_h2('User "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id);
            $hidden_fields .= $html->form_hidden("back", $back);
            $detail_fields = $html->form_text("username", $this->name);
            $detail_fields .= $html->form_text("email", $this->email);
            $detail_fields .= $html->form_text("first name", $this->first_name);
            $detail_fields .= $html->form_text("last name", $this->last_name);
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(self::FORM_EDIT, $hidden_fields . $detail_row)
                . '<br>';
        }

        return $result;
    }

}
