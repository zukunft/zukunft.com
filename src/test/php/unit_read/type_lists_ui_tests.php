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

namespace unit_read;

use html\const\paths as html_paths;
use html\frontend;
use html\helper\data_object;
use html\html_base;
use html\types\component_type_list;
use html\types\formula_link_type_list;
use html\types\formula_type_list;
use html\types\phrase_types;
use html\types\protection;
use html\types\ref_type_list;
use html\types\share;
use html\types\source_type_list;
use html\types\type_lists as type_list_dsp;
use html\types\user_profile;
use html\types\verbs;
use html\types\view_style_list;
use html\types\view_type_list;
use shared\api;
use shared\const\views;
use test\test_cleanup;

include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'phrase_types.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::TYPES . 'protection.php';

class type_lists_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {

        $html = new html_base();

        // start the test section (ts)
        $ts = 'unit ui html preloaded lists ';
        $t->header($ts);

        // load the types from the api message
        $api_msg = $t->type_lists_api($t->usr1);
        $ui_cache = new type_list_dsp($api_msg);

        // use the system view to start the HTML test page
        $msk = $ui_cache->html_system_views->get_by_code_id(views::START_CODE);
        $wrd = $t->word_dsp();
        $wrd->set_name('All type selectors');
        $cfg = new data_object();
        $cfg->typ_lst_cache = $ui->typ_lst_cache;
        $test_page = $msk->show($wrd, $cfg, '') . '<br><br>';

        // test the type list selectors
        $form_name = 'view';
        $test_page .= $html->form_start($form_name);

        $test_page .= $html->label(user_profile::NAME, user_profile::NAME);
        $test_page .= $ui_cache->html_user_profiles->selector($form_name) . '<br>';

        $test_page .= $html->label(verbs::NAME, verbs::NAME);
        $test_page .= $ui_cache->html_verbs->selector($form_name) . '<br>';

        $test_page .= $html->label(phrase_types::NAME, api::URL_VAR_PHRASE_TYPE);
        $test_page .= $ui_cache->html_phrase_types->selector($form_name) . '<br>';

        $test_page .= $html->label(formula_type_list::NAME, formula_type_list::NAME);
        $test_page .= $ui_cache->html_formula_types->selector($form_name) . '<br>';

        $test_page .= $html->label(formula_link_type_list::NAME, formula_link_type_list::NAME);
        $test_page .= $ui_cache->html_formula_link_types->selector($form_name) . '<br>';

        $test_page .= $html->label(view_type_list::NAME, view_type_list::NAME);
        $test_page .= $ui_cache->html_view_types->selector($form_name) . '<br>';

        $test_page .= $html->label(view_style_list::NAME, view_style_list::NAME);
        $test_page .= $ui_cache->html_view_styles->selector($form_name) . '<br>';

        $test_page .= $html->label(component_type_list::NAME, component_type_list::NAME);
        $test_page .= $ui_cache->html_component_types->selector($form_name) . '<br>';

        $test_page .= $html->label(ref_type_list::NAME, ref_type_list::NAME);
        $test_page .= $ui_cache->html_ref_types->selector($form_name) . '<br>';

        $test_page .= $html->label(source_type_list::NAME, source_type_list::NAME);
        $test_page .= $ui_cache->html_source_types->selector($form_name) . '<br>';

        $test_page .= $html->label(protection::NAME, protection::NAME);
        $test_page .= $ui_cache->html_protection_types->selector($form_name) . '<br>';

        $test_page .= $html->label(share::NAME, share::NAME);
        $test_page .= $ui_cache->html_share_types->selector($form_name) . '<br>';

        $test_page .= $html->form_end_with_submit($form_name, '');

        $t->html_test($test_page, 'types', 'types', $t);
    }

}