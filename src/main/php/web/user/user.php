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

use cfg\const\paths;
use html\const\paths as html_paths;
// get the api const that are shared between the backend and the html frontend
// get the pure html frontend objects
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once paths::SHARED_ENUM . 'user_profiles.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'json_fields.php';

use html\html_base;
use html\sandbox\db_object;
use shared\const\views;
use shared\enum\user_profiles;
use shared\json_fields;

class user extends db_object
{

    /*
     * object vars
     */

    public ?string $name;
    public ?string $description;
    public ?string $profile;
    // id of the user profile
    private int $profile_id;
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
        $this->profile_id = 0;
        $this->email = null;
        $this->first_name = null;
        $this->last_name = null;
    }


    /*
     * set and get
     */

    function set_profile_id(int $profile_id): void
    {
        $this->profile_id = $profile_id;
    }

    function profile_id(int $profile_id): int
    {
        return $this->profile_id;
    }

    function name(): string
    {
        return $this->name;
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
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
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->description = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->description = null;
        }
        if (array_key_exists(json_fields::PROFILE, $json_array)) {
            $this->profile = $json_array[json_fields::PROFILE];
        } else {
            $this->profile = null;
        }
        if (array_key_exists(json_fields::PROFILE_ID, $json_array)) {
            $this->set_profile_id($json_array[json_fields::PROFILE_ID]);
        } else {
            $this->profile = 0;
        }
        if (array_key_exists(json_fields::EMAIL, $json_array)) {
            $this->email = $json_array[json_fields::EMAIL];
        } else {
            $this->email = null;
        }
        if (array_key_exists(json_fields::FIRST_NAME, $json_array)) {
            $this->first_name = $json_array[json_fields::FIRST_NAME];
        } else {
            $this->first_name = null;
        }
        if (array_key_exists(json_fields::LAST_NAME, $json_array)) {
            $this->last_name = $json_array[json_fields::LAST_NAME];
        } else {
            $this->last_name = null;
        }
        return $usr_msg;
    }


    /*
     * info
     */

    /**
     * @returns bool true if the user has admin rights
     */
    function is_admin(): bool
    {
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $usr_pro_cac->id(user_profiles::ADMIN)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @returns bool true if the user is a system user e.g. the reserved word names can be used
     */
    function is_system(): bool
    {
        global $usr_pro_cac;
        log_debug();
        $result = false;

        if ($this->is_profile_valid()) {
            if ($this->profile_id == $usr_pro_cac->id(user_profiles::TEST)
                or $this->profile_id == $usr_pro_cac->id(user_profiles::SYSTEM)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @return bool false if the profile is not set or is not found
     */
    private function is_profile_valid(): bool
    {
        if ($this->profile_id > 0) {
            return true;
        } else {
            return false;
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
