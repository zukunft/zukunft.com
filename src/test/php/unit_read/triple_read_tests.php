<?php

/*

    test/php/unit_read/triple.php - database unit testing of the triple functions
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

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';

use cfg\word\triple;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class triple_read_tests
{

    function run(test_cleanup $t): void
    {

        global $vrb_cac;
        global $db_con;
        global $usr;
        global $phr_typ_cac;

        // init
        $t->name = 'triple read db->';
        $t->resource_path = 'db/triple/';

        $t->header('triple db read tests');

        $t->subheader('triple prepare read tests');
        // load the verb used for testing
        $is_id = $vrb_cac->id(verbs::IS);
        // load the words used for testing the triples (Zurich (City) and Zurich (Canton)
        $wrd_zh = $t->load_word(words::ZH);
        $wrd_canton = $t->load_word(words::CANTON);
        // create the group test word
        $wrd_company = $t->test_word(words::COMPANY);

        $t->subheader('triple load tests');
        $test_name = 'load triple ' . triples::MATH_CONST . ' by name and id';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triples::MATH_CONST);
        $trp_by_id = new triple($t->usr1);
        $trp_by_id->load_by_id($trp->id(), triple::class);
        $t->assert($test_name, $trp_by_id->name(), triples::MATH_CONST);
        $t->assert($test_name, $trp_by_id->description, triples::MATH_CONST_COM);

        $test_name = 'triple load ' . words::CANTON . ' ' . words::ZH . ' by link';
        $lnk_canton = new triple($t->usr1);
        $lnk_canton->load_by_link_id($wrd_zh->id(), $is_id, $wrd_canton->id());
        $target = words::ZH . ' (' . words::CANTON . ')';
        $result = $lnk_canton->name();
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $test_name = 'triple generated name of ' . words::CANTON . ' ' . words::ZH . ' via function';
        $lnk_canton->set_name('');
        $result = $lnk_canton->name_generated();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple load ' . triples::COMPANY_ZURICH . ' by link';
        $lnk_company = new triple($t->usr1);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $target = triples::COMPANY_ZURICH;
        $result = $lnk_company->name();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple generated name of ' . triples::COMPANY_ZURICH . ' via function';
        $lnk_company->set_name('');
        $target = 'Zurich (company)';
        $result = $lnk_company->name_generated();
        $t->assert($test_name, $result, $target);

    }
}

