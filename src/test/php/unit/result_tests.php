<?php

/*

    test/unit/result.php - unit testing of the FORMULA VALUE functions
    ---------------------------
  

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
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\group\group;
use Zukunft\ZukunftCom\main\php\cfg\group\group_list;
use Zukunft\ZukunftCom\main\php\cfg\group\result_id;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\result\result;
use Zukunft\ZukunftCom\main\php\cfg\result\result_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value_list;
use Zukunft\ZukunftCom\main\php\shared\const\fields\result_fields;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\result\result as result_ui;
use Zukunft\ZukunftCom\main\php\web\result\result_list as result_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_groups;
use Zukunft\ZukunftCom\test\php\create\test_results;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_GROUP . 'result_id.php';
include_once paths::MODEL_RESULT . 'result_list.php';
include_once paths::MODEL_VALUE . 'value_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_CONST_FIELDS . 'result_fields.php';
include_once paths::SHARED_ENUM . 'messages.php';

class result_tests
{

    function run(test_cleanup $t): void
    {


        // init
        $msg = new user_message();
        $msg_ui = new user_message_ui(); // a buffer for the frontend calls of this test
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_res = new test_results($t);
        $t_frm = new test_formulas($t);
        $t_grp = new test_groups($t);
        $t_wrd = new test_words($t);
        $t->name = 'result->';
        $t->resource_path = 'db/result/';

        // start the test section (ts)
        $ts = 'unit result ';
        $t->header($ts);

        $t->subheader($ts . 'sql creation');
        $res = $t_res->result_simple_1();
        $t->assert_sql_table_create($res);
        $t->assert_sql_index_create($res);
        $t->assert_sql_foreign_key_create($res);

        // check the sql to load a result by the id
        $res_prime = $t_res->result_prime();
        $res = $t_res->result();
        $res_main = $t_res->result_main();
        $res_big = $t_res->result_big();
        $t->assert_sql_by_id($sc, $res_prime);
        $t->assert_sql_by_id($sc, $res);
        $t->assert_sql_by_id($sc, $res_main);
        $t->assert_sql_by_id($sc, $res_big);
        // the load by group is checked for each result table, because the table name and its key
        // fields must always match e.g. the main table has no phrase_id_1 field
        $this->assert_sql_by_group($t, $db_con, $res_prime);
        $this->assert_sql_by_group($t, $db_con, $res);
        $this->assert_sql_by_group($t, $db_con, $res_main);
        $this->assert_sql_by_group($t, $db_con, $res_big);
        // a result of a calculation or an export has only the phrase list of its group, so the
        // table and its key fields must follow the phrase list and must not report the result as
        // prime with zero phrases (see sandbox_value::grp_key_id)
        $test_name = 'the query to load a result by a group without an id '
            . 'is the same as by the group with the id';
        $db_con->db_type = sql_db::POSTGRES;
        // the same phrases on both sides, but once without the group id
        $grp_no_id = $t_grp->group_16();
        $grp_no_id->set_id('');
        $qp_no_id = $t_res->result()->load_sql_by_grp($db_con->sql_creator(), $grp_no_id);
        $qp_id = $t_res->result()->load_sql_by_grp($db_con->sql_creator(), $t_grp->group_16());
        $t->assert($test_name, $qp_no_id->sql, $qp_id->sql);
        $this->assert_sql_by_formula_and_group($t, $db_con, $res);
        $this->assert_sql_by_formula_and_group_list($t, $db_con, $res);
        $this->assert_sql_load_std_by_group_id($t, $db_con, $res);


        $t->subheader($ts . 'sql load default statement');

        // sql to load the standard result by id
        $t->assert_sql_standard($sc, $res_prime);
        $t->assert_sql_user_changes($sc, $res_prime);

        $t->subheader($ts . 'result sql write');
        // result changes are not logged because potentially they can be reproduced
        // TODO check the move from prime and main if the source group does not fit the prime or main criterias (same for the formula id)
        $res_prime = $t_res->result_prime();
        $res_prime_max = $t_res->result_prime_max();
        $res_main = $t_res->result_main();
        $res_main_max = $t_res->result_main_max();
        $res_filled = $t_res->result_main_filled();
        $res = $t_res->result();
        $res_big = $t_res->result_big();
        $t->assert_sql_insert($sc, $res_prime, [sql_type::STANDARD]);
        $t->assert_sql_insert($sc, $res_prime);
        $t->assert_sql_insert($sc, $res_prime, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res_prime_max);
        $t->assert_sql_insert($sc, $res_main);
        $t->assert_sql_insert($sc, $res_main_max);
        $t->assert_sql_insert($sc, $res_filled);
        $t->assert_sql_insert($sc, $res_filled, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res, [sql_type::USER]);
        $t->assert_sql_insert($sc, $res_big);
        $res = $t_res->result_incomplete();
        $t->assert_sql_insert_fail($sc, $res);
        // TODO Prio 0 activate db write
        // TODO add tests for text, time and geo values
        $res = $t_res->result();
        $db_res_prime = $res_prime->cloned(results::TV_FLOAT);
        $db_res_prime_max = $res_prime_max->cloned(results::TV_FLOAT);
        $db_res_main = $res_main->cloned(results::TV_FLOAT);
        $db_res_filled = $res_filled->cloned(results::TV_FLOAT);
        $db_res = $res->cloned(results::TV_FLOAT);
        $db_res_big = $res_big->cloned(results::TV_FLOAT);
        // TODO Prio 0 activate db write
        $t->assert_sql_update($sc, $res_prime, $db_res_prime, [sql_type::STANDARD]);
        $t->assert_sql_update($sc, $res_prime, $db_res_prime);
        $t->assert_sql_update($sc, $res_prime, $db_res_prime, [sql_type::USER]);
        $t->assert_sql_update($sc, $res_prime_max, $db_res_prime_max);
        $t->assert_sql_update($sc, $res_main, $db_res_main);
        $t->assert_sql_update($sc, $res_main, $db_res_main, [sql_type::STANDARD]);
        $t->assert_sql_update($sc, $res_filled, $db_res_filled);
        $t->assert_sql_update($sc, $res, $db_res);
        $t->assert_sql_update($sc, $res_big, $db_res_big);
        $t->assert_sql_update($sc, $res_big, $db_res_big, [sql_type::USER]);
        // TODO Prio 0 activate db write
        $t->assert_sql_delete($sc, $res_prime);
        $t->assert_sql_delete($sc, $res_prime, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $res);
        $t->assert_sql_delete($sc, $res, [sql_type::USER]);

        $t->subheader($ts . 'result base object handling');
        $res = $t_res->result_main_filled();
        $t->assert_reset($res);

        // the source group is stored as the bigint source_group_id of results_prime and
        // results_main, so only a group of up to 4 phrases fits; the import saves a result
        // with a bigger source group without it instead of dropping the calculated number
        // (see result_list::drop_unsupported_src_grp)
        $test_name = 'a source group of one phrase can be stored';
        $t->assert_true($test_name, $t_res->result_prime()->src_grp_is_storable());
        $test_name = '... and a source group of 16 phrases cannot';
        $t->assert_false($test_name, $t_res->result_src_grp_big()->src_grp_is_storable());

        // the result page shows the values, formulas and results used for the calculation; the
        // saved source phrases name these numbers exactly, so a number must carry all of them
        $test_name = 'the used numbers are selected by the source phrases of the calculation';
        $res_src = $t_res->result_main_max();
        [$phr_lst, $any_phrase] = $res_src->used_phrase_selection($msg);
        $t->assert($test_name, $phr_lst->name(), $res_src->source_group()->phrase_list()->name());
        $test_name = '... and only a number with all of them is used';
        $t->assert_false($test_name, $any_phrase);
        $msg->reset();

        // negative: a result saved without the source group (see drop_unsupported_src_grp) falls
        // back to its own phrases, where any match makes a number related, so that the page of
        // such a result is not empty
        $test_name = 'without a source group the used numbers are selected by the result phrases';
        $res_no_src = $t_res->result_simple();
        [$phr_lst_fb, $any_fallback] = $res_no_src->used_phrase_selection($msg);
        $t->assert($test_name, $phr_lst_fb->name(), $res_no_src->grp()->phrase_list()->name());
        $test_name = '... where a number with any of them is related';
        $t->assert_true($test_name, $any_fallback);
        $msg->reset();

        // a calculation that has used nothing sends the empty lists, because only an empty list
        // tells the page that nothing has been used, whereas a missing list says that the result
        // has not been asked for it (see ui_list::values_used)
        $test_name = 'a result that has used nothing sends an empty list of used values';
        $res_none = $t_res->result_main_max();
        $res_none->values_used = new value_list($t->usr1);
        $res_none->formulas_used = new formula_list($t->usr1);
        $res_none->results_used = new result_list($t->usr1);
        $none_json = json_decode($res_none->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED], $msg), true);
        $t->assert_true($test_name, array_key_exists(json_fields::VALUES, $none_json));
        $test_name = '... and of used formulas and results';
        $t->assert_true($test_name, array_key_exists(json_fields::FORMULAS, $none_json)
            and array_key_exists(json_fields::RESULTS, $none_json));
        $msg->reset();

        // negative: a result that has not been asked for the used numbers sends no list at all,
        // so that the page shows no column instead of a wrong "nothing used"
        $test_name = 'a result without the used lists sends no used values';
        $plain_json = json_decode($t_res->result_main_max()->api_json(
            [api_types::TEST_MODE, api_types::INCL_RELATED], $msg), true);
        $t->assert_false($test_name, array_key_exists(json_fields::VALUES, $plain_json));
        $msg->reset();

        // a row of a result table carries either the text group key or the phrase id columns
        // of a prime or main table, and a union of both kinds shows an empty key for the latter,
        // so the mapper must build the same group from both (see sandbox_value::set_grp_by_row)
        $test_name = 'a row with the group key sets the group of the result';
        $res = $t_res->result();
        $grp = $t_grp->group_16();
        $phr_id_flds = $res->id_fields_main(1, result_id::MAIN_PHRASES_ALL);
        $t->assert_true($test_name, $res->set_grp_by_row(
            [result_fields::FLD_ID => $grp->id()], $msg, result_fields::FLD_ID, $phr_id_flds));
        $t->assert($test_name . ' id', $res->grp()->id(), $grp->id());
        $test_name = 'a row with the phrase id columns sets the same group as the key would';
        $grp = $t_grp->group_prime_3();
        $db_row = [result_fields::FLD_ID => ''];
        foreach ($grp->phrase_list()->ids() as $pos => $phr_id) {
            $db_row[$phr_id_flds[$pos]] = $phr_id;
        }
        $t->assert_false($test_name, $res->set_grp_by_row($db_row, $msg, result_fields::FLD_ID, $phr_id_flds));
        $t->assert($test_name . ' id', $res->grp()->id(), $grp->id());
        // negative: a row without the key and without a phrase id gives an empty group
        $test_name = 'a row without the key and without phrase ids gives an empty group';
        $res->set_grp_by_row([result_fields::FLD_ID => ''], $msg, result_fields::FLD_ID, $phr_id_flds);
        $t->assert_true($test_name, $res->grp()->phrase_list()->is_empty());
        $msg->reset();


        $t->subheader($ts . 'display');

        // test phrase based default formatter
        // ... for big values
        $wrd_const = $t_wrd->word_math();
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->add($wrd_const->phrase());
        $res->grp()->set_phrase_list($phr_lst);
        $res->set_number(results::TV_INT);
        $t->assert('result->val_formatted test big numbers', $res->val_formatted($msg), "123'456");

        // ... for small values 12.35 instead of 12.34 due to rounding
        $res->set_number(results::TV_FLOAT);
        $t->assert('result->val_formatted test small numbers', $res->val_formatted($msg), "12.35");

        // ... for percent values
        $res = $t_res->result_pct();
        $t->assert('result->val_formatted test percent formatting', $res->val_formatted($msg), '1.23 %');


        $t->subheader($ts . 'im- and export');
        $t->assert_ex_and_import($t_res->result(), $t->usr_system);
        $t->assert_ex_and_import($t_res->result_main_filled(), $t->usr_system);
        $json_file = 'unit/result/result_import_part.json';
        $t->assert_json_file(new result($t->usr1), $json_file);


        $t->subheader($ts . 'html frontend');

        $res = $t_res->result_simple_1();
        $t->assert_api_to_ui($res, new result_ui());

        // the result default page shows the calculated number with its phrase group, a link
        // to the formula that calculated it and the time of the last calculation
        global $ui_sys;
        $form = new system_form();
        $res_page = $t_res->result_page_ui();
        $test_name = 'the result page shows the number behind the linked result phrases';
        $t->assert_text_contains($test_name, $form->show_result_value($res_page), ' = ');
        $test_name = 'the result page links the formula that calculated the result';
        $t->assert_text_contains($test_name, $form->show_result_formula($res_page), formula_names::SCALE_TO_SEC);
        $test_name = 'the result page shows the time of the last calculation';
        $t->assert_text_contains($test_name, $form->show_last_update($res_page),
            date_format(new DateTime(test_const::DUMMY_DATETIME), $ui_sys->cfg->date_time_format()));
        // in a table a calculated number links to the result page and not to the value page,
        // so that the reader gets to the formula that has calculated it
        $test_name = 'the number of a result links to the result page';
        $t->assert_text_contains($test_name, $res_page->value_edit($msg_ui),
            url_var::MASK . '=' . views::RESULT_ID);
        $test_name = '... and not to the value page';
        $t->assert_text_not_contains($test_name, $res_page->value_edit($msg_ui),
            url_var::MASK . '=' . views::VALUE_DEFAULT_ID);

        // the formula page lists its results, so a result must know which formula has
        // calculated it, and a phrase page its results, so a result must know its phrases
        $test_name = 'a result knows the formula that has calculated it';
        $frm_ui = new formula_ui($t_frm->formula()->api_json());
        $t->assert_true($test_name, $res_page->calculated_by_formula($frm_ui));
        $test_name = '... and not another formula';
        $t->assert_false($test_name, $res_page->calculated_by_formula($t_frm->formula_joule_ui()));
        $test_name = 'a result knows a phrase of its group';
        $t->assert_true($test_name, $res_page->has_phrase($res_page->grp->phr_lst()->lst()[0], $msg_ui));
        $test_name = '... and not a phrase outside its group';
        $t->assert_false($test_name, $res_page->has_phrase($t_wrd->zh_ui()->phrase(), $msg_ui));
        $test_name = 'the result list keeps the results of the given formula';
        $res_lst_ui = new result_list_ui();
        $res_lst_ui->add_result($res_page);
        $t->assert($test_name, $res_lst_ui->filter($msg_ui, $frm_ui)->count(), 1);
        $test_name = '... and drops the results of another formula';
        $t->assert($test_name, $res_lst_ui->filter($msg_ui, $t_frm->formula_joule_ui())->count(), 0);
        $msg_ui->reset();

        // a result that is not yet calculated shows the labels of the empty fields
        $res_plain = new result_ui($t_res->result_incomplete()->api_json([api_types::TEST_MODE]));
        $test_name = 'a result without a formula shows only the formula label';
        $t->assert($test_name, $form->show_result_formula($res_plain),
            $t->labeled(msg_id::FORM_SELECT_FORMULA, ''));
        // the last update is written by the system, so it shows no lonely label
        $test_name = 'a never calculated result shows no last update line';
        $t->assert($test_name, $form->show_last_update($res_plain), '');

    }

    /**
     * check the SQL statements creation to get the results by the phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_group(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $grp = $res->grp();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_grp($db_con->sql_creator(), $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_grp($db_con->sql_creator(), $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements creation to get the results
     * by the formula and phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_formula_and_group(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->id = 2;
        $grp = new group($t->usr1);
        $grp->set_id(3);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_frm_grp($db_con->sql_creator(), $frm, $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the SQL statements creation to get the results
     * by the formula and phrase group
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     * @return void true if all tests are fine
     */
    private function assert_sql_by_formula_and_group_list(test_cleanup $t, sql_db $db_con, result $res): void
    {
        // prepare
        $frm = new formula($t->usr1);
        $frm->id = 2;
        $grp1 = new group($t->usr1);
        $grp1->set_id(3);
        $grp2 = new group($t->usr1);
        $grp2->set_id(4);
        $lst = new group_list($t->usr1);
        $lst->add($grp1);
        $lst->add($grp2);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_by_frm_grp_lst($db_con->sql_creator(), $frm, $lst);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * similar to $t->assert_sql_all but calling load_by_group_id_sql instead of load_sql
     *
     * @param test_cleanup $t the forwarded testing object
     * @param sql_db $db_con does not need to be connected to a real database
     * @param result $res the user sandbox object e.g. a result
     */
    private function assert_sql_load_std_by_group_id(
        test_cleanup $t,
        sql_db $db_con,
        result $res): void
    {
        $grp = new group($t->usr1);
        $grp->set_id(7);

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_std_by_grp($db_con->sql_creator(), $grp);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $res->load_sql_std_by_grp($db_con->sql_creator(), $grp);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}