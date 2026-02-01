<?php

/*

    test/unit/expression.php - unit testing of the helper objects
    ------------------------
  

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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_HELPER . 'ListOf.php';
include_once test_paths::CREATE . 'test_words.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\shared\helper\ListOf;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class base_object_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $t_wrd = new test_words($t);

        // start the test section (ts)
        $ts = 'unit base object ';
        $t->header($ts);

        $t->subheader($ts . 'list');
        $test_name = 'count';
        $lst = new ListOf([$t_wrd->word(), $t_wrd->word_inhabitant(), $t_wrd->word_inhabitant()]);
        $t->assert($test_name, 3, $lst->count());
        $test_name = 'get first';
        $wrd = $lst->get(0);
        $t->assert($test_name, $wrd->id(), $t_wrd->word()->id());
        $test_name = 'get not existing';
        $wrd = $lst->get(4);
        $t->assert_null($test_name, $wrd);
        $test_name = 'report not existing';
        $usr_msg = new user_message();
        $lst->get(4, $usr_msg);
        $t->assert($test_name, $usr_msg->all_message_text(), '4 is missing in ListOf');
        $test_name = 'reset';
        $lst->reset();
        $t->assert($test_name, 0, $lst->count());
        $test_name = 'is empty';
        $t->assert_true($test_name, $lst->is_empty());

        // TODO Prio 0 add add and unset tests

        // TODO Prio 2 add tests fpr CombineObject, Config, IdObject, ListOfIdObjects, MapObjects, TextIdObjects, Translator and Workflow


    }

}