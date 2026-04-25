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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\system\sys_log_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class sys_log_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'error log read db->';

        // start the test section (ts)
        $ts = 'db read error log ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        // use the system test user for the database updates
        $sys_usr = new user;
        $sys_usr->load_by_name(users::SYSTEM_TEST_NAME);

        // check if loading the system errors technically works
        $err_lst = new sys_log_list();
        $err_lst->set_user($sys_usr);
        $err_lst->dsp_type = sys_log_list::DSP_ALL;
        $err_lst->page = 0;
        $err_lst->size = 20;
        $result = $err_lst->load_all();
        $t->assert('system errors', $result, true);

        $t->subheader($ts . 'api');
        $t->assert_api($err_lst, 'sys_log_list_setup', [api_types::HEADER], true);

    }

}

