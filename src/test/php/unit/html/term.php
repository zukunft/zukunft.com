<?php

/*

    test/unit/html/term.php - testing of the term display functions
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

namespace unit\html;

use api\word_api;
use html\html_base;
use html\phrase\term as term_dsp;
use test\test_cleanup;

class term
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('term tests');

        $wrd = new term_dsp($t->dummy_word()->term()->api_json());
        $trp = new term_dsp($t->dummy_triple_pi()->term()->api_json());
        $vrb = new term_dsp($t->dummy_verb()->term()->api_json());
        $frm = new term_dsp($t->dummy_formula()->term()->api_json());
        $test_page = $html->text_h2('term display test');
        $test_page .= 'word term with tooltip: ' . $wrd->display() . '<br>';
        $test_page .= 'word term with link: ' . $wrd->display_linked() . '<br>';
        $test_page .= 'triple term with tooltip: ' . $trp->display() . '<br>';
        $test_page .= 'triple term with link: ' . $trp->display_linked() . '<br>';
        $test_page .= 'verb term with tooltip: ' . $vrb->display() . '<br>';
        $test_page .= 'verb term with link: ' . $vrb->display_linked() . '<br>';
        $test_page .= 'formula term with tooltip: ' . $frm->display() . '<br>';
        $test_page .= 'formula term with link: ' . $frm->display_linked() . '<br>';
        $t->html_test($test_page, 'term', $t);
    }

}