<?php

/*

  value_test.php - the VALUE class unit TESTs
  --------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

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

    if ($ch_inhabitants->id > 0) {
        // test load by value id
        $val = new value;
        $val->id = $ch_inhabitants->id;
        $val->usr = $t->usr1;
        $val->load();
        $result = $val->number;
        $target = value::TV_CH_INHABITANTS_2019_IN_MIO;
        $t->dsp(', value->load for value id "' . $ch_inhabitants->id . '"', $target, $result);

        // test load by word list first to get the value id
        $wrd_lst = $t->load_word_list(array(word::TN_CH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
        $val_by_wrd_lst = new value;
        $val_by_wrd_lst->ids = $wrd_lst->ids;
        $val_by_wrd_lst->usr = $t->usr1;
        $val_by_wrd_lst->load();
        $result = $val_by_wrd_lst->number;
        $target = value::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->dsp(', value->load for another word list ' . $wrd_lst->name(), $target, $result);

        // test load by value id
        $val = new value;
        $val->id = $val_by_wrd_lst->id;
        $val->usr = $t->usr1;
        $val->load();
        $result = $val->number;
        $target = value::TV_CH_INHABITANTS_2020_IN_MIO;
        $t->dsp(', value->load for value id "' . $ch_inhabitants->id . '"', $target, $result);

        // test rebuild_grp_id by value id
        $result = $val->check();
        $target = true;
        $t->dsp(', value->check for value id "' . $ch_inhabitants->id . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    }

    // test another rebuild_grp_id by value id
    $chk_wrd_lst = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $chk_val = new value;
    $chk_val->ids = $chk_wrd_lst->ids;
    $chk_val->usr = $t->usr1;
    $chk_val->load();
    $target = true;
    if ($chk_val->id <= 0) {
        $result = 'No value found for ' . $chk_wrd_lst->dsp_id() . '.';
        $t->dsp(', value->check for value id "' . implode(",", $chk_wrd_lst->names()) . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    } else {
        $result = $chk_val->check();
        $t->dsp(', value->check for value id "' . implode(",", $chk_wrd_lst->names()) . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

        // ... and check the number
        $result = $chk_val->number;
        $target = value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO;
        $t->dsp(', value->load for "' . dsp_array($chk_wrd_lst->names()) . '"', $target, $result);

        // ... and check the words loaded
        $result = dsp_array($chk_val->wrd_lst->names());
        $target = 'System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton,System Test Word Member e.g. Zurich,System Test Word Unit e.g. inhabitant';
        $t->dsp(', value->load words', $target, $result);

        // ... and check the time word
        $result = $chk_val->time_phr->name;
        $target = word::TN_2020;
        $t->dsp(', value->load time word', $target, $result);

        // ... and check the word reloading by group
        $chk_val->wrd_lst = null;
        $chk_val->load_phrases();
        if (isset($chk_val->wrd_lst)) {
            $result = dsp_array($chk_val->wrd_lst->names());
        } else {
            $result = '';
        }
        $target = 'System Test Scaling Word e.g. millions,System Test Word Category e.g. Canton,System Test Word Member e.g. Zurich,System Test Word Unit e.g. inhabitant';
        $t->dsp(', value->load_phrases reloaded words', $target, $result);

        // ... and check the time word reloading
        $chk_val->time_phr = null;
        $chk_val->load_phrases();
        if (isset($chk_val->time_phr)) {
            $result = $chk_val->time_phr->name;
        } else {
            $result = '';
        }
        $target = word::TN_2020;
        $t->dsp(', value->load_phrases reloaded time word', $target, $result);
    }

    // test load the word list object
    $wrd_lst = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $wrd_lst->ex_time();
    $grp = $wrd_lst->get_grp();
    if ($grp->id == 0) {
        $result = 'No word list found.';
    } else {
        $val = new value;
        $val->grp = $grp;
        $val->grp_id = $grp->id;
        $val->usr = $t->usr1;
        $val->load();
        $result = '';
        if ($val->id <= 0) {
            $result = 'No value found for ' . $val->dsp_id() . '.';
        } else {
            if ($val->grp != null) {
                if ($val->grp->wrd_lst != null) {
                    $result = dsp_array($val->grp->wrd_lst->names());
                }
            }
        }
    }
    $target = dsp_array($wrd_lst->names());
    $t->dsp(', value->load for group id "' . $grp->id . '"', $target, $result);

    // test load the word list object via word ids
    $val = new value;
    $val->grp = null;
    $val->ids = $wrd_lst->ids;
    $val->usr = $t->usr1;
    $val->load();
    $result = '';
    if ($val->id > 0) {
        if (isset($val->phr_lst)) {
            $result = dsp_array($val->phr_lst->names());
        }
    }
    $target = dsp_array($wrd_lst->names());
    $t->dsp(', value->load for ids ' . dsp_array($wrd_lst->ids) . '', $target, $result);


    // test the formatting of a value (percent)
    $wrd_lst = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_CH, word::TN_INHABITANT, word::TN_PCT, word::TN_2020));
    $pct_val = new value_dsp;
    $pct_val->ids = $wrd_lst->ids;
    $pct_val->usr = $t->usr1;
    $pct_val->load();
    $result = $pct_val->display(0);
    $target = number_format(round(value::TEST_PCT * 100, 2), 2) . '%' ;
    $t->dsp(', value->val_formatted for a word list ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test the scaling of a value
    $wrd_lst = $t->load_word_list(array(word::TN_CH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $dest_wrd_lst = new word_list;
    $dest_wrd_lst->usr = $t->usr1;
    $dest_wrd_lst->add_name(word::TN_INHABITANT);
    $dest_wrd_lst->add_name(word::TN_ONE);
    $dest_wrd_lst->load();
    $mio_val = new value;
    $mio_val->ids = $wrd_lst->ids;
    $mio_val->usr = $t->usr1;
    $mio_val->load();
    $result = $mio_val->scale($dest_wrd_lst);
    $target = value::TV_CH_INHABITANTS_2020_IN_MIO * 1000000;
    $t->dsp(', value->val_scaling for a word list ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test the figure object creation
    $wrd_lst = $t->load_word_list(array(word::TN_CANTON, word::TN_ZH, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $mio_val = new value_dsp;
    $mio_val->ids = $wrd_lst->ids;
    $mio_val->usr = $t->usr1;
    $mio_val->load();
    $fig = $mio_val->figure();
    $result = $fig->display_linked('1');
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id . '&back=1"  >1.55</a>';
    $diff = str_diff($result, $target);
    if ($diff != null) {
        if (in_array('view', $diff)) {
            if (in_array(0, $diff['view'])) {
                if ($diff['view'][0] == 0) {
                    $target = $result;
                }
            }
        }
    }
    $t->dsp(', value->figure->display_linked for word list ' . $wrd_lst->dsp_id() . '', $target, $result);

    // test the HTML code creation
    $result = $mio_val->display(0);
    $target = number_format(value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO, 2, DEFAULT_DEC_POINT, DEFAULT_THOUSAND_SEP);
    $t->dsp(', value->display', $target, $result);

    // test the HTML code creation including the hyperlink
    $result = $mio_val->display_linked('1');
    //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id . '&back=1"  >1.55</a>';
    $diff = str_diff($result, $target);
    if ($diff != null) {
        if (in_array('view', $diff)) {
            if (in_array(0, $diff['view'])) {
                if ($diff['view'][0] == 0) {
                    $target = $result;
                }
            }
        }
    }
    $t->dsp(', value->display_linked', $target, $result);

    // change the number to force using the thousand separator
    $mio_val->number = value::TEST_VALUE;
    $result = $mio_val->display_linked('1');
    //$target = '<a class="user_specific" href="/http/value_edit.php?id=2559&back=1">46\'000</a>';
    $target = '<a href="/http/value_edit.php?id=' . $mio_val->id . '&back=1"  >123\'456</a>';
    $diff = str_diff($result, $target);
    if ($diff != null) {
        if (in_array('view', $diff)) {
            if (in_array(0, $diff['view'])) {
                if ($diff['view'][0] == 0) {
                    $target = $result;
                }
            }
        }
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
    $wrd_lst = $t->load_word_list(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO, word::TN_2020));
    $phr_lst = $wrd_lst->phrase_lst();
    $add_val = new value;
    $add_val->ids = $phr_lst->ids;
    $add_val->number = value::TEST_BIG;
    $add_val->usr = $t->usr1;
    $result = $add_val->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val->number . ' for ' . $wrd_lst->name() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    $test_val_lst[] = $add_val->id;


    // ... check if the value adding has been logged
    if ($add_val->id > 0) {
        $log = new user_log;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $add_val->id;
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added 123456789';
    $t->dsp(', value->save logged for "' . $wrd_lst->name() . '"', $target, $result);

    // ... check if the value has been added
    $added_val = new value;
    $added_val->ids = $phr_lst->ids;
    $added_val->usr = $t->usr1;
    $added_val->load();
    $result = $added_val->number;
    $target = '123456789';
    $t->dsp(', value->load the value previous saved for "' . $wrd_lst->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // remember the added value id to be able to remove the test
    $added_val_id = $added_val->id;
    $test_val_lst[] = $added_val->id;

    // test if a value with the same phrases, but different time can be added
    $wrd_lst2 = $t->load_word_list(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO, word::TN_2019));
    $phr_lst2 = $wrd_lst2->phrase_lst();
    $add_val2 = new value;
    $add_val2->ids = $phr_lst2->ids;
    $add_val2->number = value::TEST_BIGGER;
    $add_val2->usr = $t->usr1;
    $result = $add_val2->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val2->number . ' for ' . $wrd_lst2->name() . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // test if a value with time stamp can be saved
    /*
    $phr_lst_ts = test_phrase_list(array(word::TN_RENAMED, word::TN_INHABITANT, word::TN_MIO));
    $add_val_ts = new value;
    $add_val_ts->ids = $phr_lst_ts->ids;
    $add_val_ts->number = TV_ABB_PRICE_20200515;
    $add_val_ts->time_stamp = new DateTime('2020-05-15');
    $add_val_ts->usr = $t->usr1;
    $result = $add_val_ts->save();
    $target = '';
    $t->dsp(', value->save ' . $add_val_ts->number . ' for ' . $phr_lst_ts->name() . ' and ' . $add_val_ts->time_stamp->format(DateTimeInterface::ATOM) . ' by user "' . $t->usr1->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    */

    // ... check if the value adding has been logged
    if ($add_val->id > 0) {
        $log = new user_log;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $add_val2->id;
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added 234567890';
    $t->dsp(', value->save logged for "' . $wrd_lst2->name() . '"', $target, $result);

    // ... check if the value has been added
    $added_val2 = new value;
    $added_val2->ids = $phr_lst2->ids;
    $added_val2->usr = $t->usr1;
    $added_val2->load();
    $result = $added_val2->number;
    $target = '234567890';
    $t->dsp(', value->load the value previous saved for "' . $phr_lst2->name() . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);
    // remember the added value id to be able to remove the test
    $test_val_lst[] = $added_val2->id;

    // check if the value can be changed
    $added_val = new value;
    $added_val->id = $added_val_id;
    $added_val->usr = $t->usr1;
    $added_val->load();
    $added_val->number = 987654321;
    $result = $added_val->save();
    $target = '';
    $t->dsp(', word->save update value id "' . $added_val_id . '" from  "' . $add_val->number . '" to "' . $added_val->number . '".', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change has been logged
    if ($added_val->id > 0) {
        $log = new user_log;
        $log->table = 'values';
        $log->field = 'word_value';
        $log->row_id = $added_val->id;
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test changed 123456789 to 987654321';
    $t->dsp(', value->save logged for "' . word::TN_RENAMED . '"', $target, $result);

    // ... check if the value has really been updated
    $added_val = new value;
    $added_val->ids = $phr_lst->ids;
    $added_val->usr = $t->usr1;
    $added_val->load();
    $result = $added_val->number;
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
    $val_usr2 = new value;
    //$val_usr2->ids = $phr_lst->ids;
    $val_usr2->id = $added_val_id;
    $val_usr2->usr = $t->usr2;
    $val_usr2->load();
    $val_usr2->number = 23456;
    $result = $val_usr2->save();
    $target = '';
    $t->dsp(', value->save ' . $val_usr2->number . ' for ' . $wrd_lst->name() . ' and user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change for the other user has been logged
    $val_usr2 = new value;
    $val_usr2->id = $added_val_id;
    $val_usr2->usr = $t->usr2;
    $val_usr2->load();
    if ($val_usr2->id > 0) {
        $log = new user_log;
        $log->table = 'user_values';
        $log->field = 'word_value';
        $log->row_id = $val_usr2->id;
        $log->usr = $t->usr2;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test partner changed 987654321 to 23456';
    $t->dsp(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value has really been updated
    $added_val_usr2 = new value;
    $added_val_usr2->ids = $phr_lst->ids;
    $added_val_usr2->usr = $t->usr2;
    $added_val_usr2->load();
    $result = $added_val_usr2->number;
    $target = '23456';
    $t->dsp(', value->load the value previous updated for "' . $wrd_lst->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value for the original user remains unchanged
    $added_val = new value;
    $added_val->ids = $phr_lst->ids;
    $added_val->usr = $t->usr1;
    $added_val->load();
    $result = $added_val->number;
    $target = '987654321';
    $t->dsp(', value->load for user "' . $t->usr1->name . '" is still', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if undo all specific changes removes the user value
    $added_val_usr2 = new value;
    $added_val_usr2->ids = $phr_lst->ids;
    $added_val_usr2->usr = $t->usr2;
    $added_val_usr2->load();
    $added_val_usr2->number = 987654321;
    $result = $added_val_usr2->save();
    $target = '';
    $t->dsp(', value->save change to ' . $val_usr2->number . ' for ' . $wrd_lst->name() . ' and user "' . $t->usr2->name . '" should undo the user change', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // ... check if the value change for the other user has been logged
    $val_usr2 = new value;
    $val_usr2->ids = $phr_lst->ids;
    $val_usr2->usr = $t->usr2;
    $val_usr2->load();
    if ($val_usr2->id > 0) {
        $log = new user_log;
        $log->table = 'user_values';
        $log->field = 'word_value';
        $log->row_id = $val_usr2->id;
        $log->usr = $t->usr2;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test partner changed 23456 to 987654321';
    $t->dsp(', value->save logged for user "' . $t->usr2->name . '"', $target, $result);

    // ... check if the value has really been changed back
    $added_val_usr2 = new value;
    $added_val_usr2->ids = $phr_lst->ids;
    $added_val_usr2->usr = $t->usr2;
    $added_val_usr2->load();
    $result = $added_val_usr2->number;
    $target = '987654321';
    $t->dsp(', value->load the value previous updated for "' . $wrd_lst->name() . '" by user "' . $t->usr2->name . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

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