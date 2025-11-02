<?php

/*

    test/unit/html/triple_list.php - testing of the html frontend functions for triples
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_ENUM . 'messages.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\triple_list as triple_list_dsp;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();
        $t_trp = new test_triples($t);

        // start the test section (ts)
        $ts = 'unit ui html triple list ';
        $t->header($ts);

        // fill the triple list based on the api message
        $db_lst = $t_trp->triple_list_short();
        $lst = new triple_list_dsp($db_lst->api_json());
        $t->assert('HTML triple list names match backend names', $lst->names(), $db_lst->names());

        // create the triple list test set
        $lst = new triple_list_dsp();
        $phr_city = $t_trp->zh_city();
        $phr_canton = $t_trp->zh_canton();
        $phr_city_dsp = new triple($phr_city->api_json());
        $phr_canton_dsp = new triple($phr_canton->api_json());
        $lst->add($phr_city_dsp);
        $lst->add($phr_canton_dsp);

        // test the triple list display functions
        $form = 'formula_list_ui_test';
        $test_page = $html->text_h2('triple list display test');
        /*
        $test_page .= 'names with links: ' . $lst->display() . '<br>';
        $test_page .= 'table cells<br>';
        $test_page .= $lst->tbl();
        */

        $from_rows = 'selector: ' . '<br>';
        $from_rows .= $lst->selector($form, 0, url_var::TRIPLE, msg_id::LABEL_FORMULA) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'triple_list', 'triple_list', $t);
    }

}