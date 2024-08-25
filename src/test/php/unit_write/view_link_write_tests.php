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

namespace unit_write;

use api\view\view as view_api;
use test\test_cleanup;

class view_link_write_tests
{

    function run(test_cleanup $t): void
    {

        $t->header('view link db write tests');

        $t->subheader('view link write sandbox tests for ' . view_api::TN_ADD);
        // TODO activate (set object id instead of id)
        //$t->assert_write_link($t->view_link_filled_add());


    }

}