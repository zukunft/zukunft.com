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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_ref = new test_refs($t);
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db write reference ';
        $t->header($ts);

        $t->subheader($ts . 'for ' . ref::TEST_REF_NAME);
        $t->assert_write_link($t_ref->ref_filled_add());

        // create the test ref
        $wrd = $t_db->test_word(word_names::TEST_ADD);
        $t_db->test_ref(word_names::TEST_ADD, ref::TEST_REF_NAME, ref_types::WIKIDATA);

        // load by phrase and type
        global $sys;
        $ref = new ref($usr);
        $ref->set_phrase($wrd->phrase());
        $ref->set_predicate_id($sys->typ_lst->ref_typ->id(ref_types::WIKIDATA));
        $ref->load_by_link_ids($wrd->phrase()->id(), $ref->predicate_id());
        $result = $ref->get_external_key();
        $target = ref::TEST_REF_NAME;
        $t->assert('ref->load "' . word_names::TEST_ADD . '" in ' . ref_types::WIKIDATA, $result, $target, $t::TIMEOUT_LIMIT_PAGE_LONG);

        if ($ref->id() > 0) {
            // load by id and test the loading of the objects
            $ref2 = new ref($usr);
            $ref2->load_by_id($ref->id());
            $result = $ref2->phrase()->name();
            $target = word_names::TEST_ADD;
            $t->assert('ref->load_object word', $result, $target, $t::TIMEOUT_LIMIT_PAGE_LONG);
            $result = $ref2->predicate_name();
            $target = ref_types::WIKIDATA;
            $t->assert('ref->load_object type', $result, $target, $t::TIMEOUT_LIMIT_PAGE_LONG);
        }

        // cleanup of ref specific tests
        $t->write_named_cleanup($wrd, word_names::TEST_ADD);
    }

}