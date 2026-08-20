<?php

/*

    test/unit/html/reference.php - testing of the html frontend functions for references
    -------------------------
  

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

use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\ref\ref;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class reference_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_ref = new test_refs($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html reference ';
        $t->header($ts);

        $ref = new ref($t_ref->reference()->api_json());
        $test_page = $html->text_h2('reference display test');
        $test_page .= 'with tooltip: ' . $ref->name_tip() . '<br>';
        $test_page .= 'with link: ' . $ref->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($ref, $msg);
        // the filled reference has a non default share and protection, so its title shows all
        // three parts of the subtitle, which the reference of the page above does not
        $ref_filled = new ref($t_ref->ref_filled()->api_json());
        $test_page .= $t->dsp_title_named_edit($ref_filled, $msg);
        $t->html_page_test($test_page, 'reference', 'reference', $msg);

        $t->subheader($ts . 'title');

        // the reference default view shows the reference name as the page title and the predicate,
        // the share and the protection in the subtitle like the source default view (see
        // base_views.json ref_default), so the api message must carry these three fields
        $test_name = 'the predicate of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->type_id($msg) > 0);
        $test_name = 'the share type of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->share_id() > 0);
        $test_name = 'the protection type of a reference is sent to the frontend';
        $t->assert_true($test_name, $ref_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($ref_filled, $msg);
        $test_name = 'the reference title names the reference';
        $t->assert_text_contains($test_name, $ttl_html, $ref_filled->name());
        $test_name = 'the reference title links to the reference edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::REF_EDIT_ID);
        $test_name = 'the reference title has a subtitle for the predicate, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);

        // a reference always provides a phrase for the rendering like the other db objects:
        // the linked phrase, or an empty phrase e.g. for a new reference of an add form
        $test_name = 'the phrase of a new reference is empty but never null';
        $ref_new = new ref();
        $t->assert($test_name, $ref_new->phrase()->id(), 0);
        $test_name = 'the phrase of a linked reference is the linked phrase';
        $t->assert_true($test_name, $ref->phrase()->id() != 0);
    }

}