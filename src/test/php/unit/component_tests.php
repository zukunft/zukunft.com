<?php

/*

    test/unit/component.php - unit testing of the view component functions
    ----------------------------
  

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

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\component\component_exe as component_ui;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_page;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\fields\component_fields;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class component_tests
{
    function run(test_cleanup $t): void
    {


        // init
        $sc = new sql_creator();
        $t_cmp = new test_components($t);
        $t->name = 'component->';
        $t->resource_path = 'db/component/';

        // start the test section (ts)
        $ts = 'unit component ';
        $t->header($ts);

        $t->subheader($ts . 'component sql setup');
        $cmp_typ = new component_type('');
        $t->assert_sql_table_create($cmp_typ);
        $t->assert_sql_index_create($cmp_typ);
        $cmp = $t_cmp->component();
        $t->assert_sql_table_create($cmp);
        $t->assert_sql_index_create($cmp);
        $t->assert_sql_foreign_key_create($cmp);

        $t->subheader($ts . 'component sql read');
        $cmp = new component($t->usr1);
        $t->assert_sql_by_id($sc, $cmp);
        $t->assert_sql_by_name($sc, $cmp);

        $t->subheader($ts . 'component sql read standard and user changes by id');
        $cmp = new component($t->usr1);
        $cmp->id = 2;
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard($sc, $cmp);
        $t->assert_sql_user_changes($sc, $cmp);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $cmp);
        $t->assert_sql_changing_users_by_ids($sc, $cmp);

        $t->subheader($ts . 'component sql read standard by name');
        $cmp = new component($t->usr1);
        $cmp->set_name(views::START_NAME);
        //$t->assert_sql_all($db_con, $cmp);
        $t->assert_sql_standard_by_name($sc, $cmp);

        $t->subheader($ts . 'component sql write insert');
        $cmp = $t_cmp->component();
        $t->assert_sql_insert($sc, $cmp);
        $t->assert_sql_insert($sc, $cmp, [sql_type::USER]);
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG, sql_type::USER]);
        $cmp = $t_cmp->component_word_add_title(); // a component with a code_id as it might be imported
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $cmp = $t_cmp->component_filled_all();
        $t->assert_sql_insert($sc, $cmp, [sql_type::LOG]);
        $cmp = $t_cmp->component_incomplete();
        $t->assert_sql_insert_fail($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component sql write update');
        $cmp = $t_cmp->component();
        $cmp_renamed = $cmp->cloned(components::TEST_RENAMED_NAME);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp);
        $t->assert_sql_update($sc, $cmp_renamed, $cmp, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'component sql delete');
        $t->assert_sql_delete($sc, $cmp);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $cmp, [sql_type::LOG]);

        $t->subheader($ts . 'component base object handling');
        $cmp = $t_cmp->component_filled();
        $t->assert_reset($cmp);

        $t->subheader($ts . 'component api');
        $cmp = $t_cmp->component_filled();
        $t->assert_api_json($cmp);
        $cmp = $t_cmp->component();
        $t->assert_api($cmp);

        // zero is a valid ui message exception value e.g. of the usage sub title, which shows
        // the 'no usage' message if the usage is zero, so it must not be treated like null
        $msg_zero = new user_message($t->usr_system); // a buffer of this zero block, checked but not merged
        $test_name = 'a ui message exception value of zero is kept by the row mapper';
        $cmp_zero = new component($t->usr1);
        // like the rows of a user sandbox query the row carries the user config id and the owner
        $db_row = [
            component_fields::FLD_ID => components::WORD_ID,
            sql_db::TBL_USER_PREFIX . component_fields::FLD_ID => null,
            user_db::FLD_ID => $t->usr1->id(),
            component_fields::FLD_NAME => components::WORD_NAME,
            component_fields::FLD_UI_MSG_VAL_EXCEPTION => 0,
        ];
        $cmp_zero->row_mapper_sandbox($db_row, $msg_zero);
        $t->assert($test_name, $cmp_zero->ui_msg_value_exception, 0);
        $test_name = 'a ui message exception value of zero is part of the api message';
        $api_zero = json_decode($cmp_zero->api_json(), true);
        $t->assert($test_name, $api_zero[json_fields::UI_MSG_CODE_VAL_EXCEPTION] ?? null, 0);
        $test_name = 'a ui message exception value of zero is part of the export';
        $ex_zero = $cmp_zero->export_json($msg_zero, [], false);
        $t->assert($test_name, $ex_zero[json_fields::UI_MSG_CODE_VAL_EXCEPTION] ?? null, 0);
        $test_name = 'a ui message exception value of zero survives an import';
        $cmp_zero_imp = new component($t->usr1);
        $cmp_zero_imp->import_mapper($ex_zero, $msg_zero);
        $t->assert($test_name, $cmp_zero_imp->ui_msg_value_exception, 0);
        // a row without the exception value is the normal case and must stay null, because
        // otherwise every component would use zero as the exception value
        $test_name = 'a component without a ui message exception value keeps it empty';
        $cmp_no_exc = new component($t->usr1);
        unset($db_row[component_fields::FLD_UI_MSG_VAL_EXCEPTION]);
        $cmp_no_exc->row_mapper_sandbox($db_row, $msg_zero);
        $t->assert_null($test_name, $cmp_no_exc->ui_msg_value_exception);
        $test_name = 'a ui message exception value of null is not part of the api message';
        $api_no_exc = json_decode($cmp_no_exc->api_json(), true);
        $t->assert_false($test_name, array_key_exists(json_fields::UI_MSG_CODE_VAL_EXCEPTION, $api_no_exc));
        // the frontend keeps the zero and sends it back to the backend, so that a save of a
        // component form does not clear the exception value
        $test_name = 'the frontend keeps a ui message exception value of zero';
        $cmp_zero_ui = new component_ui($cmp_zero->api_json());
        $t->assert($test_name, $cmp_zero_ui->ui_msg_value_exception, 0);
        $test_name = 'the api message to the backend keeps a ui message exception value of zero';
        $back_zero = $cmp_zero_ui->api_array(new api_type_list([]), new user_message_ui());
        $t->assert($test_name, $back_zero[json_fields::UI_MSG_CODE_VAL_EXCEPTION] ?? null, 0);

        // the usage sub title shows the 'no usage' message if the usage matches the exception value
        global $mtr;
        $lib = new library();
        $test_name = 'the usage sub title shows the no usage message if the usage is zero';
        $page = new system_page();
        $sub_title = $page->system_sub_tile_var(
            msg_id::FORM_SUB_TITLE_USAGE, 0, msg_id::FORM_SUB_TITLE_VAR_USAGE, 0, msg_id::FORM_SUB_TITLE_NO_USAGE);
        $t->assert_text_contains($test_name, $sub_title, $mtr->txt(msg_id::FORM_SUB_TITLE_NO_USAGE));
        $test_name = 'the usage sub title shows the usage number if the object is used';
        $sub_title = $page->system_sub_tile_var(
            msg_id::FORM_SUB_TITLE_USAGE, 3, msg_id::FORM_SUB_TITLE_VAR_USAGE, 0, msg_id::FORM_SUB_TITLE_NO_USAGE);
        $t->assert_false($test_name, str_contains($sub_title, $mtr->txt(msg_id::FORM_SUB_TITLE_NO_USAGE)));
        $t->assert_text_contains($test_name . ' in the plural', $sub_title,
            $lib->msg_var_replace(msg_id::SYS_MSG_USAGE->value, msg_id::VAR_USAGE, 3));
        // a single usage needs the singular, because "Used 1 times" is wrong
        $test_name = 'the usage sub title shows the singular if the object is used once';
        $sub_title = $page->system_sub_tile_var(
            msg_id::FORM_SUB_TITLE_USAGE, 1, msg_id::FORM_SUB_TITLE_VAR_USAGE, 0, msg_id::FORM_SUB_TITLE_NO_USAGE);
        $t->assert_text_contains($test_name, $sub_title,
            $lib->msg_var_replace(msg_id::SYS_MSG_USAGE_ONE->value, msg_id::VAR_USAGE, 1));

        $t->subheader($ts . 'component frontend');
        $t->assert_api_to_ui($cmp, new component_ui());

        // the code id and the ui message links of a component are only shown to an admin or a
        // developer and only a developer gets the input fields, because only a profile that
        // passes the backend can_set_code_id may change them
        global $ui_sys;
        $form = new system_form();
        $t_usr = new test_users($t);
        $t_frm = new test_formulas($t);
        // component_filled_all_included carries the code id, the ui message links and the
        // formula; the included factory is used, because the api message of an excluded
        // component has only the id
        $cmp_ui = new component_ui($t_cmp->component_filled_all_included()->api_json());
        // remember the session user so the changed global can be restored after the checks
        $usr_keep = $ui_sys->usr ?? null;
        $test_name = 'a developer sees the ui message input fields of a component';
        $ui_sys->usr = new user_ui($t->usr_dev->api_json());
        $dev_html = $form->form_field_ui_msg($cmp_ui);
        $t->assert_text_contains($test_name, $dev_html, 'name="' . url_var::UI_MSG_CODE_ID . '"');
        $t->assert_text_contains($test_name . ' incl the vars', $dev_html,
            'name="' . url_var::UI_MSG_CODE_ID_VARS . '"');
        $t->assert_text_contains($test_name . ' incl the exception message', $dev_html,
            'name="' . url_var::UI_MSG_CODE_ID_EXCEPTION . '"');
        $t->assert_text_contains($test_name . ' incl the exception value', $dev_html,
            'name="' . url_var::UI_MSG_VALUE_EXCEPTION . '"');
        $test_name = 'a developer sees the code id input field of a component';
        $t->assert_text_contains($test_name, $form->form_field_code_id($cmp_ui),
            'name="' . url_var::CODE_ID . '"');
        $test_name = 'an admin sees the ui message links of a component as read only text';
        $ui_sys->usr = new user_ui($t->usr_admin->api_json());
        $admin_html = $form->form_field_ui_msg($cmp_ui);
        $t->assert_text_contains($test_name, $admin_html, msg_id::PLEASE_SELECT->value);
        $test_name = 'an admin gets no ui message input field';
        $t->assert_false($test_name, str_contains($admin_html, 'name="' . url_var::UI_MSG_CODE_ID . '"'));
        $test_name = 'a normal user does not see the ui message links of a component';
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $t->assert($test_name, $form->form_field_ui_msg($cmp_ui), '');
        $test_name = 'a test profile user does not see the ui message links, so the view snapshots stay clean';
        $ui_sys->usr = new user_ui($t->usr1->api_json());
        $t->assert($test_name, $form->form_field_ui_msg($cmp_ui), '');
        $ui_sys->usr = $usr_keep;

        // the formula of a calculated component can be selected on the add and edit forms
        $test_name = 'the component formula selector posts the formula url var';
        $frm_lst_ui = $t_frm->formula_list_ui();
        $sel_html = $cmp_ui->formula_selector(views::COMPONENT_EDIT, $frm_lst_ui);
        $t->assert_text_contains($test_name, $sel_html, 'name="' . url_var::FORMULA . '"');
        $test_name = 'the component formula selector preselects the linked formula';
        $t->assert_text_contains($test_name, $sel_html, formula_names::SCALE_TO_SEC);
        $t->assert_text_contains($test_name, $sel_html, 'selected');
        $test_name = 'a component without a formula preselects no formula';
        $cmp_no_frm_ui = new component_ui($t_cmp->component()->api_json());
        $sel_html = $cmp_no_frm_ui->formula_selector(views::COMPONENT_EDIT, $frm_lst_ui);
        $t->assert_false($test_name, (bool)preg_match(
            '/value="' . formula_names::SCALE_TO_SEC_ID . '"\s+selected/', $sel_html));

        // the ui message links posted by the component form are mapped to the frontend object;
        // like every edit form the url carries the id of the changed component, because an url
        // without an id is an add form, where a missing name clears the loaded name
        $test_name = 'a posted ui message code id is mapped to the message enum';
        $cmp_map_ui = new component_ui($t_cmp->component()->api_json());
        $chg_url = [
            url_var::ID => $cmp_map_ui->id(),
            url_var::UI_MSG_CODE_ID => msg_id::PLEASE_SELECT->value,
            url_var::UI_MSG_VALUE_EXCEPTION => '0'
        ];
        $cmp_map_ui->url_mapper($chg_url, new user_message_ui());
        $t->assert($test_name, $cmp_map_ui->ui_msg_code_id?->value, msg_id::PLEASE_SELECT->value);
        $test_name = 'a posted ui message exception value is mapped to the number';
        $t->assert($test_name, $cmp_map_ui->ui_msg_value_exception, 0.0);
        $test_name = 'a partial ui message url keeps the name of the component';
        $t->assert($test_name, $cmp_map_ui->name(), components::WORD_NAME);
        $test_name = 'an empty posted ui message code id clears the link';
        $cmp_map_ui->url_mapper([
            url_var::ID => $cmp_map_ui->id(),
            url_var::UI_MSG_CODE_ID => ''
        ], new user_message_ui());
        $t->assert_null($test_name, $cmp_map_ui->ui_msg_code_id);

        // the confirm check mirrors the backend permission, so an orange warning is
        // shown on the edit view instead of a refused save
        $chg_url = [
            url_var::ID => $cmp_map_ui->id(),
            url_var::UI_MSG_CODE_ID => msg_id::DONE->value,
            url_var::PRE . url_var::UI_MSG_CODE_ID => msg_id::PLEASE_SELECT->value
        ];
        $test_name = 'a ui message change of a normal user is refused at the confirm check';
        $chk_msg = new user_message_ui();
        $chk_msg->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $t->assert_false($test_name, $cmp_map_ui->input_valid($chk_msg, url_var::CRUD_UPDATE, $chg_url));
        $test_name = 'a ui message change of a developer passes the confirm check';
        $chk_msg = new user_message_ui();
        $chk_msg->usr = new user_ui($t->usr_dev->api_json());
        $t->assert_true($test_name, $cmp_map_ui->input_valid($chk_msg, url_var::CRUD_UPDATE, $chg_url));

        $t->subheader($ts . 'component im- and export');
        $t->assert_ex_and_import($t_cmp->component(), $t->usr_system);
        $t->assert_ex_and_import($t_cmp->component_filled(), $t->usr_system);
        $json_file = 'unit/view/component_import.json';
        $t->assert_json_file(new component($t->usr1), $json_file);

        // the layout phrases (row, column and sub column) of a component import are resolved by
        // their name via the import cache, so the imported component carries the phrase ids
        $test_name = 'the row phrase of a component import is resolved via the import cache';
        $msg_imp = new user_message($t->usr_system); // a buffer of this negative import test block, checked but not merged
        $t_phr = new test_phrases($t);
        $dto = new data_object($t->usr1);
        $dto->add_phrase($t_phr->year(), $msg_imp);
        $cmp_imp = new component($t->usr1);
        $cmp_imp->import_mapper([json_fields::ROW => words::YEAR_CAP], $msg_imp, $dto);
        $t->assert($test_name, $cmp_imp->row_phrase?->id(), $t_phr->year()->id());
        // a phrase that is not in the import cache is kept by name only ("not ready yet" is a
        // normal intermediate state of an import, see docs/llm/coding.md), never loaded from the db
        $test_name = 'a row phrase that is not in the import cache keeps the name without an id';
        $cmp_imp = new component($t->usr1);
        $cmp_imp->import_mapper([json_fields::ROW => words::YEAR_CAP], $msg_imp, new data_object($t->usr1));
        $t->assert($test_name, $cmp_imp->row_phrase?->name(), words::YEAR_CAP);
        $t->assert($test_name . ' and a zero id', $cmp_imp->row_phrase?->id(), 0);

        $t->subheader($ts . 'component no update import');

        // a no update import compares the import object filled up with the database values with
        // the database object filled up with the import values, so that only a field that both
        // have set to a different value is reported as an overwrite
        // (see sandbox_list_named::update)
        $cmp_row = $t_cmp->component();
        $cmp_row->set_row_phrase($t_phr->year());

        // a field that only the import has set is filled up, which is what a no update import
        // does e.g. for the row phrase that companies.json adds to a component of company.json
        $test_name = 'a field that only the import has set is no overwrite';
        $cmp_db = $t_cmp->component();
        $t->assert_true($test_name, $this->no_upd_diff($cmp_db, $cmp_row, $t->usr1)->is_ok());

        // a field that both have set to another value would overwrite the database value
        $test_name = 'a field that both have set to another value is an overwrite';
        $cmp_db = $t_cmp->component();
        $cmp_db->set_row_phrase($t_phr->canton());
        $t->assert_false($test_name, $this->no_upd_diff($cmp_db, $cmp_row, $t->usr1)->is_ok());

    }

    /**
     * the overwrite check of a no update import as sandbox_list_named::update does it:
     * both objects are filled up from the other one, so that they differ only where both
     * have set the same field to a different value
     *
     * @param component $cmp_db the component as it is in the database
     * @param component $cmp_imp the component as the import file defines it
     * @param user $usr_req the user who has requested the import
     * @return user_message the overwrites that the no update import must not do
     */
    private function no_upd_diff(component $cmp_db, component $cmp_imp, user $usr_req): user_message
    {
        $imp_filled = $cmp_imp->clone_all();
        $imp_filled->fill($cmp_db, $usr_req);
        $db_filled = $cmp_db->clone_all();
        $db_filled->fill($cmp_imp, $usr_req);
        return $db_filled->diff_msg($imp_filled, true);
    }

}