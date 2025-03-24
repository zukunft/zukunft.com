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

namespace unit_ui;

include_once MODEL_CONST_PATH . 'files.php';
include_once WEB_HELPER_PATH . 'data_object.php';

use cfg\const\files;
use cfg\import\import;
use controller\controller;
use html\helper\data_object as data_object_dsp;
use html\html_base;
use html\list_sort;
use html\phrase\phrase;
use test\test_cleanup;

class start_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();

        $t->subheader('unit html start page tests');

        // load th cache used for the start page
        /*
        $json_str = file_get_contents(files::MESSAGE_PATH . files::START_PAGE_DATA_FILE);
        $json_msg_array = json_decode($json_str, true);
        $ctrl = new controller();
        $json_array = $ctrl->check_api_msg($json_msg_array);
        $imp = new import();
        $dto = $imp->get_data_object($json_array, $t->usr1);
        */
        $dto_dsp = new data_object_dsp();
        $dto_dsp->set_offline();
        $dto_dsp->add_phrases($t->phrase_list_start_view_dsp());

        $msk = new list_sort();
        $phr = $t->global_problem()->phrase();
        $phr_dsp = new phrase($phr->api_json());
        $test_page = $html->text_h2('start page display test');
        $test_page .= $msk->list_sort($phr_dsp, $dto_dsp);
        $t->html_test($test_page, 'start page', 'start_page', $t);
    }

}