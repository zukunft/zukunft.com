<?php

/*

    model/helper/system_object.php - a header object for the system data cache and execution time tracking
    ------------------------------

    the suggested var name in the backend is global $sys
    this object contains objects and vars from the database that are often used
    and thet are user independent
    the user specific cache is in the data_object which has the var name global $cac


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

namespace Zukunft\ZukunftCom\main\php\cfg\helper;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

// more specific includes are switched off to avoid circular includes
//include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_LOG_TEXT . 'text_log.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
//include_once paths::MODEL_USER . 'user.php';
//include_once paths::MODEL_VIEW . 'view_relation_type_list.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\log_text\text_log;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\view\view_relation_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;

class system_object
{

    /*
     *  object vars
     */

    // execution times and system control

    // name php script that has been called by the webserver
    public string $script;
    // names of the php functions
    public string  $trace;
    // the initial time the user done the request to measure the execution time
    public float $start_time;
    // time after that a log entry should be created to detect too long execution times and to be able to improve the code
    public float  $time_limit;
    public system_time_list $times;
    // to avoid repeating the same message
    public array $log_msg_lst;
    // the log object for standard io logging
    public text_log $log_txt;

    // all preloaded types
    public type_lists $typ_lst;


    /*
     * construct and map
     */

    /**
     * always set the user because always someone must have requested to create the list
     * e.g. an admin can have requested to import words for another user
     */
    function __construct(string $script_name)
    {
        $this->script = $script_name;
        $this->trace = "";
        $this->start_time = microtime(true);
        $this->time_limit = microtime(true) + 2;
        $this->times = new system_time_list();
        $this->log_msg_lst = array();
        $this->typ_lst = new type_lists();
        $this->log_txt = new text_log();
    }


    /*
     * load
     */

    /**
     * load the base data (types, system views) from the database
     * @param sql_db $db_con the database connection as a parameter to be able to force reloading from a not standard db
     * @return bool
     */
    function load_type_lists(sql_db $db_con): bool
    {
        return $this->typ_lst->load($db_con);
    }


    /*
     * api
     */

    /**
     * forward function name for shorted calls
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        return $this->typ_lst->api_json($typ_lst, $usr);
    }


    /*
     * interface
     */

    function view_relation_types(): view_relation_type_list
    {
        return $this->typ_lst->mrl_typ;
    }

    function view_relation_name(?int $id): string
    {
        return $this->typ_lst->mrl_typ->name($id);
    }

    function view_relation_code_id(?int $id): ?string
    {
        return $this->typ_lst->mrl_typ->code_id($id);
    }

}
