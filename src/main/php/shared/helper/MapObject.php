<?php

/*

    shared/helper/MapObject.php - temp helper object to map the frontend to backend objects until the api is fast enough
    ---------------------------

    $map_obj is the suggested var name


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_HELPER . 'db_object.php';
include_once html_paths::SANDBOX . 'db_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_multi_user;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\value\value as value_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\view\view as view_ui;
use Zukunft\ZukunftCom\main\php\web\component\component as component_ui;
use Zukunft\ZukunftCom\main\php\web\view\view_relation as view_relation_ui;

class MapObject
{
    /**
     * get the corresponding backend object to the given frontend object
     *
     * @param db_object_ui $ui_obj the frontend object
     * @param user|null $usr the user of the frontend already converted to a backend user object
     * @return db_object_seq_id|db_object_multi_user the empty backend object with the user set
     */
    function dbObject(db_object_ui $ui_obj, ?user $usr = null): db_object_seq_id|db_object_multi_user
    {
        if ($ui_obj::class == user_ui::class) {
            return new user();
        } elseif ($ui_obj::class == word_ui::class) {
            return new word($usr);
        } elseif ($ui_obj::class == verb_ui::class) {
            return new verb();
        } elseif ($ui_obj::class == triple_ui::class) {
            return new triple($usr);
        } elseif ($ui_obj::class == source_ui::class) {
            return new source($usr);
        } elseif ($ui_obj::class == ref_ui::class) {
            return new ref($usr);
        } elseif ($ui_obj::class == value_ui::class) {
            return new value($usr);
        } elseif ($ui_obj::class == formula_ui::class) {
            return new formula($usr);
        } elseif ($ui_obj::class == result_ui::class) {
            return new result($usr);
        } elseif ($ui_obj::class == view_ui::class) {
            return new view($usr);
        } elseif ($ui_obj::class == component_ui::class) {
            return new component($usr);
        } elseif ($ui_obj::class == view_relation_ui::class) {
            return new view_relation($usr);
        } else {
            return new db_object_seq_id();
        }
    }

    /**
     * get the corresponding frontend object to the given backend object
     *
     * @param db_object_seq_id|db_object_multi_user $obj the backend object to select the frontend object
     * @return db_object_ui the empty frontend object corresponding to the frontend object
     */
    function uiObject(db_object_seq_id|db_object_multi_user $obj): db_object_ui
    {
        if ($obj::class == user::class) {
            return new user_ui();
        } elseif ($obj::class == word::class) {
            return new word_ui();
        } elseif ($obj::class == verb::class) {
            return new verb_ui();
        } elseif ($obj::class == triple::class) {
            return new triple_ui();
        } elseif ($obj::class == source::class) {
            return new source_ui();
        } elseif ($obj::class == ref::class) {
            return new ref_ui();
        } elseif ($obj::class == value::class) {
            return new value_ui();
        } elseif ($obj::class == formula::class) {
            return new formula_ui();
        } elseif ($obj::class == result::class) {
            return new result_ui();
        } elseif ($obj::class == view::class) {
            return new view_ui();
        } elseif ($obj::class == component::class) {
            return new component_ui();
        } elseif ($obj::class == view_relation::class) {
            return new view_relation_ui();
        } else {
            return new db_object_ui();
        }
    }

    /**
     * convert a frontend object to a backend object via api json
     * @param db_object_ui $ui_obj the filled frontend object
     * @param user|null $usr the frontend user used to define the owner of the backend object
     * @return db_object_seq_id|db_object_multi_user|user the backend object filled with the value from the frontend object
     */
    function convertToDb(db_object_ui $ui_obj, user_message $usr_msg, ?user $usr = null): db_object_seq_id|db_object_multi_user|user
    {
        $db_obj = $this->dbObject($ui_obj, $usr);
        $db_obj->api_mapper($ui_obj->api_array(), $usr_msg);
        return $db_obj;
    }

    /**
     * convert a frontend object to a backend object via api json
     * @param db_object_seq_id|db_object_multi_user|user $obj the backend object filled with the value from the frontend object
     * @param user_message_ui $usr_msg the frontend user used to define the owner of the backend object
     * @return db_object_ui|user_ui the filled frontend object
     */
    function convertToUi(db_object_seq_id|db_object_multi_user|user $obj, user_message_ui $usr_msg): db_object_ui|user_ui
    {
        $ui_obj = $this->uiObject($obj);
        $ui_obj->api_mapper($obj->api_json_array(new api_type_list([])), $usr_msg);
        return $ui_obj;
    }

    /**
     * convert a frontend message object to a backend message object via api json
     * @param user_message_ui $ui_msg the filled frontend object
     * @return user_message the backend object filled with the value from the frontend object
     */
    function convertMsgToDb(user_message_ui $ui_msg): user_message
    {
        $db_msg = new user_message();
        $db_msg->api_mapper($ui_msg->api_array());
        return $db_msg;
    }

    /**
     * convert a backend message object to a frontend message object via api json
     * @param user_message $db_msg the filled backend object
     * @return user_message_ui the frontend object filled with the value from the backend object
     */
    function convertMsgToUi(user_message $db_msg): user_message_ui
    {
        $ui_msg = new user_message_ui();
        $ui_msg->api_mapper($db_msg->api_array());
        return $ui_msg;
    }

}