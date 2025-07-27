<?php

/*

    test/unit/html/formula.php - testing of the html frontend functions for formulas
    --------------------------
  

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

namespace unit_ui;

use html\formula\formula as formula_dsp;
use html\html_base;
use test\test_cleanup;

class formula_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html formula ';
        $t->header($ts);

        $frm = new formula_dsp($t->formula()->api_json());
        $test_page = $html->text_h2('formula display test');
        $test_page .= 'with tooltip: ' . $frm->name_tip() . '<br>';
        $test_page .= 'with link: ' . $frm->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $frm->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $frm->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $frm->btn_del() . '<br>';
        $t->html_test($test_page, 'formula', 'formula', $t);

        // TODO review

        /*
        global $usr;
        $t->header('Test the formula frontend scripts (e.g. /formula_add.php)');

        // load the main test word
        $wrd_company = $t->test_word(words::TN_COMPANY);

        // call the add formula page and check if at least some keywords are returned
        $frm = $t->load_formula(formulas::TN_INCREASE);
        $result = file_get_contents('https://zukunft.com/http/formula_add.php?word=' . $wrd_company->id() . '&back=' . $wrd_company->id() . '');
        $target = 'Add new formula for';
        $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the headline', $target, $result, $t::TIMEOUT_LIMIT_PAGE_LONG);
        $target = words::TN_COMPANY;
        $t->dsp_contains(', frontend formula_add.php ' . $result . ' contains at least the linked word ' . words::TN_COMPANY, $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test the edit formula frontend
        $result = file_get_contents('https://zukunft.com/http/formula_edit.php?id=' . $frm->id() . '&back=' . $wrd_company->id());
        $target = formulas::TN_INCREASE;
        $t->dsp_contains(', frontend formula_edit.php ' . $result . ' contains at least ' . $frm->name(), $target, $result, $t::TIMEOUT_LIMIT_PAGE_SEMI);

        // test the del formula frontend
        $result = file_get_contents('https://zukunft.com/http/formula_del.php?id=' . $frm->id() . '&back=' . $wrd_company->id());
        $target = formulas::TN_INCREASE;
        $t->dsp_contains(', frontend formula_del.php ' . $result . ' contains at least ' . $frm->name(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);
        */

    }

}