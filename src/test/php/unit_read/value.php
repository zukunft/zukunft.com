<?php

/*

    test/php/unit_read/value.php - database unit testing of the value functions
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

namespace test;

use api\system\phrase_group_api;
use api\system\triple_api;
use api\system\value_api;
use api\system\word_api;
use cfg\log\phrase_list;
use cfg\log\value;

class value_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'value->';

        $t->header('Unit database tests of the value class (src/main/php/model/value/value.php)');

        $t->subheader('Value load tests');

        $test_name = 'load a value by phrase group';
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(
            array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020)
        );
        $val = new value($t->usr1);
        $val->load_by_grp($phr_lst->get_grp_id());
        $result = $val->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->assert($test_name, $result, $target);

        /*
        $test_name = 'load the latest value by phrase group';
        $phr_lst->ex_time();
        $val = new value($t->usr1);
        $val->grp = $phr_lst->get_grp();
        $val->load_obj_vars();
        $result = $val->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->assert($test_name, $result, $target);
        */

        $t->subheader('Frontend API tests');

        $val = new value($t->usr1);
        $val->load_by_id(1, value::class);
        $val->load_objects();
        $api_val = $val->api_obj();
        $t->assert($t->name . 'api->id', $api_val->id, $val->id());
        $t->assert($t->name . 'api->number', $api_val->number(), $val->number());
        $t->assert_api_json_msg($api_val);

        $phr_grp = $t->add_phrase_group(array(triple_api::TN_PI_NAME), phrase_group_api::TN_READ);
        $val = $t->load_value_by_phr_grp($phr_grp);
        $t->assert_api_obj($val);

    }

}

