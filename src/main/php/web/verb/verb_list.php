<?php

/*

    web/verb/verb_list.php - al list of verb objects
    ----------------------

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

namespace html\verb;

use cfg\const\paths;
use html\const\paths as html_paths;
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED . 'library.php';

use html\html_base;
use html\types\type_list;
use html\user\user;
use html\user\user_message;
use shared\library;

class verb_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the verb list is loaded, so to say the viewer

    /*
     * construct and map
     */

    /**
     * @param string|null $api_json string with the api json message to fill the list
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     */
    function __construct(?string $api_json = null)
    {
        $this->reset([]);
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
        return $this->set_from_json_array(json_decode($json_api_msg, true));
    }

    /**
     * set the vars of a term object based on the given json
     * @param array $json_array an api single object json message
     * @param string $class to force to use the verb child class of the type object
     * @return user_message ok or a warning e.g. if the server version does not match
     */
    function set_from_json_array(array $json_array, string $class = verb::class): user_message
    {
        $usr_msg = new user_message();
        foreach ($json_array as $value) {
            $new = clone new verb();
            $msg = $new->api_mapper($value);
            $usr_msg->add($msg);
            $this->add_obj($new);
        }
        return $usr_msg;
    }

    function list(string $class, string $title = ''): string
    {
        $html = new html_base();
        if ($title != '') {
            $title = $html->text_h2($title);
        }
        return $title . $html->list($this->lst(), $class);
    }


    /**
     * display a list of elements: replaced b html->list
     */
    function dsp_list(string $item_type = 'link_type'): string
    {
        $result = "";

        $item_lst = $this->lst();
        $item_type = 'link_type';
        $edit_script = $item_type . "_edit.php";
        $add_script = $item_type . "_add.php";
        foreach ($item_lst as $item) {
            $result .= '<a href="/http/' . $edit_script . '?id=' . $item->id . '">' . $item->name . '</a><br> ';
        }
        $result .= \html\btn_add('Add ' . $item_type, $add_script);
        $result .= '<br>';

        return $result;
    }

}