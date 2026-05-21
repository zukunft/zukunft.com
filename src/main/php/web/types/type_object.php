<?php

/*

    web/types/type_object.php - the superclass for word, formula and view types
    -------------------------

    a base type object that can be used to link program code to single objects
    e.g. if a value is classified by a phrase of type percent the value by default is formatted in percent

    types are used to assign coded functionality to a word, formula or view
    a user can create a new type to group words, formulas or views and request new functionality for the group
    types can be renamed by a user and the user change the comment
    it should be possible to translate types on the fly
    on each program start the types are loaded once into an array, because they are not supposed to change during execution


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

namespace Zukunft\ZukunftCom\main\php\web\types;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

//include_once paths::API_OBJECT . 'api_message.php';
//include_once paths::SHARED_TYPES . 'phrase_types.php';
//include_once paths::SHARED_CONST . 'views.php';
//include_once paths::SHARED . 'json_fields.php';
//include_once html_paths::HTML . 'html_base.php';
//include_once html_paths::PHRASE . 'phrase.php';
//include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::USER . 'user_message.php';
//include_once html_paths::WORD . 'word.php';
include_once html_paths::HELPER . 'data_object.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word;

class type_object
{

    /*
     * object vars
     */

    // the standard fields of a type
    public int $id;                // the database id is also used as the array pointer
    public string $name;           // simply the type name as shown to the user
    public string $code_id;        // this id text is unique for all code links and is used for system im- and export
    public ?string $comment = '';  // to explain the type to the user as a tooltip


    /*
     * construct and map
     */

    function __construct(int $id, ?string $code_id, string $name = '', ?string $comment = '')
    {
        $this->set_id($id);
        $this->set_name($name);
        if ($code_id != null) {
            $this->set_code_id($code_id);
        } else {
            $this->set_code_id('');
            // TODO create an action e.g. to add a code id to the new verb
            log_warning('code id is missing for type ' . $name . ' (' . $id . ')');
        }
        if ($comment != null) {
            if ($comment != '') {
                $this->set_comment($comment);
            }
        }
    }


    /*
     * set and get
     */

    function set_id(int $id): void
    {
        $this->id = $id;
    }

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function set_code_id(string $code_id): void
    {
        $this->code_id = $code_id;
    }

    function set_comment(string $comment): void
    {
        $this->comment = $comment;
    }

    function id(): int
    {
        return $this->id;
    }

    function name(): string
    {
        return $this->name;
    }

    function get_code_id(): string
    {
        return $this->code_id;
    }

    function comment(): string
    {
        return $this->comment;
    }

    function get_description(): ?string
    {
        return $this->comment;
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
        return $html->ref($url, $this->name(), $this->comment, $style);
    }


    /*
     * api
     */

    /**
     * set the vars of this type frontend object bases on the url array
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        if (array_key_exists(url_var::ID, $url_array)) {
            $this->set_id($url_array[url_var::ID]);
        }
        if (array_key_exists(url_var::NAME, $url_array)) {
            $this->set_name($url_array[url_var::NAME]);
        } else {
            $this->set_name('');
            log_warning('Mandatory field name missing in form url array ' . json_encode($url_array));
        }
        if (array_key_exists(url_var::CODE_ID, $url_array)) {
            $this->set_code_id($url_array[url_var::CODE_ID]);
        }
        if (array_key_exists(url_var::DESCRIPTION, $url_array)) {
            $this->set_comment($url_array[url_var::DESCRIPTION]);
        }
        return $usr_msg;
    }

    /**
     * set the vars of this source frontend object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $msg->add_error_text('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->name = $json_array[json_fields::NAME];
        } else {
            $this->name = null;
        }
        if (array_key_exists(json_fields::CODE_ID, $json_array)) {
            $this->code_id = $json_array[json_fields::CODE_ID];
        } else {
            $this->code_id = null;
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->comment = $json_array[json_fields::DESCRIPTION];
        } else {
            $this->comment = null;
        }

        return $msg->is_ok();
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array() calls
     */
    function api_array(): array
    {
        $vars = array();
        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::NAME] = $this->name;
        $vars[json_fields::CODE_ID] = $this->code_id;
        $vars[json_fields::DESCRIPTION] = $this->comment;
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }


    /*
     * fixed
     */

    /**
     * @return phrase_list with the phrases that are fixed linked to this type
     */
    function type_phrases(): phrase_list
    {
        $phr_lst = new phrase_list();
        if ($this->code_id == phrase_types::MATH_CONST) {
            $phr_lst->add(new word()->math()->phrase());
        }
        return $phr_lst;
    }


    /*
     * cast
     */

    function phrase(): phrase
    {
        $phr = new phrase();
        $phr->set_name('unassigned phrase for type ' . $this->name);
        // TODO Prio 2 link a phrase to each type object to be able to use also graph values and function for types
        return $phr;
    }

    /*
     * debug
     */

    function dsp_id(): string
    {
        $txt = $this::class . ' ' . $this->name . ' ';
        $txt .= '(' . $this->id . ') ';
        $txt .= 'code_id ' . $this->code_id;
        return $txt;
    }

}
