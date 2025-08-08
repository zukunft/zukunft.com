<?php

/*

    test/unit/html/type_list.php - testing of the type list html user interface functions
    ----------------------------
  

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

use cfg\const\paths;

include_once paths::SHARED_CONST . 'views.php';

use html\frontend;
use html\helper\data_object;
use html\html_base;
use shared\const\views as view_shared;
use test\test_cleanup;

class system_views_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {

        $html = new html_base();
        $ui = new frontend('system_views_ui_tests');
        $ui->load_dummy_cache_from_test_resources();

        // start the test section (ts)
        $ts = 'unit ui html system view ';
        $t->header($ts);

        // test the type list display functions
        $test_page = $html->text_h2('add word');
        $back = '';
        $wrd = $t->word_dsp();

        // check if the system views have set
        $msk = $ui->typ_lst_cache->html_system_views->get_by_code_id(view_shared::WORD_ADD);
        $cfg = new data_object();
        $cfg->typ_lst_cache = $ui->typ_lst_cache;
        $test_page .= $msk->show($wrd, $cfg, $back) . '<br>';

        // TODO review and combine with read db tests
        $t->html_view_test($test_page, view_shared::WORD_ADD, $t);
    }

}