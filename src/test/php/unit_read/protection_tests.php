<?php

/*

    test/php/unit_read/protection.php - database unit testing of the protection handling
    ---------------------------------


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

use cfg\protection_type;
use cfg\protection_type_list;
use test\test_cleanup;

class protection_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $protection_types;

        // init
        $t->name = 'protection read db->';

        $t->header('Unit database tests of the protection handling');

        $t->subheader('Protection types tests');

        // load the protection types
        $lst = new protection_type_list();
        $result = $lst->load($db_con);
        $t->assert('load types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $protection_types->id(protection_type::NO_PROTECT);
        $t->assert('check ' . protection_type::NO_PROTECT, $result, 1);
    }

}

