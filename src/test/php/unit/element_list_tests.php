<?php

/*

    test/unit/element_list_tests.php - unit tests of for formula element lists
    --------------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::MODEL_ELEMENT . 'element_list.php';
include_once html_paths::ELEMENT . 'element_group.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\element\element_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\element_types;
use Zukunft\ZukunftCom\main\php\web\element\element_group as element_group_ui;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class element_list_tests
{
    function run(test_cleanup $t): void
    {

        global $sys;

        // init
        $sc = new sql_creator();
        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $t_phr = new test_phrases($t);
        $t->name = 'element_list->';
        $t->resource_path = 'db/element/';
        $elm_lst = new element_list($t->usr1);
        $msg = new user_message();


        // start the test section (ts)
        $ts = 'unit element list ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        $test_name = 'sql to load all elements of one formula';
        $frm = $t_frm->formula();
        $t->assert_sql_by_frm_id($sc, $elm_lst, $frm->id(), $test_name);

        $test_name = 'sql to load one type of elements related in one formula';
        $elm_type_id = $sys->typ_lst->elm_typ->id(element_types::WORD_SELECTOR);
        $this->assert_sql_by_frm_and_type_id($t, $sc, $elm_lst, $frm->id(), $elm_type_id, $test_name);

        // without a formula id the query has no name, so that the caller does not send it to the database
        $test_name = 'sql to load the elements of a formula without id is not prepared';
        $sc->reset(sql_db::POSTGRES);
        $qp = $elm_lst->load_sql_by_frm_and_type_id($sc, 0, $elm_type_id);
        $t->assert($test_name, $qp->name, '');

        $test_name = 'sql to delete a list of elements';
        $elm_lst = $t_frm->element_list();
        $this->assert_sql_del_by_id_lst($t, $sc, $elm_lst, $test_name);

        $test_name = 'element list name of the elements of one formula';
        // TODO Prio 0 add fail test cases
        $frm = $t_frm->formula();
        $trm_lst = $t_trm->term_list_time();
        $elm_lst = $frm->elements($msg, $trm_lst);
        $result = $elm_lst->dsp_id();
        $target = '"minute" (element_id 1/100) for user 3 (zukunft.com system test)';
        $t->assert($test_name, $result, $target);

        $test_name = 'element list reports a term id that is not a number';
        $frm = $t_frm->formula();
        $frm->ref_text = formula_names::SCALE_TO_SEC_EXP_REF_ID_NOT_A_NUMBER;
        $frm->elements($msg, $trm_lst);
        $t->assert_msg_false($test_name, $msg, '"' . formula_names::REF_ID_NOT_A_NUMBER . '"');

        $t->subheader($ts . 'api mapper');
        $test_name = 'the frontend api mapper adds every element of the group';
        $elm_grp_ui = new element_group_ui();
        $elm_grp_ui->api_mapper([json_fields::LIST_ELEMENTS => $t_frm->element_list()->api_json_array([], $msg)]);
        $t->assert($test_name, $elm_grp_ui->count(), $t_frm->element_list()->count());
        $test_name = 'the frontend api mapper reports an element without id';
        $elm_json = $t_frm->element()->api_json_array([], $msg);
        unset($elm_json[json_fields::ID]);
        $elm_grp_ui = new element_group_ui();
        $api_json = [json_fields::LIST_ELEMENTS => [$elm_json]];
        $t->assert_false($test_name, $elm_grp_ui->api_mapper($api_json)->is_ok());

        $t->subheader($ts . 'group api json array');

        // the phrases of the context are sent if the element group has them
        $test_name = 'the phrases of the element group are sent';
        $elm_grp = $t_frm->element_group();
        $elm_grp->phr_lst = $t_phr->phrase_list_pi();
        $vars = $elm_grp->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::PHRASES][0][json_fields::NAME], word_names::PI_SYMBOL);
        $msg->reset();

        // negative: an element group without phrases sends none
        $test_name = 'no phrases are sent without phrases';
        $elm_grp = $t_frm->element_group();
        $vars = $elm_grp->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::PHRASES, $vars);
        $msg->reset();

    }

    /**
     * test the SQL statement creation for a formula element list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the test database connection
     * @param element_list $lst the empty formula element list object
     * @param int $frm_id id of the formula to be used for the query creation
     * @param int $elm_type_id
     * @param string $test_name description of the test without the sql name
     * @return void
     */
    private function assert_sql_by_frm_and_type_id(
        test_cleanup $t,
        sql_creator  $sc,
        element_list $lst,
        int          $frm_id,
        int          $elm_type_id,
        string       $test_name = ''): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $lst->load_sql_by_frm_and_type_id($sc, $frm_id, $elm_type_id);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $lst->load_sql_by_frm_and_type_id($sc, $frm_id, $elm_type_id);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

    /**
     * check the sql to delete row select by id
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the test database connection
     * @param element_list $lst the empty formula element list object
     * @param string $test_name the test name only for the test log
     * @return void
     */
    function assert_sql_del_by_id_lst(
        test_cleanup $t,
        sql_creator  $sc,
        element_list $lst,
        string       $test_name = ''): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $lst->del_sql_without_log($sc);
        $result = $t->assert_qp($qp, $sc->db_type, $test_name);

        // check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $lst->del_sql_without_log($sc);
            $t->assert_qp($qp, $sc->db_type, $test_name);
        }
    }

}