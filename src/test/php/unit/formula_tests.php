<?php

/*

    test/unit/formula.php - unit testing of the formula database and map functions
    ---------------------

    TODO move the sql tests to a separate class
  

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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_FORMULA . 'expression.php';
include_once html_paths::ELEMENT . 'element_group.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_tests
{
    function run(test_cleanup $t): void
    {


        // init
        $msg = new user_message();
        $msg_ui = new user_message_ui();
        $sc = new sql_creator();
        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $t->name = 'formula->';
        $t->resource_path = 'db/formula/';

        // start the test section (ts)
        $ts = 'unit formula map ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $frm = $t_frm->formula();
        $t->assert_sql_table_create($frm);
        $t->assert_sql_index_create($frm);
        $t->assert_sql_foreign_key_create($frm);

        $t->subheader($ts . 'sql read');
        $frm = new formula($t->usr1);
        $t->assert_sql_by_id($sc, $frm);
        $t->assert_sql_by_name($sc, $frm);

        $t->subheader($ts . 'sql read default and user changes by id');
        $frm = new formula($t->usr1);
        $frm->id = formula_names::SCALE_HOUR_ID;
        $t->assert_sql_standard($sc, $frm);
        $t->assert_sql_not_changed($sc, $frm);
        $t->assert_sql_user_changes($sc, $frm);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $frm);
        $t->assert_sql_changing_users_by_ids($sc, $frm);
        $this->assert_sql_user_changes_frm($t, $frm);

        $t->subheader($ts . 'sql read default by name');
        $frm = new formula($t->usr1);
        $frm->set_name(formula_names::SCALE_MIO_EXP);
        $t->assert_sql_standard_by_name($sc, $frm);

        $t->subheader($ts . 'sql write insert');
        $frm = $t_frm->formula();
        $t->assert_sql_insert($sc, $frm);
        $t->assert_sql_insert($sc, $frm, [sql_type::USER]);
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG, sql_type::USER]);
        $frm = $t_frm->formula_name_only();
        $t->assert_sql_insert($sc, $frm);
        $frm = $t_frm->formula_filled();
        $t->assert_sql_insert($sc, $frm, [sql_type::LOG]);
        $frm = $t_frm->formula_incomplete();
        $t->assert_sql_insert_fail($sc, $frm, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $frm = $t_frm->formula_name_only();
        $frm_renamed = $frm->cloned(formula_names::SYSTEM_TEST_RENAMED);
        $t->assert_sql_update($sc, $frm_renamed, $frm);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::USER]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG]);
        $t->assert_sql_update($sc, $frm_renamed, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $frm);
        $t->assert_sql_delete($sc, $frm, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $frm, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $frm, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'base object handling');
        $frm = $t_frm->formula_filled();
        $t->assert_reset($frm);
        $frm = $t_frm->formula_filled();
        $t->assert_db_ready($frm);
        $frm = $t_frm->formula_filled_not_db_ready();
        $t->assert_not_db_ready($frm);

        $t->subheader($ts . 'api');
        $frm = $t_frm->formula_filled();
        $t->assert_api_json($frm);
        $frm->include();
        $t->assert_api($frm, 'formula_body');

        $t->subheader($ts . 'frontend');
        $frm = $t_frm->formula();
        $t->assert_api_to_ui($frm, new formula_ui());

        $test_name = 'the url array contains the expression and the latex of the formula';
        $frm_ui = new formula_ui($frm->api_json());
        $url_arr = $frm_ui->to_url_array($msg_ui);
        $t->assert($test_name, $url_arr[url_var::USER_EXPRESSION], formula_names::SCALE_TO_SEC_EXP);
        $t->assert($test_name, $url_arr[url_var::LATEX], formula_names::SCALE_TO_SEC_LATEX);
        $test_name = 'the url array contains the set need all values flag and impact';
        $frm_ui->need_all_val = true;
        $frm_ui->impact = test_const::DUMMY_IMPACT;
        $url_arr = $frm_ui->to_url_array($msg_ui);
        $t->assert($test_name, $url_arr[url_var::NEED_ALL], '1');
        $t->assert($test_name, $url_arr[url_var::IMPACT], test_const::DUMMY_IMPACT);
        $test_name = 'the unset formula fields are excluded from the url array';
        $url_arr = new formula_ui()->to_url_array($msg_ui);
        $t->assert_contains_not($test_name, array_keys($url_arr), url_var::USER_EXPRESSION);
        $t->assert_contains_not($test_name, array_keys($url_arr), url_var::LATEX);
        $t->assert_contains_not($test_name, array_keys($url_arr), url_var::NEED_ALL);
        $t->assert_contains_not($test_name, array_keys($url_arr), url_var::IMPACT);

        // the confirm change preview labels a pending change with the changed db field, which
        // needs a complete db field to url var map (see db_fld_to_url)
        $test_name = 'every user-editable formula db field is mapped to its url var';
        $fld_to_url = $frm_ui->db_fld_to_url();
        $unmapped = array_diff($frm_ui->sandbox_fld_order(), array_keys($fld_to_url));
        $t->assert($test_name, array_values($unmapped), [fields::FLD_LAST_UPDATE]);
        $test_name = '... e.g. a pending latex change is labeled with the latex db field';
        $t->assert($test_name, $fld_to_url[formula_fields::FLD_LATEX] ?? '', url_var::LATEX);
        $test_name = 'the system-set last update time has no url var, because no form may post it';
        $t->assert_false($test_name, array_key_exists(fields::FLD_LAST_UPDATE, $fld_to_url));

        $t->subheader($ts . 'partial update expression handling');
        // the stored formula as loaded from the database, carrying its expression
        $frm_db = $t_frm->formula();
        $frm_db->ref_text = '{w1}=1';
        // a frontend partial update (e.g. a description-only edit of a predefined formula whose
        // expression field is not shown): convertToDb builds a fresh object from the api json that
        // carries the name but no expression, so ref_text and usr_text stay null
        $frm_upd = new formula($t->usr1);
        $frm_upd->set($frm_db->id(), $frm_db->name());
        $frm_upd->set_type_id($frm_db->type_id($msg), new user_message($t->usr1));
        $frm_upd->description = 'a new formula description';
        $msg = new user_message();
        $chg_lst = $frm_upd->db_fields_changed($frm_db, $msg);
        $test_name = 'a formula update without an expression is not blocked';
        $t->assert_true($test_name, $msg->is_ok());
        $test_name = 'a formula update without an expression keeps the stored expression';
        $t->assert_false($test_name, $chg_lst->has_name(formula_fields::FLD_FORMULA_TEXT));
        $test_name = 'a formula update still applies the changed description';
        $t->assert_true($test_name, $chg_lst->has_name(fields::FLD_DESCRIPTION));
        // a real expression change (the update carries a new expression) is still detected and written
        $frm_upd->ref_text = '{w1}=2';
        $chg_lst = $frm_upd->db_fields_changed($frm_db, $msg);
        $test_name = 'a changed formula expression is written';
        $t->assert_true($test_name, $chg_lst->has_name(formula_fields::FLD_FORMULA_TEXT));

        $t->subheader($ts . 'user overlay log for a boolean change');
        // when a user turns on all_values_needed for a base formula that has it set to false, the
        // change log function must declare the _all_values_needed_old parameter it references; a
        // boolean false old value must not be dropped by a loose null check (reproduces the crash
        // "Spalte _all_values_needed_old existiert nicht")
        $frm_on = $t_frm->formula_filled();
        $frm_on->ref_text = '{w1}=1';
        $frm_base = $t_frm->formula_filled();
        $frm_base->ref_text = '{w1}=1';
        $frm_base->need_all_val = false;
        $msg = new user_message();
        $par_lst = new sql_type_list([sql_type::INSERT, sql_type::LOG, sql_type::USER]);
        $sc->reset(sql_db::POSTGRES);
        $chg_lst = $frm_on->db_fields_changed($frm_base, $msg, $par_lst);
        $qp = $frm_on->sql_insert_switch($sc, $chg_lst, $frm_on->db_fields_all(), $msg, $par_lst);
        $test_name = 'the change log function declares the all_values_needed old parameter it uses';
        $t->assert_text_contains($test_name, $qp->sql, '_all_values_needed_old smallint');

        $t->subheader($ts . 'import assignment');

        // "assigned" names the input phrases of a formula (docs/llm/json_structure.md) and the
        // mapper links each of them, so that save_links can write them once they have an id
        // TODO Prio 2 assert the created links as soon as the in-memory link list has an accessor
        $test_name = 'a formula import with assigned phrases reports no problem';
        $msg = new user_message($t->usr1);
        $frm = new formula($t->usr1);
        $dto = $t_trm->dto_minute_and_second();
        $json = [
            json_fields::NAME => formula_names::SCALE_TO_SEC,
            json_fields::EXPRESSION => formula_names::SCALE_TO_SEC_EXP,
            json_fields::ASSIGNED => [word_names::MINUTE, words::SECOND]
        ];
        $frm->import_mapper($json, $msg, $dto);
        $t->assert_true($test_name, $msg->is_ok());

        // the mapper never creates a placeholder, so a phrase that the import does not define
        // is reported instead of being assigned to a phrase that can never be saved
        $test_name = 'an assigned phrase that the import does not define is reported';
        $msg = new user_message($t->usr1);
        $frm = new formula($t->usr1);
        $json[json_fields::ASSIGNED] = [word_names::MINUTE, word_names::PI];
        $frm->import_mapper($json, $msg, $dto);
        $t->assert_text_contains($test_name, $msg->text(), word_names::PI);

        $t->subheader($ts . 'im- and export');
        $t->assert_ex_and_import($t_frm->formula(), $t->usr_system);
        $t->assert_ex_and_import($t_frm->formula_filled(), $t->usr_system);
        $json_file = 'unit/formula/scale_second_to_minute.json';
        $t->assert_json_file(new formula($t->usr1), $json_file);

    }

    /**
     * TODO check the diff to assert_sql_user_changes
     *
     * @param test_cleanup $t the test environment
     * @param formula $frm the user sandbox object e.g. a word
     */
    private function assert_sql_user_changes_frm(test_cleanup $t, formula $frm): void
    {
        $db_con = new sql_db();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm->load_sql_user_changes_frm($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm->load_sql_user_changes_frm($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}