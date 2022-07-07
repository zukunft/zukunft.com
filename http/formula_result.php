<?php

/*

  formula_result.php - explains one formula result
  ------------------
  
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

// for callable php files the standard zukunft.com header to load all classes and allow debugging
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

$db_con = prg_start("formula_result");

$result = ''; // reset the html code var

// load the session user parameters
$session_usr = new user;
$result .= $session_usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($session_usr->id > 0) {

    load_usr_data();

    // show the header
    $dsp = new view_dsp($session_usr);
    $dsp->id = cl(db_cl::VIEW, view::FORMULA_EXPLAIN);
    $back = $_GET['back']; // the page (or phrase id) from which formula testing has been called
    $result .= $dsp->dsp_navbar($back);

    // get the parameters
    $frm_val_id = $_GET['id'];      // id of the formula result if known already
    $frm_id = $_GET['formula']; // id of the formula which values should be explained
    $phr_id = $_GET['word'];    // id of the leading word used to order the result explaining
    //$wrd_group_id = $_GET['group'];   // id of the word group (excluding and time word)
    $time_id = $_GET['time'];    // id of the time word for which the value is valid (always the end of the period e.g. a value for 2016 is valid at the end of the year)

    // explain the result
    if ($frm_val_id > 0 or $frm_id > 0) {
        $fv = new formula_value($session_usr);
        $fv->load_by_id($frm_val_id);
        if ($fv->id > 0) {
            $result .= $fv->explain($phr_id, $back);
        } else {
            $result .= log_err("Formula result with id " . $frm_val_id . ' not found.', "formula_result.php");
        }
        log_debug('formula_result.php explained (id' . $fv->id . ' for user ' . $session_usr->name . ')');
    } else {
        // ... or complain about a wrong call
        $url_txt = "";
        foreach ($_GET as $key => $value) {
            $url_txt .= $key . '=' . $value . ',';
        }
        $result .= log_err("Wrong parameters: " . $url_txt, "formula_result.php");
    }
}

echo $result;

prg_end($db_con);