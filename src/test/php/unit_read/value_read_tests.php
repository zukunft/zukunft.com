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

namespace unit_read;

include_once SHARED_PATH . 'triples.php';

use api\phrase\group as group_api;
use api\word\triple as triple_api;
use api\value\value as value_api;
use api\word\word as word_api;
use cfg\group\group_id;
use cfg\phrase\phrase_list;
use cfg\value\value;
use cfg\value\value_base;
use shared\triples;
use shared\types\phrase_type;
use test\test_cleanup;

class value_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'value->';

        $t->header('Unit database tests of the value class (src/main/php/model/value/value.php)');

        $t->subheader('Value load tests');

        $test_name = 'load a value by id';
        $val = new value($t->usr1);
        $val->load_by_id(value_api::TI_PI);
        $val->load_objects();
        $t->assert($test_name, $val->number(), value_api::TV_READ);
        $t->assert($test_name, $val->name(), group_api::TN_READ);
        $phr_lst = $val->grp()->phrase_list();
        if ($phr_lst->count() > 0) {
            $phr = $phr_lst->lst()[0];
            $t->assert($test_name, $phr->description(), triples::TD_PI);
            $t->assert($test_name, $phr->type_code_id(), phrase_type::TRIPLE_HIDDEN);
        }

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
        $val->load_by_grp($phr_lst->get_grp());
        $result = $val->number();
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->assert($test_name, $result, $target);
        */

        $t->subheader('Frontend tests');

        $val = new value($t->usr1);
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add_name(triples::TN_PI_NAME);
        $grp = new group_id();
        $val->load_by_id($grp->get_id($phr_lst));
        $val->load_objects();

        $phr_grp = $t->add_phrase_group(array(triples::TN_PI_NAME), group_api::TN_READ);
        $val = $t->load_value_by_phr_grp($phr_grp);
        $t->assert_export_reload($val);

    }

}

