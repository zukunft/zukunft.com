<?php

/*

    test/unit/html/phrase_group.php - testing of the phrase_group display functions
    -------------------------------
  

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

use html\group\group;
use html\html_base;
use shared\types\api_type;
use test\test_cleanup;

class group_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html phrase group ';
        $t->header($ts);

        $grp = new group($t->group_zh()->api_json());
        $grp_2019 = new group($t->group_zh_2020()->api_json([api_type::INCL_PHRASES]));
        $test_page = $html->text_h2('Phrase group display test');
        $test_page .= 'named phrase group with tooltip: ' . $grp->name_tip() . '<br>';
        $test_page .= 'named phrase group with link: ' . $grp->name_link_list() . '<br>';
        $test_page .= 'phrase group with tooltip: ' . $grp_2019->name_tip() . '<br>';
        $test_page .= 'phrase group with link: ' . $grp_2019->name_link_list() . '<br>';
        $t->html_test($test_page, 'phrase_group', 'phrase_group', $t);
    }

}