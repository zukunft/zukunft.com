<?php

/*

  ref_test.php - TESTing of the REF class
  ------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_ref_test(testing $t)
{

    global $usr;

    dsp_test_header('Test the ref class (src/main/php/model/ref/ref.php)');

    // create the test ref
    $wrd = $t->test_word(word::TN_ADD);
    $t->test_ref(word::TN_ADD, ref::TEST_REF_NAME, ref_type::WIKIDATA);

    // load by phrase and type
    $ref_type = get_ref_type(ref_type::WIKIDATA);
    $ref = new ref;
    $ref->usr = $usr;
    $ref->phr = $wrd->phrase();
    $ref->ref_type = $ref_type;
    $ref->load();
    $result = $ref->external_key;
    $target = ref::TEST_REF_NAME;
    $t->dsp('ref->load "' . word::TN_ADD . '" in ' . ref_type::WIKIDATA, $target, $result, TIMEOUT_LIMIT_PAGE_LONG);

    if ($ref->id > 0) {
        // load by id and test the loading of the objects
        $ref2 = new ref;
        $ref2->usr = $usr;
        $ref2->id = $ref->id;
        $ref2->load();
        $result = $ref2->phr->name;
        $target = word::TN_ADD;
        $t->dsp('ref->load_object word', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
        $result = $ref2->ref_type->name;
        $target = ref_type::WIKIDATA;
        $t->dsp('ref->load_object type', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
    }

}