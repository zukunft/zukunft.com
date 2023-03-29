<?php

/*

    test/unit_db/batch_job.php - database unit testing of the batch job functions
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

namespace test;

use cfg\job_type_list;
use model\batch_job_list;
use model\user;

class batch_job_unit_db_tests
{

    function run(testing $t): void
    {

        global $db_con;

        // init
        $t->name = 'batch job read db->';

        $t->header('Unit database tests of the batch job classes (src/main/php/model/log/* and src/main/php/model/user/log_*)');

        $t->subheader('Load batch job tests');

        // use the system user for the database updates
        $sys_usr = new user;
        $sys_usr->load_by_id(SYSTEM_USER_ID);

        // check if loading of the first entry is the adding of the word name
        $job_lst = new batch_job_list($sys_usr);
        $job_lst->load_by_type(job_type_list::BASE_IMPORT);
        $first_job = $job_lst->lst()[0];
        $t->assert('first batch job change is adding', $first_job->type, '11');


        $t->subheader('API unit db tests');

        $t->assert_api($job_lst);

    }

}

