<?php 

/*

  test_import.php - TESTing of the IMPORT functions by loading the sample import files
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

function import_file ($filename, $debug) {
  global $usr;

  $msg = '';
  
  $json_str = file_get_contents($filename); 
  $import = New file_import;
  $import->usr      = $usr;
  $import->json_str = $json_str;
  $import_result .= $import->put($debug-1);
  if ($import_result == '') {
    $msg .= ' done ('.$import->words_done.' words, '.$import->triples_done.' triples, '.$import->formulas_done.' formulas, '.$import->values_done.' sources, '.$import->sources_done.' values, '.$import->views_done.' views loaded)';
  } else {
    $msg .= ' failed because '.$import_result.'.';
  }
  return $msg;
}
  
function run_import_test ($debug) {

  global $usr;
  global $usr2;
  global $exe_start_time;
  
  global $error_counter;
  global $timeout_counter;
  global $total_tests;

  echo "<br><br><h2>Zukunft.com integration tests by importing the sample cases</h2><br>";

  //import_file('../test_cases/personal_climate_gas_emissions_timon.json', $debug-1); 
  
  $import_path = '../test_cases/';
  
  $file_list = array('companies.json', 
                     'ABB_2019.json', 
                     'personal_climate_gas_emissions_timon.json', 
                     'THOMY_test.json');
  foreach ($file_list AS $json_test_filename) {                               
    $result = import_file($import_path.$json_test_filename, $debug-1); 
    $target = 'done';
    $exe_start_time = test_show_contains(', import of '.$json_test_filename.' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_IMPORT);
  }
  
}

?>
