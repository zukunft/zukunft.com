<?php 

/*

  test_db_link.php - TESTing of the DataBase LINK functions
  ----------------
  

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

function run_db_link_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Test database link functions (zu_lib_sql_code_link.php)</h2><br>";

  // test zut_name 
  $id = DBL_SYSLOG_TBL_WORD;
  $target = 2;
  $result = cl($id);
  $exe_start_time = test_show_result(", sql_code_link ".$id, $target, $result, $exe_start_time, TIMEOUT_LIMIT);

}

?>
