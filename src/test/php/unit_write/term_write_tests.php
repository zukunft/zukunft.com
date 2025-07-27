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

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';

use cfg\phrase\term;
use cfg\word\word;
use html\html_base;
use shared\library;
use shared\const\formulas;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class term_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        $lib = new library();
        $html = new html_base();

        $t->header('term database write tests');

        // load the main test word
        $wrd_zh = $t->test_word(words::ZH);

        // check that adding the predefined word "Company" creates an error message
        $term = new term($usr);
        $term->load_by_obj_name(words::ZH);
        $target = 'A word with the name "' . words::ZH . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.';
        $result = $html->dsp_err($term->id_used_msg_text($wrd_zh));
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a triple
        $term = new term($usr);
        $term->load_by_obj_name(triples::CITY_ZH);
        $target = '<style class="text-danger">A triple with the name "' . triples::CITY_ZH . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</style>';
        $result = $html->dsp_err($term->id_used_msg_text($wrd_zh));
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a verb
        $term = new term($usr);
        $term->load_by_obj_name(verbs::IS);
        $target = '<style class="text-danger">A word with the name "" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</style>';
        $result = $html->dsp_err($term->id_used_msg_text($wrd_zh));
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

        // ... check also for a formula
        $term = new term($usr);
        $term->load_by_obj_name(formulas::INCREASE);
        // each formula name has also a word
        $target = 'A formula with the name "' . formulas::INCREASE . '" already exists. '
            . 'Please use another ' . $lib->class_to_name(word::class) . ' name.';
        $result = $html->dsp_err($term->id_used_msg_text($wrd_zh));
        $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    }

}