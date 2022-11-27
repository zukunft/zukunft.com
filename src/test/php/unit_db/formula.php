<?php

/*

  test/unit_db/formula.php - database unit testing of the formula functions
  ------------------------


zukunft.com - calc with formulas

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

use cfg\formula_type;

function run_formula_unit_db_tests(testing $t)
{

    global $db_con;
    global $usr;

    $t->header('Unit database tests of the formula class (src/main/php/model/formula/formula.php)');

    $t->subheader('formula types tests');

    // load the formula types
    $lst = new formula_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_formula->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::FORMULA_TYPE, formula_type::CALC);
    $target = 1;
    $t->dsp('unit_db_formula->check ' . formula_type::CALC, $result, $target);

    // check the estimates for the calculation blocks
    $calc_blocks = (new formula_list($usr))->calc_blocks($db_con);
    $t->assert_greater_zero('unit_db_formula->calc_blocks', $calc_blocks);

    $t->subheader('Frontend API tests');

    $frm = $t->load_formula(formula::TN_READ_TEST);
    $t->assert_api_exp($frm);

}

