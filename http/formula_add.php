<?php

/*

  formula_add.php - create a new formula
  ---------------
  
  formulas should never be linked to a single value, because always a "rule" must be defined
  
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

// header for all zukunft.com code 
$debug = $_GET['debug'] ?? 0;
include_once '../src/main/php/zu_lib.php';

// open database
$db_con = prg_start("formula_add");

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id > 0) {

    load_usr_data();

    // prepare the display
    $dsp = new view_dsp_old($usr);
    $dsp->id = cl(db_cl::VIEW, view::FORMULA_ADD);
    $dsp->load();
    $back = $_GET['back'];

    // init the formula object
    $frm = new formula($usr);

    // load the parameters to the formula object to display the user input again in case of an error
    if (isset($_GET['formula_name'])) {
        $frm->name = $_GET['formula_name'];
    } // the new formula name
    if (isset($_GET['formula_text'])) {
        $frm->usr_text = $_GET['formula_text'];
    } // the new formula text in the user format
    if (isset($_GET['description'])) {
        $frm->description = $_GET['description'];
    }
    if (isset($_GET['type'])) {
        $frm->type_id = $_GET['type'];
    }
    if ($_GET['need_all_val'] == 'on') {
        $frm->need_all_val = true;
    } else {
        $frm->need_all_val = false;
    }

    // get the word to which the new formula should be linked to
    $wrd = new word($usr);
    if (isset($_GET['word'])) {
        $wrd->id = $_GET['word'];
        $wrd->load();
    }

    // if the user has requested to add a new formula
    if ($_GET['confirm'] > 0) {
        log_debug();

        // check parameters
        if (!isset($wrd)) {
            $msg .= dsp_err('Word missing; Internal error, because a formula should always be linked to a word or a list of words.');
        }

        if ($frm->name == "") {
            $msg .= dsp_err('Formula name missing; Please give the unique name to be able to identify it.');
        }

        if ($frm->usr_text == "") {
            $msg .= dsp_err('Formula text missing; Please define how the calculation should be done.');
        }

        // check if a word, verb or formula with the same name already exists
        log_debug('word');
        $trm = $frm->term();
        if (isset($trm)) {
            if ($trm->id > 0) {
                $msg .= $trm->id_used_msg();
            }
        }
        log_debug('checked');

        // if the parameters are fine
        if ($msg == '') {
            log_debug('do');

            // add to db
            $add_result = $frm->save();

            // in case of a problem show the message
            if (str_replace('1', '', $add_result) <> '') {
                $msg .= $add_result;
            } else {

                // if adding was successful ...
                // link the formula to at least one word
                if ($wrd->id > 0) {
                    $phr = $wrd->phrase();
                    $add_result .= $frm->link_phr($phr);

                    // if linking was successful ...
                    if (str_replace('1', '', $add_result) == '') {
                        $result .= dsp_go_back($back, $usr);
                    } else {
                        // ... or in case of a problem prepare to show the message
                        $msg .= $add_result;
                    }
                }
            }
        }
    }

    // if nothing yet done display the edit view (and any message on the top)
    if ($result == '') {
        // show the header
        $result .= $dsp->dsp_navbar($back);
        $result .= dsp_err($msg);

        $result .= $frm->dsp_edit(0, $wrd, $back);
    }
}

// display any error message
$result .= dsp_err($msg);

echo $result;

prg_end($db_con);