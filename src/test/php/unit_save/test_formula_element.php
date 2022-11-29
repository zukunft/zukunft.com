<?php

/*

  test_formula_element.php - TESTing of the FORMULA ELEMENT functions
  ------------------------
  

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

function run_formula_element_test(testing $t)
{

    $back = 0;

    $t->header('Test the formula element class (classes/formula_element.php)');

    // load increase formula for testing
    $frm = $t->load_formula(formula::TN_SECTOR);
    $exp = $frm->expression();
    $elm_lst = $exp->element_lst($back);

    // get the test word ids
    $wrd_country = $t->load_word(word::TN_COUNTRY);
    $wrd_canton = $t->load_word(word::TN_CANTON);
    $wrd_total = $t->load_word(word::TN_TOTAL);
    $vrb_id = cl(db_cl::VERB, verb::CAN_CONTAIN);

    if (isset($elm_lst)) {
        $pos = 0;
        $target = '';
        foreach ($elm_lst->lst as $elm) {
            if ($elm->obj == null) {
                log_err('object of formula element ' . $elm->dsp_id() . ' missing');
            } else {
                $elm->load_by_id($elm->obj->id());
            }

            $result = $elm->dsp_id();
            if ($pos == 0) {
                $target = 'word "System Test Word Parent e.g. Country" (' . $wrd_country->id() . ') for user 2 (zukunft.com system test)';
            } elseif ($pos == 1) {
                $target = 'verb "can be used as a differentiator for" (' . $vrb_id . ') for user 2 (zukunft.com system test)';
            } elseif ($pos == 2) {
                $target = 'word "System Test Word Category e.g. Canton" (' . $wrd_canton->id() . ') for user 2 (zukunft.com system test)';
            } elseif ($pos == 3) {
                $target = 'word "System Test Word Total" (' . $wrd_total->id() . ') for user 2 (zukunft.com system test)';
            }
            $t->dsp('formula_element->dsp_id', $target, $result);

            $result = $elm->name();
            if ($pos == 0) {
                $target = 'System Test Word Parent e.g. Country';
            } elseif ($pos == 1) {
                $target = 'can be used as a differentiator for';
            } elseif ($pos == 2) {
                $target = 'System Test Word Category e.g. Canton';
            } elseif ($pos == 3) {
                $target = 'System Test Word Total';
            }
            $t->dsp('formula_element->dsp_id', $target, $result);

            $result = $elm->name_linked($back);
            if ($pos == 0) {
                $target = '<a href="/http/view.php?words=' . $wrd_country->id() . '&back=0" title="System Test Word Parent e.g. Country">System Test Word Parent e.g. Country</a>';
            } elseif ($pos == 1) {
                $target = 'can be used as a differentiator for';
            } elseif ($pos == 2) {
                $target = '<a href="/http/view.php?words=' . $wrd_canton->id() . '&back=0" title="System Test Word Category e.g. Canton">System Test Word Category e.g. Canton</a>';
            } elseif ($pos == 3) {
                $target = '<a href="/http/view.php?words=' . $wrd_total->id() . '&back=0" title="System Test Word Total">System Test Word Total</a>';
            }
            $t->dsp('formula_element->dsp_id', $target, $result);

            $pos++;
        }
    } else {
        $result = 'formula element list not set';
        $target = '';
        $t->dsp('expression->element_lst', $target, $result);
    }

}

function run_formula_element_list_test(testing $t): void
{

    $back = 0;

    $t->header('Test the formula element list class (classes/formula_element_list.php)');

    // load increase formula for testing
    $frm = $t->load_formula(formula::TN_SECTOR);
    $exp = $frm->expression();
    $elm_lst = $exp->element_lst($back);

    if (isset($elm_lst)) {
        $result = $elm_lst->dsp_id();
        $target = 'System Test Word Parent e.g. Country can be used as a differentiator for System Test Word Category e.g. Canton System Test Word Total';
        $t->dsp_contains(', formula_element_list->dsp_id', $target, $result);
    } else {
        $result = 'formula element list not set';
        $target = '';
        $t->dsp('formula_element_list->dsp_id', $target, $result);
    }

}