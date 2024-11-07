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

namespace unit_write;

use api\formula\formula as formula_api;
use api\word\word as word_api;
use cfg\verb;
use test\test_cleanup;

class element_write_tests
{

    function run(test_cleanup $t): void
    {
        global $verbs;

        $back = 0;

        $t->header('Test the formula element class (classes/element.php)');

        $t->subheader('prepare formula element write');
        $wrd_total = $t->test_word(word_api::TN_TOTAL);
        $frm_sector = $t->test_formula(formula_api::TN_SECTOR, formula_api::TF_SECTOR);

        // load increase formula for testing
        $frm = $t->load_formula(formula_api::TN_SECTOR);
        $exp = $frm->expression();
        $elm_lst = $exp->element_list();

        // get the test word ids
        $wrd_country = $t->load_word(word_api::TN_COUNTRY);
        $wrd_canton = $t->load_word(word_api::TN_CANTON);
        $vrb_id = $verbs->id(verb::CAN_CONTAIN);

        if (isset($elm_lst)) {
            $pos = 0;
            $target = '';
            foreach ($elm_lst->lst() as $elm) {
                if ($elm->obj == null) {
                    log_err('object of formula element ' . $elm->dsp_id() . ' missing');
                } else {
                    $elm->load_obj_by_id($elm->obj->id(), $elm->type);
                }

                $result = $elm->dsp_id();
                if ($pos == 0) {
                    $target = 'word "Country" (' . $wrd_country->id() . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 1) {
                    $target = 'verb "can be used as a differentiator for" (' . $vrb_id . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 2) {
                    $target = 'word "Canton" (' . $wrd_canton->id() . ') for user 3 (zukunft.com system test)';
                } elseif ($pos == 3) {
                    $target = 'word "System Test Word Total" (' . $wrd_total->id() . ') for user 3 (zukunft.com system test)';
                }
                $t->display('element->dsp_id', $target, $result);

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
                $t->display('element->dsp_id', $target, $result);

                $result = $elm->name_linked($back);
                if ($pos == 0) {
                    $target = '<a href="/http/view.php?v=1&id=' . $wrd_country->id() . '&o=words&back=0" title="Country">Country</a>';
                } elseif ($pos == 1) {
                    $target = 'can be used as a differentiator for';
                } elseif ($pos == 2) {
                    $target = '<a href="/http/view.php?v=1&id=' . $wrd_canton->id() . '&o=words&back=0" title="Canton">Canton</a>';
                } elseif ($pos == 3) {
                    $target = '<a href="/http/view.php?v=1&id=' . $wrd_total->id() . '&o=words&back=0" title="System Test Word Total">System Test Word Total</a>';
                }
                $t->display('element->dsp_id', $target, $result);

                $pos++;
            }
        } else {
            $result = 'formula element list not set';
            $target = '';
            $t->display('expression->element_lst', $target, $result);
        }

        $t->subheader('cleanup formula element write');
        $frm_sector->del();
        $wrd_total->del();

    }

    function run_list(test_cleanup $t): void
    {

        $back = 0;

        $t->header('Test the formula element list class (classes/element_list.php)');

        $t->subheader('prepare formula element write');
        $wrd_total = $t->test_word(word_api::TN_TOTAL);
        $frm_sector = $t->test_formula(formula_api::TN_SECTOR, formula_api::TF_SECTOR);

        // load increase formula for testing
        $frm = $t->load_formula(formula_api::TN_SECTOR);
        $exp = $frm->expression();
        $elm_lst = $exp->element_list();

        if (!$elm_lst->is_empty()) {
            $result = $elm_lst->name();
            $target = '"Country","can be used as a differentiator for","Canton","System Test Word Total"';
            $t->dsp_contains(', element_list->dsp_id', $target, $result);
        } else {
            $result = 'formula element list not set';
            $target = '';
            $t->display('element_list->dsp_id', $target, $result);
        }

        $t->subheader('cleanup formula element write');
        $frm_sector->del();
        $wrd_total->del();

    }

}