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

namespace unit_read;

include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_PATH . 'triples.php';

use api\word\word as word_api;
use api\word\triple as triple_api;
use api\phrase\phrase as phrase_api;
use cfg\phrase\phrase_type;
use cfg\phrase\phrase;
use shared\triples;
use test\test_cleanup;
use shared\types\phrase_type as phrase_type_shared;

class phrase_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->header('Unit database tests of the phrase class (src/main/php/model/phrase/phrase.php)');
        $t->name = 'phrase read db->';
        $t->resource_path = 'db/phrase/';

        $t->subheader('Phrase db read tests');

        $test_name = 'load phrase ' . word_api::TN_READ . ' by word name and id';
        $phr = new phrase($t->usr1);
        $phr->load_by_name(word_api::TN_READ);
        $wrd_by_id = new phrase($t->usr1);
        $wrd_by_id->load_by_id($phr->id(), phrase::class);
        $t->assert($test_name, $wrd_by_id->name(), word_api::TN_READ);

        $test_name = 'load phrase ' . triples::TN_PI . ' by triple name and id';
        $phr = new phrase($t->usr1);
        $phr->load_by_name(triples::TN_PI);
        $wrd_by_id = new phrase($t->usr1);
        $wrd_by_id->load_by_id($phr->id(), phrase::class);
        $t->assert($test_name, $wrd_by_id->name(), triples::TN_PI);


        $t->subheader('Phrase type db read tests');

        // test reading a phrase type via API that is not yet included in the preloaded phrase type
        // e.g. because it has been just added by the user to request e new phrase type
        $test_name = 'load phrase type ' . phrase_type_shared::NORMAL . ' by id';
        global $phr_typ_cac;
        $phr_typ_id = $phr_typ_cac->id(phrase_type_shared::NORMAL);
        $phr_typ = new phrase_type(phrase_type_shared::NORMAL);
        $phr_typ->load_by_id($phr_typ_id);
        $t->assert($test_name, $phr_typ->code_id(), phrase_type_shared::NORMAL);

    }

}

