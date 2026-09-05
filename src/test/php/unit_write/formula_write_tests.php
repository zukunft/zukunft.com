<?php

/*

    test/php/unit_write/formula_write_tests.php - write test FORMULAS to the database and check the results
    -------------------------------------------

    just the special test cases not covered by the horizontal write tests


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\log\change_log_named;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list as term_list_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;
use Zukunft\ZukunftCom\main\php\shared\const\rest_ctrl;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTime;

class formula_write_tests
{

    function run(test_cleanup $t): void
    {
        $msg = new user_message(); // a test is an entry point, so it creates the message the conversion reports into

        global $sys;

        // init
        $t_wrd = new test_words($t);
        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $t_db = new test_db_load($t);
        $t->name = 'formula->';
        $lib = new library();
        $msg = new user_message($t->usr1);
        $msg_ui = new user_message_ui();

        // start the test section (ts)
        $ts = 'db write formula ';
        $t->header($ts);
        $t_frm->cleanup($ts);

        $t->subheader($ts . 'formula prepared write');
        $test_name = 'add formula ' . formula_names::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_frm->formula_add_by_func(), true);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formula_names::SYSTEM_TEST_ADD);

        $t->subheader($ts . 'sandbox for ' . formula_names::SYSTEM_TEST_ADD);
        // TODO Prio 0 activate
        //$t->assert_write_named($t_frm->formula_filled_add(), formula_names::SYSTEM_TEST_ADD);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formula_names::SYSTEM_TEST_ADD);
        $t->write_named_cleanup(new word($t->usr1), formula_names::SYSTEM_TEST_ADD);

        // prepare
        $this->create_test_formulas($t);
        $frm = $t_db->add_formula(formula_names::SYSTEM_TEST_ADD, formula_names::INCREASE_EXP, $msg);
        $phr = $t_db->add_word($msg, words::YEAR_CAP)->phrase();
        $frm->link_phrase_and_save($phr, $msg);

        // test loading of one formula
        $frm = new formula($t->usr1);
        $frm->load_by_name(formula_names::SYSTEM_TEST_ADD, $msg, formula::class);
        $result = $frm->usr_text;
        $target = formula_names::INCREASE_EXP;
        $t->assert('load for "' . $frm->name() . '"', $result, $target);

        // test the formula type
        $result = $lib->dsp_bool($frm->is_predefined());
        $target = $lib->dsp_bool(false);
        $t->assert('formula->is_special for "' . $frm->name() . '"', $result, $target);

        $t->subheader($ts . 'update elements in database for ' . formula_names::SYSTEM_TEST_ADD);

        $test_name = 'remove an element and update the database';
        $frm->set_user_text(formula_names::INCREASE_ALTERNATIVE_EXP, $msg);
        $trm_lst = $t_trm->term_list_all();
        $frm->element_refresh($msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list($msg);
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id(), $t::TIMEOUT_LIMIT_DB);
        $test_name = 'remove an element and update the database ... compare with fixed text';
        $target = '';
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id(), $t::TIMEOUT_LIMIT_DB);

        $test_name = 'add an element and update the database';
        $frm->set_user_text(formula_names::INCREASE_EXP, $msg);
        $frm->element_refresh($msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($msg, $trm_lst);
        $elm_lst = $elm_lst->unique();
        $elm_lst_db = $frm->load_element_list($msg);
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id(), $t::TIMEOUT_LIMIT_DB);

        $test_name = 'remove an element and update the database without term cache';
        $frm->set_user_text(formula_names::INCREASE_ALTERNATIVE_EXP, $msg);
        $frm->element_refresh($msg);
        $elm_lst = $frm->elements_incl_result_phrases($msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list($msg);
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id(), $t::TIMEOUT_LIMIT_DB);

        $test_name = 'add an element and update the database without term cache';
        $frm->set_user_text(formula_names::INCREASE_EXP, $msg);
        $frm->element_refresh($msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list($msg);
        $elm_lst = $elm_lst->unique();
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id(), $t::TIMEOUT_LIMIT_DB);

        $t->subheader($ts . 'formulas using verb following');

        $msg->reset();
        $frm = new formula($t->usr1);
        $frm->load_by_name(formula_names::SYSTEM_TEST_ADD, $msg, formula::class);
        $exp = $frm->expression($msg);
        $trm_lst = new term_list($t->usr1);
        $trm_ids = $exp->terms_missing($msg, $trm_lst);
        $trm_lst->load_additional_by_id($trm_ids, $msg);
        $frm_lst = $exp->element_special_following_frm($msg, $trm_lst);
        $phr_lst = new phrase_list($t->usr1);
        if (!$frm_lst->is_empty()) {
            if (count($frm_lst->lst()) > 0) {
                $elm_frm = $frm_lst->lst()[0];
                $result = $lib->dsp_bool($elm_frm->is_predefined());
                $target = $lib->dsp_bool(true);
                $t->assert('formula->is_special for "' . $elm_frm->name() . '"', $result, $target);

                $phr_lst->load_by_names(array(words::CH, word_names::INHABITANTS, word_names::YEAR_2019), $msg);
                $time_phr = $phr_lst->time_useful($msg);
                // TODO review
                if ($time_phr == null) {
                    $time_phr = $t_wrd->word_2019()->phrase();
                }
                $val = $elm_frm->calc_predefined($phr_lst, $time_phr, $msg);
                $result = $val->number();
                $target = word_names::YEAR_2019;
                // TODO: get the best matching number
                //$t->assert('formula->special_result for "'.$elm_frm->name.'"', $result, $target);

                if (count($frm_lst->lst()) > 1) {
                    //$elm_frm_next = $frm_lst->lst[1];
                    $elm_frm_next = $elm_frm;
                } else {
                    $elm_frm_next = $elm_frm;
                }
                $time_phr = $elm_frm_next->special_time_phr($time_phr, $msg);
                $result = $time_phr->name();
                $target = word_names::YEAR_2019;
                $t->assert('formula->special_time_phr for "' . $elm_frm_next->name() . '"', $result, $target);
            }
        }

        $phr_lst = $frm->special_phr_lst($phr_lst, $msg);
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->name();
        }
        $target = '"' . word_names::YEAR_2019 . '","' . word_names::INHABITANTS . '","' . words::CH . '"';
        $t->assert('formula->special_phr_lst for "' . $frm->name() . '"', $result, $target);

        $phr_lst = $frm->assign_phr_lst_direct($msg);
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"year"';
        $t->assert('formula->assign_phr_lst_direct for "' . $frm->name() . '"', $result, $target);

        $phr_lst = $frm->assign_phr_ulst_direct($msg);
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"year"';
        $t->assert('formula->assign_phr_ulst_direct for "' . $frm->name() . '"', $result, $target);

        // loading another formula (Price Earning ratio ) to have more test cases
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_RATIO, formula_names::SYSTEM_TEST_RATIO_EXP);
        $t_db->test_formula_link(formula_names::SYSTEM_TEST_RATIO, word_names::TEST_SHARE);
        $frm_pe = $t_db->load_formula(formula_names::SYSTEM_TEST_RATIO);

        $wrd_share = $t_db->test_word($msg, word_names::TEST_SHARE);
        $wrd_chf = $t_db->test_word($msg, word_names::TEST_CHF);

        $frm_pe->assign_phrase($wrd_share->phrase(), $msg);

        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array(word_names::TEST_SHARE, word_names::TEST_CHF), $msg);

        $phr_lst_all = $frm_pe->assign_phr_lst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . word_names::TEST_SHARE . '"';
        $t->assert('formula->assign_phr_lst for "' . $frm->name() . '"', $result, $target);

        $phr_lst_all = $frm_pe->assign_phr_ulst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . word_names::TEST_SHARE . '"';
        $t->assert('formula->assign_phr_ulst for "' . $frm->name() . '"', $result, $target);

        // test the calculation of one value
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word MIO is needed??
        $phr_lst->load_by_names(array(words::CH, word_names::INHABITANTS, word_names::YEAR_2020, word_names::MIO), $msg);
        $frm = $t_db->load_formula(formula_names::SYSTEM_TEST_ADD);
        // calculate one value via the split path: load_data_for_calc fills the cache and to_num_new computes
        // use a separate message object so the shared $usr_msg (and its user) is not overwritten
        $usr_msg_calc = new user_message($t->usr1);
        $dto = $frm->load_data_for_calc($phr_lst, $usr_msg_calc);
        $res_lst_new = $frm->to_num_new($phr_lst, $usr_msg_calc, $dto);
        if ($res_lst_new->lst() != null) {
            $res_new = $res_lst_new->lst()[0];
            $result = $res_new->num_text;
        } else {
            $res_new = null;
            $result = 'result list is empty';
        }
        $target = '=(' . values::CH_INHABITANTS_2020_IN_MIO . '-' .
            values::CH_INHABITANTS_2019_IN_MIO . ')/' .
            values::CH_INHABITANTS_2019_IN_MIO;
        $t->assert('formula->to_num_new "' . $frm->name() . '" for a term list ' . $phr_lst->dsp_id(), $result, $target);

        // to_num_new calculates the same numeric result as to_num
        if ($res_lst_new->lst() != null) {
            $res_new->save_if_updated($msg);
            $result = $res_new->number();
            $target = results::TV_INCREASE_LONG;
            $t->assert('result->save_if_updated via to_num_new "' . $frm->name() . '" for a term list ' . $phr_lst->dsp_id(), $result, $target);
        }

        $res_lst = $frm->calc($phr_lst, $msg);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = results::TV_INCREASE_LONG;
        $t->assert('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_CALC);

        // test the scaling mainly to check the scaling handling of the results later
        // TODO remove any scaling words from the phrase list if the result word is of type scaling
        // TODO automatically check the fastest way to scale and avoid double scaling calculations
        $frm_scale_mio_to_one = $t_db->load_formula(formula_names::SYSTEM_TEST_SCALE_MIO);
        $res_lst = $frm_scale_mio_to_one->calc($phr_lst, $msg);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = '8505251.0';
        $t->assert('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $result, $target, $t::TIMEOUT_LIMIT_CALC);

        // test the scaling back to a thousand
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word ONE needed?? scale shout assume one if no scaling word is set or implied
        //$phr_lst->load_by_names(array(words::TN_CH, words::TN_INHABITANTS, words::TN_2020));
        $phr_lst->load_by_names(array(words::CH, word_names::INHABITANTS, word_names::YEAR_2020, word_names::ONE), $msg);
        $frm_scale_one_to_k = $t_db->load_formula(formula_names::SYSTEM_TEST_SCALE_TO_K);
        // TODO Prio 1 activate
        //$res_lst = $frm_scale_one_to_k->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = 8505.251;
        // TODO Prio 1 activate
        // TODO if possible move as many tests as possible to unit tests
        //$t->assert('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $result, $target);

        // load the test ids
        $wrd_percent = $t_db->load_word($msg, 'percent');
        $frm_this = $t_db->load_formula(formula_names::THIS_NAME);
        $frm_prior = $t_db->load_formula(formula_names::PRIOR);

        // test the formula display functions
        $frm = $t_db->load_formula(formula_names::SYSTEM_TEST_ADD);
        $frm_html = new formula_ui($frm->api_json());
        $exp = $frm->expression($msg);
        $result = $exp->dsp_id();
        $target = '""percent" = ( "' . word_names::THIS_NAME . '" - "' . word_names::PRIOR_NAME . '" ) / "' . word_names::PRIOR_NAME . '"" ({w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '})';
        $t->assert('formula->expression for ' . $frm->dsp_id(), $result, $target);

        // ... the formula name
        $result = $frm->name();
        $target = 'System Test Formula';
        $t->assert('formula->name for ' . $frm->dsp_id(), $result, $target);

        // ... in HTML format
        // TODO test without preloaded term list
        $trm_lst = $t_trm->term_list_increase();
        $trm_lst_ui = new term_list_ui($trm_lst->api_json());
        $result = $frm_html->dsp_text($msg_ui, [], $trm_lst_ui);
        $target = '"' . words::PERCENT
            . '" = ( <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&amp;id=' . $frm_this->id() . '">' . word_names::THIS_NAME . '</a>'
            . ' - <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&amp;id=' . $frm_prior->id() . '">' . word_names::PRIOR_NAME . '</a> )'
            . ' / <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&amp;id=' . $frm_prior->id() . '">' . word_names::PRIOR_NAME . '</a>';
        $t->assert('formula->dsp_text for ' . $frm->dsp_id(), $result, $target);

        // ... in HTML format with link
        $frm_increase = $t_db->load_formula(formula_names::SYSTEM_TEST_ADD);
        $result = $frm_html->edit_link();
        $target = '<a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&amp;id=' . $frm_increase->id() . '">' . formula_names::SYSTEM_TEST_ADD . '</a>';
        $t->assert('formula->display for ' . $frm->dsp_id(), $result, $target);

        // ... the formula result selected by the word and in percent
        // TODO defined the criteria for selecting the result
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::CH, $msg);
        /*
        $result = trim($frm_ui->dsp_result($wrd, $back));
        $target = '0.79 %';
        $t->assert('formula->dsp_result for ' . $frm->dsp_id() . ' and ' . $wrd->name(), $result, $target);
        */

        /* TODO reactivate
        $result = $frm->btn_edit();
        $target = '<a href="/http/formula_edit.php?id=52&back=" title="Change formula increase"><img src="/src/main/resources/images/button_edit.svg" alt="Change formula increase"></a>';
        $target = 'data-icon="edit"';
        $t->dsp_contains(', formula->btn_edit for '.$frm->name().'', $target, $result);
        */

        $page = 1;
        $size = 20;
        $call = rest_ctrl::PATH_FIXED .'test.php';
        // TODO Prio 2 activate
        //$result = $frm_html->dsp_hist($page, $size, $call, $back);
        //$target = 'changed to';
        //$t->dsp_contains(', formula->dsp_hist for ' . $frm->dsp_id(), $target, $result);

        //$result = $frm_html->dsp_hist_links($page, $size, $call, $back);
        // TODO fix it
        //$target = 'link';
        $target = 'table';
        //$result = $hist_page;
        //$t->dsp_contains(', formula->dsp_hist_links for ' . $frm->dsp_id(), $target, $result);

        // test formula refresh functions
        $usr_msg_elm = $msg->clone_reset();
        $result = $frm->element_refresh($usr_msg_elm);
        // the element refresh writes the formula elements to the database, so a db timeout is used
        $t->assert('formula->element_refresh for ' . $frm->dsp_id(), $result, true, $t::TIMEOUT_LIMIT_DB);


        // to link and unlink a formula is tested in the formula_link section

        // test adding of one formula
        $frm = new formula($t->usr1);
        $frm->set_name(formula_names::SYSTEM_TEST_ADD);
        $frm->usr_text = formula_names::INCREASE_EXP;
        $frm->save($msg);
        if ($frm->id() > 0) {
            $result = $frm->usr_text;
        }
        $target = formula_names::INCREASE_EXP;
        $t->assert('formula->save for adding "' . $frm->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula name has been saved
        $frm = $t_db->load_formula(formula_names::SYSTEM_TEST_ADD);
        $result = $frm->usr_text;
        $target = formula_names::INCREASE_EXP;
        $t->assert('formula->load the added "' . $frm->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI); // time limit???

        // ... check the correct logging; re-adding a formula that a previous run left excluded
        // can land in the user sandbox row, which is shown with 'user' after the action
        $log_ui = $t->log_last_ui_by_field($frm, formula_fields::FLD_NAME, $frm->id(), $msg);
        $usr_marker = $log_ui->is_user_sandbox_change() ? msg_id::LOG_USER->value . ' ' : '';
        $result = $log_ui->dsp(true);
        // TODO Prio 1 use user config date format
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"System Test Formula"';
        $t->assert('formula->save adding logged for "' . formula_names::SYSTEM_TEST_ADD . '"', $result, $target);

        // check if adding the same formula again creates a correct error message
        $frm = new formula($t->usr1);
        $frm->set_name(formula_names::SYSTEM_TEST_ADD);
        $frm->usr_text = formula_names::INCREASE_ALTERNATIVE_EXP;
        $frm->save($msg);
        $result = $msg->get_last_message();
        // use the next line if system config is non-standard
        //$target = 'A formula with the name "'.formulas::TN_ADD.'" already exists. Please use another name.';
        $target = '';
        $t->assert('formula->save adding "' . $frm->name() . '" again', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula can be renamed
        $frm = $t_db->load_formula(formula_names::SYSTEM_TEST_ADD);
        $frm->set_name(formula_names::SYSTEM_TEST_RENAMED);
        $frm->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('formula->save rename "' . formula_names::SYSTEM_TEST_ADD . '" to "' . formula_names::SYSTEM_TEST_RENAMED . '".', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula renaming was successful
        $frm_renamed = new formula($t->usr1);
        $frm_renamed->load_by_name(formula_names::SYSTEM_TEST_RENAMED, $msg, formula::class);
        if ($frm_renamed->id() > 0) {
            $result = $frm_renamed->name();
        }
        $target = formula_names::SYSTEM_TEST_RENAMED;
        $t->assert('formula->load renamed formula "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and if the formula renaming has been logged (with 'user' after the action if the
        // rename landed in the user sandbox row, like the add above)
        $log_ui = $t->log_last_ui_by_field($frm_renamed, formula_fields::FLD_NAME, $frm_renamed->id(), $msg);
        $usr_marker = $log_ui->is_user_sandbox_change() ? msg_id::LOG_USER->value . ' ' : '';
        $result = $log_ui->dsp(true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "System Test Formula Renamed" from "System Test Formula"';
        $t->assert('formula->save rename logged for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // check if the formula parameters can be added
        $frm_renamed->usr_text = '= "' . word_names::THIS_NAME . '"';
        $frm_renamed->description = formula_names::SYSTEM_TEST_RENAMED . ' description';
        $frm_renamed->type_id = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $frm_renamed->need_all_val = True;
        $frm_renamed->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('formula->save all formula fields beside the name for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula parameters have been added
        $frm_reloaded = $t_db->load_formula(formula_names::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . word_names::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w160}=1-({f18}/{f20})';
        $t->assert('formula->load ref_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->description;
        $target = formula_names::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and if the formula parameter adding have been logged; all fields are written by the
        // same save, so the user sandbox marker of the resolved text row is reused for all fields
        $log_ui = $t->log_last_ui_by_field($frm_reloaded, formula_fields::FLD_FORMULA_USER_TEXT, $frm_reloaded->id(), $msg);
        $usr_marker = $log_ui->is_user_sandbox_change() ? msg_id::LOG_USER->value . ' ' : '';
        $result = $log_ui->dsp(true);
        // use the next line if system config is non-standard
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to = "' . word_names::THIS_NAME . '" from "' . words::PERCENT . '" = ( "' . word_names::THIS_NAME . '" - "' . word_names::PRIOR_NAME . '" ) / "' . word_names::PRIOR_NAME . '"';
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "= "' . word_names::THIS_NAME . '"" from ""' . words::PERCENT . '" = 1 - ( "' . word_names::THIS_NAME . '" / "' . word_names::PRIOR_NAME . '" )"';
        $t->assert('formula->load resolved_text for "' . formula_names::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, $msg, formula_fields::FLD_FORMULA_TEXT, $frm_reloaded->id(), true);
        // use the next line if system config is non-standard
        // TODO Prio 1 review
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to ={f3} from {w' . $wrd_percent->id() . '}=( {f' . $frm_this->id() . '} - {f5} ) / {f5}';
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "={f' . $frm_this->id() . '}" from "{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})"';
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "{w'
            . $wrd_percent->id() . '}=1-({f'
            . $frm_this->id() . '}/{f'
            . $frm_prior->id() . '})" from "{w'
            . $wrd_percent->id() . '}=({f'
            . $frm_this->id() . '}-{f'
            . $frm_prior->id() . '})/{f'
            . $frm_prior->id() . '}"';
        $t->assert('formula->load formula_text for "' . formula_names::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, $msg, fields::FLD_DESCRIPTION, $frm_reloaded->id(), true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"System Test Formula Renamed description"';
        $t->assert('formula->load description for "' . formula_names::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, $msg, formula_fields::FLD_TYPE, $frm_reloaded->id(), true);
        // TODO review what is correct
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to this from calc';
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"' . word_names::THIS_NAME . '"';
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' added ' . $usr_marker . '"4"';
        $t->assert('formula->load formula_type_id for "' . formula_names::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, $msg, formula_fields::FLD_ALL_NEEDED, $frm_reloaded->id(), true);
        $target = new DateTime(change_log_named::TEST_TIME)->format('d-m-Y H:i') . ' ' . users::SYSTEM_TEST_NAME . ' changed ' . $usr_marker . 'to "1" from "0"';
        $t->assert('formula->load all_values_needed for "' . formula_names::SYSTEM_TEST_RENAMED . '" logged', $result, $target);

        // check if a user-specific formula is created if another user changes the formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formula_names::SYSTEM_TEST_RENAMED, $msg, formula::class);
        $frm_usr2->usr_text = '"' . words::PERCENT . '" = ( "' . word_names::THIS_NAME . '" - "' . word_names::PRIOR_NAME . '" ) / "' . word_names::PRIOR_NAME . '"';
        $frm_usr2->description = formula_names::SYSTEM_TEST_RENAMED . ' description2';
        $frm_usr2->type_id = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $frm_usr2->need_all_val = False;
        $frm_usr2->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('formula->save all formula fields for user 2 beside the name for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user-specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formula_names::SYSTEM_TEST_RENAMED, $msg, formula::class);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '"' . words::PERCENT . '" = ( "' . word_names::THIS_NAME . '" - "' . word_names::PRIOR_NAME . '" ) / "' . word_names::PRIOR_NAME . '"';
        $t->assert('formula->load usr_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->ref_text;
        $target = '{w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->description;
        $target = formula_names::SYSTEM_TEST_RENAMED . ' description2';
        $t->assert('formula->load description for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $t->assert('formula->load type_id for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = False;
        $t->assert('formula->load need_all_val for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and the formula for the original user remains unchanged
        $frm_reloaded = $t_db->load_formula(formula_names::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . word_names::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->description;
        $target = formula_names::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // check if undo all specific changes removes the user formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formula_names::SYSTEM_TEST_RENAMED, $msg, formula::class);
        $frm_usr2->usr_text = '= "' . word_names::THIS_NAME . '"';
        $frm_usr2->description = formula_names::SYSTEM_TEST_RENAMED . ' description';
        $frm_usr2->type_id = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $frm_usr2->need_all_val = True;
        $frm_usr2->save($msg);
        $result = $msg->get_last_message();
        $target = '';
        $t->assert('formula->save undo the user formula fields beside the name for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user-specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formula_names::SYSTEM_TEST_RENAMED, $msg);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '= "' . word_names::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->description;
        $target = formula_names::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formula_names::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // redo the user-specific formula changes
        // check if the user-specific changes can be removed with one click

        // check for formulas also that

        // TODO check if the word assignment can be done for each user

        // cleanup - fallback delete
        $t_frm->cleanup($ts);

        // test if there are any test leftovers in the database and report which
        $t->check_cleanup($msg, library::class_to_name(formula::class));

    }

    function run_list(test_cleanup $t): void
    {
        $msg = new user_message();
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db write formula list ';
        $t->header($ts);

        // load the main test word
        $wrd_share = $t_db->test_word($msg, word_names::TEST_SHARE);

        $wrd = new word($t->usr1);
        $wrd->load_by_id($wrd_share->id(), $msg);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->load_by_phr($wrd->phrase(), $msg);
        // TODO fix it
        //$result = $frm_lst->display();
        //$target = formulas::TN_RATIO;
        // $t->dsp_contains(', formula_list->load formula for word "' . $wrd->dsp_id() . '" should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

    }

    function create_test_formulas(test_cleanup|a_selected_test $t): void
    {
        $t_db = new test_db_load($t);
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db create test formulas ';
        $t->header($ts);

        $t_db->test_word($msg, word_names::TEST_EARNING);
        $t_db->test_word($msg, word_names::TEST_PRICE);
        $t_db->test_word($msg, word_names::TEST_PE);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_RATIO, formula_names::SYSTEM_TEST_RATIO_EXP);
        $t_db->test_word($msg, word_names::TEST_TOTAL);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_SECTOR, formula_names::SYSTEM_TEST_SECTOR_EXP);
        //$t->test_formula(formulas::TN_THIS, formulas::TF_THIS);
        $t_db->test_word($msg, word_names::TEST_PERCENT);
        $t_db->test_word($msg, word_names::TEST_THIS);
        $t_db->test_word($msg, word_names::TEST_PRIOR);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_ADD, formula_names::INCREASE_TEST_EXP);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_EXCLUDED, formula_names::INCREASE_TEST_EXP);
        $t_db->test_word($msg, word_names::TEST_IN_K);
        $t_db->test_word($msg, word_names::TEST_BIL);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_SCALE_K, formula_names::SYSTEM_TEST_SCALE_K_EXP);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_SCALE_TO_K, formula_names::SYSTEM_TEST_SCALE_TO_K_EXP);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_SCALE_MIO, formula_names::SYSTEM_TEST_SCALE_MIO_EXP);
        $t_db->test_formula($msg, formula_names::SYSTEM_TEST_SCALE_BIL, formula_names::SYSTEM_TEST_SCALE_BIL_EXP);

        // modify the special test cases
        // use a fresh message so a message left by the creates above does not block
        // the row mapping (row_mapper_sandbox only maps the fields if $msg->is_ok())
        $frm_msg = new user_message($t->usr1);
        $frm = new formula($t->usr1);
        $frm->load_by_name(formula_names::SYSTEM_TEST_EXCLUDED, $frm_msg);
        if ($frm->name() == '') {
            log_err('formula ' . formula_names::SYSTEM_TEST_EXCLUDED . ' could not be loaded', 'create_test_formulas');
        } else {
            $frm->excluded = true;
            $frm->save($frm_msg);
        }
        $msg->merge($frm_msg); // collect the messages
    }


}