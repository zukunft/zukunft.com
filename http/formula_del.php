<?php

/*

  formula_del.php - exclude or remove a formula
  ---------------
  
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

$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

$db_con = prg_start("formula_del");

$result = ''; // reset the html code var

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->id = cl(db_cl::VIEW, view::FORMULA_DEL);
    $dsp->load();
    $back = $_GET['back'];

    // get the parameters
    $formula_id = $_GET['id'];           // id of the formula that can be changed
    $confirm = $_GET['confirm'];

    // delete the link or ask for confirmation
    if ($formula_id > 0) {

        // init the formula object
        $frm = new formula($usr);
        $frm->id = $formula_id;
        $frm->load();

        if ($confirm == 1) {
            $frm->del();

            $result .= dsp_go_back($back, $usr);
        } else {
            // display the view header
            $result .= $dsp->dsp_navbar($back);

            if ($frm->is_used()) {
                $result .= btn_yesno("Exclude \"" . $frm->name . "\" ", "/http/formula_del.php?id=" . $formula_id . "&back=" . $back);
            } else {
                $result .= btn_yesno("Delete \"" . $frm->name . "\" ", "/http/formula_del.php?id=" . $formula_id . "&back=" . $back);
            }
        }
    } else {
        $result .= dsp_go_back($back, $usr);
    }
}

echo $result;

prg_end($db_con);