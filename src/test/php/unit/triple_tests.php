<?php

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once html_paths::HELPER . 'data_object.php';
include_once html_paths::USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'url_var.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_phrases.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\helper\data_object as data_object_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t_trp = new test_triples($t);
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';

        // start the test section (ts)
        $ts = 'unit triple ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $trp = $t_trp->triple();
        $t->assert_sql_table_create($trp);
        $t->assert_sql_index_create($trp);
        $t->assert_sql_foreign_key_create($trp);

        $t->subheader($ts . 'sql read');
        $trp = new triple($usr);
        $t->assert_sql_by_id($sc, $trp);
        $t->assert_sql_by_name($sc, $trp);
        $t->assert_sql_by_link($sc, $trp);
        $this->assert_sql_by_name_generated($sc, $trp, $t);

        $t->subheader($ts . 'sql read standard and user changes by id');
        $trp = new triple($usr);
        $trp->id = 2;
        $t->assert_sql_standard($sc, $trp);
        $t->assert_sql_user_changes($sc, $trp);

        $t->subheader($ts . 'sql read standard by name');
        $trp = new triple($usr);
        $trp->set_name(triple_names::PI);
        $t->assert_sql_standard_by_name($sc, $trp);

        $t->subheader($ts . 'sql read standard by link');
        $trp = $t_trp->triple();
        $t->assert_sql_standard_by_type_link($sc, $trp);

        $t->subheader($ts . 'sql write insert');
        $trp = $t_trp->triple();
        $t->assert_sql_insert($sc, $trp);
        $t->assert_sql_insert($sc, $trp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $trp_excl = $t_trp->triple();
        $trp_excl->excluded = true;
        $t->assert_sql_insert($sc, $trp_excl);
        $trp_excl->description = '';
        $trp_excl->set_type('');
        $t->assert_sql_insert($sc, $trp_excl, [sql_type::LOG, sql_type::USER]);
        $trp = $t_trp->triple_incomplete();
        $t->assert_sql_insert_fail($sc, $trp, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $trp = $t_trp->triple();
        $trp_renamed = $trp->cloned_named(word_names::TEST_RENAMED);
        $t->assert_sql_update($sc, $trp_renamed, $trp);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::USER]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $trp_excl, $trp, [sql_type::LOG]);
        $t->assert_sql_update($sc, $trp_excl, $trp, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql delete');
        // TODO Prio 0 activate db write
        $t->assert_sql_delete($sc, $trp);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $trp, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $trp, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'view base object handling');
        $trp = $t_trp->triple_filled_add_name();
        $t->assert_reset($trp);

        $t->subheader($ts . 'rename routing');

        // a name change of a triple is a key update (so the duplicate name check of the save
        // runs), but it never identifies the database row: only a change of the link fields
        // (from, verb, to) forces a new row, so a rename keeps the id and the related values
        // (see sandbox::save and is_id_key_updated)
        $trp_db = $t_trp->triple();
        $trp_ren = $t_trp->triple();
        $trp_ren->set_name($trp_db->name() . ' renamed');
        $test_name = 'a triple rename is a key update for the duplicate check';
        $t->assert_true($test_name, $trp_ren->is_key_updated($trp_db));
        $test_name = 'a triple rename never changes the database row identity';
        $t->assert_false($test_name, $trp_ren->is_id_key_updated($trp_db));
        $trp_lnk = $t_trp->triple();
        $trp_lnk->set_from($t_trp->triple_pi()->phrase());
        $test_name = 'a changed from phrase changes the database row identity of a triple';
        $t->assert_true($test_name, $trp_lnk->is_id_key_updated($trp_db));

        $t->subheader($ts . 'no update diff treats an unset link end as empty');
        // under ex_def (the no_upd import mode) an unset from (id 0) is empty,
        // so filling it from the import is a fill-up, not a reported overwrite
        $trp_full = $t_trp->triple();
        $trp_empty_from = $t_trp->triple();
        $trp_empty_from->set_from(new phrase($usr));
        $test_name = 'diff_msg reports no overwrite when the db from is empty and ex_def is set';
        $diff = $trp_empty_from->diff_msg($trp_full, true);
        $t->assert_true($test_name, $diff->is_ok());
        // without ex_def the same unset from is a real, reported difference
        $test_name = 'diff_msg reports the difference for an empty db from without ex_def';
        $diff = $trp_empty_from->diff_msg($trp_full, false);
        $t->assert_false($test_name, $diff->is_ok());
        // two set from ends that differ stay a reported overwrite even under ex_def
        $trp_other_from = $t_trp->triple();
        $trp_other_from->set_from($trp_other_from->get_to());
        $test_name = 'diff_msg reports an overwrite when both from ends are set but differ';
        $diff = $trp_full->diff_msg($trp_other_from, true);
        $t->assert_false($test_name, $diff->is_ok());

        $t->subheader($ts . 'api');
        $trp = $t_trp->triple_filled_public();
        $t->assert_api_json($trp);
        $t->assert_api($trp);

        $t->subheader($ts . 'api mapping of an incomplete message');
        // an api message with only the id maps the id and does not fail
        $test_name = 'api_mapper with only the id keeps the id';
        $trp = new triple($usr);
        $trp->api_mapper([json_fields::ID => triple_names::MATH_CONST_ID], new user_message());
        $t->assert($test_name, $trp->id(), triple_names::MATH_CONST_ID);

        // an api message with only the name maps the name and leaves the id at 0
        $test_name = 'api_mapper with only the name keeps the name';
        $trp = new triple($usr);
        $trp->api_mapper([json_fields::NAME => triple_names::MATH_CONST], new user_message());
        $t->assert($test_name, $trp->name(), triple_names::MATH_CONST);
        $test_name = 'api_mapper with only the name leaves the id at 0';
        $t->assert($test_name, $trp->id(), 0);

        // an api message with neither the id nor the name maps nothing and leaves the id at 0
        $test_name = 'api_mapper with neither id nor name leaves the id at 0';
        $trp = new triple($usr);
        $trp->api_mapper([], new user_message());
        $t->assert($test_name, $trp->id(), 0);

        // an api message where the from, verb and to are present but null (an incomplete triple) maps
        // them to empty objects instead of throwing a TypeError; guards the phrase_from_api_json and
        // verb_from_api_json regression
        $test_name = 'api_mapper with a null from leaves the from phrase empty';
        $trp = new triple($usr);
        $trp->api_mapper([
            json_fields::ID => triple_names::MATH_CONST_ID,
            json_fields::FROM => null,
            json_fields::VERB => null,
            json_fields::TO => null
        ], new user_message());
        $t->assert($test_name, $trp->get_from()?->id() ?? 0, 0);
        $test_name = 'api_mapper with a null to leaves the to phrase empty';
        $t->assert($test_name, $trp->get_to()?->id() ?? 0, 0);
        $test_name = 'api_mapper with a null from/verb/to keeps the id';
        $t->assert($test_name, $trp->id(), triple_names::MATH_CONST_ID);

        $t->subheader($ts . 'frontend');
        $trp = $t_trp->triple_pi();
        $t->assert_api_to_ui($trp, new triple_ui());

        $t->subheader($ts . 'url mapping of phrases posted by name');
        // the datalist edit fields submit the shown phrase name instead of the id, so the url
        // mapper must resolve the name (e.g. 'Zurich') via the request cache to the phrase
        $t_phr = new test_phrases($t);
        $dto_ui = new data_object_ui();
        $dto_ui->phr_lst = $t_phr->list_ui();
        $phr_name = $dto_ui->phr_lst->lst()[0]->name();
        $trp_ui = new triple_ui();
        $map_msg = new user_message_ui();
        $trp_ui->url_mapper([
            url_var::ID => 1,
            url_var::NAME => triple_names::SYSTEM_TEST_ADD,
            url_var::PHRASE_FROM => $phr_name,
            url_var::WEIGHT => ''
        ], $map_msg, $dto_ui);
        $test_name = 'a from phrase posted by name is resolved via the request cache';
        $t->assert($test_name, $trp_ui->get_from()?->name(), $phr_name);
        $test_name = 'an empty weight posted by the edit form is kept as not set';
        $t->assert_true($test_name, $trp_ui->weight === null);

        $test_name = 'an unknown phrase name is reported to the user';
        $trp_ui = new triple_ui();
        $map_msg = new user_message_ui();
        $trp_ui->url_mapper([
            url_var::ID => 1,
            url_var::NAME => triple_names::SYSTEM_TEST_ADD,
            url_var::PHRASE_FROM => 'phrase name that does not exist'
        ], $map_msg, $dto_ui);
        $t->assert_true($test_name, $map_msg->has_msg_id(msg_id::PHRASE_NAME_NOT_FOUND));

        $test_name = 'a triple with only one linked phrase is rejected';
        $trp_ui = new triple_ui();
        $trp_ui->set_name(triple_names::SYSTEM_TEST_ADD);
        $trp_ui->set_from_by_id(1, $dto_ui);
        $val_msg = new user_message_ui();
        $t->assert_false($test_name, $trp_ui->input_valid($val_msg, url_var::CRUD_UPDATE));
        $test_name = '... and the user is told that both phrases are needed';
        $t->assert_true($test_name, $val_msg->has_msg_id(msg_id::TRIPLE_PHRASES_MISSING));

        $t->subheader($ts . 'frontend phrases_related round-trip');
        // build a target phrase ("Pi") that should appear in the triple's related list, and
        // wrap it in a one-entry json array. The frontend phrase_list api_mapper then turns
        // it into a phrase_list whose api_array round-trips back to the same json shape.
        $target_trp = $t_trp->triple_pi();
        $related_json = [[
            json_fields::OBJECT_CLASS => json_fields::CLASS_TRIPLE,
            json_fields::ID => $target_trp->id(),
            json_fields::NAME => $target_trp->name(),
        ]];
        $symbol_trp = $t_trp->triple_pi_symbol();
        $trp_json = json_decode($symbol_trp->api_json(), true);
        $trp_json[json_fields::PHRASES_RELATED] = $related_json;
        $trp_ui = new triple_ui(json_encode($trp_json));
        $test_name = 'triple ui api_mapper populates phrases_related from json';
        $t->assert_true($t->name . $test_name,
            $trp_ui->phr_lst !== null and !$trp_ui->phr_lst->is_empty());
        $test_name = 'triple ui api_array re-emits phrases_related';
        $t->assert_true($t->name . $test_name,
            array_key_exists(json_fields::PHRASES_RELATED, $trp_ui->api_array()));
        // negative: a triple without phrases_related in its json keeps the field null
        $bare_trp_ui = new triple_ui($symbol_trp->api_json());
        $test_name = 'triple ui phrases_related stays null when json key is absent';
        $t->assert_true($t->name . $test_name, $bare_trp_ui->phr_lst === null);
        $test_name = 'triple ui api_array omits phrases_related when null';
        $t->assert_true($t->name . $test_name,
            !array_key_exists(json_fields::PHRASES_RELATED, $bare_trp_ui->api_array()));

        $t->subheader($ts . 'import and export');
        $t->assert_ex_and_import($t_trp->triple(), $usr_sys);
        $t->assert_ex_and_import($t_trp->triple_filled_add_name(), $usr_sys);
        $json_file = 'unit/triple/pi.json';
        $t->assert_json_file(new triple($usr), $json_file);

        // the impact field is part of the triple im- and export
        // even if the impact is expected to be calculated internal
        // it is included in the im- and export for an initial value
        // e.g. if the calculation definition is not yet set 
        $trp = $t_trp->triple();
        $trp->set_impact(impacts::HIGH);
        $json_ex = $trp->export_json([], false);
        // the assert follows the json export above, so a page timeout is used to avoid a false timeout
        $t->assert($ts . 'export includes the impact', $json_ex[json_fields::IMPACT] ?? null, impacts::HIGH, $t::TIMEOUT_LIMIT_PAGE);
        // re-import the exported json and check that the impact is read back
        $trp_in = new triple($usr_sys);
        $trp_in->import_mapper($json_ex, new user_message($usr_sys), new data_object($usr_sys));
        $t->assert($ts . 'import reads the impact', $trp_in->impact, impacts::HIGH);


        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_trp = $t_trp->triple_name_only();
        $db_trp = $t_trp->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp), false);

        $in_trp = $t_trp->triple_link_only();
        $db_trp = $t_trp->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp), false);

    }

    /**
     * similar to assert_load_sql of the test base but for the standard (generated) triple name
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_creator $sc does not need to be connected to a real database
     * @param triple $trp the user sandbox object e.g. a word
     */
    private function assert_sql_by_name_generated(sql_creator $sc, triple $trp, test_cleanup $t): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $trp->load_sql_by_name_generated($sc, 'System test', $trp::class);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $trp->load_sql_by_name_generated($sc, 'System test', $trp::class);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

}
