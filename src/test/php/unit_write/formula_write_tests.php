<?php

/*

    test/php/unit_write/formula_tests.php - write test FORMULAS to the database and check the results
    -------------------------------------
  

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

namespace unit_write;

include_once DB_PATH . 'sql_db.php';
include_once MODEL_FORMULA_PATH . 'formula_db.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_ENUM_PATH . 'change_fields.php';

use cfg\db\sql_db;
use cfg\formula\formula;
use cfg\formula\formula_db;
use cfg\formula\formula_list;
use cfg\formula\formula_type;
use cfg\phrase\phrase_list;
use cfg\result\results;
use cfg\word\word;
use html\formula\formula as formula_dsp;
use html\phrase\term_list as term_list_dsp;
use shared\const\formulas;
use shared\const\users;
use shared\const\words;
use test\test_cleanup;

class formula_write_tests
{

    function run(test_cleanup $t): void
    {

        global $frm_typ_cac;

        // init
        $t->name = 'formula->';
        $back = 0;

        $t->header('formula db write tests');

        $t->subheader('formula prepared write');
        $test_name = 'add formula ' . formulas::SYSTEM_TEST_ADD_VIA_SQL . ' via sql insert';
        $t->assert_write_via_func_or_sql($test_name, $t->formula_add_by_sql(), false);
        $test_name = 'add formula ' . formulas::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t->formula_add_by_func(), true);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formulas::SYSTEM_TEST_ADD);

        $t->subheader('formula write sandbox tests for ' . formulas::SYSTEM_TEST_ADD);
        $t->assert_write_named($t->formula_filled_add(), formulas::SYSTEM_TEST_ADD);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formulas::SYSTEM_TEST_ADD);
        $t->write_named_cleanup(new word($t->usr1), formulas::SYSTEM_TEST_ADD);

        // prepare
        $this->create_test_formulas($t);
        $frm = $t->add_formula(formulas::SYSTEM_TEST_ADD, formulas::INCREASE_EXP);
        $phr = $t->add_word(words::YEAR_CAP)->phrase();
        $frm->link_phr($phr);

        // test loading of one formula
        $frm = new formula($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD, formula::class);
        $result = $frm->usr_text;
        $target = formulas::INCREASE_EXP;
        $t->assert('load for "' . $frm->name() . '"', $result, $target);

        // test the formula type
        $result = zu_dsp_bool($frm->is_special());
        $target = zu_dsp_bool(false);
        $t->display('formula->is_special for "' . $frm->name() . '"', $target, $result);

        $exp = $frm->expression();
        $frm_lst = $exp->element_special_following_frm();
        $phr_lst = new phrase_list($t->usr1);
        if (!$frm_lst->is_empty()) {
            if (count($frm_lst->lst()) > 0) {
                $elm_frm = $frm_lst->lst()[0];
                $result = zu_dsp_bool($elm_frm->is_special());
                $target = zu_dsp_bool(true);
                $t->display('formula->is_special for "' . $elm_frm->name() . '"', $target, $result);

                $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2019));
                $time_phr = $phr_lst->time_useful();
                // TODO review
                if ($time_phr == null) {
                    $time_phr = $t->word_2019()->phrase();
                }
                $val = $elm_frm->special_result($phr_lst, $time_phr);
                $result = $val->number();
                $target = words::YEAR_2019;
                // TODO: get the best matching number
                //$t->display('formula->special_result for "'.$elm_frm->name.'"', $target, $result);

                if (count($frm_lst->lst()) > 1) {
                    //$elm_frm_next = $frm_lst->lst[1];
                    $elm_frm_next = $elm_frm;
                } else {
                    $elm_frm_next = $elm_frm;
                }
                $time_phr = $elm_frm_next->special_time_phr($time_phr);
                $result = $time_phr->name();
                $target = words::YEAR_2019;
                $t->display('formula->special_time_phr for "' . $elm_frm_next->name() . '"', $target, $result);
            }
        }

        $phr_lst = $frm->special_phr_lst($phr_lst);
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->name();
        }
        $target = '"' . words::YEAR_2019 . '","' . words::INHABITANTS . '","' . words::CH . '"';
        $t->display('formula->special_phr_lst for "' . $frm->name() . '"', $target, $result);

        $phr_lst = $frm->assign_phr_lst_direct();
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"Year"';
        $t->display('formula->assign_phr_lst_direct for "' . $frm->name() . '"', $target, $result);

        $phr_lst = $frm->assign_phr_ulst_direct();
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"Year"';
        $t->display('formula->assign_phr_ulst_direct for "' . $frm->name() . '"', $target, $result);

        // loading another formula (Price Earning ratio ) to have more test cases
        $t->test_formula(formulas::SYSTEM_TEST_RATIO, formulas::SYSTEM_TEST_RATIO_EXP);
        $t->test_formula_link(formulas::SYSTEM_TEST_RATIO, words::TEST_SHARE);
        $frm_pe = $t->load_formula(formulas::SYSTEM_TEST_RATIO);

        $wrd_share = $t->test_word(words::TEST_SHARE);
        $wrd_chf = $t->test_word(words::TEST_CHF);

        $frm_pe->assign_phrase($wrd_share->phrase());

        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array(words::TEST_SHARE, words::TEST_CHF));

        $phr_lst_all = $frm_pe->assign_phr_lst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . words::TEST_SHARE . '"';
        $t->display('formula->assign_phr_lst for "' . $frm->name() . '"', $target, $result);

        $phr_lst_all = $frm_pe->assign_phr_ulst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . words::TEST_SHARE . '"';
        $t->display('formula->assign_phr_ulst for "' . $frm->name() . '"', $target, $result);

        // test the calculation of one value
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word MIO is needed??
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020, words::MIO));
        $frm = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $res_lst = $frm->to_num($phr_lst);
        if ($res_lst->lst() != null) {
            $res = $res_lst->lst()[0];
            $result = $res->num_text;
        } else {
            $res = null;
            $result = 'result list is empty';
        }
        $target = '=(8.505251-8.438822)/8.438822';
        $t->display('formula->to_num "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $target, $result);

        if ($res_lst->lst() != null) {
            $res->save_if_updated();
            $result = $res->number();
            $target = results::TV_INCREASE_LONG;
            $t->display('result->save_if_updated "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $target, $result);
        }

        $res_lst = $frm->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = results::TV_INCREASE_LONG;
        $t->display('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $target, $result);

        // test the scaling mainly to check the scaling handling of the results later
        // TODO remove any scaling words from the phrase list if the result word is of type scaling
        // TODO automatically check the fastest way to scale and avoid double scaling calculations
        $frm_scale_mio_to_one = $t->load_formula(formulas::SYSTEM_TEST_SCALE_MIO);
        $res_lst = $frm_scale_mio_to_one->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = '8505251.0';
        $t->display('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $target, $result);

        // test the scaling back to a thousand
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word ONE needed?? scale shout assume one if no scaling word is set or implied
        //$phr_lst->load_by_names(array(words::TN_CH, words::TN_INHABITANTS, words::TN_2020));
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020, words::ONE));
        $frm_scale_one_to_k = $t->load_formula(formulas::SYSTEM_TEST_SCALE_TO_K);
        // TODO activate Prio 1
        //$res_lst = $frm_scale_one_to_k->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = 8505.251;
        // TODO activate Prio 1
        // TODO if possible move as many tests as possible to unit tests
        //$t->display('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $target, $result);

        // load the test ids
        $wrd_percent = $t->load_word('percent');
        $frm_this = $t->load_formula(formulas::THIS_NAME);
        $frm_prior = $t->load_formula(formulas::PRIOR);

        // test the formula display functions
        $frm = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $frm_html = new formula_dsp($frm->api_json());
        $exp = $frm->expression();
        $result = $exp->dsp_id();
        $target = '""percent" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"" ({w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '})';
        $t->display('formula->expression for ' . $frm->dsp_id(), $target, $result);

        // ... the formula name
        $result = $frm->name();
        $target = 'System Test Formula';
        $t->display('formula->name for ' . $frm->dsp_id(), $target, $result);

        // ... in HTML format
        // TODO test without preloaded term list
        $trm_lst = $t->term_list_increase();
        $trm_lst_dsp = new term_list_dsp($trm_lst->api_json());
        $result = $frm_html->dsp_text($back, $trm_lst_dsp);
        $target = '"' . words::PERCENT . '" = ( <a href="/http/formula_edit.php?id=' . $frm_this->id() . '&back=0" title="' . words::THIS_NAME . '">this</a> - <a href="/http/formula_edit.php?id=' . $frm_prior->id() . '&back=0" title=<a href="/http/formula_edit.php?id=20&back=0" title="' . words::PRIOR_NAME . '">prior</a>>prior</a> ) / <a href="/http/formula_edit.php?id=20&back=0" title=<a href="/http/formula_edit.php?id=' . $frm_prior->id() . '&back=0" title="' . words::PRIOR_NAME . '">prior</a>>prior</a>';
        $t->display('formula->dsp_text for ' . $frm->dsp_id(), $target, $result);

        // ... in HTML format with link
        $frm_increase = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $result = $frm_html->edit_link($back);
        $target = '<a href="/http/formula_edit.php?id=' . $frm_increase->id() . '&back=0" title="' . formulas::SYSTEM_TEST_ADD . '">' . formulas::SYSTEM_TEST_ADD . '</a>';
        $t->display('formula->display for ' . $frm->dsp_id(), $target, $result);

        // ... the formula result selected by the word and in percent
        // TODO defined the criteria for selecting the result
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::CH);
        /*
        $result = trim($frm_dsp->dsp_result($wrd, $back));
        $target = '0.79 %';
        $t->display('formula->dsp_result for ' . $frm->dsp_id() . ' and ' . $wrd->name(), $target, $result);
        */

        /* TODO reactivate
        $result = $frm->btn_edit();
        $target = '<a href="/http/formula_edit.php?id=52&back=" title="Change formula increase"><img src="/src/main/resources/images/button_edit.svg" alt="Change formula increase"></a>';
        $target = 'data-icon="edit"';
        $t->dsp_contains(', formula->btn_edit for '.$frm->name().'', $target, $result);
        */

        $page = 1;
        $size = 20;
        $call = '/http/test.php';
        // TODO activate
        //$result = $frm_html->dsp_hist($page, $size, $call, $back);
        //$target = 'changed to';
        //$t->dsp_contains(', formula->dsp_hist for ' . $frm->dsp_id(), $target, $result);

        //$result = $frm_html->dsp_hist_links($page, $size, $call, $back);
        // TODO fix it
        //$target = 'link';
        $target = 'table';
        //$result = $hist_page;
        //$t->dsp_contains(', formula->dsp_hist_links for ' . $frm->dsp_id(), $target, $result);

        $add = 0;
        // TODO fix it
        //$result = $frm_html->dsp_edit($add, $wrd, $back);
        //$target = 'Formula "System Test Formula"';
        //$result = $edit_page;
        //$t->dsp_contains(', formula->dsp_edit for ' . $frm->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test formula refresh functions

        $result = $frm->element_refresh($frm->ref_text);
        $target = true;
        $t->display('formula->element_refresh for ' . $frm->dsp_id(), $target, $result);


        // to link and unlink a formula is tested in the formula_link section

        // test adding of one formula
        $frm = new formula($t->usr1);
        $frm->set_name(formulas::SYSTEM_TEST_ADD);
        $frm->usr_text = formulas::INCREASE_EXP;
        $result = $frm->save()->get_last_message();
        if ($frm->id() > 0) {
            $result = $frm->usr_text;
        }
        $target = formulas::INCREASE_EXP;
        $t->display('formula->save for adding "' . $frm->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula name has been saved
        $frm = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $result = $frm->usr_text;
        $target = formulas::INCREASE_EXP;
        $t->display('formula->load the added "' . $frm->name() . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI); // time limit???

        // ... check the correct logging
        $result = $t->log_last_by_field($frm, formula_db::FLD_NAME, $frm->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "System Test Formula"';
        $t->display('formula->save adding logged for "' . formulas::SYSTEM_TEST_ADD . '"', $target, $result);

        // check if adding the same formula again creates a correct error message
        $frm = new formula($t->usr1);
        $frm->set_name(formulas::SYSTEM_TEST_ADD);
        $frm->usr_text = formulas::INCREASE_ALTERNATIVE_EXP;
        $result = $frm->save()->get_last_message();
        // use the next line if system config is non-standard
        //$target = 'A formula with the name "'.formulas::TN_ADD.'" already exists. Please use another name.';
        $target = '';
        $t->display('formula->save adding "' . $frm->name() . '" again', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula can be renamed
        $frm = $t->load_formula(formulas::SYSTEM_TEST_ADD);
        $frm->set_name(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm->save()->get_last_message();
        $target = '';
        $t->display('formula->save rename "' . formulas::SYSTEM_TEST_ADD . '" to "' . formulas::SYSTEM_TEST_RENAMED . '".', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula renaming was successful
        $frm_renamed = new formula($t->usr1);
        $frm_renamed->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        if ($frm_renamed->id() > 0) {
            $result = $frm_renamed->name();
        }
        $target = formulas::SYSTEM_TEST_RENAMED;
        $t->display('formula->load renamed formula "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // ... and if the formula renaming has been logged
        $result = $t->log_last_by_field($frm_renamed, formula_db::FLD_NAME, $frm_renamed->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' changed "System Test Formula" to "System Test Formula Renamed"';
        $t->display('formula->save rename logged for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // check if the formula parameters can be added
        $frm_renamed->usr_text = '= "' . words::THIS_NAME . '"';
        $frm_renamed->description = formulas::SYSTEM_TEST_RENAMED . ' description';
        $frm_renamed->type_id = $frm_typ_cac->id(formula_type::THIS);
        $frm_renamed->need_all_val = True;
        $result = $frm_renamed->save()->get_last_message();
        $target = '';
        $t->display('formula->save all formula fields beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula parameters have been added
        $frm_reloaded = $t->load_formula(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->display('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->ref_text;
        $target = '={f' . $frm_this->id() . '}';
        $t->display('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->display('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->type_id;
        $target = $frm_typ_cac->id(formula_type::THIS);
        $t->display('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->display('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // ... and if the formula parameter adding have been logged
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_FORMULA_USER_TEXT, $frm_reloaded->id(), true);
        // use the next line if system config is non-standard
        $target = users::SYSTEM_TEST_NAME . ' changed "' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '" to = "' . words::THIS_NAME . '"';
        $target = users::SYSTEM_TEST_NAME . ' changed ""' . words::PERCENT . '" = 1 - ( "' . words::THIS_NAME . '" / "' . words::PRIOR_NAME . '" )" to "= "' . words::THIS_NAME . '""';
        $t->display('formula->load resolved_text for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_FORMULA_TEXT, $frm_reloaded->id(), true);
        // use the next line if system config is non-standard
        $target = users::SYSTEM_TEST_NAME . ' changed {w' . $wrd_percent->id() . '}=( {f' . $frm_this->id() . '} - {f5} ) / {f5} to ={f3}';
        $target = users::SYSTEM_TEST_NAME . ' changed "{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})" to "={f' . $frm_this->id() . '}"';
        $t->display('formula->load formula_text for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($frm_reloaded, sql_db::FLD_DESCRIPTION, $frm_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "System Test Formula Renamed description"';
        $t->display('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_TYPE, $frm_reloaded->id(), true);
        // TODO review what is correct
        $target = users::SYSTEM_TEST_NAME . ' changed calc to this';
        $target = users::SYSTEM_TEST_NAME . ' added "' . words::THIS_NAME . '"';
        $target = users::SYSTEM_TEST_NAME . ' added "4"';
        $t->display('formula->load formula_type_id for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $target, $result);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_ALL_NEEDED, $frm_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' changed "0" to "1"';
        $t->display('formula->load all_values_needed for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $target, $result);

        // check if a user specific formula is created if another user changes the formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $frm_usr2->usr_text = '"' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"';
        $frm_usr2->description = formulas::SYSTEM_TEST_RENAMED . ' description2';
        $frm_usr2->type_id = $frm_typ_cac->id(formula_type::NEXT);
        $frm_usr2->need_all_val = False;
        $result = $frm_usr2->save()->get_last_message();
        $target = '';
        $t->display('formula->save all formula fields for user 2 beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '"' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"';
        $t->display('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->ref_text;
        $target = '{w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
        $t->display('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description2';
        $t->display('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->type_id;
        $target = $frm_typ_cac->id(formula_type::NEXT);
        $t->display('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = False;
        $t->display('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // ... and the formula for the original user remains unchanged
        $frm_reloaded = $t->load_formula(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->display('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->ref_text;
        $target = '={f' . $frm_this->id() . '}';
        $t->display('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->display('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->type_id;
        $target = $frm_typ_cac->id(formula_type::THIS);
        $t->display('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->display('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // check if undo all specific changes removes the user formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $frm_usr2->usr_text = '= "' . words::THIS_NAME . '"';
        $frm_usr2->description = formulas::SYSTEM_TEST_RENAMED . ' description';
        $frm_usr2->type_id = $frm_typ_cac->id(formula_type::THIS);
        $frm_usr2->need_all_val = True;
        $result = $frm_usr2->save()->get_last_message();
        $target = '';
        $t->display('formula->save undo the user formula fields beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->display('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->ref_text;
        $target = '={f' . $frm_this->id() . '}';
        $t->display('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->display('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->type_id;
        $target = $frm_typ_cac->id(formula_type::THIS);
        $t->display('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = True;
        $t->display('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $target, $result);

        // redo the user specific formula changes
        // check if the user specific changes can be removed with one click

        // check for formulas also that

        // TODO check if the word assignment can be done for each user

        // cleanup - fallback delete
        $frm = new formula($t->usr1);
        foreach (formulas::TEST_FORMULAS as $frm_name) {
            $t->write_named_cleanup($frm, $frm_name);
        }

    }

    function run_list(test_cleanup $t): void
    {

        $t->header('formula list database write tests');

        // load the main test word
        $wrd_share = $t->test_word(words::TEST_SHARE);

        $wrd = new word($t->usr1);
        $wrd->load_by_id($wrd_share->id(), word::class);
        $frm_lst = new formula_list($t->usr1);
        $frm_lst->back = $wrd->id();
        $frm_lst->load_by_phr($wrd->phrase());
        // TODO fix it
        //$result = $frm_lst->display();
        //$target = formulas::TN_RATIO;
        // $t->dsp_contains(', formula_list->load formula for word "' . $wrd->dsp_id() . '" should contain', $target, $result, $t::TIMEOUT_LIMIT_PAGE);

    }

    function create_test_formulas(test_cleanup $t): void
    {
        $t->header('Check if all base formulas are correct');

        $t->test_word(words::TEST_EARNING);
        $t->test_word(words::TEST_PRICE);
        $t->test_word(words::TEST_PE);
        $t->test_formula(formulas::SYSTEM_TEST_RATIO, formulas::SYSTEM_TEST_RATIO_EXP);
        $t->test_word(words::TEST_TOTAL);
        $t->test_formula(formulas::SYSTEM_TEST_SECTOR, formulas::SYSTEM_TEST_SECTOR_EXP);
        //$t->test_formula(formulas::TN_THIS, formulas::TF_THIS);
        $t->test_word(words::TEST_THIS);
        $t->test_word(words::TEST_PRIOR);
        $t->test_formula(formulas::SYSTEM_TEST_ADD, formulas::INCREASE_EXP);
        $t->test_formula(formulas::SYSTEM_TEST_EXCLUDED, formulas::INCREASE_EXP);
        $t->test_word(words::TEST_IN_K);
        $t->test_word(words::TEST_BIL);
        $t->test_formula(formulas::SYSTEM_TEST_SCALE_K, formulas::SYSTEM_TEST_SCALE_K_EXP);
        $t->test_formula(formulas::SYSTEM_TEST_SCALE_TO_K, formulas::SYSTEM_TEST_SCALE_TO_K_EXP);
        $t->test_formula(formulas::SYSTEM_TEST_SCALE_MIO, formulas::SYSTEM_TEST_SCALE_MIO_EXP);
        $t->test_formula(formulas::SYSTEM_TEST_SCALE_BIL, formulas::SYSTEM_TEST_SCALE_BIL_EXP);

        // modify the special test cases
        global $usr;
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_EXCLUDED);
        if ($frm->name() == '') {
            log_err('formula ' . formulas::SYSTEM_TEST_EXCLUDED . ' could not be loaded');
        } else {
            $frm->set_excluded(true);
            $frm->save();
        }
    }


}