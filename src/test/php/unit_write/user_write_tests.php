<?php

/*

    test/php/unit_write/user_write_tests.php - test adding, updating and deleting users in the database
    ----------------------------------------

    just the special test cases not covered by the horizontal write tests


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once test_paths::CONST . 'word_names.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class user_write_tests
{

    function run(test_cleanup $t): void
    {

        // init
        $t_usr = new test_users($t);
        $msg = new user_message($t->usr1);
        $t->name = 'user db write->';

        // start the test section (ts)
        $ts = 'db write user ';
        $t->header($ts);
        $t_usr->cleanup($ts, $t);

        $t->subheader($ts . 'add');
        $usr = $t_usr->user_ip();
        $t->assert_write($usr, $usr->unique_value(), $usr->key_field());

        // check that switching a user to sandbox usage is actually written to the database
        $t->subheader($ts . 'sandbox usage');

        // use a test user that exists in the database
        $usr_db = new user();
        $usr_db->load_by_name(users::SYSTEM_TEST_PARTNER_NAME, $msg);

        // make sure the starting point is "not yet using the sandbox"
        if ($usr_db->uses_sandbox) {
            $usr_db->uses_sandbox = false;
            $usr_db->save_user($msg, $t->usr1);
        }

        // switch the user to sandbox usage which should store the flag in the database
        $usr_db->set_uses_sandbox($msg);

        // reload the user by id to check that the flag has really been written to the database
        $usr_reload = new user();
        $usr_reload->load_by_id($usr_db->id(), $msg);
        $test_name = 'switching a user to sandbox usage is stored in the database';
        $t->assert_true($test_name, $usr_reload->uses_sandbox);

        // counting and rechecking switches the flag off again if no sandbox row is left;
        // earlier tests can legitimately leave overlay rows for the test partner, then the
        // flag correctly stays on, so the assert checks the contract of check_sandbox_usage
        // (the stored flag mirrors the remaining sandbox rows) instead of assuming zero rows
        global $db_con;
        $usr_reload->check_sandbox_usage($db_con, $msg);
        $usr_check = new user();
        $usr_check->load_by_id($usr_db->id(), $msg);
        $usr_lst = new user_list($usr_check);
        $sandbox_rows = $usr_lst->count_user_rows($db_con, $usr_check->id(), $msg);
        $test_name = 'after the check the sandbox usage flag mirrors the remaining sandbox rows';
        $t->assert($test_name, $usr_check->uses_sandbox, $sandbox_rows > 0);

        /*

        $t->subheader($ts . 'user prepared write');
        $test_name = 'add user ' . users::TEST_NAME;
        $t->assert_write_via_func_or_sql($test_name, $t_wrd->word_add_by_func(), true);

        $t->subheader($ts . 'user write sandbox tests for ' . word_names::TEST_ADD);
        $t->assert_write_named($t_wrd->word_filled_add(), word_names::TEST_ADD);

        $test_name = 'test saving word type ' . phrase_type_shared::TIME . ' by adding add time word ' . word_names::TEST_2021;
        $wrd_time = $t->test_word(word_names::TEST_2021, phrase_type_shared::TIME);
        $result = $wrd_time->is_type(phrase_type_shared::TIME);
        $t->assert($test_name, $result, true);

        // TODO prevent access write gains
        // TODO prevent changing system users


        // load the main test words
        $wrd_read = $t->load_word(word_names::MATH);

        // create a parent test word
        $wrd_parent = $t->test_word(word_names::TEST_PARENT);
        $wrd_parent->add_child($wrd_read);

        // word children, so get all children of a parent
        // e.g. Zurich is s children of canton
        $phr_lst = $wrd_parent->children($msg);
        $target = word_names::MATH;
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . word_names::TEST_PARENT . '"', $result, $target,
            $t::TIMEOUT_LIMIT_DB, 'out of ' . $phr_lst->dsp_id());

        // ... word children excluding the start word, so the list of children should not include the parent
        // e.g. the list of cantons does not include the word canton itself
        $target = '';
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_read->name_dsp();
        } else {
            $result = '';
        }
        $t->assert('word->children for "' . word_names::TEST_PARENT . '" excluding the start word', $result, $target,
            $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // TODO move read only tests like this to the db read or unit tests
        // word are, which includes all words related to the parent
        // e.g. which is for parent canton the phrase "Zurich (canton)", but not, as tested later, the phrase "Zurich (city)"
        //      "cantons are Zurich, Bern, ... and valid is also everything related to the Word canton itself"
        $phr_lst = $wrd_parent->are();
        $target = $wrd_read->name();
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->assert('word->are for "' . word_names::TEST_PARENT . '"', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word are including the start word
        // e.g. to get also formulas related to cantons all formulas related to "Zurich (canton)" and the word "canton" itself must be selected
        $target = $wrd_read->name();
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->assert('word->are for "' . word_names::TEST_PARENT . '" including the start word', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // word parents
        $phr_lst = $wrd_read->parents($msg);
        $target = $wrd_parent->name();
        if ($phr_lst->does_contain($wrd_parent)) {
            $result = $wrd_parent->name();
        } else {
            $result = '';
        }
        $t->assert('word->parents for "' . word_names::MATH . '"', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word parents excluding the start word
        $target = '';
        if ($phr_lst->does_contain($wrd_read)) {
            $result = $wrd_read->name();
        } else {
            $result = '';
        }
        $t->assert('word->parents for "' . word_names::MATH . '" excluding the start word', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());


        */

        // cleanup - fallback delete
        $t_usr->cleanup($ts, $t);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg, library::class_to_name(user::class));

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
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db validate test words ';
        $t->header($ts);

        foreach (word_names::WORDS_SCALING as $word_name) {
            $t_db->test_word($msg, $word_name, phrase_type_shared::SCALING);
        }
        foreach (word_names::WORDS_SCALING_HIDDEN as $word_name) {
            $t_db->test_word($msg, $word_name, phrase_type_shared::SCALING_HIDDEN);
        }
        foreach (word_names::WORDS_PERCENT as $word_name) {
            $t_db->test_word($msg, $word_name, phrase_type_shared::PERCENT);
        }

        foreach (word_names::TEST_WORDS_CREATE as $word_name) {
            $t_db->test_word($msg, $word_name);
        }
        foreach (word_names::TEST_WORDS_MEASURE as $word_name) {
            $t_db->test_word($msg, $word_name, phrase_type_shared::MEASURE);
        }
        foreach (word_names::TEST_WORDS_SCALING as $word_name) {
            $t_db->test_word($msg, $word_name, phrase_type_shared::SCALING);
        }
        $prev_word_name = null;
        foreach (word_names::TEST_WORDS_TIME_YEAR as $word_name) {
            $t_db->test_triple($msg, $word_name, verbs::IS, words::YEAR_CAP);
            $t_db->test_word($msg, $word_name, phrase_type_shared::TIME);
            if ($prev_word_name != null) {
                $t_db->test_triple($msg, $word_name, verbs::FOLLOW, $prev_word_name);
            }
            $prev_word_name = $word_name;
        }
    }

}
