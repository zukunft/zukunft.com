<?php

/*

    test/php/unit_write/update_put_write_tests.php - db write tests of the update put messages
    -----------------------------------------------

    put each update put message of the calculator editor save, read the result back from the
    database and compare it, then put the undo message


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::EXPORT . 'phrase_data.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_HELPER . 'db_cache_page.php';
include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_IMPORT . 'update_put.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_ENUM . 'phrase_data_walks.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\import\update_put;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\enum\phrase_data_walks;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class update_put_write_tests
{

    // a cached answer of the phrase data, which the put must remove
    const string TV_API_JSON = '{"update_put_test":[1]}';
    // the delete entries of the undo message that are not saved after the first is refused
    const string UNDO_SKIPPED = '3';
    // a cached html page of the test word, which the put must remove
    const string TV_PAGE_URL = url_var::MASK . url_var::EQ . views::WORD_ID . url_var::ADD_ID . word_names::TEST_ADD_ID;
    const string TV_HTML = '<html lang="en"><body>update put test</body></html>';
    // the phrases of the PV capacity value and of its maximum, which the calculator editor scenarios change
    const array PV_CAPACITY_PHRASES = [
        word_names::ASSUMED,
        word_names::KWP,
        triple_names::EXAMPLE_PV_INSTALLATION,
        triple_names::PV_CAPACITY
    ];
    const array PV_CAPACITY_MAXIMUM_PHRASES = [triple_names::PV_CAPACITY, triples::FIELD_LIMIT_UP];
    // the numbers that the scenarios pv_capacity_number and pv_capacity_maximum type
    const float PV_CAPACITY_NUMBER = 12.5;
    const float PV_CAPACITY_MAXIMUM = 100;

    function run(test_cleanup $t): void
    {

        // init
        $lib = new library();
        $upd = new update_put();
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write update put ';
        $t->header($ts);

        $t->subheader($ts . 'add');

        $test_name = 'a message prepared for another user is refused';
        $json = $this->message(test_files::UPDATE_PUT_ADD);
        $json[json_fields::USER_ID] = $t->usr2->id();
        $upd->put($json, $msg);
        $msg_txt = $msg->all_message_text();
        $expected = $this->expected_text(msg_id::UPDATE_PUT_OTHER_USER, [
            msg_id::VAR_ID => (string)$t->usr2->id(),
            msg_id::VAR_USER_NAME => $t->usr1->name()
        ]);
        $t->assert_text_contains($test_name, $msg_txt, $expected, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = '... and nothing of it is added';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $chk_msg);
        $t->assert($test_name, $trp->id(), 0, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'the page of the test word is cached before the refused put';
        $cac_page = new db_cache_page();
        $cac_page->save_html(self::TV_PAGE_URL, self::TV_HTML, $chk_msg);
        $cac_html = $cac_page->html_by_url(self::TV_PAGE_URL, $chk_msg);
        $t->assert($test_name, $cac_html, self::TV_HTML, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'a triple whose words are neither in the message nor in the database is refused';
        $upd->put($this->message(test_files::UPDATE_PUT_WITHOUT_WORD), $msg);
        $expected = $this->expected_text(msg_id::PHRASE_NAME_NOT_FOUND, [msg_id::VAR_NAME => word_names::TEST_ADD_TO]);
        $t->assert_msg_false($test_name, $msg, $expected);
        $test_name = '... and not added';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $chk_msg);
        $t->assert($test_name, $trp->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but the cached pages are removed, because the objects before a problem are saved';
        $cac_html = $cac_page->html_by_url(self::TV_PAGE_URL, $chk_msg);
        $t->assert_null($test_name, $cac_html);

        $test_name = 'the phrase data answer is cached before the put';
        $cac_page->save_html(self::TV_PAGE_URL, self::TV_HTML, $chk_msg);
        $cac_key = db_cache_page::api_key(phrase_data::class, [url_var::ID => word_names::TEST_ADD_ID], $t->usr1->id());
        $cac_page->save_json($cac_key, self::TV_API_JSON, new DateTime(), $chk_msg);
        $t->assert($test_name, $cac_page->json_by_key($cac_key, $chk_msg), self::TV_API_JSON, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the add message of the words, the triple and the value';
        $add = $upd->put($this->message(test_files::UPDATE_PUT_ADD), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the triple of the add message is in the database';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::SYSTEM_TEST_ADD, $chk_msg);
        $t->assert_greater_zero($test_name, $trp->id(), test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the answer of the add message has the database id of the triple';
        $add_trp_id = $add[json_fields::TRIPLES][0][json_fields::ID];
        $t->assert($test_name, $add_trp_id, $trp->id());
        $test_name = 'the value of the add message is in the database';
        $val_id = $add[json_fields::VALUES][0][json_fields::ID];
        // the global cleanup removes the value if a later test fails before the undo
        $t->test_val_ids[] = $val_id;
        $val = new value($t->usr1);
        $val->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val->number(), values::SAMPLE_INT, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the put removes the cached phrase data';
        $cac_json = $cac_page->json_by_key($cac_key, $chk_msg);
        $t->assert_null($test_name, $cac_json);
        $test_name = '... and the cached pages';
        $cac_html = $cac_page->html_by_url(self::TV_PAGE_URL, $chk_msg);
        $t->assert_null($test_name, $cac_html);

        $t->subheader($ts . 'update');

        $test_name = 'a triple with a verb that does not exist is refused';
        $wrd_id = $add[json_fields::WORDS][0][json_fields::ID];
        $wrd_to_id = $add[json_fields::WORDS][1][json_fields::ID];
        $json = $this->update_message($trp->id(), (string)$val_id, verbs::TEST_ADD_NAME, phrase_types::MATH_CONST);
        unset($json[json_fields::BODY][json_fields::VALUES]);
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::TRIPLE_VERB_NOT_FOUND, [msg_id::VAR_NAME => verbs::TEST_ADD_NAME]);
        $t->assert_msg_false($test_name, $msg, $expected);

        $test_name = 'a triple with a type that does not exist is refused';
        $json = $this->update_message($trp->id(), (string)$val_id, verbs::IS_NAME, type_list::TEST_TYPE);
        unset($json[json_fields::BODY][json_fields::VALUES]);
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::PHRASE_TYPE_NOT_FOUND, [msg_id::VAR_NAME => type_list::TEST_TYPE]);
        $t->assert_msg_false($test_name, $msg, $expected);

        // the verb and the type by their name, without the from and to phrase, which stay as
        // they are in the database, and without the user in the header, which the session gives
        $test_name = 'put the update message with the database ids';
        $json = $this->update_message($trp->id(), (string)$val_id, verbs::IS_NAME, phrase_types::MATH_CONST);
        unset($json[json_fields::USER_ID]);
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the triple is renamed, because the id is the prime key';
        $trp_upd = new triple($t->usr1);
        $trp_upd->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_upd->name(), triple_names::SYSTEM_TEST_RENAMED, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has the description of the message';
        $t->assert($test_name, $trp_upd->description, triple_names::SYSTEM_TEST_ADD_COM);
        $test_name = '... and the verb given by its name';
        $vrb_id = $trp_upd->get_verb_id();
        $t->assert($test_name, $vrb_id, verbs::IS_ID);
        $test_name = '... and the type given by its code id';
        $typ_code_id = $trp_upd->type_code_id($chk_msg);
        $t->assert($test_name, $typ_code_id, phrase_types::MATH_CONST);
        $test_name = '... and keeps the from phrase of the database, which the message does not name';
        $from_id = $trp_upd->from_id();
        $t->assert($test_name, $from_id, $wrd_id);
        $test_name = '... and the to phrase';
        $to_id = $trp_upd->to_id();
        $t->assert($test_name, $to_id, $wrd_to_id);
        $test_name = 'the value is changed by its id';
        $val = new value($t->usr1);
        $val->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val->number(), values::SAMPLE_FLOAT, test_base::TIMEOUT_LIMIT_DB);

        $t->subheader($ts . 'undo');

        $test_name = 'the delete of a class that the message does not save is refused';
        $cls_name = $lib->class_to_name(formula::class);
        $json = $this->message(test_files::UPDATE_PUT_UNDO);
        $json[json_fields::BODY][json_fields::DELETE] = [[json_fields::OBJECT_CLASS => $cls_name, json_fields::ID => $trp->id()]];
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::OBJECT_NOT_FOUND, [
            msg_id::VAR_CLASS_NAME => $cls_name,
            msg_id::VAR_ID => (string)$trp->id()
        ]);
        $t->assert_msg_false($test_name, $msg, $expected);

        $test_name = 'put the undo message that deletes the value, the triple and the words';
        // the phrases of the value are kept to remove the change log of the value after its delete
        $val->load_phrases($chk_msg);
        $val_grp = $val->grp();
        $json = $this->undo_message($val_id, $trp->id(), $wrd_id, $wrd_to_id);
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the triple is deleted';
        $trp_del = new triple($t->usr1);
        $trp_del->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_del->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the word is deleted';
        $wrd = new word($t->usr1);
        $wrd->load_by_id($wrd_id, $chk_msg);
        $t->assert($test_name, $wrd->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the undo message again is refused and names the changes that are not saved';
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::UPDATE_PUT_SKIPPED, [msg_id::VAR_COUNTER => self::UNDO_SKIPPED]);
        $t->assert_msg_false($test_name, $msg, $expected);
        $test_name = 'the update of a deleted value is refused, because the id of a value is kept by the load';
        $json = $this->message(test_files::UPDATE_PUT_UPDATE);
        unset($json[json_fields::BODY][json_fields::TRIPLES]);
        $json[json_fields::BODY][json_fields::VALUES][0][json_fields::ID] = (string)$val_id;
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::OBJECT_NOT_FOUND, [
            msg_id::VAR_CLASS_NAME => $lib->class_to_name(value::class),
            msg_id::VAR_ID => (string)$val_id
        ]);
        $t->assert_msg_false($test_name, $msg, $expected);

        // a change log entry must never point to a deleted test row
        $t->cleanup_change_log_value($val_grp, $val_grp->phrase_list());
        $t->cleanup_change_log($trp, [triple_names::SYSTEM_TEST_ADD, triple_names::SYSTEM_TEST_RENAMED]);
        $t->cleanup_change_log($wrd, [word_names::TEST_ADD, word_names::TEST_ADD_TO]);

        $this->run_use_case($t);
        $this->run_formula($t);
        $this->run_scenarios($t);
    }

    /**
     * save the messages of the calculator editor scenarios (calculator_editor_scenarios.js) and undo them;
     * a cleared limit, a removed input and a moved input are only compared in the ui test yet (docs/llm/pending.md)
     * public to run the scenarios also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_scenarios(test_cleanup $t): void
    {
        $ts = 'db write update put scenario ';
        $this->run_add_input($t, $ts);
        $this->run_field_limit($t, $ts);
        $this->run_number($t, $ts);
        $t->subheader($ts . 'rename of an input field');
        $this->run_triple_change($t, test_files::UPDATE_PUT_PV_CAPACITY_RENAME,
            [triple_names::PV_CAPACITY_INPUT, triple_names::YIELD_FACTOR_ORDER]);
        $t->subheader($ts . 'row moved up');
        $this->run_order_change($t, test_files::UPDATE_PUT_AUTARKY_UP,
            [triple_names::SELF_CONSUMPTION_ORDER, triple_names::AUTARKY_ORDER]);
        $t->subheader($ts . 'row group moved down');
        $this->run_order_change($t, test_files::UPDATE_PUT_EMBODIED_EMISSION_DOWN,
            [triple_names::PRODUCTION_EMISSIONS_ORDER, triple_names::SAVING_ORDER]);
    }

    /**
     * the new input field of a new word, which the test user adds and deletes again
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     * @return void
     */
    private function run_add_input(test_cleanup $t, string $ts): void
    {
        $t->subheader($ts . 'new input field');
        $upd = new update_put();
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);

        $test_name = 'the input field of the test word is not yet in the database';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::TEST_ADD_INPUT, $chk_msg);
        $t->assert($test_name, $trp->id(), 0, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the new input field with the new word';
        $upd->put($this->scenario_message(test_files::UPDATE_PUT_ADD_INPUT, $t->usr1), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the input field links the inputs of the page to the new word';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::TEST_ADD_INPUT, $chk_msg);
        $to_name = $trp->get_to()->name();
        $t->assert($test_name, $to_name, word_names::TEST_ADD, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and follows the last input field';
        $ord = new triple($t->usr1);
        $ord->load_by_name(triple_names::TEST_ADD_INPUT_ORDER, $chk_msg);
        $ord_to_name = $ord->get_to()->name();
        $t->assert($test_name, $ord_to_name, triple_names::HOUSEHOLD_CONSUMPTION_INPUT, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo that deletes the order, the input field and the word';
        $wrd = new word($t->usr1);
        $wrd->load_by_name(word_names::TEST_ADD, $chk_msg);
        $upd->put($this->delete_message(test_files::UPDATE_PUT_ADD_INPUT, $t->usr1, [$ord, $trp, $wrd]), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the input field is deleted';
        $trp_del = new triple($t->usr1);
        $trp_del->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_del->id(), 0, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the word';
        $wrd_del = new word($t->usr1);
        $wrd_del->load_by_id($wrd->id(), $chk_msg);
        $t->assert($test_name, $wrd_del->id(), 0, test_base::TIMEOUT_LIMIT_DB);

        // a change log entry must never point to a deleted test row
        $t->cleanup_change_log($trp, [triple_names::TEST_ADD_INPUT, triple_names::TEST_ADD_INPUT_ORDER]);
        $t->cleanup_change_log($wrd, [word_names::TEST_ADD]);
    }

    /**
     * the new maximum of an input field, which the test user adds and deletes again
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     * @return void
     */
    private function run_field_limit(test_cleanup $t, string $ts): void
    {
        $t->subheader($ts . 'new limit of a field');
        $upd = new update_put();
        $msg = new user_message($t->usr1);
        $chk_msg = new user_message($t->usr1);

        $test_name = 'the PV capacity has no maximum yet';
        $val = new value($t->usr1);
        $found = $val->load_by_names(self::PV_CAPACITY_MAXIMUM_PHRASES, $chk_msg);
        $t->assert($test_name, $found, 0, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the new maximum of the PV capacity';
        $add = $upd->put($this->scenario_message(test_files::UPDATE_PUT_PV_CAPACITY_MAXIMUM, $t->usr1), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the maximum is in the database';
        $val_id = $add[json_fields::VALUES][0][json_fields::ID] ?? 0;
        // the global cleanup removes the value if a later test fails before the undo
        $t->test_val_ids[] = $val_id;
        $val = new value($t->usr1);
        $val->load_by_id($val_id, $chk_msg);
        $t->assert($test_name, $val->number(), self::PV_CAPACITY_MAXIMUM, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo that deletes the maximum';
        // the phrases of the value are kept to remove the change log of the value after its delete
        $val->load_phrases($chk_msg);
        $val_grp = $val->grp();
        $upd->put($this->delete_message(test_files::UPDATE_PUT_PV_CAPACITY_MAXIMUM, $t->usr1, [$val]), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the PV capacity has no maximum again';
        $val_del = new value($t->usr1);
        $found = $val_del->load_by_names(self::PV_CAPACITY_MAXIMUM_PHRASES, $chk_msg);
        $t->assert($test_name, $found, 0, test_base::TIMEOUT_LIMIT_DB);

        $t->cleanup_change_log_value($val_grp, $val_grp->phrase_list());
    }

    /**
     * the changed number of an input field by a user who does not own the value and its undo with
     * the number of another user
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     * @return void
     */
    private function run_number(test_cleanup $t, string $ts): void
    {
        $t->subheader($ts . 'number of a field');
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std = new value($t->usr1);
        $std->load_by_names(self::PV_CAPACITY_PHRASES, $std_msg);

        $test_name = 'put the changed number of the PV capacity';
        $json = $this->scenario_message(test_files::UPDATE_PUT_PV_CAPACITY_NUMBER, $usr);
        $json[json_fields::BODY][json_fields::VALUES][0][json_fields::ID] = (string)$std->id();
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the changed number';
        $val = new value($usr);
        $val->load_by_id($std->id(), $chk_msg);
        $t->assert($test_name, $val->number(), self::PV_CAPACITY_NUMBER, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the number before the change';
        $val_other = new value($t->usr1);
        $val_other->load_by_id($std->id(), $std_msg);
        $t->assert($test_name, $val_other->number(), $std->number(), test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the number of the other user';
        $json[json_fields::BODY][json_fields::VALUES][0][json_fields::NUMBER] = $std->number();
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the number before the change again';
        $val = new value($usr);
        $val->load_by_id($std->id(), $chk_msg);
        $t->assert($test_name, $val->number(), $std->number(), test_base::TIMEOUT_LIMIT_DB);
        // the field that differs is the reason why the save keeps the own version of the user
        $test_name = '... with the same fields as the value of the other user';
        $usr_flds = $this->value_fields($val);
        $std_flds = $this->value_fields($std);
        $t->assert($test_name, $usr_flds, $std_flds);
        $test_name = '... and has no own version left';
        $t->assert_false($test_name, $val->has_usr_cfg());
    }

    /**
     * @param value $val the value loaded for a user
     * @return array the fields of the value that the save compares with the standard to keep or remove the own version of the user
     */
    private function value_fields(value $val): array
    {
        return [
            json_fields::NUMBER => $val->number(),
            json_fields::SOURCE_ID => $val->source_id(),
            json_fields::SHARE => $val->share_id(),
            json_fields::PROTECTION => $val->protection_id(),
            json_fields::EXCLUDED => $val->is_excluded()
        ];
    }

    /**
     * the changed triples of a scenario by a user who does not own them, which the user then sees but
     * another user not, and the undo with the triples of the other user
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message of the scenario e.g. test_files::UPDATE_PUT_AUTARKY_UP
     * @param array $names the names of the triples of the message before the change in the order of the message
     * @return void
     */
    private function run_triple_change(test_cleanup $t, string $file, array $names): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std_lst = $this->triples_by_name($names, $std_msg);
        $std_json = $this->triples_json($std_lst);

        $test_name = 'put the changed triples';
        $json = $this->scenario_message($file, $usr);
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES];
        foreach ($std_lst as $pos => $std) {
            $trp_json[$pos][json_fields::ID] = $std->id();
        }
        $json[json_fields::BODY][json_fields::TRIPLES] = $trp_json;
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the triples as the message has sent them';
        $std_ids = $this->triple_ids($std_lst);
        $usr_json = $this->triples_json($this->triples_by_id($std_ids, $chk_msg));
        $t->assert($test_name, $usr_json, $trp_json, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the triples before the change';
        $other_json = $this->triples_json($this->triples_by_id($std_ids, $std_msg));
        $t->assert($test_name, $other_json, $std_json, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the triples of the other user';
        $json[json_fields::BODY][json_fields::TRIPLES] = $std_json;
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the triples before the change again';
        $usr_lst = $this->triples_by_id($std_ids, $chk_msg);
        $usr_json = $this->triples_json($usr_lst);
        $t->assert($test_name, $usr_json, $std_json, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $own_names = $this->own_versions($usr_lst);
        $t->assert($test_name, $own_names, []);
    }

    /**
     * the moved items of a scenario by a user who does not own the order triples: the message removes
     * the order triples of the moved items for the user and adds the ones of the new order, which the
     * user then sees but another user not; the undo removes the added order triples and adds the removed
     * ones again, like the editor does if the user moves the items back
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message of the scenario e.g. test_files::UPDATE_PUT_AUTARKY_UP
     * @param array $names the names of the order triples that the message removes in the order of its delete list
     * @return void
     */
    private function run_order_change(test_cleanup $t, string $file, array $names): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std_lst = $this->triples_by_name($names, $std_msg);
        $std_ids = $this->triple_ids($std_lst);

        $test_name = 'put the order triples of the moved items';
        $json = $this->scenario_message($file, $usr);
        foreach ($std_ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::DELETE][$pos][json_fields::ID] = $id;
        }
        $put = $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the added order triples with the links of the message';
        $add_ids = array_column($put[json_fields::TRIPLES] ?? [], json_fields::ID);
        $add_lst = $this->triples_by_id($add_ids, $chk_msg);
        $add_links = $this->links_json($add_lst);
        $msg_links = $json[json_fields::BODY][json_fields::TRIPLES];
        $t->assert($test_name, $add_links, $msg_links, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and no longer the order triples before the move';
        $shown = $this->shown_names($this->triples_by_id($std_ids, $chk_msg));
        $t->assert($test_name, $shown, [], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the order triples before the move';
        $shown = $this->shown_names($this->triples_by_id($std_ids, $std_msg));
        $t->assert($test_name, $shown, $names, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo that removes the added order triples and adds the ones before the move again';
        $undo = $this->delete_message($file, $usr, $add_lst);
        $undo[json_fields::BODY][json_fields::TRIPLES] = [];
        foreach ($std_lst as $std) {
            $undo[json_fields::BODY][json_fields::TRIPLES][] = [json_fields::NAME => $std->name()] + $this->link_json($std);
        }
        $upd->put($undo, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the order triples before the move again';
        $usr_lst = $this->triples_by_id($std_ids, $chk_msg);
        $shown = $this->shown_names($usr_lst);
        $t->assert($test_name, $shown, $names, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version of them left';
        $own_names = $this->own_versions($usr_lst);
        $t->assert($test_name, $own_names, []);
        $test_name = '... and the added order triples are deleted';
        $shown = $this->shown_names($this->triples_by_id($add_ids, $chk_msg));
        $t->assert($test_name, $shown, [], test_base::TIMEOUT_LIMIT_DB);

        // a change log entry must never point to a deleted test row
        if ($add_lst != []) {
            $t->cleanup_change_log($add_lst[0], $this->triple_names($add_lst));
        }
    }

    /**
     * @param array $names the names of the triples
     * @param user_message $msg with the user who loads the triples
     * @return triple[] the triples of the user loaded by their name
     */
    private function triples_by_name(array $names, user_message $msg): array
    {
        $result = [];
        foreach ($names as $name) {
            $trp = new triple($msg->usr);
            $trp->load_by_name($name, $msg);
            $result[] = $trp;
        }
        return $result;
    }

    /**
     * @param array $ids the database ids of the triples
     * @param user_message $msg with the user who loads the triples
     * @return triple[] the triples as the user sees them
     */
    private function triples_by_id(array $ids, user_message $msg): array
    {
        $result = [];
        foreach ($ids as $id) {
            $trp = new triple($msg->usr);
            $trp->load_by_id($id, $msg);
            $result[] = $trp;
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the loaded triples
     * @return array the database ids of the triples
     */
    private function triple_ids(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            $result[] = $trp->id();
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the loaded triples
     * @return array the names of the triples found in the database, e.g. without the ones of a refused put
     */
    private function triple_names(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            if ($trp->id() != 0) {
                $result[] = $trp->name();
            }
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the triples loaded for a user
     * @return array the names of the triples that the user sees, so without the removed and the excluded ones
     */
    private function shown_names(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            if ($trp->id() != 0 and !$trp->is_excluded()) {
                $result[] = $trp->name();
            }
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the loaded triples
     * @return array the triples as the calculator editor sends them, with the phrases and the verb by their names
     */
    private function triples_json(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            $result[] = [json_fields::ID => $trp->id(), json_fields::NAME => $trp->name()] + $this->link_json($trp);
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the loaded triples
     * @return array the links of the triples as the calculator editor sends an added order triple, which has no name
     */
    private function links_json(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            $result[] = $this->link_json($trp);
        }
        return $result;
    }

    /**
     * @param triple $trp the loaded triple
     * @return array the phrases and the verb of the triple by their names
     */
    private function link_json(triple $trp): array
    {
        return [
            json_fields::EX_FROM => $trp->get_from()->name(),
            json_fields::EX_VERB => $trp->get_verb_name(),
            json_fields::EX_TO => $trp->get_to()->name()
        ];
    }

    /**
     * @param triple[] $trp_lst the triples loaded for a user
     * @return array the names of the triples that the user has changed for himself, empty if none
     */
    private function own_versions(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            if ($trp->has_usr_cfg()) {
                $result[] = $trp->name();
            }
        }
        return $result;
    }

    /**
     * the undo of a scenario that has added objects
     *
     * @param string $file the message of the scenario e.g. test_files::UPDATE_PUT_ADD_INPUT for the header
     * @param user $usr the user who sends the undo
     * @param array $dbo_lst the objects to delete in the order of the delete e.g. a triple before its phrases
     * @return array the undo message with only the delete list
     */
    private function delete_message(string $file, user $usr, array $dbo_lst): array
    {
        $lib = new library();
        $json = $this->scenario_message($file, $usr);
        $del_lst = [];
        foreach ($dbo_lst as $dbo) {
            $del_lst[] = [
                json_fields::OBJECT_CLASS => $lib->class_to_name($dbo::class),
                json_fields::ID => $dbo->id()
            ];
        }
        $json[json_fields::BODY] = [json_fields::DELETE => $del_lst];
        return $json;
    }

    /**
     * the message that the calculator editor has sent after a scenario (calculator_editor_scenarios.js)
     * with the user who sends it in this test
     *
     * @param string $file the message of the scenario e.g. test_files::UPDATE_PUT_ADD_INPUT
     * @param user $usr the user who sends the message
     * @return array the message as the editor has sent it, but with the user of the test
     */
    private function scenario_message(string $file, user $usr): array
    {
        $json = $this->message($file);
        $json[json_fields::USER_ID] = $usr->id();
        $json[json_fields::USER] = $usr->name();
        return $json;
    }

    /**
     * the expression change of a use case formula as the calculator editor sends it, by a user who
     * does not own the formula, and its undo with the loaded expression
     * public to run the formula change also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the normal test user
     * @return void
     */
    function run_formula(test_cleanup $t): void
    {
        $upd = new update_put();
        $ts = 'db write update put use case ';
        $t->subheader($ts . 'change and undo of a formula');
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);

        // a run that has stopped before the undo leaves the user overwrite, which the undo with
        // the standard expression of another user removes
        $frm = new formula($usr);
        $frm->load_by_name(formula_names::AUTARKY, $chk_msg);
        if (!str_contains($frm->get_usr_text(), formula_names::AUTARKY_OPERAND_REMOVED)) {
            $test_name = 'the user overwrite left by an earlier run is removed by the undo';
            $frm_std = new formula($t->usr1);
            $frm_std->load_by_id($frm->id(), $chk_msg);
            $upd->put($this->formula_message($frm, $frm_std->get_usr_text()), $msg);
            $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
            $msg->reset();
            $frm = new formula($usr);
            $frm->load_by_name(formula_names::AUTARKY, $chk_msg);
        }
        $exp_original = $frm->get_usr_text();
        $test_name = 'the formula of the use case uses the operand that the change removes';
        $t->assert_text_contains($test_name, $exp_original, formula_names::AUTARKY_OPERAND_REMOVED, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the expression change of the calculator editor';
        $put_body = $upd->put($this->formula_message($frm), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = '... and the answer confirms the change';
        $frm_res = $put_body[json_fields::FORMULAS][0] ?? [];
        $t->assert($test_name, $frm_res[json_fields::ACTION] ?? '', update_put::CHANGED);
        $test_name = '... as the own version of the user';
        $t->assert_true($test_name, $frm_res[json_fields::USER_OVERWRITE] ?? false);
        $test_name = 'the database has the changed expression without the removed operand';
        $frm_chk = new formula($usr);
        $frm_chk->load_by_id($frm->id(), $chk_msg);
        $t->assert_text_not_contains($test_name, $frm_chk->get_usr_text(), formula_names::AUTARKY_OPERAND_REMOVED, test_base::TIMEOUT_LIMIT_DB);
        // the latex is derived from the expression, so the save updates it as well
        $test_name = '... and the latex follows the changed expression';
        $t->assert_text_not_contains($test_name, $frm_chk->get_latex() ?? '', formula_names::AUTARKY_OPERAND_REMOVED, test_base::TIMEOUT_LIMIT_DB);
        // negative: the message changes only the expression, so e.g. the description of the formula stays
        $test_name = '... but keeps the description of the database';
        $t->assert($test_name, $frm_chk->get_description(), $frm->get_description());
        // negative: the change is a user overwrite, so another user still gets the original expression
        $test_name = '... but another user still has the original expression';
        $frm_other = new formula($t->usr1);
        $frm_other->load_by_id($frm->id(), $chk_msg);
        $t->assert($test_name, $frm_other->get_usr_text(), $exp_original, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo of the expression change';
        $upd->put($this->formula_message($frm, $exp_original), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the database has the original expression again';
        $frm_chk = new formula($usr);
        $frm_chk->load_by_id($frm->id(), $chk_msg);
        $t->assert($test_name, $frm_chk->get_usr_text(), $exp_original, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and no user overwrite is left';
        $t->assert_false($test_name, $frm_chk->has_usr_cfg());

        // negative: a formula is changed but never deleted by a message, so its delete is refused
        $test_name = 'the delete of a formula is refused';
        $lib = new library();
        $cls_name = $lib->class_to_name(formula::class);
        $json = $this->formula_message($frm);
        $json[json_fields::BODY] = [json_fields::DELETE => [[json_fields::OBJECT_CLASS => $cls_name, json_fields::ID => $frm->id()]]];
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::OBJECT_NOT_FOUND, [
            msg_id::VAR_CLASS_NAME => $cls_name,
            msg_id::VAR_ID => (string)$frm->id()
        ]);
        $t->assert_msg_false($test_name, $msg, $expected);
        $msg->reset();
        $test_name = '... and the formula is still in the database';
        $frm_chk = new formula($usr);
        $frm_chk->load_by_id($frm->id(), $chk_msg);
        $t->assert($test_name, $frm_chk->id(), $frm->id(), test_base::TIMEOUT_LIMIT_DB);
    }

    /**
     * the description change of a use case triple as the calculator editor has sent it, by a user
     * who does not own the triple, and its undo, which must restore the original description
     * public to run the use case also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the normal test user
     * @return void
     */
    function run_use_case(test_cleanup $t): void
    {
        $upd = new update_put();
        $ts = 'db write update put use case ';
        $t->subheader($ts . 'change and undo of a triple description');
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);

        // a run that has stopped before the undo leaves the user overwrite, which the logged undo removes
        $trp = new triple($usr);
        $trp->load_by_name(triple_names::PV_CAPACITY_LABEL, $chk_msg);
        if ($trp->get_description() != triple_names::PV_CAPACITY_LABEL_COM) {
            $test_name = 'the user overwrite left by an earlier run is removed by the undo';
            $upd->put($this->use_case_message($trp, triple_names::PV_CAPACITY_LABEL_COM), $msg);
            $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
            $msg->reset();
            $trp = new triple($usr);
            $trp->load_by_name(triple_names::PV_CAPACITY_LABEL, $chk_msg);
        }
        $test_name = 'the triple of the use case has the original description';
        $t->assert($test_name, $trp->get_description(), triple_names::PV_CAPACITY_LABEL_COM, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the description change of the calculator editor';
        $put_body = $upd->put($this->use_case_message($trp), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        // the answer confirms only what the reload of the saved row shows, so that the editor can tell the user
        $test_name = '... and the answer confirms the change';
        $trp_res = $put_body[json_fields::TRIPLES][0] ?? [];
        $t->assert($test_name, $trp_res[json_fields::ACTION] ?? '', update_put::CHANGED);
        $test_name = '... with the old and the new description';
        $expected = [[
            json_fields::FIELD => json_fields::DESCRIPTION,
            json_fields::OLD_VALUE => triple_names::PV_CAPACITY_LABEL_COM,
            json_fields::NEW_VALUE => triple_names::PV_CAPACITY_LABEL_COM_CHANGED
        ]];
        $t->assert($test_name, $trp_res[json_fields::CHANGES] ?? [], $expected);
        $test_name = '... as the own version of the user';
        $t->assert_true($test_name, $trp_res[json_fields::USER_OVERWRITE] ?? false);
        $test_name = 'the database has the changed description';
        $trp_chk = new triple($usr);
        $trp_chk->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_chk->get_description(), triple_names::PV_CAPACITY_LABEL_COM_CHANGED, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the change of the description is logged';
        $log_ui = $t->log_last_ui_by_field($trp_chk, fields::FLD_DESCRIPTION, $trp_chk->id(), $chk_msg);
        $expected = $usr->name() . ' changed ' . msg_id::LOG_USER->value . ' to "'
            . triple_names::PV_CAPACITY_LABEL_COM_CHANGED . '" from "' . triple_names::PV_CAPACITY_LABEL_COM . '"';
        $t->assert($test_name, $log_ui->entry(), $expected, test_base::TIMEOUT_LIMIT_DB);
        // the user does not own the triple, so the change must be logged as its user overwrite
        $test_name = '... as a user overwrite';
        $t->assert_true($test_name, $log_ui->is_user_sandbox_change());
        $test_name = '... and the phrase data of the user who has changed it has the changed description';
        $phd_msg = new user_message($usr);
        $description = $this->phrase_data_description($phd_msg);
        $t->assert($test_name, $description, triple_names::PV_CAPACITY_LABEL_COM_CHANGED, test_base::TIMEOUT_LIMIT_IMPORT);
        $t->assert_msg($test_name, $phd_msg);
        $test_name = '... and the phraseData api answers for the user named by u';
        $phd_msg = new user_message($usr);
        $api_json = $this->phrase_data_api($t, $phd_msg);
        $t->assert($test_name, (int)($api_json[json_fields::USER_ID] ?? 0), $usr->id(), test_base::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... with the changed description';
        $t->assert($test_name, $this->label_description($api_json), triple_names::PV_CAPACITY_LABEL_COM_CHANGED);
        $t->assert_msg($test_name, $phd_msg);
        // negative: the change is a user overwrite, so another user still gets the original description
        $test_name = '... but the phrase data of another user has the original description';
        $phd_msg = new user_message($t->usr1);
        $description = $this->phrase_data_description($phd_msg);
        $t->assert($test_name, $description, triple_names::PV_CAPACITY_LABEL_COM, test_base::TIMEOUT_LIMIT_IMPORT);
        $t->assert_msg($test_name, $phd_msg);
        $test_name = '... and the phraseData api answers for the other user named by u';
        $phd_msg = new user_message($t->usr1);
        $api_json = $this->phrase_data_api($t, $phd_msg);
        $t->assert($test_name, (int)($api_json[json_fields::USER_ID] ?? 0), $t->usr1->id(), test_base::TIMEOUT_LIMIT_IMPORT);
        $test_name = '... with the original description';
        $t->assert($test_name, $this->label_description($api_json), triple_names::PV_CAPACITY_LABEL_COM);
        $t->assert_msg($test_name, $phd_msg);

        $test_name = 'put the undo of the description change';
        $upd->put($this->use_case_message($trp, triple_names::PV_CAPACITY_LABEL_COM), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the database has the original description again';
        $trp_chk = new triple($usr);
        $trp_chk->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_chk->get_description(), triple_names::PV_CAPACITY_LABEL_COM, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and no user overwrite is left';
        $t->assert_false($test_name, $trp_chk->has_usr_cfg());
    }

    /**
     * the description of the use case triple as the calculator editor receives it with the
     * phrase data of the example PV installation
     *
     * @param user_message $msg with the user of the phrase data and to report a problem of the load
     * @return string the description of the PV capacity label or an empty string if the phrase data has no such triple
     */
    private function phrase_data_description(user_message $msg): string
    {
        $phd = new phrase_data();
        $phr = new phrase($msg->usr);
        $phr->load_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $vars = $phd->export_json($phr->id(), phrase_data_walks::FROM, false, $msg);
        return $this->label_description($vars);
    }

    /**
     * the phrase data of the example PV installation as the calculator editor receives it from
     * the phraseData api of this pod, which answers for the user named by url_var::USER,
     * because the test calls the api of its own pod
     *
     * @param test_cleanup $t the test environment that calls the api
     * @param user_message $msg with the user named by u and to report the reason of a failed api call
     * @return array the json answer of the api, empty if the answer is no json
     */
    private function phrase_data_api(test_cleanup $t, user_message $msg): array
    {
        $lib = new library();
        $phr = new phrase($msg->usr);
        $phr->load_by_name(triple_names::EXAMPLE_PV_INSTALLATION, $msg);
        $url = THIS_URL . url_var::API_PATH . $lib->class_to_api_route(phrase_data::class);
        $par = [
            url_var::ID => $phr->id(),
            url_var::PHRASE_DATA_WALK => url_var::PHRASE_DATA_WALK_FROM,
            url_var::USER => $msg->usr->id()
        ];
        $result = json_decode($t->api_call_timed(rest_ctrl::GET, $url, $par), true) ?? [];
        // the reason of the api e.g. an unknown phrase, so that the test shows it
        if (array_key_exists(json_fields::MSG, $result)) {
            $msg->add(msg_id::API_MESSAGE, [msg_id::VAR_JSON_TEXT => $result[json_fields::MSG]]);
        }
        return $result;
    }

    /**
     * @param array $vars the phrase data as the export or the api returns it
     * @return string the description of the PV capacity label or an empty string if the phrase data has no such triple
     */
    private function label_description(array $vars): string
    {
        $result = '';
        foreach ($vars[json_fields::TRIPLES] ?? [] as $trp_json) {
            if (($trp_json[json_fields::NAME] ?? '') == triple_names::PV_CAPACITY_LABEL) {
                $result = $trp_json[json_fields::DESCRIPTION] ?? '';
            }
        }
        return $result;
    }

    /**
     * the message that the calculator editor has sent for the description change of the use case triple
     * (calculator_editor_scenarios.js); the database ids differ by database, so the test sets the id of
     * the loaded triple and its user, but keeps the phrases and the verb by their names as the editor sends them
     *
     * @param triple $trp the triple of the message loaded by its name for the user of the message
     * @param string|null $description the description to send e.g. the loaded one for the undo, else the one of the message
     * @return array the message with the database ids of this database
     */
    private function use_case_message(triple $trp, ?string $description = null): array
    {
        $json = $this->scenario_message(test_files::UPDATE_PUT_PV_CAPACITY_LABEL, $trp->get_user());
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES][0];
        $trp_json[json_fields::ID] = $trp->id();
        if ($description !== null) {
            $trp_json[json_fields::DESCRIPTION] = $description;
        }
        $json[json_fields::BODY][json_fields::TRIPLES][0] = $trp_json;
        return $json;
    }

    /**
     * the database ids differ by database, so the test sets the id of the loaded formula and its
     * user in the message as the calculator editor has sent it
     *
     * @param formula $frm the formula of the message loaded by its name for the user of the message
     * @param string|null $usr_text the expression to send e.g. the loaded one for the undo, else the one of the message
     * @return array the message with the database ids of this database
     */
    private function formula_message(formula $frm, ?string $usr_text = null): array
    {
        $json = $this->scenario_message(test_files::UPDATE_PUT_AUTARKY_FORMULA, $frm->get_user());
        $frm_json = $json[json_fields::BODY][json_fields::FORMULAS][0];
        $frm_json[json_fields::ID] = $frm->id();
        if ($usr_text !== null) {
            $frm_json[json_fields::USR_TEXT] = $usr_text;
        }
        $json[json_fields::BODY][json_fields::FORMULAS][0] = $frm_json;
        return $json;
    }

    /**
     * @param string $filename the path of the update put message e.g. test_files::UPDATE_PUT_ADD
     * @return array the update put message
     */
    private function message(string $filename): array
    {
        return json_decode(file_get_contents($filename), true);
    }

    /**
     * @param msg_id $msg_id the expected message e.g. msg_id::PHRASE_NAME_NOT_FOUND
     * @param array $var_lst the expected vars of the message
     * @return string the translated text that the put is expected to report
     */
    private function expected_text(msg_id $msg_id, array $var_lst): string
    {
        $exp_msg = new user_message();
        $exp_msg->add($msg_id, $var_lst);
        return $exp_msg->text();
    }

    /**
     * the database ids differ by database, so the test adds them to the update message
     *
     * @param int $trp_id the database id of the triple
     * @param string $val_id the group id of the value as text, like the editor sends an id beyond the safe integer of javascript
     * @param string $vrb_name the name of the verb of the triple, which the message names instead of its id
     * @param string $typ_code_id the code id of the phrase type of the triple, which the message names instead of its id
     * @return array the update message with the database ids
     */
    private function update_message(int $trp_id, string $val_id, string $vrb_name, string $typ_code_id): array
    {
        $json = $this->message(test_files::UPDATE_PUT_UPDATE);
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES][0];
        $trp_json[json_fields::ID] = $trp_id;
        unset($trp_json[json_fields::VERB]);
        $trp_json[json_fields::EX_VERB] = $vrb_name;
        $trp_json[json_fields::TYPE_NAME] = $typ_code_id;
        $json[json_fields::BODY][json_fields::TRIPLES][0] = $trp_json;
        $json[json_fields::BODY][json_fields::VALUES][0][json_fields::ID] = $val_id;
        return $json;
    }

    /**
     * @param int|string $val_id the database id of the value, which is the id of its group
     * @param int $trp_id the database id of the triple
     * @param int $wrd_id the database id of the from word
     * @param int $wrd_to_id the database id of the to word
     * @return array the undo message with the database ids in the order of its delete list
     */
    private function undo_message(int|string $val_id, int $trp_id, int $wrd_id, int $wrd_to_id): array
    {
        $json = $this->message(test_files::UPDATE_PUT_UNDO);
        $ids = [$val_id, $trp_id, $wrd_id, $wrd_to_id];
        foreach ($ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::DELETE][$pos][json_fields::ID] = $id;
        }
        return $json;
    }

}
