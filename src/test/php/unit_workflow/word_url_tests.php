<?php

/*

    test/php/unit_write/word_tests.php - write test words to the database and check the results
    ----------------------------------
  

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

namespace Zukunft\ZukunftCom\test\php\unit_workflow;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\frontend;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message;
use Zukunft\ZukunftCom\test\php\utils\test_base;

include_once paths::DB . 'sql_db.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

class word_url_tests
{

    function run(test_base $t): void
    {

        // init
        global $mtr;
        $usr_msg = new user_message();
        global $sys;
        $ui = new frontend('view');
        $ui->load_cache($sys);
        $usr_ui = new user_ui();
        $usr_ui->set_from_json($t->usr1->api_json(), $usr_msg);
        $usr_sys_ui = new user_ui();
        $usr_sys_ui->set_from_json($t->usr_system->api_json(), $usr_msg);
        $usr_msg->usr = $usr_sys_ui;
        $t->name = 'word url->';

        // start the test section (ts)
        $ts = 'url word ';
        $t->header($ts);


        $t->subheader($ts . 'workflow');

        $test_name = 'show edit view';
        $url_arr = [];
        $url_arr[url_var::VIEW] = views::WORD_EDIT_ID;
        $url_arr[url_var::ID] = words::MATH_ID;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, words::MATH);

        $test_name = '... view with execution time measurement';
        $url_arr[url_var::DEBUG] = url_var::DEBUG_EXE_TIME_REPORT;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, words::MATH);

        $test_name = 'add request via url without name should return a missing error message';
        $url_arr = [];
        $url_arr[url_var::VIEW] = views::WORD_ADD_ID;
        $url_arr[url_var::ACTION] = url_var::CRUD_CREATE;
        $url_arr[url_var::NAME] = '';
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 1 activate
        //$t->assert_text_contains($test_name, $result, msg_id::WORD_NAME_MISSING->text());

        $test_name = '... with name ask the user to confirm adding the word';
        $url_arr[url_var::NAME] = words::TEST_ADD;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, $mtr->get(msg_id::FORM_TITLE_CONFIRM_ADD->text()));

        $test_name = '... if confirmed the word is added';
        $url_arr[url_var::STEP] = url_var::STEP_CONFIRMED;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, words::TEST_ADD);

        $test_name = '... so it can be deleted';
        $url_arr[url_var::ACTION] = url_var::CRUD_DELETE;
        $result = $ui->url_to_html($url_arr, $usr_ui, $usr_msg, $ui->dto);
        // TODO Prio 0 activate
        //$t->assert_text_contains($test_name, $result, words::TEST_ADD);


        $t->subheader($ts . 'cleanup');

        // cleanup - fallback delete
        $wrd = new word($t->usr1);
        foreach (words::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

    }

}
