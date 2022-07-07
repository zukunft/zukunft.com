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

$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("get_csv");

// load the session user parameters
$usr = new user;
$result = $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // sample "Nestlé 2 country weight"
    $words = $_GET['words'];
    log_debug("get_csv(" . $words . ")");
    $word_names = explode(",", $words);

    $wrd_lst = (new word_list($usr))->load_by_names($word_names);

    // get time word
    $time_word_id = 0;
    /*  if (zut_has_time($word_names)) {
        $time_word_lst = zut_time_lst($word_names);
        // shortcut, replace with a most_useful function
        $time_word_id = $time_word_lst[0];
        $word_names = zu_lst_not_in($word_names, $time_word_id);
      } else {
        $time_word_id = zut_get_max_time($word_names[0], $word_names);
      }
      zu_debug("-> time word (".$time_word_id.")"); */
    log_debug("get_csv -> other words (" . implode(",", $word_names) . ")");

    // get formula
    $frm = new formula($usr);
    $formula_name = zut_get_formula($word_names);
    $formula_text = '';
    if ($formula_name <> '') {
        $frm->name = $formula_name;
        $frm->load();
        //$word_names = zu_lst_not_in($word_names, $formula_name);
        $word_names = array_diff($word_names, array($formula_name));
        log_debug("get_csv -> word names used (" . implode(",", $word_names) . ")");
        $formula_id = $frm->id;
        $formula_text = $frm->ref_text;
        log_debug("get_csv -> formula used (" . $formula_text . ")");
    }

    $word_lst = array_keys(zut_names_to_lst($word_names, $usr->id));
    log_debug("get_csv -> words used (" . implode(",", $word_lst) . ")");

    if ($formula_text <> '') {
        $in_result = $frm->to_num($word_lst, 0);
        $value_lst = $in_result[0];
        if (is_array($value_lst)) {
            $result .= $formula_name . ',name' . "\r\n<br>";
            foreach ($value_lst as $value_row) {
                $result .= '' . $value_row[0] . ',' . $value_row[1] . "\r\n<br>";
            }
        } else {
            $result .= $formula_name . ' ' . $value_lst . "\r\n<br>";
        }
    }
}

echo $result;

// Closing connection
prg_end_api($db_con);
