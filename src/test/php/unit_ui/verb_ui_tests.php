<?php

/*

    test/unit/html/verb.php - testing of the html frontend functions for verb
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

use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\languages;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\verb\verb;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_vrb = new test_verbs($t);
        $msg = new user_message();

        $base_url = THIS_URL;
        $lan = languages::DEFAULT;
        $url_arr = [url_var::MASK => views::WORD_ID, url_var::ID => word_names::ZH_ID];

        // start the test section (ts)
        $ts = 'unit ui html verb list ';
        $t->header($ts);

        $vrb = new verb($t_vrb->verb()->api_json());
        $test_page = $html->text_h2('Verb display test');
        $test_page .= 'with tooltip: ' . $vrb->name_tip() . '<br>';
        $test_page .= 'with link: ' . $vrb->name_link() . '<br>';
        $test_page .= $t->dsp_title_named_edit($vrb, $msg);

        // the selector to pick a verb e.g. for the verb of a triple
        $form = 'verb_ui_test';
        $from_rows = 'verb selector: ' . '<br>';
        $from_rows .= $t_vrb->list_all_ui($msg)->selector($form, verbs::IS_ID) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'verb', 'verb', $msg, $base_url, $lan);
    }

}