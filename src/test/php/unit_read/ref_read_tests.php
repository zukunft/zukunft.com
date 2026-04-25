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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'refs.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_read_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;
        global $db_con;

        // init
        $lib = new library();
        $t->name = 'ref db read->';

        // start the test section (ts)
        $ts = 'db read reference ';
        $t->header($ts);

        $t->subheader($ts . 'types');

        // load the ref types
        $lst = new ref_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        // TODO check
        $result = $sys->typ_lst->phr_typ->id(phrase_type_shared::NORMAL);
        $t->assert('check ' . phrase_type_shared::NORMAL, $result, 1);

        $t->subheader($ts . 'apis');

        $ref = new ref($t->usr1);
        $ref->load_by_id(refs::PI_ID);
        $t->assert_api($ref);

    }

}

