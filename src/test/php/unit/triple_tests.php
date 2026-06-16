<?php

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\web\word\triple as triple_ui;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
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

        $t->subheader($ts . 'api');
        $trp = $t_trp->triple_filled_public();
        $t->assert_api_json($trp);
        $t->assert_api($trp);

        $t->subheader($ts . 'frontend');
        $trp = $t_trp->triple_pi();
        $t->assert_api_to_ui($trp, new triple_ui());

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
        $t->assert($ts . 'export includes the impact', $json_ex[json_fields::IMPACT] ?? null, impacts::HIGH);
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
