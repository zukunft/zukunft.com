<?php

/*

    test/php/unit_write/word_tests.php - write test words to the database and check the results
    ----------------------------------
  

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

use api\formula\formula as formula_api;
use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\formula;
use cfg\log\change;
use cfg\log\change_field_list;
use cfg\log\change_table_list;
use cfg\phrase_type;
use cfg\sandbox_named;
use cfg\triple;
use cfg\verb;
use cfg\word;
use html\word\word as word_dsp;
use shared\library;
use test\all_tests;
use test\test_cleanup;

class word_tests
{

    function run(test_cleanup $t): void
    {
        global $phrase_types;

        // init
        $lib = new library();
        $t->name = 'word db write->';


        $t->header('word db write tests');

        $test_name = 'test saving word type ' . phrase_type::TIME . ' by adding add time word ' . word_api::TN_2021;
        $wrd_time = $t->test_word(word_api::TN_2021, phrase_type::TIME);
        $result = $wrd_time->is_type(phrase_type::TIME);
        $t->assert($test_name, $result, true);

        // is time
        $result = $wrd_time->is_time();
        $t->assert('word->is_time for ' . word_api::TN_2021, $result, true);

        // is not measure
        $result = $wrd_time->is_measure();
        $t->assert('word->is_measure for ' . word_api::TN_2021, $result, false);

        // is measure
        $wrd_measure = $t->test_word(word_api::TWN_CHF, phrase_type::MEASURE);
        $result = $wrd_measure->is_measure();
        $t->assert('word->is_measure for ' . word_api::TWN_CHF, $result, true);

        // is not scaling
        $result = $wrd_measure->is_scaling();
        $t->assert('word->is_scaling for ' . word_api::TWN_CHF, $result, false);

        // is scaling
        $wrd_scaling = $t->test_word(word_api::TN_MIO, phrase_type::SCALING);
        $result = $wrd_scaling->is_scaling();
        $t->assert('word->is_scaling for ' . word_api::TN_MIO, $result, true);

        // is not percent
        $result = $wrd_scaling->is_percent();
        $t->assert('word->is_percent for ' . word_api::TN_MIO, $result, false);

        // is percent
        $wrd_pct = $t->test_word(word_api::TN_PCT, phrase_type::PERCENT);
        $result = $wrd_pct->is_percent();
        $t->assert('word->is_percent for ' . word_api::TN_PCT, $result, true);

        // next word
        $wrd_time_next = $t->test_word(word_api::TN_2022, phrase_type::TIME);
        $t->test_triple(word_api::TN_2022, verb::FOLLOW, word_api::TN_2021);
        $target = $wrd_time_next->name();
        $wrd_next = $wrd_time->next();
        $result = $wrd_next->name();
        $t->assert('word->next for ' . word_api::TN_2021, $result, $target);

        $target = $wrd_time->name();
        $wrd_prior = $wrd_time_next->prior();
        $result = $wrd_prior->name();
        $t->assert('word->prior for ' . word_api::TN_2022, $result, $target);

        // load the main test words
        $wrd_read = $t->load_word(word_api::TN_READ);

        // create a parent test word
        $wrd_parent = $t->test_word(word_api::TN_PARENT);
        $wrd_parent->add_child($wrd_read);

        // word children, so get all children of a parent
        // e.g. Zurich is s children of Canton
        $phr_lst = $wrd_parent->children();
        $target = word_api::TN_READ;
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . word_api::TN_PARENT . '"', $result, $target,
            $t::TIMEOUT_LIMIT_DB, 'out of ' . $phr_lst->dsp_id());

        // ... word children excluding the start word, so the list of children should not include the parent
        // e.g. the list of Cantons does not include the word Canton itself
        $target = '';
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . word_api::TN_PARENT . '" excluding the start word', $result, $target,
            $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // TODO move read only tests like this to the db read or unit tests
        // word are, which includes all words related to the parent
        // e.g. which is for parent Canton the phrase "Zurich (Canton)", but not, as tested later, the phrase "Zurich (City)"
        //      "Cantons are Zurich, Bern, ... and valid is also everything related to the Word Canton itself"
        $phr_lst = $wrd_parent->are();
        $target = $wrd_read->name();
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->display('word->are for "' . word_api::TN_PARENT . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word are including the start word
        // e.g. to get also formulas related to Cantons all formulas related to "Zurich (Canton)" and the word "Canton" itself must be selected
        $target = $wrd_read->name();
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->display('word->are for "' . word_api::TN_PARENT . '" including the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // word parents
        $phr_lst = $wrd_read->parents();
        $target = $wrd_parent->name();
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_parent->name();
        } else {
            $result = '';
        }
        $t->display('word->parents for "' . word_api::TN_READ . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word parents excluding the start word
        $target = '';
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->display('word->parents for "' . word_api::TN_READ . '" excluding the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // create category test words for "Zurich is a Canton" and "Zurich is a City"
        // which implies that Canton contains Zurich and City contains Zurich
        // to avoid conflicts the test words actually used are 'System Test Word Category e.g. Canton' as category word
        // and 'System Test Word Member e.g. Zurich' as member
        $wrd_canton = $t->test_word(word_api::TN_CANTON);
        $wrd_city = $t->test_word(word_api::TN_CITY);
        $wrd_ZH = $t->test_word(word_api::TN_ZH);
        $t->test_triple(word_api::TN_ZH, verb::IS, word_api::TN_CANTON);
        $t->test_triple(word_api::TN_ZH, verb::IS, word_api::TN_CITY);

        // word is e.g. Zurich as a Canton ...
        $target = $wrd_canton->name();
        $phr_lst = $wrd_ZH->is();
        if ($phr_lst->does_contain($wrd_canton)) {
            $result = $wrd_canton->name();
        } else {
            $result = '';
        }
        $t->display('word->is "' . word_api::TN_ZH . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... and Zurich is a City
        $target = $wrd_city->name();
        $phr_lst = $wrd_ZH->is();
        if ($phr_lst->does_contain($wrd_city)) {
            $result = $wrd_city->name();
        } else {
            $result = '';
        }
        $t->display('word->and is "' . word_api::TN_ZH . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word is including the start word
        $target = $wrd_ZH->name();
        if ($phr_lst->does_contain($wrd_ZH)) {
            $result = $wrd_ZH->name();
        } else {
            $result = '';
        }
        $t->display('word->is for "' . word_api::TN_ZH . '" including the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // create the test words and relations for a parent child relation without inheritance
        // e.g. ...
        $wrd_cf = $t->test_word(word_api::TWN_CASH_FLOW);
        $wrd_tax = $t->test_word(word_api::TN_TAX_REPORT);
        $t->test_triple(word_api::TN_TAX_REPORT, verb::IS_PART_OF, word_api::TWN_CASH_FLOW);

        // create the test words and relations many mixed relations
        // e.g. a financial report
        $t->test_word(word_api::TN_FIN_REPORT);
        $t->test_triple(word_api::TWN_CASH_FLOW, verb::IS, word_api::TN_FIN_REPORT);

        // create the test words and relations for multi level contains
        // e.g. assets contain current assets which contains cash
        $t->test_word(word_api::TN_ASSETS);
        $t->test_word(word_api::TN_ASSETS_CURRENT);
        $t->test_word(word_api::TN_CASH);
        $t->test_triple(word_api::TN_CASH, verb::IS_PART_OF, word_api::TN_ASSETS_CURRENT);
        $t->test_triple(word_api::TN_ASSETS_CURRENT, verb::IS_PART_OF, word_api::TN_ASSETS);

        // create the test words and relations for differentiators
        // e.g. energy can be a sector
        $t->test_word(word_api::TN_SECTOR);
        $t->test_word(word_api::TN_ENERGY);
        $t->test_word(word_api::TN_WIND_ENERGY);
        $t->test_triple(word_api::TN_SECTOR, verb::CAN_CONTAIN, word_api::TN_ENERGY);
        $t->test_triple(word_api::TN_ENERGY, verb::CAN_CONTAIN, word_api::TN_WIND_ENERGY);

        // word is part
        $target = $wrd_cf->name();
        $phr_lst = $wrd_tax->is_part();
        if ($phr_lst->does_contain($wrd_cf)) {
            $result = $wrd_cf->name();
        } else {
            $result = '';
        }
        $t->display('word->is_part for "' . word_api::TN_TAX_REPORT . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        $test_name = 'check if saving a word with an existing name (' . word_api::TN_READ . ') creates a warning message for the user';
        $wrd_new = new word($t->usr1);
        $wrd_new->set_name(word_api::TN_READ);
        $result = $wrd_new->save();
        $target = 'A word with the name "'.word_api::TN_READ.'" already exists. Please use another word name.';
        $t->display($test_name, $target, $result, $t::TIMEOUT_LIMIT_DB);

        // test the creation of a new word
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(word_api::TN_ADD);
        $result = $wrd_add->save();
        $target = 'A word with the name "System Test Word" already exists. Please use another word name.';
        $t->display('word->save for "' . word_api::TN_ADD . '"', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // check that the word name cannot be used for a verb, triple or formula anymore
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(word_api::TN_ADD);
        $result = $vrb->save();
        $target = '<style class="text-danger">A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(verb::class) . ' name.</style>';
        $t->assert('verb cannot have an already used word name', $result, $target);

        // ... triple
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_api::TN_PI_NAME);
        $trp->set_name(word_api::TN_ADD);
        $result = $trp->save();
        $target = '<style class="text-danger">A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(triple::class) . ' name.</style>';
        $t->assert('triple cannot by renamed to an already used word name', $result, $target);

        // ... or formula anymore
        $frm = new formula($t->usr1);
        $frm->load_by_name(formula_api::TN_READ);
        $frm->set_name(word_api::TN_ADD);
        $result = $frm->save();
        $target = '<style class="text-danger">A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.</style>';
        $t->assert('formula cannot by renamed to an already used word name', $result, $target);


        $t->subheader('... and also testing the user log class (classes/user_log.php)');

        // ... check if the word creation has been logged
        if ($wrd_add->id() > 0) {
            $log = new change($t->usr1);
            $log->set_table(change_table_list::WORD);
            $log->set_field(change_field_list::FLD_WORD_NAME);
            $log->row_id = $wrd_add->id();
            $result = $log->dsp_last(true);
        }
        $target = 'zukunft.com system test added ' . word_api::TN_ADD;
        $t->display('word->save logged for "' . word_api::TN_ADD . '"', $target, $result);

        // ... test if the new word has been created
        $wrd_added = $t->load_word(word_api::TN_ADD);
        $wrd_added->load_by_name(word_api::TN_ADD);
        if ($wrd_added->id() > 0) {
            $result = $wrd_added->name();
        }
        $target = word_api::TN_ADD;
        $t->display('word->load of added word "' . word_api::TN_ADD . '"', $target, $result);

        // check if the word can be renamed
        $wrd_added->set_name(word_api::TN_RENAMED);
        $result = $wrd_added->save();
        $target = '';
        $t->display('word->save rename "' . word_api::TN_ADD . '" to "' . word_api::TN_RENAMED . '".', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // check if the word renaming was successful
        $wrd_renamed = new word($t->usr1);
        if ($wrd_renamed->load_by_name(word_api::TN_RENAMED)) {
            if ($wrd_renamed->id() > 0) {
                $result = $wrd_renamed->name();
            }
        }
        $target = word_api::TN_RENAMED;
        $t->display('word->load renamed word "' . word_api::TN_RENAMED . '"', $target, $result);

        // check if the word renaming has been logged
        $log = new change($t->usr1);
        $log->set_table(change_table_list::WORD);
        $log->set_field(change_field_list::FLD_WORD_NAME);
        $log->row_id = $wrd_renamed->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test changed ' . word_api::TN_ADD . ' to ' . word_api::TN_RENAMED;
        $t->display('word->save rename logged for "' . word_api::TN_RENAMED . '"', $target, $result);

        // check if the word parameters can be added
        $wrd_renamed->plural = word_api::TN_RENAMED . 's';
        $wrd_renamed->description = word_api::TN_RENAMED . ' description';
        $wrd_renamed->type_id = $phrase_types->id(phrase_type::OTHER);
        $result = $wrd_renamed->save();
        $target = '';
        $t->display('word->save all word fields beside the name for "' . word_api::TN_RENAMED . '"',
            $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the word parameters have been added
        $wrd_reloaded = $t->load_word(word_api::TN_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = word_api::TN_RENAMED . 's';
        $t->display('word->load plural for "' . word_api::TN_RENAMED . '"', $target, $result);
        $result = $wrd_reloaded->description;
        $target = word_api::TN_RENAMED . ' description';
        $t->display('word->load description for "' . word_api::TN_RENAMED . '"', $target, $result);
        $result = $wrd_reloaded->type_id;
        $target = $phrase_types->id(phrase_type::OTHER);
        $t->display('word->load type_id for "' . word_api::TN_RENAMED . '"', $target, $result);

        // check if the word parameter adding have been logged
        $log = new change($t->usr1);
        $log->set_table(change_table_list::WORD);
        $log->set_field(change_field_list::FLD_WORD_PLURAL);
        $log->row_id = $wrd_reloaded->id();
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test added ' . word_api::TN_RENAMED . 's';
        $t->display('word->load plural for "' . word_api::TN_RENAMED . '" logged', $target, $result);
        $log->set_field(sandbox_named::FLD_DESCRIPTION);
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test added ' . word_api::TN_RENAMED . ' description';
        $t->display('word->load description for "' . word_api::TN_RENAMED . '" logged', $target, $result);
        $t->display('word->load ref_2 for "' . word_api::TN_RENAMED . '" logged', $target, $result);
        $log->set_field(change_field_list::FLD_PHRASE_TYPE);
        $result = $log->dsp_last(true);
        $target = 'zukunft.com system test added differentiator filler';
        $t->display('word->load type_id for "' . word_api::TN_RENAMED . '" logged', $target, $result);

        // check if a user specific word is created if another user changes the word
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(word_api::TN_RENAMED);
        $wrd_usr2->plural = word_api::TN_RENAMED . 's2';
        $wrd_usr2->description = word_api::TN_RENAMED . ' description2';
        $wrd_usr2->type_id = $phrase_types->id(phrase_type::TIME);
        $result = $wrd_usr2->save();
        $target = '';
        $t->display('word->save all word fields for user 2 beside the name for "' . word_api::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(word_api::TN_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = word_api::TN_RENAMED . 's2';
        $t->display('word->load plural for "' . word_api::TN_RENAMED . '"', $target, $result);
        $result = $wrd_usr2_reloaded->description;
        $target = word_api::TN_RENAMED . ' description2';
        $t->display('word->load description for "' . word_api::TN_RENAMED . '"', $target, $result);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $phrase_types->id(phrase_type::TIME);
        $t->display('word->load type_id for "' . word_api::TN_RENAMED . '"', $target, $result);

        // check the word for the original user remains unchanged
        $wrd_reloaded = $t->load_word(word_api::TN_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = word_api::TN_RENAMED . 's';
        $t->display('word->load plural for "' . word_api::TN_RENAMED . '" unchanged for user 1', $target, $result);
        $result = $wrd_reloaded->description;
        $target = word_api::TN_RENAMED . ' description';
        $t->display('word->load description for "' . word_api::TN_RENAMED . '" unchanged for user 1', $target, $result);
        $result = $wrd_reloaded->type_id;
        $target = $phrase_types->id(phrase_type::OTHER);
        $t->display('word->load type_id for "' . word_api::TN_RENAMED . '" unchanged for user 1', $target, $result);

        // TODO check that the changed word name cannot be used for a verb, triple or formula anymore

        // check if undo all specific changes removes the user word
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(word_api::TN_RENAMED);
        $wrd_usr2->plural = word_api::TN_RENAMED . 's';
        $wrd_usr2->description = word_api::TN_RENAMED . ' description';
        $wrd_usr2->type_id = $phrase_types->id(phrase_type::OTHER);
        $result = $wrd_usr2->save();
        $target = '';
        $t->display('word->save undo the user word fields beside the name for "' . word_api::TN_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(word_api::TN_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = word_api::TN_RENAMED . 's';
        $t->display('word->load plural for "' . word_api::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
        $result = $wrd_usr2_reloaded->description;
        $target = word_api::TN_RENAMED . ' description';
        $t->display('word->load description for "' . word_api::TN_RENAMED . '" unchanged now also for user 2', $target, $result);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $phrase_types->id(phrase_type::OTHER);
        $t->display('word->load type_id for "' . word_api::TN_RENAMED . '" unchanged now also for user 2', $target, $result);

        // display
        $back = 1;
        $target = '<a href="/http/view.php?words=' . $wrd_read->id() . '&back=1" title="' . word_api::TD_READ . '">' . word_api::TN_READ . '</a>';
        $wrd_read_dsp = new word_dsp($wrd_read->api_json());
        $result = $wrd_read_dsp->display_linked($back);
        $t->display('word->display "' . word_api::TN_READ . '"', $target, $result);

        // check if user 2 can exclude a word without influencing user 1
        $wrd_usr1 = $t->load_word(word_api::TN_RENAMED, $t->usr1);
        $wrd_usr2 = $t->load_word(word_api::TN_RENAMED, $t->usr2);
        $wrd_usr2->del();
        $wrd_usr2_reloaded = $t->load_word(word_api::TN_RENAMED, $t->usr2);
        $target = '';
        $result = $wrd_usr2_reloaded->name_dsp();
        $t->display('user 2 has deleted word "' . word_api::TN_RENAMED . '"', $target, $result);
        $wrd_usr1_reloaded = $t->load_word(word_api::TN_RENAMED, $t->usr1);
        $target = $wrd_usr1->name_dsp();
        $result = $wrd_usr1_reloaded->name_dsp();
        $t->display('but the word "' . word_api::TN_RENAMED . '" is still the same for user 1', $target, $result);

        // TODO test the creation of a new scaling word e.g. dozen for 12
        //      and adding a related formula and calculating values based on the added formula
        // TODO test the creation of a new time word e.g. year 2042

        // TODO redo the user specific word changes including changing the default view
        // check if the user specific changes can be removed with one click

        // check if the deletion request has been logged
        //$wrd = new word($t->usr1);

        // check if the deletion has been requested
        //$wrd = new word($t->usr1);

        // confirm the deletion requested
        //$wrd = new word($t->usr1);

        // check if the confirmation of the deletion requested has been logged
        //$wrd = new word($t->usr1);

        // check if the word has been deleted
        //$wrd = new word($t->usr1);

        // review and check if still needed
        // main word from url
        /*
        $wrd = new word($t->usr1);
        $wrd->usr = $t->usr1;
        $wrd->main_wrd_from_txt($wrd_read->id() . ',' . $wrd_read->id);
        $target = word::TEST_NAME_READ;
        $result = $wrd_by_name->name();
        $t->display('word->main_wrd_from_txt', $target, $result);
        */
    }

    function create_test_words(all_tests $t): void
    {

        $t->header('Check if all base words are correct');

        foreach (word_api::TEST_WORDS_CREATE as $word_name) {
            $t->test_word($word_name);
        }
        foreach (word_api::TEST_WORDS_MEASURE as $word_name) {
            $t->test_word($word_name, phrase_type::MEASURE);
        }
        foreach (word_api::TEST_WORDS_SCALING as $word_name) {
            $t->test_word($word_name, phrase_type::SCALING);
        }
        foreach (word_api::TEST_WORDS_SCALING_HIDDEN as $word_name) {
            $t->test_word($word_name, phrase_type::SCALING_HIDDEN);
        }
        foreach (word_api::TEST_WORDS_PERCENT as $word_name) {
            $t->test_word($word_name, phrase_type::PERCENT);
        }
        $prev_word_name = null;
        foreach (word_api::TEST_WORDS_TIME_YEAR as $word_name) {
            $t->test_triple($word_name, verb::IS, word_api::TN_YEAR);
            $t->test_word($word_name, phrase_type::TIME);
            if ($prev_word_name != null) {
                $t->test_triple($word_name, verb::FOLLOW, $prev_word_name);
            }
            $prev_word_name = $word_name;
        }

    }
}
