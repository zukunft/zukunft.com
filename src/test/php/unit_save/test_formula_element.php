<?php

/*

  test_formula_element.php - TESTing of the FORMULA ELEMENT functions
  ------------------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function run_formula_element_test(testing $t)
{

    $back = 0;

    $t->header('Test the formula element class (classes/formula_element.php)');

    // load increase formula for testing
    $frm = $t->load_formula(formula::TN_SECTOR);
    $exp = $frm->expression();
    $elm_lst = $exp->element_lst($back);

    if (isset($elm_lst)) {
        if (isset($elm_lst->lst)) {
            $pos = 0;
            $target = '';
            foreach ($elm_lst->lst as $elm) {
                $elm->load();

                $result = $elm->dsp_id();
                if ($pos == 0) {
                    $target = 'word "System Test Word Parent e.g. Country" (187) for user 2 (zukunft.com system test)';
                } elseif ($pos == 1) {
                    $target = 'verb "can be used as a differentiator for" (13) for user 2 (zukunft.com system test)';
                } elseif ($pos == 2) {
                    $target = 'word "System Test Word Category e.g. Canton" (188) for user 2 (zukunft.com system test)';
                } elseif ($pos == 3) {
                    $target = 'word "System Test Word Total" (200) for user 2 (zukunft.com system test)';
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
                    $target = '<a href="/http/view.php?words=187&back=0">System Test Word Parent e.g. Country</a>';
                } elseif ($pos == 1) {
                    $target = 'can be used as a differentiator for';
                } elseif ($pos == 2) {
                    $target = '<a href="/http/view.php?words=188&back=0">System Test Word Category e.g. Canton</a>';
                } elseif ($pos == 3) {
                    $target = '<a href="/http/view.php?words=200&back=0">System Test Word Total</a>';
                }
                $t->dsp('formula_element->dsp_id', $target, $result);

                $pos++;
            }
        } else {
            $result = 'formula element list is empty';
            $target = '';
            $t->dsp('expression->element_lst', $target, $result);
        }
    } else {
        $result = 'formula element list not set';
        $target = '';
        $t->dsp('expression->element_lst', $target, $result);
    }

}

function run_formula_element_list_test(testing $t)
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