<?php

/*

    test/php/unit_ui/reference_list_ui_tests.php - test of all html frontend interface frontend functions for reference lists
    --------------------------------------------


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\ref\ref_list;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\test\php\utils\test_lib;

class reference_list_ui_tests
{
    function run(test_cleanup $t): void
    {

        $html = new html_base();
        $tl = new test_lib();
        $ui = new frontend('unit ui html reference list');
        $t_ref = new test_refs($t);
        $msg = new user_message();
        $cac_msg = new user_message();
        // the cache is created by the dev user, because the system views set a code id,
        // which the normal test user is not permitted to do (see user::can_set_code_id)
        // TODO Prio 2 check if a user with less permissions can be used
        $dto = $tl->ui_test_cache($t->usr_dev, $t, $cac_msg);
        $ui->set_cache($dto);

        // start the test section (ts)
        $ts = 'unit ui html reference list ';
        $t->header($ts);

        // test the result list display functions
        $lst = $t_ref->ref_list_math_ui();
        $test_page = $html->text_h2('reference list display test');
        $test_page .= 'short list of reference names with tooltip: ' . $lst->name_text() . '<br>';
        $test_page .= 'vertical list of reference with link:<br>' . $lst->list($msg) . '<br>';
        $test_page .= 'table of reference with add and remove option:<br>' . $lst->list($msg) . '<br>';
        $t->html_page_test($test_page, 'reference_list', 'reference_list', $msg);

        $t->subheader($ts . 'add link');

        // the plus icon behind the list opens the ref add form with the phrase preselected
        $test_name = 'add link opens the ref add form for the phrase';
        $t_wrd = new test_words($t);
        $phr = $t_wrd->zh_ui()->phrase();
        $html_add = $lst->add_link($phr);
        // the link is html escaped, so the expected url is escaped the same way
        $url_add = $html->url_back(views::REF_ADD_ID, 0, [], url_var::PHRASE . '=' . $phr->id());
        $t->assert_text_contains($test_name, $html_add, htmlspecialchars($url_add, ENT_QUOTES));

        // a phrase that is not yet saved has no id to link a reference to, so no icon is shown
        $test_name = 'no add link for a phrase without id';
        $phr_new = new word_ui($t_wrd->word_incomplete()->api_json())->phrase();
        $t->assert($test_name, $lst->add_link($phr_new), '');
    }

}