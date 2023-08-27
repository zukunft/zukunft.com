<?php

/*

    web/sandbox/sandbox.php - the superclass for the html frontend of database objects
    -----------------------

    This superclass should be used by the classes word_dsp, formula_dsp, ... to enable user specific values and links


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace html\sandbox;

include_once API_SANDBOX_PATH . 'sandbox.php';

use api\api;
use controller\controller;
use html\api as api_dsp;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\term as term_dsp;

class db_object
{

    // fields for the backend link
    public int $id = 0; // the database id of the object, which is the same as the related database object in the backend


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
     * set the vars of this frontend object bases on the api message
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
    }

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function id(): int
    {
        return $this->id;
    }


    /*
     * load
     */

    /**
     * load the user sandbox object e.g. word by id via api
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
            if ($this->name() != '') {
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
        $vars = array();
        $vars[api::FLD_ID] = $this->id();
        return $vars;
    }


    /*
     * debug
     */

    /**
     * usually overwritten by the child object
     * @return string the id of the object used mainly for debugging
     */
    function dsp_id(): string
    {
        return $this->id();
    }


    /*
     * display
     */

    /**
     * create the html url to create, change or delete this database object
     * @param string $view_code_id the code id of the view as defined in the api controller class
     * @param string|null $back the back trace url for the undo functionality
     * @returns string the html code
     */
    function obj_url(string $view_code_id, ?string $back = ''): string
    {
        return (new html_base())->url($view_code_id, $this->id(), $back);
    }


    /*
     * dummy functions to prevent polymorph warning
     * overwritten by the child classes
     */

    function name(): string
    {
        return '';
    }

    function description(): string
    {
        return '';
    }

    function phrase(): phrase_dsp
    {
        return new phrase_dsp();
    }

    /**
     * @returns term_dsp the word object cast into a term object
     */
    function term(): term_dsp
    {
        return new term_dsp();
    }


    /*
     * dummy function to be overwritten by the child objects
     */

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the share type
     */
    protected function share_type_selector(string $form_name): string
    {
        $msg = 'share type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the protection type
     */
    protected function protection_type_selector(string $form_name): string
    {
        $msg = 'protection type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

}


