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

namespace html;

use api\sandbox_typed_api;
use controller\controller;

include_once WEB_SANDBOX_PATH . 'sandbox_named.php';

class sandbox_typed_dsp extends sandbox_named_dsp
{

    // the json field names in the api json message which is supposed to be the same as the var $id
    const FLD_TYPE = 'type';

    // all named objects can have a type that links predefined functionality to it
    // e.g. all value assigned with the percent word are per default shown as percent with two decimals
    // the frontend object just contains the id of the type
    // because the type can be fast selected from the preloaded type list
    public ?int $type_id;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null, ?int $type_id = null)
    {
        parent::__construct($id, $name);
        $this->set_type_id($type_id);
    }


    /*
     * set and get
     */

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        // TODO combine the controller::API_FLD_TYPE
        if (array_key_exists(controller::API_FLD_TYPE, $json_array)) {
            $this->set_type_id($json_array[controller::API_FLD_TYPE]);
        }
    }

    function set_type_id(?int $type_id): void
    {
        $this->type_id = $type_id;
    }

    function type_id(): ?int
    {
        return $this->type_id;
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

        $vars[controller::API_FLD_TYPE] = $this->type_id();
        return $vars;
    }

}


