<?php

/*

    web/sandbox/list.php - the superclass for html list objects
    --------------------

    e.g. used to display phrase, term and figure lists

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

include_once API_OBJECT_PATH . 'api_message.php';
include_once WEB_HTML_PATH . 'html_selector.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';
include_once MODEL_USER_PATH . 'user.php';
include_once WEB_USER_PATH . 'user_message.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_TYPES_PATH . 'view_styles.php';
include_once SHARED_HELPER_PATH . 'CombineObject.php';
include_once SHARED_HELPER_PATH . 'IdObject.php';
include_once SHARED_HELPER_PATH . 'TextIdObject.php';
include_once SHARED_HELPER_PATH . 'ListOfIdObjects.php';
include_once SHARED_PATH . 'api.php';

use cfg\user\user;
use controller\api_message;
use html\rest_ctrl as api_dsp;
use html\html_selector;
use html\user\user_message;
use shared\api;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\ListOfIdObjects;
use shared\helper\TextIdObject;
use shared\types\api_type_list;
use shared\types\view_styles;

class list_dsp extends ListOfIdObjects
{

    /*
     * construct and map
     */

    function __construct(?string $api_json = null)
    {
        parent::__construct();
        if ($api_json != null) {
            $this->set_from_json($api_json);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @return void
     */
    function set_from_json(string $json_api_msg): void
    {
        $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this figure list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array): user_message
    {
        return new user_message('set_from_json_array not overwritten by child object ' . $this::class);
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @param IdObject|TextIdObject|CombineObject $dbo an object with a unique database id that should be added to the list
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_list_from_json(array $json_array, IdObject|TextIdObject|CombineObject $dbo): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_array as $value) {
            $new = clone $dbo;
            $msg = $new->set_from_json_array($value);
            $usr_msg->add($msg);
            $this->add_obj($new, true);
        }
        return $usr_msg;
    }

    /**
     * @returns array with the names on the db keys
     */
    function lst_key(): array
    {
        $result = array();
        foreach ($this->lst() as $val) {
            $result[$val->id()] = $val->name();
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
    function api_array(api_type_list|array $typ_lst = []): array
    {
        $result = array();
        foreach ($this->lst() as $obj) {
            if ($obj != null) {
                $result[] = $obj->api_array();
            }
        }
        return $result;
    }

    /**
     * create the api json message string of this list that can be sent to the backend
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return string with the api json string that should be sent to the backend
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        $vars = $this->api_array($typ_lst);

        // add header if requested
        if ($typ_lst->use_header()) {
            global $db_con;
            $api_msg = new api_message();
            $msg = $api_msg->api_header_array($db_con, $this::class, $usr, $vars);
        } else {
            $msg = $vars;
        }

        return json_encode($msg);
    }


    /*
     * load
     */

    /**
     * add the objects from the backend
     * @param string $pattern part of the name that should be used to select the objects
     * @return bool true if at least one object has been found
     */
    function load_like(string $pattern): bool
    {
        $result = false;

        $api = new api_dsp();
        $data = array();
        $data[api::URL_VAR_PATTERN] = $pattern;
        $json_body = $api->api_get($this::class, $data);
        $this->set_from_json_array($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * modify functions
     */

    /**
     * @returns array with all unique ids of this list
     */
    function id_lst(): array
    {
        return parent::ids();
    }


    /*
     * html - function that create html code
     */

    /**
     * create a selector for this list
     * used for words, triples, phrases, formulas, terms, views and components
     *
     * the calling function hierarchy is
     * 1. msk_lst->selector: adding the default parameters to select a view
     * 2. sbx->view_selector: adding the sandbox related parameters e.g. the default view of the object
     * 3. cmp->view_selector: adding the component specific parameters e.g. the phrase context to sort the views
     * 4. cmp->view_select: add the component and view parameters e.g. the form name and the unique name within the form
     *
     * @param string $form the html form name which must be unique within the html page
     * @param int $selected the unique database id of the object that has been selected
     * @param string $name the name of this selector which must be unique within the form
     * @param string $label the text show to the user
     * @param string $col_class the formatting code to adjust the formatting
     * @returns string the html code to select a word from this list
     */
    function selector(
        string $form = '',
        int    $selected = 0,
        string $name = '',
        string $label = '',
        string $col_class = view_styles::COL_SM_4,
        string $type = html_selector::TYPE_SELECT
    ): string
    {
        $sel = new html_selector();
        $sel->name = $name;
        $sel->form = $form;
        $sel->label = $label;
        $sel->bs_class = $col_class;
        $sel->type = $type;
        $sel->lst = $this->lst_key();
        $sel->selected = $selected;
        return $sel->display();
    }

}
