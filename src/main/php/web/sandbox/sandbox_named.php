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

namespace html;

include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once API_SANDBOX_PATH . 'sandbox_named.php';

use api\sandbox_named_api;
use controller\controller;

class sandbox_named_dsp extends db_object_dsp
{

    // the unique name of the object that is shown to the user
    // the name must always be set
    public string $name;

    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null)
    {
        parent::__construct($id);
        $this->set_name($name);
        $this->set_description($description);
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
        if (array_key_exists(sandbox_named_api::FLD_NAME, $json_array)) {
            $this->set_name($json_array[sandbox_named_api::FLD_NAME]);
        } else {
            log_err('Mandatory field name missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(sandbox_named_api::FLD_DESCRIPTION, $json_array)) {
            $this->set_description($json_array[sandbox_named_api::FLD_DESCRIPTION]);
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
     * interface
     */

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[controller::API_FLD_NAME] = $this->name();
        $vars[controller::API_FLD_DESCRIPTION] = $this->description();
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
        if ($this->name <> '') {
            $result .= '"' . $this->name . '"';
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        $result .= '';
        return $result;
    }

}


