<?php

/*

  test_word.php - TESTing of the word class
  -------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function create_test_words(testing $t)
{

    $t->header('Check if all base words are correct');

    foreach (word::TEST_WORDS_STANDARD as $word_name) {
        $t->test_word($word_name);
    }
    foreach (word::TEST_WORDS_MEASURE as $word_name) {
        $t->test_word($word_name, word_type_list::DBL_MEASURE);
    }
    foreach (word::TEST_WORDS_SCALING as $word_name) {
        $t->test_word($word_name, word_type_list::DBL_SCALING);
    }
    foreach (word::TEST_WORDS_SCALING_HIDDEN as $word_name) {
        $t->test_word($word_name, word_type_list::DBL_SCALING_HIDDEN);
    }
    foreach (word::TEST_WORDS_PERCENT as $word_name) {
        $t->test_word($word_name, word_type_list::DBL_PERCENT);
    }
    $prev_word_name = null;
    foreach (word::TEST_WORDS_TIME as $word_name) {
        $t->test_word_link($word_name, verb::IS_A, word::TN_YEAR);
        $t->test_word($word_name, word_type_list::DBL_TIME);
        if ($prev_word_name != null) {
            $t->test_word_link($word_name, verb::DBL_FOLLOW, $prev_word_name);
        }
        $prev_word_name = $word_name;
    }

}

function run_word_tests(testing $t)
{

    $t->header('Test the word class (classes/word.php)');

    // load the main test words
    $wrd_read = $t->add_word(word::TN_READ);

    // check if loading a word by name and id works
    $wrd_by_name = $t->add_word(word::TN_READ, null, $t->usr1);
    $wrd_by_id = new word;
    $wrd_by_id->id = $wrd_by_name->id;
    $wrd_by_id->usr = $t->usr1;
    $wrd_by_id->load();
    $target = word::TN_READ;
    $result = $wrd_by_id->name;
    $t->dsp('word->load of ' . $wrd_read->id . ' by id ' . $wrd_by_name->id, $target, $result);

    // word type
    $wrd_time = $t->test_word(word::TN_2021, word_type_list::DBL_TIME);
    $target = True;
    $result = $wrd_time->is_type(word_type_list::DBL_TIME);
    $t->dsp('word->is_type for ' . word::TN_2021 . ' and "' . word_type_list::DBL_TIME . '"', $target, $result);

    // is time
    $target = True;
    $result = $wrd_time->is_time();
    $t->dsp('word->is_time for ' . word::TN_2021 . '', $target, $result);

    // is not measure
    $target = False;
    $result = $wrd_time->is_measure();
    $t->dsp('word->is_measure for ' . word::TN_2021 . '', $target, $result);

    // is measure
    $wrd_measure = $t->test_word(word::TN_CHF, word_type_list::DBL_MEASURE);
    $target = True;
    $result = $wrd_measure->is_measure();
    $t->dsp('word->is_measure for ' . word::TN_CHF . '', $target, $result);

    // is not scaling
    $target = False;
    $result = $wrd_measure->is_scaling();
    $t->dsp('word->is_scaling for ' . word::TN_CHF . '', $target, $result);

    // is scaling
    $wrd_scaling = $t->test_word(word::TN_MIO, word_type_list::DBL_SCALING);
    $target = True;
    $result = $wrd_scaling->is_scaling();
    $t->dsp('word->is_scaling for ' . word::TN_MIO . '', $target, $result);

    // is not percent
    $target = False;
    $result = $wrd_scaling->is_percent();
    $t->dsp('word->is_percent for ' . word::TN_MIO . '', $target, $result);

    // is percent
    $wrd_pct = $t->test_word(word::TN_PCT, word_type_list::DBL_PERCENT);
    $target = True;
    $result = $wrd_pct->is_percent();
    $t->dsp('word->is_percent for ' . word::TN_PCT . '', $target, $result);

    // next word
    $wrd_time_next = $t->test_word(word::TN_2022, word_type_list::DBL_TIME);
    $t->test_word_link(word::TN_2022, verb::DBL_FOLLOW, word::TN_2021);
    $target = $wrd_time_next->name;
    $wrd_next = $wrd_time->next();
    $result = $wrd_next->name;
    $t->dsp('word->next for ' . word::TN_2021 . '', $target, $result);

    $target = $wrd_time->name;
    $wrd_prior = $wrd_time_next->prior();
    $result = $wrd_prior->name;
    $t->dsp('word->prior for ' . word::TN_2022 . '', $target, $result);

    // create a parent test word
    $wrd_parent = $t->test_word(word::TN_PARENT);
    $wrd_parent->add_child($wrd_read);

    // word children, so get all children of a parent
    // e.g. Zurich is s children of Canton
    $phr_lst = $wrd_parent->children();
    $target = word::TN_READ;
    if ($phr_lst->does_contain($wrd_read)) {
        $result = $wrd_read->name();
    } else {
        $result = '';
    }
    $t->dsp('word->children for "' . word::TN_PARENT . '"', $target, $result, TIMEOUT_LIMIT_DB, 'out of ' . $phr_lst->dsp_id() . '');

    // ... word children excluding the start word, so the list of children should not include the parent
    // e.g. the list of Cantons does not include the word Canton itself
    $target = '';
    if ($phr_lst->does_contain($wrd_parent)) {
        $result = $wrd_read->name();
    } else {
        $result = '';
    }
    $t->dsp('word->children for "' . word::TN_PARENT . '" excluding the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // word are, which includes all words related to the parent
    // e.g. which is for parent Canton the phrase "Zurich (Canton)", but not, as tested later, the phrase "Zurich (City)"
    //      "Cantons are Zurich, Bern, ... and valid is also everything related to the Word Canton itself"
    $phr_lst = $wrd_parent->are();
    $target = $wrd_read->name;
    if ($phr_lst->does_contain($wrd_parent)) {
        $result = $wrd_read->name;
    } else {
        $result = '';
    }
    $t->dsp('word->are for "' . word::TN_PARENT . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // ... word are including the start word
    // e.g. to get also formulas related to Cantons all formulas related to "Zurich (Canton)" and the word "Canton" itself must be selected
    $target = $wrd_read->name;
    if ($phr_lst->does_contain($wrd_read)) {
        $result = $wrd_read->name;
    } else {
        $result = '';
    }
    $t->dsp('word->are for "' . word::TN_PARENT . '" including the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // word parents
    $phr_lst = $wrd_read->parents();
    $target = $wrd_parent->name;
    if ($phr_lst->does_contain($wrd_parent)) {
        $result = $wrd_parent->name;
    } else {
        $result = '';
    }
    $t->dsp('word->parents for "' . word::TN_READ . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // ... word parents excluding the start word
    $target = '';
    if ($phr_lst->does_contain($wrd_read)) {
        $result = $wrd_read->name;
    } else {
        $result = '';
    }
    $t->dsp('word->parents for "' . word::TN_READ . '" excluding the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // create category test words for "Zurich is a Canton" and "Zurich is a City"
    // which implies that Canton contains Zurich and City contains Zurich
    // to avoid conflicts the test words actually used are 'System Test Word Category e.g. Canton' as category word
    // and 'System Test Word Member e.g. Zurich' as member
    $wrd_canton = $t->test_word(word::TN_CANTON);
    $wrd_city = $t->test_word(word::TN_CITY);
    $wrd_ZH = $t->test_word(word::TN_ZH);
    $t->test_word_link(word::TN_ZH, verb::IS_A, word::TN_CANTON);
    $t->test_word_link(word::TN_ZH, verb::IS_A, word::TN_CITY);

    // word is e.g. Zurich as a Canton ...
    $target = $wrd_canton->name;
    $phr_lst = $wrd_ZH->is();
    if ($phr_lst->does_contain($wrd_canton)) {
        $result = $wrd_canton->name;
    } else {
        $result = '';
    }
    $t->dsp('word->is "' . word::TN_ZH . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // ... and Zurich is a City
    $target = $wrd_city->name;
    $phr_lst = $wrd_ZH->is();
    if ($phr_lst->does_contain($wrd_city)) {
        $result = $wrd_city->name;
    } else {
        $result = '';
    }
    $t->dsp('word->and is "' . word::TN_ZH . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // ... word is including the start word
    $target = $wrd_ZH->name;
    if ($phr_lst->does_contain($wrd_ZH)) {
        $result = $wrd_ZH->name;
    } else {
        $result = '';
    }
    $t->dsp('word->is for "' . word::TN_ZH . '" including the start word', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // create the test words and relations for a parent child relation without inheritance
    // e.g. ...
    $wrd_cf = $t->test_word(word::TN_PARENT_NON_INHERITANCE);
    $wrd_tax = $t->test_word(word::TN_CHILD_NON_INHERITANCE);
    $t->test_word_link(word::TN_CHILD_NON_INHERITANCE, verb::IS_PART_OF, word::TN_PARENT_NON_INHERITANCE);

    // word is part
    $target = $wrd_cf->name;
    $phr_lst = $wrd_tax->is_part();
    if ($phr_lst->does_contain($wrd_cf)) {
        $result = $wrd_cf->name;
    } else {
        $result = '';
    }
    $t->dsp('word->is_part for "' . word::TN_CHILD_NON_INHERITANCE . '"', $target, $result, TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id() . '');

    // save a new word
    $wrd_new = new word;
    $wrd_new->name = word::TN_READ;
    $wrd_new->usr = $t->usr1;
    $result = $wrd_new->save();
    //$target = 'A word with the name "'.word::TEST_NAME_READ.'" already exists. Please use another name.';
    $target = '';
    $t->dsp('word->save for "' . word::TN_READ . '"', $target, $result, TIMEOUT_LIMIT_DB);

    // test the creation of a new word
    $wrd_add = new word;
    $wrd_add->name = word::TN_ADD;
    $wrd_add->usr = $t->usr1;
    $result = $wrd_add->save();
    $target = '';
    $t->dsp('word->save for "' . word::TN_ADD . '"', $target, $result, TIMEOUT_LIMIT_DB);

    echo "... and also testing the user log class (classes/user_log.php)<br>";

    // ... check if the word creation has been logged
    if ($wrd_add->id > 0) {
        $log = new user_log_named;
        $log->table = 'words';
        $log->field = 'word_name';
        $log->row_id = $wrd_add->id;
        $log->usr = $t->usr1;
        $result = $log->dsp_last(true);
    }
    $target = 'zukunft.com system test added ' . word::TN_ADD . '';
    $t->dsp('word->save logged for "' . word::TN_ADD . '"', $target, $result);

    // ... test if the new word has been created
    $wrd_added = $t->load_word(word::TN_ADD);
    $wrd_added->load();
    if ($wrd_added->id > 0) {
        $result = $wrd_added->name;
    }
    $target = word::TN_ADD;
    $t->dsp('word->load of added word "' . word::TN_ADD . '"', $target, $result);

    // check if the word can be renamed
    $wrd_added->name = word::TN_RENAMED;
    $result = $wrd_added->save();
    $target = '';
    $t->dsp('word->save rename "' . word::TN_ADD . '" to "' . word::TN_RENAMED . '".', $target, $result, TIMEOUT_LIMIT_DB);

    // check if the word renaming was successful
    $wrd_renamed = new word;
    $wrd_renamed->name = word::TN_RENAMED;
    $wrd_renamed->usr = $t->usr1;
    if ($wrd_renamed->load()) {
        if ($wrd_renamed->id > 0) {
            $result = $wrd_renamed->name;
        }
    }
    $target = word::TN_RENAMED;
    $t->dsp('word->load renamed word "' . word::TN_RENAMED . '"', $target, $result);

    // check if the word renaming has been logged
    $log = new user_log_named;
    $log->table = 'words';
    $log->field = 'word_name';
    $log->row_id = $wrd_renamed->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test changed ' . word::TN_ADD . ' to ' . word::TN_RENAMED . '';
    $t->dsp('word->save rename logged for "' . word::TN_RENAMED . '"', $target, $result);

    // check if the word parameters can be added
    $wrd_renamed->plural = word::TN_RENAMED . 's';
    $wrd_renamed->description = word::TN_RENAMED . ' description';
    $wrd_renamed->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_OTHER);
    $result = $wrd_renamed->save();
    $target = '';
    $t->dsp('word->save all word fields beside the name for "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if the word parameters have been added
    $wrd_reloaded = $t->load_word(word::TN_RENAMED);
    $result = $wrd_reloaded->plural;
    $target = word::TN_RENAMED . 's';
    $t->dsp('word->load plural for "' . word::TN_RENAMED . '"', $target, $result);
    $result = $wrd_reloaded->description;
    $target = word::TN_RENAMED . ' description';
    $t->dsp('word->load description for "' . word::TN_RENAMED . '"', $target, $result);
    $result = $wrd_reloaded->type_id;
    $target = cl(db_cl::WORD_TYPE, word_type_list::DBL_OTHER);
    $t->dsp('word->load type_id for "' . word::TN_RENAMED . '"', $target, $result);

    // check if the word parameter adding have been logged
    $log = new user_log_named;
    $log->table = 'words';
    $log->field = 'plural';
    $log->row_id = $wrd_reloaded->id;
    $log->usr = $t->usr1;
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added ' . word::TN_RENAMED . 's';
    $t->dsp('word->load plural for "' . word::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'description';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added ' . word::TN_RENAMED . ' description';
    $t->dsp('word->load description for "' . word::TN_RENAMED . '" logged', $target, $result);
    $t->dsp('word->load ref_2 for "' . word::TN_RENAMED . '" logged', $target, $result);
    $log->field = 'word_type_id';
    $result = $log->dsp_last(true);
    $target = 'zukunft.com system test added differentiator filler';
    $t->dsp('word->load type_id for "' . word::TN_RENAMED . '" logged', $target, $result);

    // check if a user specific word is created if another user changes the word
    $wrd_usr2 = new word;
    $wrd_usr2->name = word::TN_RENAMED;
    $wrd_usr2->usr = $t->usr2;
    $wrd_usr2->load();
    $wrd_usr2->plural = word::TN_RENAMED . 's2';
    $wrd_usr2->description = word::TN_RENAMED . ' description2';
    $wrd_usr2->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
    $result = $wrd_usr2->save();
    $target = '';
    $t->dsp('word->save all word fields for user 2 beside the name for "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific word changes have been saved
    $wrd_usr2_reloaded = new word;
    $wrd_usr2_reloaded->name = word::TN_RENAMED;
    $wrd_usr2_reloaded->usr = $t->usr2;
    $wrd_usr2_reloaded->load();
    $result = $wrd_usr2_reloaded->plural;
    $target = word::TN_RENAMED . 's2';
    $t->dsp('word->load plural for "' . word::TN_RENAMED . '"', $target, $result);
    $result = $wrd_usr2_reloaded->description;
    $target = word::TN_RENAMED . ' description2';
    $t->dsp('word->load description for "' . word::TN_RENAMED . '"', $target, $result);
    $result = $wrd_usr2_reloaded->type_id;
    $target = cl(db_cl::WORD_TYPE, word_type_list::DBL_TIME);
    $t->dsp('word->load type_id for "' . word::TN_RENAMED . '"', $target, $result);

    // check the word for the original user remains unchanged
    $wrd_reloaded = $t->load_word(word::TN_RENAMED);
    $result = $wrd_reloaded->plural;
    $target = word::TN_RENAMED . 's';
    $t->dsp('word->load plural for "' . word::TN_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $wrd_reloaded->description;
    $target = word::TN_RENAMED . ' description';
    $t->dsp('word->load description for "' . word::TN_RENAMED . '" unchanged for user 1', $target, $result);
    $result = $wrd_reloaded->type_id;
    $target = cl(db_cl::WORD_TYPE, word_type_list::DBL_OTHER);
    $t->dsp('word->load type_id for "' . word::TN_RENAMED . '" unchanged for user 1', $target, $result);

    // check if undo all specific changes removes the user word
    $wrd_usr2 = new word;
    $wrd_usr2->name = word::TN_RENAMED;
    $wrd_usr2->usr = $t->usr2;
    $wrd_usr2->load();
    $wrd_usr2->plural = word::TN_RENAMED . 's';
    $wrd_usr2->description = word::TN_RENAMED . ' description';
    $wrd_usr2->type_id = cl(db_cl::WORD_TYPE, word_type_list::DBL_OTHER);
    $result = $wrd_usr2->save();
    $target = '';
    $t->dsp('word->save undo the user word fields beside the name for "' . word::TN_RENAMED . '"', $target, $result, TIMEOUT_LIMIT_DB_MULTI);

    // check if a user specific word changes have been saved
    $wrd_usr2_reloaded = new word;
    $wrd_usr2_reloaded->name = word::TN_RENAMED;
    $wrd_usr2_reloaded->usr = $t->usr2;
    $wrd_usr2_reloaded->load();
    $result = $wrd_usr2_reloaded->plural;
    $target = word::TN_RENAMED . 's';
    $t->dsp('word->load plural for "' . word::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $wrd_usr2_reloaded->description;
    $target = word::TN_RENAMED . ' description';
    $t->dsp('word->load description for "' . word::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
    $result = $wrd_usr2_reloaded->type_id;
    $target = cl(db_cl::WORD_TYPE, word_type_list::DBL_OTHER);
    $t->dsp('word->load type_id for "' . word::TN_RENAMED . '" unchanged now also for user 2', $target, $result);

    // display
    $back = 1;
    $target = '<a href="/http/view.php?words=' . $wrd_read->id . '&back=1">' . word::TN_READ . '</a>';
    $result = $wrd_read->display($back);
    $t->dsp('word->display "' . word::TN_READ . '"', $target, $result);

    // check if user 2 can exclude a word without influencing user 1
    $wrd_usr1 = $t->load_word(word::TN_RENAMED, $t->usr1);
    $wrd_usr2 = $t->load_word(word::TN_RENAMED, $t->usr2);
    $wrd_usr2->del();
    $wrd_usr2_reloaded = $t->load_word(word::TN_RENAMED, $t->usr2);
    $target = '';
    $result = $wrd_usr2_reloaded->name();
    $t->dsp('user 2 has deleted word "' . word::TN_RENAMED . '"', $target, $result);
    $wrd_usr1_reloaded = $t->load_word(word::TN_RENAMED, $t->usr1);
    $target = $wrd_usr1->name();
    $result = $wrd_usr1_reloaded->name();
    $t->dsp('but the word "' . word::TN_RENAMED . '" is still the same for user 1', $target, $result);


    // TODO redo the user specific word changes including changing the default view
    // check if the user specific changes can be removed with one click

    // check if the deletion request has been logged
    //$wrd = new word;

    // check if the deletion has been requested
    //$wrd = new word;

    // confirm the deletion requested
    //$wrd = new word;

    // check if the confirmation of the deletion requested has been logged
    //$wrd = new word;

    // check if the word has been deleted
    //$wrd = new word;

    // review and check if still needed
    // main word from url
    /*
    $wrd = new word;
    $wrd->usr = $t->usr1;
    $wrd->main_wrd_from_txt($wrd_read->id . ',' . $wrd_read->id);
    $target = word::TEST_NAME_READ;
    $result = $wrd_by_name->name;
    $t->dsp('word->main_wrd_from_txt', $target, $result);
    */


}
