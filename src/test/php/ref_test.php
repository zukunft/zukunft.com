<?php 

/*

  ref_test.php - TESTing of the REF class
  ------------
  

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

function run_ref_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  dsp_test_header ('Test the ref class (src/main/php/model/ref/ref.php)');

  // load by phrase and type
  $wrd_abb = load_word(TW_ABB, $debug-1);
  $ref_type = get_ref_type_by_name(TRT_WIKIDATA, $debug-1);
  $ref = New ref;
  $ref->usr         = $usr;
  $ref->phr_id      = $wrd_abb->id;
  $ref->ref_type_id = $ref_type->id;
  $ref->load($debug-1);
  $result = $ref->external_key;
  $target = TR_WIKIDATA_ABB;
  $exe_start_time = test_show_result(', ref->load "'.TW_ABB.'" in '.TRT_WIKIDATA, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
  
  if ($ref->id > 0) {
    // load by id and test the loading of the objects
    $ref2 = New ref;
    $ref2->usr = $usr;
    $ref2->id  = $ref->id;
    $ref2->load($debug-1);
    $result = $ref2->phr->name;
    $target = TW_ABB;
    $exe_start_time = test_show_result(', ref->load_object ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
    $result = $ref2->ref_type->name;
    $target = TRT_WIKIDATA;
    $exe_start_time = test_show_result(', ref->load_object ', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);
  }

}