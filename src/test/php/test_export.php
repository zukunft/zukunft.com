<?php 

/*

  test_export.php - TESTing of the EXPORT functions
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

function run_export_test ($debug) {

  global $usr;
  global $exe_start_time;
  
  test_header('Test the xml export class (classes/xml.php)');

  $phr_lst = New phrase_list;
  $phr_lst->usr = $usr;
  $phr_lst->add_name(TEST_WORD);
  $phr_lst->load($debug-1);
  $xml_export = New xml_io;
  $xml_export->usr     = $usr;
  $xml_export->phr_lst = $phr_lst;
  $result = $xml_export->export($debug-1);
  $target = 'Company has a balance sheet';
  $exe_start_time = test_show_contains(', xml->export for '.$phr_lst->dsp_id().' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);

  test_header('Test the json export class (classes/json.php)');

  $json_export = New json_io;
  $json_export->usr     = $usr;
  $json_export->phr_lst = $phr_lst;
  $result = $json_export->export($debug-1);
  $target = 'Company has a balance sheet';
  $exe_start_time = test_show_contains(', json->export for '.$phr_lst->dsp_id().' contains at least '.$target, $target, $result, $exe_start_time, TIMEOUT_LIMIT_PAGE);
  
}
