<?php

/*

    test/php/unit_read/result_list_read_tests.php - result list tests that only read from the database
    ---------------------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';

use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class result_list_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message($t->usr1);
        $t->name = 'result list read db->';

        // start the test section (ts)
        $ts = 'db read result list ';
        $t->header($ts);

        // the results have no word or triple id field, so they are selected by the phrase in their group id
        $test_name = 'the results of the seed triple "' . triple_names::INITIAL_EFFORT . '" are loaded by its phrase';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::INITIAL_EFFORT, $msg);
        $res_lst = new result_list($t->usr1);
        $res_lst->load_by_phrase($trp->phrase(), $msg);
        $res_cnt = $res_lst->count();
        $t->assert_greater($test_name, 0, $res_cnt, $t::TIMEOUT_LIMIT_DB);
        // negative: a word that no formula result uses
        $test_name = 'the seed word "' . word_names::MATH . '" has no result';
        $wrd = new word($t->usr1);
        $wrd->load_by_name(word_names::MATH, $msg);
        $res_lst = new result_list($t->usr1);
        $res_lst->load_by_phrase($wrd->phrase(), $msg);
        $res_cnt = $res_lst->count();
        $t->assert($test_name, $res_cnt, 0, $t::TIMEOUT_LIMIT_DB);

    }

}
