<?php

/*

    test/unit/html/phrase.php - testing of the phrase display functions
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

namespace unit\html;

use api\word\word as word_api;
use html\html_base;
use html\phrase\phrase as phrase_dsp;
use test\test_cleanup;

class phrase
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('Phrase tests');

        $wrd = new phrase_dsp($t->dummy_word()->phrase()->api_json());
        $trp = new phrase_dsp($t->dummy_triple_pi()->phrase()->api_json());
        $test_page = $html->text_h2('Phrase display test');
        $test_page .= 'word phrase with tooltip: ' . $wrd->display() . '<br>';
        $test_page .= 'word phrase with link: ' . $wrd->display_linked() . '<br>';
        $test_page .= 'triple phrase with tooltip: ' . $trp->display() . '<br>';
        $test_page .= 'triple phrase with link: ' . $trp->display_linked() . '<br>';
        $t->html_test($test_page, 'phrase', $t);
    }

}