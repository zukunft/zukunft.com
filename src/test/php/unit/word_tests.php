<?php

/*

    test/unit/word_tests.php - word unit tests
    ------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_db.php';
include_once WEB_WORD_PATH . 'word.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_type;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_named;
use cfg\word\word;
use cfg\word\word_db;
use html\word\word as word_dsp;
use shared\const\formulas;
use shared\const\words;
use shared\types\phrase_type as phrase_type_shared;
use test\test_cleanup;

class word_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;
        global $usr_sys;
        global $phr_typ_cac;

        // init
        $sc = new sql_creator();
        $t->name = 'word->';
        $t->resource_path = 'db/word/';

        // start the test section (ts)
        $ts = 'unit word ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $wrd = $t->word();
        $t->assert_sql_table_create($wrd);
        $t->assert_sql_index_create($wrd);
        $t->assert_sql_foreign_key_create($wrd);

        $t->subheader($ts . 'sql read');
        $wrd = new word($usr);
        $t->assert_sql_by_id($sc, $wrd);
        $t->assert_sql_by_name($sc, $wrd);
        $this->assert_sql_formula_name($t, $sc, $wrd);

        $t->subheader($ts . 'sql read default and user changes');
        $wrd = new word($usr);
        $wrd->set_id(words::CONST_ID);
        $t->assert_sql_standard($sc, $wrd);
        $t->assert_sql_not_changed($sc, $wrd);
        $t->assert_sql_user_changes($sc, $wrd);
        $t->assert_sql_changing_users($sc, $wrd);
        $this->assert_sql_view($t, $wrd);

        $t->subheader($ts . 'sql write insert');
        $wrd = new word($usr);
        $wrd->set_name(words::TEST_ADD);
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG]);
        $wrd = $t->word();
        $t->assert_sql_insert($sc, $wrd);
        $t->assert_sql_insert($sc, $wrd, [sql_type::USER]);
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $wrd_view = $t->word_view_set();
        $t->assert_sql_insert($sc, $wrd_view, [sql_type::LOG, sql_type::USER]);
        $wrd_no_view = $t->word_view_not_4_user();
        $t->assert_sql_save_fields($sc, $wrd_no_view, $wrd_view, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write update');
        $wrd_renamed = $wrd->cloned(words::TEST_RENAMED);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::USER]);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG]);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write update failed cases e.g. description update');
        $wrd = $t->word();
        $wrd->description = words::MATH_COM;
        $wrd_updated = $t->word();
        $wrd_updated->set_user($usr_sys);
        $wrd_updated->plural = words::TEST_RENAMED;
        $wrd_updated->description = words::TEST_RENAMED;
        $wrd_updated->type_id = $phr_typ_cac->id(phrase_type_shared::TIME);
        $t->assert_sql_update($sc, $wrd_updated, $wrd, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write update of all fields changed');
        $wrd_filled = $t->word_filled();
        $wrd_renamed->set_id($wrd->id());
        $t->assert_sql_update($sc, $wrd_renamed, $wrd_filled, [sql_type::LOG]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $wrd);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'base object handling');
        $wrd = $t->word_filled();
        $t->assert_reset($wrd);

        $t->subheader($ts . 'api');
        $wrd = $t->word();
        $t->assert_api_json($wrd);
        $wrd = $t->word_filled();
        $t->assert_api_json($wrd);
        $wrd->include();
        $t->assert_api($wrd, 'word_full');
        $wrd = $t->word();
        $t->assert_api($wrd, 'word_body');

        $t->subheader($ts . 'html frontend');
        $wrd = $t->word();
        $t->assert_api_to_dsp($wrd, new word_dsp());

        $t->subheader($ts . 'im- and export');
        // TODO check that all objects have a im and export test
        $t->assert_ex_and_import($t->word());
        $t->assert_ex_and_import($t->word_filled());
        $json_file = 'unit/word/second.json';
        $t->assert_json_file(new word($usr), $json_file);

        $t->subheader($ts . 'sync and fill');
        $test_name = 'check if the word fill function set all database fields and the view is updated';
        $wrd_imp = $t->word_filled();
        $wrd_db = $t->word();
        $wrd_db->fill($wrd_imp);
        $non_do_fld_names = $wrd_db->db_fields_changed($wrd_imp)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_do_fld_names, [word_db::FLD_VIEW]);
        $test_name = 'check if importing of just the admin protection does overwrite the protection in the database';
        $wrd_db = $t->word_filled();
        $wrd_imp = $t->word();
        $wrd_db_after = clone $wrd_db;
        $wrd_db_after->fill($wrd_imp);
        $non_do_fld_names = $wrd_db->db_fields_changed($wrd_db_after)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_do_fld_names, [sandbox::FLD_PROTECT]);
        $test_name = 'check if importing just the word name does not overwrite any database fields';
        $wrd_db = $t->word_filled();
        $wrd_imp = $t->word_name_only();
        $wrd_db_after = clone $wrd_db;
        $wrd_db_after->fill($wrd_imp);
        $non_do_fld_names = $wrd_db->db_fields_changed($wrd_db_after)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_do_fld_names, []);
        $test_name = 'check if the word id is filled up';
        $wrd_imp = $t->word();
        $wrd_imp->set_id(0);
        $wrd_db = $t->word();
        $wrd_imp->fill($wrd_db);
        $non_do_fld_names = $wrd_db->db_fields_changed($wrd_imp)->names();
        $t->assert($t->name . 'fill id: ' . $test_name, $non_do_fld_names, []);
        $test_name = 'check if description can be set to an empty string';
        $wrd_imp = $t->word();
        $wrd_imp->set_description('');
        $wrd_db = $t->word();
        $wrd_db->fill($wrd_imp);
        $non_do_fld_names = $wrd_db->db_fields_changed($wrd_imp)->names();
        $t->assert($t->name . 'fill id: ' . $test_name, $non_do_fld_names, [sandbox_named::FLD_DESCRIPTION]);

        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_wrd = $t->word_name_only();
        $db_wrd = $t->word_filled();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_wrd->needs_db_update($db_wrd), false);

    }

    /**
     * check the load SQL statements creation to get the word corresponding to the formula name
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_creator $sc does not need to be connected to a real database
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_formula_name(test_cleanup $t, sql_creator $sc, word $wrd): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $wrd->load_sql_by_formula_name($sc, formulas::SCALE_TO_SEC);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $wrd->load_sql_by_formula_name($sc, formulas::SCALE_TO_SEC);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the view
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_view(test_cleanup $t, word $wrd): void
    {
        $db_con = new sql_db();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $wrd->view_sql($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $wrd->view_sql($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}