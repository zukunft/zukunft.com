<?php

/*

    web/sandbox/combine_object.php - parent object to combine two or four sandbox web objects
    ------------------------------

    e.g. to combine value and result to figure
    or word and triple to phrase
    or word, triple, verb and formula to term

    TODO use it for phrase
    TODO use it for term


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

include_once HTML_PATH . 'rest_ctrl.php';

use html\rest_ctrl as api_dsp;

class combine_object
{

    /*
     * object vars
     */

    protected object $obj;


    /*
     * construct and map
     */

    /**
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of this combine frontend object bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this combine frontend object bases on the api json array
     * dummy function that should be overwritten by the child object
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        log_err('This set_from_json_array function should have been overwritten by the child object');
    }

    function set_obj(object $obj): void
    {
        $this->obj = $obj;
    }

    function obj(): object
    {
        return $this->obj;
    }


    /*
     * load
     */

    /**
     * load the combine object e.g. phrase by id via api
     * @param int $id
     * @return bool
     */
    function load_by_id(int $id): bool
    {
        $result = false;

        $api = new api_dsp();
        $json_body = $api->api_call_id($this::class, $id);
        if ($json_body) {
            $this->set_from_json_array($json_body);
            $result = true;
        }
        return $result;
    }

}
