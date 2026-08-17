<?php

/*

    test/unit/html/verb_list.php - testing of the verb list html frontend functions
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

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::VERB . 'verb_list.php';

use Zukunft\ZukunftCom\main\php\web\html\html_base;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\main\php\web\verb\verb as verb_ui;
use Zukunft\ZukunftCom\main\php\web\verb\verb_list as verb_list_ui;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_list_ui_tests
{
    function run(test_cleanup $t): void
    {
        $html = new html_base();
        $t_vrb = new test_verbs($t);
        $msg = new user_message();

        // start the test section (ts)
        $ts = 'unit ui html verb list ';
        $t->header($ts);

        // fill the frontend verb list based on the api message of the backend list
        $test_name = 'verbs of the api message';
        $db_lst = $t_vrb->list_short();
        $lst = new verb_list_ui();
        $lst->set_from_json($db_lst->api_json(), $msg);
        $t->assert($test_name, $lst->db_id_list(), $db_lst->db_id_list());
        $msg->reset();

        $test_name = 'a verb of a second api message is reported as double';
        $lst->set_from_json($db_lst->api_json(), $msg);
        $t->assert_msg_false($test_name, $msg, '"' . verbs::IS_NAME . '"');

        $test_name = 'a verb of a second api message is not added twice';
        $t->assert($test_name, $lst->db_id_list(), $db_lst->db_id_list());

        // a list can hold the same verb more than once e.g. to count the verb usages of a triple list
        $test_name = 'a verb of a second api message is added twice if duplicates are allowed';
        $lst_dbl = new verb_list_ui();
        $lst_dbl->set_from_json($db_lst->api_json(), $msg, true);
        $lst_dbl->set_from_json($db_lst->api_json(), $msg, true);
        $t->assert($test_name, count($lst_dbl->lst()), $db_lst->count() * 2);

        $test_name = 'a double verb is not reported if duplicates are allowed';
        $t->assert_empty($test_name, $msg->text());
        $msg->reset();

        $test_name = 'an empty api message adds no verb';
        $lst_empty = new verb_list_ui();
        $lst_empty->set_from_json($t_vrb->list_empty()->api_json(), $msg);
        $t->assert($test_name, $lst_empty->db_id_list(), []);
        $msg->reset();

        // test the verb list display functions
        $form = 'verb_list_ui_test';
        $test_page = $html->text_h2('verb list display test');
        $test_page .= $lst->list(verb_ui::class, 'Verbs');
        $test_page .= 'link types: ' . '<br>' . $lst->dsp_list() . '<br>';
        $from_rows = 'selector: ' . '<br>';
        $from_rows .= $lst->type_selector($form, verbs::IS_ID, url_var::VERB, msg_id::FORM_SELECT_VERB) . '<br>';
        $test_page .= $html->form($form, $from_rows);

        $t->html_page_test($test_page, 'verb_list', 'verb_list', $msg);
    }

}
