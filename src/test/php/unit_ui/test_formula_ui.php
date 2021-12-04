<?php

/*

  test_formula_ui.php - TESTing of the FORMULA User Interface class
  -------------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_formula_ui_test(testing $t)
{

    $t->header('Test the formula frontend scripts (e.g. /formula_add.php)');

    // load the main test word
    $wrd_company = $t->test_word(TEST_WORD);

    // call the add formula page and check if at least some keywords are returned
    $frm = $t->load_formula(formula::TN_INCREASE);
    $result = file_get_contents('https://zukunft.com/http/formula_add.php?word=' . $wrd_company->id . '&back=' . $wrd_company->id . '');
    $target = 'Add new formula for';
    $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the headline', $target, $result, TIMEOUT_LIMIT_PAGE_LONG);
    $target = TEST_WORD;
    $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the linked word ' . TEST_WORD, $target, $result, TIMEOUT_LIMIT_PAGE);

    // test the edit formula frontend
    $result = file_get_contents('https://zukunft.com/http/formula_edit.php?id=' . $frm->id . '&back=' . $wrd_company->id . '');
    $target = formula::TN_INCREASE;
    $t->dsp_contains(', frontend formula_edit.php ' . $result . ' contains at least ' . $frm->name, $target, $result, TIMEOUT_LIMIT_PAGE_SEMI);

    // test the del formula frontend
    $result = file_get_contents('https://zukunft.com/http/formula_del.php?id=' . $frm->id . '&back=' . $wrd_company->id . '');
    $target = formula::TN_INCREASE;
    $t->dsp_contains(', frontend formula_del.php ' . $result . ' contains at least ' . $frm->name, $target, $result, TIMEOUT_LIMIT_PAGE);

}