<?php

/*

  test_formula_ui.php - TESTing of the FORMULA User Interface class
  -------------------
  

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

function run_formula_ui_test()
{

    test_header('Test the formula frontend scripts (e.g. /formula_add.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    // call the add formula page and check if at least some keywords are returned
    $frm = load_formula(formula::TN_INCREASE);
    $result = file_get_contents('https://zukunft.com/http/formula_add.php?word=' . $wrd_company->id . '&back=' . $wrd_company->id . '');
    $target = 'Add new formula for';
    test_dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the headline', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
    $target = TEST_WORD;
    test_dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the linked word ' . TEST_WORD, $target, $result, TIMEOUT_LIMIT_PAGE);

    // test the edit formula frontend
    $result = file_get_contents('https://zukunft.com/http/formula_edit.php?id=' . $frm->id . '&back=' . $wrd_company->id . '');
    $target = formula::TN_INCREASE;
    test_dsp_contains(', frontend formula_edit.php ' . $result . ' contains at least ' . $frm->name, $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test the del formula frontend
    $result = file_get_contents('https://zukunft.com/http/formula_del.php?id=' . $frm->id . '&back=' . $wrd_company->id . '');
    $target = formula::TN_INCREASE;
    test_dsp_contains(', frontend formula_del.php ' . $result . ' contains at least ' . $frm->name, $target, $result, TIMEOUT_LIMIT_PAGE);

}