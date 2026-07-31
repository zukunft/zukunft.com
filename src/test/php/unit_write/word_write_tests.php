<?php

/*

    test/php/unit_write/word_tests.php - write test words to the database and check the results
    ----------------------------------

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

use DateTime;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql_db.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'api.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\all_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;

class word_write_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;

        // init
        $t_wrd = new test_words($t);
        $t_frm = new test_formulas($t);
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);
        $t->name = 'word db write->';

        // start the test section (ts)
        $ts = 'db write word ';
        $t->header($ts);
        // TODO Prio 1 add this cleanup before the test to all write tests
        $t_wrd->cleanup($ts);

        $t->subheader($ts . 'prepared');
        $test_name = 'add word ' . word_names::TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_wrd->word_add_by_func(), true);

        $t->subheader($ts . 'sandbox for ' . word_names::TEST_ADD);
        $t->assert_write_named($t_wrd->word_filled_add(), word_names::TEST_ADD);

        $test_name = 'test saving word type ' . phrase_type_shared::TIME . ' by adding add time word ' . word_names::TEST_2021;
        $wrd_time = $t_db->test_word(word_names::TEST_2021, phrase_type_shared::TIME);
        $result = $wrd_time->is_type(phrase_type_shared::TIME);
        $t->assert($test_name, $result, true);

        // is time
        $result = $wrd_time->is_time();
        $t->assert('word->is_time for ' . word_names::TEST_2021, $result, true);

        // is not measure
        $result = $wrd_time->is_measure();
        $t->assert('word->is_measure for ' . word_names::TEST_2021, $result, false);

        // is measure
        $wrd_measure = $t_db->test_word(word_names::TEST_CHF, phrase_type_shared::MEASURE);
        $result = $wrd_measure->is_measure();
        $t->assert('word->is_measure for ' . word_names::TEST_CHF, $result, true);

        // is not scaling
        $result = $wrd_measure->is_scaling();
        $t->assert('word->is_scaling for ' . word_names::TEST_CHF, $result, false);

        // is scaling
        $wrd_scaling = $t_db->test_word(word_names::MIO, phrase_type_shared::SCALING);
        $result = $wrd_scaling->is_scaling();
        $t->assert('word->is_scaling for ' . word_names::MIO, $result, true);

        // is not percent
        $result = $wrd_scaling->is_percent();
        $t->assert('word->is_percent for ' . word_names::MIO, $result, false);

        // is percent
        $wrd_pct = $t_db->test_word(words::PCT, phrase_type_shared::PERCENT);
        $result = $wrd_pct->is_percent();
        $t->assert('word->is_percent for ' . words::PCT, $result, true);

        // next word
        $wrd_time_next = $t_db->test_word(word_names::TEST_2022, phrase_type_shared::TIME);
        $t_db->test_triple(word_names::TEST_2022, verbs::FOLLOW, word_names::TEST_2021);
        $target = $wrd_time_next->name();
        $wrd_next = $wrd_time->next();
        $result = $wrd_next->name();
        $t->assert('word->next for ' . word_names::TEST_2021, $result, $target);

        $target = $wrd_time->name();
        $wrd_prior = $wrd_time_next->prior();
        $result = $wrd_prior->name();
        $t->assert('word->prior for ' . word_names::TEST_2022, $result, $target);

        // load the main test words
        $wrd_read = $t_db->load_word(word_names::MATH);

        // create a parent test word
        $wrd_parent = $t_db->test_word(word_names::TEST_PARENT);
        $wrd_parent->add_child($wrd_read, $msg);

        // word children, so get all children of a parent
        // e.g. Zurich is s children of canton
        $phr_lst = $wrd_parent->children();
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
        $phr_lst = $wrd_read->parents();
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

        // create category test words for "Zurich is a canton" and "Zurich is a city"
        // which implies that canton contains Zurich and city contains Zurich
        // to avoid conflicts the test words actually used are 'System Test Word Category e.g. canton' as category word
        // and 'System Test Word Member e.g. Zurich' as member
        $wrd_canton = $t_db->test_word(word_names::CANTON);
        $wrd_city = $t_db->test_word(word_names::CITY);
        $wrd_ZH = $t_db->test_word(word_names::ZH);
        $t_db->test_triple(word_names::ZH, verbs::IS, word_names::CANTON);
        $t_db->test_triple(word_names::ZH, verbs::IS, word_names::CITY);

        // word is e.g. Zurich as a canton ...
        $target = $wrd_canton->name();
        $phr_lst = $wrd_ZH->is_phrases();
        if ($phr_lst->does_contain($wrd_canton)) {
            $result = $wrd_canton->name();
        } else {
            $result = '';
        }
        $t->assert('word->is "' . word_names::ZH . '"', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... and Zurich is a city
        $target = $wrd_city->name();
        $phr_lst = $wrd_ZH->is_phrases();
        if ($phr_lst->does_contain($wrd_city)) {
            $result = $wrd_city->name();
        } else {
            $result = '';
        }
        $t->assert('word->and is "' . word_names::ZH . '"', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // ... word is including the start word
        $target = $wrd_ZH->name();
        if ($phr_lst->does_contain($wrd_ZH)) {
            $result = $wrd_ZH->name();
        } else {
            $result = '';
        }
        $t->assert('word->is for "' . word_names::ZH . '" including the start word', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        // create the test words and relations for a parent child relation without inheritance
        // e.g. ...
        $wrd_cf = $t_db->test_word(word_names::TEST_CASH_FLOW);
        $wrd_tax = $t_db->test_word(word_names::TEST_TAX_REPORT);
        $t_db->test_triple(word_names::TEST_TAX_REPORT, verbs::PART_NAME, word_names::TEST_CASH_FLOW);

        // create the test words and relations many mixed relations
        // e.g. a financial report
        $t_db->test_word(word_names::TEST_FIN_REPORT);
        $t_db->test_triple(word_names::TEST_CASH_FLOW, verbs::IS, word_names::TEST_FIN_REPORT);

        // create the test words and relations for multi level contains
        // e.g. assets contain current assets which contains cash
        $t_db->test_word(word_names::TEST_ASSETS);
        $t_db->test_word(word_names::TEST_ASSETS_CURRENT);
        $t_db->test_word(word_names::TEST_CASH);
        $t_db->test_triple(word_names::TEST_CASH, verbs::PART_NAME, word_names::TEST_ASSETS_CURRENT);
        $t_db->test_triple(word_names::TEST_ASSETS_CURRENT, verbs::PART_NAME, word_names::TEST_ASSETS);

        // create the test words and relations for differentiators
        // e.g. energy can be a sector
        $t_db->test_word(word_names::TEST_SECTOR);
        $t_db->test_word(word_names::TEST_ENERGY);
        $t_db->test_word(word_names::TEST_WIND_ENERGY);
        $t_db->test_triple(word_names::TEST_SECTOR, verbs::CAN_CONTAIN, word_names::TEST_ENERGY);
        $t_db->test_triple(word_names::TEST_ENERGY, verbs::CAN_CONTAIN, word_names::TEST_WIND_ENERGY);

        // word is part
        $target = $wrd_cf->name();
        $phr_lst = $wrd_tax->is_part();
        if ($phr_lst->does_contain($wrd_cf)) {
            $result = $wrd_cf->name();
        } else {
            $result = '';
        }
        $t->assert('word->is_part for "' . word_names::TEST_TAX_REPORT . '"', $result, $target, $t::TIMEOUT_LIMIT, 'out of ' . $phr_lst->dsp_id());

        $test_name = 'check if saving a word with an existing name (' . word_names::MATH . ') merges the word and creates an info message for the user';
        $wrd_new = new word($t->usr1);
        $wrd_new->set(word_names::CONST_ID, word_names::MATH);
        $msg = new user_message($t->usr1);
        $wrd_new->save($msg);
        $result = $msg->text();
        $target = 'A word with the name "'.word_names::MATH.'" already exists. Please use another word name.';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $test_name = 'creating a new word with the name "' . word_names::TEST_ADD . '" does not return any error messages';
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(word_names::TEST_ADD);
        $msg = new user_message($t->usr1);
        $wrd_add->save($msg);
        $result = $msg->text();
        $t->assert($test_name, $result, '', $t::TIMEOUT_LIMIT_DB);

        $test_name = '... check if the word creation with the name "' . word_names::TEST_ADD . '" has been logged';
        if ($wrd_add->id() > 0) {
            $log_ui = $t->log_last_ui_by_field($wrd_add, change_fields::FLD_WORD_NAME, $wrd_add->id());
            $result = $log_ui->dsp(true);
            $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ';
            // re-adding a word that a previous test left excluded can land in the user sandbox
            // row, which the change log shows with 'user' after the action
            if ($log_ui->is_user_sandbox_change()) {
                $target .= msg_id::LOG_USER->value . ' ';
            }
            $target .= '"' . word_names::TEST_ADD . '"';
            $t->assert($test_name, $result, $target);
        }

        $test_name = 'trying to create a new word with the name "' . word_names::TEST_ADD . '" again automatically merges the word with existing word if no unique key differs';
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(word_names::TEST_ADD);
        $wrd_add->description = word_names::TEST_ADD_COM;
        $msg = new user_message($t->usr1);
        $wrd_add->save($msg);
        $result = $msg->text();
        $t->assert($test_name, $result, '', $t::TIMEOUT_LIMIT_DB);

        $test_name = 'trying to create a new word with the name "' . word_names::TEST_ADD . '" again can be used to add a unique id e.g. the code_id "' . word_names::TEST_ADD_CODE_ID . '"';
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(word_names::TEST_ADD);
        $wrd_add->code_id = word_names::TEST_ADD_CODE_ID;
        $wrd_add->save($msg);
        $result = $msg->text();
        $t->assert($test_name, $result, '', $t::TIMEOUT_LIMIT_DB);

        $test_name = 'trying to create a new word with the name "' . word_names::TEST_ADD . '" again is rejected if an unique key differs';
        $wrd_add = new word($t->usr1);
        $wrd_add->set_name(word_names::TEST_ADD);
        $wrd_add->code_id = words::SYSTEM_CODE_ID;
        $msg = new user_message($t->usr1);
        $wrd_add->save($msg);
        $result = $msg->text();
        $target = 'A word with the name "'.word_names::TEST_ADD.'" already exists. Please use another word name.';
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $t->subheader($ts . 'protection');
        $test_name = 'the owner can raise the protection of "' . word_names::TEST_ADD . '" to admin';
        $wrd_prt = $t_db->load_word(word_names::TEST_ADD);
        $wrd_prt->set_protection_by_code_id(protection_types::ADMIN);
        $msg = new user_message($t->usr1);
        $wrd_prt->save($msg);
        $wrd_db = $t_db->load_word(word_names::TEST_ADD);
        $t->assert($test_name, $wrd_db->protection_id(), $sys->typ_lst->ptc_typ->id(protection_types::ADMIN), $t::TIMEOUT_LIMIT_DB);

        $test_name = 'a re-import without the protection field keeps the admin protection';
        $wrd_prt = $t_db->load_word(word_names::TEST_ADD);
        $wrd_prt->set_protection_id(null);
        $wrd_prt->description = word_names::TEST_RENAMED;
        $msg = new user_message($t->usr1);
        $wrd_prt->save($msg);
        $wrd_db = $t_db->load_word(word_names::TEST_ADD);
        $t->assert($test_name, $wrd_db->protection_id(), $sys->typ_lst->ptc_typ->id(protection_types::ADMIN), $t::TIMEOUT_LIMIT_DB);
        // restore the description so that the later description log tests are not affected
        $wrd_prt = $t_db->load_word(word_names::TEST_ADD);
        $wrd_prt->description = word_names::TEST_ADD_COM;
        $msg = new user_message($t->usr1);
        $wrd_prt->save($msg);

        $test_name = 'a normal user cannot reduce the protection level';
        $wrd_prt = $t_db->load_word(word_names::TEST_ADD, $t->usr_normal);
        $wrd_prt->set_protection_by_code_id(protection_types::NO_PROTECT);
        $msg = new user_message($t->usr_normal);
        $wrd_prt->save($msg);
        $wrd_db = $t_db->load_word(word_names::TEST_ADD);
        $t->assert($test_name, $wrd_db->protection_id(), $sys->typ_lst->ptc_typ->id(protection_types::ADMIN), $t::TIMEOUT_LIMIT_DB);
        $test_name = '... and the denied reduction is reported to the user';
        $t->assert_text_contains($test_name, $msg->all_message_text(), word_names::TEST_ADD);

        // check that the word name cannot be used for a verb, triple or formula any more
        // TODO Prio 0 review
        /*
        $vrb = new verb();
        $vrb->set_user($t->usr1);
        $vrb->set_name(word_names::TEST_ADD);
        $usr_msg = new user_message($t->usr1);
        $vrb->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(verb::class) . ' name.';
        $t->assert('verb cannot have an already used word name', $result, $target);

        // ... triple
        $t_trp = new test_triples($t);
        $trp = $t_trp->triple();
        $trp->id = 0;
        $trp->set_name(word_names::TEST_ADD);
        $usr_msg = new user_message($t->usr1);
        $trp->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(triple::class) . ' name.';
        $target = 'user message translation for position -1 not found';
        $t->assert('triple cannot by renamed to an already used word name', $result, $target);

        // ... or formula any more
        $t_frm = new test_formulas($t);
        $frm = $t_frm->formula();
        $frm->id = 0;
        $frm->set_name(word_names::TEST_ADD);
        $usr_msg = new user_message($t->usr1);
        $frm->save($usr_msg);
        $result = $usr_msg->text();
        $target = 'A word with the name "System Test Word" already exists. '
            . 'Please use another ' . $lib->class_to_name(formula::class) . ' name.';
        $t->assert('formula cannot by renamed to an already used word name', $result, $target);
        */


        $t->subheader($ts . 'user log');

        // ... test if the new word has been created
        $wrd_added = $t_db->load_word(word_names::TEST_ADD);
        $wrd_added->load_by_name(word_names::TEST_ADD);
        if ($wrd_added->id() > 0) {
            $result = $wrd_added->name();
        }
        $target = word_names::TEST_ADD;
        $t->assert('word->load of added word "' . word_names::TEST_ADD . '"', $result, $target);

        $test_name = 'check if the word "' . word_names::TEST_ADD . '" can be renamed to "' . word_names::TEST_RENAMED . '"';
        $wrd_added->set_name(word_names::TEST_RENAMED);
        $msg = new user_message($t->usr1);
        $t->assert_true($test_name, $wrd_added->save($msg), $t::TIMEOUT_LIMIT_DB);

        // check if the word renaming was successful
        $wrd_renamed = new word($t->usr1);
        if ($wrd_renamed->load_by_name(word_names::TEST_RENAMED)) {
            if ($wrd_renamed->id() > 0) {
                $result = $wrd_renamed->name();
            }
        }
        $target = word_names::TEST_RENAMED;
        $t->assert('word->load renamed word "' . word_names::TEST_RENAMED . '"', $result, $target);

        // check if the word parameters can be added
        $wrd_renamed->plural = word_names::TEST_RENAMED . 's';
        $wrd_renamed->description = word_names::TEST_RENAMED . ' description';
        $wrd_renamed->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::OTHER);
        $msg = new user_message($t->usr1);
        $wrd_renamed->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('word->save all word fields beside the name for "' . word_names::TEST_RENAMED . '"', $result,
            $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the word parameters have been added
        $wrd_reloaded = $t_db->load_word(word_names::TEST_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = word_names::TEST_RENAMED . 's';
        $t->assert('word->load plural for "' . word_names::TEST_RENAMED . '"', $result, $target);
        $result = $wrd_reloaded->description;
        $target = word_names::TEST_RENAMED . ' description';
        $t->assert('word->load description for "' . word_names::TEST_RENAMED . '"', $result, $target);
        $result = $wrd_reloaded->type_id;
        $target = $sys->typ_lst->phr_typ->id(phrase_type_shared::OTHER);
        $t->assert('word->load type_id for "' . word_names::TEST_RENAMED . '"', $result, $target);

        // check if the word parameter adding have been logged; the field changes are written to
        // the user sandbox row and shown with 'user' after the action if usr1 cannot change the
        // standard row e.g. because a previous run has left the standard row to another owner;
        // all three fields are written by the same save, so the marker of the plural row is reused
        $log_ui = $t->log_last_ui_by_field($wrd_reloaded, change_fields::FLD_WORD_PLURAL, $wrd_reloaded->id());
        $usr_marker = $log_ui->is_user_sandbox_change() ? msg_id::LOG_USER->value . ' ' : '';
        $result = $log_ui->dsp(true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"' . word_names::TEST_RENAMED . 's"';
        $t->assert('word->load plural for "' . word_names::TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($wrd_reloaded, fields::FLD_DESCRIPTION, $wrd_reloaded->id(), true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "' . word_names::TEST_RENAMED . ' description" from "' . word_names::TEST_ADD_COM . '"';
        $t->assert('word->load description for "' . word_names::TEST_RENAMED . '" logged', $result, $target);
        $t->assert('word->load ref_2 for "' . word_names::TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($wrd_reloaded, change_fields::FLD_PHRASE_TYPE, $wrd_reloaded->id(), true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"differentiator filler"';
        $t->assert('word->load type_id for "' . word_names::TEST_RENAMED . '" logged', $result, $target);

        $test_name = 'check if a user-specific word is created if another user changes the word to ' . word_names::TEST_RENAMED;
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(word_names::TEST_RENAMED);
        $wrd_usr2->plural = word_names::TEST_RENAMED . 's2';
        $wrd_usr2->description = word_names::TEST_RENAMED . ' description2';
        $wrd_usr2->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::TIME);
        $t->assert_true($test_name, $wrd_usr2->save($msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user-specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(word_names::TEST_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = word_names::TEST_RENAMED . 's2';
        $t->assert('word->load plural for "' . word_names::TEST_RENAMED . '"', $result, $target);
        $result = $wrd_usr2_reloaded->description;
        $target = word_names::TEST_RENAMED . ' description2';
        $t->assert('word->load description for "' . word_names::TEST_RENAMED . '"', $result, $target);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $sys->typ_lst->phr_typ->id(phrase_type_shared::TIME);
        $t->assert('word->load type_id for "' . word_names::TEST_RENAMED . '"', $result, $target);

        // ... and the user overwrites for the 'my' tab list the changed field with both values
        $test_name = 'the user overwrites list the plural with the user and the standard value';
        $usr_ovr = $wrd_usr2_reloaded->user_overwrites_api_array(new user_message($t->usr2));
        $ovr_key = array_search(word_fields::FLD_PLURAL, array_column($usr_ovr, json_fields::FIELD));
        $t->assert_true($test_name, $ovr_key !== false);
        if ($ovr_key !== false) {
            $test_name = '... with the user value of the plural';
            $t->assert($test_name, $usr_ovr[$ovr_key][json_fields::USR_VALUE], word_names::TEST_RENAMED . 's2');
            $test_name = '... and a standard value that differs from the user value';
            $t->assert_true($test_name, $usr_ovr[$ovr_key][json_fields::STD_VALUE] != word_names::TEST_RENAMED . 's2');
        }

        // check the word for the original user remains unchanged
        $wrd_reloaded = $t_db->load_word(word_names::TEST_RENAMED);
        $result = $wrd_reloaded->plural;
        $target = word_names::TEST_RENAMED . 's';
        $t->assert('word->load plural for "' . word_names::TEST_RENAMED . '" unchanged for user 1', $result, $target);

        // ... and the shared overwrite of user 2 is listed for user 1 in the others tab data
        $test_name = 'the other overwrites list the plural of the partner user';
        $oth_ovr = $wrd_reloaded->other_overwrites_api_array(new user_message($t->usr1));
        $oth_found = false;
        foreach ($oth_ovr as $oth_row) {
            if ($oth_row[json_fields::FIELD] == word_fields::FLD_PLURAL
                and $oth_row[json_fields::USER_NAME] == users::SYSTEM_TEST_PARTNER_NAME
                and $oth_row[json_fields::USR_VALUE] == word_names::TEST_RENAMED . 's2') {
                $oth_found = true;
            }
        }
        $t->assert_true($test_name, $oth_found);
        $result = $wrd_reloaded->description;
        $target = word_names::TEST_RENAMED . ' description';
        $t->assert('word->load description for "' . word_names::TEST_RENAMED . '" unchanged for user 1', $result, $target);
        $result = $wrd_reloaded->type_id;
        $target = $sys->typ_lst->phr_typ->id(phrase_type_shared::OTHER);
        $t->assert('word->load type_id for "' . word_names::TEST_RENAMED . '" unchanged for user 1', $result, $target);

        // TODO check that the changed word name cannot be used for a verb, triple or formula anymore

        $test_name = 'check if undo all specific changes removes the user word ' . word_names::TEST_RENAMED;
        $wrd_usr2 = new word($t->usr2);
        $wrd_usr2->load_by_name(word_names::TEST_RENAMED);
        $wrd_usr2->plural = word_names::TEST_RENAMED . 's';
        $wrd_usr2->description = word_names::TEST_RENAMED . ' description';
        $wrd_usr2->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::OTHER);
        $t->assert_true($test_name, $wrd_usr2->save($msg), $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if a user-specific word changes have been saved
        $wrd_usr2_reloaded = new word($t->usr2);
        $wrd_usr2_reloaded->load_by_name(word_names::TEST_RENAMED);
        $result = $wrd_usr2_reloaded->plural;
        $target = word_names::TEST_RENAMED . 's';
        $t->assert('word->load plural for "' . word_names::TEST_RENAMED . '" unchanged now also for user 2', $result, $target);
        $result = $wrd_usr2_reloaded->description;
        $target = word_names::TEST_RENAMED . ' description';
        $t->assert('word->load description for "' . word_names::TEST_RENAMED . '" unchanged now also for user 2', $result, $target);
        $result = $wrd_usr2_reloaded->type_id;
        $target = $sys->typ_lst->phr_typ->id(phrase_type_shared::OTHER);
        $t->assert('word->load type_id for "' . word_names::TEST_RENAMED . '" unchanged now also for user 2', $result, $target);

        // display
        $back = 1;
        $target = '<a href="' . api::MAIN_SCRIPT . '?' . url_var::MASK . '=' . views::WORD_ID . '&amp;id=' . $wrd_read->id() . '&amp;back=1" title="' . word_names::MATH_COM . '">' . word_names::MATH . '</a>';
        $wrd_read_ui = new word_ui($wrd_read->api_json());
        $result = $wrd_read_ui->name_link($back);
        $t->assert('word->display "' . word_names::MATH . '"', $result, $target);

        // check if user 2 can exclude a word without influencing user 1
        $wrd_usr1 = $t_db->load_word(word_names::TEST_RENAMED);
        $wrd_usr2 = $t_db->load_word(word_names::TEST_RENAMED, $t->usr2);
        $wrd_usr2->del($msg);
        $wrd_usr2_reloaded = $t_db->load_word(word_names::TEST_RENAMED, $t->usr2);
        $target = '';
        $result = $wrd_usr2_reloaded->name_dsp();
        $t->assert('user 2 has deleted word "' . word_names::TEST_RENAMED . '"', $result, $target);
        $wrd_usr1_reloaded = $t_db->load_word(word_names::TEST_RENAMED);
        $target = $wrd_usr1->name_dsp();
        $result = $wrd_usr1_reloaded->name_dsp();
        $t->assert('but the word "' . word_names::TEST_RENAMED . '" is still the same for user 1', $result, $target);

        $test_name = 'delete the word also for user 1';
        $wrd_usr1_reloaded->del($msg);
        $wrd_usr1_deleted = new word($t->usr1);
        $wrd_usr1_deleted->load_by_name(word_names::TEST_RENAMED);
        $t->assert($test_name, $wrd_usr1_deleted->id(), 0);

        // TODO test the creation of a new scaling word e.g. dozen for 12
        //      and adding a related formula and calculating values based on the added formula
        // TODO test the creation of a new time word e.g. year 2042

        // TODO redo the user-specific word changes including changing the default view
        // check if the user-specific changes can be removed with one click

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
        $t->assert('word->main_wrd_from_txt', $result, $target);
        */

        // controlled deletion of the share test word before the fallback cleanup: it is created by the
        // normal user (see create_test_words), so load it as that owner and request the deletion as that
        // owner, then assert the row is really gone instead of leaving it for the fallback cleanup below
        $test_name = 'delete word "' . word_names::TEST_SHARE . '"';
        $wrd_share = $t_db->load_word(word_names::TEST_SHARE);
        $wrd_share->del($msg);
        $wrd_share_deleted = new word($t->usr1);
        $wrd_share_deleted->load_by_name(word_names::TEST_SHARE);
        $t->assert($test_name, $wrd_share_deleted->id(), 0);

        // cleanup - fallback delete
        $t_wrd->cleanup($ts);
        // cleanup - including related formulas
        $t_frm->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg);

    }

    /**
     * create some fixed words that are used for db read unit testing
     * these words are not expected to be changed and cannot be changed by the normal users
     *
     * @param all_tests|a_selected_test $t
     * @return void
     */
    function create_test_words(all_tests|a_selected_test $t): void
    {
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db create test words ';
        $t->header($ts);

        // Check words with types
        // TODO Prio 2 move outside create_test_words
        foreach (word_names::WORDS_SCALING as $word_name) {
            $t_db->test_word($word_name, phrase_type_shared::SCALING, $t->usr_system);
        }
        foreach (word_names::WORDS_SCALING_HIDDEN as $word_name) {
            $t_db->test_word($word_name, phrase_type_shared::SCALING_HIDDEN, $t->usr_system);
        }
        foreach (word_names::WORDS_PERCENT as $word_name) {
            $t_db->test_word($word_name, phrase_type_shared::PERCENT, $t->usr_system);
        }

        foreach (word_names::TEST_WORDS_CREATE as $word_name) {
            $t_db->test_word($word_name, null, $t->usr1);
        }
        foreach (word_names::TEST_WORDS_MEASURE as $word_name) {
            $t_db->test_word($word_name, phrase_type_shared::MEASURE, $t->usr1);
        }
        foreach (word_names::TEST_WORDS_SCALING as $word_name) {
            $t_db->test_word($word_name, phrase_type_shared::SCALING, $t->usr1);
        }
        $prev_word_name = null;
        foreach (word_names::TEST_WORDS_TIME_YEAR as $word_name) {
            $t_db->test_triple($word_name, verbs::IS, words::YEAR_CAP);
            $t_db->test_word($word_name, phrase_type_shared::TIME, $t->usr1);
            if ($prev_word_name != null) {
                $t_db->test_triple($word_name, verbs::FOLLOW, $prev_word_name);
            }
            $prev_word_name = $word_name;
        }
    }

}
