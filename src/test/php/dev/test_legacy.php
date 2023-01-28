<?php

/*

  test_legacy.php - TESTing of LEGACY functions
  ---------------
  

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

function run_legacy_test(testing $t): void
{

    $t->header('Test calc functions');


    /*
    // test zuc_get_formula
    $formula_part_text = "{f19}";
    $context_word_lst = array();
    $context_word_lst[] = $word_nesn;
    $time_word_id = $word_2016;
    $target = TV_NESN_SALES_2016;
    $result = zuc_get_formula($formula_part_text, $context_word_lst, $time_word_id, $usr->id());
    $t->dsp(", zuc_get_formula: the result for formula \"".$formula_part_text."\"", $target, $result);

    // test zuf_2num_value
    $formula_part_text = "{w6}{t12}";
    $context_word_lst = array();
    $context_word_lst[] = $word_nesn;
    $time_word_id = $word_2016;
    $target = 5;
    $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result);

    // test zuf_2num_value
    $formula_part_text = "{w6}{t12}{w83}";
    $context_word_lst = array();
    $context_word_lst[] = $word_nesn;
    $time_word_id = $word_2016;
    $target = 5;
    $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result);

    // test zuf_2num_value
    $formula_part_text = "{f19}";
    $context_word_lst = array();
    $context_word_lst[] = $word_nesn;
    $time_word_id = $word_2016;
    $target = TV_NESN_SALES_2016;
    $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result);

    // test zuf_2num_value
    $formula_part_text = "/{f19}";
    $context_word_lst = array();
    $context_word_lst[] = $word_nesn;
    $time_word_id = $word_2016;
    $target = TV_NESN_SALES_2016;
    $result = zuf_2num_value($formula_part_text, $context_word_lst, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num_value: the result for formula \"".$formula_part_text."\", Nestlé 2016", $target, $result);

    // test if zuf_2num still does a simple calculation
    $frm_id = 0;
    $math_text = "=(3 - 1) * 2";
    $word_array = array();
    $time_word_id = 0;
    $target = 4;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = " 3 ";
    $word_array = array();
    $time_word_id = 0;
    $target = 3;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = "=3 - 1";
    $word_array = array();
    $time_word_id = 0;
    $target = 2;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = "={w6}{t12}/{f19}";
    $target = 1;
    $word_array = array($word_nesn);
    $time_word_id = $word_2016;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = "=93686000000 - {f5}";
    $target = 1000000000;
    $word_array = array($word_abb,$word_revenues);
    $time_word_id = 0;
    $debug = false;
    $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = "={f4} - {f5}";
    $target = 1100000000;
    $word_array = array($word_abb,$word_revenues);
    $time_word_id = 0;
    $debug = false;
    $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $math_text = "={f2}";
    $target = 1100000000;
    $word_array = array($word_abb,$word_revenues);
    $time_word_id = 0;
    $debug = false;
    $result = zuf_2num($math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula \"".$math_text."\"", $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $target = "1.19%";
    $word_array = array($word_abb,$word_revenues);
    $time_word_id = 0;
    $debug = false;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula with id ".$frm_id, $target, $result);

    // test zuf_2num
    $frm_id = 0;
    $target = "1.19%";
    $word_array = array($word_abb,$word_revenues);
    $time_word_id = 0;
    $debug = false;
    $result = zuf_2num($frm_id, $math_text, $word_array, $time_word_id, $usr->id());
    $t->dsp(", zuf_2num: the result for formula with id ".$frm_id, $target, $result);

    // test zuc_has_operator
    $math_text = "3 - 1";
    $target = true;
    $result = zuc_has_operator($math_text);
    $t->dsp(", zuc_has_operator: the result for formula \"".$math_text."\"", $target, $result);

    */


    $t->header('Old test functions');

    // load the database records used for testing
    $t->header('check database records');

    function test_show_db_id($test_text, $result)
    {
        if ($result > 0) {
            echo "<font color=green>OK</font> " . $test_text . " has id \"" . $result . "\"<br>";
        } else {
            echo "<font color=red>Error</font> " . $test_text . " is missing<br>";
        }
    }


}
