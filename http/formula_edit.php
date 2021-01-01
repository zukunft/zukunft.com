<?php 

/*

  formula_edit.php - change a formula
  ----------------
  
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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../lib/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

$link = zu_start("formula_edit", "", $debug);

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_FORMULA_EDIT);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
    $back = $_GET['back'];
        
    // create the formula object to have an place to update the parameters
    $frm = New formula;
    $frm->id  = $_GET['id']; // id of the formula that can be changed
    $frm->usr = $usr;
    $frm->load($debug-1);
    
    // load the parameters to the formula object to display the user input again in case of an error
    if (isset($_GET['formula_name']))  { $frm->name        = $_GET['formula_name']; } // the new formula name
    if (isset($_GET['formula_text']))  { $frm->usr_text    = $_GET['formula_text']; } // the new formula text in the user format
    if (isset($_GET['description']))   { $frm->description = $_GET['description']; }
    if (isset($_GET['type']))          { $frm->type_id     = $_GET['type']; }
    if ($_GET['need_all_val'] == 'on') { $frm->need_all_val = true; } else { if ($_GET['confirm'] == 1) { $frm->need_all_val = false; } }
    //if (isset($_GET['need_all_val']))  { if ($_GET['need_all_val'] == 'on') { $frm->need_all_val = true; } else { $frm->need_all_val = false; } }

    if ($frm->id <= 0) {
      $result .= zu_err("No formula found to change because the id is missing.", "formula_edit.php", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // do the direct changes initiated by other buttons than the save button
      // to link the formula to another word  
      if ($_GET['link_phrase'] > 0) {
        $phr = New phrase;
        $phr->id  = $_GET['link_phrase'];
        $phr->usr = $usr;
        $phr->load($debug-1);
        $upd_result .= $frm->link_phr($phr, $debug-1);
      }

      // to unlink a word from the formula 
      if ($_GET['unlink_phrase'] > 0) {
        $phr = New phrase;
        $phr->id  = $_GET['unlink_phrase'];
        $phr->usr = $usr;
        $phr->load($debug-1);
        $upd_result .= $frm->unlink_phr($phr, $debug-1);
      }

      // if the save botton has been pressed at least the name is filled (an empty name should never be saved; instead the word should be deleted)
      if ($frm->usr_text <> '') {

        // update the formula if it has been changed
        $upd_result = $frm->save($debug);

        // if update was successful ...
        if (str_replace ('1','',$upd_result) == '') {
          // ... display the calling view
          // because formula changing may need several updates the edit view is shown again
          //$result .= dsp_go_back($back, $usr, $debug-1);

          // trigger to update the related formula values / results
          if ($frm->needs_fv_upd) {
            // update the formula results
            $phr_lst = $frm->assign_phr_lst($debug-1);
            //$fv_list = $frm->calc($phr_lst, $debug-1);
          }
        } else {
          // ... or in case of a problem prepare to show the message
          $msg .= $upd_result;
        }
      }  

      // if nothing yet done display the edit view (and any message on the top)
      if ($result == '')  {
        // display the view header
        $result .= $dsp->dsp_navbar($back, $debug-1);
        $result .= dsp_err($msg);

        // display the view to change the formula
        $frm->load($debug-1); // reload to formula object to display the real database values
        if (isset($_GET['add_link'])) { $add_link = $_GET['add_link']; } else { $add_link = 0; } 
        $result .= $frm->dsp_edit ($add_link, 0, $back, $debug-1); // with add_link to add a link and display a word selector
      } 
    }
  }

  echo $result;

zu_end($link, $debug);
?>
