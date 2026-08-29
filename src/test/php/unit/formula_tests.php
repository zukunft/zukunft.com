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
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use DateTime;
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

        // the formula default page shows the all values needed flag and the last update time
        // as display only info; both only if set, so a default formula shows no extra lines
        global $ui_sys;
        global $mtr;
        $form = new system_form();
        $frm_page = new formula_ui(
            $t_frm->formula_filled()->api_json([api_types::INCL_RELATED, api_types::TEST_MODE], $msg));
        $test_name = 'the formula page shows the all values needed flag';
        $t->assert($test_name, $form->show_all_values_needed($frm_page),
            $mtr->txt(msg_id::FORM_FIELD_FORMULA_ALL_VARS));
        $test_name = 'the formula page shows the time of the last update';
        $t->assert_text_contains($test_name, $form->show_last_update($frm_page),
            date_format(new DateTime(sys_log_tests::TV_TIME), $ui_sys->cfg->date_time_format()));
        $frm_plain = new formula_ui($t_frm->formula()->api_json([api_types::TEST_MODE], $msg));
        $test_name = 'a formula that also calculates with missing values shows no flag line';
        $t->assert($test_name, $form->show_all_values_needed($frm_plain), '');
        $test_name = 'a never calculated formula shows no last update line';
        $t->assert($test_name, $form->show_last_update($frm_plain), '');

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

        $t->subheader($ts . 'expression update based on the latex changes');
        // the refresh icon beside the expression field of the formula form takes the change that
        // the user has done in the latex field over into the expression; the latex shows a term by
        // its symbol, so only the changed tokens are applied by their position (see update_usr_text)
        $exp = '"second (time)" = "minute" * 60';
        $latex = '\text{s} = 60 \cdot \text{min}';

        $test_name = 'a changed number of the latex is taken over into the expression';
        $t->assert($test_name,
            $this->usr_text_updated($t, $exp, $latex, '\text{s} = 3600 \cdot \text{min}'),
            '"second (time)" = "minute" * 3600');

        $test_name = 'a changed term of the latex is taken over into the expression';
        $t->assert($test_name,
            $this->usr_text_updated($t, $exp, $latex, '\text{s} = 60 \cdot \text{h}'),
            '"second (time)" = "h" * 60');

        $test_name = 'a changed term and number of the latex are both taken over';
        $t->assert($test_name,
            $this->usr_text_updated($t, $exp, $latex, '\text{ms} = 3600 \cdot \text{h}'),
            '"ms" = "h" * 3600');

        $test_name = 'an unchanged latex keeps the expression';
        $t->assert($test_name, $this->usr_text_updated($t, $exp, $latex, $latex), $exp);

        $test_name = 'a term of a fraction is taken over into the expression';
        $t->assert($test_name,
            $this->usr_text_updated($t,
                '"percent" = "this" / "prior"',
                '\text{percent} = \frac{\text{this}}{\text{prior}}',
                '\text{percent} = \frac{\text{this}}{\text{next}}'),
            '"percent" = "this" / "next"');

        $test_name = 'a number within a term name is not changed by a latex number';
        $t->assert($test_name,
            $this->usr_text_updated($t, '"CO2" = "kg" * 44', '\text{CO2} = 44 \cdot \text{kg}',
                '\text{CO2} = 12 \cdot \text{kg}'),
            '"CO2" = "kg" * 12');

        // a latex change that cannot be assigned to an expression token must never guess, so the
        // expression stays unchanged and the user is told to change the expression itself
        $frm_ltx = new formula($t->usr1);
        $frm_ltx->usr_text = $exp;
        $msg = new user_message();
        $test_name = 'an added latex term keeps the expression';
        $t->assert($test_name,
            $frm_ltx->update_usr_text($latex, $latex . ' \cdot \text{h}', $msg), $exp);
        $test_name = 'an added latex term is reported to the user';
        $t->assert_true($test_name, $msg->all_message_text() != '');

        $frm_ltx = new formula($t->usr1);
        $frm_ltx->usr_text = '"a" = "b" * "c" * 2';
        $msg = new user_message();
        $test_name = 'a latex that does not match the expression keeps the expression';
        $t->assert($test_name,
            $frm_ltx->update_usr_text($latex, '\text{s} = 61 \cdot \text{min}', $msg),
            '"a" = "b" * "c" * 2');
        $test_name = 'a latex that does not match the expression is reported to the user';
        $t->assert_true($test_name, $msg->all_message_text() != '');

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

        // the 'all values needed' flag is a user changeable db field, so it must survive an
        // export and import round trip, but like in the api message it is only included if set
        $test_name = 'the all values needed flag is part of the formula export';
        $msg_exp = new user_message($t->usr_system); // a buffer of this export block, checked but not merged
        $frm_exp = $t_frm->formula_filled();
        $ex_json = $frm_exp->export_json($msg_exp, [], false);
        $t->assert_true($test_name, $ex_json[json_fields::NEED_ALL_VAL] ?? false);
        $test_name = 'the all values needed flag survives a formula import';
        $frm_imp = new formula($t->usr1);
        $frm_imp->import_mapper($ex_json, $msg_exp);
        $t->assert_true($test_name, $frm_imp->need_all_val);
        $test_name = 'a formula without the all values needed flag does not export it';
        $frm_off = $t_frm->formula();
        $ex_json_off = $frm_off->export_json($msg_exp, [], false);
        $t->assert_false($test_name, array_key_exists(json_fields::NEED_ALL_VAL, $ex_json_off));
        $test_name = 'a formula import without the all values needed flag does not set it';
        $frm_imp_off = new formula($t->usr1);
        $frm_imp_off->import_mapper($ex_json_off, $msg_exp);
        $t->assert_null($test_name, $frm_imp_off->need_all_val);

    }

    /**
     * apply a latex change to an expression like the refresh icon of the formula form does
     *
     * @param test_cleanup $t the test environment
     * @param string $usr_text the formula expression before the latex change
     * @param string $latex_pre the latex before the change of the user
     * @param string $latex the latex as changed by the user
     * @return string the updated expression
     */
    private function usr_text_updated(test_cleanup $t, string $usr_text, string $latex_pre, string $latex): string
    {
        $frm = new formula($t->usr1);
        $frm->usr_text = $usr_text;
        return $frm->update_usr_text($latex_pre, $latex, new user_message());
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