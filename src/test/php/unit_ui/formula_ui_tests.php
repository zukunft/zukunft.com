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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SYSTEM . 'back_trace.php';
include_once html_paths::LOG . 'change_log_list.php';

use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_frm = new test_formulas($t);

        // start the test section (ts)
        $ts = 'unit ui html formula ';
        $t->header($ts);

        $frm = new formula($t_frm->formula()->api_json());
        $test_page = $html->text_h2('formula display test');
        $test_page .= 'with tooltip: ' . $frm->name_tip() . '<br>';
        $test_page .= 'with link: ' . $frm->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $frm->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $frm->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $frm->btn_del() . '<br>';
        $test_page .= $t->dsp_title_named_edit($frm);

        // the formula page title shows the formula name with its assigned phrases as subtitle,
        // e.g. "increase" with the assigned "year" phrase
        $frm_increase = $t_frm->formula_increase_ui();
        $test_page .= $t->dsp_title_formula($frm_increase);

        // the expression in latex format with a tooltip and a link for each term, e.g. the
        // "definition of joule" formula joule = ( kg * metre * metre ) / ( second * second )
        $frm_joule = $t_frm->formula_joule_ui();
        $test_page .= $html->text_h2('expression in latex format with term links');
        $test_page .= 'latex with links: ' . $frm_joule->expression_latex_link() . '<br>';

        // the increase formula expression in latex format with a tooltip and a link for each
        // term (percent, this and prior)
        $frm_increase_linked = $t_frm->formula_increase_ui(true);
        $test_page .= $html->text_h2('increase expression in latex format with term links');
        $test_page .= 'latex with links: ' . $frm_increase_linked->expression_latex_link() . '<br>';

        // expression_latex shows the same expression in latex format without the term links,
        // e.g. the increase formula "percent = ( this - prior ) / prior"
        $test_page .= $html->text_h2('increase expression in latex format without term links');
        $test_page .= 'latex without links: ' . $frm_increase->expression_latex() . '<br>';

        // the changes of the increase formula as a table, e.g. the name and expression added
        $t_log = new test_log($t);
        $back = new back_trace();
        $api_typ_lst = new api_type_list([api_types::TEST_MODE]);
        $log_lst = new change_log_list($t_log->log_list_formula_increase()->api_json($api_typ_lst));
        $test_page .= $html->text_h2('changes of the formula increase');
        $test_page .= $log_lst->tbl($back);

        $t->html_page_test($test_page, 'formula', 'formula', $t);

        // TODO review

        /*
        global $usr;
        $ts = 'unit ui html formula user ';
        $t->header($ts);

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