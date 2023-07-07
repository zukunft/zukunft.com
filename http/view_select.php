<?php

/*

  view_select.php - define how a word should be displayed
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

// standard zukunft header for callable php files to allow debugging and lib loading
use html\view\view_dsp_old;
use html\word\word as word_dsp;
use cfg\user;
use cfg\view;
use cfg\word;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database
$db_con = prg_start("view_select");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $html = new \html\html_base();

    $usr->load_usr_data();

    // in view edit views the view cannot be changed
    $dsp = new view_dsp_old($usr);
    //$dsp->set_id(cl(SQL_VIEW_FORMULA_EXPLAIN));
    $back = $_GET[controller::API_BACK]; // the original calling page that should be shown after the change if finished
    $result .= $dsp->dsp_navbar_no_view($back);
    $view_id = 0;
    $word_id = $back;

    // get the view id used utils now and the word id
    if (isset($_GET[controller::URL_VAR_ID])) {
        $view_id = $_GET[controller::URL_VAR_ID];
    }
    if (isset($_GET['word'])) {
        $word_id = $_GET['word'];
    }

    // show the word name
    $wrd = new word($usr);
    if ($word_id > 0) {
        $wrd->load_by_id($word_id);
        $result .= $html->dsp_text_h2('Select the display format for "' . $wrd->name() . '"');
    } else {
        $result .= $html->dsp_text_h2('The word is missing for which the display format should be changed. If you can explain how to reproduce this error message, please report the steps on https://github.com/zukunft/zukunft.com/issues.');
    }

    // allow to change to type
    $dsp = new view($usr);
    $dsp->set_id($view_id);
    $result .= $dsp->selector_page($word_id, $back);

    // show the changes
    $wrd_html = new word_dsp($wrd->api_json());
    $result .= $wrd_html->log_view($back);
}

echo $result;

prg_end($db_con);
