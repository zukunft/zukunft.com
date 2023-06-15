<?php

/*

    view.php - create the final HTML code to display a zukunft.com view
    --------

    - the view contains the overall formatting like page size
    - the view component links to words, values or formulas
    - a view component can be linked to a view or a view component define by the view_link_type

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
use controller\controller;
use html\api;
use html\view\view_dsp_old;
use model\user;
use model\view;
use model\word;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . '/../';
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

// open database 
$db_con = prg_start("view");

global $system_views;

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately
$back = $_GET[controller::API_BACK]; // the word id from which this value change has been called (maybe later any page)

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // get the word(s) to display
    // TODO replace it with phrase
    $wrd = new word($usr);
    if (isset($_GET[api::PAR_VIEW_WORDS])) {
        $wrd->main_wrd_from_txt($_GET[api::PAR_VIEW_WORDS]);
    } else {
        // get last word used by the user or a default value
        $wrd = $usr->last_wrd();
    }

    // select the view
    if ($wrd->id() > 0) {
        // if the user has changed the view for this word, save it
        if (isset($_GET['new_id'])) {
            $view_id = $_GET['new_id'];
            $wrd->save_view($view_id);
        } else {
            // if the user has selected a special view, use it
            if (isset($_GET['view'])) {
                $view_id = $_GET['view'];
            } else {
                // if the user has set a view for this word, use it
                $view_id = $wrd->view_id();
                if ($view_id <= 0) {
                    // if any user has set a view for this word, use the common view
                    $view_id = $wrd->calc_view_id();
                    if ($view_id <= 0) {
                        // if no one has set a view for this word, use the fallback view
                        $view_id = $system_views->id(controller::DSP_WORD);
                    }
                }
            }
        }

        // create a display object, select and load the view and display the word according to the view
        if ($view_id > 0) {
            $dsp = new view_dsp_old($usr);
            $dsp->load_by_id($view_id, view::class);
            $dsp_text = $dsp->display($wrd, $back);

            // use a fallback if the view is empty
            if ($dsp_text == '' or $dsp->name() == '') {
                $view_id = $system_views->id(controller::DSP_START);
                $dsp->load_by_id($view_id, view::class);
                $dsp_text = $dsp->display($wrd, $back);
            }
            if ($dsp_text == '') {
                $result .= 'Please add a component to the view by clicking on Edit on the top right.';
            } else {
                $result .= $dsp_text;
            }
        } else {
            $result .= log_err('No view for "' . $wrd->name() . '" found.', "view.php", '', (new Exception)->getTraceAsString(), $usr);
        }

    } else {
        $result .= log_err("No word selected.", "view.php", '', (new Exception)->getTraceAsString(), $usr);
    }
}

echo $result;
// close the database  
prg_end($db_con);