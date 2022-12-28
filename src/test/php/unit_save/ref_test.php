<?php

/*

  ref_test.php - TESTing of the REF class
  ------------
  

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

use api\word_api;

function run_ref_test(testing $t): void
{

    global $usr;

    dsp_test_header('Test the ref class (src/main/php/model/ref/ref.php)');

    // create the test ref
    $wrd = $t->test_word(word_api::TN_ADD);
    $t->test_ref(word_api::TN_ADD, ref::TEST_REF_NAME, ref_type::WIKIDATA);

    // load by phrase and type
    $ref_type = get_ref_type(ref_type::WIKIDATA);
    $ref = new ref($usr);
    $ref->phr = $wrd->phrase();
    $ref->ref_type = $ref_type;
    $ref->load_obj_vars();
    $result = $ref->external_key;
    $target = ref::TEST_REF_NAME;
    $t->dsp('ref->load "' . word_api::TN_ADD . '" in ' . ref_type::WIKIDATA, $target, $result, TIMEOUT_LIMIT_PAGE_LONG);

    if ($ref->id() > 0) {
        // load by id and test the loading of the objects
        $ref2 = new ref($usr);
        $ref2->load_by_id($ref->id());
        $result = $ref2->phr->name();
        $target = word_api::TN_ADD;
        $t->dsp('ref->load_object word', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
        $result = $ref2->ref_type->name;
        $target = ref_type::WIKIDATA;
        $t->dsp('ref->load_object type', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
    }

}