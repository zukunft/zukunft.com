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
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\term_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\results;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\values;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\web\formula\formula as formula_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\term_list as term_list_ui;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_formulas;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_ENUM . 'change_fields.php';

class formula_write_tests
{

    function run(test_cleanup $t): void
    {

        global $sys;

        // init
        $t_wrd = new test_words($t);
        $t_frm = new test_formulas($t);
        $t_trm = new test_terms($t);
        $t_db = new test_db_load($t);
        $t->name = 'formula->';
        $back = 0;
        $lib = new library();
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula ';
        $t->header($ts);
        $t_frm->cleanup($ts);

        $t->subheader($ts . 'formula prepared write');
        $test_name = 'add formula ' . formulas::SYSTEM_TEST_ADD_VIA_FUNC . ' via sql function';
        $t->assert_write_via_func_or_sql($test_name, $t_frm->formula_add_by_func(), true);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formulas::SYSTEM_TEST_ADD);

        $t->subheader($ts . 'sandbox for ' . formulas::SYSTEM_TEST_ADD);
        // TODO Prio 0 activate
        //$t->assert_write_named($t_frm->formula_filled_add(), formulas::SYSTEM_TEST_ADD);

        // TODO remove
        $t->write_named_cleanup(new formula($t->usr1), formulas::SYSTEM_TEST_ADD);
        $t->write_named_cleanup(new word($t->usr1), formulas::SYSTEM_TEST_ADD);

        // prepare
        $this->create_test_formulas($t);
        $frm = $t_db->add_formula(formulas::SYSTEM_TEST_ADD, formulas::INCREASE_EXP, $usr_msg);
        $phr = $t_db->add_word(words::YEAR_CAP)->phrase();
        $frm->link_phrase_and_save($phr, $usr_msg);

        // test loading of one formula
        $frm = new formula($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD, formula::class);
        $result = $frm->usr_text;
        $target = formulas::INCREASE_EXP;
        $t->assert('load for "' . $frm->name() . '"', $result, $target);

        // test the formula type
        $result = $lib->dsp_bool($frm->is_predefined());
        $target = $lib->dsp_bool(false);
        $t->assert('formula->is_special for "' . $frm->name() . '"', $result, $target);

        $t->subheader($ts . 'update elements in database for ' . formulas::SYSTEM_TEST_ADD);

        $test_name = 'remove an element and update the database';
        $frm->set_user_text(formulas::INCREASE_ALTERNATIVE_EXP);
        $trm_lst = $t_trm->term_list_all();
        $frm->element_refresh($usr_msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($usr_msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list();
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id());
        $test_name = 'remove an element and update the database ... compare with fixed text';
        $target = '';
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id());

        $test_name = 'add an element and update the database';
        $frm->set_user_text(formulas::INCREASE_EXP);
        $frm->element_refresh($usr_msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($usr_msg, $trm_lst);
        $elm_lst = $elm_lst->unique();
        $elm_lst_db = $frm->load_element_list();
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id());

        $test_name = 'remove an element and update the database without term cache';
        $frm->set_user_text(formulas::INCREASE_ALTERNATIVE_EXP);
        $frm->element_refresh($usr_msg);
        $elm_lst = $frm->elements_incl_result_phrases($usr_msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list();
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id());

        $test_name = 'add an element and update the database without term cache';
        $frm->set_user_text(formulas::INCREASE_EXP);
        $frm->element_refresh($usr_msg, $trm_lst);
        $elm_lst = $frm->elements_incl_result_phrases($usr_msg, $trm_lst);
        $elm_lst_db = $frm->load_element_list();
        $elm_lst = $elm_lst->unique();
        $t->assert($test_name, $elm_lst_db->dsp_id(), $elm_lst->dsp_id());

        $t->subheader($ts . 'formulas using verb following');

        $usr_msg->reset();
        $frm = new formula($t->usr1);
        $frm->load_by_name(formulas::SYSTEM_TEST_ADD, formula::class);
        $exp = $frm->expression();
        $trm_lst = new term_list($t->usr1);
        $trm_ids = $exp->terms_missing($usr_msg, $trm_lst);
        $trm_lst->load_additional_by_id($trm_ids);
        $frm_lst = $exp->element_special_following_frm($usr_msg, $trm_lst);
        $phr_lst = new phrase_list($t->usr1);
        if (!$frm_lst->is_empty()) {
            if (count($frm_lst->lst()) > 0) {
                $elm_frm = $frm_lst->lst()[0];
                $result = $lib->dsp_bool($elm_frm->is_predefined());
                $target = $lib->dsp_bool(true);
                $t->assert('formula->is_special for "' . $elm_frm->name() . '"', $result, $target);

                $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2019));
                $time_phr = $phr_lst->time_useful();
                // TODO review
                if ($time_phr == null) {
                    $time_phr = $t_wrd->word_2019()->phrase();
                }
                $val = $elm_frm->calc_predefined($phr_lst, $time_phr, $usr_msg);
                $result = $val->number();
                $target = words::YEAR_2019;
                // TODO: get the best matching number
                //$t->assert('formula->special_result for "'.$elm_frm->name.'"', $result, $target);

                if (count($frm_lst->lst()) > 1) {
                    //$elm_frm_next = $frm_lst->lst[1];
                    $elm_frm_next = $elm_frm;
                } else {
                    $elm_frm_next = $elm_frm;
                }
                $time_phr = $elm_frm_next->special_time_phr($time_phr);
                $result = $time_phr->name();
                $target = words::YEAR_2019;
                $t->assert('formula->special_time_phr for "' . $elm_frm_next->name() . '"', $result, $target);
            }
        }

        $phr_lst = $frm->special_phr_lst($phr_lst);
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->name();
        }
        $target = '"' . words::YEAR_2019 . '","' . words::INHABITANTS . '","' . words::CH . '"';
        $t->assert('formula->special_phr_lst for "' . $frm->name() . '"', $result, $target);

        $phr_lst = $frm->assign_phr_lst_direct();
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"year"';
        $t->assert('formula->assign_phr_lst_direct for "' . $frm->name() . '"', $result, $target);

        $phr_lst = $frm->assign_phr_ulst_direct();
        if (!isset($phr_lst)) {
            $result = '';
        } else {
            $result = $phr_lst->dsp_name();
        }
        $target = '"year"';
        $t->assert('formula->assign_phr_ulst_direct for "' . $frm->name() . '"', $result, $target);

        // loading another formula (Price Earning ratio ) to have more test cases
        $t_db->test_formula(formulas::SYSTEM_TEST_RATIO, formulas::SYSTEM_TEST_RATIO_EXP, $usr_msg);
        $t_db->test_formula_link(formulas::SYSTEM_TEST_RATIO, words::TEST_SHARE);
        $frm_pe = $t_db->load_formula(formulas::SYSTEM_TEST_RATIO);

        $wrd_share = $t_db->test_word(words::TEST_SHARE);
        $wrd_chf = $t_db->test_word(words::TEST_CHF);

        $frm_pe->assign_phrase($wrd_share->phrase(), $usr_msg);

        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array(words::TEST_SHARE, words::TEST_CHF));

        $phr_lst_all = $frm_pe->assign_phr_lst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . words::TEST_SHARE . '"';
        $t->assert('formula->assign_phr_lst for "' . $frm->name() . '"', $result, $target);

        $phr_lst_all = $frm_pe->assign_phr_ulst();
        $phr_lst = $phr_lst_all->del_list($phr_lst);
        $result = $phr_lst->dsp_name();
        $target = '"' . words::TEST_SHARE . '"';
        $t->assert('formula->assign_phr_ulst for "' . $frm->name() . '"', $result, $target);

        // test the calculation of one value
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word MIO is needed??
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020, words::MIO));
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
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
            $res_new->save_if_updated();
            $result = $res_new->number();
            $target = results::TV_INCREASE_LONG;
            $t->assert('result->save_if_updated via to_num_new "' . $frm->name() . '" for a term list ' . $phr_lst->dsp_id(), $result, $target);
        }

        $res_lst = $frm->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = results::TV_INCREASE_LONG;
        $t->assert('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $result, $target);

        // test the scaling mainly to check the scaling handling of the results later
        // TODO remove any scaling words from the phrase list if the result word is of type scaling
        // TODO automatically check the fastest way to scale and avoid double scaling calculations
        $frm_scale_mio_to_one = $t_db->load_formula(formulas::SYSTEM_TEST_SCALE_MIO);
        $res_lst = $frm_scale_mio_to_one->calc($phr_lst);
        if ($res_lst != null) {
            $result = $res_lst[0]->number();
        } else {
            $result = '';
        }
        $target = '8505251.0';
        $t->assert('formula->calc "' . $frm->name() . '" for a tern list ' . $phr_lst->dsp_id(), $result, $target);

        // test the scaling back to a thousand
        $phr_lst = new phrase_list($t->usr1);
        // TODO check why is this word ONE needed?? scale shout assume one if no scaling word is set or implied
        //$phr_lst->load_by_names(array(words::TN_CH, words::TN_INHABITANTS, words::TN_2020));
        $phr_lst->load_by_names(array(words::CH, words::INHABITANTS, words::YEAR_2020, words::ONE));
        $frm_scale_one_to_k = $t_db->load_formula(formulas::SYSTEM_TEST_SCALE_TO_K);
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
        $wrd_percent = $t_db->load_word('percent');
        $frm_this = $t_db->load_formula(formulas::THIS_NAME);
        $frm_prior = $t_db->load_formula(formulas::PRIOR);

        // test the formula display functions
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $frm_html = new formula_ui($frm->api_json());
        $exp = $frm->expression();
        $result = $exp->dsp_id();
        $target = '""percent" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"" ({w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '})';
        $t->assert('formula->expression for ' . $frm->dsp_id(), $result, $target);

        // ... the formula name
        $result = $frm->name();
        $target = 'System Test Formula';
        $t->assert('formula->name for ' . $frm->dsp_id(), $result, $target);

        // ... in HTML format
        // TODO test without preloaded term list
        $trm_lst = $t_trm->term_list_increase();
        $trm_lst_ui = new term_list_ui($trm_lst->api_json());
        $result = $frm_html->dsp_text($back, $trm_lst_ui);
        $target = '"' . words::PERCENT
            . '" = ( <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&id=' . $frm_this->id() . '&back=0">' . words::THIS_NAME . '</a>'
            . ' - <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&id=' . $frm_prior->id() . '&back=0">' . words::PRIOR_NAME . '</a> )'
            . ' / <a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&id=' . $frm_prior->id() . '&back=0">' . words::PRIOR_NAME . '</a>';
        $t->assert('formula->dsp_text for ' . $frm->dsp_id(), $result, $target);

        // ... in HTML format with link
        $frm_increase = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $result = $frm_html->edit_link($back);
        $target = '<a href="/http/view.php?m=' . views::FORMULA_EDIT_ID . '&id=' . $frm_increase->id() . '&back=0">' . formulas::SYSTEM_TEST_ADD . '</a>';
        $t->assert('formula->display for ' . $frm->dsp_id(), $result, $target);

        // ... the formula result selected by the word and in percent
        // TODO defined the criteria for selecting the result
        $wrd = new word($t->usr1);
        $wrd->load_by_name(words::CH);
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
        $call = '/http/test.php';
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

        $add = 0;
        // TODO fix it
        //$result = $frm_html->dsp_edit($add, $wrd, $back);
        //$target = 'Formula "System Test Formula"';
        //$result = $edit_page;
        //$t->dsp_contains(', formula->dsp_edit for ' . $frm->dsp_id(), $target, $result, $t::TIMEOUT_LIMIT_PAGE);

        // test formula refresh functions
        $usr_msg_elm = $usr_msg->clone_reset();
        $result = $frm->element_refresh($usr_msg_elm);
        $t->assert('formula->element_refresh for ' . $frm->dsp_id(), $result, true);


        // to link and unlink a formula is tested in the formula_link section

        // test adding of one formula
        $frm = new formula($t->usr1);
        $frm->set_name(formulas::SYSTEM_TEST_ADD);
        $frm->usr_text = formulas::INCREASE_EXP;
        $frm->save($usr_msg);
        if ($frm->id() > 0) {
            $result = $frm->usr_text;
        }
        $target = formulas::INCREASE_EXP;
        $t->assert('formula->save for adding "' . $frm->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula name has been saved
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $result = $frm->usr_text;
        $target = formulas::INCREASE_EXP;
        $t->assert('formula->load the added "' . $frm->name() . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI); // time limit???

        // ... check the correct logging
        $result = $t->log_last_by_field($frm, formula_db::FLD_NAME, $frm->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "System Test Formula"';
        $t->assert('formula->save adding logged for "' . formulas::SYSTEM_TEST_ADD . '"', $result, $target);

        // check if adding the same formula again creates a correct error message
        $frm = new formula($t->usr1);
        $frm->set_name(formulas::SYSTEM_TEST_ADD);
        $frm->usr_text = formulas::INCREASE_ALTERNATIVE_EXP;
        $frm->save($usr_msg);
        $result = $usr_msg->get_last_message();
        // use the next line if system config is non-standard
        //$target = 'A formula with the name "'.formulas::TN_ADD.'" already exists. Please use another name.';
        $target = '';
        $t->assert('formula->save adding "' . $frm->name() . '" again', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // check if the formula can be renamed
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_ADD);
        $frm->set_name(formulas::SYSTEM_TEST_RENAMED);
        $frm->save($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert('formula->save rename "' . formulas::SYSTEM_TEST_ADD . '" to "' . formulas::SYSTEM_TEST_RENAMED . '".', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula renaming was successful
        $frm_renamed = new formula($t->usr1);
        $frm_renamed->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        if ($frm_renamed->id() > 0) {
            $result = $frm_renamed->name();
        }
        $target = formulas::SYSTEM_TEST_RENAMED;
        $t->assert('formula->load renamed formula "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and if the formula renaming has been logged
        $result = $t->log_last_by_field($frm_renamed, formula_db::FLD_NAME, $frm_renamed->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' changed "System Test Formula" to "System Test Formula Renamed"';
        $t->assert('formula->save rename logged for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // check if the formula parameters can be added
        $frm_renamed->usr_text = '= "' . words::THIS_NAME . '"';
        $frm_renamed->description = formulas::SYSTEM_TEST_RENAMED . ' description';
        $frm_renamed->type_id = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $frm_renamed->need_all_val = True;
        $frm_renamed->save($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert('formula->save all formula fields beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if the formula parameters have been added
        $frm_reloaded = $t_db->load_formula(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w161}=1-({f18}/{f20})';
        $t->assert('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and if the formula parameter adding have been logged
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_FORMULA_USER_TEXT, $frm_reloaded->id(), true);
        // use the next line if system config is non-standard
        $target = users::SYSTEM_TEST_NAME . ' changed "' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '" to = "' . words::THIS_NAME . '"';
        $target = users::SYSTEM_TEST_NAME . ' changed ""' . words::PERCENT . '" = 1 - ( "' . words::THIS_NAME . '" / "' . words::PRIOR_NAME . '" )" to "= "' . words::THIS_NAME . '""';
        $t->assert('formula->load resolved_text for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_FORMULA_TEXT, $frm_reloaded->id(), true);
        // use the next line if system config is non-standard
        // TODO Prio 1 review
        $target = users::SYSTEM_TEST_NAME . ' changed {w' . $wrd_percent->id() . '}=( {f' . $frm_this->id() . '} - {f5} ) / {f5} to ={f3}';
        $target = users::SYSTEM_TEST_NAME . ' changed "{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})" to "={f' . $frm_this->id() . '}"';
        $target = users::SYSTEM_TEST_NAME . ' changed "{w'
            . $wrd_percent->id() . '}=({f'
            . $frm_this->id() . '}-{f'
            . $frm_prior->id() . '})/{f'
            . $frm_prior->id() . '}" to "{w'
            . $wrd_percent->id() . '}=1-({f'
            . $frm_this->id() . '}/{f'
            . $frm_prior->id() . '})"';
        $t->assert('formula->load formula_text for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, sql_db::FLD_DESCRIPTION, $frm_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' added "System Test Formula Renamed description"';
        $t->assert('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_TYPE, $frm_reloaded->id(), true);
        // TODO review what is correct
        $target = users::SYSTEM_TEST_NAME . ' changed calc to this';
        $target = users::SYSTEM_TEST_NAME . ' added "' . words::THIS_NAME . '"';
        $target = users::SYSTEM_TEST_NAME . ' added "4"';
        $t->assert('formula->load formula_type_id for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $result, $target);
        $result = $t->log_last_by_field($frm_reloaded, formula_db::FLD_ALL_NEEDED, $frm_reloaded->id(), true);
        $target = users::SYSTEM_TEST_NAME . ' changed "0" to "1"';
        $t->assert('formula->load all_values_needed for "' . formulas::SYSTEM_TEST_RENAMED . '" logged', $result, $target);

        // check if a user-specific formula is created if another user changes the formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $frm_usr2->usr_text = '"' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"';
        $frm_usr2->description = formulas::SYSTEM_TEST_RENAMED . ' description2';
        $frm_usr2->type_id = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $frm_usr2->need_all_val = False;
        $frm_usr2->save($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert('formula->save all formula fields for user 2 beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user-specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '"' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"';
        $t->assert('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->ref_text;
        $target = '{w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description2';
        $t->assert('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::NEXT);
        $t->assert('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = False;
        $t->assert('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // ... and the formula for the original user remains unchanged
        $frm_reloaded = $t_db->load_formula(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // check if undo all specific changes removes the user formula
        $frm_usr2 = new formula($t->usr2);
        $frm_usr2->load_by_name(formulas::SYSTEM_TEST_RENAMED, formula::class);
        $frm_usr2->usr_text = '= "' . words::THIS_NAME . '"';
        $frm_usr2->description = formulas::SYSTEM_TEST_RENAMED . ' description';
        $frm_usr2->type_id = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $frm_usr2->need_all_val = True;
        $frm_usr2->save($usr_msg);
        $result = $usr_msg->get_last_message();
        $target = '';
        $t->assert('formula->save undo the user formula fields beside the name for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target, $t::TIMEOUT_LIMIT_DB_MULTI);

        // ... and if a user-specific formula changes have been saved
        $frm_usr2_reloaded = new formula($t->usr2);
        $frm_usr2_reloaded->load_by_name(formulas::SYSTEM_TEST_RENAMED);
        $result = $frm_usr2_reloaded->usr_text;
        $target = '= "' . words::THIS_NAME . '"';
        $t->assert('formula->load usr_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->ref_text;
        // TODO Prio 1 review
        $target = '={f' . $frm_this->id() . '}';
        $target = '{w' . $wrd_percent->id() . '}=1-({f' . $frm_this->id() . '}/{f' . $frm_prior->id() . '})';
        $t->assert('formula->load ref_text for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->description;
        $target = formulas::SYSTEM_TEST_RENAMED . ' description';
        $t->assert('formula->load description for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->type_id;
        $target = $sys->typ_lst->frm_typ->id(formula_type::THIS);
        $t->assert('formula->load type_id for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);
        $result = $frm_usr2_reloaded->need_all_val;
        $target = True;
        $t->assert('formula->load need_all_val for "' . formulas::SYSTEM_TEST_RENAMED . '"', $result, $target);

        // redo the user-specific formula changes
        // check if the user-specific changes can be removed with one click

        // check for formulas also that

        // TODO check if the word assignment can be done for each user

        // cleanup - fallback delete
        $t_frm->cleanup($ts);

    }

    function run_list(test_cleanup $t): void
    {
        $t_db = new test_db_load($t);

        // start the test section (ts)
        $ts = 'db write formula list ';
        $t->header($ts);

        // load the main test word
        $wrd_share = $t_db->test_word(words::TEST_SHARE);

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
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db create test formulas ';
        $t->header($ts);

        $t_db->test_word(words::TEST_EARNING);
        $t_db->test_word(words::TEST_PRICE);
        $t_db->test_word(words::TEST_PE);
        $t_db->test_formula(formulas::SYSTEM_TEST_RATIO, formulas::SYSTEM_TEST_RATIO_EXP, $usr_msg);
        $t_db->test_word(words::TEST_TOTAL);
        $t_db->test_formula(formulas::SYSTEM_TEST_SECTOR, formulas::SYSTEM_TEST_SECTOR_EXP, $usr_msg);
        //$t->test_formula(formulas::TN_THIS, formulas::TF_THIS);
        $t_db->test_word(words::TEST_THIS);
        $t_db->test_word(words::TEST_PRIOR);
        $t_db->test_formula(formulas::SYSTEM_TEST_ADD, formulas::INCREASE_EXP, $usr_msg);
        $t_db->test_formula(formulas::SYSTEM_TEST_EXCLUDED, formulas::INCREASE_EXP, $usr_msg);
        $t_db->test_word(words::TEST_IN_K);
        $t_db->test_word(words::TEST_BIL);
        $t_db->test_formula(formulas::SYSTEM_TEST_SCALE_K, formulas::SYSTEM_TEST_SCALE_K_EXP, $usr_msg);
        $t_db->test_formula(formulas::SYSTEM_TEST_SCALE_TO_K, formulas::SYSTEM_TEST_SCALE_TO_K_EXP, $usr_msg);
        $t_db->test_formula(formulas::SYSTEM_TEST_SCALE_MIO, formulas::SYSTEM_TEST_SCALE_MIO_EXP, $usr_msg);
        $t_db->test_formula(formulas::SYSTEM_TEST_SCALE_BIL, formulas::SYSTEM_TEST_SCALE_BIL_EXP, $usr_msg);

        // modify the special test cases
        global $usr;
        $frm = new formula($usr);
        $frm->load_by_name(formulas::SYSTEM_TEST_EXCLUDED);
        if ($frm->name() == '') {
            log_err('formula ' . formulas::SYSTEM_TEST_EXCLUDED . ' could not be loaded', 'create_test_formulas');
        } else {
            $frm->excluded = true;
            $frm->save($usr_msg);
        }
    }


}