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

include_once SHARED_CONST_PATH . 'triples.php';

use cfg\db\sql;
use cfg\db\sql_type;
use cfg\group\group_id;
use cfg\log\change_values_norm;
use cfg\phrase\phrase_list;
use cfg\user\user;
use cfg\value\value;
use shared\const\groups;
use shared\const\triples;
use shared\const\values;
use shared\const\words;
use shared\enum\change_fields;
use shared\types\phrase_type;
use test\test_cleanup;

class value_read_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t->name = 'value->';

        // start the test section (ts)
        $ts = 'read value ';
        $t->header($ts);

        $t->subheader($ts . 'by id');
        $test_name = words::PI . ' number is ' . values::PI_LONG;
        $val = new value($t->usr1);
        $val->load_by_id(values::PI_ID);
        $t->assert($ts . $test_name, $val->number(), values::PI_LONG);

        $test_name = words::PI . ' phrase group ' . groups::TN_READ;
        $val->load_objects();
        $t->assert($ts . $test_name, $val->name(), groups::TN_READ);

        $test_name = words::PI . ' phrase ' . triples::PI_COM;
        $phr_lst = $val->grp()->phrase_list();
        if ($phr_lst->count() > 0) {
            $phr = $phr_lst->lst()[0];
            $t->assert($ts . $test_name, $phr->description(), triples::PI_COM);
            $test_name = words::PI . ' phrase code id ' . phrase_type::TRIPLE_HIDDEN;
            $t->assert($ts . $test_name, $phr->type_code_id(), phrase_type::TRIPLE_HIDDEN);
        } else {
            log_err($ts . $test_name . ' has no phrases');
        }

        $t->subheader($ts . 'by phrase group');
        $test_name = ' ' . words::CH . ' ' . words::INHABITANTS;
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(
            array(words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020)
        );
        $val = new value($t->usr1);
        $val->load_by_grp($phr_lst->get_grp_id());
        $t->assert($ts . $test_name, $val->number(), values::CH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value without time returns the latest value';
        $val = $t->load_value(array(
            words::CANTON,
            words::ZH,
            words::INHABITANTS,
            words::MIO
        ));
        // TODO activate
        //$t->assert($ts . $test_name, $val->number(), values::CANTON_ZH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value of a words group can be accessed by the triple e.g. '
            . words::INHABITANTS . ' of ' . words::ZH . ' and ' . words::CANTON
            . ' is fallback value for ' . triples::CANTON_ZURICH;
        // check if loading value with a phrase returns a value created with the phrase parts
        // e.g. the value created with words canton and zurich
        // should be returned if requested with the phrase canton of zurich
        // TODO activate Prio 2
        $val = $t->load_value(array(
            triples::CANTON_ZURICH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020
        ));
        //$t->assert('Check if loading the latest value works',
        //    $val->number(), values::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value of a triple can be accessed by the word group e.g. '
            . words::INHABITANTS . ' of ' . triples::CANTON_ZURICH
            . ' is fallback value for ' . words::ZH . ' and ' . words::CANTON;
        // check if loading value with a phrase returns a value created with the phrase parts
        // e.g. the value created with words canton and zurich
        // should be returned if requested with the phrase canton of zurich
        // TODO activate Prio 2
        $val = $t->load_value(array(
            words::CANTON,
            words::ZH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020
        ));
        //$t->assert('Check if loading the latest value works',
        //    $val->number(), values::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // test load by phrase list first to get the value id
        $ch_inhabitants = $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2019
        ),
            values::CH_INHABITANTS_2019_IN_MIO);

        if (!$ch_inhabitants->is_id_set()) {
            log_err('Loading of test value ' . $ch_inhabitants->dsp_id() . ' failed');
        } else {
            // test load by value id
            $val = $t->load_value_by_id($t->usr1, $ch_inhabitants->id());
            $result = $val->number();
            $target = values::CH_INHABITANTS_2019_IN_MIO;
            $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

            // test load by phrase list first to get the value id
            $phr_lst = $t->load_phrase_list(array(words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020));
            $val_by_phr_lst = new value($t->usr1);
            $val_by_phr_lst->load_by_grp($phr_lst->get_grp_id());
            $result = $val_by_phr_lst->number();
            $target = values::CH_INHABITANTS_2020_IN_MIO;
            $t->assert(', value->load for another word list ' . $phr_lst->dsp_name(), $result, $target);

            // test load by value id
            $val = new value($t->usr1);
            if ($val_by_phr_lst->is_id_set()) {
                $val->load_by_id($val_by_phr_lst->id());
                $result = $val->number();
                $target = values::CH_INHABITANTS_2020_IN_MIO;
                $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

                // test rebuild_grp_id by value id
                $result = $val->check();
                $target = true;
            }
            $t->assert(', value->check for value id "' . $ch_inhabitants->id() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        }


        // TODO add time, test and geo value read tests

        /*
        $test_name = 'load the latest value by phrase group';
        $phr_lst->ex_time();
        $val = new value($t->usr1);
        $val->load_by_grp($phr_lst->get_grp());
        $result = $val->number();
        $target = values::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->assert($test_name, $result, $target);
        */


        $t->subheader($ts . 'frontend api');
        $val = new value($t->usr1);
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add_name(triples::PI_NAME);
        $grp = new group_id();
        $val->load_by_id($grp->get_id($phr_lst));
        $val->load_objects();

        $test_name = groups::TN_READ;
        $phr_grp = $t->add_phrase_group(array(triples::PI_NAME), groups::TN_READ);
        $val = $t->load_value_by_phr_grp($phr_grp);
        $t->assert_export_reload($ts . $test_name, $val);

    }

}

