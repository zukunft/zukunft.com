<?php

/*

    test/php/unit_write/element_tests.php - write test FORMULA ELEMENTS to the database and check the results
    ---------------------------------------------
  

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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\api;
use Zukunft\ZukunftCom\main\php\web\element\element;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

include_once paths::SHARED_TYPES . 'verbs.php';

class element_write_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;

        // init
        $back = 0;
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula element ';
        $t->header($ts);

        $t->subheader($ts . 'prepare');
        $wrd_total = $t_db->test_word(words::TEST_TOTAL);
        $frm_sector = $t_db->test_formula(formulas::SYSTEM_TEST_SECTOR, formulas::SYSTEM_TEST_SECTOR_EXP, $usr_msg);

        // load increase formula for testing
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_SECTOR);
        $exp = $frm->expression();
        $elm_lst = $exp->element_list($usr_msg);

        // get the test word ids
        $wrd_country = $t_db->load_word(words::COUNTRY);
        $wrd_canton = $t_db->load_word(words::CANTON);
        $vrb_id = $sys->typ_lst->vrb->id(verbs::CAN_CONTAIN);

        if (isset($elm_lst)) {
            $pos = 0;
            $target = '';
            foreach ($elm_lst->lst() as $elm) {
                if ($elm->obj == null) {
                    log_err('object of formula element ' . $elm->dsp_id() . ' missing');
                } else {
                    $elm->load_obj_by_id($elm->obj->id, $elm->type());
                }

                $result = $elm->dsp_id();
                if ($pos == 0) {
                    $target = 'word "Country" (' . $wrd_country->id . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 1) {
                    $target = 'verb "can be used as a differentiator for" (' . $vrb_id . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 2) {
                    $target = 'word "Canton" (' . $wrd_canton->id . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 3) {
                    $target = 'word "System Test Word Total" (' . $wrd_total->id . ') for user 3 (zukunft.com system test)';
                }
                $t->assert('element->dsp_id', $result, $target);

                $result = $elm->name();
                if ($pos == 0) {
                    $target = 'Country';
                } elseif ($pos == 1) {
                    $target = 'can be used as a differentiator for';
                } elseif ($pos == 2) {
                    $target = 'Canton';
                } elseif ($pos == 3) {
                    $target = 'System Test Word Total';
                }
                $t->assert('element->dsp_id', $result, $target);

                $elm_dsp = new element($elm->api_json());
                $result = $elm_dsp->link($back);
                $url = '<a href="' . api::MAIN_SCRIPT_REL . '?' . url_var::MASK . '=' . views::WORD_ID . '&' . url_var::ID . '=';
                if ($pos == 0) {
                    $target = $url . $wrd_country->id . '&back=0" title="Country">Country</a>';
                } elseif ($pos == 1) {
                    $target = 'can be used as a differentiator for';
                } elseif ($pos == 2) {
                    $target = $url . $wrd_canton->id . '&back=0" title="Canton">Canton</a>';
                } elseif ($pos == 3) {
                    $target = $url . $wrd_total->id . '&back=0" title="System Test Word Total">System Test Word Total</a>';
                }
                // TODO Prio 0 activate
                //$t->assert('element->dsp_id', $result, $target);

                $pos++;
            }
        } else {
            $result = 'formula element list not set';
            $target = '';
            $t->assert('expression->element_lst', $result, $target);
        }

        $t->subheader($ts . 'cleanup formula element write');
        $usr_msg->reset(true);
        $usr_msg->usr = $t->usr1;
        $frm_sector->del($usr_msg);
        $wrd_total->del($usr_msg);

    }

    function run_list(test_cleanup $t): void
    {

        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write formula element list ';
        $t->header($ts);

        $t->subheader($ts . 'prepare');
        $wrd_total = $t_db->test_word(words::TEST_TOTAL);
        $frm_sector = $t_db->test_formula(formulas::SYSTEM_TEST_SECTOR, formulas::SYSTEM_TEST_SECTOR_EXP, $usr_msg);

        // load increase formula for testing
        $frm = $t_db->load_formula(formulas::SYSTEM_TEST_SECTOR);
        $trm_lst = $frm->load_terms($usr_msg);
        $exp = $frm->expression($trm_lst);
        $elm_lst = $exp->element_list($usr_msg, $trm_lst);

        if (!$elm_lst->is_empty()) {
            $result = $elm_lst->name();
            $target = '"Country","can be used as a differentiator for","Canton","System Test Word Total"';
            $t->dsp_contains(', element_list->dsp_id', $target, $result);
        } else {
            $result = 'formula element list not set';
            $target = '';
            $t->assert('element_list->dsp_id', $result, $target);
        }

        $t->subheader($ts . 'cleanup');
        $usr_msg->reset(true);
        $usr_msg->usr = $t->usr1;
        $frm_sector->del($usr_msg);
        $wrd_total->del($usr_msg);

    }

}