<?php

/*

    test/unit/pod_tests.php - unit testing of the mash pod network
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit;

use cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'pod_type.php';
include_once paths::MODEL_SYSTEM . 'pod_status.php';
include_once paths::MODEL_SYSTEM . 'pod.php';
include_once paths::MODEL_PHRASE . 'phrase_table_status.php';
include_once paths::MODEL_PHRASE . 'phrase_table.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\system\pod;
use cfg\system\pod_status;
use cfg\system\pod_type;
use test\test_cleanup;

class pod_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t->name = 'pod->';
        $t->resource_path = 'db/pod/';

        // start the test section (ts)
        $ts = 'unit pod ';
        $t->header($ts);

        $t->subheader($ts . 'type sql setup');
        $pod_typ = new pod_type('');
        $t->assert_sql_table_create($pod_typ);
        $t->assert_sql_index_create($pod_typ);

        $t->subheader($ts . 'status sql setup');
        $pod_sta = new pod_status('');
        $t->assert_sql_table_create($pod_sta);
        $t->assert_sql_index_create($pod_sta);

        $t->subheader($ts . 'sql setup');
        $pod = new pod('');
        $t->assert_sql_table_create($pod);
        $t->assert_sql_index_create($pod);
        $t->assert_sql_foreign_key_create($pod);

    }

}
