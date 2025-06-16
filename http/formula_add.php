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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

// header for all zukunft.com code 
$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
const PHP_PATH = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
include_once PHP_PATH . 'zu_lib.php';

include_once SHARED_CONST_PATH . 'views.php';

use cfg\formula\formula;
use cfg\user\user;
use cfg\view\view;
use cfg\word\word;
use html\formula\formula as formula_dsp;
use html\html_base;
use html\view\view as view_dsp;
use shared\api;
use shared\const\views as view_shared;

// open database
$db_con = prg_start("formula_add");
$html = new html_base();

$result = ''; // reset the html code var
$msg = ''; // to collect all messages that should be shown to the user immediately

// load the session user parameters
$usr = new user;
$result .= $usr->get();

// check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
if ($usr->id() > 0) {

    $usr->load_usr_data();

    // prepare the display
    $msk = new view($usr);
    $msk->load_by_code_id(view_shared::FORMULA_ADD);
    $back = $_GET[api::URL_VAR_BACK] = '';

    // init the formula object
    $frm = new formula($usr);

    // load the parameters to the formula object to display the user input again in case of an error
    if (isset($_GET['formula_name'])) {
        $frm->set_name($_GET['formula_name']);
    } // the new formula name
    if (isset($_GET[api::URL_VAR_USER_EXPRESSION])) {
        $frm->set_user_text($_GET[api::URL_VAR_USER_EXPRESSION]);
    } // the new formula text in the user format
    if (isset($_GET[api::URL_VAR_DESCRIPTION])) {
        $frm->description = $_GET[api::URL_VAR_DESCRIPTION];
    }
    if (isset($_GET['type'])) {
        $frm->type_id = $_GET['type'];
    }
    if ($_GET[api::URL_VAR_NEED_ALL] == 'on') {
        $frm->need_all_val = true;
    } else {
        $frm->need_all_val = false;
    }

    // get the word to which the new formula should be linked to
    $wrd = new word($usr);
    if (isset($_GET['word'])) {
        $wrd->load_by_id($_GET['word']);
    }

    // if the user has requested to add a new formula
    if ($_GET['confirm'] > 0) {
        log_debug();

        // check parameters
        if (!isset($wrd)) {
            $msg .= $html->dsp_err('Word missing; Internal error, because a formula should always be linked to a word or a list of words.');
        }

        if ($frm->name() == "") {
            $msg .= $html->dsp_err('Formula name missing; Please give the unique name to be able to identify it.');
        }

        if ($frm->usr_text == "") {
            $msg .= $html->dsp_err('Formula text missing; Please define how the calculation should be done.');
        }

        // check if a word, verb or formula with the same name already exists
        log_debug('word');
        $trm = $frm->get_term();
        if ($trm->id_obj() > 0) {
            $msg .= $html->dsp_err($trm->id_used_msg_text($this));
        }
        log_debug('checked');

        // if the parameters are fine
        if ($msg == '') {
            log_debug('do');

            // add to db
            $add_result = $frm->save()->get_last_message();

            // in case of a problem show the message
            if (str_replace('1', '', $add_result) <> '') {
                $msg .= $add_result;
            } else {

                // if adding was successful ...
                // link the formula to at least one word
                if ($wrd->id() > 0) {
                    $phr = $wrd->phrase();
                    $add_result .= $frm->link_phr($phr);

                    // if linking was successful ...
                    if (str_replace('1', '', $add_result) == '') {
                        $result .= $html->dsp_go_back($back, $usr);
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
        $msk_dsp = new view_dsp($msk->api_json());
        $result .= $msk_dsp->dsp_navbar($back);
        $result .= $html->dsp_err($msg);

        $frm_dsp = new formula_dsp($frm->api_json());
        $result .= $frm_dsp->dsp_edit(0, $wrd, $back);
    }
}

// display any error message
$result .= $html->dsp_err($msg);

echo $result;

prg_end($db_con);