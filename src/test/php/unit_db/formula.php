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

use api\formula_api;
use cfg\formula_type;

class formula_unit_db_tests
{

    function run(testing $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'formula read db->';

        $t->header('Unit database tests of the formula class (src/main/php/model/formula/formula.php)');

        $t->subheader('formula tests');

        /*
        // ... check if the link is shown correctly also for the second user
        // ... the second user has excluded the word at this point, so even if the word is linked the word link is nevertheless false
        // TODO check what that the word is linked if the second user activates the word
        $phr = new phrase($usr);
        $phr->load_by_name(word::TN_READ);
        $frm = new formula($t->usr2);
        $frm->load_by_name(formula_api::TN_RENAMED, formula::class);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->dsp('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '"', $target, $result);
        */


        $t->subheader('formula types tests');

        // load the formula types
        $lst = new formula_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = cl(db_cl::FORMULA_TYPE, formula_type::CALC);
        $target = 1;
        $t->assert('check ' . formula_type::CALC, $result, 1);

        // check the estimates for the calculation blocks
        $calc_blocks = (new formula_list($usr))->calc_blocks($db_con);
        $t->assert_greater_zero('calc_blocks', $calc_blocks);

        $t->subheader('Frontend API tests');

        $frm = $t->load_formula(formula_api::TN_INCREASE);
        $t->assert_api_exp($frm);
    }

}

