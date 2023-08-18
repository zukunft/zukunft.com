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

namespace unit\html;

include_once WEB_TYPES_PATH . 'type_list.php';
include_once WEB_TYPES_PATH . 'type_lists.php';
include_once WEB_TYPES_PATH . 'formula_type_list.php';
include_once WEB_TYPES_PATH . 'phrase_types.php';
include_once WEB_TYPES_PATH . 'pprotection.php';

use api\view_api;
use html\html_base;
use html\types\type_lists as type_list_dsp;
use test\test_cleanup;

class type_lists
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();

        $t->Header('Test the HTML functions for the list preloaded in the Frontend');

        // load the types from the api message
        $api_msg = $t->dummy_type_lists_api($t->usr1)->get_json();
        new type_list_dsp($api_msg);

        // use the system view to start the HTML test page
        global $html_system_views;
        $dsp = $html_system_views->get(view_api::TI_READ);
        $wrd = $t->dummy_word_dsp();
        $wrd->set_name('All type selectors');
        $test_page = $dsp->show($wrd, '') . '<br><br>';

        // test the type list selectors
        $form_name = 'view';
        $test_page .= $html->form_start($form_name);

        global $html_user_profiles;
        $test_page .= $html->label('user profile', 'user profile');
        $test_page .= $html_user_profiles->selector($form_name) . '<br>';

        global $html_phrase_types;
        $test_page .= $html->label('phrase type', 'phrase type');
        $test_page .= $html_phrase_types->selector($form_name) . '<br>';

        global $html_formula_types;
        $test_page .= $html->label('formula type', 'formula type');
        $test_page .= $html_formula_types->selector($form_name) . '<br>';

        global $html_protection_types;
        $test_page .= $html->label('protection', 'protection');
        $test_page .= $html_protection_types->selector('protection') . '<br>';

        $test_page .= $html->form_end_with_submit($form_name, '');

        $t->html_test($test_page, 'types', $t);
    }

}