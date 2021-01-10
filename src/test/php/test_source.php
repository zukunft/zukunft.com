<?php 

/*

  test_source.php - TESTing of the SOURCE class
  ---------------
  

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

function run_source_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test the source class (classes/source.php)</h2><br>";

  $src = New source;
  $src->id = TS_NESN_2016_ID;
  $src->usr = $usr;
  $src->load($debug-1);
  $result = $src->name;
  $target = TS_NESN_2016_NAME;
  $exe_start_time = test_show_result(', source->load of ID "'.TS_NESN_2016_ID.'"', $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE_LONG);

}

?>
