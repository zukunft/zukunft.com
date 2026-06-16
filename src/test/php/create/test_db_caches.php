<?php

/*

    test/create/test_db_caches.php - create the test database cache entries
    ------------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_HELPER . 'db_cache.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';
include_once paths::SHARED_TYPES . 'db_cache_statuum.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UNIT . 'sys_log_tests.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_statuum;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class test_db_caches
{

    /*
     * init
     */

    // use the global test environment
    private test_cleanup $env;

    function __construct(test_cleanup $env)
    {
        $this->env = $env;
    }


    /*
     * map
     */

    /**
     * @return db_cache a batch db_cache entry with some dummy values
     */
    function db_cache(): db_cache
    {
        $t_usr = new test_users();
        $db_cache = new db_cache($t_usr->user_ip());
        $db_cache->id = 1;
        $db_cache->type_id = db_cache_types::SYSTEM_CONFIG_ID;
        $db_cache->data = [];
        return $db_cache;
    }

    /**
     * @return db_cache a batch db_cache entry with all fields set
     */
    function db_cache_filled(): db_cache
    {
        $t_usr = new test_users();
        $db_cache = $this->db_cache();
        $db_cache->usr = $t_usr->user_ip();
        $db_cache->status_id = db_cache_statuum::CLEAN_ID;
        $db_cache->last_update = new DateTime(sys_log_tests::TV_TIME);
        return $db_cache;
    }

}
