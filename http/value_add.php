<?php 

/*

  value_add.php - add a value
  -------------
  
  This file is part of zukunft.com - calc with words

  zukunft.com is free software: you can redistribute it and/or modify it
  under the terms of the GNU General Public License as
  published by the Free Software Foundation, either version 3 of
  the License, or (at your option) any later version.
  zukunft.com is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

// open database
$link = zu_start("value_add", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude google from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_VALUE_ADD);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)
        
    // create the object to store the parameters so that if the add form is shown again it is already filled
    $val = New value;
    $val->usr = $usr;
          
    // before the value convertion, all phrases should be loaded to use the updated words for the conversion e.g. percent
    // get the linked phrases from url
    $phr_ids  = array(); // suggested word for the new value that the user can change
    $type_ids = array(); // word to preselect the suggested words e.g. "Country" to list all ther countries first for the suggested word
                         // if the type id is -1 the word is not supposed to be adjusted e.g. when editing a table cell
    if (isset($_GET['phrase1'])) {
      $phr_pos  = 1;
      while (isset($_GET['phrase'.$phr_pos])) {
        $phr_ids[] = $_GET['phrase'.$phr_pos];
        if (isset($_GET['type'.$phr_pos])) {
          $type_ids[] = $_GET['type'.$phr_pos];
        } else {
          $type_ids[] = 0;
        }
        $phr_pos++;
      }
      zu_debug("value_add -> phrases ".implode(",",$phr_ids) .".", $debug-12);
      zu_debug("value_add -> types "  .implode(",",$type_ids).".", $debug-12);
      $val->ids = $phr_ids;
    } elseif (isset($_GET['phrases'])) {
      $phr_ids = array();
      if ($_GET['phrases'] <> '') {
        $phr_ids = explode(",",$_GET['phrases']);
      }
      zu_debug("value_add -> phrases ".implode(",",$phr_ids) .".", $debug-12);
      $val->ids = $phr_ids;
    }

    // get the essential parameters for adding a value
    $new_val   = $_GET['value'];    // the value as changed by the user

    // if the user has pressed "save" confirm is 1
    if ($_GET['confirm'] > 0 AND $new_val <> '') {
    
      // adjust the user entries for the database
      $val->usr_value = $new_val;
      $val->convert($debug-1);

      // add the new value to the database
      $upd_result = $val->save($debug-1);
      
      // if update was successful ...
      if ($val->id > 0 AND str_replace ('1','',$upd_result) == '') {
        zu_debug("value_add -> save value done.", $debug-12);
        // update the parameters on the object, so that the object save can update the database
        // save the source id as changed by the user
        if (isset($_GET['source'])) { 
          $val->source_id = $_GET['source']; 
          if ($val->source_id > 0) {
            zu_debug("value_add -> save source".$val->source_id.".", $debug-12);
            $usr->set_source ($val->source_id, $debug-1);
            $upd_result = $val->save($debug-1);
            zu_debug("value_add -> save source done.", $debug-12);
          }
        } 
      } else {
        $result .= zu_err("Adding ".$new_val." for words ".implode(",",$val->ids)." failed (".$upd_result.").","value_add");
      }
    
      zu_debug("value_add -> go back to ".$back.".", $debug-12);
      $result .= dsp_go_back($back, $usr, $debug-1);
    }
    
    // if nothing yet done display the add view (and any message on the top)
    if ($result == '')  {
      // display the view header
      $result .= $dsp->dsp_navbar($back, $debug-1);
      $result .= dsp_err($msg);

      $result .= $val->dsp_edit($type_ids, $back, $debug-1);
    }
  }

  echo $result;

zu_end($link, $debug);
?>
