<?php

/*

    /web/system/language.php - the extension of the language API objects to create language base html code
    ------------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace html\system;

use controller\controller;
use api\api;
use html\api as api_dsp;
use html\html_base;
use html\sandbox\sandbox_typed;

class language extends sandbox_typed
{

    private ?string $url;

    /*
     * set and get
     */

    /**
     * set the vars of this language frontend object bases on the api json array
     * @param array $json_array an api json message
     * @return void
     */
    function set_from_json_array(array $json_array): void
    {
        parent::set_from_json_array($json_array);
        if (array_key_exists(api::FLD_URL, $json_array)) {
            $this->set_url($json_array[api::FLD_URL]);
        } else {
            $this->set_url(null);
        }
    }

    function set_url(?string $url): void
    {
        $this->url = $url;
    }

    function url(): ?string
    {
        return $this->url;
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
        $vars[api::FLD_URL] = $this->url();
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

    /*
     * display
     */

    /**
     * display the language name with the tooltip
     * @returns string the html code
     */
    function display(): string
    {
        return $this->name();
    }

    /**
     * display the language name with a link to the main page for the language
     * @param string|null $back the back trace url for the undo functionality
     * @param string $style the CSS style that should be used
     * @returns string the html code
     */
    function display_linked(?string $back = '', string $style = ''): string
    {
        $html = new html_base();
        $url = $html->url(api_dsp::LANGUAGE, $this->id, $back, api_dsp::PAR_VIEW_LANGUAGES);
        return $html->ref($url, $this->name(), $this->name(), $style);
    }

}
