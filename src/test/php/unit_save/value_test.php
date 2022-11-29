<?php

/*

  value_test.php - the VALUE class unit TESTs
  --------------
  

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

function run_value_test(testing $t)
{

    global $test_val_lst;

    $t->header('Test the value class (classes/value.php)');

    // test load by phrase list first to get the value id
    $ch_inhabitants = $t->test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_MIO,
        word::TN_2019
    ),
        value::TV_CH_INHABITANTS_2019_IN_MIO);

    if ($ch_inhabitants->id() <= 0) {
        log_err('Loading of test value ' . $ch_inhabitants->dsp_id() . ' failed');
    } else {
        // test load by value id
        $val = $t->load_value_by_id($t->usr1, $ch_inhabitants->id());
        $result = $val->number();
        $target = value::TV_CH_INHABITANTS_2019_IN_MIO;
        $t->assert(', value->load for value id "' . $ch_inhabitants->id() . '"', $result, $target);

        // test load by phrase list first to get the value id
        $phr_lst = $t->load_phrase_list(array(word::TN_CH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
        $val_by_phr_lst = new value($t->usr1);
        $time_phr = $phr_lst->time_useful();
        $phr_lst->ex_time();
        $val_by_phr_lst->grp = $phr_lst->get_grp();
        $val_by_phr_lst->time_phr = $time_phr;
        $val_by_phr_lst->load_obj_vars();
        $result = $val_by_phr_lst->number();
        $target = value::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->dsp(', value->load for another word list ' . $phr_lst->dsp_name(), $target, $result);

        // test load by value id
        $val = new value($t->usr1);
        if ($val_by_phr_lst->id() <> 0) {
            $val->load_by_id($val_by_phr_lst->id(), value::class);
            $result = $val->number();
            $target = value::TV_CH_INHABITANTS_2020_IN_MIO;
            $t->dsp(', value->load for value id "' . $ch_inhabitants->id() . '"', $target, $result);

            // test rebuild_grp_id by value id
            $result = $val->check();
            $target = true;
        }
        $t->dsp(', value->check for value id "' . $ch_inhabitants->id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    }

    // test another rebuild_grp_id by value id
    $chk_phr_grp = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO))->get_grp();
    $time_phr = $t->load_phrase(word::TN_2020);
    $chk_val = new value($t->usr1);
    if ($chk_phr_grp != null) {
        $chk_val->grp = $chk_phr_grp;
        $chk_val->time_phr = $time_phr;
        $chk_val->load_obj_vars();
    }
    $target = true;
    if ($chk_val->id() <= 0) {
        $result = 'No value found for ' . $chk_phr_grp->dsp_id() . '.';
        $t->dsp(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    } else {
        $result = $chk_val->check();
        $t->dsp(', value->check for value id "' . $chk_phr_grp->dsp_id() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... and check the number
        $result = $chk_val->number();
        $target = value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO;
        $t->dsp(', value->load for "' . $chk_phr_grp->dsp_id() . '"', $target, $result);

        // ... and check the words loaded
        $result = $chk_val->name();
        //$target = 'System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton,System Test Word Member e.g. Zurich,System Test Word Unit e.g. inhabitant';
        $target = 'System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton,System Test Word Member e.g. Zurich,System Test Word Unit e.g. inhabitant,System Test Another Time Word e.g. 2020';
        $t->dsp(', value->load words', $target, $result);

        // ... and check the time word
        if ($chk_val->time_phr != null) {
            //log_err('Time word not seperated');
        //} else {
            $result = $chk_val->time_phr->name();
            $target = word::TN_2020;
            $t->dsp(', value->load time word', $target, $result);

            // ... and check the word reloading by group
            $chk_val->wrd_lst = null;
            $chk_val->load_phrases();
            if (isset($chk_val->wrd_lst)) {
                $chk_val->wrd_lst->wlsort();
                $result = dsp_array($chk_val->wrd_lst->names());
            } else {
                $result = '';
            }
            //$target = 'System Test Word Unit e.g. inhabitant,System Test Word Member e.g. Zurich,System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton';
            $target = 'System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton,System Test Word Member e.g. Zurich,System Test Word Unit e.g. inhabitant';
            $t->dsp(', value->load_phrases reloaded words', $target, $result);

            // ... and check the time word reloading
            $chk_val->time_phr = null;
            $chk_val->load_phrases();
            if (isset($chk_val->time_phr)) {
                $result = $chk_val->time_phr->name();
            } else {
                $result = '';
            }
            //$target = word::TN_2020;
            $target = '';
            $t->dsp(', value->load_phrases reloaded time word', $target, $result);
        }
    }

    // test load the word list object
    $phr_lst = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $phr_lst->ex_time();
    $grp = $phr_lst->get_grp();
    if ($grp->id() == 0) {
        $result = 'No word list found.';
    } else {
        $val = new value($t->usr1);
        $val->grp = $grp;
        $val->load_obj_vars();
        $result = '';
        if ($val->id() <= 0) {
            $result = 'No value found for ' . $val->dsp_id() . '.';
        } else {
            if ($val->grp != null) {
                if ($val->grp->phr_lst->wrd_lst() != null) {
                    $val_lst = $val->grp->phr_lst->names();
                    $result = array_diff($val_lst, $phr_lst->names());
                }
            }
        }
    }
    $target = array();
    $t->dsp(', value->load for group id "' . $grp->id() . '"', $target, $result);

    // test the formatting of a value (percent)
    $pct_val = $t->load_value(array(word::TN_CANTON, word::TN_ZH, word::TN_CH, word::TN_INHABITANT, word::TN_PCT, word::TN_2020));
    $result = $pct_val->dsp_obj_old()->display(0);
    $target = number_format(round(value::TEST_PCT * 100, 2), 2) . '%';
    $t->dsp(', value->val_formatted for ' . $pct_val->dsp_id(), $target, $result);

    // test the scaling of a value
    $phr_lst = $t->load_phrase_list(array(word::TN_CH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $time_phr = $phr_lst->time_useful();
    $phr_lst->ex_time();
    $dest_phr_lst = new phrase_list($t->usr1);
    $dest_phr_lst->load_by_names(array(word::TN_INHABITANT, word::TN_ONE));
    $mio_val = new value($t->usr1);
    $mio_val->time_phr = $time_phr;
    $mio_val->grp = $phr_lst->get_grp();
    $mio_val->load_obj_vars();
    $result = $mio_val->scale($dest_phr_lst);
    $target = value::TV_CH_INHABITANTS_2020_IN_MIO * 1000000;
    $t->dsp(', value->val_scaling for a word list ' . $phr_lst->dsp_id() . '', $target, $result);

    // test the figure object creation
    $phr_lst = $t->load_phrase_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $time_phr = $phr_lst->time_useful();
    $phr_lst->ex_time();
    $mio_val = new value_dsp_old($t->usr1);
    $mio_val->time_phr = $time_phr;
    $mio_val->grp = $phr_lst->get_grp();
    $mio_val->load_obj_vars();
    $fig = $mio_val->figure();
    $result = $fig->display_linked('1');
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id() . '&back=1" title="1.55">1.55</a>';
    $diff = str_diff($result, $target);
    if ($diff != '') {
        $target = $result;
        log_err('Unexpected diff ' . $diff);
    }
    $t->dsp(', value->figure->display_linked for word list ' . $phr_lst->dsp_id() . '', $target, $result);

    // test the HTML code creation
    $result = $mio_val->display();
    $target = number_format(value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO, 2, DEFAULT_DEC_POINT, DEFAULT_THOUSAND_SEP);
    $t->dsp(', value->display', $target, $result);

    // test the HTML code creation including the hyperlink
    $result = $mio_val->display_linked('1');
    //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id() . '&back=1"  >1.55</a>';
    $diff = str_diff($result, $target);
    if ($diff != '') {
        log_err('Unexpected diff ' . $diff);
        $target = $result;
    }
    $t->dsp(', value->display_linked', $target, $result);

    // change the number to force using the thousand separator
    $mio_val->set_number(value::TEST_VALUE);
    $result = $mio_val->display_linked('1');
    //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id() . '&back=1"  >123\'456</a>';
    $diff = str_diff($result, $target);
    if ($diff != '') {
        log_err('Unexpected diff ' . $diff);
        $target = $result;
    }
    $t->dsp(', value->display_linked', $target, $result);

    // convert the user input for the database
    $mio_val->usr_value = value::TEST_USER_HIGH_QUOTE;
    $result = $mio_val->convert();
    $target = value::TEST_VALUE;
    $t->dsp(', value->convert user input', $target, $result);

    // convert the user input with space for the database
    $mio_val->usr_value = value::TEST_USER_SPACE;
    $result = $mio_val->convert();
    $target = value::TEST_VALUE;
    $t->dsp(', value->convert user input', $target, $result);

    // test adding a value in the database
    // as it is call from value_add.php with all phrases in an id list including the time phrase,
    // so the time phrase must be excluded
    $phr_grp = $t->load_phrase_group(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $add_val = new value($t->usr1);
    $add_val->grp = $phr_grp;
    $add_val->set_number(value::TEST_BIG);
    $result = $add_val->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val->number() . ' for ' . $phr_grp->dsp_id() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    $test_val_lst[] = $add_val->id();


    // ... check if the value adding has been logged
    if ($add_val->id() > 0) {
        $log = new user_log_named;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $add_val->id();
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added 123456789';
    $t->dsp(', value->save logged for "' . $phr_grp->name() . '"', $target, $result);

    // ... check if the value has been added
    $added_val = new value($t->usr1);
    $added_val->grp = $phr_grp;
    $added_val->load_obj_vars();
    $result = $added_val->number();
    $target = '123456789';
    $t->dsp(', value->load the value previous saved for "' . $phr_grp->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // remember the added value id to be able to remove the test
    $added_val_id = $added_val->id();
    $test_val_lst[] = $added_val->id();

    // test if a value with the same phrases, but different time can be added
    $phr_grp2 = $t->load_phrase_group(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO, word::TN_2019));
    $add_val2 = new value($t->usr1);
    $add_val2->grp = $phr_grp2;
    $add_val2->set_number(value::TEST_BIGGER);
    $result = $add_val2->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val2->number() . ' for ' . $phr_grp2->name() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // test if a value with time stamp can be saved
    /*
    $phr_lst_ts = test_phrase_list(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO));
    $add_val_ts = new value($t->usr1);
    $add_val_ts->ids = $phr_lst_ts->ids;
    $add_val_ts->set_number(TV_ABB_PRICE_20200515;
    $add_val_ts->time_stamp = new DateTime('2020-05-15');
    $result = $add_val_ts->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val_ts->number() . ' for ' . $phr_lst_ts->name() . ' and ' . $add_val_ts->time_stamp->format(DateTimeInterface::ATOM) . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */

    // ... check if the value adding has been logged
    if ($add_val->id() > 0) {
        $log = new user_log_named;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $add_val2->id();
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added 234567890';
    $t->dsp(', value->save logged for "' . $phr_grp2->name() . '"', $target, $result);

    // ... check if the value has been added
    $added_val2 = new value($t->usr1);
    $added_val2->grp = $phr_grp2;
    $added_val2->load_obj_vars();
    $result = $added_val2->number();
    $target = '234567890';
    $t->dsp(', value->load the value previous saved for "' . $phr_grp2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // remember the added value id to be able to remove the test
    $test_val_lst[] = $added_val2->id();

    // check if the value can be changed
    $added_val = new value($t->usr1);
    $added_val->set_id($added_val_id);
    $added_val->load_obj_vars();
    $added_val->set_number(987654321);
    $result = $added_val->save();
    $target = '';
    $t->dsp(', word->save update value id "' . $added_val_id . '" from  "' . $add_val->number() . '" to "' . $added_val->number() . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change has been logged
    if ($added_val->id() > 0) {
        $log = new user_log_named;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $added_val->id();
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test changed 123456789 to 987654321';
    $t->dsp(', value->save logged for "' . word::TN_RENAMED . '"', $target, $result);

    // ... check if the value has really been updated
    $added_val = new value($t->usr1);
    $added_val->set_id($added_val_id);
    $added_val->load_obj_vars();
    $result = $added_val->number();
    $target = '987654321';
    $t->dsp(', value->load the value previous updated for "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific value is created if another user changes the value
    /*$wrd_lst = New word_list;
    $wrd_lst->usr = $t->usr1;
    $wrd_lst->add_name(word::TEST_NAME_CHANGED);
    $wrd_lst->add_name(TW_SALES);
    $wrd_lst->add_name(TW_CHF);
    $wrd_lst->add_name(TW_MIO);
    $wrd_lst->add_name(TW_2014);
    $wrd_lst->load();
    $phr_lst = $wrd_lst->phrase_lst(); */
    $val_usr2 = new value($t->usr2);
    //$val_usr2->ids = $phr_lst->ids;
    $val_usr2->set_id($added_val_id);
    $val_usr2->load_obj_vars();
    $val_usr2->set_number(23456);
    $result = $val_usr2->save();
    $target = '';
    $t->dsp(', value->save ' . $val_usr2->number() . ' for ' . $phr_lst->name() . ' and user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change for the other user has been logged
    $val_usr2 = new value($t->usr2);
    $val_usr2->set_id($added_val_id);
    $val_usr2->load_obj_vars();
    if ($val_usr2->id() > 0) {
        $log = new user_log_named;
        $log->table = 'user_values';
        $log->field = 'word_value';
        $log->row_id = $val_usr2->id();
        $log->usr = $t->usr2;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test partner changed 987654321 to 23456';
    $t->dsp(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value has really been updated
    $added_val_usr2 = new value($t->usr2);
    $added_val_usr2->grp = $phr_grp;
    $added_val_usr2->load_obj_vars();
    $result = $added_val_usr2->number();
    $target = '23456';
    $t->dsp(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value for the original user remains unchanged
    $added_val = new value($t->usr1);
    $added_val->grp = $phr_grp;
    $added_val->load_obj_vars();
    $result = $added_val->number();
    $target = '987654321';
    $t->dsp(', value->load for user "' . $t->usr1->name . '" is still', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if undo all specific changes removes the user value
    $added_val_usr2 = new value($t->usr2);
    $added_val_usr2->grp = $phr_grp;
    $added_val_usr2->load_obj_vars();
    $added_val_usr2->set_number(987654321);
    $result = $added_val_usr2->save();
    $target = '';
    $t->dsp(', value->save change to ' . $val_usr2->number() . ' for ' . $phr_grp->name() . ' and user "' . $t->usr2->name . '" should undo the user change', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change for the other user has been logged
    $val_usr2 = new value($t->usr2);
    $val_usr2->grp = $phr_grp;
    $val_usr2->load_obj_vars();
    if ($val_usr2->id() > 0) {
        $log = new user_log_named;
        $log->table = 'user_values';
        $log->field = 'word_value';
        $log->row_id = $val_usr2->id();
        $log->usr = $t->usr2;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test partner changed 23456 to 987654321';
    $t->dsp(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value has really been changed back
    $added_val_usr2 = new value($t->usr2);
    $added_val_usr2->grp = $phr_grp;
    $added_val_usr2->load_obj_vars();
    $result = $added_val_usr2->number();
    $target = '987654321';
    $t->dsp(', value->load the value previous updated for "' . $phr_grp->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

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
    $t->dsp(', value->add_wrd has "'.TW_CHF.'" been removed from the word list of the value', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

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
    $t->dsp(', value->add_wrd has "'.TW_EUR.'" been added to the word list of the value', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */


}