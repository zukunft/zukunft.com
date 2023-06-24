<?php

/*

    model/helper/config.php - additional behavior for the system and user config graph value tree
    -----------------------


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

namespace cfg;

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once MODEL_HELPER_PATH . 'library.php';
include_once MODEL_VERB_PATH . 'verb.php';
include_once API_SYSTEM_PATH . 'type_list.php';
include_once WEB_USER_PATH . 'user_type_list.php';

use api\value_list_api;

class config_numbers extends value_list
{

    /*
     * cast
     */

    /**
     * @return value_list_api the object type list frontend api object
     */
    function api_obj(): value_list_api
    {
        return parent::api_obj();
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * load
     */

    /**
     *
     * @return bool true if the values of the user configuration have been loaded
     */
    function load_usr_cgf(sql_db $db_con, user $usr): bool
    {
        $result = false;
        $root_phr = new phrase($this->user(), word::SYSTEM_CONFIG);
        $phr_lst = $root_phr->all_children();
        $val_lst = new value_list($usr);
        $val_lst->load_by_phr_lst($phr_lst);
        if (!$val_lst->is_empty()) {
            $result = true;
        }
        return $result;
    }

}