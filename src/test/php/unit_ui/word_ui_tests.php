<?php

/*

    test/unit_ui/word.php - testing of the html frontend functions for words
    ---------------------
  

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
use html\word\word as word_dsp;
use shared\const\views as view_shared;
use test\test_cleanup;

class word_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('word html ui unit tests');

        // TODO add a list of differences between the user word and the standard word
        //      with an undo button to change back to the standard
        // TODO add this ui test for all main sandbox objects

        $wrd = new word_dsp($t->word()->api_json());
        $wrd_pi = new word_dsp($t->word_pi()->api_json());
        $wrd_zh = new word_dsp($t->word_zh()->api_json());
        $wrd_city = new word_dsp($t->word_city()->api_json());
        $test_page = $html->text_h1('Word display test');
        $test_page .= $html->text_h2('names');
        $test_page .= 'with tooltip: ' . $wrd->name_tip() . '<br>';
        $test_page .= 'with link: ' . $wrd->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $wrd->btn_add() . '<br>';
        $test_page .= 'edit button: ' . $wrd->btn_edit() . '<br>';
        $test_page .= 'del button: ' . $wrd->btn_del() . '<br>';
        $test_page .= 'unlink button: ' . $wrd->btn_unlink(1) . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $wrd->dsp_type_selector(view_shared::WORD_EDIT) . '<br>';
        $from_rows .= $wrd->view_selector(view_shared::WORD_EDIT, $t->view_list_dsp()) . '<br>';
        $from_rows .= $wrd->view_selector(view_shared::WORD_EDIT, $t->view_list_long_dsp(), 'view_long') . '<br>';
        $test_page .= $html->form(view_shared::WORD_EDIT, $from_rows);
        $test_page .= $html->text_h2('table');
        $test_page .= $html->tbl($html->tr($wrd->th()) . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $html->tbl($wrd->dsp_del()) . '<br>';
        $test_page .= 'unlink in columns: ' . $html->tbl($wrd_pi->dsp_unlink($wrd->id())) . '<br>';
        $test_page .= $html->text_h2('view header');
        $test_page .= $wrd->header() . '<br>';
        $test_page .= $html->text_h2('parents');
        $test_page .= $wrd_zh->parents()->name_link() . '<br>';
        $test_page .= $html->text_h2('children');
        $test_page .= $wrd_city->children()->name_link() . '<br>';
        $t->html_test($test_page, 'word html components', 'word', $t);

    }

}