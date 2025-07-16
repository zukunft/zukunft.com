<?php

/*

    test/php/unit_write/ref_tests.php - write test REFS to the database and check the results
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

namespace unit_write;

use cfg\ref\ref;
use cfg\ref\ref_type;
use shared\const\words;
use test\test_cleanup;

class ref_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        $t->header('reference db write tests');

        $t->subheader('reference write sandbox tests for ' . ref::TEST_REF_NAME);
        $t->assert_write_link($t->ref_filled_add());

        // create the test ref
        $wrd = $t->test_word(words::TEST_ADD);
        $t->test_ref(words::TEST_ADD, ref::TEST_REF_NAME, ref_type::WIKIDATA);

        // load by phrase and type
        global $ref_typ_cac;
        $ref = new ref($usr);
        $ref->set_phrase($wrd->phrase());
        $ref->set_predicate_id($ref_typ_cac->id(ref_type::WIKIDATA));
        $ref->load_by_link_ids($wrd->phrase()->id(), $ref->predicate_id());
        $result = $ref->external_key();
        $target = ref::TEST_REF_NAME;
        $t->display('ref->load "' . words::TEST_ADD . '" in ' . ref_type::WIKIDATA, $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);

        if ($ref->id() > 0) {
            // load by id and test the loading of the objects
            $ref2 = new ref($usr);
            $ref2->load_by_id($ref->id());
            $result = $ref2->phrase()->name();
            $target = words::TEST_ADD;
            $t->display('ref->load_object word', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
            $result = $ref2->predicate_name();
            $target = ref_type::WIKIDATA;
            $t->display('ref->load_object type', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        }

        // cleanup of ref specific tests
        $t->write_named_cleanup($wrd, words::TEST_ADD);
    }

}