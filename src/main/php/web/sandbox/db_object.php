<?php

/*

    web/sandbox/db_object.php - the superclass for the html frontend of database objects
    -------------------------

    This superclass should be used by the classes word_ui, formula_ui, ... to enable user-specific values and links


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

namespace Zukunft\ZukunftCom\main\php\web\sandbox;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\helper\MapObject;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::API_OBJECT . 'api_message.php';
//include_once html_paths::COMPONENT . 'component_list.php';
//include_once html_paths::FORMULA . 'formula_list.php';
//include_once html_paths::TYPES . 'type_lists.php';
//include_once html_paths::REF . 'source_list.php';
//include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'button.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::HTML . 'rest_call.php';
//include_once html_paths::PHRASE . 'phrase.php';
//include_once html_paths::PHRASE . 'phrase_list.php';
//include_once html_paths::PHRASE . 'term.php';
//include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
//include_once html_paths::RESULT . 'result_list.php';
//include_once html_paths::VALUE . 'value_list.php';
//include_once html_paths::VIEW . 'view_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_HELPER . 'MapObject.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\web\component\component_list;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\button;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\web\phrase\term as term_ui;
use Zukunft\ZukunftCom\main\php\web\html\rest_call;
use Zukunft\ZukunftCom\main\php\web\ref\source_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\result\result_list;
use Zukunft\ZukunftCom\main\php\web\value\value_list;
use Zukunft\ZukunftCom\main\php\web\view\view_list;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\TextIdObject;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use DateTime;

class db_object extends TextIdObject
{

    /*
     * const
     */

    // the fallback crud views that are expected to be overwritten by the child objects;
    // the *_ID variants are the numeric view ids used in URLs (m=<id>) so links
    // resolve through the view-by-id router instead of the slower code-id lookup
    const string VIEW_ADD = views::WORD_ADD;
    const string VIEW_EDIT = views::WORD_EDIT;
    const string VIEW_DEL = views::WORD_DEL;
    const int VIEW_EDIT_ID = views::WORD_EDIT_ID;

    // the fallback crud message id that are expected to be overwritten by the child objects
    const msg_id MSG_ADD = msg_id::WORD_ADD;
    const msg_id MSG_EDIT = msg_id::WORD_EDIT;
    const msg_id MSG_DEL = msg_id::WORD_DEL;


    /*
     * object vars
     */

    // fields for the backend link
    public int|string $id = 0; // the database id of the object, which is the same as the related database object in the backend


    /*
     * construct and map
     */

    /**
     * TODO Prio 1 add user_message parameter
     * the html display object are always filled base on the api message
     * @param string|null $api_json the api message to set all object vars
     */
    function __construct(?string $api_json = null)
    {
        $usr_msg = new user_message();
        parent::__construct();
        if ($api_json != null) {
            $this->set_from_json($api_json, $usr_msg);
        }
    }

    /**
     * TODO rename to api mapper (but only for the frontend)
     * set the vars of this object bases on the url array
     * public because it is reused e.g. by the phrase group display object
     * @param array $url_array an array based on $_GET from a form submit
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto the cache as a parameter to be able to simulate test conditions
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function url_mapper(array $url_array, user_message $usr_msg, data_object|null $dto = null): user_message
    {
        $usr_msg = new user_message();
        if (!$this->url_is_add_action($url_array)) {
            // if the request is to add an object ignore the id
            if (array_key_exists(url_var::ID, $url_array)) {
                $this->set_id($url_array[url_var::ID]);
            } else {
                $this->set_id(0);
                $usr_msg->add_error_text('Mandatory field id missing in form url array ' . json_encode($url_array));
            }
        }
        return $usr_msg;
    }

    function url_is_add_action(array $url_array): bool
    {
        $is_add = false;
        if (array_key_exists(url_var::ACTION, $url_array)) {
            if ($url_array[url_var::ACTION] == url_var::CRUD_CREATE) {
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
     * @param user_message $usr_msg ok or a warning e.g. if the server version does not match
     * @return bool true if the mapping has been completed successfully
     */
    function set_from_json(string $json_api_msg, user_message $usr_msg): bool
    {
        return $this->api_mapper(json_decode($json_api_msg, true), $usr_msg);
    }

    /**
     * set the vars of this object bases on the api json array
     * this function is expected to be extended by each child object that has additional object vars
     *
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

        // remember to send the updates to the backend
        $this->set_modified();

        return $msg->is_ok();
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
     * TODO Prio 1 add user_message as parameter
     * @param int|string $id the database id of the object that should be loaded
     * @param array $data additional data that should be included in the get request
     * @return bool
     */
    function load_by_id(int|string $id, array $data = []): bool
    {
        $result = false;
        $usr_msg = new user_message();

        $api = new rest_call();
        $json_array = $api->api_call_id($this::class, $id, $data);
        if ($json_array) {
            $excluded = false;
            if (array_key_exists(json_fields::EXCLUDED, $json_array)) {
                $excluded = $json_array[json_fields::EXCLUDED];
            }
            if (!$excluded) {
                $this->api_mapper($json_array, $usr_msg);
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
        return new html_base()->url($view_code_id, $this->id(), $back);
    }


    /*
     * dummy functions to prevent polymorph warning
     * overwritten by the child classes
     */

    function name(): string|null
    {
        $msg = 'ERROR:  name not overwritten by ' . $this::class;
        log_warning($msg);
        return $this->id;
    }

    function get_description(): string
    {
        $msg = 'ERROR: description not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function get_plural(): ?string
    {
        $msg = 'ERROR: plural not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function reverse(): ?string
    {
        $msg = 'ERROR: reverse not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function plural_reverse(): ?string
    {
        $msg = 'ERROR: plural reverse not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function formula_name(): ?string
    {
        $msg = 'ERROR: formula_name not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function phrase_name(): ?string
    {
        $msg = 'ERROR: phrase_name not overwritten by ' . $this::class;
        log_err($msg);
        return $msg;
    }

    function value(): float|string|DateTime|null
    {
        $msg = 'ERROR: value not overwritten by ' . $this::class;
        log_err($msg);
        return 0;
    }

    /**
     * @return int|null how many times the object has been referenced or used
     */
    function get_usage(): int|null
    {
        $msg = 'ERROR: usage not overwritten by ' . $this::class;
        log_err($msg);
        return 0;
    }

    function phrase(): phrase_ui
    {
        return new phrase_ui();
    }

    /**
     * @returns term_ui the word object cast into a term object
     */
    function term(): term_ui
    {
        return new term_ui();
    }

    /**
     * @returns string the formula expression in the user-readable format and including user formatting
     */
    function user_expression(): string
    {
        return 'Missing user expression';
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
     * crud
     */

    /**
     * save the frontend object in the database
     * TODO Prio 2 should be done via api
     *
     * @param user $usr the frontend user
     * @param user_message $usr_msg the frontend message object to collect the message to the user
     * @return user_message the frontend message object filled up with the backend message for the user
     */
    function add_via_api(user $usr, user_message $usr_msg): user_message
    {
        $map_obj = new MapObject();
        $usr_msg_db = $map_obj->convertMsgToDb($usr_msg);
        $db_usr = $map_obj->convertToDb($usr, $usr_msg_db);
        $db_obj = $map_obj->convertToDb($this, $usr_msg_db, $db_usr);
        $add_result = $db_obj->save($usr_msg_db);
        /*
         * TODO Prio 2 activate api call
        $rest = new rest_call();
        $result = $rest->api_post($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        */
        return $map_obj->convertMsgToUi($usr_msg_db);
    }

    /**
     * update the frontend object via api in the database
     *
     * @param user $usr the frontend user
     * @param user_message $usr_msg the frontend message object to collect the message to the user
     * @return user_message the frontend message object filled up with the backend message for the user
     */
    function update(user $usr, user_message $usr_msg): user_message
    {
        $map_obj = new MapObject();
        $usr_msg_db = $map_obj->convertMsgToDb($usr_msg);
        $db_usr = $map_obj->convertToDb($usr, $usr_msg_db);
        $db_obj = $map_obj->convertToDb($this, $usr_msg_db, $db_usr);
        $upd_result = $db_obj->save($usr_msg_db);
        /*
         * TODO Prio 2 activate api call
        $rest = new rest_call();
        $result = $rest->api_put($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        */
        return $map_obj->convertMsgToUi($usr_msg_db);
    }

    /**
     * exclude this frontend object via api from the database
     *
     * @param user $usr the frontend user
     * @param user_message $usr_msg the frontend message object to collect the message to the user
     * * @return user_message the frontend message object filled up with the backend message for the user
     */
    function del(user $usr, user_message $usr_msg): user_message
    {
        $map_obj = new MapObject();
        $usr_msg_db = $map_obj->convertMsgToDb($usr_msg);
        $db_usr = $map_obj->convertToDb($usr, $usr_msg_db);
        $db_obj = $map_obj->convertToDb($this, $usr_msg_db, $db_usr);
        $del_result = $db_obj->del($usr_msg_db);
        /*
         * TODO Prio 2 activate api call
        $rest = new rest_call();
        $result = $rest->api_del($this::class, $this->api_array());
        foreach ($result as $msg) {
            $usr_msg->add_message_text($msg);
        }
        */
        return $map_obj->convertMsgToUi($usr_msg_db);
    }


    /*
     * dummy function to be overwritten by the child objects
     */

    /**
     * @return string|null the url field of some child object e.g. the source
     */
    function url(): ?string
    {
        $usr_msg = new user_message();
        $usr_msg->add_err_with_vars(msg_id::MISSING_FUNCTION_OVERWRITE, [
            msg_id::VAR_FUNCTION_NAME => 'url',
            msg_id::VAR_CLASS_NAME => $this::class
        ]);
        return $usr_msg->get_last_message();
    }

    /**
     * create the html code to select the phrase type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the phrase type
     */
    public function phrase_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'phrase type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the source type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the source type
     */
    public function source_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'source type selector not defined for ' . $this::class . '.';;
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the reference type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the reference type
     */
    public function ref_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $msg = 'reference type selector not defined for ' . $this::class . '.';
        // TODO Prio 1 active
        //log_warning($msg);
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the value type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the value type
     */
    public function value_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'value type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the formula type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the formula type
     */
    public function formula_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'formula type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the view type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    public function view_type_selector(string $form, ?type_lists $typ_lst): string
    {
        $msg = 'view type selector not defined for ' . $this::class . '.';
        // TODO Prio 1 active
        //log_warning($msg);
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the view style
     * used by the view and the component
     *
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    public function style_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'view style selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the component type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    public function component_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'component type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the component style
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    public function component_style_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'component style selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the view relation type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view relation type
     */
    public function view_relation_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'view relation type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the formula link type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the formula type
     */
    public function formula_link_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'formula link type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the view link type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the view type
     */
    public function view_link_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'view link type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select the component link type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the component type
     */
    public function component_link_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'component link type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * the html code to select a share type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the share type
     */
    public function share_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'share type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * the html code to select a protection type
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select the protection type
     */
    public function protection_type_selector(string $form, ?type_lists $typ_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'protection type selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * @param string $name the unique name inside the form for this selector
     * @param string $form the name of the html form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @param int $selected the id of the preselected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param phrase_ui|null $phr phrase to preselect the phrases e.g. use country to narrow the selection
     * @return string with the HTML code to show the phrase selector
     */
    public function phrase_selector_old(
        string     $name,
        string     $form,
        string     $label = '',
        string     $col_class = '',
        int        $selected = 0,
        string     $pattern = '',
        ?phrase_ui $phr = null
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'phrase selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * html code for a form field to select a word or triple
     *
     * @param phrase_list $phr_lst a preloaded phrase list for the selection
     * @param string $name the unique html field name that matches the resulting url field name
     * @param string $form the name of the html form
     * @param int|null $selected the row id of the suggested phrase or the already selected phrase
     * @param string $pattern the pattern to filter the phrases
     * @param msg_id $label_id the translation id for the text show to the user
     * @param string $style the style code e.g. to define the target width
     * @return string the html code to select the phrase
     */
    function phrase_selector(
        phrase_list $phr_lst,
        string      $name,
        string      $form,
        ?int        $selected = null,
        string      $pattern = '',
        msg_id      $label_id = msg_id::FORM_SELECT_PHRASE,
        string      $style = view_styles::COL_SM_4
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'phrase selector ' . $name . ' for ' . $form . ' not defined in class ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the HTML code for a field to select a value by the group or phrase list
     * @param string $form the name of the html form
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function value_selector(
        string     $form,
        value_list $val_lst,
        string     $name = url_var::VALUE
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'view selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the HTML code to select a formula
     * @param string $form the name of the html form
     * @param formula_list $frm_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function formula_selector(
        string       $form,
        formula_list $frm_lst,
        string       $name = url_var::FORMULA
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'formula selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the HTML code for a field to select a result by the group or phrase list
     * @param string $form the name of the html form
     * @param result_list|null $res_lst with the suggested results
     * @param string $name the unique html field name for the selection of the result
     * @return string the html code to select a result
     */
    public function result_selector(
        string      $form,
        result_list $res_lst = null,
        string      $name = url_var::RESULT
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'result selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the HTML code to select a view
     * @param string $form the name of the html form
     * @param view_list $msk_lst with the suggested views
     * @param string $name the unique html field name for the selection of the view
     * @return string the html code to select a view
     */
    public function view_selector(
        string    $form,
        view_list $msk_lst,
        string    $name = url_var::VIEW,
        msg_id    $msg_id = msg_id::FORM_SELECT_VIEW
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'view selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the HTML code to select a file for im- or export
     * @param string $form the name of the html form
     * @param string|null $name the suggested name of the file
     * @param array $lst with the suggested file names
     * @return string the html code to select a view
     */
    public function file_selector(
        string      $form,
        string|null $name = '',
        array       $lst = [],
        msg_id      $msg_id = msg_id::FORM_SELECT_FILE
    ): string
    {
        global $mtr;
        $html = new html_base();
        $action = api::MAIN_SCRIPT . url_var::PAR . url_var::MASK . url_var::EQ . $form;
        $frm_str = $html->form_field('fileToUpload', $msg_id, $name, html_base::INPUT_FILE);
        $frm_str .= $html->form_submit($mtr->txt(msg_id::SYSTEM_BUTTON_IMPORT));
        $result = '<' . html_base::FORM
            . ' ' . html_base::ACTION . '="' . $action . '"'
            . ' ' . html_base::METHOD . '="' . html_base::METHOD_POST . '"'
            . ' ' . html_base::ENCTYPE . '="multipart/form-data">'
            . $frm_str
            . '</' . html_base::FORM . '>';
        return $result;
    }

    /**
     * create the HTML code to select a component
     * @param string $form the name of the html form
     * @param string $pattern the pattern used to filter the components by the name
     * @param int $id the id of the component selected until now
     * @param component_list $cmp_lst with the suggested components
     * @return string the html code to select a component
     */
    public function component_selector(
        string         $form,
        string         $pattern,
        int            $id,
        component_list $cmp_lst
    ): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'component selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select a verb
     * @param string $form the name of the html form
     * @param type_lists|null $typ_lst the frontend cache with the configuration, the preloaded types and the cached objects
     * @return string the html code to select a verb
     */
    public function verb_selector(string $form, ?type_lists $typ_lst, string $style = view_styles::COL_SM_3): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'verb selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select a source
     * @param string $form the name of the html form
     * @param string $pattern
     * @param source_list|null $src_lst the frontend cache with the configuration, the preloaded source and the cached objects
     * @return string the html code to select a source
     */
    public function source_selector(string $form, string $pattern, ?source_list $src_lst): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'source selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

    /**
     * create the html code to select a reference
     * @param string $form the name of the html form
     * @param string $pattern
     * @return string the html code to select a reference
     */
    public function ref_selector(string $form, string $pattern): string
    {
        // TODO Prio 0 add message text to $msg object
        $msg = 'reference selector not defined for ' . $this::class . '.';
        log_warning($msg);
        return $msg;
    }

}


