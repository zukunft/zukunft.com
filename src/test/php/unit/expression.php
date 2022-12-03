<?php

/*

    test/unit/expression.php - unit testing of the expression functions
    --------------------
  

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

use api\formula_api;

class expression_unit_tests
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $t->name = 'expression->';

        $t->header('Unit tests of the formula expression class (src/main/php/model/formula/expression.php)');

        $test_name = 'test the conversion of the user text to the database reference text';
        $exp = new expression($usr);
        $exp->usr_text = formula::TF_INCREASE;
        $trm_names = $exp->get_usr_names();
        $trm_lst = $t->dummy_term_list($trm_names);
        $exp->ref_text = $exp->get_ref_text($trm_lst);
        $result = $exp->ref_text;
        $target = '{w1}=({w2}-{w3})/{w3}';
        $t->assert($test_name, $result, $target);

        $test_name = 'test the conversion of the database reference text to the user text';
        $result = $exp->get_usr_text($trm_lst);
        $target = $t->trim_all_spaces(formula::TF_INCREASE);
        $t->assert($test_name, $result, $target);

        /*
        $test_name = 'getting phrases that should be added to the result of a formula for "' . $exp->dsp_id() . '"';
        $phr_lst_fv = $exp->fv_phr_lst();
        $result = $phr_lst_fv->dsp_name();
        $target = '"' . word::TN_READ_PERCENT . '"';
        $t->assert($test_name, $result, $target);
        */

        $test_name = 'source phrase list with id from the reference text';
        $exp_sector = new expression($usr);
        $exp_sector->ref_text = formula_api::TF_SECTOR_REF;
        $phr_lst = $exp_sector->phr_id_lst_as_phr_lst($exp_sector->r_part());
        $result = $phr_lst->dsp_id();
        $target = '"","","" (1,2,3)';
        $t->assert($test_name, $result, $target);

        $test_name = 'result phrase list with id from the reference text';
        $exp_scale = new expression($usr);
        $exp_scale->ref_text = formula_api::TF_SCALE_MIO_REF;
        $phr_lst = $exp_scale->phr_id_lst_as_phr_lst($exp_scale->fv_part());
        $result = $phr_lst->dsp_id();
        $target = '1';
        $t->assert($test_name, $result, $target);

        /*
        $frm = new formula($usr);
        $frm->name = formula::TN_SECTOR;
        $frm->ref_text = formula_api::TF_SECTOR_REF;
        $frm->set_ref_text();
        $result = $frm->usr_text;
        $target = '= "' . word::TN_COUNTRY . '" "differentiator" "' . word::TN_CANTON . '" / "' . word::TN_TOTAL . '"';
        $t->assert('expression->is_std if formula is changed by the user', $result, $target);
        */

    }

}