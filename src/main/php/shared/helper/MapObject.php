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
     * convert a frontend object to a backend object via api json
     * @param db_object_ui $ui_obj the filled frontend object
     * @param user|null $usr the frontend user used to define the owner of the backend object
     * @return db_object_seq_id|db_object_multi_user|user the backend object filled with the value from the frontend object
     */
    function convertToDb(db_object_ui $ui_obj, ?user $usr = null): db_object_seq_id|db_object_multi_user|user
    {
        $db_obj = $this->dbObject($ui_obj, $usr);
        $db_obj->api_mapper($ui_obj->api_array());
        return $db_obj;
    }

}