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

use cfg\const\paths;
use controller\api_message;
use html\button;
use html\const\paths as html_paths;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use html\phrase\phrase_list;
use html\phrase\term as term_dsp;
use html\rest_call;
use html\rest_call as api_dsp;
use html\types\type_lists;
use html\user\user_message;
use html\view\view_list;
use shared\const\views;
use shared\enum\messages as msg_id;
use shared\helper\TextIdObject;
use shared\json_fields;
use shared\types\view_styles;
use shared\url_var;

include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::TYPES . 'type_lists.php';
//include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
//include_once html_paths::PHRASE . 'phrase.php';
//include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::PHRASE . 'term.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED . 'json_fields.php';

class db_object extends TextIdObject
{

    /*
     * const
     */

    // the fallback curl views that are expected to be overwritten by the child objects
    const VIEW_ADD = views::WORD_ADD;
    const VIEW_EDIT = views::WORD_EDIT;
    const VIEW_DEL = views::WORD_DEL;

    // the fallback curl message id that are expected to be overwritten by the child objects
    const MSG_ADD = msg_id::WORD_ADD;
    const MSG_EDIT = msg_id::WORD_EDIT;
    const MSG_DEL = msg_id::WORD_DEL;


    /*
     * object vars
     */

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
        parent::__construct();
        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }

    /**
     * TODO rename to api mapper (but only for the frontend)
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array): user_message
    {
        $usr_msg = new user_message();
        if (!$this->url_is_add_action($url_array)) {
            // if the request is to add an object ignore the id
            if (array_key_exists(url_var::ID, $url_array)) {
                $this->set_id($url_array[url_var::ID]);
            } else {
                $this->set_id(0);
                $usr_msg->add_err('Mandatory field id missing in form url array ' . json_encode($url_array));
            }
        }
        return $usr_msg;
    }

    function url_is_add_action(array $url_array): bool
    {
        $is_add = false;
        if (array_key_exists(url_var::ACTION, $url_array)) {
            if ($url_array[url_var::ACTION] == url_var::CURL_CREATE) {
                $is_add = true;
            }
        }
        if (array_key_exists(url_var::ACTION_LONG, $url_array)) {
            if ($url_array[url_var::ACTION_LONG] == url_var::CURL_CREATE) {
                $is_add = true;
            }
        }
        return $is_add;
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
        return $this->api_mapper(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this object bases on the api json array
     * this function is expected to be extended by each child object that has additional object vars
     *
     * @param array $json_array an api json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        $usr_msg = new user_message();

        // get body from message
        $api_msg = new api_message();
        $json_array = $api_msg->validate($json_array);

        if (array_key_exists(json_fields::ID, $json_array)) {
            $this->set_id($json_array[json_fields::ID]);
        } else {
            $this->set_id(0);
            $usr_msg->add_err('Mandatory field id missing in API JSON ' . json_encode($json_array));
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
     * @param int|string $id the database id of the object that should be loaded
     * @param array $data additional data that should be included in the get request
     * @return bool
     */
    function load_by_id(int|string $id, array $data = []): bool
    {
        $result = false;

        $api = new api_dsp();
        $json_array = $api->api_call_id($this::class, $id, $data);
        if ($json_array) {
            $excluded = false;
            if (array_key_exists(json_fields::EXCLUDED, $json_array)) {
                $excluded = $json_array[json_fields::EXCLUDED];
            }
            if (!$excluded) {
                $this->api_mapper($json_array);
                if ($this->name() != '') {
                    $result = true;
                }
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
     * @return string the json message to the backend as a string
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
     * buttons
     */

    /**
     * @return string the html code for a bottom
     * to create a new sandbox object e.g. word for the current user
     */
    function btn_add(string $back = ''): string
    {
        return $this->btn_add_sbx(
            $this::VIEW_ADD,
            $this::MSG_ADD,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to change a sandbox object e.g. the word name or the type
     */
    function btn_edit(string $back = ''): string
    {
        return $this->btn_edit_sbx(
            $this::VIEW_EDIT,
            $this::MSG_EDIT,
            $back);
    }

    /**
     * @return string the html code for a bottom
     * to exclude the sandbox object e.g. word for the current user
     * or if no one uses the sandbox object delete the complete sandbox object e.g. word
     */
    function btn_del(string $back = ''): string
    {
        return $this->btn_del_sbx(
            $this::VIEW_DEL,
            $this::MSG_DEL,
            $back);
    }

    /**
     * create the html code to add a sandbox object for the current user
     *
     * @param int|string $msk_id the code id or database id of the view used to add the object
     * @param msg_id $msg_code_id the code id of the message that should be shown to the user as a tooltip for the button
     * @param string $back the backtrace for the return page after adding the object and for undo actions
     * @param string $explain additional text created by the calling child to understand the action better e.g. the phrases used for a new value
     * @return string the html code for a bottom
     */
    function btn_add_sbx(int|string $msk_id, msg_id $msg_code_id, string $back = '', string $explain = ''): string
    {
        $btn = $this->btn_sbx($msk_id, $back);
        return $btn->add($msg_code_id, $explain);
    }

    /**
     * html code to change a sandbox object e.g. the name or the type
     *
     * @param int|string $msk_id the code id or database id of the view used to add the object
     * @param msg_id $msg_code_id the code id of the message that should be shown to the user as a tooltip for the button
     * @param string $back the backtrace for the return page after adding the object and for undo actions
     * @param string $explain additional text created by the calling child to understand the action better e.g. the phrases used for a new value
     * @return string the html code for a bottom
     */
    function btn_edit_sbx(int|string $msk_id, msg_id $msg_code_id, string $back = '', string $explain = ''): string
    {
        $btn = $this->btn_sbx($msk_id, $back);
        return $btn->edit($msg_code_id, $explain);
    }

    /**
     * html code to exclude the sandbox object for the current user
     * or if no one uses the word delete the complete word
     *
     * @param int|string $msk_id the code id or database id of the view used to add the object
     * @param msg_id $msg_code_id the code id of the message that should be shown to the user as a tooltip for the button
     * @param string $back the backtrace for the return page after adding the object and for undo actions
     * @param string $explain additional text created by the calling child to understand the action better e.g. the phrases used for a new value
     * @return string the html code for a bottom
     */
    function btn_del_sbx(int|string $msk_id, msg_id $msg_code_id, string $back = '', string $explain = ''): string
    {
        $btn = $this->btn_sbx($msk_id, $back);
        return $btn->del($msg_code_id, $explain);
    }

    /**
     * create the html code for a button
     *
     * @param int|string $msk_id the code id or database id of the view used to add the object
     * @param string $back the backtrace for the return page after adding the object and for undo actions
     * @return button the filled bottom object
     */
    private function btn_sbx(int|string $msk_id, string $back = ''): button
    {
        $html = new html_base();
        $url = $html->url_new($msk_id, $this->id(), '', $back);
        return new button($url, $back);
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
        return 'name not overwritten by ' . $this::class;
    }

    function description(): string
    {
        return 'description not overwritten by ' . $this::class;
    }

    function plural(): ?string
    {
        return 'plural not overwritten by ' . $this::class;
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

    /**
     * @returns string the formula expression in the user readable format and including user formatting
     */
    function user_expression(): string
    {
        return '';
    }

    /**
     * @returns bool true e.g. if all term of the formula expression needs to be set for calculation the result
     */
    function need_all(): bool
    {
        return false;
    }


    /*
     * load
     */

    function view_list(?string $pattern = null): view_list
    {
        $msk_lst = new view_list();
        if ($pattern == null) {
            $pattern = '*';
        }
        $msk_lst->load_by_pattern($pattern);
        return $msk_lst;
    }


    /*
     * curl
     */

    /**
     * add the frontend object via api to the database
     *
     * @return user_message
     */
    function add_via_api(): user_message
    {
        $usr_msg = new user_message();
        $rest = new rest_call();
        $result = $rest->api_post($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        return $usr_msg;
    }

    /**
     * update the frontend object via api in the database
     *
     * @return user_message
     */
    function update(): user_message
    {
        $usr_msg = new user_message();
        $rest = new rest_call();
        $result = $rest->api_put($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        return $usr_msg;
    }

    /**
     * exclude this frontend object via api from the database
     *
     * @return user_message
     */
    function del(): user_message
    {
        $usr_msg = new user_message();
        $rest = new rest_call();
        $result = $rest->api_del($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        return $usr_msg;
    }


    /*
     * dummy function to be overwritten by the child objects
     */

    /**
     * create the html code to select the phrase type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $msg = 'phrase type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the source type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type
     */
    public function source_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'source type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the reference type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference type
     */
    public function ref_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'reference type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the formula type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the formula type
     */
    public function formula_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'formula type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the view type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    public function view_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'view type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the view style
     * used by the view and the component
     *
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    public function view_style_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'view style selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select the component type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    public function component_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'component type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * the html code to select a share type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function share_type_selector(string $form_name, ?type_lists $typ_lst): string
    {
        $msg = 'share type selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * the html code to select a protection type
     * @param string $form_name the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    public function protection_type_selector(string $form_name, ?type_lists $typ_lst): string
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
    public function phrase_selector_old(
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
     * to select the from phrase
     * @param string $form the name of the html form
     * @param int $id the row id of the suggested phrase or the already selected phrase
     * @param phrase_list|null $phr_lst a preloaded phrase list for the selection
     * @param string $name the unique html field name that matches the resulting url field name
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    function phrase_selector(
        string $form,
        int $id,
        ?phrase_list $phr_lst = null,
        string $name = '',
        string $label = '',
        string $style = view_styles::COL_SM_4
    ): string
    {
        $msg = $name . ' from phrase selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string|null $name the suggested name of the view
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select a view
     */
    public function view_selector(
        string $form,
        view_list $msk_lst,
        string $name = null,
        ?type_lists $typ_lst = null
    ): string
    {
        $msg = 'view selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

    /**
     * create the html code to select a verb
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select a verb
     */
    public function verb_selector(string $form, ?type_lists $typ_lst): string
    {
        $msg = 'verb selector not defined for ' . $this::class;
        log_err($msg);
        return $msg;
    }

}


