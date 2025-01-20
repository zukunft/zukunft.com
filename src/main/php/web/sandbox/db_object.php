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
include_once HTML_PATH . 'html_base.php';
include_once HTML_PATH . 'rest_ctrl.php';
include_once WEB_PHRASE_PATH . 'phrase.php';
//include_once WEB_PHRASE_PATH . 'term.php';
include_once WEB_USER_PATH . 'user_message.php';
//include_once WEB_VIEW_PATH . 'view_list.php';
include_once SHARED_PATH . 'api.php';
include_once SHARED_PATH . 'json_fields.php';

use html\view\view_list;
use shared\api;
use html\rest_ctrl as api_dsp;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\term as term_dsp;
use html\user\user_message;
use shared\json_fields;

class db_object
{

    // fields for the backend link
    public int|string $id = 0; // the database id of the object, which is the same as the related database object in the backend


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
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this object bases on the api json array
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
        }
        return $usr_msg;
    }

    /**
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_url_array(array $url_array): user_message
    {
        $usr_msg = new user_message();
        if (array_key_exists(api::URL_VAR_ID, $url_array)) {
            $this->set_id($url_array[api::URL_VAR_ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in form url array ' . json_encode($url_array));
        }
        return $usr_msg;
    }

    function set_id(int|string $id): void
    {
        $this->id = $id;
    }

    function id(): int|string
    {
        return $this->id;
    }


    /*
     * load
     */

    /**
     * load the user sandbox object e.g. word by id via api
     * @param int $id
     * @param array $data additional data that should be included in the get request
     * @return bool
     */
    function load_by_id(int $id, array $data = []): bool
    {
        $result = false;

        $api = new api_dsp();
        $json_body = $api->api_call_id($this::class, $id, $data);
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
        $vars[json_fields::ID] = $this->id();
        return $vars;
    }

    /**
     * @return string the jsom message to the backend as a string
     */
    function api_json(): string
    {
        return json_encode($this->api_array());
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
     * load
     */


    function view_list(?string $pattern = null): view_list
    {
        $msk_lst = new view_list();
        $msk_lst->load_by_pattern($pattern);
        return $msk_lst;
    }


    /*
     * dummy function to be overwritten by the child objects
     */

    /**
     * @param string $form the name of the html form
     * @return string the html code to select the phrase type
     */
    protected function phrase_type_selector(string $form): string
    {
        $msg = 'phrase type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * @param string $form_name the name of the html form
     * @return string the html code to select the phrase type
     */
    protected function source_type_selector(string $form_name): string
    {
        $msg = 'source type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

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

    /**
     * @param string $name the unique name inside the form for this selector
     * @param string $form the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_dsp|null $phr phrase to preselect the phrases e.g. use Country to narrow the selection
     * @return string with the HTML code to show the phrase selector
     */
    protected function phrase_selector(
        string      $name,
        string      $form,
        string      $label = '',
        string      $col_class = '',
        int         $selected = 0,
        string      $pattern = '',
        ?phrase_dsp $phr = null
    ): string
    {
        $msg = 'phrase selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @return string the html code to select a view
     */
    protected function view_selector(string $form, view_list $msk_lst): string
    {
        $msg = 'view selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * @param string $form the name of the html form
     * @return string the html code to select a phrase
     */
    protected function verb_selector(string $form): string
    {
        $msg = 'verb selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

}


