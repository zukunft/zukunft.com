<?php

/*

    test/unit/html/type_list.php - testing of the type list html user interface functions
    ----------------------------
  

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

include_once WEB_TYPES_PATH . 'type_list.php';

use api\view_api;
use cfg\formula_type_list;
use html\html_base;
use html\view\view as view_dsp;
use html\types\protection;
use html\types\type_list as type_list_dsp;
use test\testing;

class type_list
{
    function run(testing $t): void
    {

        $html = new html_base();

        $t->subheader('Type list tests');

        // create the formula type list test set
        $frm_lst = new formula_type_list();
        $frm_lst->load_dummy();

        // load the types from the api message
        $api_msg = $t->dummy_type_lists_api()->get_json();
        new type_list_dsp($api_msg);

        // test the type list display functions
        $test_page = $html->text_h2('type list display test');

        $test_page .= 'formula type selector from dummy: ' . '<br>';
        $test_page .= $frm_lst->dsp_obj()->selector() . '<br>';
        $test_page .= 'protection selector from api message: ' . '<br>';
        $test_page .= (new protection())->selector() . '<br>';

        // check if the system views have set
        global $html_system_views;
        $dsp = $html_system_views->get(view_api::TI_READ);
        $wrd = $t->dummy_word_dsp();
        $back = '';
        $test_page .= 'simple mask: ' . '<br>';
        $test_page .= $dsp->show($wrd, $back) . '<br>';

        $t->html_test($test_page, 'types', $t);
    }

}