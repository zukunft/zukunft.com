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

function run_term_test(testing $t): void
{

    global $usr;

    $t->header('est the term class (classes/term.php)');

    // load the main test word
    $wrd_zh = $t->test_word(word::TN_ZH);

    // check that adding the predefined word "Company" creates an error message
    $term = new term($usr);
    $term->load_by_obj_name(word::TN_ZH);
    $target = 'A word with the name "' . word::TN_ZH . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    // ... check also for a triple
    $term = new term($usr);
    $term->load_by_obj_name(phrase::TN_ZH_CITY);
    $target = 'A triple with the name "' . phrase::TN_ZH_CITY . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    // ... check also for a verb
    $term = new term($usr);
    $term->load_by_obj_name(verb::IS_A);
    $target = 'A verb with the name "is a" already exists. Please use another name.';
    $result = $term->id_used_msg();
    $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

    // ... check also for a formula
    $term = new term($usr);
    $term->load_by_obj_name(formula::TN_INCREASE);
    // each formula name has also a word
    $target = 'A formula with the name "' . formula::TN_INCREASE . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    $t->dsp_contains(', term->load for id ' . $wrd_zh->id(), $target, $result);

}