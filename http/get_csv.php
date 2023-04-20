<?php

/*

  get_csv.php - get data from zukunft.com in the csv format
  -----------


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

use controller\controller;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("get_csv");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    load_usr_data();

    // sample "Nestlé 2 country weight"
    $words = $_GET[controller::URL_VAR_WORD];
    log_debug("get_csv(" . $words . ")");
    $word_names = explode(",", $words);

    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names($word_names);

    // get time word
    /*
      $time_word_lst = zut_time_lst($word_names);
      // shortcut, replace with a most_useful function
      $word_names = zu_lst_not_in($word_names);
      zu_debug("-> time word (".$time_word_id.")"); */
    log_debug("other words (" . implode(",", $word_names) . ")");

    // get formulas and related values
    $frm_lst = new formula_list($usr);
    $frm_lst->load_by_names($word_names);
    foreach ($frm_lst AS $frm) {
        if ($frm->ref_text <> '') {
            $val_lst = $frm->get_res_lst();
            if (!$val_lst->is_empty()) {
                $result .= $frm->ref_text . ',name' . "\r\n<br>";
                foreach ($val_lst as $val) {
                    $result .= '' . $val->name() . ',' . $val->val_formatted() . "\r\n<br>";
                }
            } else {
                $result .= $frm->ref_text . " \r\n<br>";
            }
        }
    }

    log_debug("words used (" . $wrd_lst->name() . ")");

}

echo $result;

// Closing connection
prg_end_api($db_con);
