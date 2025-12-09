<?php

/*

    test/php/unit_read/phrase.php - database unit testing of the phrase functions
    -----------------------------


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

include_once paths::SHARED_TYPES . 'phrase_type.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_type as phrase_type_shared;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->name = 'phrase read db->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'db read phrase ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        $test_name = 'load phrase ' . words::MATH . ' by word name and id';
        $phr = new phrase($t->usr1);
        $phr->load_by_name(words::MATH);
        $wrd_by_id = new phrase($t->usr1);
        $wrd_by_id->load_by_id($phr->id(), phrase::class);
        $t->assert($test_name, $wrd_by_id->name(), words::MATH);

        $test_name = 'load phrase ' . triples::PI . ' by triple name and id';
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triples::PI);
        $wrd_by_id = new phrase($t->usr1);
        $wrd_by_id->load_by_id($phr->id(), phrase::class);
        $t->assert($test_name, $wrd_by_id->name(), triples::PI);


        $t->subheader($ts . 'type');

        // test reading a phrase type via API that is not yet included in the preloaded phrase type
        // e.g. because it has been just added by the user to request e new phrase type
        $test_name = 'load phrase type ' . phrase_type_shared::NORMAL . ' by id';
        global $sys;
        $phr_typ_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::NORMAL);
        $phr_typ = new phrase_type(phrase_type_shared::NORMAL);
        $phr_typ->load_by_id($phr_typ_id);
        $t->assert($test_name, $phr_typ->get_code_id(), phrase_type_shared::NORMAL);

    }

}

