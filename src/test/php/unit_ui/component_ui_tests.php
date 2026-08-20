<?php

/*

    test/unit/html/component.php - testing of the component display functions
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

namespace Zukunft\ZukunftCom\test\php\unit_ui;

use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\web\component\component;
use Zukunft\ZukunftCom\main\php\web\component\component_exe;
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html component ';
        $t->header($ts);

        //$wrd_pi = new word_dsp(2, words::TN_CONST);
        $test_page = $html->text_h2('Component display test');
        /*
        $test_page .= 'with tooltip: ' . $wrd->display() . '<br>';
        $test_page .= 'with link: ' . $wrd->display_linked() . '<br>';
        $test_page .= 'del button: ' . $wrd->btn_del() . '<br>';
        $test_page .= 'table<br>';
        $test_page .= $html->tbl($wrd->th() . $wrd_pi->tr());
        $test_page .= 'del in columns: ' . $wrd->dsp_del() . '<br>';
        $test_page .= 'unlink in columns: ' . $wrd_pi->dsp_unlink($wrd->id) . '<br>';
        $test_page .= 'view header<br>';
        $test_page .= $wrd->header() . '<br>';
        */
        // this test page stacks several forms that never share a real page; pass an
        // incrementing counter so each form's field names/ids stay unique on the snapshot
        // (production renders one form per page and passes no counter -> name="k")
        $test_form_unique_id = 1;
        $cmp = new component_exe();
        $cmp->set_id(0);
        $test_page .= 'add mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '', '', $test_form_unique_id++) . '<br>';
        $cmp = new component_exe();
        $cmp->set_id(1);
        $cmp->set_name(components::WORD_NAME);
        $cmp->description = components::WORD_COM;
        $test_page .= 'edit mask<br>';
        $test_page .= $cmp->form_edit('', '', '', '', '', '', $test_form_unique_id++) . '<br>';
        $test_page .= $t->dsp_title_named_edit($cmp, $msg);
        // the filled component has a non default type, share and protection, so its title shows
        // all three parts of the subtitle, which the hand-built component of the line above does
        // not; the included copy is used, because the api message of the excluded filled
        // component is empty
        $t_cmp = new test_components($t);
        $cmp_filled = new component($t_cmp->component_filled_included()->api_json());
        $test_page .= $t->dsp_title_named_edit($cmp_filled, $msg);
        $t->html_page_test($test_page, 'component', 'component', $msg);

        $t->subheader($ts . 'title');

        // the component default page shows the component name as the page title and the type, the
        // share and the protection in the subtitle like the word default view (see base_views.json
        // component_default), so the api message of a component must carry these three fields
        $test_name = 'the type of a component is sent to the frontend';
        $t->assert_true($test_name, $cmp_filled->type_id($msg) > 0);
        $test_name = 'the share type of a component is sent to the frontend';
        $t->assert_true($test_name, $cmp_filled->share_id() > 0);
        $test_name = 'the protection type of a component is sent to the frontend';
        $t->assert_true($test_name, $cmp_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($cmp_filled, $msg);
        $test_name = 'the component title names the component';
        $t->assert_text_contains($test_name, $ttl_html, $cmp_filled->name());
        $test_name = 'the component title links to the component edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::COMPONENT_EDIT_ID);
        $test_name = 'the component title has a subtitle for the type, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
        // a component without a type shows no empty subtitle brackets
        $test_name = 'a component without a type shows no subtitle';
        $cmp_plain = new component($t_cmp->component_add()->api_json());
        $t->assert_text_not_contains($test_name, $sfm->title_named($cmp_plain, $msg), styles::SUBTITLE);

        $t->subheader($ts . 'link title');

        // the component link default page shows the generated link name as the page title with
        // the share and protection in the subtitle (see base_views.json component_link_default)
        $lnk = new component_link_ui($t_cmp->component_link_filled_included()->api_json([api_types::INCL_PHRASES]));
        $ttl_html = $sfm->title_link($lnk, $msg);
        $test_name = 'the component link title links to the component link edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::COMPONENT_LINK_EDIT_ID);
        $test_name = 'the component link title has a subtitle for the share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);

        // a fresh component link of an add form shows no empty subtitle brackets and has an
        // empty name, never a 'objects not set' placeholder as the page title
        $test_name = 'a fresh component link shows no subtitle';
        $lnk_new = new component_link_ui();
        $t->assert_text_not_contains($test_name, $sfm->title_link($lnk_new, $msg), styles::SUBTITLE);
        $test_name = 'a fresh component link has an empty name';
        $t->assert($test_name, $lnk_new->name(), '');
    }

}