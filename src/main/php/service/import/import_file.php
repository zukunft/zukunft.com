<?php 

/*

  import.php - IMPORT a json in the zukunft.com exchange format
  ----------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Zurich

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

# import a single json file
function import_json_file ($filename, $debug) {
  global $usr;

  $msg = '';
  
  $json_str = file_get_contents($filename); 
  $import = New file_import;
  $import->usr      = $usr;
  $import->json_str = $json_str;
  $import_result = $import->put($debug-1);
  if ($import_result == '') {
    $msg .= ' done ('.$import->words_done.' words, '.$import->triples_done.' triples, '.$import->formulas_done.' formulas, '.$import->values_done.' sources, '.$import->sources_done.' values, '.$import->views_done.' views loaded)';
  } else {
    $msg .= ' failed because '.$import_result.'.';
  }
  return $msg;
}
  
# import all zukunft.com base configuration json files
function import_base_config ($debug) {
  $import_path = '/src/main/resources/';

  zu_debug('load base config', $debug -1 );

  $file_list = unserialize (BASE_CONFIG_FILES);
  foreach ($file_list AS $filename) {
    $result = import_json_file($import_path . $filename, $debug - 1);
  }

  zu_debug('load base config ... done', $debug -1 );

  return $result;
}

?>
