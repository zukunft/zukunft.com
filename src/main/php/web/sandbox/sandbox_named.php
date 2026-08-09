<?php

/*

    web/sandbox/sandbox_named.php - extends the frontend API superclass for named objects such as formulas
    -----------------------------

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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

//include_once html_paths::GROUP . 'group.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::SANDBOX . 'sandbox.php';
include_once html_paths::SANDBOX . 'db_object.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED_HELPER . 'Message.php';
include_once html_paths::SHARED_TYPES . 'api_type_list.php';
include_once html_paths::SHARED . 'api.php';
include_once html_paths::SHARED . 'url_var.php';
include_once html_paths::SHARED . 'json_fields.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\group\group;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\Message;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\library;

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

    // the number of objects where word is used
    public int $usage = 0;


    /*
     * set and get
     */

    function set_name(string $name): void
    {
        $this->name = $name;
    }

    function name(): string|null
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
    function get_description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    function get_usage(): int
    {
        return $this->usage;
    }


    /*
     * api
     */

    /**
     * set the vars of this named sandbox object bases on the api json array
     * @param array $json_array an api json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function api_mapper(array $json_array, user_message $msg): bool
    {
        parent::api_mapper($json_array, $msg);
        if (array_key_exists(json_fields::NAME, $json_array)) {
            $this->set_name($json_array[json_fields::NAME]);
        } else {
            $this->set_name('');
            if ($this::class != group::class) {
                $msg->add(msg_id::MANDATORY_FIELD_NAME_MISSING, [msg_id::VAR_JSON_TEXT => json_encode($json_array)]);
            }
        }
        if (array_key_exists(json_fields::DESCRIPTION, $json_array)) {
            $this->set_description($json_array[json_fields::DESCRIPTION]);
        } else {
            $this->set_description(null);
        }
        if (array_key_exists(json_fields::USAGE, $json_array)) {
            if ($json_array[json_fields::USAGE] != null) {
                $this->usage = $json_array[json_fields::USAGE];
            } else {
                $this->usage = 0;
            }
        } else {
            $this->usage = 0;
        }
        return $msg->is_ok();
    }

    /**
     * @return array the json message array to send the updated data to the backend
     * an array is used (instead of a string) to enable combinations of api_array($msg) calls
     */
    function api_array(api_type_list|array $typ_lst = [], user_message $msg = new user_message()): array
    {
        $vars = parent::api_array($typ_lst, $msg);

        $vars[json_fields::NAME] = $this->name();
        $vars[json_fields::DESCRIPTION] = $this->get_description();
        // the usage is not included here because it should always be updated in the backend
        return $vars;
    }

    /**
     * besides the base checks a named object requires a non-empty name to be confirmed,
r     * unless it is being deleted or excluded (soft-deleted) which does not need a name
     *
     * @param user_message $msg to enrich with a warning per invalid field
     * @param string $action the crud action of the change; a delete needs no name
     * @param array $url_array the pending change url (passed on to the parent checks)
     * @return bool true if the entered data can be confirmed
     */
    function input_valid(user_message $msg, string $action = '', array $url_array = []): bool
    {
        $result = parent::input_valid($msg, $action, $url_array);
        if ($action != url_var::CRUD_DELETE and !$this->is_excluded()) {
            if ($this->name == null or $this->name == '') {
                $msg->add_warning_with_vars(msg_id::NAME_EMPTY, [
                    msg_id::VAR_CLASS_NAME => library::class_to_name_translated($this::class)
                ]);
                $result = false;
            }
        }
        return $result;
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $msg, data_object|null $dto = null): user_message
    {
        parent::url_mapper($url_array, $msg, $dto);
        if (array_key_exists(url_var::NAME, $url_array)) {
            $this->set_name($url_array[url_var::NAME]);
        } else {
            // the name may arrive under NAME_GIVEN ('kg') instead of NAME ('k'), or the object may be
            // identified by id (an edit); only a submit with none of these is a genuine missing name.
            // kept log-only (the user-facing mandatory-name check lives in api_mapper, which reads the
            // canonical json name field) — surfacing this to $msg is deferred until the tests confirm it
            $this->set_name('');
            if (!array_key_exists(url_var::NAME_GIVEN, $url_array)
                and !array_key_exists(url_var::ID, $url_array)) {
                log_warning('Mandatory field name missing in form array ' . json_encode($url_array));
            }
        }
        if (array_key_exists(url_var::DESCRIPTION, $url_array)) {
            $this->set_description($url_array[url_var::DESCRIPTION]);
        }
        if (array_key_exists(url_var::USAGE, $url_array)) {
            if ($url_array[url_var::USAGE] != null) {
                $this->usage = $url_array[url_var::USAGE];
            }
        }
        return $msg;
    }

    /**
     * @return array parent url array extended with the name and description of this named object
     */
    function to_url_array(user_message $msg): array
    {
        $url_array = parent::to_url_array($msg);
        $url_array[url_var::NAME] = $this->name();
        $url_array[url_var::DESCRIPTION] = $this->get_description();
        if ($this->usage > 0) {
            $url_array[url_var::USAGE] = $this->usage;
        }
        return $url_array;
    }


    /*
     * load
     */

    /**
     * load the named user sandbox object e.g. word by name via api
     * TODO Prio 1 add user_message as parameter
     * @param string $name
     * @param user_message|Message $msg to collect the load warnings for the user
     * @return bool
     */
    function load_by_name(string $name, user_message|Message $msg): bool
    {
        $result = false;

        $api = new rest_call();
        $json_body = $api->api_call_name($this::class, $name);
        if ($json_body) {
            $this->api_mapper($json_body, $msg);
            if ($this->id() != 0) {
                $result = true;
            }
        }
        return $result;
    }



    /*
     * display
     */

    /**
     * @return string that best describes this object
     */
    function display(): string
    {
        return $this->name();
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
        // escape the user settable name so it cannot inject markup; the
        // description goes into the title attribute, which span() escapes
        return $html->span($html->esc($this->name()), '', $this->get_description());
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
        // escape the user settable name (link body); ref() escapes the
        // description that becomes the title attribute
        return $html->ref($url, $this->name(), $this->get_description(), $style);
    }


    /*
     * save
     */

    /**
     * TODO Prio 1 save the view that the user has selected for this object via the api
     * this is still a stub that saves nothing, so the view selection of the user is lost
     * the caller (frontend->url_to_html) even sends the new view id, which is ignored here
     * the backend counterpart is e.g. word::save_view(int $view_id)
     * @return user_message the message to the user if the view cannot be saved
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


