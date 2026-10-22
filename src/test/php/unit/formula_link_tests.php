<?php

/*

  test/unit/formula_link.php - unit testing of the formula link functions
  --------------------------
  

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

include_once paths::MODEL_FORMULA . 'formula_link_type.php';
include_once paths::MODEL_FORMULA . 'formula_link_list.php';
include_once html_paths::FORMULA . 'formula_link.php';
include_once html_paths::FORMULA . 'formula_link_list.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\formula_link_types;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link as formula_link_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list as formula_link_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class formula_link_tests
{
    function run(test_cleanup $t): void
    {
        global $sys;

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_frm = new test_formulas($t);
        $msg = new user_message(); // a test is an entry point, so it creates the message the mappers report into
        $msg_ui = new user_message_ui();
        $t->name = 'formula_link->';
        $t->resource_path = 'db/formula/';

        // TODO Prio 2 use assert_sql_all if possible

        // start the test section (ts)
        $ts = 'unit formula link ';
        $t->header($ts);

        $t->subheader($ts . 'type sql setup');
        $frm_lnk_typ = new formula_link_type('');
        $t->assert_sql_table_create($frm_lnk_typ);
        $t->assert_sql_index_create($frm_lnk_typ);
        $frm_lnk = $t_frm->formula_link();
        $t->assert_sql_table_create($frm_lnk);
        $t->assert_sql_index_create($frm_lnk);
        $t->assert_sql_foreign_key_create($frm_lnk);

        $t->subheader($ts . 'sql user sandbox statement');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $flk = new formula_link($t->usr1);
        $t->assert_sql_by_id($sc, $flk);
        $t->assert_sql_by_link($sc, $flk);


        $t->subheader($ts . 'sql load default statement');

        // sql to load the standard formula link by id
        $lnk = new formula_link($t->usr1);
        $lnk->id = 1;
        $t->assert_sql_standard($sc, $lnk);
        $t->assert_sql_not_changed($sc, $lnk);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $lnk);
        $t->assert_sql_changing_users_by_ids($sc, $lnk);

        // sql to load the user formula link by id
        $t->assert_sql_user_changes($sc, $lnk);

        $t->subheader($ts . 'formula link sql write');
        $lnk = $t_frm->formula_link();
        $t->assert_sql_insert($sc, $lnk);
        $t->assert_sql_insert($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk_filled = $t_frm->formula_link_filled();
        $t->assert_sql_insert($sc, $lnk_filled, [sql_type::LOG]);
        $lnk = $t_frm->formula_link_incomplete();
        $t->assert_sql_insert_fail($sc, $lnk, [sql_type::LOG]);
        $lnk = $t_frm->formula_link();
        $lnk_reordered = clone $lnk;
        $lnk_reordered->order_nbr = 1;
        $t->assert_sql_update($sc, $lnk_reordered, $lnk);
        $t->assert_sql_update($sc, $lnk_reordered, $lnk, [sql_type::LOG, sql_type::USER]);
        // the description says why the formula applies to the phrase
        $lnk_described = clone $lnk;
        $lnk_described->description = test_const::FORMULA_LINK_COM;
        $t->assert_sql_update($sc, $lnk_described, $lnk);
        $t->assert_sql_update($sc, $lnk_described, $lnk, [sql_type::USER]);
        $t->assert_sql_update($sc, $lnk_described, $lnk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'formula link base object handling');
        $lnk = $t_frm->formula_link();
        $t->assert_reset($lnk);

        $t->subheader($ts . 'row mapper');

        // the list query joins the names of the linked formula and phrase, so that the link can
        // name both e.g. for the change log (see formula_link_list::load_sql)
        $test_name = 'a row with the joined names names both linked objects';
        $db_row = [
            formula_link::FLD_ID => 1,
            'user_' . formula_link::FLD_ID => null,
            user_db::FLD_ID => $t->usr1->id(),
            formula_fields::FLD_ID => formula_names::SCALE_TO_SEC_ID,
            phrase::FLD_ID => word_names::MINUTE_ID,
            formula_link_type::FLD_ID => null,
            formula_link::FLD_ORDER => test_const::FORMULA_LINK_ORDER_NBR,
            fields::FLD_DESCRIPTION => test_const::FORMULA_LINK_COM,
            fields::FLD_EXCLUDED => null,
            fields::FLD_SHARE => null,
            fields::FLD_PROTECT => null,
            formula_link::FLD_FORMULA_NAME_JOINED => formula_names::SCALE_TO_SEC,
            formula_link::FLD_PHRASE_NAME_JOINED => word_names::MINUTE,
        ];
        $lnk_row = new formula_link($t->usr1);
        $lnk_row->row_mapper_sandbox($db_row, $msg);
        $t->assert($test_name, $lnk_row->name(),
            formula_names::SCALE_TO_SEC . ' to ' . word_names::MINUTE);
        $test_name = 'the description of a row is mapped';
        $t->assert($test_name, $lnk_row->description, test_const::FORMULA_LINK_COM);

        // a load by id has no join, so the names stay empty instead of showing a wrong name
        $test_name = 'a row without the joined names leaves the formula name empty';
        unset($db_row[formula_link::FLD_FORMULA_NAME_JOINED]);
        unset($db_row[formula_link::FLD_PHRASE_NAME_JOINED]);
        unset($db_row[fields::FLD_DESCRIPTION]);
        $lnk_id_only = new formula_link($t->usr1);
        $lnk_id_only->row_mapper_sandbox($db_row, $msg);
        $t->assert($test_name, $lnk_id_only->formula()->name() ?? '', '');
        $test_name = 'a row without a description leaves the description empty';
        $t->assert($test_name, $lnk_id_only->description ?? '', '');

        $t->subheader($ts . 'api mapper');

        // the frontend sends no type for a simple link, but the similar link check of a save needs
        // one, so the api mapper keeps the default type of a new link instead of the reset null
        $test_name = 'an api message without a type maps to the default formula link type';
        $lnk_new = new formula_link($t->usr1);
        $lnk_new->api_mapper([json_fields::ID => 0], $msg);
        $t->assert($test_name, $lnk_new->predicate_id(),
            $sys->typ_lst->frm_lnk_typ->id(formula_link_types::DEFAULT));
        $test_name = 'an api message with a type keeps that type';
        $time_id = $sys->typ_lst->frm_lnk_typ->id(formula_link_types::TIME_PERIOD);
        $lnk_new->api_mapper([json_fields::ID => 0, json_fields::PREDICATE_ID => $time_id], $msg);
        $t->assert($test_name, $lnk_new->predicate_id(), $time_id);
        $test_name = 'the priority of the api message is the order of the formula link';
        $lnk_new = new formula_link($t->usr1);
        $lnk_new->api_mapper($t_frm->formula_link()->api_json_array([], $msg), $msg);
        $t->assert($test_name, $lnk_new->order_nbr, test_const::FORMULA_LINK_ORDER_NBR);
        $msg->reset();
        // the formula is expected as a json object, not only by its id
        $test_name = 'an api message with only an id as the formula is refused';
        $lnk_new = new formula_link($t->usr1);
        $api_json = [json_fields::ID => 0, json_fields::FORMULA => formula_names::SCALE_TO_SEC_ID];
        $t->assert_false($test_name, $lnk_new->api_mapper($api_json, $msg));
        $msg->reset();
        // a formula link type can be limited to one formula
        $test_name = 'the api mapper sets the formula of a formula link type';
        $frm_lnk_typ = new formula_link_type('');
        $frm_lnk_typ->api_mapper([json_fields::ID => $time_id, json_fields::FORMULA_ID => formula_names::SCALE_TO_SEC_ID, json_fields::PHRASE_TYPE_ID => ''], $msg);
        $t->assert($test_name, $frm_lnk_typ->frm_id, formula_names::SCALE_TO_SEC_ID);
        $msg->reset();
        $test_name = 'an empty formula id leaves a formula link type valid for all formulas';
        $frm_lnk_typ = new formula_link_type('');
        $frm_lnk_typ->api_mapper([json_fields::ID => $time_id, json_fields::FORMULA_ID => '', json_fields::PHRASE_TYPE_ID => ''], $msg);
        $t->assert_null($test_name, $frm_lnk_typ->frm_id);
        $msg->reset();
        $test_name = 'the frontend api mapper sets the priority of the formula link';
        $lnk_ui = new formula_link_ui();
        $lnk_ui->api_mapper(json_decode($t_frm->formula_link()->api_json([], $msg), true), $msg_ui);
        $t->assert($test_name, $lnk_ui->order_nbr, test_const::FORMULA_LINK_ORDER_NBR);
        $msg_ui->reset();
        $test_name = 'the frontend api mapper refuses a formula link without id';
        $lnk_json = $t_frm->formula_link()->api_json_array([], $msg);
        unset($lnk_json[json_fields::ID]);
        $lnk_ui = new formula_link_ui();
        $t->assert_false($test_name, $lnk_ui->api_mapper($lnk_json, $msg_ui));
        $msg_ui->reset();
        $test_name = 'the frontend api mapper adds every formula link of the list';
        $lnk_lst_ui = new formula_link_list_ui();
        $lnk_lst_ui->api_mapper($t_frm->formula_link_list()->api_json_array([], $msg));
        $t->assert($test_name, $lnk_lst_ui->count(), $t_frm->formula_link_list()->count());
        $test_name = 'the frontend api mapper reports a formula link without id in the list';
        $lnk_lst_ui = new formula_link_list_ui();
        $t->assert_false($test_name, $lnk_lst_ui->api_mapper([$lnk_json])->is_ok());

        $t->subheader($ts . 'api json array');

        // with the phrases the formula is sent as an object with its name
        $test_name = 'the formula is sent with the phrases';
        $lnk = $t_frm->formula_link();
        $vars = $lnk->api_json_array([api_types::INCL_PHRASES, api_types::TEST_MODE], $msg);
        $t->assert($test_name, $vars[json_fields::FORMULA][json_fields::NAME], formula_names::SCALE_TO_SEC);
        $msg->reset();

        // negative: without the phrases the formula is sent only by its id
        $test_name = 'the formula id is sent without the phrases';
        $lnk = $t_frm->formula_link();
        $vars = $lnk->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::FORMULA_ID], formula_names::SCALE_TO_SEC_ID);
        $msg->reset();

        // the priority is only sent if set
        $test_name = 'the priority is sent if set';
        $lnk = $t_frm->formula_link();
        $vars = $lnk->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::PRIORITY], test_const::FORMULA_LINK_ORDER_NBR);
        $msg->reset();

        // negative: a formula link without priority sends none
        $test_name = 'no priority is sent if not set';
        $lnk = $t_frm->formula_link_increase();
        $vars = $lnk->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::PRIORITY, $vars);
        $msg->reset();

        // the description is only sent if set
        $test_name = 'the description is sent if set';
        $lnk = $t_frm->formula_link_filled_included();
        $vars = $lnk->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::DESCRIPTION], test_const::FORMULA_LINK_COM);
        $msg->reset();

        // negative: a formula link without description sends none
        $test_name = 'no description is sent if not set';
        $lnk = $t_frm->formula_link();
        $vars = $lnk->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::DESCRIPTION, $vars);
        $msg->reset();

        // a formula link type can be limited to one formula
        $test_name = 'the formula of a limited link type is sent';
        $frm_lnk_typ = $t_frm->link_type_limited();
        $vars = $frm_lnk_typ->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::FORMULA_ID], formula_names::SCALE_TO_SEC_ID);
        $msg->reset();

        // negative: a link type for all formulas sends no formula
        $test_name = 'no formula is sent for a link type of all formulas';
        $frm_lnk_typ = $t_frm->link_type();
        $vars = $frm_lnk_typ->api_json_array([], $msg);
        $t->assert_null($test_name, $vars[json_fields::FORMULA_ID]);
        $msg->reset();

        /*
        $t->subheader($ts . 'im- and export');

        $json_in = json_decode(file_get_contents(PATH_TEST_IMPORT_FILES . 'unit/formula/scale_second_to_minute.json'), true);
        $lnk = new formula($t->usr1);
        $lnk->import_obj($json_in, $usr_msg, $dto);
        $json_ex = json_decode(json_encode($lnk->export_json($msg, $exp_typ, false)), true);
        $result = json_is_similar($json_in, $json_ex);
        $target = true;
        $t->assert('formula_link->import check name', $result, $target);
        */

        $t->name = 'formula_link_list->';

        // start the test section (ts)
        $ts = 'unit formula link list ';
        $t->header($ts);

        $t->subheader($ts . 'sql statement');

        // sql to load the formula link list by formula id
        $frm_lnk_lst = new formula_link_list($t->usr1);
        $this->assert_sql_by_frm_id($t, $db_con, $frm_lnk_lst);

        // sql to load the formula link list by the formula link ids
        $this->assert_sql_by_ids($t, $db_con, $frm_lnk_lst);

        $t->subheader($ts . 'sql statement without ids');

        // without an id the query has no name, so that the caller does not send it to the database
        $test_name = 'the formula link list query of an empty id list is not prepared';
        $qp = $frm_lnk_lst->load_sql_by_ids($db_con->sql_creator(), []);
        $t->assert($test_name, $qp->name, '');

    }

    /**
     * check the load SQL statements to get a named log entry by field row
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_link_list $frm_lnk
     */
    private function assert_sql_by_frm_id(test_cleanup $t, sql_db $db_con, formula_link_list $frm_lnk): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lnk->load_sql_by_frm_id($db_con->sql_creator(), 7);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lnk->load_sql_by_frm_id($db_con->sql_creator(), 7);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * check the load SQL statements to get the formula links by their ids
     * for all allowed SQL database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con does not need to be connected to a real database
     * @param formula_link_list $frm_lnk
     */
    private function assert_sql_by_ids(test_cleanup $t, sql_db $db_con, formula_link_list $frm_lnk): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $frm_lnk->load_sql_by_ids($db_con->sql_creator(), [1, 2]);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $frm_lnk->load_sql_by_ids($db_con->sql_creator(), [1, 2]);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

}