<?php

/*

    test/create/test_formulas.php - create the test formula objects
    -----------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_ELEMENT . 'element.php';
include_once paths::MODEL_ELEMENT . 'element_list.php';
include_once paths::MODEL_FORMULA . 'expression.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_FORMULA . 'formula_list.php';
include_once paths::MODEL_FORMULA . 'formula_type.php';
include_once paths::MODEL_FORMULA . 'formula_link.php';
include_once paths::MODEL_FORMULA . 'formula_link_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_TYPES . 'api_types.php';
include_once paths::SHARED_TYPES . 'formula_link_types.php';
include_once paths::SHARED_TYPES . 'protection_types.php';
include_once paths::SHARED_TYPES . 'share_types.php';
include_once html_paths::FORMULA . 'formula_list.php';
include_once html_paths::FORMULA . 'formula_link_list.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CREATE . 'test_const.php';
include_once test_paths::CREATE . 'test_objects.php';
include_once test_paths::UNIT . 'sys_log_tests.php';
include_once test_paths::UTILS . 'test_lib.php';

use Zukunft\ZukunftCom\main\php\cfg\element\element;
use Zukunft\ZukunftCom\main\php\cfg\element\element_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\expression;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_type;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\web\formula\formula_list as formula_list_ui;
use Zukunft\ZukunftCom\main\php\web\formula\formula_link_list as formula_link_list_ui;
use Zukunft\ZukunftCom\test\php\unit\sys_log_tests;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\formula_link_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\utils\test_lib;
use DateTime;


class test_formulas extends test_objects
{

    /*
     * cleanup
     */

    /**
     * delete any remaining test formulas for a clean test start
     */
    function cleanup(string $ts): void
    {
        parent::cleanup_objects($ts, formula_names::TEST_FORMULAS, new formula($this->env->usr1));

        // also clean up the triples, verbs and words used for the triples
        $t_trp = new test_triples($this->env);
        $t_trp->cleanup($ts);
    }


    /*
     * unit
     */

    /**
     * @return formula for testing of e.g. the expression calculation
     */
    function formula(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::SCALE_TO_SEC_ID, formula_names::SCALE_TO_SEC);
        $frm->set_user_text(formula_names::SCALE_TO_SEC_EXP, $t_trm->term_list_time());
        $frm->set_latex(formula_names::SCALE_TO_SEC_LATEX);
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula for db write testingthat does not have a reserved name
     */
    function formula_rename(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::SCALE_HOUR_ID, formula_names::SCALE_HOUR);
        $frm->set_user_text(formula_names::SCALE_HOUR_EXP, $t_trm->term_list_time());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula to scale a value from millions to one e.g. for the scaling tests
     */
    function formula_scale_mio(): formula
    {
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::SCALE_MIO_ID, formula_names::SCALE_MIO);
        $frm->ref_text = formula_names::SCALE_MIO_DB;
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula with only the name set to test reserving the name
     */
    function formula_name_only(): formula
    {
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::SCALE_TO_SEC_ID, formula_names::SCALE_MIO_EXP);
        $frm->ref_text = null;
        return $frm;
    }

    /**
     * @return formula where the mandatory expression is missing
     */
    function formula_incomplete(): formula
    {
        $frm = $this->formula();
        $t_trm = new test_terms($this->env);
        $frm->set_user_text('', $t_trm->term_list_time());
        $frm->ref_text = null;
        return $frm;
    }

    /**
     * @return formula with all object variables set for complete unit testing e.g. of the sql function creation
     */
    function formula_filled(): formula
    {
        global $sys;
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::SCALE_TO_SEC_ID, formula_names::SCALE_TO_SEC);
        // TODO Prio 1 activate
        //$frm->set_code_id(formula_names::SCALE_TO_SEC_CODE_ID, $this->env->usr_system);
        $frm->set_user_text(formula_names::SCALE_TO_SEC_EXP, $t_trm->term_list_time());
        // TODO Prio 1 activate
        //$frm->set_owner_id($this->env->usr1->id());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        $frm->description = formula_names::SCALE_TO_SEC_COM;
        $frm->need_all_val = true;
        $frm->last_update = new DateTime(sys_log_tests::TV_TIME);
        $frm->set_view_id(views::START_ID);
        $frm->usage = test_const::DUMMY_USAGE_FORMULA;
        $frm->impact = test_const::DUMMY_IMPACT;
        $frm->exclude();
        $frm->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $frm->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $frm;
    }

    /**
     * @return formula with all fields set and a reserved test name for testing the db write function
     */
    function formula_filled_not_db_ready(): formula
    {
        $frm = $this->formula_filled();
        $frm->usr_text = '';
        $frm->ref_text = '';
        return $frm;
    }

    function formula_add(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set_name(formula_names::SYSTEM_TEST_ADD);
        $frm->set_user_text(formula_names::INCREASE_EXP, $t_trm->term_list_increase());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula with all fields set and a reserved test name for testing the db write function
     */
    function formula_filled_add(): formula
    {
        global $sys;
        $frm = $this->formula_add();
        // TODO Prio 1 activate
        //$frm->set_code_id(formula_names::SCALE_TO_SEC_CODE_ID, $this->env->usr_system);
        //$frm->set_owner_id($this->env->usr1->id());
        $frm->description = formula_names::SCALE_TO_SEC_COM;
        $frm->need_all_val = true;
        $frm->last_update = new DateTime(sys_log_tests::TV_TIME);
        $frm->set_view_id(views::START_ID);
        $frm->usage = test_const::DUMMY_USAGE_FORMULA;
        $frm->impact = test_const::DUMMY_IMPACT;
        $frm->exclude();
        $frm->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $frm->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $frm;
    }

    /**
     * @return formula to test the "increase" calculations
     */
    function formula_increase(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::INCREASE_ID, formula_names::INCREASE);
        $frm->set_user_text(formula_names::INCREASE_EXP, $t_trm->term_list_increase());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula to select the actual value related to the given context
     */
    function formula_this(): formula
    {
        global $sys;
        $t_phr = new test_phrases($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::THIS_ID, formula_names::THIS_NAME);
        $frm->set_user_text(formula_names::THIS_EXP, $t_phr->phrase_list_increase()->term_list());
        $frm->set_type(formula_type::THIS, $this->env->usr1);
        $frm->description = formula_names::THIS_COM;
        $frm->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $frm;
    }

    /**
     * @return formula to select the last value previous the actual value related to the given context
     */
    function formula_prior(): formula
    {
        $t_phr = new test_phrases($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::PRIOR_ID, formula_names::PRIOR);
        $frm->set_user_text(formula_names::PRIOR_EXP, $t_phr->phrase_list_increase()->term_list());
        $frm->set_type(formula_type::PREV, $this->env->usr1);
        return $frm;
    }

    /**
     * @return formula to get the sum of all people living in cities
     */
    function formula_city_population(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set(formula_names::CITY_POPULATION_ID, formula_names::CITY_POPULATION);
        $frm->set_user_text(formula_names::CITY_POPULATION_EXP, $t_trm->term_list_increase());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    function formula_list_short(): formula_list
    {
        $lst = new formula_list($this->env->usr1);
        $lst->add($this->formula());
        return $lst;
    }

    function formula_list(): formula_list
    {
        $lst = new formula_list($this->env->usr1);
        $lst->add($this->formula());
        $lst->add($this->formula_increase());
        $lst->add($this->formula_this());
        $lst->add($this->formula_prior());
        $lst->add($this->formula_city_population());
        return $lst;
    }

    function formula_list_ui(): formula_list_ui
    {
        $tl = new test_lib();
        return $tl->list_to_ui($this->formula_list());
    }

    function formula_link(): formula_link
    {
        global $sys;
        $t_wrd = new test_words($this->env);
        $lnk = new formula_link($this->env->usr1);
        $lnk->set(1, $this->formula(), $t_wrd->word_minute()->phrase());
        $lnk->set_predicate_id($sys->typ_lst->frm_lnk_typ->id(formula_link_types::TIME_PERIOD));
        $lnk->order_nbr = 2;
        return $lnk;
    }

    /**
     * @return formula_link the increase formula assigned to the word "mathematics"
     *         as a sample to show the formula list on the default word page
     */
    function formula_link_increase(): formula_link
    {
        $t_wrd = new test_words($this->env);
        $lnk = new formula_link($this->env->usr1);
        $lnk->set(2, $this->formula_increase(), $t_wrd->word()->phrase());
        return $lnk;
    }

    function formula_link_add(): formula_link
    {
        $t_wrd = new test_words($this->env);
        $lnk = new formula_link($this->env->usr1);
        $lnk->set_formula($this->formula_add());
        $lnk->set_phrase($t_wrd->word_add()->phrase());
        return $lnk;
    }

    function formula_link_incomplete(): formula_link
    {
        $t_wrd = new test_words($this->env);
        $lnk = $this->formula_link();
        $lnk->set_phrase($t_wrd->word_incomplete()->phrase());
        return $lnk;
    }

    function formula_link_filled(): formula_link
    {
        global $sys;
        $lnk = $this->formula_link();
        $lnk->exclude();
        $lnk->set_share_id($sys->typ_lst->shr_typ->id(share_types::GROUP));
        $lnk->set_protection_id($sys->typ_lst->ptc_typ->id(protection_types::USER));
        return $lnk;
    }

    function formula_link_filled_add(): formula_link
    {
        $t_wrd = new test_words($this->env);
        $lnk = $this->formula_link();
        $lnk->include();
        $lnk->id = 0;
        $lnk->set_formula($this->formula_filled_add());
        $lnk->set_phrase($t_wrd->word_filled_add()->phrase());
        return $lnk;
    }

    function formula_link_list(): formula_link_list
    {
        $lst = new formula_link_list($this->env->usr1);
        $lst->add_link($this->formula_link());
        $lst->add_link($this->formula_link_increase());
        return $lst;
    }

    function formula_link_list_ui(): formula_link_list_ui
    {
        $tl = new test_lib();
        $lnk_lst = $this->formula_link_list();
        return $tl->list_to_ui($lnk_lst, [api_types::INCL_PHRASES]);
    }

    /**
     * @return formula to test the sql insert via function
     */
    function formula_add_by_func(): formula
    {
        $t_trm = new test_terms($this->env);
        $frm = new formula($this->env->usr1);
        $frm->set_name(formula_names::SYSTEM_TEST_ADD_VIA_FUNC);
        $frm->set_user_text(formula_names::INCREASE_EXP, $t_trm->term_list_increase());
        $frm->set_type(formula_type::CALC, $this->env->usr1);
        return $frm;
    }

    function expression(): expression
    {
        $t_trm = new test_terms($this->env);
        $trm_lst = $t_trm->term_list_time();
        return $this->formula()->expression($trm_lst);
    }

    function element(): element
    {
        $elm_lst = $this->element_list();
        return $elm_lst->lst()[0];
    }

    function element_list(): element_list
    {
        $t_trm = new test_terms($this->env);
        $usr_msg = new user_message();
        $trm_lst = $t_trm->term_list_time();
        $frm = $this->formula();
        $exp = $frm->expression($trm_lst);
        $elm_lst = $exp->element_list($usr_msg, $trm_lst);
        return $this->add_seq_number_to_element_list($elm_lst);
    }

    function add_seq_number_to_element_list(element_list $elm_lst): element_list
    {
        $id = 1;
        foreach ($elm_lst->lst() as $elm) {
            if ($elm->id() == 0) {
                $elm->id = $id++;
            }
        }
        return $elm_lst;
    }

    /*
     * formula test creation
     */

    /**
     * create a formula with random parameters for speed testing
     *
     * @param int|null $id a given sequence number to assure that the word name is unique
     * @return formula the created formula object
     */
    function random(?int $id = null): formula
    {
        global $sys;

        if ($id == null) {
            $id = $this->env->next_seq_nbr();
        }
        $test_usr = $this->env->usr1;

        $frm = new formula($test_usr);
        $frm->id = $id;
        $frm->set_name(formula_names::TEST_SPEED_PREFIX . $id);

        $type_id = rand(1, $sys->typ_lst->frm_typ->count());
        $frm->set_type_id($type_id, $test_usr);
        return $frm;
    }

}