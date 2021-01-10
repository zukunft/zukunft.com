<?php 

/*

  user.php - the main user page with the key settings of the user that is logged in
  --------


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

if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php';  if ($debug > 1) { echo 'lib loaded<br>'; }

$db_con = zu_start("user", "", $debug);

  $result = ''; // reset the html code var

  // get the parameters
  $id        = $_GET['id'];
  $back = $_GET['back'];
  $undo_val     = $_GET['undo_value'];
  $undo_wrd     = $_GET['undo_word'];
  $undo_lnk     = $_GET['undo_triple'];
  $undo_frm     = $_GET['undo_formula'];
  $undo_frm_lnk = $_GET['undo_formula_link'];
  $undo_dsp     = $_GET['undo_view'];
  $undo_cmp     = $_GET['undo_component'];
  $undo_cmp_lnk = $_GET['undo_view_link'];
  $undo_src     = $_GET['undo_source'];

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get($debug-1);
  $dsp_usr = $usr->dsp_user($debug-1);
  
  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {
    zu_debug("user -> (".$usr->id.")", $debug-1);
    
    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(SQL_VIEW_USER);
    $dsp->usr = $usr;
    $dsp->load($debug-1);
        
    // do user changes
    $result .= $usr->upd_pars ($_GET, $debug-1);
    
    // undo user changes for values
    if ($undo_val > 0) {
      $val = New value;
      $val->id = $undo_val;
      $val->usr = $usr;
      $val->del_usr_cfg($debug-1);
    }
    
    // undo user changes for words
    if ($undo_wrd > 0) {
      $wrd = New word_dsp;
      $wrd->id = $undo_wrd;
      $wrd->usr = $usr;
      $wrd->del_usr_cfg($debug-1);
    }
    
    // undo user changes for triples
    if ($undo_lnk > 0) {
      $lnk = New word_link;
      $lnk->id = $undo_lnk;
      $lnk->usr = $usr;
      $lnk->del_usr_cfg($debug-1);
    }
    
    // undo user changes for formulas
    if ($undo_frm > 0) {
      $frm = New formula;
      $frm->id = $undo_frm;
      $frm->usr = $usr;
      $frm->del_usr_cfg($debug-1);
    }
    
    // undo user changes for formula word links
    if ($undo_frm_lnk > 0) {
      $frm_lnk = New formula_link;
      $frm_lnk->id = $undo_frm_lnk;
      $frm_lnk->usr = $usr;
      $frm_lnk->del_usr_cfg($debug-1);
    }
    
    // undo user changes for formulas
    if ($undo_dsp > 0) {
      $dsp = new view;
      $dsp->id = $undo_dsp;
      $dsp->usr = $usr;
      $dsp->del_usr_cfg($debug-1);
    }
    
    // undo user changes for formulas
    if ($undo_cmp > 0) {
      $cmp = new view_component;
      $cmp->id = $undo_cmp;
      $cmp->usr = $usr;
      $cmp->del_usr_cfg($debug-1);
    }
    
    // undo user changes for formulas
    if ($undo_cmp_lnk > 0) {
      $cmp_lnk = new view_component_link;
      $cmp_lnk->id = $undo_cmp_lnk;
      $cmp_lnk->usr = $usr;
      $cmp_lnk->del_usr_cfg($debug-1);
    }
    
    $result .= $dsp->dsp_navbar($back, $debug-1);
    $result .= $dsp_usr->dsp_edit($back, $debug-1);

    // allow to import data
    if ($usr->can_import($debug-1)) {
      $result .= dsp_text_h2('<br>Data import<br>');
      $result .= dsp_text_h3('<br>Import <a href="/http/import.php">JSON</a><br>');
      $result .= dsp_text_h3('<br>');
    }

      // allow admins to test the system consistence
      if ($usr->is_admin($debug-1)) {
        $result .= dsp_text_h2('<br>System testing<br>');
        $result .= dsp_text_h3('<br>Perform all unit <a href="/http/test.php">tests</a><br>');
        $result .= dsp_text_h3('<br>Perform critical unit and integration <a href="/http/test_quick.php">tests</a><br>');
        $result .= dsp_text_h3('<br>');
      }

      // display the user sandbox if there is something in
    $sandbox = $dsp_usr->dsp_sandbox ($back, $debug-1);
    if (trim($sandbox) <> "") {
      $result .= dsp_text_h2("Your changes, which are not standard");
      $result .= $sandbox;
      $result .= dsp_text_h3('<br>');
    }

    // display the user changes 
    $changes = $dsp_usr->dsp_changes (0, SQL_ROW_LIMIT, 1, $back, $debug-1)  ;
    if (trim($changes) <> "") {
      $result .= dsp_text_h2("Your latest changes");
      $result .= $changes;
      $result .= dsp_text_h3('<br>');
    }

    // display the program issues that the user has found if there are some
    $errors = $dsp_usr->dsp_errors  ("", SQL_ROW_LIMIT, 1, $back, $debug-1);
    if (trim($errors) <> "") {
      $result .= dsp_text_h2("Program issues that you found, that have not yet been solved.");
      $result .= $errors;
      $result .= dsp_text_h3('<br>');
    }
    
    // display all program issues if the user is an admin
    if ($usr->profile_id == cl(SQL_USER_ADMIN)) {
      $errors_all = $dsp_usr->dsp_errors  ("other", SQL_ROW_LIMIT, 1, $back, $debug-1);
      if (trim($errors_all) <> "") {
        $result .= dsp_text_h2("Program issues that other user have found, that have not yet been solved.");
        $result .= $errors_all;
        $result .= dsp_text_h3('<br>');
      }
    }

    if ($_SESSION['logged']) {
      $result .= '<br><br><a href="/http/logout.php">logout</a>';
    }  
  }

  $result .= '<br><br>';
  $result .= btn_back($back);

  echo $result;

// Closing connection
zu_end($db_con, $debug);
