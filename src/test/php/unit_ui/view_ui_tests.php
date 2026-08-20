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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\view\view;
use Zukunft\ZukunftCom\main\php\web\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class view_ui_tests
{
    function run(test_cleanup $t, frontend $ui): void
    {
        $html = new html_base();
        $t_msk = new test_views($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::VIEW_EDIT_ID, url_var::ID => views::WORD_ID];

        // start the test section (ts)
        $ts = 'unit ui html view ';
        $t->header($ts);

        $msk = new view($t_msk->view()->api_json());
        $test_page = $html->text_h2('view display test');
        $test_page .= 'with tooltip: ' . $msk->name_tip() . '<br>';
        $test_page .= 'with link: ' . $msk->name_link() . '<br>';
        $test_page .= $html->text_h2('buttons');
        $test_page .= 'add button: ' . $msk->btn_add($url_arr, $base_url) . '<br>';
        $test_page .= 'edit button: ' . $msk->btn_edit($url_arr, $base_url) . '<br>';
        $test_page .= 'del button: ' . $msk->btn_del($url_arr, $base_url) . '<br>';
        $test_page .= $html->text_h2('select');
        $from_rows = $msk->view_type_selector(views::VIEW_EDIT, $ui->dto->typ_lst_cache, $msg) . '<br>';
        //$from_rows .= $msk->component_selector(views::VIEW_EDIT, '', 1) . '<br>';
        $test_page .= $html->form(views::VIEW_EDIT, $from_rows);
        $test_page .= $t->dsp_title_named_edit($msk, $msg);
        // the filled view has a non default type, share and protection, so its title shows all
        // three parts of the subtitle, which the plain view of the line above does not
        $msk_filled = new view($t_msk->view_filled_included()->api_json());
        $test_page .= $t->dsp_title_named_edit($msk_filled, $msg);

        $t->subheader($ts . 'title');

        // the view default page shows the view name as the page title and the type, the share
        // and the protection in the subtitle like the word default view (see base_views.json
        // view_default), so the api message of a view must carry these three fields
        $test_name = 'the type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->type_id($msg) > 0);
        $test_name = 'the share type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->share_id() > 0);
        $test_name = 'the protection type of a view is sent to the frontend';
        $t->assert_true($test_name, $msk_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($msk_filled, $msg);
        $test_name = 'the view title names the view';
        $t->assert_text_contains($test_name, $ttl_html, $msk_filled->name());
        $test_name = 'the view title links to the view edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::VIEW_EDIT_ID);
        $test_name = 'the view title has a subtitle for the type, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
        // a view without type, share and protection set shows no empty subtitle brackets
        $test_name = 'a view without a type shows no subtitle';
        $t->assert_text_not_contains($test_name, $sfm->title_named($msk, $msg), styles::SUBTITLE);

        // show a view with a side-or-below group where the columns
        // are shown side by side on wide screens and stacked on small screens
        $t_wrd = new test_words($t);
        $wrd = new word($t_wrd->word()->api_json());
        $msk_cols = new view($t_msk->view_side_or_below()->api_json([api_types::INCL_COMPONENTS]));
        $cols_html = $msk_cols->show($wrd, $msg, $ui->dto, '', '', true);
        $test_page .= $html->text_h2('side or below columns');
        $test_page .= $cols_html;
        $t->html_page_test($test_page, 'view', 'view', $msg, $base_url, $lan);

        $t->subheader($ts . 'side or below columns');
        $test_name = 'each column limits the minimal width so that up to four fit at the wide side width';
        $t->assert_text_contains($test_name, $cols_html, 'min-width: 700px');
        $test_name = 'the first column is shown before the side-or-first-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_FIRST_NAME, components::COL_SECOND_NAME);
        $test_name = 'the side-or-below column is shown before the side-or-last-below column';
        $t->assert_text_order($test_name, $cols_html, components::COL_THIRD_NAME, components::COL_FOURTH_NAME);
        // rendering the plain view builds the complete component html, so a semi page timeout is used
        $test_name = 'without the side or below position types no minimal width is set';
        $msk_plain = new view($t_msk->view_with_components()->api_json([api_types::INCL_COMPONENTS]));
        $t->assert_text_not_contains($test_name, $msk_plain->show($wrd, $msg, $ui->dto, '', '', true), 'min-width', $t::TIMEOUT_LIMIT_PAGE_SEMI);
    }

}