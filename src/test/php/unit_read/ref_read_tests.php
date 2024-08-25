<?php

/*

    test/php/unit_read/ref_read_tests.php - database unit testing of external references
    -------------------------------------


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

use api\ref\ref as ref_api;
use cfg\phrase_type;
use cfg\ref;
use cfg\ref_type_list;
use shared\library;
use test\test_cleanup;

class ref_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $phrase_types;

        // init
        $lib = new library();
        $t->name = 'ref db read->';

        $t->header('Rrference db read tests');

        $t->subheader('Reference types tests');

        // load the ref types
        $lst = new ref_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        // TODO check
        $result = $phrase_types->id(phrase_type::NORMAL);
        $t->assert('check ' . phrase_type::NORMAL, $result, 1);

        $t->subheader('API unit db tests');

        $ref = new ref($t->usr1);
        $ref->load_by_id(ref_api::TI_PI);
        $t->assert_api($ref);

    }

}

