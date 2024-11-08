<?php

/*

    test/unit/html/result.php - testing of the html frontend functions for result
    ------------------------
  

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

use html\html_base;
use html\result\result as result_dsp;
use test\test_cleanup;

class result
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('result tests');

        $res = new result_dsp($t->result_simple()->api_json(false));
        $test_page = $html->text_h2('result display test');
        $test_page .= 'with tooltip: ' . $res->display() . '<br>';
        $test_page .= 'with link: ' . $res->display_linked() . '<br>';
        $t->html_test($test_page, 'result', 'result', $t);
    }

}