<?php

/*

  test_term.php - TESTing of the TERM class
  -------------
  

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

function run_term_test()
{

    global $usr;

    test_header('est the term class (classes/term.php)');

    // load the main test word
    $wrd_zh = test_word(word::TN_ZH);

    // check that adding the predefined word "Company" creates an error message
    $term = new term;
    $term->name = word::TN_ZH;
    $term->usr = $usr;
    $term->load();
    $target = 'A word with the name "' . word::TN_ZH . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    test_dsp_contains(', term->load for id ' . $wrd_zh->id, $target, $result);

    // ... check also for a triple
    $term = new term;
    $term->name = phrase::TN_ZH_CITY;
    $term->usr = $usr;
    $term->load();
    $target = 'A triple with the name "' . phrase::TN_ZH_CITY . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    test_dsp_contains(', term->load for id ' . $wrd_zh->id, $target, $result);

    // ... check also for a verb
    $term = new term;
    $term->name = 'is a';
    $term->usr = $usr;
    $term->load();
    $target = 'A verb with the name "is a" already exists. Please use another name.';
    $result = $term->id_used_msg();
    test_dsp_contains(', term->load for id ' . $wrd_zh->id, $target, $result);

    // ... check also for a formula
    $term = new term;
    $term->name = formula::TN_INCREASE;
    $term->usr = $usr;
    $term->load();
    // each formula name has also a word
    $target = 'A formula with the name "' . formula::TN_INCREASE . '" already exists. Please use another name.';
    $result = $term->id_used_msg();
    test_dsp_contains(', term->load for id ' . $wrd_zh->id, $target, $result);

}