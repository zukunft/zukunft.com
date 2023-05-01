<?php

/*

    test/unit/html/triple.php - testing of the html frontend functions for triples
    -------------------------
  

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

namespace test\html;

use api\word_api;
use html\html_base;
use html\word\triple as triple_dsp;
use html\word\word as word_dsp;
use test\testing;

class triple
{
    function run(testing $t): void
    {
        global $usr;
        $html = new html_base();

        $t->subheader('Triple tests');

        $trp = new triple_dsp('{"class":"triple","id":1,"name":"' . word_api::TN_READ . '"}');
        $wrd = new word_dsp('{"class":"word","id":-1,"name":"' . word_api::TN_READ . '"}');
        $test_page = $html->text_h2('Triple display test');
        $test_page .= 'with tooltip: ' . $trp->display() . '<br>';
        $test_page .= 'edit button: ' . $trp->btn_edit($wrd->phrase()) . '<br>';
        $t->html_test($test_page, 'triple', $t);
    }

}