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

use cfg\const\paths;
use html\const\paths as html_paths;
use html\user\user_message;
use shared\json_fields;
use shared\url_var;

include_once html_paths::SANDBOX . 'sandbox_named.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

class sandbox_typed extends sandbox_named
{

    // all named objects can have a type that links predefined functionality to it
    // e.g. all value assigned with the percent word are per default shown as percent with two decimals
    // the frontend object just contains the id of the type
    // because the type can be fast selected from the preloaded type list
    private ?int $type_id = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if (array_key_exists(url_var::TYPE, $url_array)) {
            $this->set_type_id($url_array[url_var::TYPE]);
        } else {
            $this->set_type_id();
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::TYPE, $json_array)) {
            $this->set_type_id($json_array[json_fields::TYPE]);
        } else {
            $this->set_type_id();
        }
        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::TYPE] = $this->type_id();
        return $vars;
    }


    /*
     * set and get
     */

    function set_type_id(?int $type_id = null): void
    {
        $this->type_id = $type_id;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }


}


