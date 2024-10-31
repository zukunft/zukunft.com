<?php

/*

    phrase_list.php - return a phrase list API object
    ---------------

    This file is part of the frontend of zukunft.com - calc with words

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


/* standard zukunft header for callable php files to allow debugging and lib loading */

use controller\controller;
use html\html_base;
use html\view\view as view_dsp;
use html\word\word as word_dsp;
use cfg\term;
use cfg\triple;
use cfg\user;
use cfg\view;
use cfg\word;

$debug = $_GET['debug'] ?? 0;
const ROOT_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
include_once ROOT_PATH . 'src/main/php/zu_lib.php';

/* open database */
$db_con = prg_start("phrase_list");
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
    $msk_db = new view($usr);
    $msk_db->load_by_code_id(controller::MC_WORD_ADD);
    $msk = new view_dsp($msk_db->api_json());
    $back = $_GET[api::URL_VAR_BACK] = ''; // the calling page which should be displayed after saving

    // create the word object to have a place to update the parameters
    $wrd = new word($usr);

    // update the parameters on the object, so that the object save can update the database
    if (isset($_GET['word_name'])) {
        $wrd->set_name($_GET['word_name']);
    } // the name that must be unique for words, triples, formulas and verbs
    if (isset($_GET['type'])) {
        $wrd->type_id = $_GET['type'];
    }      // the type that adds special behavior to the word

    // all words should be linked to an existing word, so collect the parameters for the triple now
    $phr_id = $_GET['add'];  // id of an existing word that should be linked
    $vrb_id = $_GET['verb']; // id of the link between the words e.g. clicking add at Nestle is a company should lead to a question ... is (also) a company
    $phr_to = $_GET['word']; // a selected word where the new word should be linked to; e.g. company in the example above

    // if the user has pressed "save" it is 1
    if ($_GET['confirm'] > 0) {

        // check if either a new word text is entered by the user or the user as selected an existing word to link
        if ($wrd->name() == "" and $phr_id <= 0) {
            $msg .= 'Either enter a name for the new word or select an existing word to link.';
        }
        /*
        For easy adding of new words it is no longer needed to link a word to an existing word. Instead, a special page with the unlinked words should be added.
        if ($vrb_id == 0) {
          $msg .= 'Link missing; Please press back and select a triple, because all new words must be linked in a defined way to an existing word. ';
        }
        if ($wrd_to <= 0) {
          $msg .= 'Word missing; Please press back and select a related word, because all new words must be linked to an existing word. ';
        }
        */
        if ($wrd->type_id <= 0 and $wrd->name() <> "") {
            $phr_id = 0; // if new word in supposed to be added, but type is missing, do not add an existing word
            $msg .= 'Type missing; Please press back and select a word type. ';
        }

        // check if a word, verb or formula with the same name already exists
        if ($wrd->name() <> "") {
            $trm = new term($usr);
            $trm->load_by_name($wrd->name());
            if ($trm->id_obj() > 0) {
                /*
                // TODO: if a formula exists, suggest to create a word as a formula link, so that the formula results can be shown in parallel to the entered values
                if (substr($id_txt, 0, strlen(expression::MAKER_FORMULA_START)) == expression::MAKER_FORMULA_START) {
                  // maybe ask for confirmation
                  // change the link type to "formula link"
                  $wrd->type_id = cl(SQL_WORD_TYPE_FORMULA_LINK);
                  zu_debug('word_add -> changed type to ('.$wrd->type_id.')');
                } else {
                */
                $msg .= $trm->id_used_msg($this);
                log_debug();
                //}
            }

        } elseif ($phr_id > 0) {
            // check link of the existing word already exists
            $trp = new triple($usr);
            $trp->load_by_link_id($phr_id, $vrb_id, $phr_to);
            if ($trp->id() > 0) {
                $trp->load_objects();
                log_debug('forward link ' . $phr_id . ' ' . $vrb_id . ' ' . $phr_to . '');
                $msg .= '"' . $trp->from_name . ' ' . $trp->verb_name() . ' ' . $trp->to_name . '" already exists. ';
            }
            $trp_rev = new triple($usr);
            $trp_rev->load_by_link_id($phr_to, $vrb_id, $phr_id);
            if ($trp_rev->id() > 0) {
                $trp_rev->load_objects();
                $msg .= 'The reverse of "' . $trp_rev->from_name . ' ' . $trp_rev->verb_name() . ' ' . $trp_rev->to_name . '" already exists. Do you really want to add both sides? ';
            }
        }

        // if the parameters are fine ...
        if ($msg == '') {
            log_debug('no msg');
            $add_result = '';
            // ... add the new word to the database
            if ($wrd->name() <> "") {
                $add_result .= $wrd->save()->get_last_message();
            } else {
                $wrd->load_by_id($phr_id);
            }
            log_debug('test word');
            if ($wrd->id() > 0 and $vrb_id <> 0 and $phr_to > 0) {
                // ... and link it to an existing word
                log_debug('word ' . $wrd->id() . ' linked via ' . $vrb_id . ' to ' . $phr_to . ': ' . $add_result);
                $lnk = new triple($usr);
                $lnk->from()->set_id($wrd->id());
                $lnk->set_verb_id($vrb_id);
                $lnk->to()->set_id($phr_to);
                $add_result .= $lnk->save()->get_last_message();
            }

            // if adding was successful ...
            if (str_replace('1', '', $add_result) == '') {
                // if word has been added or linked successfully, go back
                //if ($wrd->id() > 0 AND $lnk->id <> 0 ) {
                // display the calling view
                //$result .= dsp_go_back($back, $usr);
                //}
            } else {
                // ... or in case of a problem prepare to show the message
                $msg .= $add_result;
            }
        }
    }

    // if nothing yet done display the add view (and any message on the top)
    if ($result == '') {
        // display the add view again
        $result .= $msk->dsp_navbar($back);
        $result .= $html->dsp_err($msg);

        $wrd_dsp = new word_dsp($wrd->api_json());
        $result .= $wrd_dsp->dsp_add($phr_id, $phr_to, $vrb_id, $back);
    }
}

echo $result;

prg_end($db_con);