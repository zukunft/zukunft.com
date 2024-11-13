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

include_once SHARED_TYPES_PATH . 'verbs.php';

use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\phrase;
use cfg\phrase_type;
use cfg\phrase_types;
use cfg\verb;
use cfg\triple;
use cfg\triple_list;
use shared\types\verbs;
use test\test_cleanup;

class triple_read_tests
{

    function run(test_cleanup $t): void
    {

        global $verbs;
        global $db_con;
        global $usr;
        global $phrase_types;

        // init
        $t->name = 'triple read db->';
        $t->resource_path = 'db/triple/';

        $t->header('triple db read tests');

        $t->subheader('triple prepare read tests');
        // load the verb used for testing
        $is_id = $verbs->id(verbs::IS);
        // load the words used for testing the triples (Zurich (City) and Zurich (Canton)
        $wrd_zh = $t->load_word(word_api::TN_ZH);
        $wrd_canton = $t->load_word(word_api::TN_CANTON);
        // create the group test word
        $wrd_company = $t->test_word(word_api::TN_COMPANY);

        $t->subheader('triple load tests');
        $test_name = 'load triple ' . triple_api::TN_READ . ' by name and id';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_api::TN_READ);
        $trp_by_id = new triple($t->usr1);
        $trp_by_id->load_by_id($trp->id(), triple::class);
        $t->assert($test_name, $trp_by_id->name(), triple_api::TN_READ);
        $t->assert($test_name, $trp_by_id->description, triple_api::TD_READ);

        $test_name = 'triple load ' . word_api::TN_CANTON . ' ' . word_api::TN_ZH . ' by link';
        $lnk_canton = new triple($t->usr1);
        $lnk_canton->load_by_link_id($wrd_zh->id(), $is_id, $wrd_canton->id());
        $target = word_api::TN_ZH . ' (' . word_api::TN_CANTON . ')';
        $result = $lnk_canton->name();
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $test_name = 'triple generated name of ' . word_api::TN_CANTON . ' ' . word_api::TN_ZH . ' via function';
        $result = $lnk_canton->name_generated();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple load ' . triple_api::TN_ZH_COMPANY . ' by link';
        $lnk_company = new triple($t->usr1);
        $lnk_company->load_by_link_id($wrd_zh->id(), $is_id, $wrd_company->id());
        $target = triple_api::TN_ZH_COMPANY;
        $result = $lnk_company->name();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple generated name of ' . triple_api::TN_ZH_COMPANY . ' via function';
        $target = 'Zurich (Company)';
        $result = $lnk_company->name_generated();
        $t->assert($test_name, $result, $target);

    }
}

