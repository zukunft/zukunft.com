<?php

/*

    api/sandbox/user_sandbox_named_api.php - extends the frontend API superclass for named objects such as formulas
    --------------------------------------


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

include_once SANDBOX_PATH . 'db_object.php';
include_once SANDBOX_PATH . 'sandbox.php';
include_once API_SANDBOX_PATH . 'sandbox_named.php';

use api\api;
use html\rest_ctrl as api_dsp;

class sandbox_named extends sandbox
{

    // the unique name of the object that is shown to the user
    // the name must always be set
    public string $name = '';

    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null;


    /*
     * set and get
     */

    /**
     * set the vars of this named sandbox object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(api::FLD_NAME, $json_array)) {
            $this->set_name($json_array[api::FLD_NAME]);
        } else {
            $this->set_name('');
            log_err('Mandatory field name missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(api::FLD_DESCRIPTION, $json_array)) {
            $this->set_description($json_array[api::FLD_DESCRIPTION]);
        } else {
            $this->set_description(null);
        }
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function name(): string
    {
        return $this->name;
    }

    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string the display value of the tooltip where null is an empty string
     */
    function description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }


    /*
     * load
     */

    /**
     * load the named user sandbox object e.g. word by name via api
     * @param string $name
     * @return bool
     */
    function load_by_name(string $name): bool
    {
        $result = false;

        $api = new api_dsp();
        $json_body = $api->api_call_name($this::class, $name);
        if ($json_body) {
            $this->set_from_json_array($json_body);
            if ($this->id() != 0) {
                $result = true;
            }
        }
        return $result;
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

        $vars[api::FLD_NAME] = $this->name();
        $vars[api::FLD_DESCRIPTION] = $this->description();
        return $vars;
    }


    /*
     * logging
     */

    /**
     * @return string best possible identification for this object mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = '';
        if ($this->name() <> '') {
            $result .= '"' . $this->name() . '"';
            if ($this->id() != 0) {
                $result .= ' (' . $this->id() . ')';
            }
        } else {
            $result .= $this->id();
        }
        return $result;
    }

}


