<?php

/*

    api/api_config.php - combine the objects for the config api message
    ------------------

    target is to reduce the traffic between the frontend and the backend
    this object combines all objects used for the initial response to the frontend
    so the steps are:
    1. the frontend ask for the user-specific frontend configuration and data cache
    2. the backend returns one "big" message created by this object as json or if stable as compressed yaml
    3. the backend remembers what the frontend might have cached and sends push messages in case of updates
       until the frontend confirms the cache dismiss or a connection reset
    the suggested var name in the backend is $ui_cfg


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

namespace Zukunft\ZukunftCom\main\php\api;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

//include_once paths::SERVICE . 'config.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;

class ui_config
{

    /**
     * create a user-specific api json message string of this combine object for the frontend
     * that contains at the moment the preloaded types and the system views
     * target is to include the data cache with the objects most often used by the user
     * the final message should be cached in the backend for faster response
     * in a first step in the database later maybe as a file if it is faster
     * and if possible cached in the frontend in a cookie if possible
     *
     * @param api_type_list|array $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|user_ui|null $usr the user for whom the api message should be created which can differ from the session user
     * @returns string the api json message for the object as a string
     */
    function api_json(
        api_type_list|array $typ_lst,
        user|user_ui|null $usr = null
    ): string
    {
        global $sys;
        global $db_con;
        global $cac;

        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }

        $vars = $sys->typ_lst->api_json_array($typ_lst);
        $vars[json_fields::LIST_SYSTEM_VIEWS] = $cac->sys_msk->api_json_array($typ_lst);
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        return $api_msg->api_json($pod_name, $this::class, $vars, $typ_lst, $usr);
    }

    /**
     * TODO Prio 1 include user_message and return bool and reload only if triggered
     * @param user $usr the user for whom the api message should be created which can differ from the session user
     * @return void
     */
    function reload(user $usr): void
    {
        global $sys;
        global $db_con;
        global $cac;

        // force the reload of the data
        $sys->load_type_lists($db_con);
        if ($cac == null) {
            $cac = new data_object($usr);
        }
        $cac->load_system_views($db_con);
    }

}
