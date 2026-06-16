<?php

/*

    web/system/language.php - the extension of the language API objects to create language base html code
    -----------------------

    $lan is the suggested var name

    The main sections of this object are
    - object vars:       the variables of this word object
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

namespace Zukunft\ZukunftCom\main\php\web\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::TYPES . 'type_object.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\types\type_object;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;

class language extends type_object
{

    /*
     * object vars
     */

    public ?string $wiki_code = null; // the language code from Wikimedia for synchronisation
    public ?string $local_name = null; // the name in the language
    public ?int $usage = null; // estimation how many users the language has for sorting


    /*
     * api
     */

    /**
     * set the vars of this language frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $usr_msg, $dto);
        if (array_key_exists(url_var::LANGUAGE_SYMBOL, $url_array)) {
            $this->wiki_code = $url_array[url_var::LANGUAGE_SYMBOL];
        }
        if (array_key_exists(url_var::USAGE, $url_array)) {
            $this->usage = $url_array[url_var::USAGE];
        }
        return $usr_msg;
    }


    /*
     * selectors
     */

    function select_list_item(string $url, string $field = url_var::LANGUAGE, ?string $name = null, ?string $tip = null): string
    {
        return parent::select_list_item($url, $field, $this->get_local_name(), $this->name);
    }


    /*
     * internal
     */

    private function get_local_name(): string
    {
        if ($this->local_name == null) {
            return $this->name;
        } elseif ($this->local_name == '') {
            return $this->name;
        } else {
            return $this->local_name;
        }
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
