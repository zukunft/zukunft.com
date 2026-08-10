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

namespace Zukunft\ZukunftCom\main\php\web\verb;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::USER . 'user.php';
include_once html_paths::USER . 'user_message.php';
include_once html_paths::SHARED_CONST . 'rest_ctrl.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\library;

class verb_list extends type_list
{

    private ?user $usr = null; // the user object of the person for whom the verb list is loaded, so to say the viewer

    /*
     * construct and map
     */

    /**
     * the parent constructor is called after the reset of lst_name_dirty to enable setting by adding the list
     * @param string|null $api_json string with the api json message to fill the list
     * @param user_message|null $msg to report the api mapping problems; null loses them
     */
    function __construct(?string $api_json = null, ?user_message $msg = null)
    {
        $this->reset([]);
        if ($api_json != null) {
            // only the api mapping needs a message; a caller that passes none loses the mapping
            // problems, so every caller that has one should hand it over
            $msg = $msg ?? new user_message();
            $this->set_from_json($api_json, $msg);
        }
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if there are no errors
     */
    function set_from_json(string $json_api_msg, user_message $msg): bool
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true), $msg);
    }

    /**
     * set the vars of a term object based on the given json
     * TODO Prio 1 add user_message as parameter
     * @param array $json_array an api single object json message
     * @param string $class to force to use the verb child class of the type object
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @return bool true if there are no errors
     */
    function set_from_json_array(array $json_array, user_message $msg, string $class = verb::class): bool
    {
        foreach ($json_array as $value) {
            $new = clone new verb();
            if ($new->api_mapper($value, $msg)) {
                $this->add_obj($new);
            }
        }
        return $msg->is_ok();
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
        $html = new html_base();

        $item_lst = $this->lst();
        $item_type = 'link_type';
        $edit_script = $item_type . "_edit.php";
        $add_script = $item_type . "_add.php";
        foreach ($item_lst as $item) {
            $result .= $html->ref(rest_ctrl::PATH_FIXED . $edit_script . '?id=' . $item->id, $item->name) . '<br> ';
        }
        $result .= \Zukunft\ZukunftCom\main\php\web\btn_add('Add ' . $item_type, $add_script);
        $result .= '<br>';

        return $result;
    }

}