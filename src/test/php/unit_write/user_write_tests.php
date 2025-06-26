<?php

/*

    test/php/unit_write/user_write_tests.php - test adding, updating and deleting users in the database
    ----------------------------------------
  

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

class user_write_tests
{

    function run(test_cleanup $t): void
    {
        global $phr_typ_cac;

        // init
        $t->name = 'user db write->';

        // start the test section (ts)
        $ts = 'db write user ';
        $t->header($ts);

        $t->subheader($ts . 'add');
        $usr = $t->user_ip();
        $t->assert_write($usr, $usr->unique_value(), $usr->key_field());

        /*

        $t->subheader('user prepared write');
        $test_name = 'add user ' . users::TEST_NAME;
        $t->assert_write_via_func_or_sql($test_name, $t->word_add_by_func(), true);

        $t->subheader('user write sandbox tests for ' . words::TEST_ADD);
        $t->assert_write_named($t->word_filled_add(), words::TEST_ADD);

        $test_name = 'test saving word type ' . phrase_type_shared::TIME . ' by adding add time word ' . words::TEST_2021;
        $wrd_time = $t->test_word(words::TEST_2021, phrase_type_shared::TIME);
        $result = $wrd_time->is_type(phrase_type_shared::TIME);
        $t->assert($test_name, $result, true);

        // TODO prevent access write gains
        // TODO prevent changing system users


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


        // cleanup - fallback delete
        $wrd = new word($t->usr1);
        foreach (words::TEST_WORDS as $wrd_name) {
            $t->write_named_cleanup($wrd, $wrd_name);
        }

        */

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
