<?php

/*

  values_paste.php - add more than one values by pasting a table
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// standard zukunft header for callable php files to allow debugging and lib loading
use html\view\view_dsp_old;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("values_paste");

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->load_by_code_id(view::VALUE_ADD);
    /*
        // get the fixed parameters
        $new_tbl   = $_GET['table'];    // the value table as pasted by the user
        $src_id    = $_GET['source'];   // the source id as changed by the user
        $confirm   = $_GET['confirm'];  // 1 if the user has pressed "save"
        $back = $_GET['back'];     // the word id from which this value change has been called (maybe later any page)

        // get the linked words from url
        $wrd_pos  = 1;
        $wrd_ids  = array(); // suggested word for the new value that the user can change
        $type_ids = array(); // word to preselect the suggested words e.g. "Country" to list all their countries first for the suggested word; if the type id is -1 the word is not supposed to be adjusted e.g. when editing a table cell
        $db_ids   = array(); // database id of the link to identify link updates
        while (isset($_GET['word'.$wrd_pos])) {
          $wrd_ids[] = $_GET['word'.$wrd_pos];
          if (isset($_GET['type'.$wrd_pos])) {
            $type_ids[] = $_GET['type'.$wrd_pos];
          } else {
            $type_ids[] = 0;
          }
          if (isset($_GET['db'.$wrd_pos])) {
            $db_ids[] = $_GET['db'.$wrd_pos];
          } else {
            $db_ids[] = 0;
          }
          $wrd_pos = $wrd_pos + 1;
        }
        zu_debug("value_add ... words " .implode(",",$wrd_ids) .".");
        zu_debug("value_add ... types " .implode(",",$type_ids).".");
        zu_debug("value_add ... db ids ".implode(",",$db_ids).  ".");

        if ($confirm > 0 AND $new_tbl <> '') {

          // adjust the user entries for the database
          $new_tbl = v_convert($new_tbl, $usr->id());

          // add the new value to the database
          $val_wrd_lst = New word_list;
          $val_wrd_lst->ids = $wrd_ids;
          $val_wrd_lst->set_user($usr);
          $val_wrd_lst->load();
          $val = New value;
          $val->
          $val_id = v_db_add($new_tbl, $wrd_ids, $usr->id());

          if ($val_id > 0) {
            // save the source
            if ($src_id > 0) {
              zuv_db_add($val_id, $src_id, $usr->id());
              zuu_set_source ($usr->id(), $src_id);
            }
          } else {
            zu_err("Adding ".$new_tbl." for words ".implode(",",$wrd_ids)." failed.","value_add");
          }

          $result .= dsp_go_back($back, $usr);
        } else {
          // display the view header
          $result .= $dsp->dsp_navbar($back);

          $result .= zuv_dsp_edit_or_add (0, $wrd_ids, $type_ids, $db_ids, $src_id, $back, $usr->id());

        } */
}

echo $result;

prg_end($db_con);
