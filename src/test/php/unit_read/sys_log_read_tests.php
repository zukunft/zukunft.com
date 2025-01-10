<?php

/*

    test/php/unit_read/sys_log.php - database unit testing of the error log functions
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

namespace unit_read;

use cfg\system\sys_log_list;
use cfg\user\user;
use test\test_cleanup;

class sys_log_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'error log read db->';

        $t->header('Unit database tests of the error log classes (src/main/php/model/log/* and src/main/php/model/user/log_*)');

        $t->subheader('Load error log tests');

        // use the system user for the database updates
        $sys_usr = new user;
        $sys_usr->load_by_id(SYSTEM_USER_TEST_ID);

        // check if loading the system errors technically works
        $err_lst = new sys_log_list();
        $err_lst->set_user($sys_usr);
        $err_lst->dsp_type = sys_log_list::DSP_ALL;
        $err_lst->page = 0;
        $err_lst->size = 20;
        $result = $err_lst->load_all();
        $t->assert('system errors', $result, true);

        $t->subheader('API unit db tests');
        $t->assert_api($err_lst, 'sys_log_list_setup', false, true);

    }

}

