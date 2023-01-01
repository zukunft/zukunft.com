<?php

/*

    test/unit/batch_log.php - unit testing of the user log functions
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

use api\triple_api;

class batch_job_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        $t->header('Unit tests of the batch job class (src/main/php/log/batch_job.php)');

        $t->subheader('SQL statement tests');

        // init
        $lib = new library();
        $db_con = new sql_db();
        $t->name = 'batch_job->';
        $t->resource_path = 'db/job/';

        // sql to load the word by id
        $job = new batch_job($usr);
        $t->assert_load_sql_id($db_con, $job);

    }

}
