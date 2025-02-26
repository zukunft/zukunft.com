<?php

/*

    api/sandbox/user_sandbox_named_api.php - extends the frontend API superclass for named objects such as formulas
    --------------------------------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - load:              get an api json from the backend and
    - base:              html code for the single object vars


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

include_once WEB_HTML_PATH . 'html_base.php';
//include_once WEB_GROUP_PATH . 'group.php';
include_once WEB_SANDBOX_PATH . 'sandbox.php';
include_once WEB_SANDBOX_PATH . 'db_object.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';

use html\group\group;
use html\html_base;
use shared\api;
use html\rest_ctrl as api_dsp;
use html\user\user_message;
use shared\const\views;
use shared\json_fields;

class sandbox_named extends sandbox
{

    /*
     * object vars
     */

    // the unique name of the object that is shown to the user
    // the name must always be set
    public string $name = '';

    // the mouse over tooltip for the named object e.g. word, triple, formula, verb, view or component
    public ?string $description = null;


    /*
     * set and get
     */

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
     * api
     */

    /**
     * set the vars of this named sandbox object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->set_name($json_array[json_fields::NAME]);
        } else {
            $this->set_name('');
            if ($this::class != group::class) {
                log_err('Mandatory field name missing in API JSON ' . json_encode($json_array));
            }
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->set_description($json_array[json_fields::DESCRIPTION]);
        } else {
            $this->set_description(null);
        }
        return $usr_msg;
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = parent::api_array();

        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->description();
        return $vars;
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = parent::url_mapper($url_array);
        if (array_key_exists(api::URL_VAR_NAME, $url_array)) {
            $this->set_name($url_array[api::URL_VAR_NAME]);
        } else {
            $this->set_name('');
            log_err('Mandatory field name missing in form array ' . json_encode($url_array));
        }
        return $usr_msg;
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
            $this->api_mapper($json_body);
            if ($this->id() != 0) {
                $result = true;
            }
        }
        return $result;
    }



    /*
     * base
     */

    /**
     * @return string with the html code to show the name of the object with the tooltip
     */
    function name_tip(): string
    {
        $html = new html_base();
        return $html->span($this->name(), '', $this->description());
    }

    /**
     * display a word with a link to the main page for the word
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::GROUP_EDIT_ID): string
    {
        $html = new html_base();
        $url = $html->url_new($msk_id, $this->id(), '', $back);
        return $html->ref($url, $this->name(), $this->description(), $style);
    }


    /*
     * save
     */

    function save_view(): user_message
    {
        return new user_message();
    }


    /*
     * debug
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


    /*
     * review
     */

    function calc_view_id(): int
    {
        return 0;
    }

}


