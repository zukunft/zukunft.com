<?php

/*

    test/php/unit_read/formula.php - database unit testing of the formula functions
    ------------------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Switzerland
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit_read;

include_once SHARED_PATH . 'formulas.php';

use cfg\formula\formula;
use cfg\formula\formula_type;
use cfg\formula\formula_type_list;
use cfg\formula\formula_list;
use shared\formulas;
use test\test_cleanup;

class formula_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $frm_typ_cac;

        // init
        $t->name = 'formula read db->';

        $t->header('formula db read tests');

        $t->subheader('formula load');
        $frm = new formula($t->usr1);
        $t->assert_load($frm, formulas::SCALE_TO_SEC);

        $t->subheader('formula tests');

        /*
        // ... check if the link is shown correctly also for the second user
        // ... the second user has excluded the word at this point, so even if the word is linked the word link is nevertheless false
        // TODO check what that the word is linked if the second user activates the word
        $phr = new phrase($t->usr1);
        $phr->load_by_name(words::TN_READ);
        $frm = new formula($t->usr2);
        $frm->load_by_name(formulas::TN_RENAMED);
        $phr_lst = $frm->assign_phr_ulst();
        $result = $phr_lst->does_contain($phr);
        $target = false;
        $t->display('formula->assign_phr_ulst contains "' . $phr->name() . '" for user "' . $t->usr2->name . '"', $target, $result);
        */


        $t->subheader('formula types tests');

        // load the formula types
        $lst = new formula_type_list();
        $result = $lst->load($db_con);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        $result = $frm_typ_cac->id(formula_type::CALC);
        $target = 1;
        $t->assert('check ' . formula_type::CALC, $result, 1);

        // check the estimates for the calculation blocks
        $calc_blocks = (new formula_list($t->usr1))->calc_blocks($db_con);
        $t->assert_greater_zero('calc_blocks', $calc_blocks);

        $t->subheader('Frontend API tests');

        $frm = $t->load_formula(formulas::INCREASE);
        if ($frm->name() != '') {
            $t->assert_export_reload($frm);
        }
    }

}

