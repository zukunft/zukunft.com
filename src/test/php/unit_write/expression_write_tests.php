<?php

/*

    test/php/unit_write/expression_tests.php - write test EXPRESSIONS to the database and check the results
    ----------------------------------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class expression_write_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_db = new test_db_load($t);
        $t->name = 'expression->';
        $lib = new library();
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write expression ';
        $t->header($ts);

        $t->subheader($ts . 'prepare');
        $wrd_price = $t_db->test_word(words::TEST_PRICE);
        $wrd_earning = $t_db->test_word(words::TEST_EARNING);
        $wrd_pe = $t_db->test_word(words::TEST_PE);
        $frm_ratio = $t_db->test_formula(formulas::SYSTEM_TEST_RATIO, formulas::SYSTEM_TEST_RATIO_EXP, $usr_msg);
        $wrd_total = $t_db->test_word(words::TEST_TOTAL);
        $frm_sector = $t_db->test_formula(formulas::SYSTEM_TEST_SECTOR, formulas::SYSTEM_TEST_SECTOR_EXP, $usr_msg);

        $back = '';

        // load formulas for expression testing
        $frm_this = $t_db->load_formula(formulas::SYSTEM_TEST_THIS);
        $frm = $t_db->load_formula(formulas::INCREASE);
        $frm_pe = $t_db->load_formula(formulas::SYSTEM_TEST_RATIO);

        $result = $frm_sector->usr_text;
        $target = '= "' . words::COUNTRY . '" "differentiator" "' . words::CANTON . '" / "' . words::TEST_TOTAL . '"';
        $t->assert('user text', $result, $target, $t::TIMEOUT_LIMIT_PAGE_LONG);

        // create expressions for testing
        $exp = new expression($frm);
        $exp->set_user_text($frm->usr_text);

        $exp_pe = new expression($frm);
        $exp_pe->set_user_text($frm_pe->usr_text);

        $exp_sector = new expression($frm);
        $exp_sector->set_user_text($frm_sector->usr_text);

        // load the test ids
        $wrd_percent = $t_db->load_word(words::PCT);
        $frm_this = $t_db->load_formula(formulas::THIS_NAME);
        $frm_prior = $t_db->load_formula(formulas::PRIOR);

        // test the expression processing of the user readable part
        $target = '"' . words::PCT . '"';
        $result = $exp->res_part_usr();
        $t->assert('res_part_usr for "' . $frm->usr_text . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG); // ??? why???
        $target = '( "' . formulas::THIS_NAME . '" - "' . formulas::PRIOR . '" ) / "' . formulas::PRIOR . '"';
        $result = $exp->r_part_usr();
        $t->assert('r_part_usr for "' . $frm->usr_text . '"', $result, $target);
        $target = 'true';
        $result = $lib->dsp_bool($exp->has_ref());
        $t->assert('has_ref for "' . $frm->usr_text . '"', $result, $target);
        $target = '{w' . $wrd_percent->id() . '}=({f' . $frm_this->id() . '}-{f' . $frm_prior->id() . '})/{f' . $frm_prior->id() . '}';
        $result = $exp->ref_text();
        $t->assert('get_ref_text for "' . $frm->usr_text . '"', $result, $target);

        // test the expression processing of the database reference
        $exp_db = new expression($frm);
        $exp_db->set_ref_text('{w' . $wrd_percent->id() . '} = ( is.numeric( {f' . $frm_this->id() . '} ) & is.numeric( {f' . $frm_prior->id() . '} ) ) ( {f' . $frm_this->id() . '} - {f' . $frm_prior->id() . '} ) / {f' . $frm_prior->id() . '}');
        $target = '"' . words::PERCENT . '"=( is.numeric( "' . formulas::THIS_NAME . '" ) & is.numeric( "' . formulas::PRIOR . '" ) ) ( "' . formulas::THIS_NAME . '" - "' . formulas::PRIOR . '" ) / "' . formulas::PRIOR . '"';
        $result = $exp_db->user_text();
        $t->assert('get_usr_text for "' . $exp_db->ref_text() . '"', $result, $target);

        // test getting phrases that should be added to the result of a formula
        $phr_lst_res = $exp->result_phrases();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . words::PCT . '"';
        $t->assert('res_phr_lst for "' . $exp->dsp_id() . '"', $result, $target, $t::TIMEOUT_LIMIT_LONG); // ??? why???

        // ... and the phrases used in the formula
        $phr_lst_res = $exp_pe->phr_lst();
        if ($phr_lst_res != null) {
            $result = $phr_lst_res->dsp_name();
        }
        $target = '"' . words::TEST_EARNING . '","' . words::TEST_PRICE . '"';
        // TODO Prio 0 activate
        //$t->assert('phr_lst for "' . $exp_pe->dsp_id() . '"', $result, $target);

        // ... and all elements used in the formula
        $elm_lst = $exp_sector->element_list();
        $result = $elm_lst->name();
        $target = '"Country","can be used as a differentiator for","Canton","System Test Word Total"';
        $t->assert('element_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

        // ... and all element groups used in the formula
        $elm_grp_lst = $exp_sector->element_grp_lst();
        $result = $elm_grp_lst->name();
        $target = '"Country,can be used as a differentiator for,Canton","System Test Word Total"';
        $t->assert('element_grp_lst for "' . $exp_sector->dsp_id() . '"', $result, $target);

        // test getting the phrases if the formula contains a verb
        // not sure if test is correct!
        $phr_lst = $exp_sector->phr_verb_lst();
        $result = $phr_lst->dsp_name();
        $target = '"Canton","Country","System Test Word Total"';
        // TODO $t->assert('phr_verb_lst for "' . $exp_sector->ref_text() . '"', $result, $target);

        // test getting special phrases
        $phr_lst = $exp->element_special_following();
        $result = $phr_lst->dsp_name();
        $target = '"' . formulas::THIS_NAME . '","' . formulas::PRIOR . '"';
        // TODO $t->assert('element_special_following for "'.$exp->dsp_id().'"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        // test getting for special phrases the related formula
        $frm_lst = $exp->element_special_following_frm();
        $result = $frm_lst->name();
        $target = '' . formulas::THIS_NAME . ',' . formulas::PRIOR . '';
        // TODO $t->assert('element_special_following_frm for "'.$exp->dsp_id().'"', $result, $target, $t::TIMEOUT_LIMIT_LONG);

        $t->subheader($ts . 'cleanup');
        $frm_ratio->del($usr_msg);
        $wrd_price->del($usr_msg);
        $wrd_earning->del($usr_msg);
        $wrd_pe->del($usr_msg);
        $frm_sector->del($usr_msg);
        $wrd_total->del($usr_msg);

    }

}