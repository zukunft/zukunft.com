<?php

/*

  word_link.php - the object that links two words (an RDF triple)
  -------------
  
  A link can also be used in replacement for a word
  e.g. Zurich (Company) where the the link "Zurich is a company" is used 
  
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

class word_link {

  // database fields
  public $id          = NULL; // the database id of the word link, which is the same for the standard and the user specific word link
  public $usr_cfg_id  = NULL; // the database id if there is already some user specific configuration for this link otherwise zero
  public $usr         = NULL; // the user object of the person for whom the triple is loaded, so to say the viewer
  public $owner_id    = NULL; // the user id of the person who created the link, so if another user wants to change it, a user specific record is created
  public $from_id     = NULL; // the id of the first phrase (a positive id is a word and a negative a triple)
  public $verb_id     = NULL; // the id of the link_type (negative of only the reverse link is valid)
  public $to_id       = NULL; // the id of the second phrase (a positive id is a word and a negative a triple)
  public $description = '';   // the description that may differ from the generic created text e.g. Zurich AG instead of Zurich (Company)
                              // if the description is empty the generic created name is used
  public $name        = '';   // the generic created name is saved in the database for faster check on duplicates
  public $excluded    = NULL; // for this object the excluded field is handled as a normal user sandbox field, but for the list excluded row are like deleted

  // in memory only fields
  public $from        = NULL; // the first object (either word, triple or group)
  public $from_name   = '';   // the name of the first object (either word, triple or group)
  public $verb        = NULL; // the link type object
  public $verb_name   = '';   // the name of the link type object (verb)
  public $to          = NULL; // the second object (either word, triple or group)
  public $to_name     = '';   // the name of the second object (either word, triple or group)

  // not used any more
  //public $from_type   = NULL; // the type id of the first word (either word, word link or word group)
  //public $to_type     = NULL; // the type id of the second word (either word, word link or word group)

  
  // reset the in memory fields used e.g. if some ids are updated
  private function reset_objects() {
    $this->from      = NULL;
    $this->from_name = '';
    $this->verb      = NULL;
    $this->verb_name = '';
    $this->to        = NULL;
    $this->to_name   = '';
  }
  
  // if needed reverse the order if the user has entered it the other way round
  // e.g. "Cask Flow Statement" "contains" "Taxes" instead of "Taxes" "is part of" "Cask Flow Statement"
  private function check_order($debug) {
    if ($this->verb_id <  0 ) {
      $to            = $this->to;
      $to_id         = $this->to_id;
      $to_name       = $this->to_name;
      $this->to      = $this->from;
      $this->to_id   = $this->from_id;
      $this->to_name = $this->from_name;
      $this->verb_id = $this->verb_id * - 1;
      if (isset($this->verb)) {
        $this->verb_name = $this->verb->reverse;
      }
      $this->from      = $to;
      $this->from_id   = $to_id;
      $this->from_name = $to_name;
      log_debug('word_link->check_order -> reversed', $debug-9);
    }
  }
  
  // load the word link without the linked objects, because in many cases the object are already loaded by the caller
  // similar to term->load, but with a different use of verbs
  function load_objects($debug) {
    log_debug('word_link->load_objects.'.$this->from_id.' '.$this->verb_id.' '.$this->to_id.'', $debug-7);
    
    // after every load call from outside the class the order should be check and reversed if needed
    $this->check_order($debug-1);
    
    // load word from
    if (!isset($this->from) AND $this->from_id <> 0 AND !is_null($this->usr->id)) {
      if ($this->from_id > 0) {
        $wrd = new word_dsp;
        $wrd->id  = $this->from_id;
        $wrd->usr = $this->usr;
        $wrd->load($debug-1);
        if ($wrd->name <> '') {
          $this->from = $wrd;
          $this->from_name = $wrd->name;
        }
      } elseif ($this->from_id < 0) {
        $lnk = New word_link;
        $lnk->id  = $this->from_id * -1;
        $lnk->usr = $this->usr;
        $lnk->load($debug-1);
        if ($lnk->id > 0) {
          $this->from = $lnk;
          $this->from_name = $lnk->name();
        }
      } else {
        // if type is not (yet) set, create a dummy object to enable the selection
        $phr = New phrase;
        $phr->usr = $this->usr;
        $this->from = $phr;
      }
      log_debug('word_link->load_objects -> from '.$this->from_name, $debug-7);
    } else {
      if (!isset($this->from)) {
        log_err("The word (".$this->from_id.") must be set before it can be loaded.", "word_link->load_objects", '', (new Exception)->getTraceAsString(), $this->usr);
      }  
    }  

    // load verb
    if (!isset($this->verb) AND $this->verb_id <> 0 AND !is_null($this->usr->id)) {
      $vrb = New verb;
      $vrb->id     = $this->verb_id;
      $vrb->usr_id = $this->usr->id;
      $vrb->load($debug-1);
      $this->verb = $vrb;
      $this->verb_name = $vrb->name;
      log_debug('word_link->load_objects -> verb '.$this->verb_name, $debug-7);
    } else {
      if (!isset($this->verb)) {
        log_err("The verb (".$this->verb_id.") must be set before it can be loaded.", "word_link->load_objects", '', (new Exception)->getTraceAsString(), $this->usr);
      }  
    }  

    // load word to
    if (!isset($this->to) AND $this->to_id <> 0 AND !is_null($this->usr->id)) {
      if ($this->to_id > 0) {
        $wrd_to = new word_dsp;
        $wrd_to->id  = $this->to_id;
        $wrd_to->usr = $this->usr;
        $wrd_to->load($debug-1);
        if ($wrd_to->name <> '') {
          $this->to = $wrd_to;
          $this->to_name = $wrd_to->name;
        }
      } elseif ($this->to_id < 0) {
        $lnk = New word_link;
        $lnk->id  = $this->to_id * -1;
        $lnk->usr = $this->usr;
        $lnk->load($debug-1);
        if ($lnk->id > 0) {
          $this->to = $lnk;
          $this->to_name = $lnk->name();
        }
      } else {
        // if type is not (yet) set, create a dummy object to enable the selection
        $phr_to = New phrase;
        $phr_to->usr = $this->usr;
        $this->to = $phr_to;
      }
      log_debug('word_link->load_objects -> to '.$this->to_name, $debug-7);
    } else {
      if (!isset($this->to)) {
        if ($this->to_id == 0) {
          // set a dummy word
          $wrd_to = New word_dsp; 
          $wrd_to->usr = $this->usr;
          $this->to = $wrd_to; 
        }  
      }  
    }
  }

  function load_standard($debug) {

    global $db_con;

    // after every load call from outside the class the order should be check and reversed if needed
    $this->check_order($debug-1);
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "l.word_link_id = ".$this->id;
    } elseif ($this->from_id <> 0 
          AND $this->verb_id  > 0 
          AND $this->to_id   <> 0) {
      $sql_where  =      "l.from_phrase_id = ".sf($this->from_id)."
                      AND l.verb_id        = ".sf($this->verb_id)."
                      AND l.to_phrase_id   = ".sf($this->to_id);
    // search for a backward link e.g. Cask Flow Statement contains Taxes
    } elseif ($this->from_id <> 0 
          AND $this->verb_id <  0 
          AND $this->to_id   <> 0) {
          
      $sql_where  =      "l.from_phrase_id = ".sf($this->to_id)."
                      AND l.verb_id        = ".sf($this->verb_id)."
                      AND l.to_phrase_id   = ".sf($this->from_id);
    }

    if ($sql_where == '') {
      log_err('The database ID ('.$this->id.') or the word and verb ids ('.$this->from_id.','.$this->verb_id.','.$this->to_id.') must be set to load a triple.', "word_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
      $sql = "SELECT l.word_link_id,
                     l.user_id,
                     l.from_phrase_id,
                     l.verb_id,
                     l.to_phrase_id,
                     l.name,
                     l.description,
                     l.excluded
                FROM word_links l 
               WHERE ".$sql_where.";";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_lnk = $db_con->get1($sql, $debug-5);  
      if ($db_lnk['word_link_id'] > 0) {
        $this->id           = $db_lnk['word_link_id'];
        $this->owner_id     = $db_lnk['user_id'];
        $this->from_id      = $db_lnk['from_phrase_id'];
        $this->verb_id      = $db_lnk['verb_id'];
        $this->to_id        = $db_lnk['to_phrase_id'];
        $this->name         = $db_lnk['name'];
        $this->description  = $db_lnk['description'];
        $this->excluded     = $db_lnk['excluded'];

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE word_links SET user_id = ".$this->usr->id." WHERE word_link_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "word_link->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          if ($sql_result <> '') {
            log_err('Value owner has been missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
          }
        }

        // automatically update the generic name
        $this->load_objects($debug-1);  
        $new_name = $this->name();
        log_debug('word_link->load_standard check if name '.$this->dsp_id().' needs to be updated to "'.$new_name.'"', $debug-10);
        if ($new_name <> $this->name) {
          $db_con->type = 'word_link';         
          $db_con->update($this->id, 'name', $new_name, $debug-1); 
          $this->name = $new_name;
        }
      } 
      log_debug('word_link->load_standard ... done ('.$this->description.')', $debug-10);
    }  
  }
  
  // load the word link without the linked objects, because in many cases the object are already loaded by the caller
  function load($debug) {
    log_debug('word_link->load.'.$this->from_id.' '.$this->verb_id.' '.$this->to_id.'', $debug-7);

    global $db_con;

    // after every load call from outside the class the order should be check and reversed if needed
    $this->check_order($debug-1);
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0 AND !is_null($this->usr->id)) {
      $sql_where = "l.word_link_id = ".$this->id;
    // search for a forward link e.g. Taxes is part of Cask Flow Statement
    } elseif ($this->from_id  <> 0 
          AND $this->verb_id   > 0 
          AND $this->to_id    <> 0 
          AND !is_null($this->usr->id)) {
      $sql_where  =      "l.from_phrase_id = ".sf($this->from_id)."
                      AND l.verb_id        = ".sf($this->verb_id)."
                      AND l.to_phrase_id   = ".sf($this->to_id);
    // search for a backward link e.g. Cask Flow Statement contains Taxes
    } elseif ($this->from_id  <> 0 
          AND $this->verb_id  <  0 
          AND $this->to_id    <> 0 
          AND !is_null($this->usr->id)) {
      $sql_where  =      "l.from_phrase_id = ".sf($this->to_id)."
                      AND l.verb_id        = ".sf($this->verb_id)."
                      AND l.to_phrase_id   = ".sf($this->from_id);
    /*
    // if the search including the type is not requested, try without the type  
    } elseif ($this->from_id  <> 0 
          AND $this->verb_id   > 0 
          AND $this->to_id    <> 0 
          AND !is_null($this->usr->id)) {
      $sql_where  =      "l.from_phrase_id = ".sf($this->from_id)."
                      AND l.verb_id        = ".sf($this->verb_id)."
                      AND l.to_phrase_id   = ".sf($this->to_id);
    */
    } elseif ($this->name <> '' AND !is_null($this->usr->id)) {
      $sql_where = "l.name = ".sf($this->name);
    }

    if ($sql_where == '') {
      if (is_null($this->usr->id)) {
        log_err("The user id must be set to load a word.", "word_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {  
        log_err('Either the database ID ('.$this->id.'), unique word link ('.$this->from_id.','.$this->verb_id.','.$this->to_id.') or the name ('.$this->name.') and the user ('.$this->usr->id.') must be set to load a word link.', "word_link->load", '', (new Exception)->getTraceAsString(), $this->usr);
      }
    } else {
      // similar statement used in word_link_list->load, check if changes should be repeated in word_link_list.php
      $sql = "SELECT l.word_link_id,
                     u.word_link_id AS user_link_id,
                     l.user_id,
                     l.from_phrase_id,
                     l.to_phrase_id,
                     l.verb_id,
                     l.name,
                     l.description,
                     IF(u.name IS NULL,        l.name,        u.name)        AS name,
                     IF(u.description IS NULL, l.description, u.description) AS description,
                     IF(u.excluded IS NULL,    l.excluded,    u.excluded)    AS excluded
                FROM word_links l 
           LEFT JOIN user_word_links u ON u.word_link_id = l.word_link_id 
                                      AND u.user_id = ".$this->usr->id." 
               WHERE ".$sql_where.";";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_lnk = $db_con->get1($sql, $debug-5);  
      if ($db_lnk['word_link_id'] > 0) {
        $this->id           = $db_lnk['word_link_id'];
        $this->usr_cfg_id   = $db_lnk['user_link_id'];
        $this->owner_id     = $db_lnk['user_id'];
        $this->from_id      = $db_lnk['from_phrase_id'];
        $this->to_id        = $db_lnk['to_phrase_id'];
        $this->verb_id      = $db_lnk['verb_id'];
        $this->name         = $db_lnk['name'];
        $this->description  = $db_lnk['description'];
        $this->excluded     = $db_lnk['excluded'];
        // automatically update the generic name
        $this->load_objects($debug-1);  
        $new_name = $this->name();
        log_debug('word_link->load check if name '.$this->dsp_id().' needs to be updated to "'.$new_name.'"', $debug-10);
        if ($new_name <> $this->name) {
          $db_con->type = 'word_link';         
          $db_con->update($this->id, 'name', $new_name, $debug-1); 
          $this->name = $new_name;
        }
      } 
      log_debug('word_link->load ... done ('.$this->name().')', $debug-10);
    }  
  }
      
  // recursive function to include the foaf words for this triple
  function wrd_lst ($debug) {
    log_debug('word_link->wrd_lst '.$this->dsp_id(), $debug-10);
    $wrd_lst = New word_list;
    $wrd_lst->usr = $this->usr;    

    // add the "from" side
    if (isset($this->from)) {
      if ($this->from->id > 0) {
        $wrd_lst->add($this->from, $debug-1);
      } elseif ($this->from->id < 0) {
        $sub_wrd_lst = $this->from->wrd_lst($debug-1);
        foreach ($sub_wrd_lst AS $wrd) {
          $wrd_lst->add($wrd, $debug-1);
        }
      } else {
        log_err('The from phrase '.$this->from->dsp_id().' should not have the id 0','word_link->wrd_lst', '', (new Exception)->getTraceAsString(), $this->usr);
      }
    }

    // add the "to" side
    if (isset($this->to)) {
      if ($this->to->id > 0) {
        $wrd_lst->add($this->to, $debug-1);
      } elseif ($this->to->id < 0) {
        $sub_wrd_lst = $this->to->wrd_lst($debug-1);
        foreach ($sub_wrd_lst AS $wrd) {
          $wrd_lst->add($wrd, $debug-1);
        }
      } else {
        log_err('The to phrase '.$this->to->dsp_id().' should not have the id 0','word_link->wrd_lst', '', (new Exception)->getTraceAsString(), $this->usr);
      }
    }
    
    log_debug('word_link->wrd_lst -> ('.$wrd_lst->name($debug-1).')', $debug-7);
    return $wrd_lst;
  }
  
      
  // create an object for the export
  function export_obj ($debug) {
    log_debug('word_link->export_obj', $debug-10);
    $result = New word_link();

    if ($this->name <> '')        { $result->name        = $this->name;        }
    if ($this->description <> '') { $result->description = $this->description; }
    $result->from = $this->from_name;
    $result->verb = $this->verb->name;
    $result->to   = $this->to_name;

    log_debug('word_link->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a view from an object
  function import_obj ($json_obj, $debug) {
    log_debug('word_link->import_obj', $debug-10);
    $result = '';
    
    foreach ($json_obj AS $key => $value) {
      if ($key == 'name')        { $this->name        = $value; }
      if ($key == 'description') { $this->description = $value; }
      if ($key == 'from')        {
        $phr_from = New phrase;
        $phr_from->name = $value;
        $phr_from->usr  = $this->usr;
        $phr_from->load($debug-1);
        if ($phr_from->id == 0) {
          $wrd = New word;
          $wrd->name = $value;
          $wrd->usr  = $this->usr;
          $wrd->load($debug-1);
          if ($wrd->id == 0) {
            $wrd->name = $value;
            $wrd->type_id = cl(DBL_WORD_TYPE_NORMAL);
            $wrd->save($debug-1);
          }
          if ($wrd->id == 0) {
            log_err('Cannot add from word "'.$value.'" when importing '.$this->dsp_id(), 'word_link->import_obj', '', (new Exception)->getTraceAsString(), $this->usr);
          } else {
            $this->from      = $wrd; 
            $this->from_id   = $wrd->id; 
            $this->from_name = $wrd->name; 
          }
        } else {         
          $this->from      = $phr_from; 
          $this->from_id   = $phr_from->id; 
          $this->from_name = $phr_from->name; 
        }  
      }
      if ($key == 'to')          {
        $phr_to = New phrase;
        $phr_to->name = $value;
        $phr_to->usr  = $this->usr;
        $phr_to->load($debug-1);
        if ($phr_to->id == 0) {
          $wrd = New word;
          $wrd->name = $value;
          $wrd->usr  = $this->usr;
          $wrd->load($debug-1);
          if ($wrd->id == 0) {
            $wrd->name = $value;
            $wrd->type_id = cl(DBL_WORD_TYPE_NORMAL);
            $wrd->save($debug-1);
          }
          if ($wrd->id == 0) {
            log_err('Cannot add to word "'.$value.'" when importing '.$this->dsp_id(), 'word_link->import_obj', '', (new Exception)->getTraceAsString(), $this->usr);
          } else {
            $this->to      = $wrd; 
            $this->to_id   = $wrd->id; 
            $this->to_name = $wrd->name; 
          }
        } else {         
          $this->to      = $phr_to; 
          $this->to_id   = $phr_to->id; 
          $this->to_name = $phr_to->name; 
        }  
      }
      if ($key == 'verb') { 
        $vrb = New verb;
        $vrb->name   = $value;
        $vrb->usr_id = $this->usr->id;
        $vrb->load($debug-1);
        if ($vrb->id <= 0) {
            // TODO add an error message
          $result .= ' verb "'.$value.'" not found';
          if ($this->name <> '') { $result .= ' for triple "'.$this->name.'"'; }
        } else {         
          $this->verb      = $vrb; 
          $this->verb_id   = $vrb->id; 
          $this->verb_name = $vrb->name; 
        }  
      }
    }
    if ($result == '') {
      $this->save($debug-1);
      log_debug('word_link->import_obj -> '.$this->dsp_id(), $debug-18);
    } else {
      log_debug('word_link->import_obj -> '.$result, $debug-18);
    }

    return $result;
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  // TODO check if $this->load_objects($debug-1); needs to be called from the calling function upfront
  function dsp_id () {
    $result = ''; 

    if ($this->from_name <> '' AND $this->verb_name <> '' AND $this->to_name <> '') {
      $result .= $this->from_name.' '; // e.g. Australia
      $result .= $this->verb_name.' '; // e.g. is a
      $result .= $this->to_name;       // e.g. Country 
    }
    $result .= ' ('.$this->from_id.','.$this->verb_id.','.$this->to_id;
    if ($this->id > 0) {
      $result .= ' -> '.$this->id.')';
    }  
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  // either the user edited description or the
  // Australia is a Country
  function name() {
    $result = '';

    if ($this->excluded <> 1) {
      // use the user defined description
      if ($this->description <> '') {
        $result = $this->description;
      } else {
        $result = $this->from_name.' '.$this->verb_name.' '.$this->to_name;
      }
    }
    
    return $result;
  }
      
  // same as name, but only for non debug usage
  // TODO check if name or name_usr should be used
  function name_usr() {
    $result = '';

    if ($this->excluded <> 1) {
      // use the user defined description
      if ($this->description <> '') {
        $result = $this->description;
      // or use special verb based generic description
      } elseif ($this->verb_id == cl(DBL_LINK_TYPE_IS)) {
        $result = $this->from_name.' ('.$this->to_name.')';
      // or use the standard generic description
      } else {
        $result = $this->from_name.' '.$this->verb_name.' '.$this->to_name;
      }
    }
    
    return $result;
  }
      
  // display one link to the user by returning the HTML code for the link to the calling function
  // to do: include the user sandbox in the selection
  private function dsp ($debug) {
    log_debug("word_link->dsp ".$this->id.".", $debug-14);
    
    $result = ''; // reset the html code var

    // get the link from the database
    $this->load_objects($debug-1);

    // prepare to show the word link
    $result .= $this->from_name.' '; // e.g. Australia
    $result .= $this->verb_name.' '; // e.g. is a
    $result .= $this->to_name;       // e.g. Country 

    return $result;
  }

  // similar to dsp, but display the reverse expression
  private function dsp_r ($debug) {
    log_debug("word_link->dsp_r ".$this->id.".", $debug-14);
    
    $result = ''; // reset the html code var

    // get the link from the database
    $this->load_objects($debug-1);

    // prepare to show the word link
    $result .= $this->to_name.' ';   // e.g. Countries
    $result .= $this->verb_name.' '; // e.g. are
    $result .= $this->from_name;     // e.g. Australia (and others) 

    return $result;
  }

  // display a bottom to edit the word link in a table cell
  function dsp_btn_edit ($wrd, $debug) {
    log_debug("word_link->dsp_btn_edit (".$this->id.",b".$wrd->id.")", $debug-10);
    $result = ''; // reset the html code var

    // get the link from the database
    $result .= '    <td>'."\n";
    $result .= btn_edit ("edit word link", "/http/link_edit.php?id=".$this->id."&back=".$wrd->id);
    $result .= '    </td>'."\n";

    return $result;
  }

  // display a form to create a triple
  function dsp_add ($back, $debug) {
    log_debug("word_link->dsp_add.", $debug-10);
    $result = ''; // reset the html code var
    
    // at least to create the dummy objects to display the selectors
    $this->load_objects($debug-1);

    // for creating a new triple the first word / triple is fixed
    $form_name = 'link_add';
    //$result .= 'Create a combined word (semantic triple):<br>';
    $result .= '<br>Define a new relation for <br><br>';
    $result .= '<b>'.$this->from_name.'</b> ';
    $result .= dsp_form_start($form_name);
    $result .= dsp_form_hidden ("back", $back);
    $result .= dsp_form_hidden ("confirm", '1');
    $result .= dsp_form_hidden ("from", $this->from_id);
    $result .= '<div class="form-row">';
    if (isset($this->verb)) { $result .= $this->verb->dsp_selector('both', $form_name,    "col-sm-6", $back, $debug-1); }
    if (isset($this->to))   { $result .= $this->to->dsp_selector  (0,      $form_name, 0, "col-sm-6", $back, $debug-1); }
    $result .= '</div>';
    $result .= '<br>';
    $result .= dsp_form_end('', $back);

    return $result;
  }

  // display a form to adjust the link between too words or triples
  function dsp_edit ($back, $debug) {
    log_debug("word_link->dsp_edit id ".$this->id." for user".$this->usr->id.".", $debug-10);
    $result = ''; // reset the html code var

    // at least to create the dummy objects to display the selectors
    $this->load($debug-1);
    $this->load_objects($debug-1);
    log_debug("word_link->dsp_edit id ".$this->id." load done.", $debug-10);

    // prepare to show the word link
    if ($this->id > 0) {
      $form_name = 'link_edit';
      $result .= dsp_text_h2('Change "'.$this->from_name.' '.$this->verb_name.' '.$this->to_name.'" to ');
      $result .= dsp_form_start($form_name);
      $result .= dsp_form_hidden ("back", $back);
      $result .= dsp_form_hidden ("confirm", '1');
      $result .= dsp_form_hidden ("id", $this->id);
      $result .= '<div class="form-row">';
      if (isset($this->from)) { $result .= $this->from->dsp_selector(0,         $form_name, 1, "col-sm-4", $back, $debug-1); }
      if (isset($this->verb)) { $result .= $this->verb->dsp_selector('forward', $form_name,    "col-sm-4", $back, $debug-1); }
      if (isset($this->to))   { $result .= $this->to->dsp_selector  (0,         $form_name, 2, "col-sm-4", $back, $debug-1); }
      $result .= '</div>';
      $result .= dsp_form_end('', $back);
      $result .= '<br>';
    }

    return $result;
  }

  // display a form to adjust the link between too words or triples
  function dsp_del ($back, $debug) {
    log_debug("word_link->dsp_del ".$this->id.".", $debug-10);
    $result = ''; // reset the html code var

    $result .= btn_yesno('Is "'.$this->dsp($debug-1).'" wrong?','/http/link_del.php?id='.$this->id.'&back='.$back);
    $result .= '<br><br>... and "'.$this->dsp_r($debug-1).'" is also wrong.<br><br>If you press Yes, both rules will be removed.';

    return $result;
  }
  
  // simply to display a single triple in a table
  function dsp_link () {
    $result = '<a href="/http/view.php?link='.$this->id.'" title="'.$this->description.'">'.$this->name.'</a>';
    return $result;
  }

  // simply to display a single triple in a table
  function dsp_tbl ($intent, $debug) {
    log_debug('word_link->dsp_tbl', $debug-10);
    $result  = '    <td>'."\n";
    while ($intent > 0) {
      $result .= '&nbsp;';
      $intent = $intent - 1;
    }
    $result .= '      '.$this->dsp_link().''."\n";
    $result .= '    </td>'."\n";
    return $result;
  }

  function dsp_tbl_row ($debug) {
    $result  = '  <tr>'."\n";
    $result .= $this->dsp_tbl(0, $debug-1);
    $result .= '  </tr>'."\n";
    return $result;
  }
  
  /*
  
  convert functions
  
  */
  
  // convert the word object into a phrase object
  function phrase ($debug) {
    $phr = New phrase;
    $phr->usr  = $this->usr;
    $phr->id   = $this->id;
    $phr->name = $this->name;
    $phr->obj  = $this;
    log_debug('word_link->phrase of '.$this->dsp_id(), $debug-12);
    return $phr;
  }

  /*
  
  save functions
  
  */
  
  // true if no one has used this triple
  private function not_used($debug) {
    log_debug('word_link->not_used ('.$this->id.')', $debug-10);

    // todo review: maybe replace by a database foreign key check
    return $this->not_changed($debug-1);
  }

  // true if no other user has modified the triple
  private function not_changed($debug) {
    log_debug('word_link->not_changed ('.$this->id.') by someone else than the owner ('.$this->owner_id.')', $debug-10);

    global $db_con;
    $result = true;
    
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_word_links 
               WHERE word_link_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND (excluded <> 1 OR excluded is NULL)";
    } else {
      $sql = "SELECT user_id 
                FROM user_word_links 
               WHERE word_link_id = ".$this->id."
                 AND (excluded <> 1 OR excluded is NULL)";
    }
    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    if ($db_row['user_id'] > 0) {
      $result = false;
    }
    log_debug('word_link->not_changed for '.$this->id.' is '.zu_dsp_bool($result), $debug-10);
    return $result;
  }

  // true if the user is the owner and no one else has changed the word_link
  // because if another user has changed the word_link and the original value is changed, maybe the user word_link also needs to be updated
  private function can_change($debug) {
    log_debug('word_link->can_change '.$this->dsp_id().' by user "'.$this->usr->name.'" (id '.$this->usr->id.', owner id '.$this->owner_id.')', $debug-12);
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  
    log_debug('word_link->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);
    return $can_change;
  }

  // true if a record for a user specific configuration already exists in the database
  private function has_usr_cfg() {
    $has_cfg = false;
    if ($this->usr_cfg_id > 0) {
      $has_cfg = true;
    }  
    return $has_cfg;
  }

  // create a database record to save user specific settings for this word_link
  private function add_usr_cfg($debug) {

    global $db_con;
    $result = '';

    if (!$this->has_usr_cfg()) {
      if (isset($this->from) AND isset($this->to)) {
        log_debug('word_link->add_usr_cfg for "'.$this->from->name.'"/"'.$this->to->name.'" by user "'.$this->usr->name.'"', $debug-10);
      } else {
        log_debug('word_link->add_usr_cfg for "'.$this->id.'" and user "'.$this->usr->name.'"', $debug-10);
      }

      // check again if there ist not yet a record
      $sql = "SELECT word_link_id 
                FROM user_word_links 
               WHERE word_link_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      if ($db_row['word_link_id'] <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_word_link';
        $log_id = $db_con->insert(array('word_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_word_link failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    log_debug('word_link->del_usr_cfg_if_not_needed pre check for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-12);

    global $db_con;
    $result = '';

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT word_link_id,
                     name,
                     description,
                     excluded
                FROM user_word_links
               WHERE word_link_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_cfg = $db_con->get1($sql, $debug-5);  
      log_debug('word_link->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' und user '.$this->usr->name.' with ('.$sql.')', $debug-12);
      if ($usr_cfg['word_link_id'] > 0) {
        if ($usr_cfg['name']         == Null
        AND $usr_cfg['description']  == Null
        AND $usr_cfg['excluded']     == Null) {
          // delete the entry in the user sandbox
          log_debug('word_link->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }
  
  // simply remove a user adjustment without check
  private function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_word_link';
    $result .= $db_con->delete(array('word_link_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user triple '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {

    global $db_con;
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      log_debug('word_link->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name, $debug-12);

      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      log_err("The triple database ID and the user must be set to remove a user specific modification.", "word_link->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  // e.g. that the user can see "added ABB is a Company"
  private function log_add($debug) {
    log_debug('word_link->log_add for '.$this->dsp_id().' by user "'.$this->usr->name.'"', $debug-10);
    $log = New user_log_link;
    $log->usr       = $this->usr;
    $log->action    = 'add';
    $log->table     = 'word_links';
    $log->new_from  = $this->from;
    $log->new_link  = $this->verb;
    $log->new_to    = $this->to;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating the triple itself
  private function log_upd($debug) {
    $log = New user_log_link;
    $log->usr       = $this->usr;
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'word_links';
    } else {  
      $log->table   = 'user_word_links';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a triple
  // e.g. that the user can see "ABB is a Company not any more"
  private function log_del($debug) {
    log_debug('word_link->log_del for '.$this->dsp_id().' by user "'.$this->usr->name.'"', $debug-10);
    $log = New user_log_link;
    $log->usr       = $this->usr;
    $log->action    = 'del';
    $log->table     = 'word_links';
    $log->old_from  = $this->from;
    $log->old_link  = $this->verb;
    $log->old_to    = $this->to;
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one display word link field
  private function log_upd_field($debug) {
    $log = New user_log;
    $log->usr       = $this->usr;
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'word_links';
    } else {  
      $log->table   = 'user_word_links';
    }
    
    return $log;    
  }
  
  // actually update a triple field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    $result = '';
    log_debug('word_link->save_field_do ', $debug-16);
    if ($log->new_id > 0) {
      $new_value = $log->new_id;
      $std_value = $log->std_id;
    } else {
      $new_value = $log->new_value;
      $std_value = $log->std_value;
    }  
    if ($log->add($debug-1)) {
      if ($this->can_change($debug-1)) {
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg()) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_word_link';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    log_debug('word_link->save_field_do done', $debug-16);
    return $result;
  }
  
  // set the update parameters for the phrase link name
  private function save_field_name($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    // the name field is a generic created field, so update it before saving
    $db_rec->name  = $db_rec->name($debug-1);
    $this->name    = $this->name();
    $std_rec->name = $std_rec->name($debug-1);
    
    if ($db_rec->name <> $this->name) {
      if ($this->name == '') {
        $this->name = Null;
      }
      $log = $this->log_upd_field($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $std_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'name';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the phrase link description
  private function save_field_description($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->description <> $this->description) {
      $log = $this->log_upd_field($debug-1);
      $log->old_value = $db_rec->description;
      $log->new_value = $this->description;
      $log->std_value = $std_rec->description;
      $log->row_id    = $this->id; 
      $log->field     = 'description';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the phrase link excluded
  private function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->excluded <> $this->excluded) {
      if ($this->excluded == 1) {
        $log = $this->log_del($debug-1);
      } else {
        $log = $this->log_add($debug-1);
      }
      $log->field = 'excluded';
      $new_value  = $this->excluded;
      $std_value  = $std_rec->excluded;
      // also part of $this->save_field_do
      if ($this->can_change($debug-1)) {
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg()) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_word_link';
        if ($new_value == $std_value) {
          $result .= $db_con->update($this->id, $log->field, Null, $debug-1);
        } else {  
          $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
        }
        $result .= $this->del_usr_cfg_if_not_needed($debug-1);
      }
    }
    return $result;
  }
    
  // save all updated word_link fields excluding id fields (from, verb and to), because already done when adding a word_link
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_name        ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_description ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded    ($db_con, $db_rec, $std_rec, $debug-1);
    //$result .= $this->save_field_type     ($db_con, $db_rec, $std_rec, $debug-1);
    log_debug('word_link->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
  
  // save updated the word_link id fields (from, verb and to)
  // should only be called if the user is the owner and nobody has used the triple
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->from_id <> $this->from_id
     OR $db_rec->verb_id <> $this->verb_id
     OR $db_rec->to_id   <> $this->to_id) {
      log_debug('word_link->save_id_fields to "'.$this->to_name.'" ('.$this->to_id.') from "'.$db_rec->to_name.'" ('.$db_rec->to_id.') standard '.$std_rec->to_name.'" ('.$std_rec->to_id.')', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_from = $db_rec->from;
      $log->new_from = $this->from;
      $log->std_from = $std_rec->from;
      $log->old_link = $db_rec->verb;
      $log->new_link = $this->verb;
      $log->std_link = $std_rec->verb;
      $log->old_to = $db_rec->to;
      $log->new_to = $this->to;
      $log->std_to = $std_rec->to;
      $log->row_id   = $this->id; 
      //$log->field    = 'from_phrase_id';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("from_phrase_id",      "verb_id","to_phrase_id"),
                                              array($this->from->id,$this->verb->id, $this->to->id), $debug-1);
      }
    }
    log_debug('word_link->save_id_fields for '.$this->dsp_id().' has been done', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->from_id <> $this->from_id 
     OR $db_rec->verb_id <> $this->verb_id 
     OR $db_rec->to_id   <> $this->to_id) {
      $this->reset_objects();
      // check if target link already exists
      log_debug('word_link->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        // ... if yes request to delete or exclude the record with the id parameters before the change
        $to_del = clone $db_rec;
        $result .= $to_del->del($debug-20);        
        // .. and use it for the update
        $this->id = $db_chk->id;
        $this->owner_id = $db_chk->owner_id;
        // force the include again
        $this->excluded = Null;
        $db_rec->excluded = '1';
        $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-20);
        log_debug('word_link->save_id_if_updated found a triple with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id(), $debug-14);
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          log_debug('word_link->save_id_if_updated change the existing triple '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")', $debug-14);
          $this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-20);
        } else {
          // if the target link has not yet been created
          // ... request to delete the old
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and create a deletion request for all users ???
          
          // ... and create a new triple
          $this->id = 0;
          $this->owner_id = $this->usr->id;
          $result .= $this->add($db_con, $debug-20);
          log_debug('word_link->save_id_if_updated recreate the triple del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")', $debug-14);
        }
      }
    }  

    log_debug('word_link->save_id_if_updated for '.$this->dsp_id().' has been done', $debug-12);
    return $result;
  }
  
  // add a new triple to the database
  private function add($db_con, $debug) {
    log_debug('word_link->add new word_link for "'.$this->from->name.'" '.$this->verb->name.' "'.$this->to->name.'"', $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new word_link
      $this->id = $db_con->insert(array("from_phrase_id",      "verb_id","to_phrase_id",     "user_id"), 
                                  array($this->from->id,$this->verb->id, $this->to->id,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = New word_link;
        $db_rec->from = $this->from;
        $db_rec->verb = $this->verb;
        $db_rec->to   = $this->to;
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the word_link fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        log_err("Adding word_link ".$this->name." failed", "word_link->add");
      }
    }  
    
    return $result;
  }
  
  // update a triple in the database or create a user triple
  function save($debug) {
    log_debug('word_link->save "'.$this->description.'" for user '.$this->usr->id, $debug-10);

    global $db_con;
    $result = '';

    // build the database object because the is anyway needed
    //$db_con = new mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'word_link';         
    
    // load the objects if needed
    $this->load_objects($debug-1);
    
    // check if the opposite triple already exists and if yes, ask for confirmation
    if ($this->id <= 0) {
      log_debug('word_link->save check if a new word_link for "'.$this->from->name.'" and "'.$this->to->name.'" needs to be created', $debug-12);
      // check if the same triple is already in the database
      $db_chk_rev = clone $this;
      $db_chk_rev->from    = $this->to;
      $db_chk_rev->from_id = $this->to_id;
      $db_chk_rev->to      = $this->from;
      $db_chk_rev->to_id   = $this->from_id;
      $db_chk_rev->load_standard($debug-1);
      if ($db_chk_rev->id > 0) {
        $this->id = $db_chk_rev->id;
        $result .= dsp_err('The reverse of "'.$this->from->name.' '.$this->verb->name.' '.$this->to->name.'" already exists. Do you really want to create both sides?');
      }
    }  
      
    // check if the triple already exists and if yes, update it if needed
    if ($this->id <= 0 AND $result == '') {
      log_debug('word_link->save check if a new word_link for "'.$this->from->name.'" and "'.$this->to->name.'" needs to be created', $debug-12);
      // check if the same triple is already in the database
      $db_chk = clone $this;
      $db_chk->load_standard($debug-1);
      if ($db_chk->id > 0) {
        $this->id = $db_chk->id;
      }
    }  
      
    // try to save the link only if no question has been raised until now
    if ($result == '') {
      // check if a new value is supposed to be added
      if ($this->id <= 0) {
        $result .= $this->add($db_con, $debug-1);
      } else {  
        log_debug('word_link->save update "'.$this->id.'"', $debug-12);
        // read the database values to be able to check if something has been changed; 
        // done first, because it needs to be done for user and general phrases
        $db_rec = New word_link;
        $db_rec->id  = $this->id;
        $db_rec->usr = $this->usr;
        $db_rec->load($debug-1);
        log_debug('word_link->save -> database triple "'.$db_rec->name.'" ('.$db_rec->id.') loaded', $debug-14);
        $std_rec = New word_link;
        $std_rec->id = $this->id;
        $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
        $std_rec->load_standard($debug-1);
        log_debug('word_link->save -> standard triple settings for "'.$std_rec->name.'" ('.$std_rec->id.') loaded', $debug-14);
        
        // for a correct user word link detection (function can_change) set the owner even if the word link has not been loaded before the save 
        if ($this->owner_id <= 0) {
          $this->owner_id = $std_rec->owner_id;
        }
        
        // check if the id parameters are supposed to be changed 
        $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);

        // if a problem has appeared up to here, don't try to save the values
        // the problem is shown to the user by the calling interactive script
        if (str_replace ('1','',$result) == '') {
          // update the order or link type
          $result .= $this->save_fields ($db_con, $db_rec, $std_rec, $debug-1);        
        }
      }  
    }  
    
    return $result;    
  }

  // delete the complete triple (the calling function del must have checked that no one uses this triple)
  private function del_exe($debug) {
    log_debug('word_link->del_exe', $debug-16);

    global $db_con;
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      // delete first the user configurations that have also been excluded to prevent problems with the foreign keys
      $db_con->type = 'user_word_link';
      $result .= $db_con->delete(array('word_link_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = 'word_link';         
      $result .= $db_con->delete('word_link_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a triple
  function del($debug) {
    log_debug('word_link->del', $debug-16);
    $result = '';
    $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      log_debug('word_link->del '.$this->dsp_id(), $debug-14);
      if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
        $result .= $this->del_exe($debug-1);
      } else {
        $this->excluded = 1;
        $result .= $this->save($debug-1);        
      }
    }
    return $result;    
  }
  

}

?>
