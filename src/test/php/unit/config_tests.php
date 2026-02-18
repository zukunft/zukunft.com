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
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_type.php';
include_once paths::SERVICE . 'config.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class config_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $sc = new sql_creator();
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

    }

}