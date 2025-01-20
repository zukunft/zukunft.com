<?php

namespace unit;

include_once API_WORD_PATH . 'triple.php';
include_once SHARED_PATH . 'triples.php';

use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\db\sql_creator;
use cfg\db\sql_type;
use html\word\triple as triple_dsp;
use cfg\db\sql_db;
use cfg\word\triple;
use shared\triples;
use test\test_cleanup;

class triple_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';

        $t->header('triple unit tests');

        $t->subheader('triple sql setup');
        $trp = $t->triple();
        $t->assert_sql_table_create($trp);
        $t->assert_sql_index_create($trp);
        $t->assert_sql_foreign_key_create($trp);

        $t->subheader('triple sql read');
        $trp = new triple($usr);
        $t->assert_sql_by_id($sc, $trp);
        $t->assert_sql_by_name($sc, $trp);
        $t->assert_sql_by_link($sc, $trp);
        $this->assert_sql_by_name_generated($sc, $trp, $t);

        $t->subheader('triple sql read standard and user changes by id');
        $trp = new triple($usr);
        $trp->set_id(2);
        $t->assert_sql_standard($sc, $trp);
        $t->assert_sql_user_changes($sc, $trp);

        $t->subheader('triple sql read standard by name');
        $trp = new triple($usr);
        $trp->set_name(triples::TN_PI);
        $t->assert_sql_standard($sc, $trp);

        $t->subheader('triple sql write insert');
        $trp = $t->triple();
        $t->assert_sql_insert($sc, $trp);
        $t->assert_sql_insert($sc, $trp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $trp_excl = $t->triple();
        $trp_excl->set_excluded(true);
        $t->assert_sql_insert($sc, $trp_excl);
        $trp_excl->description = '';
        $trp_excl->set_type('');
        $t->assert_sql_insert($sc, $trp_excl, [sql_type::LOG, sql_type::USER]);

        $t->subheader('triple sql write update');
        $trp_renamed = $trp->cloned_named(word_api::TN_RENAMED);
        $t->assert_sql_update($sc, $trp_renamed, $trp);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::USER]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG]);
        $t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_update($sc, $trp_excl, $trp, [sql_type::LOG]);

        $t->subheader('triple sql delete');
        // TODO activate db write
        $t->assert_sql_delete($sc, $trp);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER]);
        $t->assert_sql_delete($sc, $trp, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $trp, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $trp, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $trp, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader('view base object handling');
        $trp = $t->triple_filled_add();
        $t->assert_reset($trp);

        $t->subheader('triple api unit tests');
        $trp = $t->triple();
        $t->assert_api_json($trp);
        $t->assert_api($trp);

        $t->subheader('triple frontend unit tests');
        $trp = $t->triple_pi();
        $t->assert_api_to_dsp($trp, new triple_dsp());

        $t->subheader('triple import and export tests');
        $t->assert_ex_and_import($t->triple());
        $t->assert_ex_and_import($t->triple_filled_add());
        $json_file = 'unit/triple/pi.json';
        $t->assert_json_file(new triple($usr), $json_file);


        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_trp = $t->triple_name_only();
        $db_trp = $t->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp), false);

        $in_trp = $t->triple_link_only();
        $db_trp = $t->triple();
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
