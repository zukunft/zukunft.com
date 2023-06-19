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

use api\word_api;
use cfg\config;
use model\phrase;
use model\value;
use model\value_list;
use model\word;

class value_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->header('Value list unit database tests to test src/main/php/model/value/value_list.php');
        $t->name = 'value list_read db->';
        $t->resource_path = 'db/value/';

        $t->subheader('Get related');

        // load by id
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_ids([1,2]);
        $pi = new value($t->usr1);
        $pi->load_by_id(1);
        $e = new value($t->usr1);
        $e->load_by_id(2);
        $target_lst = new value_list($t->usr1);
        $target_lst->add($pi);
        $target_lst->add($e);
        $test_name = 'Loading pi and e via value list is the same as single loading';
        $t->assert($test_name, $val_lst->dsp_id(), $target_lst->dsp_id());
        $target = '3.1415926535898 (1) / 0.57721566490153 (2)';
        $test_name = 'A value list with pi and e matches the expected result';
        $t->assert($test_name, $val_lst->dsp_id(), $target);

        // load by phrase list
        $phr = new phrase($t->usr1, word::SYSTEM_CONFIG);
        $phr_lst = $phr->all_children();
        $val_lst = new value_list($t->usr1);
        $val_lst->load_by_phr_lst($phr_lst);
        $test_name = 'System configuration values contain also the default number of years';
        $target = 10;
        $t->assert_contains($test_name, $val_lst->numbers(), [$target]);

    }

}

