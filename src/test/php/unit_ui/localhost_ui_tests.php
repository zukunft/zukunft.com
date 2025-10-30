<?php

/*

    test/unit_ui/system_view_ui_tests.php - test if the system view still look the same without using the api
    -------------------------------------


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

include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'language_codes.php';
include_once test_paths::CREATE . 'test_users.php';
include_once test_paths::UTILS . 'test_base.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\system\system_time_type;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\language_codes;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class localhost_ui_tests
{
    function run(test_cleanup $t): void
    {

        // init
        global $mtr;
        global $sys_times;

        // start the test section (ts)
        $ts = 'unit ui localhost ';
        $t->header($ts);

        $t->subheader($ts . 'translator');
        $test_name = 'verb add view title';
        $t->assert($test_name, $mtr->txt(msg_id::FORM_VERB_ADD_TITLE), 'Add a new verb');
        $test_name = 'verb add view title translated';
        $t->assert($test_name, $mtr->txt(msg_id::FORM_VERB_ADD_TITLE, language_codes::DE), 'Neues Verb');

        $t->subheader($ts . 'views');
        $test_name = 'word edit by url';
        $sys_times->switch(system_time_type::LOCALHOST_VIEWS);
        $page = file_get_contents(api::URL_DEV . views::WORD_EDIT_ID . url_var::ADD_ID . words::MATH_ID);
        $sys_times->switch(system_time_type::DEFAULT);
        $t->assert_text_contains($test_name, $page, words::MATH, test_base::TIMEOUT_LOCALHOST);
        $test_name = 'verb add by url';
        $sys_times->switch(system_time_type::LOCALHOST_VIEWS);
        $page = file_get_contents(api::URL_DEV . views::VERB_ADD_ID);
        $sys_times->switch(system_time_type::DEFAULT);
        $t->assert_text_contains($test_name, $page, $mtr->txt(msg_id::FORM_VERB_ADD_TITLE), test_base::TIMEOUT_LOCALHOST);
    }

}