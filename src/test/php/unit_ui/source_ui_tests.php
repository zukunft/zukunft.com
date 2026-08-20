<?php

/*

    test/unit/html/source.php - testing of the html frontend functions for sources
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
use Zukunft\ZukunftCom\main\php\web\ref\source;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_src = new test_sources($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html source ';
        $t->header($ts);

        $src = new source($t_src->source_reserved()->api_json());
        $test_page = $html->text_h2('source display test');
        $test_page .= 'with tooltip: ' . $src->name_tip() . '<br>';
        $test_page .= 'with link: ' . $src->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($src, $msg);
        // the filled source has a non default type, share and protection, so its title shows all
        // three parts of the subtitle, which the default source of the page above does not
        $src_filled = new source($t_src->source_filled_included()->api_json());
        $test_page .= $t->dsp_title_named_edit($src_filled, $msg);
        $t->html_page_test($test_page, 'source', 'source', $msg);

        $t->subheader($ts . 'title');

        // the source default view shows the source name as the page title and the type, the share
        // and the protection in the subtitle like the word default view (see base_views.json
        // source_default), so the api message of a source must carry these three fields
        $test_name = 'the type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->type_id($msg) > 0);
        $test_name = 'the share type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->share_id() > 0);
        $test_name = 'the protection type of a source is sent to the frontend';
        $t->assert_true($test_name, $src_filled->protection_id() > 0);

        $sfm = new system_form();
        $ttl_html = $sfm->title_named($src_filled, $msg);
        $test_name = 'the source title names the source';
        $t->assert_text_contains($test_name, $ttl_html, $src_filled->name());
        $test_name = 'the source title links to the source edit view';
        $t->assert_text_contains($test_name, $ttl_html, url_var::MASK . '=' . views::SOURCE_EDIT_ID);
        $test_name = 'the source title has a subtitle for the type, share and protection';
        $t->assert_text_contains($test_name, $ttl_html, styles::SUBTITLE);
    }

}