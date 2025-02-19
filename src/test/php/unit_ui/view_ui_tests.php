<?php

/*

    test/unit/html/view.php - testing of the html frontend functions for view
    -----------------------
  

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

use html\html_base;
use html\view\view as view_dsp;
use shared\const\views;
use test\test_cleanup;

class view_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('view html ui unit tests');

        $msk = new view_dsp($t->view()->api_json());
        $test_page = $html->text_h2('view display test');
        $test_page .= 'with tooltip: ' . $msk->name_tip() . '<br>';
        $test_page .= 'with link: ' . $msk->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $msk->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $msk->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $msk->btn_del() . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $msk->type_selector(views::VIEW_EDIT) . '<br>';
        //$from_rows .= $msk->component_selector(views::VIEW_EDIT, '', 1) . '<br>';
        $test_page .= $html->form(views::VIEW_EDIT, $from_rows);
        $t->html_test($test_page, 'view', 'view', $t);
    }

}