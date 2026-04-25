<?php

/*

    test/unit/html/change_log.php - testing of the change log display functions
    -----------------------------
  

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

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\test\php\create\test_log;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class change_log_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_log = new test_log($t);

        // start the test section (ts)
        $ts = 'unit ui change log ';
        $t->header($ts);

        $t->subheader($ts . 'display');

        //$wrd_pi = new word_dsp(2, words::TN_CONST);
        $test_page = $html->text_h2('Change log display test');

        // prepare test data
        $back = new back_trace();
        $api_typ_lst = new api_type_list([api_types::TEST_MODE]);

        $test_page .= '<br>changes as a text<br>';
        $chg = $t_log->log_word_add();
        $chg_dsp = new change_log_named($chg->api_json());
        $test_page .= $chg_dsp->dsp() .  '<br>';


        $test_page .= '<br>simple list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_dsp = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_dsp->tbl($back);

        $test_page .= '<br>condensed list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_dsp = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_dsp->tbl($back, true, true);

        $t->html_page_test($test_page, 'change_log', 'change_log', $t);
    }

}