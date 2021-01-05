<?php 

/*

  test_user.php - TESTing of the USER display funtions
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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------
  
function run_user_test ($debug) {

  global $usr;
  global $exe_start_time;

  // test the user display after the word changes to have a sample case
  echo "<br><br><h2>Test the user display class (classes/user_display.php)</h2><br>";

  $result = $usr->dsp_edit($debug-1);
  $target = TEST_USER_NAME;
  $exe_start_time = test_show_contains(', user_display->dsp_edit', $target, $result, $exe_start_time, TIMEOUT_LIMIT);

  // display system user names
  echo "based on<br>";
  echo 'php user: '.$_SERVER['PHP_AUTH_USER'].'<br>';
  echo 'remote user: '.$_SERVER['REMOTE_USER'].'<br>';
  echo 'user id: '.$usr->id.'<br>';

}

?>
