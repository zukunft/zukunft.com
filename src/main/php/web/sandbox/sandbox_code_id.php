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
include_once html_paths::SANDBOX . 'sandbox_typed.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'json_fields.php';

use html\user\user_message;
use shared\json_fields;

class sandbox_code_id extends sandbox_typed
{

    // the code_id to use single objects with predefined functionality also in the frontend
    private ?string $code_id = null;


    /*
     * construct and map
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->set_code_id($json_array[json_fields::CODE_ID]);
        } else {
            $this->set_code_id(null);
        }
        return $usr_msg;
    }


    /*
     * api
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * the code id is included in the message only to fill up backend object but never to change the code_id via ui
     */
    function api_array(): array
    {
        $vars = parent::api_array();
        $vars[json_fields::CODE_ID] = $this->code_id();
        return $vars;
    }


    /*
     * set and get
     */

    function set_code_id(?string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function code_id(): ?string
    {
        return $this->code_id;
    }

}


