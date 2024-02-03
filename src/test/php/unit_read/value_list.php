<?php

/*

    test/php/unit_read/value_list.php - database unit testing of the value list functions
    ---------------------------------


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

namespace test;

use api\word\word as word_api;
use cfg\config;
use cfg\phrase;
use cfg\value\value;
use cfg\value\value_list;
use cfg\word;

class value_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->header('Value list unit database tests to test src/main/php/model/value/value_list.php');
        $t->name = 'value list_read db->';
        $t->resource_path = 'db/value/';

        $t->subheader('Get related');

        // load by phrase
        $test_name = 'Load a value list by phrase pi';
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_phr($t->phrase_pi());
        $result = $val_lst->dsp_id();
        $target = '"" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,,) for user 2 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        // load by ids
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_ids([5,8]);
        $pi = new value($t->usr1);
        $pi->load_by_id(5);
        $e = new value($t->usr1);
        $e->load_by_id(8);
        $target_lst = new value_list($t->usr1);
        $target_lst->add($pi);
        $target_lst->add($e);
        $test_name = 'Loading pi and e via value list is the same as single loading';
        $target = $target_lst->dsp_id();
        $result = $val_lst->dsp_id();
        // TODO check why order may changes
        if ($target != $result) {
            $target = '"" 0.57721566490153 / "" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 4,,, / -2,,,) for user 2 (zukunft.com system test)';
        }
        $t->assert($test_name, $result, $target);
        $target = '"" 3.1415926535898 / "" 0.57721566490153 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,, / 4,,,) for user 2 (zukunft.com system test)';
        if ($target != $result) {
            $target = '"" 0.57721566490153 / "" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 4,,, / -2,,,) for user 2 (zukunft.com system test)';
        }
        $test_name = 'A value list with pi and e matches the expected result';
        $t->assert($test_name, $val_lst->dsp_id(), $target);

        // load values related to all phrases of a list
        $test_name = 'Load the the inhabitants of Canton Zurich over time';
        $val_lst = new value_list($t->usr1);
        $phr_lst = $t->dummy_phrase_list_zh();
        $val_lst->load_by_phr_lst($phr_lst);
        $result = $val_lst->dsp_id();
        $target = '"" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,,) for user 2 (zukunft.com system test)';
        // TODO activate Prio 1
        //$t->assert($test_name, $result, $target);

        // load values related to any phrase of a list
        $test_name = 'Load the list of math const';
        $val_lst = new value_list($t->usr1);
        $phr_lst = $t->phrase_list_math_const();
        $val_lst->load_by_phr_lst($phr_lst, true);
        $result = $val_lst->dsp_id();
        $target = '"" 3.1415926535898 / "" 0.57721566490153 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,, / -3,,,) for user 2 (zukunft.com system test)';
        if ($target != $result) {
            $target = '"" 0.57721566490153 / "" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -3,,, / -2,,,) for user 2 (zukunft.com system test)';
        }
        $t->assert($test_name, $result, $target);

        // load by phrase list
        $phr = new phrase($t->usr1, word::SYSTEM_CONFIG);
        $phr_lst = $phr->all_children();
        $val_lst = new value_list($t->usr1);
        // TODO activate Prio 1
        //$val_lst->load_by_phr_lst($phr_lst);
        $test_name = 'System configuration values contain also the default number of years';
        $target = 10;
        //$t->assert_contains($test_name, $val_lst->numbers(), [$target]);

        // ... based on the phrase list
        $phr_lst = $t->dummy_phrase_list();
        $val_lst = $phr_lst->val_lst();
        $result = $val_lst->dsp_id();
        $target = '"" 3.1415926535898 / "" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = -2,,, / 3,,,) for user 2 (zukunft.com system test)';
        if ($target != $result) {
            $target = '"" 3.1415926535898 / "" 3.1415926535898 (phrase_id_1, phrase_id_2, phrase_id_3, phrase_id_4 = 3,,, / -2,,,) for user 2 (zukunft.com system test)';
        }
        $t->assert($test_name, $result, $target);

    }

}

