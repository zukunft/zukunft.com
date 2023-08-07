<?php

/*

    test/php/unit_read/formula_list.php - TESTing of the FORMULA LIST functions that only read from the database
    -----------------------------------
  

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

namespace test;

use api\formula_api;
use api\triple_api;
use api\verb_api;
use api\word_api;
use cfg\formula;
use cfg\formula_list;
use cfg\triple;
use cfg\verb;
use cfg\word;
use cfg\word_list;

class formula_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'formula list read db->';

        $t->header('Test the formula list class (classes/formula_list.php)');

        // test loading formula names
        $test_name = 'loading formula names with pattern return the expected formula';
        $pattern = substr(formula_api::TN_READ, 0, -1);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_names($pattern);
        $t->assert_contains($test_name, $frm_lst->names(), formula_api::TN_READ);

        // test load by formula list by ids
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_ids([1, 2]);
        $result = $frm_lst->name();
        $target = formula_api::TN_READ . ',' . formula_api::TN_READ_ANOTHER; // order adjusted based on the number of usage
        if ($result != $target) {
            $target = formula_api::TN_READ_ANOTHER . ',' . formula_api::TN_READ; // try another order
        }
        $t->assert(
            'load by ids for ' . $frm_lst->dsp_id(),
            $result, $target);

        // test loading the formulas that use the results related to the word second
        $wrd_sec = new word($t->usr1);
        $wrd_sec->load_by_name(word_api::TN_SECOND);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_word_ref($wrd_sec);
        $t->assert_contains(
            'formulas that use the word "second" are at least "scale minute to sec"',
            $frm_lst->names(), [formula_api::TN_READ]);

        // test loading the formulas that use the results related to the triple "Zurich (City)"
        $trp_zh = new triple($t->usr1);
        $trp_zh->load_by_name(triple_api::TN_ZH_CITY);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_triple_ref($trp_zh);
        $t->assert_contains(
            'formulas that use the word "Zurich" are at least "population in the biggest city"',
            $frm_lst->names(), [formula_api::TN_BIGGEST_CITY]);

        // test loading the formulas that use the results related to the verb "time step"
        $vrb_time_step = new verb();
        $vrb_time_step->load_by_name(verb_api::TN_TIME_STEP);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_verb_ref($vrb_time_step);
        $t->assert_contains(
            'formulas that use the verb "time step" are at least "prior"',
            $frm_lst->names(), [formula_api::TN_READ_PRIOR]);

        // test loading the formulas that use the results of a given formula
        $frm_this = new formula($t->usr1);
        $frm_this->load_by_name(formula_api::TN_READ_THIS);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_formula_ref($frm_this);
        $t->assert_contains(
            'formulas that use the formula "this" are at least "increase"',
            $frm_lst->names(), [formula_api::TN_INCREASE]);

        $test_name = 'load formulas staring with i';
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_like('i');
        $t->assert_contains($test_name, $frm_lst->names(), formula_api::TN_INCREASE);
    }

}

