<?php 

/*

  word_add.php - to add a new word
  ------------

  
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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

  /*

  ------------------------
  commit and cancel button
  select a related word, because no word should be added without relation to an existing word
  select the relation type
  ------------------------

  To Do: 

  Split word into two words and create a group for the combined word
  Delete a word (check if nothing is depending on the word to delete)

  */


/* standard zukunft header for callable php files to allow debugging and lib loading */
if (isset($_GET['debug'])) { $debug = $_GET['debug']; } else { $debug = 0; }
include_once '../src/main/php/zu_lib.php'; if ($debug > 0) { echo 'libs loaded<br>'; }

/* open database */
$db_con = prg_start("word_add");

  $result = ''; // reset the html code var
  $msg    = ''; // to collect all messages that should be shown to the user immediately

  // load the session user parameters
  $usr = New user;
  $result .= $usr->get();

  // check if the user is permitted (e.g. to exclude crawlers from doing stupid stuff)
  if ($usr->id > 0) {

    // prepare the display
    $dsp = new view_dsp;
    $dsp->id = cl(DBL_VIEW_WORD_ADD);
    $dsp->usr = $usr;
    $dsp->load();
    $back = $_GET['back']; // the calling page which should be displayed after saving
        
    // create the word object to have an place to update the parameters
    $wrd = New word_dsp;
    $wrd->usr = $usr;
      
    // update the parameters on the object, so that the object save can update the database
    if (isset($_GET['word_name'])) { $wrd->name    = $_GET['word_name']; } // the name that must be unique for words, triples, formulas and verbs
    if (isset($_GET['type']))      { $wrd->type_id = $_GET['type']; }      // the type that adds special behavior to the word
    
    // all words should be linked to an existing word, so collect the parameters for the word link now
    $wrd_id = $_GET['add'];  // id of an existing word that should be linked 
    $vrb_id = $_GET['verb']; // id of the link between the words e.g. clicking add at Nestle is a company should lead to a question ... is (also) a company
    $wrd_to = $_GET['word']; // a selected word where the new word should be linked to; e.g. company in the example above

    // if the user has pressed "save" it is 1
    if ($_GET['confirm'] > 0) {
    
      // check if either a new word text is entered by the user or the user as selected an existing word to link 
      if ($wrd->name == "" AND $wrd_id <= 0) {
        $msg .= 'Either enter a name for the new word or select an existing word to link.';
      }
      /*
      For easy adding of new words it is no longer needed to link a word to an existing word. Instead a special page with the unlinked words should be added.
      if ($vrb_id == 0) {
        $msg .= 'Link missing; Please press back and select a word link, because all new words must be linked in a defined way to an existing word. ';
      }
      if ($wrd_to <= 0) {
        $msg .= 'Word missing; Please press back and select a related word, because all new words must be linked to an existing word. ';
      }
      */
      if ($wrd->type_id <= 0 AND $wrd->name <> "") {
        $wrd_id = 0; // if new word in supposed to be added, but type is missing, do not add an existing word
        $msg .= 'Type missing; Please press back and select a word type. ';
      }
      
      // check if a word, verb or formula with the same name already exists
      if ($wrd->name <> "") {
        $trm = New term;
        $trm->usr    = $usr;
        $trm->name   = $wrd->name;
        $trm->load();
        if ($trm->id > 0) {
          /*
          // todo: if a formula exists, suggest to create a word as a formula link, so that the formula results can be shown in parallel to the entered values
          if (substr($id_txt, 0, strlen(ZUP_CHAR_FORMULA_START)) == ZUP_CHAR_FORMULA_START) {
            // maybe ask for confirmation
            // change the link type to "formula link"
            $wrd->type_id = cl(SQL_WORD_TYPE_FORMULA_LINK);
            zu_debug('word_add -> changed type to ('.$wrd->type_id.')');
          } else {
          */
          $msg .= $trm->id_used_msg();
          log_debug('word_add -> ');
          //}  
        }  
      
      } elseif ($wrd_id > 0) {
        // check link of the existing word already exists
        $lnk_test = New word_link;
        $lnk_test->usr     = $usr;
        $lnk_test->from_id = $wrd_id;
        $lnk_test->verb_id = $vrb_id;
        $lnk_test->to_id   = $wrd_to;
        $lnk_test->load();
        if ($lnk_test->id > 0) {
          $lnk_test->load_objects();
          log_debug('word_add -> check forward link '.$wrd_id.' '.$vrb_id.' '.$wrd_to.'');
          $msg .= '"'.$lnk_test->from_name.' '.$lnk_test->verb_name.' '.$lnk_test->to_name.'" already exists. ';
        }
        $lnk_rev = New word_link;
        $lnk_rev->usr     = $usr;
        $lnk_rev->from_id = $wrd_to;
        $lnk_rev->verb_id = $vrb_id;
        $lnk_rev->to_id   = $wrd_id;
        $lnk_rev->load();
        if ($lnk_rev->id > 0) {
          $lnk_rev->load_objects();
          $msg .= 'The reverse of "'.$lnk_rev->from_name.' '.$lnk_rev->verb_name.' '.$lnk_rev->to_name.'" already exists. Do you really want to add both sides? ';
        }
      }
      
      // if the parameters are fine ...
      if ($msg == '') {
        log_debug('word_add -> no msg');
        $add_result = '';
        // ... add the new word to the database
        if ($wrd->name <> "") {
          $add_result .= $wrd->save();
        } else {
          $wrd->id = $wrd_id;
          $wrd->load();
        }
        log_debug('word_add -> test word');
        if ($wrd->id > 0 AND $vrb_id <> 0 AND $wrd_to > 0) {
          // ... and link it to an existing word
          log_debug('word_add -> word '.$wrd->id.' linked via '.$vrb_id.' to '.$wrd_to.': '.$add_result);
          $lnk = New word_link;
          $lnk->usr     = $usr;
          $lnk->from_id = $wrd->id;
          $lnk->verb_id = $vrb_id;
          $lnk->to_id   = $wrd_to;
          $add_result .= $lnk->save();
        }  

        // if adding was successful ...
        if (str_replace ('1','',$add_result) == '') {
          // if word has been added or linked successfully, go back
          //if ($wrd->id > 0 AND $lnk->id <> 0 ) {
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
    if ($result == '')  {
      // display the add view again
      $result .= $dsp->dsp_navbar($back);
      $result .= dsp_err($msg);

      $result .= $wrd->dsp_add ($wrd_id, $wrd_to, $vrb_id, $back);
    }
  }

  echo $result;

prg_end($db_con);