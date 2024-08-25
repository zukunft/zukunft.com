<?php

/*

    test/php/unit_read/share.php - database unit testing of the share handling
    ----------------------------


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

include_once SHARED_TYPES_PATH . 'share_type.php';

use shared\types\share_type as share_type_shared;
use cfg\share_type;
use cfg\share_type_list;
use test\test_cleanup;

class share_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $share_types;

        // init
        $t->name = 'share read db->';

        $t->header('Unit database tests of the share handling');

        $t->subheader('Share types tests');

        // load the share types
        $lst = new share_type_list();
        $result = $lst->load($db_con);
        $t->assert('load types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $share_types->id(share_type_shared::PUBLIC);
        $t->assert('check ' . share_type_shared::PUBLIC, $result, 1);
    }

}

