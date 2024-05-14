<?php

namespace unit;

include_once API_WORD_PATH . 'triple.php';

use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\db\sql;
use cfg\db\sql_type;
use html\word\triple as triple_dsp;
use cfg\db\sql_db;
use cfg\triple;
use cfg\verb;
use test\test_cleanup;

class triple_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/pi.json';
        $usr->set_id(1);

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
        $this->assert_sql_by_name_generated($db_con, $trp, $t);

        $t->subheader('triple sql read default and user changes');
        // sql to load the triple by id
        $trp = new triple($usr);
        $trp->set_id(2);
        $t->assert_sql_standard($sc, $trp);
        $t->assert_sql_user_changes($sc, $trp);

        // sql to load the triple by name
        $trp = new triple($usr);
        $trp->set_name(triple_api::TN_PI);
        $t->assert_sql_standard($sc, $trp);

        $t->subheader('triple sql write');
        // insert
        $trp = $t->triple();
        $t->assert_sql_insert($sc, $trp);
        $t->assert_sql_insert($sc, $trp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $trp, [sql_type::LOG, sql_type::USER]);
        // update
        // TODO activate db write
        $trp_renamed = $trp->cloned(word_api::TN_RENAMED);
        $t->assert_sql_update($sc, $trp_renamed, $trp);
        //$t->assert_sql_update($sc, $trp_renamed, $trp, [sql_type::USER]);
        // TODO activate db write
        //$t->assert_sql_delete($sc, $trp);
        //$t->assert_sql_delete($sc, $trp, [sql_type::USER]);

        $t->subheader('API unit tests');

        $trp = new triple($usr);
        $trp->set(1, triple_api::TN_PI_NAME, triple_api::TN_PI, verb::IS, word_api::TN_READ);
        $trp->description = 'The mathematical constant Pi';
        $api_trp = $trp->api_obj();
        $t->assert($t->name . 'api->id', $api_trp->id(), $trp->id());
        $t->assert($t->name . 'api->name', $api_trp->name(), $trp->name());
        $t->assert($t->name . 'api->description', $api_trp->description, $trp->description);
        $t->assert($t->name . 'api->from', $api_trp->from()->name(), $trp->fob->obj()->name_dsp());
        $t->assert($t->name . 'api->to', $api_trp->to()->name(), $trp->tob->obj()->name_dsp());


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new triple($usr), $json_file);

        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_trp = $t->triple_name_only();
        $db_trp = $t->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp), false);

        $in_trp = $t->triple_link_only();
        $db_trp = $t->triple();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_trp->needs_db_update($db_trp), false);



        $t->subheader('HTML frontend unit tests');

        $trp = $t->dummy_triple_pi();
        $t->assert_api_to_dsp($trp, new triple_dsp());
    }

    /**
     * similar to assert_load_sql of the test base but for the standard (generated) triple name
     * check the object load by name SQL statements for all allowed SQL database dialects
     *
     * @param sql_db $db_con does not need to be connected to a real database
     * @param triple $trp the user sandbox object e.g. a word
     */
    private function assert_sql_by_name_generated(sql_db $db_con, triple $trp, test_cleanup $t): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $trp->load_sql_by_name_generated($db_con->sql_creator(), 'System test', $trp::class);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $trp->load_sql_by_name_generated($db_con->sql_creator(), 'System test', $trp::class);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}
