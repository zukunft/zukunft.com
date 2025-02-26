<?php

/*

    web/system/language.php - the extension of the language API objects to create language base html code
    -----------------------

    The main sections of this object are
    - object vars:       the variables of this word object
    - set and get:       to capsule the vars from unexpected changes
    - api:               set the object vars based on the api json message and create a json for the backend


    This file is part of the frontend of zukunft.com - calc with words

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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\system;

include_once WEB_SANDBOX_PATH . 'sandbox_typed.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_CONST_PATH . 'views.php';
include_once SHARED_PATH . 'json_fields.php';

use html\rest_ctrl as api_dsp;
use html\html_base;
use html\sandbox\sandbox_typed;
use html\user\user_message;
use shared\const\views;
use shared\json_fields;

class language extends sandbox_typed
{

    /*
     * object vars
     */

    private ?string $url;


    /*
     * set and get
     */

    function set_url(?string $url): void
    {
        $this->url = $url;
    }

    function url(): ?string
    {
        return $this->url;
    }


    /*
     * api
     */

    /**
     * set the vars of this language frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = parent::api_mapper($json_array);
        if (array_key_exists(json_fields::URL, $json_array)) {
            $this->set_url($json_array[json_fields::URL]);
        } else {
            $this->set_url(null);
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
        $vars[json_fields::URL] = $this->url();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /*
     * base
     */

    /**
     * display the language name with the tooltip
     * @returns string the html code
     */
    function name_tip(): string
    {
        return $this->name();
    }

    /**
     * display the language name with a link to the main page for the language
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function name_link(?string $back = '', string $style = '', int $msk_id = views::LANGUAGE_ID): string
    {
        return parent::name_link($back, $style, $msk_id);
    }

}
