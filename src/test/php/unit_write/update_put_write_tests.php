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
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
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
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\export\phrase_data;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_cache_page;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\import\update_put;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
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
            $upd->put($this->use_case_message(test_files::UPDATE_PUT_PV_CAPACITY_LABEL_UNDO, $trp), $msg);
            $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
            $msg->reset();
            $trp = new triple($usr);
            $trp->load_by_name(triple_names::PV_CAPACITY_LABEL, $chk_msg);
        }
        $test_name = 'the triple of the use case has the original description';
        $t->assert($test_name, $trp->get_description(), triple_names::PV_CAPACITY_LABEL_COM, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the description change of the calculator editor';
        $upd->put($this->use_case_message(test_files::UPDATE_PUT_PV_CAPACITY_LABEL, $trp), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        // TODO Prio 0 why message reset without check? Report any unexpected message before reset
        $msg->reset();
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
        // TODO Prio 0 add an actual api call to check if the user is correctly taken from the url_par 'u'
        // negative: the change is a user overwrite, so another user still gets the original description
        $test_name = '... but the phrase data of another user has the original description';
        $phd_msg = new user_message($t->usr1);
        $description = $this->phrase_data_description($phd_msg);
        $t->assert($test_name, $description, triple_names::PV_CAPACITY_LABEL_COM, test_base::TIMEOUT_LIMIT_IMPORT);
        $t->assert_msg($test_name, $phd_msg);
        // TODO Prio 0 add an actual api call to check if the user is correctly taken from the url_par 'u'

        $test_name = 'put the undo of the description change';
        $upd->put($this->use_case_message(test_files::UPDATE_PUT_PV_CAPACITY_LABEL_UNDO, $trp), $msg);
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
        $result = '';
        foreach ($vars[json_fields::TRIPLES] ?? [] as $trp_json) {
            if (($trp_json[json_fields::NAME] ?? '') == triple_names::PV_CAPACITY_LABEL) {
                $result = $trp_json[json_fields::DESCRIPTION] ?? '';
            }
        }
        return $result;
    }

    /**
     * the database ids differ by database, so the test sets the ids of the loaded triple and its
     * user in the message as the calculator editor has sent it
     *
     * @param string $filename the path of the message e.g. test_files::UPDATE_PUT_PV_CAPACITY_LABEL
     * @param triple $trp the triple of the message loaded by its name for the user of the message
     * @return array the message with the database ids of this database
     */
    private function use_case_message(string $filename, triple $trp): array
    {
        $json = $this->message($filename);
        $json[json_fields::USER_ID] = $trp->get_user()->id();
        $json[json_fields::USER] = $trp->get_user()->name();
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES][0];
        $trp_json[json_fields::ID] = $trp->id();
        $trp_json[json_fields::FROM] = $trp->from_id();
        $trp_json[json_fields::TO] = $trp->to_id();
        $trp_json[json_fields::VERB] = $trp->get_verb_id();
        $json[json_fields::BODY][json_fields::TRIPLES][0] = $trp_json;
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
