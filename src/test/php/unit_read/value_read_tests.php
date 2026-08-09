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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::SHARED_CONST . 'triples.php';

use Zukunft\ZukunftCom\main\php\cfg\group\group_id;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\const\groups;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class value_read_tests
{

    function run(test_cleanup $t): void
    {
        $msg = new user_message();

        // init
        $t_db = new test_db_load($t);
        $t->name = 'value->';

        // start the test section (ts)
        $ts = 'db read value ';
        $t->header($ts);

        $t->subheader($ts . 'by id');
        $test_name = word_names::PI . ' number is ' . values::PI_LONG;
        $val = new value($t->usr1);
        $val->load_by_id(values::PI_MATH_ID, $msg);
        $t->assert($ts . $test_name, $val->number(), values::PI_LONG);

        // regression test for sandbox_multi::load_standard: loading the standard row into a
        // fresh object by id must select the table and the where condition based on the given
        // id and not on the empty object vars (the pattern used by value_base::save)
        $test_name = 'load_standard into a fresh object gets the value by id';
        $std_val = new value($t->usr1);
        $std_val->load_standard(values::PI_MATH_ID, $msg);
        $t->assert($ts . $test_name, $std_val->number(), values::PI_LONG);

        // negative: a missing standard row is reported instead of failing later
        // e.g. with 'Value for numeric_value is undefined'
        $test_name = 'load_standard for an unknown id reports the missing row';
        $msg_missing = new user_message();
        $std_val_missing = new value($t->usr1);
        $std_val_missing->load_standard(999999999, $msg_missing);
        $t->assert_false($ts . $test_name, $msg_missing->is_ok());

        // the pi number value is keyed by the "Pi (math)" triple (see the pi value in units.json),
        // so the phrase group of the loaded value is named after that triple
        $test_name = word_names::PI . ' phrase group ' . triple_names::PI;
        $val->load_objects($msg);
        $t->assert($ts . $test_name, $val->name(), triple_names::PI);

        $test_name = word_names::PI . ' phrase ' . triple_names::PI_COM;
        $phr_lst = $val->grp()->phrase_list();
        if ($phr_lst->count() > 0) {
            $phr = $phr_lst->lst()[0];
            $t->assert($ts . $test_name, $phr->get_description(), triple_names::PI_COM);
            $test_name = word_names::PI . ' phrase code id ' . phrase_types::TRIPLE_HIDDEN;
            $t->assert($ts . $test_name, $phr->type_code_id($msg), phrase_types::TRIPLE_HIDDEN);
        } else {
            log_err($ts . $test_name . ' has no phrases');
        }

        $t->subheader($ts . 'by phrase group');
        $test_name = ' ' . words::CH . ' ' . word_names::INHABITANTS;
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(
            array(words::CH, word_names::INHABITANTS, word_names::MIO, word_names::YEAR_2020)
        , $msg);
        $val = new value($t->usr1);
        $val->load_by_grp($phr_lst->get_grp_id(), $msg);
        $t->assert($ts . $test_name, $val->number(), values::CH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value without time returns the latest value';
        $val = $t_db->load_value(array(
            word_names::CANTON,
            word_names::ZH,
            word_names::INHABITANTS,
            word_names::MIO
        ));
        // TODO Prio 2 activate
        //$t->assert($ts . $test_name, $val->number(), values::CANTON_ZH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value of a words group can be accessed by the triple e.g. '
            . word_names::INHABITANTS . ' of ' . word_names::ZH . ' and ' . word_names::CANTON
            . ' is fallback value for ' . triple_names::CANTON_ZURICH;
        // check if loading value with a phrase returns a value created with the phrase parts
        // e.g. the value created with words canton and zurich
        // should be returned if requested with the phrase canton of zurich
        // TODO Prio 2 activate
        $val = $t_db->load_value(array(
            triple_names::CANTON_ZURICH,
            word_names::INHABITANTS,
            word_names::MIO,
            word_names::YEAR_2020
        ));
        //$t->assert('Check if loading the latest value works',
        //    $val->number(), values::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        $test_name = 'value of a triple can be accessed by the word group e.g. '
            . word_names::INHABITANTS . ' of ' . triple_names::CANTON_ZURICH
            . ' is fallback value for ' . word_names::ZH . ' and ' . word_names::CANTON;
        // check if loading value with a phrase returns a value created with the phrase parts
        // e.g. the value created with words canton and zurich
        // should be returned if requested with the phrase canton of zurich
        // TODO Prio 2 activate
        $val = $t_db->load_value(array(
            word_names::CANTON,
            word_names::ZH,
            word_names::INHABITANTS,
            word_names::MIO,
            word_names::YEAR_2020
        ));
        //$t->assert('Check if loading the latest value works',
        //    $val->number(), values::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // test load by phrase list first to get the value id
        $ch_inhabitants = $t_db->test_value(array(
            words::CH,
            word_names::INHABITANTS,
            word_names::MIO,
            word_names::YEAR_2019
        ),
            values::CH_INHABITANTS_2019_IN_MIO);

        if (!$ch_inhabitants->is_id_set()) {
            log_err('Loading of test value ' . $ch_inhabitants->dsp_id() . ' failed');
        } else {
            // test load by value id
            $val = $t_db->load_value_by_id($t->usr1, $ch_inhabitants->id());
            $result = $val->number();
            $target = values::CH_INHABITANTS_2019_IN_MIO;
            $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

            // test load by phrase list first to get the value id
            $phr_lst = $t_db->load_phrase_list(array(words::CH, word_names::INHABITANTS, word_names::MIO, word_names::YEAR_2019));
            $val_by_phr_lst = new value($t->usr1);
            $val_by_phr_lst->load_by_grp($phr_lst->get_grp_id(), $msg);
            $result = $val_by_phr_lst->number();
            $target = values::CH_INHABITANTS_2019_IN_MIO;
            $t->assert(', value->load for another word list ' . $phr_lst->dsp_name(), $result, $target);

            // test load by phrase list first to get the value id
            $phr_lst = $t_db->load_phrase_list(array(words::CH, word_names::INHABITANTS, word_names::MIO, word_names::YEAR_2020));
            $val_by_phr_lst = new value($t->usr1);
            $val_by_phr_lst->load_by_grp($phr_lst->get_grp_id(), $msg);
            $result = $val_by_phr_lst->number();
            $target = values::CH_INHABITANTS_2020_IN_MIO;
            $t->assert(', value->load for another word list ' . $phr_lst->dsp_name(), $result, $target);

            // test load by value id
            $val = new value($t->usr1);
            if ($val_by_phr_lst->is_id_set()) {
                $val->load_by_id($val_by_phr_lst->id(), $msg);
                $result = $val->number();
                $target = values::CH_INHABITANTS_2020_IN_MIO;
                $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

                // test rebuild_grp_id by value id
                $result = $val->check($msg);
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
        $phr_lst->add_name(triple_names::PI_NAME, $msg);
        $grp = new group_id();
        $val->load_by_id($grp->get_id($phr_lst), $msg);
        $val->load_objects($msg);

        $test_name = groups::TN_READ;
        $phr_grp = $t_db->add_phrase_group(array(triple_names::PI_NAME), groups::TN_READ, $msg);
        $val = $t_db->load_value_by_phr_grp($phr_grp);
        $t->assert_export_reload($ts . $test_name, $val);

    }

}

