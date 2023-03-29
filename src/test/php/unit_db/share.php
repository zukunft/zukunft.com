<?php

/*

    test/unit_db/share.php - database unit testing of the share handling
    ---------------------


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

use cfg\share_type;
use cfg\share_type_list;
use model\db_cl;

class share_unit_db_tests
{

    function run(testing $t): void
    {

        global $db_con;

        // init
        $t->name = 'share read db->';

        $t->header('Unit database tests of the share handling');

        $t->subheader('Share types tests');

        // load the share types
        $lst = new share_type_list();
        $result = $lst->load($db_con);
        $t->assert('load types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = cl(db_cl::SHARE_TYPE, share_type::PUBLIC);
        $t->assert('check ' . share_type::PUBLIC, $result, 1);
    }

}

