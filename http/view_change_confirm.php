<?php

/*

  view_change_confirm.php - show the old and the new view and let the user confirm the change
  -----------------------
  
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
use controller\controller;
use html\html_base;
use html\view\view as view_dsp;
use cfg\user;
use cfg\view;
use cfg\word;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("view_confirm");
$html = new html_base();

$result = ''; // reset the html code var
$back = $_GET[controller::API_BACK]; // the word id from which this value change has been called (maybe later any page)
$word_id = $back;
$view_id = 0;

// get the view id used utils now and the word id
if (isset($_GET[controller::URL_VAR_ID])) {
    $view_id = $_GET[controller::URL_VAR_ID];
}
if (isset($_GET['word'])) {
    $word_id = $_GET['word'];
}

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // in view edit views the view cannot be changed
    if ($word_id <= 0) {
        $result .= $html->dsp_err('word not found');
    } else {
        $dsp = new view_dsp();
        //$dsp->set_id(cl(SQL_VIEW_FORMULA_EXPLAIN));
        $back = $word_id;
        $result .= $dsp->dsp_navbar_no_view($back);

        // show the word name
        $wrd = new word($usr);
        $wrd->load_by_id($word_id);
        $result .= $html->dsp_text_h2('Select the display format for "' . $wrd->name() . '"');
    }

    // allow to change to type
    if ($view_id <= 0) {
        $result .= $html->dsp_err('view not found');
    } else {
        $dsp = new view_dsp();
        $dsp->set_id($view_id);
        $result .= $dsp->selector_page($word_id, $back);
    }
}

echo $result;

prg_end($db_con);

