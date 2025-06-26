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

include_once SHARED_CONST_PATH . 'words.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_ENUM_PATH . 'change_fields.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';

use cfg\formula\formula;
use cfg\log\change;
use cfg\log\change_field_list;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\word\word;
use html\word\word as word_dsp;
use shared\const\users;
use shared\enum\change_fields;
use shared\enum\change_tables;
use shared\library;
use shared\const\formulas;
use shared\const\triples;
use shared\const\views;
use shared\const\words;
use shared\types\phrase_type as phrase_type_shared;
use shared\types\verbs;
use test\all_tests;
use test\test_cleanup;

class word_write_tests
{

    function run(test_cleanup $t): void
    {
        global $phr_typ_cac;

        // init
        $lib = new library();
        $t->name = 'word db write->';


        $t->header('word db write tests');

        $t->subheader('word prepared write');
        $test_name = 'add word ' . words::TEST_ADD_VIA_SQL . ' via sql insert';
        $t->assert_write_via_func_or_sql($test_name, $t->word_add_by_sql(), false);
        $test_name = 'add word ' . words::TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t->word_add_by_func(), true);

        $t->subheader('word write sandbox tests for ' . words::TEST_ADD);
        $t->assert_write_named($t->word_filled_add(), words::TEST_ADD);

        $test_name = 'test saving word type ' . phrase_type_shared::TIME . ' by adding add time word ' . words::TEST_2021;
        $wrd_time = $t->test_word(words::TEST_2021, phrase_type_shared::TIME);
        $result = $wrd_time->is_type(phrase_type_shared::TIME);
        $t->assert($test_name, $result, true);

        // is time
        $result = $wrd_time->is_time();
        $t->assert('word->is_time for ' . words::TEST_2021, $result, true);

        // is not measure
        $result = $wrd_time->is_measure();
        $t->assert('word->is_measure for ' . words::TEST_2021, $result, false);

        // is measure
        $wrd_measure = $t->test_word(words::TEST_CHF, phrase_type_shared::MEASURE);
        $result = $wrd_measure->is_measure();
        $t->assert('word->is_measure for ' . words::TEST_CHF, $result, true);

        // is not scaling
        $result = $wrd_measure->is_scaling();
        $t->assert('word->is_scaling for ' . words::TEST_CHF, $result, false);

        // is scaling
        $wrd_scaling = $t->test_word(words::MIO, phrase_type_shared::SCALING);
        $result = $wrd_scaling->is_scaling();
        $t->assert('word->is_scaling for ' . words::MIO, $result, true);

        // is not percent
        $result = $wrd_scaling->is_percent();
        $t->assert('word->is_percent for ' . words::MIO, $result, false);

        // is percent
        $wrd_pct = $t->test_word(words::PCT, phrase_type_shared::PERCENT);
        $result = $wrd_pct->is_percent();
        $t->assert('word->is_percent for ' . words::PCT, $result, true);

        // next word
        $wrd_time_next = $t->test_word(words::TEST_2022, phrase_type_shared::TIME);
        $t->test_triple(words::TEST_2022, verbs::FOLLOW, words::TEST_2021);
        $target = $wrd_time_next->name();
        $wrd_next = $wrd_time->next();
        $result = $wrd_next->name();
        $t->assert('word->next for ' . words::TEST_2021, $result, $target);

        $target = $wrd_time->name();
        $wrd_prior = $wrd_time_next->prior();
        $result = $wrd_prior->name();
        $t->assert('word->prior for ' . words::TEST_2022, $result, $target);

        // load the main test words
        $wrd_read = $t->load_word(words::MATH);

        // create a parent test word
        $wrd_parent = $t->test_word(words::TEST_PARENT);
        $wrd_parent->add_child($wrd_read);

        // word children, so get all children of a parent
        // e.g. Zurich is s children of Canton
        $phr_lst = $wrd_parent->children();
        $target = words::MATH;
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . words::TEST_PARENT . '"', $result, $target,
            $t::TIMEOUT_LIMIT_DB, 'out of ' . $phr_lst->dsp_id());

        // ... word children excluding the start word, so the list of children should not include the parent
        // e.g. the list of Cantons does not include the word Canton itself
        $target = '';
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . words::TEST_PARENT . '" excluding the start word', $result, $target,
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
        $t->display('word->are for "' . words::TEST_PARENT . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word are including the start word
        // e.g. to get also formulas related to Cantons all formulas related to "Zurich (Canton)" and the word "Canton" itself must be selected
        $target = $wrd_read->name();
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->display('word->are for "' . words::TEST_PARENT . '" including the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // word parents
        $phr_lst = $wrd_read->parents();
        $target = $wrd_parent->name();
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_parent->name();
        } else {
            $result = '';
        }
        $t->display('word->parents for "' . words::MATH . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word parents excluding the start word
        $target = '';
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->display('word->parents for "' . words::MATH . '" excluding the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // create category test words for "Zurich is a Canton" and "Zurich is a City"
        // which implies that Canton contains Zurich and City contains Zurich
        // to avoid conflicts the test words actually used are 'System Test Word Category e.g. Canton' as category word
        // and 'System Test Word Member e.g. Zurich' as member
        $wrd_canton = $t->test_word(words::CANTON);
        $wrd_city = $t->test_word(words::CITY);
        $wrd_ZH = $t->test_word(words::ZH);
        $t->test_triple(words::ZH, verbs::IS, words::CANTON);
        $t->test_triple(words::ZH, verbs::IS, words::CITY);

        // word is e.g. Zurich as a Canton ...
        $target = $wrd_canton->name();
        $phr_lst = $wrd_ZH->is();
        if ($phr_lst->does_contain($wrd_canton)) {
            $result = $wrd_canton->name();
        } else {
            $result = '';
        }
        $t->display('word->is "' . words::ZH . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... and Zurich is a City
        $target = $wrd_city->name();
        $phr_lst = $wrd_ZH->is();
        if ($phr_lst->does_contain($wrd_city)) {
            $result = $wrd_city->name();
        } else {
            $result = '';
        }
        $t->display('word->and is "' . words::ZH . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word is including the start word
        $target = $wrd_ZH->name();
        if ($phr_lst->does_contain($wrd_ZH)) {
            $result = $wrd_ZH->name();
        } else {
            $result = '';
        }
        $t->display('word->is for "' . words::ZH . '" including the start word', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // create the test words and relations for a parent child relation without inheritance
        // e.g. ...
        $wrd_cf = $t->test_word(words::TEST_CASH_FLOW);
        $wrd_tax = $t->test_word(words::TEST_TAX_REPORT);
        $t->test_triple(words::TEST_TAX_REPORT, verbs::PART_NAME, words::TEST_CASH_FLOW);

        // create the test words and relations many mixed relations
        // e.g. a financial report
        $t->test_word(words::TEST_FIN_REPORT);
        $t->test_triple(words::TEST_CASH_FLOW, verbs::IS, words::TEST_FIN_REPORT);

        // create the test words and relations for multi level contains
        // e.g. assets contain current assets which contains cash
        $t->test_word(words::TEST_ASSETS);
        $t->test_word(words::TEST_ASSETS_CURRENT);
        $t->test_word(words::TEST_CASH);
        $t->test_triple(words::TEST_CASH, verbs::PART_NAME, words::TEST_ASSETS_CURRENT);
        $t->test_triple(words::TEST_ASSETS_CURRENT, verbs::PART_NAME, words::TEST_ASSETS);

        // create the test words and relations for differentiators
        // e.g. energy can be a sector
        $t->test_word(words::TEST_SECTOR);
        $t->test_word(words::TEST_ENERGY);
        $t->test_word(words::TEST_WIND_ENERGY);
        $t->test_triple(words::TEST_SECTOR, verbs::CAN_CONTAIN, words::TEST_ENERGY);
        $t->test_triple(words::TEST_ENERGY, verbs::CAN_CONTAIN, words::TEST_WIND_ENERGY);

        // word is part
        $target = $wrd_cf->name();
        $phr_lst = $wrd_tax->is_part();
        if ($phr_lst->does_contain($wrd_cf)) {
            $result = $wrd_cf->name();
        } else {
            $result = '';
        }
        $t->display('word->is_part for "' . words::TEST_TAX_REPORT . '"', $target, $result, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        $test_name = 'check if saving a word with an existing name (' . words::MATH . ') creates a warning message for the user';
        $wrd_new = new word($t->usr1);
        $wrd_new->set_name(words::MATH);
        $result = $wrd_new->save()->get_last_message_translated();
        $target = 'A word with the name "'.words::MATH.'" already exists. Please use another word name.';
        $t->display($test_name, $target, $result, $t::TIMEOUT_LIMIT_DB);

        // test the creation of a new word
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(words::TEST_ADD);
        $result = $wrd_add->save()->get_last_message_translated();
        $target = '';
        $t->assert('word->save for "' . words::TEST_ADD . '"', $result, $target, $t::TIMEOUT_LIMIT_DB);
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(words::TEST_ADD);
        $result = $wrd_add->save()->get_last_message_translated();
        $target = 'A word with the name "'.words::TEST_ADD.'" already exists. Please use another word name.';
        $t->display('word->save reject for "' . words::TEST_ADD . '"', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // check that the word name cannot be used for a verb, triple or formula anymore
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(words::TEST_ADD);
        $result = $vrb->save()->get_last_message_translated();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(verb::class) . ' name.';
        $t->assert('verb cannot have an already used word name', $result, $target);

        // ... triple
        $trp = new triple($t->usr1);
        $trp->load_by_name(triples::PI_NAME);
        $trp->set_name(words::TEST_ADD);
        $result = $trp->save()->get_last_message_translated();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(triple::class) . ' name.';
        $t->assert('triple cannot by renamed to an already used word name', $result, $target);

        // ... or formula anymore
        $frm = new formula($t->usr1);
        $frm->load_by_name(formulas::SCALE_TO_SEC);
        $frm->set_name(words::TEST_ADD);
        $result = $frm->save()->get_last_message_translated();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.';
        $t->assert('formula cannot by renamed to an already used word name', $result, $target);


        $t->subheader('... and also testing the user log class (classes/user_log.php)');

        // ... check if the word creation has been logged
        if ($wrd_add->id() > 0) {
            $result = $t->log_last_by_field($wrd_add, change_fields::FLD_WORD_NAME, $wrd_add->id(), true);
        }
        $target = users::SYSTEM_TEST_NAME . ' added "' . words::TEST_ADD . '"';
        $t->display('word->save logged for "' . words::TEST_ADD . '"', $target, $result);

        // ... test if the new word has been created
        $wrd_added = $t->load_word(words::TEST_ADD);
        $wrd_added->load_by_name(words::TEST_ADD);
        if ($wrd_added->id() > 0) {
            $result = $wrd_added->name();
        }
        $target = words::TEST_ADD;
        $t->display('word->load of added word "' . words::TEST_ADD . '"', $target, $result);

        // check if the word can be renamed
        $wrd_added->set_name(words::TEST_RENAMED);
        $result = $wrd_added->save()->get_last_message();
        $target = '';
        $t->display('word->save rename "' . words::TEST_ADD . '" to "' . words::TEST_RENAMED . '".', $target, $result, $t::TIMEOUT_LIMIT_DB);

        // check if the word renaming was successful
        $wrd_renamed = new word($t->usr1);
        if ($wrd_renamed->load_by_name(words::TEST_RENAMED)) {
            if ($wrd_renamed->id() > 0) {
                $result = $wrd_renamed->name();
            }
        }
        $target = words::TEST_RENAMED;
        $t->display('word->load renamed word "' . words::TEST_RENAMED . '"', $target, $result);

        // check if the word parameters can be added
        $wrd_renamed->plural = words::TEST_RENAMED . 's';
        $wrd_renamed->description = words::TEST_RENAMED . ' description';
        $wrd_renamed->type_id = $phr_typ_cac->id(phrase_type_shared::OTHER);
        $result = $wrd_renamed->save()->get_last_message();
        $target = '';
        $t->display('word->save all word fields beside the name for "' . words::TEST_RENAMED . '"',
            $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the word parameters have been added
        $wrd_reloaded = $t->load_word(words::TEST_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = words::TEST_RENAMED . 's';
        $t->display('word->load plural for "' . words::TEST_RENAMED . '"', $target, $result);
        $result = $wrd_reloaded->description;
        $target = words::TEST_RENAMED . ' description';
        $t->display('word->load description for "' . words::TEST_RENAMED . '"', $target, $result);
        $result = $wrd_reloaded->type_id;
        $target = $phr_typ_cac->id(phrase_type_shared::OTHER);
        $t->display('word->load type_id for "' . words::TEST_RENAMED . '"', $target, $result);

        // check if the word parameter adding have been logged
        $result = $t->log_last_by_field($wrd_reloaded, change_fields::FLD_WORD_PLURAL, $wrd_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "' . words::TEST_RENAMED . 's"';
        $t->display('word->load plural for "' . words::TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($wrd_reloaded, sandbox_named::FLD_DESCRIPTION, $wrd_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "' . words::TEST_RENAMED . ' description"';
        $t->display('word->load description for "' . words::TEST_RENAMED . '" logged', $target, $result);
        $t->display('word->load ref_2 for "' . words::TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($wrd_reloaded, change_fields::FLD_PHRASE_TYPE, $wrd_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "differentiator filler"';
        $t->display('word->load type_id for "' . words::TEST_RENAMED . '" logged', $target, $result);

        // check if a user specific word is created if another user changes the word
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(words::TEST_RENAMED);
        $wrd_usr2->plural = words::TEST_RENAMED . 's2';
        $wrd_usr2->description = words::TEST_RENAMED . ' description2';
        $wrd_usr2->type_id = $phr_typ_cac->id(phrase_type_shared::TIME);
        $result = $wrd_usr2->save()->get_last_message();
        $target = '';
        $t->display('word->save all word fields for user 2 beside the name for "' . words::TEST_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(words::TEST_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = words::TEST_RENAMED . 's2';
        $t->display('word->load plural for "' . words::TEST_RENAMED . '"', $target, $result);
        $result = $wrd_usr2_reloaded->description;
        $target = words::TEST_RENAMED . ' description2';
        $t->display('word->load description for "' . words::TEST_RENAMED . '"', $target, $result);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $phr_typ_cac->id(phrase_type_shared::TIME);
        $t->display('word->load type_id for "' . words::TEST_RENAMED . '"', $target, $result);

        // check the word for the original user remains unchanged
        $wrd_reloaded = $t->load_word(words::TEST_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = words::TEST_RENAMED . 's';
        $t->display('word->load plural for "' . words::TEST_RENAMED . '" unchanged for user 1', $target, $result);
        $result = $wrd_reloaded->description;
        $target = words::TEST_RENAMED . ' description';
        $t->display('word->load description for "' . words::TEST_RENAMED . '" unchanged for user 1', $target, $result);
        $result = $wrd_reloaded->type_id;
        $target = $phr_typ_cac->id(phrase_type_shared::OTHER);
        $t->display('word->load type_id for "' . words::TEST_RENAMED . '" unchanged for user 1', $target, $result);

        // TODO check that the changed word name cannot be used for a verb, triple or formula anymore

        // check if undo all specific changes removes the user word
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(words::TEST_RENAMED);
        $wrd_usr2->plural = words::TEST_RENAMED . 's';
        $wrd_usr2->description = words::TEST_RENAMED . ' description';
        $wrd_usr2->type_id = $phr_typ_cac->id(phrase_type_shared::OTHER);
        $result = $wrd_usr2->save()->get_last_message();
        $target = '';
        $t->display('word->save undo the user word fields beside the name for "' . words::TEST_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(words::TEST_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = words::TEST_RENAMED . 's';
        $t->display('word->load plural for "' . words::TEST_RENAMED . '" unchanged now also for user 2', $target, $result);
        $result = $wrd_usr2_reloaded->description;
        $target = words::TEST_RENAMED . ' description';
        $t->display('word->load description for "' . words::TEST_RENAMED . '" unchanged now also for user 2', $target, $result);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $phr_typ_cac->id(phrase_type_shared::OTHER);
        $t->display('word->load type_id for "' . words::TEST_RENAMED . '" unchanged now also for user 2', $target, $result);

        // display
        $back = 1;
        $target = '<a href="/http/view.php?m=' . views::WORD_ID . '&id=' . $wrd_read->id() . '&back=1" title="' . words::MATH_COM . '">' . words::MATH . '</a>';
        $wrd_read_dsp = new word_dsp($wrd_read->api_json());
        $result = $wrd_read_dsp->name_link($back);
        $t->display('word->display "' . words::MATH . '"', $target, $result);

        // check if user 2 can exclude a word without influencing user 1
        $wrd_usr1 = $t->load_word(words::TEST_RENAMED, $t->usr1);
        $wrd_usr2 = $t->load_word(words::TEST_RENAMED, $t->usr2);
        $wrd_usr2->del();
        $wrd_usr2_reloaded = $t->load_word(words::TEST_RENAMED, $t->usr2);
        $target = '';
        $result = $wrd_usr2_reloaded->name_dsp();
        $t->display('user 2 has deleted word "' . words::TEST_RENAMED . '"', $target, $result);
        $wrd_usr1_reloaded = $t->load_word(words::TEST_RENAMED, $t->usr1);
        $target = $wrd_usr1->name_dsp();
        $result = $wrd_usr1_reloaded->name_dsp();
        $t->display('but the word "' . words::TEST_RENAMED . '" is still the same for user 1', $target, $result);

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

        // cleanup - fallback delete
        $wrd = new word($t->usr1);
        foreach (words::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

    }

    /**
     * create some fixed words that are used for db read unit testing
     * these words are not expected to be changed and cannot be changed by the normal users
     *
     * @param all_tests $t
     * @return void
     */
    function create_test_words(all_tests $t): void
    {
        $t->header('Check if all base words are correct');

        foreach (words::TEST_WORDS_CREATE as $word_name) {
            $t->test_word($word_name);
        }
        foreach (words::TEST_WORDS_MEASURE as $word_name) {
            $t->test_word($word_name, phrase_type_shared::MEASURE);
        }
        foreach (words::TEST_WORDS_SCALING as $word_name) {
            $t->test_word($word_name, phrase_type_shared::SCALING);
        }
        foreach (words::TEST_WORDS_SCALING_HIDDEN as $word_name) {
            $t->test_word($word_name, phrase_type_shared::SCALING_HIDDEN);
        }
        foreach (words::TEST_WORDS_PERCENT as $word_name) {
            $t->test_word($word_name, phrase_type_shared::PERCENT);
        }
        $prev_word_name = null;
        foreach (words::TEST_WORDS_TIME_YEAR as $word_name) {
            $t->test_triple($word_name, verbs::IS, words::YEAR_CAP);
            $t->test_word($word_name, phrase_type_shared::TIME);
            if ($prev_word_name != null) {
                $t->test_triple($word_name, verbs::FOLLOW, $prev_word_name);
            }
            $prev_word_name = $word_name;
        }
    }

}
