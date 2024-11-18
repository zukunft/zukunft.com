<?php

/*

    web/sandbox/sandbox_typed.php - extends the superclass for named html objects with the type id
    ------------------------------


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

namespace html\sandbox;

include_once SANDBOX_PATH . 'sandbox_named.php';
include_once SHARED_PATH . 'api.php';
include_once WEB_USER_PATH . 'user_message.php';

use shared\api;
use html\user\user_message;

class sandbox_typed extends sandbox_named
{

    // all named objects can have a type that links predefined functionality to it
    // e.g. all value assigned with the percent word are per default shown as percent with two decimals
    // the frontend object just contains the id of the type
    // because the type can be fast selected from the preloaded type list
    private ?int $type_id = null;


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(api::FLD_TYPE, $json_array)) {
            $this->set_type_id($json_array[api::FLD_TYPE]);
        } else {
            $this->set_type_id();
        }
        return $usr_msg;
    }

    function set_type_id(?int $type_id = null): void
    {
        $this->type_id = $type_id;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }

    /**
     * set the vars of this object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_url_array(array $url_array): user_message
    {
        $usr_msg = parent::set_from_url_array($url_array);
        if (array_key_exists(api::URL_VAR_TYPE, $url_array)) {
            $this->set_type_id($url_array[api::URL_VAR_TYPE]);
        } else {
            $this->set_type_id();
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
        $vars = parent::api_array();
        $vars[api::FLD_TYPE] = $this->type_id();
        return $vars;
    }

}


