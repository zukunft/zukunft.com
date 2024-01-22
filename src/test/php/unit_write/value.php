<?php

/*

    test/php/unit_write/value.php - write test VALUES to the database and check the results
    -----------------------------
  

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

namespace test\write;

include_once MODEL_VALUE_PATH . 'value_dsp_old.php';

use api\value\value as value_api;
use api\word\word as word_api;
use api\word\triple as triple_api;
use cfg\value\value;
use cfg\value\value_dsp_old;
use html\figure\figure as figure_dsp;
use cfg\log\change_log_field;
use cfg\log\change;
use cfg\log\change_log_table;
use cfg\library;
use cfg\phrase_list;
use test\test_cleanup;
use const test\TIMEOUT_LIMIT_DB_MULTI;

class value_test
{

    function run(test_cleanup $t): void
    {

        global $test_val_lst;
        $lib = new library();

        $t->header('Test the value class (classes/value.php)');

        // check if loading the value without time still returns the value
        /* TODO fix and activate
        $val = $t->load_value(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO
        ));
        $t->assert('Check if loading the latest value works',
            $val->number(), value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);
        */

        // check if loading value with a phrase returns a value created with the phrase parts
        // e.g. the value created with words canton and zurich
        // should be returned if requested with the phrase canton of zurich
        // TODO activate
        $val = $t->load_value(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020
        ));
        //$t->assert('Check if loading the latest value works',
        //    $val->number(), value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // test load by phrase list first to get the value id
        $ch_inhabitants = $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2019
        ),
            value_api::TV_CH_INHABITANTS_2019_IN_MIO);

        if (!$ch_inhabitants->is_id_set()) {
            log_err('Loading of test value ' . $ch_inhabitants->dsp_id() . ' failed');
        } else {
            // test load by value id
            $val = $t->load_value_by_id($t->usr1, $ch_inhabitants->id());
            $result = $val->number();
            $target = value_api::TV_CH_INHABITANTS_2019_IN_MIO;
            $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

            // test load by phrase list first to get the value id
            $phr_lst = $t->load_phrase_list(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
            $val_by_phr_lst = new value($t->usr1);
            $val_by_phr_lst->load_by_grp($phr_lst->get_grp_id());
            $result = $val_by_phr_lst->number();
            $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
            $t->display(', value->load for another word list ' . $phr_lst->dsp_name(), $target, $result);

            // test load by value id
            $val = new value($t->usr1);
            if ($val_by_phr_lst->is_id_set()) {
                $val->load_by_id($val_by_phr_lst->id(), value::class);
                $result = $val->number();
                $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO;
                $t->display(', value->load for value id "' . $ch_inhabitants->id() . '"', $target, $result);

                // test rebuild_grp_id by value id
                $result = $val->check();
                $target = true;
            }
            $t->display(', value->check for value id "' . $ch_inhabitants->id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        }

        // test another rebuild_grp_id by value id
        $chk_phr_grp = $t->load_word_list(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020))->get_grp();
        $chk_val = new value($t->usr1);
        if ($chk_phr_grp != null) {
            $chk_val->load_by_grp($chk_phr_grp);
        }
        $target = true;
        if (!$chk_val->is_id_set()) {
            $chk_phr_grp = $t->load_word_list(array(
                word_api::TN_CANTON,
                word_api::TN_ZH,
                word_api::TN_INHABITANTS,
                word_api::TN_MIO))->get_grp();
            $chk_val = new value($t->usr1);
            if ($chk_phr_grp != null) {
                $chk_val->load_by_grp($chk_phr_grp);
            }
        }
        if (!$chk_val->is_id_set()) {
            $result = 'No value found for ' . $chk_phr_grp->dsp_id() . '.';
            $t->display(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        } else {
            $result = $chk_val->check();
            $t->display(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

            // ... and check the number
            $result = $chk_val->number();
            $target = value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO;
            $t->display(', value->load for "' . $chk_phr_grp->dsp_id() . '"', $target, $result);

            // ... and check the words loaded
            $result = $chk_val->name();
            $target = '2020,Canton,Zurich,inhabitants,million';
            $t->display(', value->load words', $target, $result);

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
            $t->display(', value->load_phrases reloaded words', $target, $result);
        }

        // test load the word list object
        $phr_lst = $t->load_word_list(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020));
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
                if ($val->grp != null) {
                    if ($val->phr_lst()->wrd_lst() != null) {
                        $val_lst = $val->phr_lst()->names();
                        $result = array_diff($val_lst, $phr_lst->names());
                    }
                }
            }
        }
        $target = array();
        $t->display(', value->load for group id "' . $grp->id() . '"', $target, $result);

        // test the formatting of a value (percent)
        $pct_val = $t->load_value(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_PCT,
            word_api::TN_2020));
        $result = $pct_val->dsp_obj()->display(0);
        $target = number_format(round(value_api::TV_PCT * 100, 2), 2) . '%';
        $t->display(', value->val_formatted for ' . $pct_val->dsp_id(), $target, $result);

        // test the scaling of a value
        $phr_lst = $t->load_phrase_list(array(word_api::TN_CH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $dest_phr_lst = new phrase_list($t->usr1);
        $dest_phr_lst->load_by_names(array(word_api::TN_INHABITANTS, word_api::TN_ONE));
        $mio_val = new value($t->usr1);
        $mio_val->load_by_grp($phr_lst->get_grp_id());
        $result = $mio_val->scale($dest_phr_lst);
        $target = value_api::TV_CH_INHABITANTS_2020_IN_MIO * 1000000;
        $t->display(', value->val_scaling for a word list ' . $phr_lst->dsp_id() . '', $target, $result);

        // test the figure object creation
        $phr_lst = $t->load_phrase_list(array(word_api::TN_CANTON, word_api::TN_ZH, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $mio_val = new value_dsp_old($t->usr1);
        $mio_val->load_by_grp($phr_lst->get_grp_id());
        $fig = $mio_val->figure();
        $fig_dsp = $t->dsp_obj($fig, new figure_dsp());
        $result = $fig_dsp->display_linked('1');
        $target = '<a href="/http/result_edit.php?id=' . $mio_val->id() . '&back=1" title="1.55">1.55</a>';
        $t->assert(', value->figure->display_linked for word list ' . $phr_lst->dsp_id(), $result, $target);

        // test the HTML code creation
        $result = $mio_val->display();
        $target = number_format(value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO, 2, DEFAULT_DEC_POINT, DEFAULT_THOUSAND_SEP);
        $t->display(', value->display', $target, $result);

        // test the HTML code creation including the hyperlink
        $result = $mio_val->display_linked('1');
        //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
        $target = '<a href="/http/value_edit.php?id=' . $mio_val->id() . '&back=1"  >1.55</a>';
        $t->assert(', value->display_linked', $result, $target);

        // change the number to force using the thousand separator
        $mio_val->set_number(value_api::TV_INT);
        $result = $mio_val->display_linked('1');
        //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
        $target = '<a href="/http/value_edit.php?id=' . $mio_val->id() . '&back=1"  >123\'456</a>';
        $t->assert(', value->display_linked', $result, $target);

        // convert the user input for the database
        $mio_val->usr_value = value_api::TV_USER_HIGH_QUOTE;
        $result = $mio_val->convert();
        $target = value_api::TV_INT;
        $t->display(', value->convert user input', $target, $result);

        // convert the user input with space for the database
        $mio_val->usr_value = value_api::TV_USER_SPACE;
        $result = $mio_val->convert();
        $target = value_api::TV_INT;
        $t->display(', value->convert user input', $target, $result);

        // test adding a value in the database
        // as it is call from value_add.php with all phrases in an id list including the time phrase,
        // so the time phrase must be excluded
        $phr_grp = $t->load_phrase_group(array(word_api::TN_RENAMED, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2020));
        $add_val = new value($t->usr1);
        $add_val->grp = $phr_grp;
        $add_val->set_number(value_api::TV_BIG);
        $result = $add_val->save();
        $target = '';
        $t->display(', value->save ' . $add_val->number() . ' for ' . $phr_grp->dsp_id() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        $test_val_lst[] = $add_val->id();


        // ... check if the value adding has been logged
        if ($add_val->is_id_set()) {
            $log = new change($t->usr1);
            $log->set_table(change_log_table::VALUE);
            $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
            $log->row_id = $add_val->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test added 123456789';
        $t->display(', value->save logged for "' . $phr_grp->name() . '"', $target, $result);

        // ... check if the value has been added
        $added_val = new value($t->usr1);
        $added_val->load_by_grp($phr_grp);
        $result = $added_val->number();
        $target = '123456789';
        $t->display(', value->load the value previous saved for "' . $phr_grp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        // remember the added value id to be able to remove the test
        $added_val_id = $added_val->id();
        $test_val_lst[] = $added_val->id();

        // test if a value with the same phrases, but different time can be added
        $phr_grp2 = $t->load_phrase_group(array(word_api::TN_RENAMED, word_api::TN_INHABITANTS, word_api::TN_MIO, word_api::TN_2019));
        $add_val2 = new value($t->usr1);
        $add_val2->grp = $phr_grp2;
        $add_val2->set_number(value_api::TV_BIGGER);
        $result = $add_val2->save();
        $target = '';
        $t->display(', value->save ' . $add_val2->number() . ' for ' . $phr_grp2->name() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // test if a value with time stamp can be saved
        /*
        $phr_lst_ts = test_phrase_list(array(word_api::TN_RENAMED, word_api::TN_INHABITANTS, word_api::TN_MIO));
        $add_val_ts = new value($t->usr1);
        $add_val_ts->ids = $phr_lst_ts->ids;
        $add_val_ts->set_number(TV_ABB_PRICE_20200515;
        $add_val_ts->time_stamp = new DateTime('2020-05-15');
        $result = $add_val_ts->save();
        $target = '';
        $t->display(', value->save ' . $add_val_ts->number() . ' for ' . $phr_lst_ts->name() . ' and ' . $add_val_ts->time_stamp->format(DateTimeInterface::ATOM) . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        */

        // ... check if the value adding has been logged
        if ($add_val->is_id_set()) {
            $log = new change($t->usr1);
            $log->set_table(change_log_table::VALUE);
            $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
            $log->row_id = $add_val2->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test added 234567890';
        $t->display(', value->save logged for "' . $phr_grp2->name() . '"', $target, $result);

        // ... check if the value has been added
        $added_val2 = new value($t->usr1);
        $added_val2->load_by_grp($phr_grp2);
        $result = $added_val2->number();
        $target = '234567890';
        $t->display(', value->load the value previous saved for "' . $phr_grp2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        // remember the added value id to be able to remove the test
        $test_val_lst[] = $added_val2->id();

        // check if the value can be changed
        $added_val = new value($t->usr1);
        $added_val->load_by_id($added_val_id);
        $added_val->set_number(987654321);
        $result = $added_val->save();
        $target = '';
        $t->display(', word->save update value id "' . $added_val_id . '" from  "' . $add_val->number() . '" to "' . $added_val->number() . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change has been logged
        if ($added_val->is_id_set()) {
            $log = new change($t->usr1);
            $log->set_table(change_log_table::VALUE);
            $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
            $log->row_id = $added_val->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test changed 123456789 to 987654321';
        $t->display(', value->save logged for "' . word_api::TN_RENAMED . '"', $target, $result);

        // ... check if the value has really been updated
        $added_val = new value($t->usr1);
        $added_val->load_by_id($added_val_id);
        $result = $added_val->number();
        $target = '987654321';
        $t->display(', value->load the value previous updated for "' . word_api::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific value is created if another user changes the value
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_id($added_val_id);
        $val_usr2->set_number(23456);
        $result = $val_usr2->save();
        $target = '';
        $t->display(', value->save ' . $val_usr2->number() . ' for ' . $phr_lst->name() . ' and user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change for the other user has been logged
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_id($added_val_id);
        if ($val_usr2->is_id_set()) {
            $log = new change($t->usr2);
            $log->set_table(change_log_table::VALUE_USR);
            $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
            $log->row_id = $val_usr2->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test partner changed 987654321 to 23456';
        $t->display(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

        // ... check if the value has really been updated
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $result = $added_val_usr2->number();
        $target = '23456';
        $t->display(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value for the original user remains unchanged
        $added_val = new value($t->usr1);
        $added_val->load_by_grp($phr_grp);
        $result = $added_val->number();
        $target = '987654321';
        $t->display(', value->load for user "' . $t->usr1->name . '" is still', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // check if undo all specific changes removes the user value
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $added_val_usr2->set_number(987654321);
        $result = $added_val_usr2->save();
        $target = '';
        $t->display(', value->save change to ' . $val_usr2->number() . ' for ' . $phr_grp->name() . ' and user "' . $t->usr2->name . '" should undo the user change', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... check if the value change for the other user has been logged
        $val_usr2 = new value($t->usr2);
        $val_usr2->load_by_grp($phr_grp);
        if ($val_usr2->is_id_set()) {
            $log = new change($t->usr2);
            $log->set_table(change_log_table::VALUE_USR);
            $log->set_field(change_log_field::FLD_NUMERIC_VALUE);
            $log->row_id = $val_usr2->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test partner changed 23456 to 987654321';
        $t->display(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

        // ... check if the value has really been changed back
        $added_val_usr2 = new value($t->usr2);
        $added_val_usr2->load_by_grp($phr_grp);
        $result = $added_val_usr2->number();
        $target = '987654321';
        $t->display(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // test adding a value
        // if the word is not used, the user can add or remove words
        // if a value is used adding another word should create a new value
        // but if the new value with the added word already exists the values should be merged after a confirmation by the user

        // test to remove a word from the value
        /*$added_val = New value;
        $added_val->id = $added_val_id;
        $added_val->usr = $t->usr1;
        $added_val->load();
        $wrd_to_del = load_word(TW_CHF);
        $result = $added_val->del_wrd($wrd_to_del->id);
        $wrd_lst = $added_val->wrd_lst;
        $result = $wrd_lst->does_contain(TW_CHF);
        $target = false;
        $t->display(', value->add_wrd has "'.TW_CHF.'" been removed from the word list of the value', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

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
        $t->display(', value->add_wrd has "'.TW_EUR.'" been added to the word list of the value', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
        */


    }

    function create_test_values(test_cleanup $t): void
    {
        $t->header('Check if all base values exist or create them if needed');

        // add a number with a concrete time value
        // e.g. inhabitants in the canton of zurich in the year 2020
        // used to test if loading the value without time returns this value a the last available
        $t->test_value(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020
        ),
            value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

        // add a number with a triple without time definition
        // e.g. the inhabitants in the city of zurich
        // using the triple zurich (city) instead of two single words
        // used to test if requesting the value with the separate words returns the value
        $t->test_value(array(
            triple_api::TN_ZH_CITY,
            word_api::TN_INHABITANTS
        ),
            value_api::TV_CITY_ZH_INHABITANTS_2019);

        // ... same with the concrete year
        $t->test_value(array(
            triple_api::TN_ZH_CITY,
            word_api::TN_INHABITANTS,
            word_api::TN_2019
        ),
            value_api::TV_CITY_ZH_INHABITANTS_2019);

        // add the number of inhabitants in switzerland without time definition
        $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO
        ),
            value_api::TV_CH_INHABITANTS_2020_IN_MIO);

        // ... same with the concrete year
        $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2020
        ),
            value_api::TV_CH_INHABITANTS_2020_IN_MIO);

        // ... same with the previous year
        $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_MIO,
            word_api::TN_2019
        ),
            value_api::TV_CH_INHABITANTS_2019_IN_MIO);

        // add the percentage of inhabitants in Canton Zurich compared to Switzerland for calculation validation
        $t->test_value(array(
            word_api::TN_CANTON,
            word_api::TN_ZH,
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_PCT,
            word_api::TN_2020
        ),
            value_api::TV_PCT);

        // add the increase of inhabitants in Switzerland from 2019 to 2020 for calculation validation
        $t->test_value(array(
            word_api::TN_CH,
            word_api::TN_INHABITANTS,
            word_api::TN_INCREASE,
            word_api::TN_PCT,
            word_api::TN_2020
        ),
            value_api::TV_INCREASE);

        // add some simple number for formula testing
        $t->test_value(array(
            word_api::TN_SHARE,
            word_api::TN_CHF
        ),
            value_api::TV_SHARE_PRICE);

        $t->test_value(array(
            word_api::TN_EARNING,
            word_api::TN_CHF
        ),
            value_api::TV_EARNINGS_PER_SHARE);

    }
}