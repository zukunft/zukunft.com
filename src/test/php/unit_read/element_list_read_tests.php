<?php

/*

    test/php/unit_read/element_list_tests.php - formula element list test that only read from the database
    -----------------------------------------
  

    This file is part of zukunft.com - calc with words

    zukunft.com is free software: you can redistribute it and/or modify it
    under the words of the GNU General Public License as
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

use api\word\word as word_api;
use cfg\element_list;
use test\test_cleanup;

class element_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'element list read db->';
        $elm_lst = new element_list($t->usr1);


        $t->header('element list db read tests');

        $test_name = 'load the elements of the scale minute to second formula and check if it contains the word second';
        $elm_lst->load_by_frm($t->formula()->id());
        // TODO activate
        //$t->assert_contains($test_name, $elm_lst->names(), word_api::TN_SECOND);

    }

}

