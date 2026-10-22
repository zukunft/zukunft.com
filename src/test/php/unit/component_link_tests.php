<?php

/*

  test/unit/component_link.php - unit testing of the VIEW COMPONENT LINK functions
  ---------------------------------
  

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

include_once paths::MODEL_COMPONENT . 'position_type.php';
include_once paths::MODEL_COMPONENT . 'component_link_type.php';
include_once paths::MODEL_COMPONENT . 'component_link.php';
include_once paths::MODEL_COMPONENT . 'component_link_list.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_type;
use Zukunft\ZukunftCom\main\php\cfg\component\position_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\export\export_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\component\component_link as component_link_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\position_types;
use Zukunft\ZukunftCom\main\php\shared\types\view_styles;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_link_tests
{
    function run(test_cleanup $t): void
    {

        // init
        $db_con = new sql_db();
        $sc = new sql_creator();
        $t_cmp = new test_components($t);
        $t->name = 'component_link->';
        $t->resource_path = 'db/component/';

        // start the test section (ts)
        $ts = 'unit component link ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup statements');
        $cmp_lnk_typ = new component_link_type('');
        $t->assert_sql_table_create($cmp_lnk_typ);
        $t->assert_sql_index_create($cmp_lnk_typ);
        $cmp_pos_typ = new position_type('');
        $t->assert_sql_table_create($cmp_pos_typ);
        $t->assert_sql_index_create($cmp_pos_typ);
        $cmp_lnk = $t_cmp->component_link();
        $t->assert_sql_table_create($cmp_lnk);
        $t->assert_sql_index_create($cmp_lnk);
        $t->assert_sql_foreign_key_create($cmp_lnk);

        $t->subheader($ts . 'sql user sandbox statement');

        // SQL creation tests (mainly to use the IDE check for the generated SQL statements)
        $vcl = new component_link($t->usr1);
        $t->assert_sql_by_id($sc, $vcl);
        $t->assert_sql_by_link($sc, $vcl);
        // the two queries that read many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $vcl);
        $t->assert_sql_changing_users_by_ids($sc, $vcl);
        $this->assert_sql_link_and_pos($t, $db_con, $vcl);
        $this->assert_sql_max_pos($t, $db_con, $vcl);


        $t->subheader($ts . 'sql statement');

        // sql to load a view component link by the id
        $lnk = new component_link($t->usr1);
        $lnk->id = 1;
        $t->assert_sql_user_changes($sc, $lnk);

        $t->subheader($ts . 'component link sql write');
        $lnk = $t_cmp->component_link();
        $t->assert_sql_insert($sc, $lnk);
        $t->assert_sql_insert($sc, $lnk, [sql_type::USER]);
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk = $t_cmp->component_link();
        $lnk->exclude();
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk_filled = $t_cmp->component_link_filled();
        $t->assert_sql_insert($sc, $lnk_filled, [sql_type::LOG]);
        $lnk = $t_cmp->component_link_half_filled();
        $t->assert_sql_insert($sc, $lnk, [sql_type::LOG, sql_type::USER]);
        $lnk = $t_cmp->component_link_incomplete();
        $t->assert_sql_insert_fail($sc, $lnk, [sql_type::LOG]);
        $lnk = $t_cmp->component_link();
        $lnk->exclude();
        $lnk_reordered = clone $lnk;
        $lnk_reordered->order_nbr = 2;
        $t->assert_sql_update($sc, $lnk_reordered, $lnk);
        $t->assert_sql_update($sc, $lnk_reordered, $lnk, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $lnk);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $lnk, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $lnk, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'component link base object handling');
        $lnk = $t_cmp->component_link_filled();
        $t->assert_reset($lnk);

        $t->subheader($ts . 'component link api mapper');

        // the detailed api json of a component link carries the id of the linked view
        $test_name = 'the api mapper sets the view of a component link';
        $msg = new user_message($t->usr1);
        $lnk_map = new component_link($t->usr1);
        $lnk_map->api_mapper($t_cmp->component_link()->api_json_array([api_types::LINK_DETAILS], $msg), $msg);
        $t->assert($test_name, $lnk_map->view_id(), views::START_ID);
        $msg->reset();

        // a component link json without the component id must not keep the previous component
        $test_name = 'a component link json without a component id links no component';
        $lnk_map->api_mapper([json_fields::ID => $lnk->id()], $msg);
        $t->assert($test_name, $lnk_map->component_id(), 0);
        $msg->reset();

        // ... and a linked component is returned by its id
        $test_name = 'the component id of a component link';
        $lnk_cmp = $t_cmp->component_link();
        $cmp_id = $lnk_cmp->component_id();
        $t->assert($test_name, $cmp_id, components::WORD_ID);

        // the position orders the component within the view
        $test_name = 'the position of a component link';
        $lnk_pos = $t_cmp->component_link();
        $lnk_pos->set_pos(test_const::COMPONENT_LINK_ORDER_NBR);
        $t->assert($test_name, $lnk_pos->get_pos(), test_const::COMPONENT_LINK_ORDER_NBR);

        // negative: null removes the position, so that the view orders the component itself
        $test_name = 'remove the position of a component link';
        $lnk_pos = $t_cmp->component_link();
        $lnk_pos->set_pos(null);
        $t->assert_null($test_name, $lnk_pos->get_pos());

        $t->subheader($ts . 'component link api json array');

        // the detailed api json of a component link sends the view by its id
        $test_name = 'link details view id';
        $lnk_arr = $t_cmp->component_link();
        $vars = $lnk_arr->api_json_array([api_types::LINK_DETAILS], $msg);
        $t->assert($test_name, $vars[json_fields::VIEW_ID] ?? null, views::START_ID);
        $t->assert_key_not_exists($test_name . ' without link id', json_fields::LINK_ID, $vars);
        $msg->reset();

        // negative: the single layer json moves the link id to link_id and uses id for the component
        $test_name = 'single layer link id';
        $lnk_arr = $t_cmp->component_link();
        $vars = $lnk_arr->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::LINK_ID], test_const::LINK_ID);
        $t->assert($test_name . ' component id', $vars[json_fields::ID] ?? null, components::WORD_ID);
        $t->assert_key_not_exists($test_name . ' without view id', json_fields::VIEW_ID, $vars);
        $msg->reset();

        // the position of a stored component link is always sent
        $test_name = 'position';
        $lnk_arr = $t_cmp->component_link();
        $vars = $lnk_arr->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::POSITION] ?? null, component_link::START_ORDER_NBR);
        $msg->reset();

        // negative: a new component link at the start position sends no position
        $test_name = 'no position';
        $lnk_arr = $t_cmp->component_link_add();
        $lnk_arr->set_pos(component_link::START_ORDER_NBR);
        $vars = $lnk_arr->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::POSITION, $vars);
        $msg->reset();

        // a new component link sends the position type if it is not the default
        $test_name = 'position type';
        $lnk_arr = $t_cmp->component_link_add();
        $lnk_arr->set_pos_type(position_types::SAME_LINE);
        $vars = $lnk_arr->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::POS_TYPE] ?? null, position_types::SAME_LINE_ID);
        $msg->reset();

        // negative: a new component link with the default position type sends no position type
        $test_name = 'default position type';
        $lnk_arr = $t_cmp->component_link_add();
        $lnk_arr->set_pos_type(position_types::DEFAULT);
        $vars = $lnk_arr->api_json_array([], $msg);
        $t->assert_key_not_exists($test_name, json_fields::POS_TYPE, $vars);
        $msg->reset();

        $t->subheader($ts . 'component link export');

        // a component link exports the view and the component as objects
        $test_name = 'export view';
        $lnk_exp = $t_cmp->component_link();
        $exp = $lnk_exp->export_json($msg, [], false);
        $msk_exp = $exp[json_fields::VIEW];
        $t->assert($test_name, $msk_exp[json_fields::NAME], views::START_NAME);
        $test_name = 'export component';
        $cmp_exp = $exp[json_fields::COMPONENT];
        $t->assert($test_name, $cmp_exp[json_fields::NAME], components::WORD_NAME);
        $msg->reset();

        // negative: as part of the view export the view is left out and the component is named
        $test_name = 'export without view';
        $lnk_exp = $t_cmp->component_link();
        $exp = $lnk_exp->export_json($msg, [export_type::IGNORE_FROM], false);
        $t->assert_key_not_exists($test_name, json_fields::VIEW, $exp);
        $test_name = 'export component name';
        $t->assert($test_name, $exp[json_fields::NAME], components::WORD_NAME);
        $msg->reset();

        // the link type, the position type and the style are exported if they are not the default
        $test_name = 'export link type';
        $lnk_exp = $t_cmp->component_link_filled_included();
        $exp = $lnk_exp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::PREDICATE], component_link_type::EXPRESSION);
        $test_name = 'export position type';
        $t->assert($test_name, $exp[json_fields::POS_TYPE], position_types::SIDE);
        $test_name = 'export style';
        $t->assert($test_name, $exp[json_fields::STYLE], view_styles::COL_SM_8);
        $msg->reset();

        // negative: the default link type and position type and no style are left out of the export
        $test_name = 'export default link type';
        $lnk_exp = $t_cmp->component_link();
        $exp = $lnk_exp->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::PREDICATE, $exp);
        $test_name = 'export default position type';
        $t->assert_key_not_exists($test_name, json_fields::POS_TYPE, $exp);
        $test_name = 'export no style';
        $t->assert_key_not_exists($test_name, json_fields::STYLE, $exp);
        $msg->reset();

        // the position of a component link is exported
        $test_name = 'export position';
        $lnk_exp = $t_cmp->component_link();
        $exp = $lnk_exp->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::POSITION], component_link::START_ORDER_NBR);
        $msg->reset();

        $t->subheader($ts . 'component link debug id');

        // the debug id shows the position of the component within the view
        $test_name = 'the debug id shows the position';
        $lnk_dsp = $t_cmp->component_link();
        $lnk_dsp->set_pos(test_const::COMPONENT_LINK_ORDER_NBR);
        $dsp = $lnk_dsp->dsp_id();
        $pos_txt = 'at pos ' . test_const::COMPONENT_LINK_ORDER_NBR;
        $t->assert_text_contains($test_name, $dsp, $pos_txt);

        // negative: a link without position shows no position
        $test_name = 'the debug id of a link without position';
        $lnk_dsp = $t_cmp->component_link();
        $lnk_dsp->set_pos(null);
        $dsp = $lnk_dsp->dsp_id();
        $pos_txt = 'at pos ' . test_const::COMPONENT_LINK_ORDER_NBR;
        $t->assert_text_not_contains($test_name, $dsp, $pos_txt);

        // the add and edit form of a component link shows the position type and the link type,
        // so each posts its own url var, else the form has two link type fields with the same id
        global $ui_sys;
        $t->subheader($ts . 'component link form');
        $form = new system_form();
        $lnk_ui = new component_link_ui($lnk->api_json());
        $test_name = 'the position type field posts the position type';
        $pos_html = $form->form_component_pos_type($lnk_ui, views::COMPONENT_LINK_ADD, $ui_sys->typ_lst_cache);
        $t->assert_text_contains($test_name, $pos_html, 'name="' . url_var::POSITION_TYPE . '"');
        $test_name = '... and not the link type, which the link type field posts';
        $lnk_typ_html = $form->form_component_link_type($lnk_ui, views::COMPONENT_LINK_ADD, $ui_sys->typ_lst_cache);
        $t->assert_text_contains($test_name, $lnk_typ_html, 'name="' . url_var::COMPONENT_LINK_TYPE . '"');
        $t->assert_text_not_contains($test_name, $pos_html, 'name="' . url_var::COMPONENT_LINK_TYPE . '"');
        // negative: the link type field posts only its own url var, and a link without a position
        // type has no default to preselect, so the selector asks the user to pick one
        $test_name = 'the link type field does not post the position type';
        $t->assert_text_not_contains($test_name,
            $form->form_component_link_type($lnk_ui, views::COMPONENT_LINK_ADD, $ui_sys->typ_lst_cache),
            'name="' . url_var::POSITION_TYPE . '"');
        $test_name = 'a link without a position type preselects the please select entry';
        $lnk_plain_ui = new component_link_ui($t_cmp->component_link()->api_json());
        // every test fixture has a position type, so it is removed for the test
        $lnk_plain_ui->pos_type_id = null;
        $pos_plain_html = $form->form_component_pos_type($lnk_plain_ui, views::COMPONENT_LINK_ADD, $ui_sys->typ_lst_cache);
        $t->assert_text_contains($test_name, $pos_plain_html, '<option value="0" selected>');

    }

    /**
     * test the SQL statement creation to retrieve a component link by view, component and pos
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link $vcl
     * @return void
     */
    private function assert_sql_link_and_pos(
        test_cleanup   $t,
        sql_db         $db_con,
        component_link $vcl): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vcl->load_sql_by_link_and_pos($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $vcl->load_sql_by_link_and_pos($db_con->sql_creator(), 1, 2, 3);
        $t->assert_qp($qp, $db_con->db_type);
    }

    /**
     * test the SQL statement creation to retrieve the max order number of one view
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_db $db_con the test database connection
     * @param component_link $vcl
     * @return void
     */
    private function assert_sql_max_pos(
        test_cleanup   $t,
        sql_db         $db_con,
        component_link $vcl): void
    {
        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $vcl->load_sql_max_pos($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);

        // check the MySQL query syntax
        $db_con->db_type = sql_db::MYSQL;
        $qp = $vcl->load_sql_max_pos($db_con->sql_creator(), 1);
        $t->assert_qp($qp, $db_con->db_type);
    }

}