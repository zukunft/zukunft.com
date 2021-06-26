<?php 

/*

  test_import.php - TESTing of the IMPORT functions by loading the sample import files
  ---------------
  

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

function run_import_test ($file_list) {

  global $exe_start_time;

  test_header('Zukunft.com integration tests by importing the sample cases');

  $import_path = PATH_TEST_IMPORT_FILES;
  
  foreach ($file_list AS $json_test_filename) {                               
    $result = import_json_file($import_path.$json_test_filename);
    $target = 'done';
    $exe_start_time = test_show_contains(', import of '.$json_test_filename.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_IMPORT);
  }
  
}
