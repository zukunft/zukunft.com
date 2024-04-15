<?php

/*

    test/php/unit_write/term_tests.php - write test TERMS to the database and check the results
    ----------------------------------
  

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

use api\formula\formula as formula_api;
use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\term;
use cfg\verb;
use cfg\word;
use shared\library;
use test\test_cleanup;

class term_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();

        $t->header('est the term class (classes/term.php)');

        // load the main test word
        $wrd_zh = $t->test_word(word_api::TN_ZH);

        // check that adding the predefined word "Company" creates an error message
        $term = new term($usr);
        $term->load_by_obj_name(word_api::TN_ZH);
        $target = 'A word with the name "' . word_api::TN_ZH . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.';
        $result = $term->id_used_msg($wrd_zh);
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a triple
        $term = new term($usr);
        $term->load_by_obj_name(triple_api::TN_ZH_CITY);
        $target = '<style class="text-danger">A triple with the name "' . triple_api::TN_ZH_CITY . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</style>';
        $result = $term->id_used_msg($wrd_zh);
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a verb
        $term = new term($usr);
        $term->load_by_obj_name(verb::IS);
        $target = '<style class="text-danger">A word with the name "" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</style>';
        $result = $term->id_used_msg($wrd_zh);
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a formula
        $term = new term($usr);
        $term->load_by_obj_name(formula_api::TN_ADD);
        // each formula name has also a word
        $target = 'A formula with the name "' . formula_api::TN_ADD . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.';
        $result = $term->id_used_msg($wrd_zh);
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    }

}