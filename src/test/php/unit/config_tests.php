<?php

/*

    test/unit/config_tests.php - unit testing of the system configuration
    --------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_HELPER . 'config_numbers.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_type.php';
include_once paths::SERVICE . 'config.php';
include_once paths::SHARED_TYPES . 'db_cache_types.php';
include_once test_paths::CREATE . 'test_values.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\config_numbers;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\shared\types\db_cache_types;
use Zukunft\ZukunftCom\test\php\create\test_values;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class config_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
        $t_val = new test_values($t);
        $t->name = 'config->';
        $t->resource_path = 'db/system/';

        // start the test section (ts)
        $ts = 'unit config ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $cfg = new config();
        $t->assert_sql_table_create($cfg);
        $t->assert_sql_index_create($cfg);

        $t->subheader($ts . 'sql write insert');
        $cfg = new config();
        $cfg->code_id = config::VERSION_DB;
        $cfg->value = def::FIRST_VERSION;
        $t->assert_sql_insert($sc, $cfg, [sql_type::LOG]);
        $cfg_db = clone $cfg;
        $cfg->value = def::PRG_VERSION;
        $cfg->name = config::VERSION_DB_NAME;
        $cfg->description = config::VERSION_DB_COM;
        $t->assert_sql_update($sc, $cfg, $cfg_db, [sql_type::LOG]);


        $t->subheader($ts . 'ip user permission');

        // the pod permission that decides if a user without login can change data in the database
        $test_name = 'an ip user can change data if this pod permits it';
        $t->assert_true($test_name, $t_val->config_ip_user_change(true)->ip_user_can_change());
        $test_name = 'an ip user cannot change data if this pod does not permit it';
        $t->assert_false($test_name, $t_val->config_ip_user_change(false)->ip_user_can_change());
        // a missing permission is as restrictive as the default of config.yaml
        $test_name = 'an ip user cannot change data if the permission is missing';
        $t->assert_false($test_name, $t_val->config_empty()->ip_user_can_change());


        $t->subheader($ts . 'database cache switches');

        // each database cache can be switched off by a pod setting in config.yaml
        // and a pod without the switch uses the cache
        $names = config_numbers::CACHE_ALLOWED_NAMES[db_cache_types::TYPES];
        $test_name = 'the types cache is used if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'the types cache is not used if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'a pod without the switch uses the types cache';
        $t->assert_true($test_name, $t_val->config_empty()->cache_allowed(db_cache_types::TYPES));
        $test_name = 'the system config switch does not change the types cache';
        $names = config_numbers::CACHE_ALLOWED_NAMES[db_cache_types::SYSTEM_CONFIG];
        $t->assert_true($test_name, $t_val->config_cache_switch($names, false)->cache_allowed(db_cache_types::TYPES));
        $test_name = 'an unknown cache switch is reported and the cache is not used';
        $t->assert_false($test_name, $t_val->config_empty()->cache_allowed('unexpected_cache_type'));

        // the html page cache (db_cache_pages) has its own pod switch
        $names = config_numbers::CACHE_PAGES_ALLOWED_NAMES;
        $test_name = 'the html pages are cached if the pod setting is true';
        $t->assert_true($test_name, $t_val->config_cache_switch($names, true)->page_cache_allowed());
        $test_name = 'the html pages are not cached if the pod setting is false';
        $t->assert_false($test_name, $t_val->config_cache_switch($names, false)->page_cache_allowed());
        $test_name = 'a pod without the switch caches the html pages';
        $t->assert_true($test_name, $t_val->config_empty()->page_cache_allowed());

    }

}