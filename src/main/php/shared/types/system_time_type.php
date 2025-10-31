<?php

/*

    model/system/system_time_type.php - the areas of execution times
    -------------------------------

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

namespace Zukunft\ZukunftCom\main\php\shared\types;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_object.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;

class system_time_type extends type_object
{

    /*
     * code links
     */

    // list of the monitored areas
    const string DEFAULT = "not_specified";
    const string DB_WRITE = "db_write";
    const string DB_READ = "db_read";
    const string DB_UPGRADE = "db_upgrade";
    const string DB_SETUP = "db_setup";
    const string DB_OPEN = "db_open";
    const string DB_CHECK = "db_check";
    const string LOAD_SYS_CONFIG = "load_sys_config";
    const string LOAD_CONFIG = "load_config";
    const string LOAD_CONFIG_CACHE = "load_config_cache";
    const string WRITE_CONFIG_CACHE = "write_config_cache";
    const string LOAD_FRONTEND = "load_frontend";
    const string LOAD_TYPES = "load_types";
    const string LOAD_USER_DATA = "load_user_data";
    const string LOCALHOST_VIEWS = "localhost view";
    const string URL_TO_HTML = "url to html";
    const string CLOSE = "close";

    // time unit the db connection is open e.g. reading the scripts
    const string INIT = "INIT";
    const string MAP_JSON = "map_json";
    const string API_CTRL = "api_ctrl";


    /*
     * database link
     */

    // comments used for the database creation
    const string TBL_COMMENT = 'to define the execution time groups';
    const string FLD_ID = 'system_time_type_id'; // name of the id field as const for other const

}
