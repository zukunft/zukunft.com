<?php

/*

    test/unit/html/reference.php - testing of the html frontend functions for references
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

namespace unit_ui;

use html\html_base;
use html\ref\ref as ref_dsp;
use test\test_cleanup;

class reference_ui_tests
{
    function run(test_cleanup $t): void
    {
        global $usr;
        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html reference ';
        $t->header($ts);

        $ref = new ref_dsp($t->reference()->api_json());
        $test_page = $html->text_h2('reference display test');
        $test_page .= 'with tooltip: ' . $ref->name_tip() . '<br>';
        $test_page .= 'with link: ' . $ref->name_link() . '<br>';
        $t->html_test($test_page, 'reference', 'reference', $t);
    }

}