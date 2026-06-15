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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::SYSTEM . 'back_trace.php';

use Zukunft\ZukunftCom\main\php\cfg\log\change_log_link_list as change_log_link_list_cfg;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\log\change_log_link_list as change_log_link_list_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_list;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\system\back_trace;
use Zukunft\ZukunftCom\test\php\const\word_names;
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
        $chg_ui = new change_log_named($chg->api_json());
        $test_page .= $chg_ui->dsp() .  '<br>';


        $test_page .= '<br>simple list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_ui = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_ui->tbl($back);

        $test_page .= '<br>condensed list of changes of a word<br>';
        $log_lst = $t_log->log_list_named();
        $log_ui = new change_log_list($log_lst->api_json($api_typ_lst));
        $test_page .= $log_ui->tbl($back, true, true);

        $t->html_page_test($test_page, 'change_log', 'change_log', $t);

        // link change history rendering (e.g. the triples added to or removed from a word)
        // the link list classes are loaded here, not at the top of the file, because the frontend
        // change_log_link extends change_log_named which sits at the root of the bootstrap include chain
        include_once paths::MODEL_LOG . 'change_log_link_list.php';
        include_once html_paths::LOG . 'change_log_link_list.php';

        $t->subheader($ts . 'link changes');
        // round-trip a backend link change through the api to the frontend and render it
        $cl_lst = new change_log_link_list_cfg();
        $cl = $t_log->log_link();
        $cl->new_text_to = word_names::MATH;
        $cl_lst->add($cl);
        $log_link_ui = new change_log_link_list_ui($cl_lst->api_json($api_typ_lst));
        $test_name = 'a link change is shown as a link to the new target';
        $t->assert_text_contains($test_name, $log_link_ui->tbl($back), word_names::MATH);
        $test_name = 'an empty link change list renders no table row';
        $t->assert_text_not_contains($test_name, new change_log_link_list_ui()->tbl($back), '<tr');
    }

}