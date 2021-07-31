<?php

/*

  test_word.php - TESTing of the word class
  -------------
  

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

function create_base_words()
{

    test_header('Check if all base words are correct');

    test_word(TW_ABB);
    test_word(TW_DAN);
    test_word(TW_NESN);
    test_word(TW_VESTAS);
    test_word(TW_ZH);
    test_word(TW_SALES);
    test_word(TW_SALES2);
    test_word(TW_PRICE);
    test_word(TW_SHARE);
    test_word(TW_CHF, word_type_list::DBL_MEASURE);
    test_word(TW_EUR, word_type_list::DBL_MEASURE);
    test_word(TW_YEAR);
    test_word(TW_2012, word_type_list::DBL_TIME);
    test_word(TW_2013, word_type_list::DBL_TIME);
    test_word(TW_2014, word_type_list::DBL_TIME);
    test_word(TW_2015, word_type_list::DBL_TIME);
    test_word(TW_2016, word_type_list::DBL_TIME);
    test_word(TW_2017, word_type_list::DBL_TIME);
    test_word(TW_2020, word_type_list::DBL_TIME);
    test_word(TW_BIL, word_type_list::DBL_SCALING);
    test_word(TW_MIO, word_type_list::DBL_SCALING);
    test_word(TW_K, word_type_list::DBL_SCALING);
    test_word(TW_M, word_type_list::DBL_SCALING);
    test_word(TW_PCT, word_type_list::DBL_SCALING_PCT);
    test_word(TW_CF);
    test_word(TW_TAX);
    test_word(TW_SECT_AUTO);
    test_word(TW_BALANCE);
    echo "<br><br>";
}

function run_word_test()
{

    global $usr1;
    global $usr2;

    test_header('Test the word class (classes/word.php)');

    // load the main test word
    $wrd_company = test_word(TEST_WORD);

    // check loading a word by name and id works by loading the word first by name and than by id, which should return the same name
    $wrd1 = new word;
    $wrd1->name = TEST_WORD;
    $wrd1->usr = $usr1;
    $wrd1->load();
    $wrd2 = new word;
    $wrd2->id = $wrd1->id;
    $wrd2->usr = $usr1;
    $wrd2->load();
    $target = TEST_WORD;
    $result = $wrd2->name;
    test_dsp('word->load of ' . $wrd_company->id . ' by id ' . $wrd1->id, $target, $result);

    // load by name
    $wrd_company = test_word(TEST_WORD);

    // main word from url
    $wrd = new word;
    $wrd->usr = $usr1;
    $wrd->main_wrd_from_txt($wrd_company->id . ',' . $wrd_company->id);
    $target = TEST_WORD;
    $result = $wrd1->name;
    test_dsp('word->main_wrd_from_txt', $target, $result);

    // display
    $back = 1;
    $target = '<a href="/http/view.php?words=' . $wrd_company->id . '&back=1">' . TEST_WORD . '</a>';
    $result = $wrd_company->display($back);
    test_dsp('word->display "' . TEST_WORD . '"', $target, $result);

    // word type
    $wrd_2013 = test_word(TW_2013);
    $target = True;
    $result = $wrd_2013->is_type(DBL_WORD_TYPE_TIME);
    test_dsp('word->is_type for ' . TW_2013 . ' and "' . DBL_WORD_TYPE_TIME . '"', $target, $result);

    // is time
    $target = True;
    $result = $wrd_2013->is_time();
    test_dsp('word->is_time for ' . TW_2013 . '', $target, $result);

    // is not measure
    $target = False;
    $result = $wrd_2013->is_measure();
    test_dsp('word->is_measure for ' . TW_2013 . '', $target, $result);

    // is measure
    $wrd_CHF = test_word(TW_CHF);
    $target = True;
    $result = $wrd_CHF->is_measure();
    test_dsp('word->is_measure for ' . TW_CHF . '', $target, $result);

    // is not scaling
    $target = False;
    $result = $wrd_CHF->is_scaling();
    test_dsp('word->is_scaling for ' . TW_CHF . '', $target, $result);

    // is scaling
    $wrd_mio = test_word(TW_MIO);
    $target = True;
    $result = $wrd_mio->is_scaling();
    test_dsp('word->is_scaling for ' . TW_MIO . '', $target, $result);

    // is not percent
    $target = False;
    $result = $wrd_mio->is_percent();
    test_dsp('word->is_percent for ' . TW_MIO . '', $target, $result);

    // is percent
    $wrd_pct = test_word(TW_PCT);
    $target = True;
    $result = $wrd_pct->is_percent();
    test_dsp('word->is_percent for ' . TW_PCT . '', $target, $result);

    // next word
    $wrd_2014 = test_word(TW_2014);
    $target = $wrd_2014->name;
    $wrd_next = $wrd_2013->next();
    $result = $wrd_next->name;
    test_dsp('word->next for ' . TW_2013 . '', $target, $result);

    // prior word
    $target = $wrd_2013->name;
    $wrd_prior = $wrd_2014->prior();
    $result = $wrd_prior->name;
    test_dsp('word->prior for ' . TW_2014 . '', $target, $result);

    // word children
    $wrd_company = test_word(TEST_WORD);
    $wrd_ABB = test_word(TW_ABB);
    $wrd_lst = $wrd_company->children();
    $target = $wrd_ABB->name;
    if ($wrd_lst->does_contain($wrd_ABB)) {
        $result = $wrd_ABB->name;
    } else {
        $result = '';
    }
    test_dsp('word->children for "' . TEST_WORD . '"', $target, $result, TIMEOUT_LIMIT_DB, 'out of ' . $wrd_lst->dsp_id() . '');

    // ... word children excluding the start word
    $target = '';
    if ($wrd_lst->does_contain($wrd_company)) {
        $result = $wrd_company->name;
    } else {
        $result = '';
    }
    test_dsp('word->children for "' . TEST_WORD . '" excluding the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // word are
    $wrd_lst = $wrd_company->are();
    $target = $wrd_ABB->name;
    if ($wrd_lst->does_contain($wrd_ABB)) {
        $result = $wrd_ABB->name;
    } else {
        $result = '';
    }
    test_dsp('word->are for "' . TEST_WORD . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // ... word are including the start word
    $target = $wrd_company->name;
    if ($wrd_lst->does_contain($wrd_company)) {
        $result = $wrd_company->name;
    } else {
        $result = '';
    }
    test_dsp('word->are for "' . TEST_WORD . '" including the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // word parents
    $wrd_ABB = test_word(TW_ABB);
    $wrd_company = test_word(TEST_WORD);
    $wrd_lst = $wrd_ABB->parents();
    $target = $wrd_company->name;
    if ($wrd_lst->does_contain($wrd_company)) {
        $result = $wrd_company->name;
    } else {
        $result = '';
    }
    test_dsp('word->parents for "' . TW_ABB . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // ... word parents excluding the start word
    $target = '';
    if ($wrd_lst->does_contain($wrd_ABB)) {
        $result = $wrd_ABB->name;
    } else {
        $result = '';
    }
    test_dsp('word->parents for "' . TW_ABB . '" excluding the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // word is
    $wrd_ZH = test_word(TW_ZH);
    $wrd_canton = test_word(TW_CANTON);
    $target = $wrd_canton->name;
    $wrd_lst = $wrd_ZH->is();
    if ($wrd_lst->does_contain($wrd_canton)) {
        $result = $wrd_canton->name;
    } else {
        $result = '';
    }
    test_dsp('word->is for "' . TW_ZH . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // ... word is including the start word
    $target = $wrd_ZH->name;
    // TODO check if not Zurich Insurance should be the result
    if ($wrd_lst->does_contain($wrd_company)) {
        $result = $wrd_ZH->name;
    } else {
        $result = '';
    }
    test_dsp('word->is for "' . TW_ZH . '" including the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // word is part
    $wrd_cf = test_word(TW_CF);
    $wrd_tax = test_word(TW_TAX);
    $target = $wrd_cf->name;
    $wrd_lst = $wrd_tax->is_part();
    if ($wrd_lst->does_contain($wrd_cf)) {
        $result = $wrd_cf->name;
    } else {
        $result = '';
    }
    test_dsp('word->is_part for "' . TW_TAX . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $wrd_lst->dsp_id() . '');

    // save a new word
    $wrd_new = new word;
    $wrd_new->name = TEST_WORD;
    $wrd_new->usr = $usr1;
    $result = num2bool($wrd_new->save());
    //$target = 'A word with the name "'.TEST_WORD.'" already exists. Please use another name.';
    $target = true;
    test_dsp('word->save for "' . TEST_WORD . '"', $target, $result, TIMEOUT_LIMIT_DB);

    // test the creation of a new word
    $wrd_add = new word;
    $wrd_add->name = TW_ADD;
    $wrd_add->usr = $usr1;
    $result = num2bool($wrd_add->save());
    $target = true;
    test_dsp('word->save for "' . TEST_WORD . '"', $target, $result, TIMEOUT_LIMIT_DB);

    echo "... and also testing the user log class (classes/user_log.php)<br>";

    // ... check if the word creation has been logged
    if ($wrd_add->id > 0) {
        $log = new user_log;
        $log->table = 'words';
        $log->field = 'word_name';
        $log->row_id = $wrd_add->id;
        $log->usr = $usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system batch job added ' . TW_ADD . '';
    test_dsp('word->save logged for "' . TW_ADD . '"', $target, $result);

    // ... test if the new word has been created
    $wrd_added = load_word(TW_ADD);
    $wrd_added->load();
    if ($wrd_added->id > 0) {
        $result = $wrd_added->name;
    }
    $target = TW_ADD;
    test_dsp('word->load of added word "' . TW_ADD . '"', $target, $result);

    // check if the word can be renamed
    $wrd_added->name = TW_ADD_RENAMED;
    $result = num2bool($wrd_added->save());
    $target = true;
    test_dsp('word->save rename "' . TW_ADD . '" to "' . TW_ADD_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB);

    // check if the word renaming was successful
    $wrd_renamed = new word;
    $wrd_renamed->name = TW_ADD_RENAMED;
    $wrd_renamed->usr = $usr1;
    if ($wrd_renamed->load()) {
        if ($wrd_renamed->id > 0) {
            $result = $wrd_renamed->name;
        }
    }
    $target = TW_ADD_RENAMED;
    test_dsp('word->load renamed word "' . TW_ADD_RENAMED . '"', $target, $result);

    // check if the word renaming has been logged
    $log = new user_log;
    $log->table = 'words';
    $log->field = 'word_name';
    $log->row_id = $wrd_renamed->id;
    $log->usr = $usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job changed ' . TW_ADD . ' to ' . TW_ADD_RENAMED . '';
    test_dsp('word->save rename logged for "' . TW_ADD_RENAMED . '"', $target, $result);

    // check if the word parameters can be added
    $wrd_renamed->plural = TW_ADD_RENAMED . 's';
    $wrd_renamed->description = TW_ADD_RENAMED . ' description';
    $wrd_renamed->type_id = cl(DBL_WORD_TYPE_OTHER);
    $result = num2bool($wrd_renamed->save());
    $target = true;
    test_dsp('word->save all word fields beside the name for "' . TW_ADD_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the word parameters have been added
    $wrd_reloaded = load_word(TW_ADD_RENAMED);
    $result = $wrd_reloaded->plural;
    $target = TW_ADD_RENAMED . 's';
    test_dsp('word->load plural for "' . TW_ADD_RENAMED . '"', $target, $result);
    $result = $wrd_reloaded->description;
    $target = TW_ADD_RENAMED . ' description';
    test_dsp('word->load description for "' . TW_ADD_RENAMED . '"', $target, $result);
    $result = $wrd_reloaded->type_id;
    $target = cl(DBL_WORD_TYPE_OTHER);
    test_dsp('word->load type_id for "' . TW_ADD_RENAMED . '"', $target, $result);

    // check if the word parameter adding have been logged
    $log = new user_log;
    $log->table = 'words';
    $log->field = 'plural';
    $log->row_id = $wrd_reloaded->id;
    $log->usr = $usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job added ' . TW_ADD_RENAMED . 's';
    test_dsp('word->load plural for "' . TW_ADD_RENAMED . '" logged', $target, $result);
    $log->field = 'description';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job added ' . TW_ADD_RENAMED . ' description';
    test_dsp('word->load description for "' . TW_ADD_RENAMED . '" logged', $target, $result);
    test_dsp('word->load ref_2 for "' . TW_ADD_RENAMED . '" logged', $target, $result);
    $log->field = 'word_type_id';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system batch job added differentiator filler';
    test_dsp('word->load type_id for "' . TW_ADD_RENAMED . '" logged', $target, $result);

    // check if a user specific word is created if another user changes the word
    $wrd_usr2 = new word;
    $wrd_usr2->name = TW_ADD_RENAMED;
    $wrd_usr2->usr = $usr2;
    $wrd_usr2->load();
    $wrd_usr2->plural = TW_ADD_RENAMED . 's2';
    $wrd_usr2->description = TW_ADD_RENAMED . ' description2';
    $wrd_usr2->type_id = cl(DBL_WORD_TYPE_TIME);
    $result = num2bool($wrd_usr2->save());
    $target = true;
    test_dsp('word->save all word fields for user 2 beside the name for "' . TW_ADD_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific word changes have been saved
    $wrd_usr2_reloaded = new word;
    $wrd_usr2_reloaded->name = TW_ADD_RENAMED;
    $wrd_usr2_reloaded->usr = $usr2;
    $wrd_usr2_reloaded->load();
    $result = $wrd_usr2_reloaded->plural;
    $target = TW_ADD_RENAMED . 's2';
    test_dsp('word->load plural for "' . TW_ADD_RENAMED . '"', $target, $result);
    $result = $wrd_usr2_reloaded->description;
    $target = TW_ADD_RENAMED . ' description2';
    test_dsp('word->load description for "' . TW_ADD_RENAMED . '"', $target, $result);
    $result = $wrd_usr2_reloaded->type_id;
    $target = cl(DBL_WORD_TYPE_TIME);
    test_dsp('word->load type_id for "' . TW_ADD_RENAMED . '"', $target, $result);

    // check the word for the original user remains unchanged
    $wrd_reloaded = load_word(TW_ADD_RENAMED);
    $result = $wrd_reloaded->plural;
    $target = TW_ADD_RENAMED . 's';
    test_dsp('word->load plural for "' . TW_ADD_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $wrd_reloaded->description;
    $target = TW_ADD_RENAMED . ' description';
    test_dsp('word->load description for "' . TW_ADD_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $wrd_reloaded->type_id;
    $target = cl(DBL_WORD_TYPE_OTHER);
    test_dsp('word->load type_id for "' . TW_ADD_RENAMED . '" unchanged for user 1', $target, $result);

    // check if undo all specific changes removes the user word
    $wrd_usr2 = new word;
    $wrd_usr2->name = TW_ADD_RENAMED;
    $wrd_usr2->usr = $usr2;
    $wrd_usr2->load();
    $wrd_usr2->plural = TW_ADD_RENAMED . 's';
    $wrd_usr2->description = TW_ADD_RENAMED . ' description';
    $wrd_usr2->type_id = cl(DBL_WORD_TYPE_OTHER);
    $result = num2bool($wrd_usr2->save());
    $target = true;
    test_dsp('word->save undo the user word fields beside the name for "' . TW_ADD_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific word changes have been saved
    $wrd_usr2_reloaded = new word;
    $wrd_usr2_reloaded->name = TW_ADD_RENAMED;
    $wrd_usr2_reloaded->usr = $usr2;
    $wrd_usr2_reloaded->load();
    $result = $wrd_usr2_reloaded->plural;
    $target = TW_ADD_RENAMED . 's';
    test_dsp('word->load plural for "' . TW_ADD_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $wrd_usr2_reloaded->description;
    $target = TW_ADD_RENAMED . ' description';
    test_dsp('word->load description for "' . TW_ADD_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $wrd_usr2_reloaded->type_id;
    $target = cl(DBL_WORD_TYPE_OTHER);
    test_dsp('word->load type_id for "' . TW_ADD_RENAMED . '" unchanged now also for user 2', $target, $result);

    // TODO redo the user specific word changes
    // check if the user specific changes can be removed with one click

    // check if the deletion request has been logged
    //$wrd = new word;

    // check if the deletion has been requested
    //$wrd = new word;

    // confirm the deletion requested
    //$wrd = new word;

    // check if the confirm of the deletion requested has been logged
    //$wrd = new word;

    // check if the word has been delete
    //$wrd = new word;

}
