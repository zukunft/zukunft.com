<?php

/*

    test/php/unit_write/view_link_write_tests.php - write test VIEW term LINKs to the database and check the results
    ---------------------------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_link_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t_msk = new test_views($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write view link ';
        $t->header($ts);

        $t->subheader($ts . 'sandbox for ' . views::TEST_ADD_NAME);
        $msk_lnk = $t_msk->term_view();
        $msk_lnk->save($usr_msg);
        // TODO Prio 2 activate (set object id instead of id)
        //$t->assert_write_link($t_msk->view_link_filled_add());


    }

    /**
     * check if the view links used for unit testing are created
     * and if not create the missing links
     *
     * @param test_cleanup $t
     * @return void
     */
    function create_base_view_links(test_cleanup $t): void
    {
        // init
        $t_msk = new test_views($t);
        $usr_msg = new user_message($t->usr1);

        $msk_lnk = $t_msk->term_view_filled_add();
        $msk_lnk->save($usr_msg);
    }

}