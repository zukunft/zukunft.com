<?php 

/*

  json_save.php - download a data file from zukunft.com in the json format
  -------------


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

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$db_con = prg_start_api("json_save");

  // load the session user parameters
  $usr = New user;
  $result = $usr->get();

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // get the words that are supposed to be exported, sample "Nestlé 2 country weight"
    $phrases = $_GET['words'];
    log_debug("json_save(".$phrases.")");
    $word_names = explode(",",$phrases);
    
    // get all related Phrases
    $phr_lst = New phrase_list;
    $phr_lst->usr = $usr;
    foreach ($word_names AS $wrd_name) {
      if ($wrd_name <> '') {
        $phr_lst->add_name($wrd_name);
      }  
    }
    
    if (count($phr_lst->lst) > 0) {
      $phr_lst->load();
      $phr_lst = $phr_lst->are();
    
      log_debug("json_save.php ... phrase loaded.");
      $json_export = New json_io;
      $json_export->usr     = $usr;
      $json_export->phr_lst = $phr_lst;
      $result = $json_export->export();
    } else {
      $result .= log_info('No JSON can be created, because no word or triple is given.','', (new Exception)->getTraceAsString(), $this->usr);
    }

    if ($result <> '') {
      echo $result;
    } else {
      // TODO replace with proper error message
      print(json_encode($phrases));
    }

  }

// Closing connection
prg_end_api($db_con);
