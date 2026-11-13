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
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST . 'rest_ctrl.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'values.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
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
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
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
    const array PV_CAPACITY_MINIMUM_PHRASES = [triple_names::PV_CAPACITY, triples::FIELD_LIMIT_DOWN];
    // the number that the scenario pv_capacity_maximum types
    const float PV_CAPACITY_MAXIMUM = 100;
    // the order triples of the PV inputs that the scenarios pv_capacity_down and remove_input remove, in the order of the delete list
    const array PV_CAPACITY_DOWN_TRIPLES = [
        'PV installation input yield factor order',
        'PV installation input battery capacity order'
    ];
    const array PV_REMOVE_INPUT_TRIPLES = [
        'PV installation input battery capacity order',
        'PV installation input battery capacity',
        'PV installation input household consumption order'
    ];
    // the triples of the Swiss NPP vote (npp_ch_vote.json) that the remove actions remove, in the order of the delete list
    const array NPP_REMOVE_ARGUMENT_TRIPLES = [
        'new NPP argument supply gap',
        'new NPP argument competitiveness pro order'
    ];
    const array NPP_REMOVE_PRESET_TRIPLES = [
        'new NPP preset ETH low label',
        'new NPP preset ETH low hint',
        'new NPP preset ETH low',
        'new NPP preset BAK base order'
    ];
    // the triples of the Swiss NPP vote that the actions with another link remove, because a triple with another
    // link is another triple, together with the triples that name them, in the order of the delete list
    const string NPP_ARGUMENT_SUPPLY_GAP = 'supply gap supports lifting the NPP ban';
    const array NPP_ARGUMENT_SIDE_TRIPLES = [
        'new NPP argument competitiveness pro order',
        'supply gap argument label',
        'new NPP argument supply gap',
        self::NPP_ARGUMENT_SUPPLY_GAP
    ];
    const array NPP_VERDICT_RESULT_TRIPLES = [
        'new NPP verdict threshold order',
        'new NPP verdict result'
    ];
    const array NPP_SENSITIVITY_CELL_HINT_TRIPLES = [
        'new NPP sensitivity cell average cost order',
        'new NPP sensitivity cell average cost'
    ];
    // the verb that the refused link change sends for the first triple of the argument side
    const string NPP_ARGUMENT_SIDE_VERB = 'limits';
    // the phrases of the values of the Swiss NPP vote that the number and the clear actions change, one list per value of the message
    const array NPP_SENSITIVITY_PHRASES = [
        [word_names::ASSUMED, 'CHF per kW', 'construction cost 5000', 'new NPP sensitivity']
    ];
    const array NPP_PRESET_VALUE_PHRASES = [
        [word_names::ASSUMED, 'CHF per kW', 'Academies pessimistic scenario', 'overnight construction cost']
    ];
    const array NPP_VERDICT_SCALE_LIMIT_PHRASES = [
        [triples::FIELD_LIMIT_UP, 'new NPP verdict scale'],
        [triples::FIELD_LIMIT_DOWN, 'new NPP verdict scale']
    ];
    const array NPP_PRESET_CLEARED_PHRASES = [words::YEAR, 'construction time', 'Academies pessimistic scenario'];
    // the triples of the table definition of sector of revenue (table_editor_sample.json) that the table editor actions remove, in the order of the delete list
    const array TABLE_MOVE_COLUMN_FOLLOWERS = [
        'sector of revenue columns 2025 is follower of sector of revenue columns 2024',
        'sector of revenue columns growth is follower of sector of revenue columns 2025'
    ];
    const array TABLE_REMOVE_COLUMN_TRIPLES = [
        'sector of revenue columns confidence is follower of sector of revenue columns source',
        'sector of revenue columns confidence marginal',
        'sector of revenue columns confidence'
    ];
    const array TABLE_ORDER_RULE_LISTED = ['sector of revenue column order rule listed'];
    // the flag that the header_rotate action adds
    const string TABLE_HEADER_ROTATE = 'sector of revenue header rotate';
    // the row limit value and the number that the row_limit action types
    const array TABLE_ROW_LIMIT_PHRASES = [triple_names::SECTOR_OF_REVENUE, triples::SYSTEM_ROW_LIMIT];
    // the title triple with its description of the import and the one that the title action types
    const string TABLE_TITLE = 'sector of revenue title';
    const string TABLE_TITLE_COM = 'Revenue by sector';
    const string TABLE_TITLE_COM_CHANGED = 'Revenue by business area';
    // a source of the PV use case, which is owned by the user who has imported it, so that the change of another
    // user is saved as the own version of that user; a seed source without owner is changed for all users
    const string PV_SOURCE = 'KBOB energy systems 2022';
    // the sources of the Swiss NPP vote that the source rename and the source description actions change
    const string NPP_SOURCE = 'BAK economiesuisse study 2026';
    const string NPP_SOURCE_FRAUNHOFER = 'Fraunhofer ISE LCOE 2024 via Faktenfackel';

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
        $this->run_source($t);
        $this->run_scenarios($t);
        $this->run_table_editor($t);
        $this->run_npp_vote($t);
        $this->run_notes_and_sources($t);
    }

    /**
     * save the messages of the calculator editor actions on the arguments, the lead text, the verdict,
     * the sensitivity table and the presets of the Swiss NPP vote and undo them
     * public to run the actions also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_npp_vote(test_cleanup $t): void
    {
        $ts = 'db write update put NPP vote ';
        $descriptions = [
            test_files::SCENARIO_ARGUMENT_TEXT => 'argument text',
            test_files::SCENARIO_MORE_TEXT => 'hideable lead text',
            test_files::SCENARIO_VERDICT_LABEL => 'verdict label',
            test_files::SCENARIO_VERDICT_SCALE_NOTE => 'verdict scale and note',
            test_files::SCENARIO_PRESET_LABEL => 'preset label',
        ];
        foreach ($descriptions as $scenario => $section) {
            $t->subheader($ts . $section);
            $this->run_descriptions_change($t, $this->scenario_file($scenario));
        }
        $numbers = [
            test_files::SCENARIO_VERDICT_SCALE_LIMITS => self::NPP_VERDICT_SCALE_LIMIT_PHRASES,
            test_files::SCENARIO_SENSITIVITY_NUMBER => self::NPP_SENSITIVITY_PHRASES,
            test_files::SCENARIO_PRESET_VALUE => self::NPP_PRESET_VALUE_PHRASES,
        ];
        foreach ($numbers as $scenario => $phrase_lst) {
            $t->subheader($ts . 'number ' . $scenario);
            $this->run_number($t, $this->scenario_file($scenario), $phrase_lst);
        }
        $items = [
            test_files::SCENARIO_ADD_ARGUMENT => [],
            test_files::SCENARIO_REMOVE_ARGUMENT => self::NPP_REMOVE_ARGUMENT_TRIPLES,
            test_files::SCENARIO_SENSITIVITY_ADD_LEVEL => [],
            test_files::SCENARIO_PRESET_NEW_VALUE => [],
            test_files::SCENARIO_ADD_PRESET => [],
            test_files::SCENARIO_REMOVE_PRESET => self::NPP_REMOVE_PRESET_TRIPLES,
            // another link is a removed and an added triple
            test_files::SCENARIO_ARGUMENT_SIDE => self::NPP_ARGUMENT_SIDE_TRIPLES,
            test_files::SCENARIO_VERDICT_RESULT => self::NPP_VERDICT_RESULT_TRIPLES,
            test_files::SCENARIO_SENSITIVITY_CELL_HINT => self::NPP_SENSITIVITY_CELL_HINT_TRIPLES,
        ];
        foreach ($items as $scenario => $names) {
            $t->subheader($ts . 'items ' . $scenario);
            $this->run_order_change($t, $this->scenario_file($scenario), $names);
        }
        $t->subheader($ts . 'preset value cleared');
        $this->run_value_cleared($t, $this->scenario_file(test_files::SCENARIO_PRESET_VALUE_CLEARED),
            self::NPP_PRESET_CLEARED_PHRASES);
        $this->run_npp_links($t);
    }

    /**
     * another link for an existing triple is refused by the save (the do not update a link guard), because
     * another link is another triple (docs/llm/architecture.md); the editor sends it as a removed and an added
     * triple instead, which run_npp_vote saves
     * public to run the link check also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_npp_links(test_cleanup $t): void
    {
        $ts = 'db write update put NPP vote ';
        // the normal user does not own the triples of the seed, the system user does
        $users = ['by a user' => $t->usr_normal, 'by the owner' => $t->usr_system];
        foreach ($users as $by => $usr) {
            $t->subheader($ts . 'link refused ' . $by);
            $this->run_link_refused($t, $usr);
        }
    }

    /**
     * the argument of the supply gap sent with its id and another verb is refused, also for its owner,
     * so that the user still sees the argument with its link and has no own version of it
     *
     * @param test_cleanup $t the test environment with the test users
     * @param user $usr the user who sends the message, either the owner of the triple or not
     * @return void
     */
    private function run_link_refused(test_cleanup $t, user $usr): void
    {
        $upd = new update_put();
        $lib = new library();
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std = new triple($t->usr1);
        $std->load_by_name(self::NPP_ARGUMENT_SUPPLY_GAP, $std_msg);
        $std_json = $this->triples_json([$std]);
        $trp_json = $std_json[0];
        $trp_json[json_fields::EX_VERB] = self::NPP_ARGUMENT_SIDE_VERB;
        $json = $this->scenario_message($this->scenario_file(test_files::SCENARIO_ARGUMENT_SIDE), $usr);
        $json[json_fields::BODY] = [json_fields::TRIPLES => [$trp_json]];
        $expected = $this->expected_text(msg_id::LINK_CHANGE_REFUSED, [
            msg_id::VAR_CLASS_NAME => $lib->class_to_name(triple::class),
            msg_id::VAR_ID => (string)$std->id(),
            msg_id::VAR_NAME_FROM => $trp_json[json_fields::EX_FROM],
            msg_id::VAR_NAME_TO => $trp_json[json_fields::EX_TO]
        ]);

        $test_name = 'another link for an existing triple is refused';
        $upd->put($json, $msg);
        $t->assert_msg_false($test_name, $msg, $expected);
        $test_name = '... so the user still sees the triple with its link';
        $usr_lst = $this->triples_by_id([$std->id()], $chk_msg);
        $t->assert($test_name, $this->triples_json($usr_lst), $std_json, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version of it';
        $t->assert($test_name, $this->own_versions($usr_lst), []);
    }

    /**
     * save the messages of the note text, the source rename and the source description actions on the
     * Swiss NPP vote and undo them
     * public to run the actions also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_notes_and_sources(test_cleanup $t): void
    {
        $ts = 'db write update put notes and sources ';
        $t->subheader($ts . 'note text');
        $this->run_descriptions_change($t, test_files::UPDATE_PUT_NOTE_TEXT);
        $t->subheader($ts . 'source rename');
        $this->run_source_rename($t);
        $t->subheader($ts . 'source description');
        $this->run_source_description($t);
    }

    /**
     * the changed descriptions of triples by a user who does not own them, which the user then sees but
     * another user not, and the undo with the descriptions of the other user
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message of the action e.g. test_files::UPDATE_PUT_NOTE_TEXT
     * @return void
     */
    private function run_descriptions_change(test_cleanup $t, string $file): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $json = $this->scenario_message($file, $usr);
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES];
        $std_lst = $this->triples_by_name($this->message_names($trp_json), $std_msg);
        $trp_ids = $this->triple_ids($std_lst);
        $descriptions = $this->triple_descriptions($std_lst);

        $test_name = 'put the changed descriptions';
        foreach ($trp_ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::TRIPLES][$pos][json_fields::ID] = $id;
        }
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the descriptions of the message';
        $usr_descriptions = $this->triple_descriptions($this->triples_by_id($trp_ids, $chk_msg));
        $msg_descriptions = array_column($trp_json, json_fields::DESCRIPTION);
        $t->assert($test_name, $usr_descriptions, $msg_descriptions, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the descriptions before the change';
        $std_descriptions = $this->triple_descriptions($this->triples_by_id($trp_ids, $std_msg));
        $t->assert($test_name, $std_descriptions, $descriptions, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the descriptions of the other user';
        foreach ($descriptions as $pos => $description) {
            $json[json_fields::BODY][json_fields::TRIPLES][$pos][json_fields::DESCRIPTION] = $description;
        }
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the descriptions before the change again';
        $usr_lst = $this->triples_by_id($trp_ids, $chk_msg);
        $t->assert($test_name, $this->triple_descriptions($usr_lst), $descriptions, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert($test_name, $this->own_versions($usr_lst), []);
    }

    /**
     * the changed description of a source of the Swiss NPP vote by a user who does not own it, which the
     * user then sees but another user not, and the undo with the description of the other user
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    private function run_source_description(test_cleanup $t): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $json = $this->scenario_message(test_files::UPDATE_PUT_SOURCE_DESCRIPTION, $usr);
        $changed = $json[json_fields::BODY][json_fields::SOURCES][0][json_fields::DESCRIPTION];
        $src = new source($t->usr1);
        $src->load_by_name(self::NPP_SOURCE_FRAUNHOFER, $std_msg);
        $description = $src->get_description();

        $test_name = 'put the changed description of the source';
        $json[json_fields::BODY][json_fields::SOURCES][0][json_fields::ID] = $src->id();
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the changed description';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->get_description(), $changed, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the description before the change';
        $src_std = new source($t->usr1);
        $src_std->load_by_id($src->id(), $std_msg);
        $t->assert($test_name, $src_std->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the description of the other user';
        $json[json_fields::BODY][json_fields::SOURCES][0][json_fields::DESCRIPTION] = $description;
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the description before the change again';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert_false($test_name, $src_chk->has_usr_cfg());
    }

    /**
     * a value removed by a user who does not own it, which the user then no longer sees but another
     * user still, and the undo that includes the value again for the user
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message that removes the value e.g. of the scenario preset_value_cleared
     * @param array $phrases the names of the phrases of the removed value
     * @return void
     */
    private function run_value_cleared(test_cleanup $t, string $file, array $phrases): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std = new value($t->usr1);
        $std->load_by_names($phrases, $std_msg);
        $val_ids = [$std->id()];

        $test_name = 'put the removed value';
        $json = $this->scenario_message($file, $usr);
        $json[json_fields::BODY][json_fields::DELETE][0][json_fields::ID] = (string)$std->id();
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user no longer sees the value';
        $t->assert($test_name, $this->shown_numbers($val_ids, $chk_msg), [], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees it';
        $t->assert($test_name, $this->shown_numbers($val_ids, $std_msg), [$std->number()], test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo that includes the value again';
        $undo = $this->scenario_message(test_files::UPDATE_PUT_VALUE_CLEARED_UNDO, $usr);
        $undo[json_fields::BODY][json_fields::VALUES][0][json_fields::ID] = (string)$std->id();
        $undo[json_fields::BODY][json_fields::VALUES][0][json_fields::NUMBER] = $std->number();
        $upd->put($undo, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the value again with the same fields as the other user';
        $val = new value($usr);
        $val->load_by_id($std->id(), $chk_msg);
        $t->assert($test_name, $this->value_fields($val), $this->value_fields($std), test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert_false($test_name, $val->has_usr_cfg());
    }

    /**
     * the rename of a source by a user who does not own it, sent with the texts that name the source by
     * its placeholder, which the user then sees but another user not, and the undo with the texts of the
     * other user; the source and the texts before the change are loaded for the other user, so that an own
     * version left by an aborted run is found and removed by the undo
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    private function run_source_rename(test_cleanup $t): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $json = $this->scenario_message(test_files::UPDATE_PUT_SOURCE_RENAME, $usr);
        $src_json = $json[json_fields::BODY][json_fields::SOURCES][0];
        $trp_json = $json[json_fields::BODY][json_fields::TRIPLES];
        $src = new source($t->usr1);
        $src->load_by_name(self::NPP_SOURCE, $std_msg);
        $trp_lst = $this->triples_by_name($this->message_names($trp_json), $std_msg);
        $trp_ids = $this->triple_ids($trp_lst);
        $descriptions = $this->triple_descriptions($trp_lst);

        $test_name = 'put the renamed source with the texts that name it';
        $src_json[json_fields::ID] = $src->id();
        foreach ($trp_ids as $pos => $id) {
            $trp_json[$pos][json_fields::ID] = $id;
        }
        $json[json_fields::BODY][json_fields::SOURCES][0] = $src_json;
        $json[json_fields::BODY][json_fields::TRIPLES] = $trp_json;
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the new name of the source';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->name(), $src_json[json_fields::NAME], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the texts with the new name';
        $usr_descriptions = $this->triple_descriptions($this->triples_by_id($trp_ids, $chk_msg));
        $msg_descriptions = array_column($trp_json, json_fields::DESCRIPTION);
        $t->assert($test_name, $usr_descriptions, $msg_descriptions, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the name before the change';
        $src_std = new source($t->usr1);
        $src_std->load_by_id($src->id(), $std_msg);
        $t->assert($test_name, $src_std->name(), self::NPP_SOURCE, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the texts with the name before the change';
        $std_descriptions = $this->triple_descriptions($this->triples_by_id($trp_ids, $std_msg));
        $t->assert($test_name, $std_descriptions, $descriptions, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the name and the texts before the change';
        $json[json_fields::BODY][json_fields::SOURCES][0][json_fields::NAME] = self::NPP_SOURCE;
        foreach ($descriptions as $pos => $description) {
            $json[json_fields::BODY][json_fields::TRIPLES][$pos][json_fields::DESCRIPTION] = $description;
        }
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the name of the source before the change again';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->name(), self::NPP_SOURCE, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version of the source left';
        $t->assert_false($test_name, $src_chk->has_usr_cfg());
        $test_name = '... and none of the texts';
        $own_names = $this->own_versions($this->triples_by_id($trp_ids, $chk_msg));
        $t->assert($test_name, $own_names, []);
    }

    /**
     * @param triple[] $trp_lst the loaded triples
     * @return array the descriptions of the triples
     */
    private function triple_descriptions(array $trp_lst): array
    {
        $result = [];
        foreach ($trp_lst as $trp) {
            $result[] = $trp->get_description();
        }
        return $result;
    }

    /**
     * save the messages of the table editor actions on the definition of sector of revenue and undo them;
     * the changes of the imported definition are saved by a user who does not own it, so that the undo
     * restores the definition without a new database row
     * public to run the actions also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the test users
     * @return void
     */
    function run_table_editor(test_cleanup $t): void
    {
        $ts = 'db write update put table editor ';
        $this->run_table_refused($t, $ts);
        $t->subheader($ts . 'new column');
        $this->run_order_change($t, test_files::UPDATE_PUT_TABLE_ADD_COLUMN, []);
        $t->subheader($ts . 'column moved');
        $this->run_order_change($t, test_files::UPDATE_PUT_TABLE_MOVE_COLUMN, self::TABLE_MOVE_COLUMN_FOLLOWERS);
        $t->subheader($ts . 'column removed');
        $this->run_order_change($t, test_files::UPDATE_PUT_TABLE_REMOVE_COLUMN, self::TABLE_REMOVE_COLUMN_TRIPLES);
        $t->subheader($ts . 'column order rule');
        $this->run_order_change($t, test_files::UPDATE_PUT_TABLE_COLUMN_ORDER_RULE, self::TABLE_ORDER_RULE_LISTED);
        $t->subheader($ts . 'flag set');
        $this->run_order_change($t, test_files::UPDATE_PUT_TABLE_HEADER_ROTATE, []);
        $t->subheader($ts . 'row limit');
        $this->run_number($t, test_files::UPDATE_PUT_TABLE_ROW_LIMIT, [self::TABLE_ROW_LIMIT_PHRASES]);
        $t->subheader($ts . 'title');
        $this->run_description_change($t, test_files::UPDATE_PUT_TABLE_TITLE, self::TABLE_TITLE,
            self::TABLE_TITLE_COM, self::TABLE_TITLE_COM_CHANGED);
    }

    /**
     * a table editor message with a bad item is refused and changes nothing
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $ts the name of the test section
     * @return void
     */
    private function run_table_refused(test_cleanup $t, string $ts): void
    {
        $t->subheader($ts . 'refused flag');
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);

        $test_name = 'a flag with a verb that does not exist is refused';
        $json = $this->scenario_message(test_files::UPDATE_PUT_TABLE_HEADER_ROTATE, $usr);
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::EX_VERB] = verbs::TEST_ADD_NAME;
        $upd->put($json, $msg);
        $expected = $this->expected_text(msg_id::TRIPLE_VERB_NOT_FOUND, [msg_id::VAR_NAME => verbs::TEST_ADD_NAME]);
        $t->assert_msg_false($test_name, $msg, $expected);
        $test_name = '... and the flag is not added';
        $trp = new triple($usr);
        $trp->load_by_name(self::TABLE_HEADER_ROTATE, $chk_msg);
        $t->assert($test_name, $trp->id(), 0, test_base::TIMEOUT_LIMIT_DB);
    }

    /**
     * the changed description of a triple by a user who does not own it, which the user then sees but
     * another user not, and the undo with the description before the change
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message of the action e.g. test_files::UPDATE_PUT_TABLE_TITLE
     * @param string $name the name of the triple that the message changes
     * @param string $description the description of the triple before the change
     * @param string $changed the description that the message sends
     * @return void
     */
    private function run_description_change(
        test_cleanup $t,
        string       $file,
        string       $name,
        string       $description,
        string       $changed
    ): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);

        $test_name = 'the user sees the description before the change';
        $trp = new triple($usr);
        $trp->load_by_name($name, $chk_msg);
        $t->assert($test_name, $trp->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the changed description';
        $json = $this->scenario_message($file, $usr);
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::ID] = $trp->id();
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the changed description';
        $trp_chk = new triple($usr);
        $trp_chk->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_chk->get_description(), $changed, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the description before the change';
        $trp_std = new triple($t->usr1);
        $trp_std->load_by_id($trp->id(), $std_msg);
        $t->assert($test_name, $trp_std->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the description before the change';
        $json[json_fields::BODY][json_fields::TRIPLES][0][json_fields::DESCRIPTION] = $description;
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the description before the change again';
        $trp_chk = new triple($usr);
        $trp_chk->load_by_id($trp->id(), $chk_msg);
        $t->assert($test_name, $trp_chk->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert_false($test_name, $trp_chk->has_usr_cfg());
    }

    /**
     * save the messages of the calculator editor scenarios (calculator_editor_scenarios.js) and undo them
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
        $t->subheader($ts . 'number of a field');
        $this->run_number($t, test_files::UPDATE_PUT_PV_CAPACITY_NUMBER, [self::PV_CAPACITY_PHRASES]);
        $t->subheader($ts . 'cleared limit of a field');
        $this->run_value_cleared($t, $this->scenario_file(test_files::SCENARIO_PV_CAPACITY_MINIMUM_CLEARED),
            self::PV_CAPACITY_MINIMUM_PHRASES);
        $t->subheader($ts . 'label with an undone hint');
        $this->run_descriptions_change($t, $this->scenario_file(test_files::SCENARIO_PV_CAPACITY_LABEL_UNDO));
        $t->subheader($ts . 'rename of an input field');
        $this->run_triple_change($t, test_files::UPDATE_PUT_PV_CAPACITY_RENAME,
            [triple_names::PV_CAPACITY_INPUT, triple_names::YIELD_FACTOR_ORDER]);
        $t->subheader($ts . 'row moved up');
        $this->run_order_change($t, test_files::UPDATE_PUT_AUTARKY_UP,
            [triple_names::SELF_CONSUMPTION_ORDER, triple_names::AUTARKY_ORDER]);
        $t->subheader($ts . 'row group moved down');
        $this->run_order_change($t, test_files::UPDATE_PUT_EMBODIED_EMISSION_DOWN,
            [triple_names::PRODUCTION_EMISSIONS_ORDER, triple_names::SAVING_ORDER]);
        $t->subheader($ts . 'input field moved down');
        $this->run_order_change($t, $this->scenario_file(test_files::SCENARIO_PV_CAPACITY_DOWN),
            self::PV_CAPACITY_DOWN_TRIPLES);
        $t->subheader($ts . 'input field removed');
        $this->run_order_change($t, $this->scenario_file(test_files::SCENARIO_REMOVE_INPUT),
            self::PV_REMOVE_INPUT_TRIPLES);
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
        $upd->put($this->delete_message(test_files::UPDATE_PUT_PV_CAPACITY_MAXIMUM, $t->usr1, [$val]), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the PV capacity has no maximum again';
        $val_del = new value($t->usr1);
        $found = $val_del->load_by_names(self::PV_CAPACITY_MAXIMUM_PHRASES, $chk_msg);
        $t->assert($test_name, $found, 0, test_base::TIMEOUT_LIMIT_DB);

        // a change log entry must never point to a deleted test row, also if its phrases are no test rows
        $t->cleanup_change_log_of_deleted([$val]);
    }

    /**
     * the changed numbers of values by a user who does not own the values and their undo with
     * the numbers of another user
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message that changes the numbers e.g. test_files::UPDATE_PUT_PV_CAPACITY_NUMBER
     * @param array $phrase_lst the names of the phrases of each changed value in the order of the message
     * @return void
     */
    private function run_number(test_cleanup $t, string $file, array $phrase_lst): void
    {
        $upd = new update_put();
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);
        $std_msg = new user_message($t->usr1);
        $std_lst = $this->values_by_names($phrase_lst, $std_msg);
        $val_ids = $this->value_ids($std_lst);
        $std_numbers = $this->numbers($std_lst);
        $json = $this->scenario_message($file, $usr);
        // as float, because the message sends e.g. 2 for a number that the database returns as 2.0
        $msg_numbers = array_map('floatval', array_column($json[json_fields::BODY][json_fields::VALUES], json_fields::NUMBER));

        $test_name = 'put the changed numbers';
        foreach ($val_ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::VALUES][$pos][json_fields::ID] = (string)$id;
        }
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the changed numbers';
        $usr_numbers = $this->numbers($this->values_by_id($val_ids, $chk_msg));
        $t->assert($test_name, $usr_numbers, $msg_numbers, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the numbers before the change';
        $other_numbers = $this->numbers($this->values_by_id($val_ids, $std_msg));
        $t->assert($test_name, $other_numbers, $std_numbers, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo with the numbers of the other user';
        foreach ($std_numbers as $pos => $number) {
            $json[json_fields::BODY][json_fields::VALUES][$pos][json_fields::NUMBER] = $number;
        }
        $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        // the field that differs is the reason why the save keeps the own version of the user
        $test_name = 'the user sees the values before the change again with the same fields as the other user';
        $usr_lst = $this->values_by_id($val_ids, $chk_msg);
        $t->assert($test_name, $this->values_fields($usr_lst), $this->values_fields($std_lst), test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert($test_name, $this->own_value_ids($usr_lst), []);
    }

    /**
     * @param array $phrase_lst the names of the phrases of each value
     * @param user_message $msg with the user who loads the values
     * @return value[] the values of the user loaded by their phrases
     */
    private function values_by_names(array $phrase_lst, user_message $msg): array
    {
        $result = [];
        foreach ($phrase_lst as $phrases) {
            $val = new value($msg->usr);
            $val->load_by_names($phrases, $msg);
            $result[] = $val;
        }
        return $result;
    }

    /**
     * @param array $ids the database ids of the values, which are the ids of their groups
     * @param user_message $msg with the user who loads the values
     * @return value[] the values as the user sees them
     */
    private function values_by_id(array $ids, user_message $msg): array
    {
        $result = [];
        foreach ($ids as $id) {
            $val = new value($msg->usr);
            $val->load_by_id($id, $msg);
            $result[] = $val;
        }
        return $result;
    }

    /**
     * @param value[] $val_lst the loaded values
     * @return array the database ids of the values
     */
    private function value_ids(array $val_lst): array
    {
        $result = [];
        foreach ($val_lst as $val) {
            $result[] = $val->id();
        }
        return $result;
    }

    /**
     * @param value[] $val_lst the loaded values
     * @return array the numbers of the values
     */
    private function numbers(array $val_lst): array
    {
        $result = [];
        foreach ($val_lst as $val) {
            $result[] = $val->number();
        }
        return $result;
    }

    /**
     * @param array $ids the database ids of the values
     * @param user_message $msg with the user who loads the values
     * @return array the numbers of the values that the user sees, so without the removed and the excluded ones
     */
    private function shown_numbers(array $ids, user_message $msg): array
    {
        $result = [];
        foreach ($ids as $id) {
            $val = new value($msg->usr);
            $found = $val->load_by_id($id, $msg) != 0;
            if ($found and !$val->is_excluded()) {
                $result[] = $val->number();
            }
        }
        return $result;
    }

    /**
     * @param value[] $val_lst the values loaded for a user
     * @return array the fields of each value that the save compares with the standard
     */
    private function values_fields(array $val_lst): array
    {
        $result = [];
        foreach ($val_lst as $val) {
            $result[] = $this->value_fields($val);
        }
        return $result;
    }

    /**
     * @param value[] $val_lst the values loaded for a user
     * @return array the database ids of the values that the user has changed for himself, empty if none
     */
    private function own_value_ids(array $val_lst): array
    {
        $result = [];
        foreach ($val_lst as $val) {
            if ($val->has_usr_cfg()) {
                $result[] = $val->id();
            }
        }
        return $result;
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
     * the added and removed triples of a scenario e.g. the moved items, by a user who does not own the
     * removed triples: the message removes them for the user and adds the new ones, which the user then
     * sees but another user still sees the removed ones; the undo removes the added values, triples and
     * words and adds the removed triples again, like the editor does if the user moves the items back;
     * a triple of the message with its id is a changed triple, e.g. a hint next to a link change, whose
     * description the undo sets back
     *
     * @param test_cleanup $t the test environment with the test users
     * @param string $file the message of the scenario e.g. test_files::UPDATE_PUT_AUTARKY_UP
     * @param array $names the names of the triples that the message removes in the order of its delete list, the children first
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
        $json = $this->scenario_message($file, $usr);
        $msg_trp = $json[json_fields::BODY][json_fields::TRIPLES];
        $chg_pos = $this->changed_positions($msg_trp);
        $msg_new = $this->message_triples($msg_trp, false);
        $chg_names = $this->message_names($this->message_triples($msg_trp, true));
        $chg_lst = $this->triples_by_name($chg_names, $std_msg);

        $test_name = 'the named triples of the message are not yet in the database';
        $shown = $this->shown_names($this->triples_by_name($this->message_names($msg_new), $chk_msg));
        $t->assert($test_name, $shown, [], test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the changed triples';
        foreach ($std_ids as $pos => $id) {
            $json[json_fields::BODY][json_fields::DELETE][$pos][json_fields::ID] = $id;
        }
        foreach ($chg_pos as $i => $pos) {
            $json[json_fields::BODY][json_fields::TRIPLES][$pos][json_fields::ID] = $chg_lst[$i]->id();
        }
        $put = $upd->put($json, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the added values with the numbers of the message';
        $add_val = $this->added_values($put, $chk_msg);
        $add_val_ids = $this->value_ids($add_val);
        // the global cleanup removes the values if a later test fails before the undo
        $t->test_val_ids = array_merge($t->test_val_ids, $add_val_ids);
        $msg_numbers = array_map('floatval', array_column($json[json_fields::BODY][json_fields::VALUES] ?? [], json_fields::NUMBER));
        $t->assert($test_name, $this->numbers($add_val), $msg_numbers, test_base::TIMEOUT_LIMIT_DB);
        $test_name = 'the user sees the added triples with the links of the message';
        $add_ids = $this->added_ids($put[json_fields::TRIPLES] ?? []);
        $add_lst = $this->triples_by_id($add_ids, $chk_msg);
        $add_links = $this->links_json($add_lst);
        $msg_links = $this->message_links($msg_new);
        $t->assert($test_name, $add_links, $msg_links, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and no longer the removed triples';
        $shown = $this->shown_names($this->triples_by_id($std_ids, $chk_msg));
        $t->assert($test_name, $shown, [], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... but another user still sees the removed triples';
        $shown = $this->shown_names($this->triples_by_id($std_ids, $std_msg));
        $t->assert($test_name, $shown, $names, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo that removes the added values and triples and adds the removed triples again';
        // the values before the triples and words of their phrases, the children first, because the message
        // adds the parents first, and the words after the triples that use them
        $add_wrd = $this->added_words($put, $chk_msg);
        $undo = $this->delete_message($file, $usr, array_merge($add_val, array_reverse($add_lst), $add_wrd));
        $undo[json_fields::BODY][json_fields::TRIPLES] = [];
        foreach ($this->parents_first($std_lst) as $std) {
            $undo[json_fields::BODY][json_fields::TRIPLES][] = [json_fields::NAME => $std->name()] + $this->link_json($std);
        }
        foreach ($chg_lst as $chg) {
            $undo[json_fields::BODY][json_fields::TRIPLES][] = [
                json_fields::ID => $chg->id(),
                json_fields::DESCRIPTION => $chg->get_description()
            ];
        }
        $upd->put($undo, $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the removed triples again';
        $usr_lst = $this->triples_by_id($std_ids, $chk_msg);
        $shown = $this->shown_names($usr_lst);
        $t->assert($test_name, $shown, $names, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the changed triples with the description before the change';
        $usr_chg = $this->triples_by_id($this->triple_ids($chg_lst), $chk_msg);
        $t->assert($test_name, $this->triple_descriptions($usr_chg), $this->triple_descriptions($chg_lst), test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version of them left';
        $own_names = $this->own_versions(array_merge($usr_lst, $usr_chg));
        $t->assert($test_name, $own_names, []);
        $test_name = '... and the added triples are deleted';
        $shown = $this->shown_names($this->triples_by_id($add_ids, $chk_msg));
        $t->assert($test_name, $shown, [], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the added words';
        $wrd_names = $this->shown_word_names($add_wrd, $chk_msg);
        $t->assert($test_name, $wrd_names, [], test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the added values';
        $t->assert($test_name, $this->shown_numbers($add_val_ids, $chk_msg), [], test_base::TIMEOUT_LIMIT_DB);

        // a change log entry must never point to a deleted test row, also if it has no reserved test name
        $t->cleanup_change_log_of_deleted(array_merge($add_val, $add_lst, $add_wrd));
    }

    /**
     * @param array $trp_json_lst the triples of a message
     * @return array the positions of the changed triples, which the message names by their id
     */
    private function changed_positions(array $trp_json_lst): array
    {
        $result = [];
        foreach ($trp_json_lst as $pos => $trp_json) {
            if (key_exists(json_fields::ID, $trp_json)) {
                $result[] = $pos;
            }
        }
        return $result;
    }

    /**
     * @param array $trp_json_lst the triples of a message
     * @param bool $changed true for the changed triples with their id, false for the added triples without id
     * @return array the changed or the added triples of the message in their order
     */
    private function message_triples(array $trp_json_lst, bool $changed): array
    {
        $result = [];
        foreach ($trp_json_lst as $trp_json) {
            if (key_exists(json_fields::ID, $trp_json) == $changed) {
                $result[] = $trp_json;
            }
        }
        return $result;
    }

    /**
     * @param array $put_trp_lst the triples of the answer of a put
     * @return array the database ids of the triples that the put has added, not of the changed ones
     */
    private function added_ids(array $put_trp_lst): array
    {
        $result = [];
        foreach ($put_trp_lst as $trp_json) {
            if (($trp_json[json_fields::ACTION] ?? '') == update_put::ADDED) {
                $result[] = $trp_json[json_fields::ID];
            }
        }
        return $result;
    }

    /**
     * @param triple[] $trp_lst the removed triples
     * @return triple[] the triples in an order that adds a triple only after the removed triples that it links,
     *                  because the undo names the phrases of a triple, which the user must already see again
     */
    private function parents_first(array $trp_lst): array
    {
        $result = [];
        $open = $trp_lst;
        $added = true;
        while ($open != [] and $added) {
            $added = false;
            $open_names = $this->found_names($open);
            foreach ($open as $key => $trp) {
                $link = $this->link_json($trp);
                $from_open = in_array($link[json_fields::EX_FROM], $open_names);
                $to_open = in_array($link[json_fields::EX_TO], $open_names);
                if (!$from_open and !$to_open) {
                    $result[] = $trp;
                    unset($open[$key]);
                    $added = true;
                }
            }
        }
        // a cycle is not possible for triples, but would be reported by the put instead of an endless loop
        return array_merge($result, $open);
    }

    /**
     * @param array $put the answer of the put
     * @param user_message $msg with the user who loads the values
     * @return value[] the values that the put has added
     */
    private function added_values(array $put, user_message $msg): array
    {
        $result = [];
        foreach ($put[json_fields::VALUES] ?? [] as $val_json) {
            if (($val_json[json_fields::ACTION] ?? '') == update_put::ADDED) {
                $val = new value($msg->usr);
                $val->load_by_id($val_json[json_fields::ID], $msg);
                $result[] = $val;
            }
        }
        return $result;
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
     * @param triple[]|word[] $dbo_lst the loaded triples or words
     * @return array the names of the objects found in the database, e.g. without the ones of a refused put
     */
    private function found_names(array $dbo_lst): array
    {
        $result = [];
        foreach ($dbo_lst as $dbo) {
            if ($dbo->id() != 0) {
                $result[] = $dbo->name();
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
     * @param array $trp_json_lst the triples of a message
     * @return array the links of the triples without the given name, which the loaded triples are compared with
     */
    private function message_links(array $trp_json_lst): array
    {
        $result = [];
        foreach ($trp_json_lst as $trp_json) {
            $result[] = [
                json_fields::EX_FROM => $trp_json[json_fields::EX_FROM],
                json_fields::EX_VERB => $trp_json[json_fields::EX_VERB],
                json_fields::EX_TO => $trp_json[json_fields::EX_TO]
            ];
        }
        return $result;
    }

    /**
     * @param array $trp_json_lst the triples of a message
     * @return array the names of the triples that the message names, so without e.g. an order triple
     */
    private function message_names(array $trp_json_lst): array
    {
        $result = [];
        foreach ($trp_json_lst as $trp_json) {
            if (key_exists(json_fields::NAME, $trp_json)) {
                $result[] = $trp_json[json_fields::NAME];
            }
        }
        return $result;
    }

    /**
     * @param array $put the answer of the put
     * @param user_message $msg with the user who loads the words
     * @return word[] the words that the put has added, but not the existing words that the message names to make sure they exist
     */
    private function added_words(array $put, user_message $msg): array
    {
        $result = [];
        foreach ($put[json_fields::WORDS] ?? [] as $wrd_json) {
            if (($wrd_json[json_fields::ACTION] ?? '') == update_put::ADDED) {
                $wrd = new word($msg->usr);
                $wrd->load_by_id($wrd_json[json_fields::ID], $msg);
                $result[] = $wrd;
            }
        }
        return $result;
    }

    /**
     * @param word[] $wrd_lst the words loaded before e.g. a delete
     * @param user_message $msg with the user who loads the words
     * @return array the names of the words that the user still finds in the database
     */
    private function shown_word_names(array $wrd_lst, user_message $msg): array
    {
        $result = [];
        foreach ($wrd_lst as $wrd) {
            $wrd_chk = new word($msg->usr);
            $wrd_chk->load_by_id($wrd->id(), $msg);
            if ($wrd_chk->id() != 0) {
                $result[] = $wrd_chk->name();
            }
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
     * @param string $scenario the name of a calculator editor scenario e.g. test_files::SCENARIO_ARGUMENT_TEXT
     * @return string the path of the message that the calculator editor has sent after the scenario
     */
    private function scenario_file(string $scenario): string
    {
        return test_paths::WEB_CALCULATOR_EDITOR_RES . test_files::UI_PUT_PREFIX . $scenario . test_files::JSON;
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
        // one full phrase data load per user, because each takes about 15 seconds
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
     * the change of the description and the url of a source of the PV use case as the calculator editor sends it,
     * by a user who does not own the source, and its undo with the loaded fields
     * public to run the source change also alone from the selected tests
     *
     * @param test_cleanup $t the test environment with the normal test user
     * @return void
     */
    function run_source(test_cleanup $t): void
    {
        $upd = new update_put();
        $ts = 'db write update put ';
        $t->subheader($ts . 'change and undo of a source');
        $usr = $t->usr_normal;
        $msg = new user_message($usr);
        $chk_msg = new user_message($usr);

        $src = new source($usr);
        $src->load_by_name(self::PV_SOURCE, $chk_msg);
        $description = $src->get_description();
        $url = $src->url;

        $test_name = 'put the changed description and url of a source';
        $put_body = $upd->put($this->source_message($src, sources::TEST_DESCRIPTION_CHANGED, sources::TEST_URL_CHANGED), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = '... and the answer confirms the change';
        $src_res = $put_body[json_fields::SOURCES][0] ?? [];
        $t->assert($test_name, $src_res[json_fields::ACTION] ?? '', update_put::CHANGED);
        $test_name = '... as the own version of the user';
        $t->assert_true($test_name, $src_res[json_fields::USER_OVERWRITE] ?? false);
        $test_name = 'the user sees the changed description';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->get_description(), sources::TEST_DESCRIPTION_CHANGED, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and the changed url';
        $t->assert($test_name, $src_chk->url, sources::TEST_URL_CHANGED);
        // negative: the change is a user overwrite, so another user still gets the source as before
        $test_name = '... but another user still sees the description before the change';
        $src_other = new source($t->usr1);
        $src_other->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_other->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);

        $test_name = 'put the undo of the source change';
        $upd->put($this->source_message($src, $description, $url), $msg);
        $t->assert_msg($test_name, $msg, test_base::TIMEOUT_LIMIT_DB_MULTI);
        $msg->reset();
        $test_name = 'the user sees the description before the change again';
        $src_chk = new source($usr);
        $src_chk->load_by_id($src->id(), $chk_msg);
        $t->assert($test_name, $src_chk->get_description(), $description, test_base::TIMEOUT_LIMIT_DB);
        $test_name = '... and has no own version left';
        $t->assert_false($test_name, $src_chk->has_usr_cfg());
    }

    /**
     * @param source $src the source with its database id
     * @param string|null $description the description that the message sets
     * @param string|null $url the url that the message sets
     * @return array the update put message of the calculator editor that changes the description and the url of the source
     */
    private function source_message(source $src, ?string $description, ?string $url): array
    {
        $json = $this->scenario_message(test_files::UPDATE_PUT_SOURCE_DESCRIPTION, $src->get_user());
        $json[json_fields::BODY][json_fields::SOURCES] = [[
            json_fields::ID => $src->id(),
            json_fields::DESCRIPTION => $description,
            json_fields::URL => $url
        ]];
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
