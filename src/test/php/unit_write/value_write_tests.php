<?php

/*

    test/php/unit_write/value_tests.php - write test VALUES to the database and check the results
    -----------------------------------
  

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

namespace unit_write;

use cfg\const\paths;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';

use cfg\log\change_values_big;
use cfg\log\change_values_norm;
use cfg\log\change_values_prime;
use cfg\phrase\phrase_list;
use cfg\user\user;
use cfg\value\value;
use html\figure\figure as figure_dsp;
use html\value\value as value_dsp;
use shared\const\users;
use shared\enum\change_fields;
use shared\helper\Config as shared_config;
use shared\library;
use shared\const\triples;
use shared\const\values;
use shared\const\words;
use shared\types\api_type;
use test\test_cleanup;

class value_write_tests
{

    // const only used for these write tests
    const NUMBER_TEST = 123456789;
    const NUMBER_ADD2 = 234567890;
    const NUMBER_ADD = 987654321;
    const NUMBER_CHANGED = 23456;

    function run(test_cleanup $t): void
    {

        global $test_val_lst;

        // init
        $t->name = 'value->';
        $lib = new library();

        // start the test section (ts)
        $ts = 'write value ';
        $t->header($ts);

        // test another rebuild_grp_id by value id
        $chk_phr_grp = $t->load_word_list(array(
            words::CANTON,
            words::ZH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020))->get_grp();
        $chk_val = new value($t->usr1);
        if ($chk_phr_grp != null) {
            $chk_val->load_by_grp($chk_phr_grp);
        }
        if (!$chk_val->is_id_set()) {
            $chk_phr_grp = $t->load_word_list(array(
                words::CANTON,
                words::ZH,
                words::INHABITANTS,
                words::MIO))->get_grp();
            $chk_val = new value($t->usr1);
            if ($chk_phr_grp != null) {
                $chk_val->load_by_grp($chk_phr_grp);
            }
        }
        if (!$chk_val->is_id_set()) {
            $result = 'No value found for ' . $chk_phr_grp->dsp_id() . '.';
            $t->assert(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $result, true, $t::TIMEOUT_LIMIT_DB_MULTI);
        } else {
            $result = $chk_val->check();
            $t->assert(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $result, true, $t::TIMEOUT_LIMIT_DB_MULTI);

            // ... and check the number
            $result = $chk_val->number();
            $target = values::CANTON_ZH_INHABITANTS_2020_IN_MIO;
            $t->assert(', value->load for "' . $chk_phr_grp->dsp_id() . '"', $result, $target);

            // ... and check the words loaded
            $result = $chk_val->name();
            $target = words::YEAR_2020 . ','
                . words::CANTON . ','
                . words::INHABITANTS . ','
                . words::MIO . ','
                . words::ZH;
            $t->assert(', value->load words', $result, $target);

            // ... and check the word reloading by group
            $chk_val->phr_lst()->set_lst(array());
            $chk_val->load_phrases();
            if (!$chk_val->phr_lst()->is_empty()) {
                // TODO check if sort is needed
                //$chk_val->phr_lst()->wlsort();
                $result = $lib->dsp_array($chk_val->phr_names());
            } else {
                $result = '';
            }
            $t->assert(', value->load_phrases reloaded words', $result, $target);
        }

        // test load the word list object
        $phr_lst = $t->load_word_list(array(
            words::CANTON,
            words::ZH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020));
        //$phr_lst->ex_time();
        $grp = $phr_lst->get_grp();
        if (!$grp->is_id_set()) {
            $result = 'No word list found.';
        } else {
            $val = new value($t->usr1);
            $val->load_by_grp($grp);
            $result = '';
            if (!$val->is_id_set()) {
                $result = 'No value found for ' . $val->dsp_id() . '.';
            } else {
                if ($val->grp() != null) {
                    if ($val->phr_lst()->words() != null) {
                        $val_lst = $val->phr_lst()->names();
                        $result = array_diff($val_lst, $phr_lst->names());
                    }
                }
            }
        }
        $target = array();
        $t->assert(', value->load for group id "' . $grp->id() . '"', $result, $target);

        // test the formatting of a value (percent)
        $pct_val = $t->load_value(array(
            words::CANTON,
            words::ZH,
            words::CH,
            words::INHABITANTS,
            words::PCT,
            words::YEAR_2020));
        $api_msg = $pct_val->api_json([api_type::INCL_PHRASES]);
        $val_dsp = new value_dsp($api_msg);
        $result = $val_dsp->value(0);
        $target = number_format(round(values::SAMPLE_PCT * 100, 2), 2) . '%';
        $t->assert(', value->val_formatted for ' . $pct_val->dsp_id(), $result, $target);

        // test the scaling of a value
        $phr_lst = $t->load_phrase_list(array(words::CH, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $dest_phr_lst = new phrase_list($t->usr1);
        $dest_phr_lst->load_by_names(array(words::INHABITANTS, words::ONE));
        $mio_val = new value($t->usr1);
        $mio_val->load_by_grp($phr_lst->get_grp_id());
        $result = $mio_val->scale($dest_phr_lst);
        $target = values::CH_INHABITANTS_2020_IN_MIO * 1000000;
        $t->assert(', value->val_scaling for a word list ' . $phr_lst->dsp_id(), $result, $target);

        // test the figure object creation
        $phr_lst = $t->load_phrase_list(array(words::CANTON, words::ZH, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $mio_val = new value($t->usr1);
        $mio_val->load_by_grp($phr_lst->get_grp_id());
        $mio_val_dsp = new value_dsp();
        $mio_val_dsp->set_from_json($mio_val->api_json([api_type::INCL_PHRASES]));
        $fig = $mio_val->figure();
        $fig_dsp = $t->dsp_obj($fig, new figure_dsp());
        $result = $fig_dsp->display_linked('1');
        $target = '<a href="/http/result_edit.php?id=' . $mio_val_dsp->id() . '&back=1" title="1.55">1.55</a>';
        $t->assert(', value->figure->display_linked for word list ' . $phr_lst->dsp_id(), $result, $target);

        // test the HTML code creation
        $result = $mio_val_dsp->value();
        $target = number_format(values::CANTON_ZH_INHABITANTS_2020_IN_MIO, 2, shared_config::DEFAULT_DEC_POINT, shared_config::DEFAULT_THOUSAND_SEP);
        $t->display(', value->display', $result, $target);

        // test the HTML code creation including the hyperlink
        $result = $mio_val_dsp->value_edit('1');
        //$target = '<a class="' . styles::STYLE_USER . '" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
        $target = '<a href="/http/view.php?m=value_edit&id=' . $mio_val_dsp->id() . '&back=1" title="1.55">1.55</a>';
        $t->assert(', value->display_linked', $result, $target);

        // change the number to force using the thousand separator
        $mio_val_dsp->set_number(values::SAMPLE_INT);
        $result = $mio_val_dsp->value_edit('1');
        //$target = '<a class="' . styles::STYLE_USER . '" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
        $target = '<a href="/http/view.php?m=value_edit&id=' . $mio_val_dsp->id() . '&back=1" title="123\'456">123\'456</a>';
        $t->assert(', value->display_linked', $result, $target);

        // convert the user input for the database
        $mio_val->usr_value = values::SAMPLE_FLOAT_HIGH_QUOTE_FORM;
        $result = $mio_val->convert();
        $target = values::SAMPLE_INT;
        $t->assert(', value->convert user input', $result, $target);

        // convert the user input with space for the database
        $mio_val->usr_value = values::SAMPLE_FLOAT_SPACE_FORM;
        $result = $mio_val->convert();
        $target = values::SAMPLE_INT;
        $t->assert(', value->convert user input', $result, $target);

        // test adding a value in the database
        // as it is call from value_add.php with all phrases in an id list including the time phrase,
        // so the time phrase must be excluded
        $phr_grp = $t->load_phrase_group(array(words::TEST_RENAMED, words::INHABITANTS, words::MIO, words::YEAR_2020));
        $add_val = new value($t->usr1);
        $add_val->set_grp($phr_grp);
        $add_val->set_number(values::SAMPLE_BIG);
        $result = $add_val->save()->get_last_message();
        $target = '';
        $t->assert(', value->save ' . $add_val->number() . ' for ' . $phr_grp->dsp_id() . ' by user "' . $t->usr1->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        $test_val_lst[] = $add_val->id();


        // ... check if the value adding has been logged
        if ($add_val->is_id_set()) {
            $val_class = change_values_norm::class;
            if ($add_val->is_prime()) {
                $val_class = change_values_prime::class;
            } elseif ($add_val->is_big()) {
                $val_class = change_values_big::class;
            }
            $result = $t->log_last_by_field($add_val, change_fields::FLD_NUMERIC_VALUE, $add_val->id(), true);
        }
        $target = users::SYSTEM_TEST_NAME . ' added ' . self::NUMBER_TEST;
        // TODO activate
        //$t->assert(', value->save logged for "' . $phr_grp->name() . '"', $result, $target);

        // ... check if the value has been added
        $added_val = new value($t->usr1);
        $added_val->load_by_grp($phr_grp);
        $result = $added_val->number();
        $target = self::NUMBER_TEST;
        $t->assert(', value->load the value previous saved for "' . $phr_grp->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        // remember the added value id to be able to remove the test
        $added_val_id = $added_val->id();
        $test_val_lst[] = $added_val->id();

        // test if a value with the same phrases, but different time can be added
        $phr_grp2 = $t->load_phrase_group(array(words::TEST_RENAMED, words::INHABITANTS, words::MIO, words::YEAR_2019));
        $add_val2 = new value($t->usr1);
        $add_val2->set_grp($phr_grp2);
        $add_val2->set_number(values::SAMPLE_BIGGER);
        $result = $add_val2->save()->get_last_message();
        $target = '';
        $t->assert(', value->save ' . $add_val2->number() . ' for ' . $phr_grp2->name() . ' by user "' . $t->usr1->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // test if a value with time stamp can be saved
        /*
        $phr_lst_ts = test_phrase_list(array(words::TN_RENAMED, words::TN_INHABITANTS, words::TN_MIO));
        $add_val_ts = new value($t->usr1);
        $add_val_ts->ids = $phr_lst_ts->ids;
        $add_val_ts->set_number(TV_ABB_PRICE_20200515;
        $add_val_ts->time_stamp = new DateTime('2020-05-15');
        $result = $add_val_ts->save()->get_last_message();
        $target = '';
        $t->display(', value->save ' . $add_val_ts->number() . ' for ' . $phr_lst_ts->name() . ' and ' . $add_val_ts->time_stamp->format(DateTimeInterface::ATOM) . ' by user "' . $t->usr1->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        */

        // ... check if the value adding has been logged
        if ($add_val->is_id_set()) {
            $result = $t->log_last_by_field($add_val2, change_fields::FLD_NUMERIC_VALUE, $add_val2->id(), true);
        }
        $target = users::SYSTEM_TEST_NAME . ' added ' . self::NUMBER_ADD2;
        // TODO activate
        //$t->assert(', value->save logged for "' . $phr_grp2->name() . '"', $result, $target);

        // ... check if the value has been added
        $added_val2 = new value($t->usr1);
        $added_val2->load_by_grp($phr_grp2);
        $result = $added_val2->number();
        $target = self::NUMBER_ADD2;
        $t->assert(', value->load the value previous saved for "' . $phr_grp2->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        // remember the added value id to be able to remove the test
        $test_val_lst[] = $added_val2->id();

        // check if the value can be changed
        $added_val = new value($t->usr1);
        $added_val->load_by_id($added_val_id);
        $added_val->set_number(self::NUMBER_ADD);
        $result = $added_val->save()->get_last_message();
        $target = '';
        $t->assert(', word->save update value id "' . $added_val_id . '" from  "' . $add_val->number() . '" to "' . $added_val->number() . '".', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change has been logged
        if ($added_val->is_id_set()) {
            $result = $t->log_last_by_field($added_val, change_fields::FLD_NUMERIC_VALUE, $added_val->id(), true);
        }
        // TODO fix it
        $target = users::SYSTEM_TEST_NAME . ' changed ' . self::NUMBER_TEST . ' to ' . self::NUMBER_ADD;
        if ($result != $target) {
            $target = users::SYSTEM_TEST_NAME . ' added ' . self::NUMBER_TEST . '';
        }
        // TODO activate
        //$t->assert(', value->save logged for "' . words::TN_RENAMED . '"', $result, $target);

        // ... check if the value has really been updated
        $added_val = new value($t->usr1);
        $added_val->load_by_id($added_val_id);
        $result = $added_val->number();
        $target = self::NUMBER_ADD;
        $t->assert(', value->load the value previous updated for "' . words::TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific value is created if another user changes the value
        // TODO check loaded value matches the value for usr1
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_id($added_val_id);
        $val_usr2->set_number(self::NUMBER_CHANGED);
        $result = $val_usr2->save()->get_last_message();
        $target = '';
        $t->assert(', value->save ' . $val_usr2->number() . ' for ' . $phr_lst->name() . ' and user "' . $t->usr2->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change for the other user has been logged
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_id($added_val_id);
        if ($val_usr2->is_id_set()) {
            $result = $t->log_last_by_field($val_usr2, change_fields::FLD_NUMERIC_VALUE, $val_usr2->id(),
                true);
        }
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' changed "' . self::NUMBER_ADD . '" to "' . self::NUMBER_CHANGED . '"';
        // TODO activate
        //$t->assert(', value->save logged for user "' . $t->usr2->name . '"', $result, $target);

        // ... check if the value has really been updated
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $result = $added_val_usr2->number();
        $target = self::NUMBER_CHANGED;
        $t->assert(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value for the original user remains unchanged
        $added_val = new value($t->usr1);
        $added_val->load_by_grp($phr_grp);
        $result = $added_val->number();
        $target = self::NUMBER_ADD;
        $t->assert(', value->load for user "' . $t->usr1->name . '" is still', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if undo all specific changes removes the user value
        $test_name = 'change to ' . $val_usr2->number() . ' for ' . $phr_grp->name() . ' and user "' . $t->usr2->name . '" should undo the user change';
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $added_val_usr2->set_number(self::NUMBER_ADD);
        $result = $added_val_usr2->save()->get_last_message();
        $target = '';
        $t->assert($ts . $test_name, $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change for the other user has been logged
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_grp($phr_grp);
        if ($val_usr2->is_id_set()) {
            $result = $t->log_last_by_field($val_usr2,  change_fields::FLD_NUMERIC_VALUE, $val_usr2->id(),
                true);
        }
        $target = users::SYSTEM_TEST_PARTNER_NAME . ' changed "' . self::NUMBER_CHANGED . '" to "' . self::NUMBER_ADD . '"';
        $t->assert(', value->save logged for user "' . $t->usr2->name . '"', $result, $target);

        // ... check if the value has really been changed back
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $result = $added_val_usr2->number();
        $target = self::NUMBER_ADD;
        $t->assert(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // test adding a value
        // if the word is not used, the user can add or remove words
        // if a value is used adding another word should create a new value
        // but if the new value with the added word already exists the values should be merged after a confirmation by the user

        // test to remove a word from the value
        /*$added_val = New value;
        $added_val->id = $added_val_id;
        $added_val->usr = $t->usr1;
        $added_val->load();
        $wrd_to_del = load_word(words::TN_CHF);
        $result = $added_val->del_wrd($wrd_to_del->id);
        $wrd_lst = $added_val->wrd_lst;
        $result = $wrd_lst->does_contain(words::TN_CHF);
        $target = false;
        $t->assert(', value->add_wrd has "'.words::TN_CHF.'" been removed from the word list of the value', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // test to link an additional word to a value
        $added_val = New value;
        $added_val->id = $added_val_id;
        $added_val->usr = $t->usr1;
        $added_val->load();
        $wrd_to_add = load_word(TW_EUR);
        $result = $added_val->add_wrd($wrd_to_add->id);
        // load word list
        $wrd_lst = $added_val->wrd_lst;
        // does the word list contain TW_EUR
        $result = $wrd_lst->does_contain(TW_EUR);
        $target = true;
        $t->assert(', value->add_wrd has "'.TW_EUR.'" been added to the word list of the value', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);
        */

        /*
         * remove the test values just added
         */

        /*
        $added_val = new value($t->usr1);
        $added_val->load_by_id($added_val_id);
        $added_val->del();

        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_grp($phr_grp);
        $val_usr2->del();
        */


    }

    function create_test_values(test_cleanup $t): void
    {
        $t->header('Check if all base values exist or create them if needed');

        // add a number with a concrete time value
        // e.g. inhabitants in the canton of zurich in the year 2020
        // used to test if loading the value without time returns this value a the last available
        $t->test_value(array(
            words::CANTON,
            words::ZH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020
        ),
            values::CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // add a number with a triple without time definition
        // e.g. the inhabitants in the city of zurich
        // using the triple zurich (city) instead of two single words
        // used to test if requesting the value with the separate words returns the value
        $t->test_value(array(
            triples::CITY_ZH,
            words::INHABITANTS
        ),
            values::CITY_ZH_INHABITANTS_2019);

        // ... same with the concrete year
        $t->test_value(array(
            triples::CITY_ZH,
            words::INHABITANTS,
            words::YEAR_2019
        ),
            values::CITY_ZH_INHABITANTS_2019);

        // add the number of inhabitants in switzerland without time definition
        $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO
        ),
            values::CH_INHABITANTS_2020_IN_MIO);

        // ... same with the concrete year
        $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2020
        ),
            values::CH_INHABITANTS_2020_IN_MIO);

        // ... same with the previous year
        $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::MIO,
            words::YEAR_2019
        ),
            values::CH_INHABITANTS_2019_IN_MIO);

        // add the percentage of inhabitants in Canton Zurich compared to Switzerland for calculation validation
        $t->test_value(array(
            words::CANTON,
            words::ZH,
            words::CH,
            words::INHABITANTS,
            words::PCT,
            words::YEAR_2020
        ),
            values::SAMPLE_PCT);

        // add the increase of inhabitants in Switzerland from 2019 to 2020 for calculation validation
        $t->test_value(array(
            words::CH,
            words::INHABITANTS,
            words::TEST_INCREASE,
            words::PCT,
            words::YEAR_2020
        ),
            values::INCREASE);

        // add some simple number for formula testing
        $t->test_value(array(
            words::TEST_SHARE,
            words::TEST_CHF
        ),
            values::SHARE_PRICE);

        $t->test_value(array(
            words::TEST_EARNING,
            words::TEST_CHF
        ),
            values::EARNINGS_PER_SHARE);

    }
}