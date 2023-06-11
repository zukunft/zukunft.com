<?php

/*

  test_term.php - TESTing of the TERM class
  -------------
  

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

use model\library;
use model\word;
use model\triple;
use model\verb;
use model\formula;
use model\term;
use api\word_api;
use api\triple_api;
use api\formula_api;
use test\test_cleanup;

function run_term_test(test_cleanup $t): void
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
    $target = '<font class="text-danger">A triple with the name "' . triple_api::TN_ZH_CITY . '" already exists. '
        . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</font>';
    $result = $term->id_used_msg($wrd_zh);
    $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    // ... check also for a verb
    $term = new term($usr);
    $term->load_by_obj_name(verb::IS_A);
    $target = '<font class="text-danger">A word with the name "" already exists. '
        . 'Please use another ' . $lib->class_to_name(word::class) . ' name.</font>';
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