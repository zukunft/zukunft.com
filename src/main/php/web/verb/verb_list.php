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
include_once html_paths::SHARED_CONST . 'views.php';
include_once html_paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\type_list;
use Zukunft\ZukunftCom\main\php\web\user\user;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\library;

class verb_list extends type_list
{

    /*
     * const
     */

    // the view that shows the complete list, used as the target of the "... and n more" tail
    const int VIEW_ALL_ID = views::VERBS_ID;


    private ?user $usr = null; // the user object of the person for whom the verb list is loaded, so to say the viewer

    /*
     * construct and map
     */

    /**
     * create an empty list, which needs no message; a caller with an api json message
     * fills the list with set_from_json($api_json, $msg), which reports the mapping problems
     */
    function __construct()
    {
        $this->reset([]);
    }


    /*
     * set and get
     */

    /**
     * set the vars of these list display objects bases on the api message
     * @param string $json_api_msg an api json message as a string
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @param bool $allow_duplicates true if the same verb may be part of this list more than once
     *                               e.g. to count the verb usages of a triple list
     * @return bool true if there are no errors
     */
    function set_from_json(string $json_api_msg, user_message $msg, bool $allow_duplicates = false): bool
    {
        return $this->set_from_json_array(json_decode($json_api_msg, true), $msg, verb::class, $allow_duplicates);
    }

    /**
     * set the vars of a term object based on the given json
     * @param array $json_array an api single object json message
     * @param user_message $msg ok or a warning e.g. if the server version does not match
     * @param string $class to force to use the verb child class of the type object
     * @param bool $allow_duplicates true if the same verb may be part of this list more than once
     * @return bool true if there are no errors
     */
    function set_from_json_array(
        array        $json_array,
        user_message $msg,
        string       $class = verb::class,
        bool         $allow_duplicates = false
    ): bool
    {
        foreach ($json_array as $value) {
            $new = clone new verb();
            if ($new->api_mapper($value, $msg)) {
                $this->add_obj($new, $msg, $allow_duplicates);
            }
        }
        return $msg->is_ok();
    }

    /*
     * display
     */

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
        $vrb = null;
        foreach ($item_lst as $item) {
            $result .= $html->ref(rest_ctrl::PATH_FIXED . $edit_script . '?id=' . $item->id, $item->name) . '<br> ';
            $vrb = $item;
        }
        if ($vrb != null) {
            // TODO Prio 1 add tooltip
            $result .= $vrb->btn_add();
        }
        $result .= '<br>';

        return $result;
    }

}