<?php

/*

    test/php/unit_ui/reference_list_ui_tests.php - test of all html frontend interface frontend functions for reference lists
    --------------------------------------------


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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_TYPES . 'api_type.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_type;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class reference_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();
        $tl = new test_lib();
        $ui = new frontend('unit ui html reference list');
        $t_ref = new test_refs($t);
        $dto = $tl->ui_test_cache($t->usr1, $t);
        $ui->set_cache($dto);

        // start the test section (ts)
        $ts = 'unit ui html reference list ';
        $t->header($ts);

        // test the result list display functions
        $lst = $t_ref->ref_list_math_ui();
        $test_page = $html->text_h2('reference list display test');
        $test_page .= 'short list of reference names with tooltip: ' . $lst->name_text() . '<br>';
        $test_page .= 'vertical list of reference with link:<br>' . $lst->list() . '<br>';
        $test_page .= 'table of reference with add and remove option:<br>' . $lst->list() . '<br>';
        $t->html_page_test($test_page, 'reference_list', 'reference_list', $t);
    }

}