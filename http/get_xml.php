<?php 

/*

  get_xml.php - get data from zukunft.com in the xml format
  -----------


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

Header('Content-type: text/xml');

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start_api("get_xml", "", $debug);

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // get the words that are supposed to be exported, sample "Nestlé%2Ccountryweight"
    $phrases = $_GET['words'];
    zu_debug("get_xml(".$phrases.")", $debug);
    $word_names = explode(",",$phrases);
    
    // get all related Phrases
    $phr_lst = New phrase_list;
    $phr_lst->usr = $usr;
    foreach ($word_names AS $wrd_name) {
      if ($wrd_name <> '') {
        $phr_lst->add_name($wrd_name, $debug-1);
      }  
    }
    if (count($phr_lst->lst) > 0) {
      $phr_lst->load($debug-1);
      $phr_lst = $phr_lst->are($debug-1);
    
      zu_debug("get_xml.php ... phrase loaded.", $debug-10);
      $xml_export = New xml_io;
      $xml_export->usr     = $usr;
      $xml_export->phr_lst = $phr_lst;
      $xml = $xml_export->export($debug-1);
    } else {
      $result .= zu_info('No XML can be created, because no word or triple is given.','', (new Exception)->getTraceAsString(), $this->usr);
    }

  } 
  
  if ($result <> '') {
    echo $result;
  } else { 
    print($xml); 
  }

// Closing connection
zu_end_api($link, $debug); 
?>
