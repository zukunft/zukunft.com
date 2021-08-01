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

function run_formula_unit_db_tests()
{

    global $db_con;

    test_header('Unit database tests of the formula class (src/main/php/model/formula/formula.php)');

    test_subheader('formula types tests');

    // load the formula types
    $lst = new formula_type_list();
    $result = $lst->load($db_con);
    $target = true;
    test_dsp('unit_db_formula->init_view_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::FORMULA_TYPE, formula_type_list::DBL_CALC);
    $target = 1;
    test_dsp('unit_db_formula->check ' . formula_type_list::DBL_CALC, $result, $target);

}

