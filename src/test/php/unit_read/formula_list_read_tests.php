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

namespace unit_read;

include_once SHARED_CONST_PATH . 'triples.php';

use cfg\formula\formula;
use cfg\formula\formula_list;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use shared\const\formulas;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class formula_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'formula list read db->';

        $t->header('formula list database read tests');

        // test loading formula names
        $test_name = 'loading formula names with pattern return the expected formula';
        $pattern = substr(formulas::SCALE_TO_SEC, 0, -1);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_names($pattern);
        $t->assert_contains($test_name, $frm_lst->names(), formulas::SCALE_TO_SEC);

        // test load by formula list by ids
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_ids([1, 2]);
        $result = $frm_lst->name();
        $target = formulas::SCALE_TO_SEC . ',' . formulas::SCALE_HOUR; // order adjusted based on the number of usage
        if ($result != $target) {
            $target = formulas::SCALE_HOUR . ',' . formulas::SCALE_TO_SEC; // try another order
        }
        $t->assert('load by ids for ' . $frm_lst->dsp_id(), $result, $target);

        // test loading the formulas that use the results related to the word second
        $test_name = 'formulas that use the word "second" are at least "scale minute to sec"';
        $wrd_sec = new word($t->usr1);
        $wrd_sec->load_by_name(words::SECOND);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_word_ref($wrd_sec);
        $t->assert_contains($test_name, $frm_lst->names(), [formulas::SCALE_TO_SEC]);

        // test loading the formulas that use the results related to the triple "Zurich (City)"
        $test_name = 'formulas that use the word "Zurich" are at least "population in the biggest city"';
        $trp_zh = new triple($t->usr1);
        $trp_zh->load_by_name(triples::CITY_ZH);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_triple_ref($trp_zh);
        $t->assert_contains($test_name, $frm_lst->names(), [formulas::BIGGEST_CITY]);

        // test loading the formulas that use the results related to the verb "time step"
        $test_name = 'formulas that use the verb "time step" are at least "prior"';
        $vrb_time_step = new verb();
        $vrb_time_step->load_by_name(verbs::TIME_STEP);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_verb_ref($vrb_time_step);
        $t->assert_contains($test_name, $frm_lst->names(), [formulas::PRIOR]);

        // test loading the formulas that use the results of a given formula
        $test_name = 'formulas that use the formula "this" are at least "increase"';
        $frm_this = new formula($t->usr1);
        $frm_this->load_by_name(formulas::THIS_NAME);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_formula_ref($frm_this);
        $t->assert_contains($test_name, $frm_lst->names(), [formulas::INCREASE]);

        $test_name = 'load formulas staring with i';
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_like('i');
        $t->assert_contains($test_name, $frm_lst->names(), formulas::INCREASE);
    }

}

