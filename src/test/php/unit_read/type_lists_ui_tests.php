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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::WEB . 'frontend.php';
include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::HTML . 'html_base.php';
include_once html_paths::TYPES . 'component_type_list.php';
include_once html_paths::TYPES . 'formula_link_type_list.php';
include_once html_paths::TYPES . 'formula_type_list.php';
include_once html_paths::TYPES . 'phrase_type_list.php';
include_once html_paths::TYPES . 'protection.php';
include_once html_paths::TYPES . 'ref_type_list.php';
include_once html_paths::TYPES . 'share.php';
include_once html_paths::TYPES . 'source_type_list.php';
include_once html_paths::TYPES . 'type_list.php';
include_once html_paths::TYPES . 'type_lists.php';
include_once html_paths::TYPES . 'verbs.php';
include_once html_paths::TYPES . 'view_style_list.php';
include_once html_paths::TYPES . 'view_type_list.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CREATE . 'test_types.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\helper\data_object;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\types\component_type_list;
use Zukunft\ZukunftCom\main\php\web\types\formula_link_type_list;
use Zukunft\ZukunftCom\main\php\web\types\formula_type_list;
use Zukunft\ZukunftCom\main\php\web\types\phrase_type_list;
use Zukunft\ZukunftCom\main\php\web\types\protection;
use Zukunft\ZukunftCom\main\php\web\types\ref_type_list;
use Zukunft\ZukunftCom\main\php\web\types\share;
use Zukunft\ZukunftCom\main\php\web\types\source_type_list;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\types\user_profile;
use Zukunft\ZukunftCom\main\php\web\types\verbs;
use Zukunft\ZukunftCom\main\php\web\types\view_style_list;
use Zukunft\ZukunftCom\main\php\web\types\view_type_list;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class type_lists_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {

        $html = new html_base();
        $t_wrd = new test_words($t);
        $t_typ = new test_types($t);

        // start the test section (ts)
        $ts = 'db read type list ui ';
        $t->header($ts);

        // load the types from the api message
        $api_msg = $t_typ->type_lists_api($t->usr1);
        $ui_cache = new type_lists($api_msg);

        // use the system view to start the HTML test page
        $msk = $ui_cache->html_system_views->get_by_code_id(views::START_CODE);
        $wrd = $t_wrd->word_dsp();
        $wrd->set_name('All type selectors');
        $cfg = new data_object();
        $cfg->typ_lst_cache = $ui->dto->typ_lst_cache;
        $test_page = $msk->show($wrd, $cfg, '') . '<br><br>';

        // test the type list selectors
        $form = 'view';
        $test_page .= $html->form_start($form);

        $test_page .= $html->label(user_profile::NAME, user_profile::NAME);
        $test_page .= $ui_cache->html_user_profiles->selector($form) . '<br>';

        $test_page .= $html->label(verbs::NAME, verbs::NAME);
        $test_page .= $ui_cache->html_verbs->selector($form) . '<br>';

        $test_page .= $html->label(phrase_type_list::NAME, url_var::PHRASE_TYPE);
        $test_page .= $ui_cache->html_phrase_types->selector($form) . '<br>';

        $test_page .= $html->label(formula_type_list::NAME, formula_type_list::NAME);
        $test_page .= $ui_cache->html_formula_types->selector($form) . '<br>';

        $test_page .= $html->label(formula_link_type_list::NAME, formula_link_type_list::NAME);
        $test_page .= $ui_cache->html_formula_link_types->selector($form) . '<br>';

        $test_page .= $html->label(view_type_list::NAME, view_type_list::NAME);
        $test_page .= $ui_cache->html_view_types->selector($form) . '<br>';

        $test_page .= $html->label(view_style_list::NAME, view_style_list::NAME);
        $test_page .= $ui_cache->html_view_styles->selector($form) . '<br>';

        $test_page .= $html->label(component_type_list::NAME, component_type_list::NAME);
        $test_page .= $ui_cache->html_component_types->selector($form) . '<br>';

        $test_page .= $html->label(ref_type_list::NAME, ref_type_list::NAME);
        $test_page .= $ui_cache->html_ref_types->selector($form) . '<br>';

        $test_page .= $html->label(source_type_list::NAME, source_type_list::NAME);
        $test_page .= $ui_cache->html_source_types->selector($form) . '<br>';

        $test_page .= $html->label(protection::NAME, protection::NAME);
        $test_page .= $ui_cache->html_protection_types->selector($form) . '<br>';

        $test_page .= $html->label(share::NAME, share::NAME);
        $test_page .= $ui_cache->html_share_types->selector($form) . '<br>';

        $test_page .= $html->form_end_with_submit($form, '');

        $t->html_page_test($test_page, 'types', 'types', $t);
    }

}