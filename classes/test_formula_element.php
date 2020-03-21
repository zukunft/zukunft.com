<?php 

/*

  test_formula_element.php - TESTing of the FORMULA ELEMENT functions
  ------------------------
  

zukunft.com - calc with words

copyright 1995-2020 by zukunft.com AG, Zurich

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

function run_formula_element_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the formula element class (classes/formula_element.php)</h2><br>";

  // load increase formula for testing
  $frm = load_formula(TF_SECTOR, $debug-1);
  $exp = $frm->expression($debug-1);
  $elm_lst = $exp->element_lst ($back, $debug-1);

  if (isset($elm_lst)) {
    if (isset($elm_lst->lst)) {
      $pos = 0;
      foreach ($elm_lst->lst AS $elm) {
        $elm->load($debug-1);
        
        $result = $elm->dsp_id();
        if ($pos == 0) {
          $target = 'word "Sales" (6) for user zukunft.com system batch job';
        } elseif ($pos == 1) {
          $target = 'verb "can be used as a differentiator for" (12) for user zukunft.com system batch job';
        } elseif ($pos == 2) {
          $target = 'word "Sector" (54) for user zukunft.com system batch job';
        } elseif ($pos == 3) {
          $target = 'formula "Total Sales" (19) for user zukunft.com system batch job';
        } 
        $exe_start_time = test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
        
        $result = $elm->name($debug-1);
        if ($pos == 0) {
          $target = 'Sales';
        } elseif ($pos == 1) {
          $target = 'can be used as a differentiator for';
        } elseif ($pos == 2) {
          $target = 'Sector';
        } elseif ($pos == 3) {
          $target = 'Total Sales';
        } 
        $exe_start_time = test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
        
        $result = $elm->name_linked($back, $debug-1);
        if ($pos == 0) {
          $target = '<a href="/http/view.php?words=6&back=1">Sales</a>';
        } elseif ($pos == 1) {
          $target = 'can be used as a differentiator for';
        } elseif ($pos == 2) {
          $target = '<a href="/http/view.php?words=54&back=1">Sector</a>';
        } elseif ($pos == 3) {
          $target = '<a href="/http/formula_edit.php?id=19&back=1">Total Sales</a>';
        } 
        $exe_start_time = test_show_result(', formula_element->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
        
        $pos++;
      }
    } else {
      $result = 'formula element list is empty';
      $target = '';
      $exe_start_time = test_show_result(', expression->element_lst', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
    }
  } else {
    $result = 'formula element list not set';
    $target = '';
    $exe_start_time = test_show_result(', expression->element_lst', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

}

function run_formula_element_list_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the formula element list class (classes/formula_element_list.php)</h2><br>";

  // load increase formula for testing
  $frm = load_formula(TF_SECTOR, $debug-1);
  $exp = $frm->expression($debug-1);
  $elm_lst = $exp->element_lst ($back, $debug-1);

  if (isset($elm_lst)) {
    $result = $elm_lst->dsp_id($debug-1);
    $target = 'Sales can be used as a differentiator for Sector Total Sales';
    $exe_start_time = test_show_contains(', formula_element_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  } else {
    $result = 'formula element list not set';
    $target = '';
    $exe_start_time = test_show_result(', formula_element_list->dsp_id', $target, $result, $exe_start_time, TIMEOUT_LIMIT);
  }

}

?>
