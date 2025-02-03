<?php

/*

    user_dsp.php - functions to create the HTML code to display the user setup and log information
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

// get the api const that are shared between the backend and the html frontend
// get the pure html frontend objects
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'json_fields.php';

use html\html_base;
use html\sandbox\db_object;
use shared\const\views;
use shared\json_fields;

class user extends db_object
{

    /*
     * object vars
     */

    public ?string $name;
    public ?string $description;
    public ?string $profile;
    public ?string $email;
    public ?string $first_name;
    public ?string $last_name;


    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        $this->reset();
        parent::__construct($api_json);
    }

    function reset(): void
    {
        $this->name = '';
        $this->description = null;
        $this->profile = null;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
    }


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json string
     * @param string $json_api_msg an api json message as a string
     * @return user_message
     */
    function set_from_json(string $json_api_msg): user_message
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->name = $json_array[json_fields::NAME];
        } else {
            $this->name = null;
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->description = $json_array[json_fields::NAME];
        } else {
            $this->description = null;
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->profile = $json_array[json_fields::NAME];
        } else {
            $this->profile = null;
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->email = $json_array[json_fields::NAME];
        } else {
            $this->email = null;
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->first_name = $json_array[json_fields::NAME];
        } else {
            $this->first_name = null;
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->last_name = $json_array[json_fields::NAME];
        } else {
            $this->last_name = null;
        }
        return $usr_msg;
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
        $vars[json_fields::NAME] = $this->name;
        $vars[json_fields::DESCRIPTION] = $this->description;
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

        if ($this->id() > 0) {
            // display the user fields using a table and not using px in css to be independent of any screen solution
            $header = $html->text_h2('User "' . $this->name . '"');
            $hidden_fields = $html->form_hidden("id", $this->id());
            $hidden_fields .= $html->form_hidden("back", $back);
            $detail_fields = $html->form_text("username", $this->name);
            $detail_fields .= $html->form_text("email", $this->email);
            $detail_fields .= $html->form_text("first name", $this->first_name);
            $detail_fields .= $html->form_text("last name", $this->last_name);
            $detail_row = $html->fr($detail_fields) . '<br>';
            $result = $header
                . $html->form(views::USER_EDIT, $hidden_fields . $detail_row)
                . '<br>';
        }

        return $result;
    }

}
