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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\web\group\group;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class group_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_grp = new test_groups($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html phrase group ';
        $t->header($ts);

        $grp = new group($t_grp->group_zh()->api_json());
        $grp_2019 = new group($t_grp->group_zh_2020()->api_json([api_types::INCL_PHRASES]));
        $test_page = $html->text_h2('Phrase group display test');
        $test_page .= 'named phrase group with tooltip: ' . $grp->name_tip() . '<br>';
        $test_page .= 'named phrase group with link: ' . $grp->name_link_list() . '<br>';
        $test_page .= 'phrase group with tooltip: ' . $grp_2019->name_tip() . '<br>';
        $test_page .= 'phrase group with link: ' . $grp_2019->name_link_list() . '<br>';
        $t->html_page_test($test_page, 'phrase_group', 'phrase_group', $msg);

        // a phrase that carries only its id e.g. of a value built from a url has no name to show,
        // so it adds neither a name nor a separator to the group name (e.g. the page title)
        $t->subheader($ts . 'unnamed phrase');
        $test_name = 'a phrase without a name is left out of the group name';
        $name_named = $grp_2019->name();
        $name_tip_named = $grp_2019->name_tip();
        $phr_no_name = new phrase();
        $phr_no_name->set_id(word_names::TEST_ADD_ID);
        $grp_2019->add($phr_no_name);
        $t->assert($test_name, $grp_2019->name(), $name_named);
        $test_name = '... and of the group name with the tooltips';
        $t->assert($test_name, $grp_2019->name_tip(), $name_tip_named);
        $test_name = 'a group of unnamed phrases only has an empty name';
        $grp_no_names = new group();
        $grp_no_names->add($phr_no_name);
        $t->assert($test_name, $grp_no_names->name(), '');
    }

}