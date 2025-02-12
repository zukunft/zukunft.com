<?php

/*

    web/verb/verb.php - the display extension of the api verb object
    -----------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend
    - cast:              create related frontend objects e.g. the phrase of a triple
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

namespace html\verb;

include_once WEB_SANDBOX_PATH . 'sandbox_named.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_SANDBOX_PATH . 'sandbox_named.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'json_fields.php';

use html\phrase\term;
use html\sandbox\sandbox_named;
use html\user\user_message;
use shared\const\views;
use shared\json_fields;

class verb extends sandbox_named
{

    /*
     * object vars
     */

    // this id text is unique for all code links and is used for system im- and export
    public string $code_id;
    public int $usage = 0;


    /*
     * set and get
     */

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function code_id(): string
    {
        return $this->code_id;
    }

    /**
     * the verb itself is a type
     * this function is only used as an interface mapping for the term
     * @return int|null
     */
    function type_id(): ?int
    {
        return $this->id;
    }


    /*
     * api
     */

    /**
     * set the vars of this object bases on the api json array
     * public because it is reused e.g. by the phrase group display object
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = parent::set_from_json_array($json_array);
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->set_code_id($json_array[json_fields::CODE_ID]);
        } else {
            $this->set_code_id('');
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
        $vars[json_fields::CODE_ID] = $this->code_id();
        $vars[json_fields::USAGE] = $this->usage;
        //$lib = new library();
        //$class = $lib->class_to_name($this::class);
        //$vars[json_fields::OBJECT_CLASS] = $class;
        return $vars;
    }


    /*
     * cast
     */

    function term(): term
    {
        $trm = new term();
        $trm->set_obj($this);
        return $trm;
    }


    /*
     * base
     */

    /**
     * display the verb with a link to the main page for the verb
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @param int $msk_id database id of the view that should be shown
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::VERB_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }

}
