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

use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once html_paths::HTML . 'html_selector.php';
include_once html_paths::HTML . 'rest_call.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'CombineObject.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
include_once paths::SHARED_HELPER . 'TextIdObject.php';
include_once paths::SHARED_HELPER . 'ListOfIdObjects.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'view_styles.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';

use controller\api_message;
use html\html_selector;
use html\rest_call as api_dsp;
use html\user\user;
use html\user\user_message;
use shared\enum\messages as msg_id;
use shared\helper\CombineObject;
use shared\helper\IdObject;
use shared\helper\ListOfIdObjects;
use shared\helper\TextIdObject;
use shared\types\api_type_list;
use shared\types\view_styles;
use shared\url_var;

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
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json(string $json_api_msg): user_message
    {
        return $this->api_mapper(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of this figure list based on the given json
     * @param array $json_array an api single object json message
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper(array $json_array): user_message
    {
        return new user_message('set_from_json_array not overwritten by child object ' . $this::class);
    }

    /**
     * set the vars of these list display objects bases on the api json array
     * @param array $json_array an api list json message
     * @param IdObject|TextIdObject|CombineObject $dbo an object with a unique database id that should be added to the list
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function api_mapper_list(array $json_array, IdObject|TextIdObject|CombineObject $dbo): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_array as $value) {
            $new = clone $dbo;
            $msg = $new->api_mapper($value);
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
        $data[url_var::PATTERN] = $pattern;
        $json_body = $api->api_get($this::class, $data);
        $this->api_mapper($json_body);
        if (!$this->is_empty()) {
            $result = true;
        }
        return $result;
    }


    /*
     * modify
     */

    function merge(list_dsp $lst): void
    {
        foreach ($lst->lst() as $phr) {
            $this->add($phr);
        }
    }

    /**
     * add one named object e.g. a word to the list, but only if it is not yet part of the list
     * @param IdObject|TextIdObject|CombineObject|null $to_add the named object e.g. a word object that should be added
     * @returns bool true the object has been added
     */
    function add(IdObject|TextIdObject|CombineObject|null $to_add): bool
    {
        $result = false;
        if ($to_add != null) {
            $this->add_obj($to_add);
            $result = true;
        }
        return $result;
    }


    /*
     * info
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
     * @param int|null $selected the unique database id of the object that has been selected
     * @param string $name the name of this selector which must be unique within the form
     * @param msg_id $label_id the text show to the user
     * @param string $style the formatting code to adjust the formatting
     * @returns string the html code to select a word from this list
     */
    function selector(
        string $form = '',
        ?int   $selected = null,
        string $name = '',
        msg_id $label_id = msg_id::LABEL,
        string $style = view_styles::COL_SM_4,
        string $type = html_selector::TYPE_SELECT
    ): string
    {
        $sel = new html_selector();
        $sel->lst = $this->lst_key();
        $sel->name = $name;
        $sel->form = $form;
        $sel->selected = $selected;
        $sel->label_id = $label_id;
        $sel->style = $style;
        $sel->type = $type;
        return $sel->display();
    }

}
