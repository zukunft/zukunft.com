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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {
        $html = new html_base();
        $t_msk = new test_views($t);

        // start the test section (ts)
        $ts = 'unit ui html view ';
        $t->header($ts);

        $msk = new view($t_msk->view()->api_json());
        $test_page = $html->text_h2('view display test');
        $test_page .= 'with tooltip: ' . $msk->name_tip() . '<br>';
        $test_page .= 'with link: ' . $msk->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $msk->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $msk->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $msk->btn_del() . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $msk->view_type_selector(views::VIEW_EDIT, $ui->dto->typ_lst_cache) . '<br>';
        //$from_rows .= $msk->component_selector(views::VIEW_EDIT, '', 1) . '<br>';
        $test_page .= $html->form(views::VIEW_EDIT, $from_rows);
        $test_page .= $t->dsp_title_named_edit($msk);

        // show a view with a side-or-below group where the columns
        // are shown side by side on wide screens and stacked on small screens
        $t_wrd = new test_words($t);
        $wrd = new word($t_wrd->word()->api_json());
        $msk_cols = new view($t_msk->view_side_or_below()->api_json([api_types::INCL_COMPONENTS]));
        $cols_html = $msk_cols->show($wrd, $ui->dto, '', '', true);
        $test_page .= $html->text_h2('side or below columns');
        $test_page .= $cols_html;
        $t->html_page_test($test_page, 'view', 'view', $t);

        $t->subheader($ts . 'side or below columns');
        $test_name = 'each column limits the minimal width so that up to four fit at the wide side width';
        $t->assert_text_contains($test_name, $cols_html, 'min-width: 700px');
        $test_name = 'the first column is shown before the side-or-first-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_FIRST_NAME, components::COL_SECOND_NAME);
        $test_name = 'the side-or-below column is shown before the side-or-last-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_THIRD_NAME, components::COL_FOURTH_NAME);
        $test_name = 'without the side or below position types no minimal width is set';
        $msk_plain = new view($t_msk->view_with_components()->api_json([api_types::INCL_COMPONENTS]));
        $t->assert_text_not_contains($test_name, $msk_plain->show($wrd, $ui->dto, '', '', true), 'min-width');
    }

}