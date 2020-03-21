<?php

/*

  phrase_list.php - a list of phrase (word or triple) objects
  ---------------
  
  Compared to phrase_groups a phrase list is a memory only object that cannot be saved to the database
  
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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class phrase_list {

  public $lst          = array(); // array of the loaded phrase objects 
                                  // (key is at the moment the database id, but it looks like this has no advantages, 
                                  // so a normal 0 to n order could have more advantages)
  public $ids          = array(); // array of ids corresponding to the lst->id to load a list of phrases from the database
  public $usr          = NULL;    // the user object of the person for whom the phrase list is loaded, so to say the viewer
  
  
  /*
  
  load function
    
  */

  // load the phrases based on the id list
  // TODO slow but working version of the load function
  function load($debug) {
    $result = $this->lst; // to add to the existing list
    foreach ($this->ids AS $phr_id) {
      if ($phr_id <> 0) {
        $phr = New phrase;
        $phr->id  = $phr_id;
        $phr->usr = $this->usr;
        $phr->load($debug-1);
        $result[] = $phr;
        zu_debug('phrase_list->load -> add '.$phr->name.' for user '.$phr->usr->name, $debug-16);
      }
    }
    // refresh the ids and list
    $this->ids($debug-1);
    $this->lst = $result;
    return $result; 
  }
  
  // build a word list including the triple words or in other words flatten the list e.g. for parent inclusions
  function wrd_lst_all ($debug) {
    zu_debug('phrase_list->wrd_lst_all for '.$this->dsp_id($debug-1), $debug-10);

    // check the basic settings
    if (!isset($this->lst)) {
      zu_info('Phrase list '.$this->dsp_id().' is empty','phrase_list->wrd_lst_all', '', (new Exception)->getTraceAsString(), $this->usr); 
    } else {
      if (!isset($this->usr)) {
        zu_err('User for phrase list '.$this->dsp_id().' missing','phrase_list->wrd_lst_all', '', (new Exception)->getTraceAsString(), $this->usr); 
      }
      
      // create and fill the word list
      $wrd_lst = New word_list;
      $wrd_lst->usr = $this->usr;    
      foreach ($this->lst AS $phr) {
        if (!isset($phr->obj)) {
          $phr->load ($debug-1);
          zu_warning('Phrase '.$phr->dsp_id().' needs unexpected reload','phrase_list->wrd_lst_all', '', (new Exception)->getTraceAsString(), $this->usr); 
        }
        if (!isset($phr->obj)) {
          zu_err('Phrase '.$phr->dsp_id().' could not be loaded','phrase_list->wrd_lst_all', '', (new Exception)->getTraceAsString(), $this->usr); 
        } else {
          // TODO check if old can ge removed: if ($phr->id > 0) {
          if (get_class($phr->obj) == 'word' or get_class($phr->obj) == 'word_dsp') {
            $wrd_lst->add($phr->obj, $debug-1);
          } elseif (get_class($phr->obj) == 'word_link') {
            // use the recursive triple function to include the foaf words
            $sub_wrd_lst = $phr->obj->wrd_lst($debug-1);
            foreach ($sub_wrd_lst->lst AS $wrd) {
              $wrd_lst->add($wrd, $debug-1);
            }  
          } else {
            zu_err('The phrase list '.$this->dsp_id().' contains '.$phr->obj->dsp_id().', which is neither a word nor a phrase, but it is a '.get_class($phr->obj),'phrase_list->wrd_lst_all', '', (new Exception)->getTraceAsString(), $this->usr); 
          }
        }
      }
    }

    zu_debug('phrase_list->wrd_lst_all -> '.$wrd_lst->dsp_id($debug-1), $debug-12);
    return $wrd_lst;
  }
  
  // get a word list from the phrase list
  function wrd_lst ($debug) {
    zu_debug('phrase_list->wrd_lst for '.$this->dsp_id($debug-1), $debug-10);
    $wrd_lst = New word_list;
    $wrd_lst->usr = $this->usr;    
    if (isset($this->lst)) {
      foreach ($this->lst AS $phr) {
        if ($phr->id > 0) {
          if (isset($phr->obj)) {
            $wrd_lst->add($phr->obj, $debug-1);
          }
        }
      }
    }
    zu_debug('phrase_list->wrd_lst -> '.$wrd_lst->dsp_id($debug-1), $debug-12);
    return $wrd_lst;
  }
  
  // get a triple list from the phrase list
  function lnk_lst ($debug) {
    zu_debug('phrase_list->lnk_lst for '.$this->dsp_id($debug-1), $debug-10);
    $lnk_lst = New word_link_list;
    $lnk_lst->usr = $this->usr;    
    if (isset($this->lst)) {
      foreach ($this->lst AS $phr) {
        if ($phr->id < 0) {
          if (isset($phr->obj)) {
            $lnk_lst->add($phr->obj, $debug-1);
          }
        }
      }
    }
    zu_debug('phrase_list->lnk_lst -> '.$lnk_lst->dsp_id($debug-1), $debug-12);
    return $lnk_lst;
  }
  
  // collect all triples from the phrase list
  function wrd_lnk_lst ($debug) {
    //zu_debug('phrase_list->wrd_lnk_lst for '.$this->dsp_id($debug-1), $debug-10);

    $lnk_lst = New word_link_list;
    $lnk_lst->wrd_lst   = $this;
    $lnk_lst->usr       = $this->usr;
    $lnk_lst->direction = 'up';
    $lnk_lst->load($debug-1);

    //zu_debug('phrase_list->wrd_lnk_lst -> '.$lnk_lst->dsp_id($debug-1), $debug-12);
    return $lnk_lst;
  }

  /*
    tree building function
    ----------------------
    
    Overview for words, triples and phrases and it's lists
    
               childs and            parents return the direct parents and childs   without the original phrase(s)
          foaf_childs and       foaf_parents return the    all parents and childs   without the original phrase(s)
                  are and                 is return the    all parents and childs including the original phrase(s) for the specific verb "is a"
             contains                        return the    all             childs including the original phrase(s) for the specific verb "contains" 
                                  is part of return the    all parents              without the original phrase(s) for the specific verb "contains" 
                 next and              prior return the direct parents and childs   without the original phrase(s) for the specific verb "follows"
          followed_by and        follower_of return the    all parents and childs   without the original phrase(s) for the specific verb "follows"
    differentiated_by and differentiator_for return the    all parents and childs   without the original phrase(s) for the specific verb "can_contain"
        
    Samples
    
    the      parents of  "ABB" can be "public limited company"
    the foaf_parents of  "ABB" can be "public limited company" and "company"
                "is" of  "ABB" can be "public limited company" and "company" and "ABB" (used to get all related values)
    the       childs for "company" can include "public limited company"
    the  foaf_childs for "company" can include "public limited company" and "ABB"
               "are" for "company" can include "public limited company" and "ABB" and "company" (used to get all related values)

          "contains" for "balance sheet" is "assets" and "liabilities" and "company" and "balance sheet" (used to get all related values)
        "is part of" for "assets" is "balance sheet" but not "assets" 

              "next" for "2016" is "2017" 
             "prior" for "2017" is "2016" 
    "is followed by" for "2016" is "2017" and "2018"
    "is follower of" for "2016" is "2015" and "2014"

    "wind energy" and "energy" "can be differentiator for" "sector"
                      "sector" "can be differentiated_by"  "wind energy" and "energy"
    
    if "wind energy" "is part of" "energy"
    
  */

  // returns a list of phrases, that characterises the given phrase e.g. for the "ABB Ltd." it will return "Company" if the verb_id is "is a"
  // ex foaf_parent
  function foaf_parents ($verb_id, $debug) {
    zu_debug('phrase_list->foaf_parents (type id '.$verb_id.')', $debug-10);
    $wrd_lst = $this->wrd_lst_all($debug-1);
    $added_wrd_lst = $wrd_lst->foaf_parents ($verb_id, $debug-1);
    $added_phr_lst = $added_wrd_lst->phrase_lst($debug-1);

    zu_debug('phrase_list->foaf_parents -> ('.$added_phr_lst->name().')', $debug-7);
    return $added_phr_lst;
  }

  // similar to foaf_parents, but for only one level
  // $level is the number of levels that should be looked into
  // ex foaf_parent_step
  function parents ($verb_id, $level, $debug) {
    zu_debug('phrase_list->parents('.$verb_id.')', $debug-10);
    $wrd_lst = $this->wrd_lst_all($debug-1);
    $added_wrd_lst = $wrd_lst->parents ($verb_id, $level, $debug-1);
    $added_phr_lst = $added_wrd_lst->phrase_lst($debug-1);

    zu_debug('phrase_list->parents -> ('.$added_phr_lst->name().')', $debug-7);
    return $added_phr_lst;
  }

  // similar to foaf_parent, but the other way round e.g. for "Companies" it will return "ABB Ltd." and others if the link type is "are"
  // ex foaf_child
  function foaf_childs ($verb_id, $debug) {
    zu_debug('phrase_list->foaf_childs type '.$verb_id.'', $debug-10);
    $added_phr_lst = Null;

    if ($verb_id > 0) {
      $wrd_lst = $this->wrd_lst_all($debug-1);
      $added_wrd_lst = $wrd_lst->foaf_childs ($verb_id, $debug-1);
      $added_phr_lst = $added_wrd_lst->phrase_lst($debug-1);

      zu_debug('phrase_list->foaf_childs -> ('.$added_phr_lst->name().')', $debug-7);
    }
    return $added_phr_lst;
  }

  // similar to foaf_child, but for only one level
  // $level is the number of levels that should be looked into
  // ex foaf_child_step
  function childs ($verb_id, $level, $debug) {
    zu_debug('phrase_list->childs type '.$verb_id.'', $debug-10);
    $wrd_lst = $this->wrd_lst_all($debug-1);
    $added_wrd_lst = $wrd_lst->childs ($verb_id, $level, $debug-1);
    $added_phr_lst = $added_wrd_lst->phrase_lst($debug-1);

    zu_debug('phrase_list->childs -> ('.$added_phr_lst->name().')', $debug-7);
    return $added_phr_lst;
  }
 
  // returns a list of phrases that are related to this phrase list e.g. for "ABB" and "Daimler" it will return "Company" (but not "ABB"???)
  function is ($debug) {
    $phr_lst = $this->foaf_parents(cl(SQL_LINK_TYPE_IS), $debug-1);
    zu_debug('phrase_list->is -> ('.$this->name().' is '.$phr_lst->name().')', $debug-8);
    return $phr_lst;
  }

  // returns a list of phrases that are related to this phrase list e.g. for "Company" it will return "ABB" and "Daimler" and "Company" 
  // e.g. to get all related values
  function are ($debug) {
    zu_debug('phrase_list->are -> '.$this->dsp_id(), $debug-16);
    $phr_lst = $this->foaf_childs(cl(SQL_LINK_TYPE_IS), $debug-1);
    zu_debug('phrase_list->are -> '.$this->dsp_id().' are '.$phr_lst->dsp_id(), $debug-12);
    $phr_lst->merge($this, $debug-1);
    zu_debug('phrase_list->are -> '.$this->dsp_id().' merged into '.$phr_lst->dsp_id(), $debug-8);
    return $phr_lst;
  }

  // returns a list of phrases that are related to this phrase list 
  function contains ($debug) {
    $phr_lst = $this->foaf_childs(cl(SQL_LINK_TYPE_CONTAIN), $debug-1);
    $phr_lst->merge($this, $debug-1);
    zu_debug('phrase_list->contains -> ('.$this->name().' contains '.$phr_lst->name().')', $debug-8);
    return $phr_lst;
  }

  // makes sure that all combinations of "are" and "conatins" are included
  function are_and_contains ($debug) {
    zu_debug('phrase_list->are_and_contains for '.$this->name(), $debug-18);

    // this first time get all related items
    $phr_lst = clone $this;
    $phr_lst   = $phr_lst->are     ($debug-1);
    $phr_lst   = $phr_lst->contains($debug-1);
    $added_lst = $phr_lst->diff($this, $debug-1);
    // ... and after that get only for the new
    if (count($added_lst->lst) > 0) {
      $loops = 0;
      zu_debug('phrase_list->are_and_contains -> added '.$added_lst->name().' to '.$phr_lst->name(), $debug-18);
      do {
        $next_lst  = clone $added_lst;
        $next_lst  = $next_lst->are     ($debug-1);
        $next_lst  = $next_lst->contains($debug-1);
        $added_lst = $next_lst->diff($phr_lst, $debug-1);
        if (count($added_lst->lst) > 0) { zu_debug('phrase_list->are_and_contains -> add '.$added_lst->name().' to '.$phr_lst->name(), $debug-18); }  
        $phr_lst->merge($added_lst, $debug-1);
        $loops++;
      } while (count($added_lst->lst) > 0 AND $loops < MAX_LOOP);
    }
    zu_debug('phrase_list->are_and_contains -> '.$this->name().' are_and_contains '.$phr_lst->name(), $debug-8);
    return $phr_lst;
  }
  
  // add all potential differentiator phrases of the phrase lst e.g. get "energy" for "sector"
  function differentiators ($debug) {
    zu_debug('phrase_list->differentiators for '.$this->name(), $debug-18);
    $phr_lst = $this->foaf_childs(cl(SQL_LINK_TYPE_DIFFERANTIATOR), $debug-1);
    zu_debug('phrase_list->differentiators merge '.$this->name(), $debug-18);
    $this->merge($phr_lst, $debug-1);
    zu_debug('phrase_list->differentiators -> '.$phr_lst->name().' for '.$this->name(), $debug-8);
    return $phr_lst;
  }

  // same as differentiators, but including the sub types e.g. get "energy" and "wind energy" for "sector" if "wind energy" is part of "energy"
  function differantiators_all($debug) {
    zu_debug('phrase_list->differantiators_all for '.$this->name(), $debug-18);
    // this first time get all related items
    $phr_lst = clone $this;
    $phr_lst = $this->foaf_childs(cl(SQL_LINK_TYPE_DIFFERANTIATOR), $debug-1);
    $phr_lst = $phr_lst->are     ($debug-1);
    $phr_lst = $phr_lst->contains($debug-1);
    $added_lst = $phr_lst->diff($this, $debug-1);
    // ... and after that get only for the new
    if (count($added_lst->lst) > 0) {
      $loops = 0;
      zu_debug('phrase_list->differentiators -> added '.$added_lst->name().' to '.$phr_lst->name(), $debug-18);
      do {
        $next_lst  = $added_lst->foaf_childs(cl(SQL_LINK_TYPE_DIFFERANTIATOR), $debug-1);
        $next_lst  = $next_lst->are     ($debug-1);
        $next_lst  = $next_lst->contains($debug-1);
        $added_lst = $next_lst->diff($phr_lst, $debug-1);
        if (count($added_lst->lst) > 0) { zu_debug('phrase_list->differentiators -> add '.$added_lst->name().' to '.$phr_lst->name(), $debug-18); }  
        $phr_lst->merge($added_lst, $debug-1);
        $loops++;
      } while (count($added_lst->lst) > 0 AND $loops < MAX_LOOP);
    }
    zu_debug('phrase_list->differentiators -> '.$phr_lst->name().' for '.$this->name(), $debug-8);
    return $phr_lst;
  }

  // similar to differentiators, but only a filtered list of differentiators is viewed to increase speed
  function differentiators_filtered ($filter_lst, $debug) {
    zu_debug('phrase_list->differentiators_filtered for '.$this->name(), $debug-18);
    $result = $this->differantiators_all($debug-1);
    $result = $result->filter($filter_lst, $debug-1);
    zu_debug('phrase_list->differentiators_filtered -> '.$result->name(), $debug-1);
    return $result;
  }

  /*
    extract functions
    -----------------
  */

  // return a unique id of the phrase list
  function id($debug) {
    $id_lst = $this->ids();
    asort($id_lst);
    $result = implode(",",$id_lst);
    return $result; 
  }
  
  // return a list of the phrase ids
  function ids($debug) {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $phr) {
        // use only valid ids
        if ($phr->id <> 0) {
          $result[] = $phr->id;
        }  
      }
    }
    $this->ids = $result;
    return $result; 
  }
  
  // return an url with the phrase ids
  // the order of the ids is used to sort the phrases for the user
  function id_url($debug) {
    $result = '';
    if (isset($this->lst)) {
      if (count($this->lst) > 0) {
        $result = '&phrases='.implode(",",$this->ids());
      }
    }
    return $result; 
  }
  
  // the old long form to encode 
  function id_url_long($debug) {
    $result = zu_ids_to_url($this->ids(),"phrase", $debug-1);
    return $result; 
  }
  
  /*
    display functions
    -----------------
  */

  // return best possible id for this element mainly used for debugging
  function dsp_id ($debug) {
    $id = $this->ids_txt();
    $name = $this->name();
    if ($name <> '""') {
      $result = ''.$name.' ('.$id.')';
    } else {
      $result = ''.$id.'';
    }
    /* the user is no most cases no extra info
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    */

    return $result;
  }

  // return a list of the phrase ids as an sql compatible text
  function ids_txt($debug) {
    $result = implode(',',$this->ids($debug-1));
    return $result; 
  }
  
  // return one string with all names of the list
  function name($debug) {
    if ($debug > 10) {
      $result = '"'.implode('","',$this->names($debug-1)).'"';
    } else {
      $result = '"'.implode('","',array_slice($this->names($debug-1), 0, 7));
      if (count($this->names($debug-1)) > 8) {
        $result .= ' ... total '.count($this->lst);
      }
      $result .= '"';
    }
    return $result; 
  }
  
  // return one string with all names of the list without hiquotes for the user, but not nessesary as a unique text
  // e.g. >Company Zurich< can be either >"Company Zurich"< or >"Company" "Zurich"<, means either a triple or two words
  //      but this "short" form probably confuses the user less and 
  //      if the user cannot change the tags anyway the saving of a related value is possible
  function name_dsp($debug) {
    $result = implode(' ',$this->names($debug-1));
    return $result; 
  }
  
  // return one string with all names of the list with the link
  function name_linked($debug) {
    $result = implode(',',$this->names_linked($debug-1));
    return $result; 
  }
  
  // return a list of the phrase names
  function names($debug) {
    $result = array();
    if (isset($this->lst)) {
      foreach ($this->lst AS $phr) {
        $result[] = $phr->name;
        if (!isset($phr->usr)) {
          zu_err('The user of a phrase list element differs from the list user.', 'phrase_list->names','The user of "'.$phr->name.'" is missing, but the list user is "'.$this->usr->name.'".' , (new Exception)->getTraceAsString(), $this->usr);
        } elseif ($phr->usr <> $this->usr) {
          zu_err('The user of a phrase list element differs from the list user.', 'phrase_list->names','The user "'.$phr->usr->name.'" of "'.$phr->name.'" does not match the list user "'.$this->usr->name.'".' , (new Exception)->getTraceAsString(), $this->usr);
        }
      }
    }
    zu_debug('phrase_list->names ('.implode(",",$result).')', $debug-19);
    return $result; 
  }
  
  // return a list of the phrase names with html links
  function names_linked($debug) {
    zu_debug('phrase_list->names_linked ('.count($this->lst).')', $debug-20);
    $result = array();
    foreach ($this->lst AS $phr) {
      $result[] = $phr->display ($debug-1);
    }
    zu_debug('phrase_list->names_linked ('.implode(",",$result).')', $debug-19);
    return $result; 
  }
  
  // offer the user to add a new value for this phrases
  // similar to value.php/btn_add
  function btn_add_value ($back, $debug) {
    $result = btn_add_value($this, Null, $back, $debug-1);
    /*
    zu_debug('phrase_list->btn_add_value.', $debug-19);
    $val_btn_title = '';
    $url_phr = '';
    if (!empty($this->lst)) {
      $val_btn_title = "add new value similar to ".htmlentities($this->name($debug-1));
    } else {
      $val_btn_title = "add new value";
    }  
    $url_phr = $this->id_url_long();
    
    $val_btn_call  = '/http/value_add.php?back='.$back.$url_phr;
    $result .= btn_add ($val_btn_title, $val_btn_call); 
    zu_debug('phrase_list->btn_add_value -> done.', $debug-19);
    */
    return $result;    
  }
  
  // true if the phrase is part of the phrase list
  function does_contain($phr_to_check, $debug) {
    $result = false; 
    
    foreach ($this->lst AS $phr) {
      if ($phr->id == $phr_to_check->id) {
        $result = true; 
      }
    }

    return $result; 
  }
  
  // add one phrase to the phrase list, but only if it is not yet part of the phrase list
  function add($phr_to_add, $debug) {
    // check parameters
    if (isset($phr_to_add)) {
      // autocorrect word to phrase
      if (get_class($phr_to_add) == 'word' OR get_class($phr_to_add) == 'word_dsp') {
        $phr_to_add = $phr_to_add->phrase($debug-1);
      }
      if (get_class($phr_to_add) <> 'phrase') {
        zu_err("Object to add must be of type phrase, but it is ".get_class($phr_to_add).".", "phrase_list->add", '', (new Exception)->getTraceAsString(), $this->usr);
      } else {
        if ($phr_to_add->id <> 0 AND isset($this->ids)) {
          zu_debug('phrase_list->add '.$phr_to_add->dsp_id(), $debug-14);
          if (!in_array($phr_to_add->id, $this->ids)) {
            if ($phr_to_add->id <> 0) {
              $this->lst[] = $phr_to_add;
              $this->ids[] = $phr_to_add->id;
            }
          }
        } else {
          $this->lst[] = $phr_to_add;
          $this->ids[] = $phr_to_add->id;
        }
      }
    }
    zu_debug('phrase_list->add -> done.', $debug-16);
  }
  
  // add one phrase by the id to the phrase list, but only if it is not yet part of the phrase list
  function add_id($phr_id_to_add, $debug) {
    zu_debug('phrase_list->add_id ('.$phr_id_to_add.')', $debug-10);
    if (!in_array($phr_id_to_add, $this->ids)) {
      if ($phr_id_to_add <> 0) {
        $phr_to_add = New phrase;
        $phr_to_add->id  = $phr_id_to_add;
        $phr_to_add->usr = $this->usr;
        $phr_to_add->load($debug-1);
        
        $this->add($phr_to_add, $debug-1);
      }
    }
  }
  
  // add one phrase to the phrase list defined by the phrase name
  function add_name($phr_name_to_add, $debug) {
    zu_debug('phrase_list->add_name "'.$phr_name_to_add.'".', $debug-10);
    if (is_null($this->usr->id)) {
      zu_err("The user must be set.", "phrase_list->add_name", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $phr_to_add = New phrase;
      $phr_to_add->name = $phr_name_to_add;
      $phr_to_add->usr  = $this->usr;
      $phr_to_add->load($debug-1);
      
      if ($phr_to_add->id <> 0) {
        $this->add($phr_to_add, $debug-1);
      } else {
        zu_err('"'.$phr_name_to_add.'" not found.', "phrase_list->add_name", '', (new Exception)->getTraceAsString(), $this->usr);
      }
    }
    zu_debug('phrase_list->add_name -> added "'.$phr_name_to_add.'" to '.$this->dsp_id().').', $debug-10);
  }
  
  // del one phrase to the phrase list, but only if it is not yet part of the phrase list
  function del($phr_to_del, $debug) {
    zu_debug('phrase_list->del '.$phr_to_del->name.' ('.$phr_to_del->id.')', $debug-10);
    if (in_array($phr_to_del->id, $this->ids)) {
      if (isset($this->ids)) {
        $del_pos = array_search($phr_to_del->id, $this->ids());
        unset ($this->ids[$del_pos]);
        if (isset($this->lst)) {
          unset ($this->lst[$del_pos]);
        }
      }
    }
  }
  
  // merge as a function, because the array_merge does not create a object
  function merge($new_phr_lst, $debug) {
    zu_debug('phrase_list->merge '.$new_phr_lst->dsp_id().' to '.$this->dsp_id(), $debug-8);        
    if (isset($new_phr_lst->lst)) {
      zu_debug('phrase_list->merge -> do.', $debug-8);        
      foreach ($new_phr_lst->lst AS $new_phr) {
        zu_debug('phrase_list->merge -> add.', $debug-8);        
        zu_debug('phrase_list->merge add '.$new_phr->dsp_id(), $debug-12);
        $this->add($new_phr, $debug-1);
        zu_debug('phrase_list->merge -> added.', $debug-8);        
      }
    }
    zu_debug('phrase_list->merge -> to '.$this->dsp_id(), $debug-8); 
    return $this;
  }
  
  // filters a phrase list e.g. out of "2014", "2015", "2016", "2017" with the filter "2016", "2017","2018" the result is "2016", "2017"
  function filter($filter_lst, $debug) {
    $result = clone $this;
    
    // check an adjust the parameters
    if (get_class($filter_lst) == 'word_list') { 
      $filter_phr_lst = $filter_lst->phrase_lst($debug-1);
    } else {
      $filter_phr_lst = $filter_lst;
    }
    if (!isset($filter_phr_lst)) { 
      zu_err('Phrases to delete are missing.','phrase_list->diff', '', (new Exception)->getTraceAsString(), $this->usr); 
    }
    if (get_class($filter_phr_lst) <> 'phrase_list') { 
      zu_err(get_class($filter_phr_lst).' cannot be used to delete phrases.','phrase_list->diff', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    if (isset($result->lst)) {
      if (!empty($result->lst)) {
        $phr_lst = array();
        $lst_ids = $filter_phr_lst->ids();
        foreach ($result->lst AS $phr) {
          if (in_array($phr->id, $lst_ids)) {
            $phr_lst[] = $phr;
          }
        }  
        $result->lst = $phr_lst;
        $result->ids = $result->ids($debug-1);
      }
      zu_debug('phrase_list->filter -> '.$result->dsp_id(), $debug-10);
    }
    return $result;
  }
  
  // diff as a function, because the array_diff does not work for an object list
  // e.g. for "2014", "2015", "2016", "2017" and the delete list of "2016", "2017","2018" the result is "2014", "2015"
  function diff($del_lst, $debug) {
    zu_debug('phrase_list->diff of '.$del_lst->dsp_id().' and '.$this->dsp_id(), $debug-10);

    // check an adjust the parameters
    if (get_class($del_lst) == 'word_list') { 
      $del_phr_lst = $del_lst->phrase_lst($debug-1);
    } else {
      $del_phr_lst = $del_lst;
    }
    if (!isset($del_phr_lst)) { 
      zu_err('Phrases to delete are missing.','phrase_list->diff', '', (new Exception)->getTraceAsString(), $this->usr); 
    }
    if (get_class($del_phr_lst) <> 'phrase_list') { 
      zu_err(get_class($del_phr_lst).' cannot be used to delete phrases.','phrase_list->diff', '', (new Exception)->getTraceAsString(), $this->usr);
    }

    if (isset($this->lst)) {
      if (!empty($this->lst)) {
        $result = array();
        $lst_ids = $del_phr_lst->ids();
        foreach ($this->lst AS $phr) {
          if (!in_array($phr->id, $lst_ids)) {
            $result[] = $phr;
          }
        }  
        $this->lst = $result;
        $this->ids = $this->ids();
      }
    }  
    
    zu_debug('phrase_list->diff -> '.$this->dsp_id(), $debug-12);
  }
  
  // same as diff but sometimes this name looks better 
  function not_in($del_phr_lst, $debug) {
    zu_debug('phrase_list->not_in get out of '.$this->name().' not in '.$del_phr_lst->name().')', $debug-14);
    $this->diff($del_phr_lst, $debug-4);
  }
  /*
  // keep only those phrases in the list that are not in the list to delete
  // e.g. for "2014", "2015", "2016", "2017" and the exclude list of "2016", "2017","2018" the result is "2014", "2015"
  function not_in($del_phr_lst, $debug) {
    zu_debug('phrase_list->not_in.', $debug-14);
    foreach ($this->lst AS $phr) {
      if ($phr->id <> 0) {
        if (in_array($phr->id, $del_phr_lst->ids)) {
          $del_pos = array_search($phr->id, $this->ids);
          zu_debug('phrase_list->not_in -> to exclude ('.$this->lst[$del_pos]->name.')', $debug-14);
          unset ($this->lst[$del_pos]);
          unset ($this->ids[$del_pos]);
        }
      }
    }
    zu_debug('phrase_list->not_in -> '.$this->dsp_id(), $debug-10);
  }
  */
  
  // similar to diff, but using an id array to exclude instaed of a phrase list object
  function diff_by_ids($del_phr_ids, $debug) {
    $this->ids($debug-1);
    foreach ($del_phr_ids AS $del_phr_id) {
      if ($del_phr_id > 0) {
        zu_debug('phrase_list->diff_by_ids '.$del_phr_id, $debug-10);
        if ($del_phr_id > 0 AND in_array($del_phr_id, $this->ids)) {
          $del_pos = array_search($del_phr_id, $this->ids);
          zu_debug('phrase_list->diff_by_ids -> exclude ('.$this->lst[$del_pos]->name.')', $debug-10);
          unset ($this->lst[$del_pos]);
        }
      }
    }
    $this->ids = array_diff($this->ids, $del_phr_ids);
    zu_debug('phrase_list->diff_by_ids -> '.$this->dsp_id(), $debug-10);
  }
  
  // look at a phrase list and remove the general phrase, if there is a more specific phrase also part of the list e.g. remove "Country", but keep "Switzerland"
  function keep_only_specific ($debug) {
    zu_debug('phrase_list->keep_only_specific ('.$this->dsp_id(), $debug-10);

    $result = $this->ids();
    foreach ($this->lst AS $phr) {
      // temp workaround until the reason is found, why the user is sometimes not set
      if (!isset($phr->usr)) {
        $phr->usr = $this->usr;
      }
      $phr_lst_is = $phr->is($debug-1);
      if (isset($phr_lst_is)) {
        if (!empty($phr_lst_is->ids)) {
          $result = zu_lst_not_in_no_key($result, $phr_lst_is->ids, $debug-1);
          zu_debug('phrase_list->keep_only_specific -> "'.$phr->name.'" is of type '.$phr_lst_is->dsp_id(), $debug-10);
        }
      }
    }

    zu_debug('phrase_list->keep_only_specific -> ('.implode(",",$result).')', $debug-10);
    return $result;
  }

  // true if a phrase lst contains a time phrase
  function has_time ($debug) {
    $result = false;
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      zu_debug('phrase_list->has_time -> check ('.$phr->name.')', $debug-10);
      if ($result == false) { 
        if ($phr->is_time ($debug-1)) { 
          $result = true;
        }
      }
    }
    zu_debug('phrase_list->has_time -> ('.zu_dsp_bool($result).')', $debug-10);
    return $result;    
  }

  // true if a phrase lst contains a measure phrase
  function has_measure ($debug) {
    zu_debug('phrase_list->has_measure for '.$this->dsp_id(), $debug-10);
    $result = false;
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      zu_debug('phrase_list->has_measure -> check '.$phr->dsp_id(), $debug-10);
      if ($result == false) { 
        if ($phr->is_measure ($debug-1)) { 
          $result = true;
        }
      }
    }
    zu_debug('phrase_list->has_measure -> ('.zu_dsp_bool($result).')', $debug-10);
    return $result;    
  }

  // true if a phrase lst contains a scaling phrase
  function has_scaling ($debug) {
    $result = false;
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      zu_debug('phrase_list->has_scaling -> check '.$phr->dsp_id(), $debug-10);
      if ($result == false) { 
        if ($phr->is_scaling ($debug-1)) { 
          $result = true;
        }
      }
    }
    zu_debug('phrase_list->has_scaling -> ('.zu_dsp_bool($result).')', $debug-10);
    return $result;    
  }

  // true if a phrase lst contains a percent scaling phrase, which is used for a predefined formatting of the value
  function has_percent ($debug) {
    $result = false;
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      // temp solution for testing
      $phr->usr = $this->usr;
      zu_debug('phrase_list->has_percent -> check '.$phr->dsp_id(), $debug-10);
      if ($result == false) { 
        if ($phr->is_percent ($debug-1)) { 
          $result = true;
        }
      }
    }
    zu_debug('phrase_list->has_percent -> ('.zu_dsp_bool($result).')', $debug-10);
    return $result;    
  }

  // to be replaced by time_lst
  function time_lst_old ($debug) {
    zu_debug('phrase_list->time_lst_old('.$this->dsp_id().')', $debug-10);

    $result = array();
    $time_type = cl(SQL_WORD_TYPE_TIME);
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      if ($phr->type_id($debug-1) == $time_type) { 
        $result[] = $phr;
      }
    }
    //zu_debug('phrase_list->time_lst_old -> ('.zu_lst_dsp($result).')', $debug-1);
    return $result;    
  }

  // get all phrases of this phrase list that have a least one time term
  function time_lst ($debug) {
    zu_debug('phrase_list->time_lst for phrases '.$this->dsp_id(), $debug-10);

    $wrd_lst = $this->wrd_lst_all ($debug-12);
    $time_wrd_lst = $wrd_lst->time_lst ($debug-12);
    $result = $time_wrd_lst->phrase_lst($debug-12);
    $result->usr = $this->usr;
    return $result;    
  }

  // create a useful list of time phrase
  // to review !!!!
  function time_useful ($debug) {
    zu_debug('phrase_list->time_useful for '.$this->name(), $debug-14);
    
    $result = Null;

    $wrd_lst = $this->wrd_lst_all ($debug-1);
    $time_wrds = $wrd_lst->time_lst ($debug-1);
    zu_debug('phrase_list->time_useful times .', $debug-14);
    zu_debug('phrase_list->time_useful times '.implode(",",$time_wrds->ids), $debug-14);
    $result = Null; 
    foreach ($time_wrds->ids AS $time_id) {
      if (is_null($result)) {
        $time_wrd = New word_dsp;
        $time_wrd->id  = $time_id;
        $time_wrd->usr = $this->usr;
        $time_wrd->load($debug-1);
        // return a phrase not a word because "Q1" can be also a wikidata Qualifier and to differenciate this, "Q1 (Quarter)" should be returned
        $result = $time_wrd->phrase($debug-1); 
      } else {
        zu_warning("The word list contains more time word than supported by the program.","phrase_list->time_useful", '', (new Exception)->getTraceAsString(), $this->usr); 
      }
    }
    //$result = zu_lst_to_flat_lst($phrase_lst, $debug-1);
    //$result = clone $this;
    //$result->wlsort($debug-1);
    //$result = $phrase_lst;
    //asort($result);
    // sort 
    //print_r($phrase_lst);
    
    // get the most ofter time type e.g. years if the list contains more than 5 years
    //$type_most_used = zut_time_type_most_used ($phrase_lst, $debug-1);
    
    // if nothing special is defined try to select 20 % outlook to the future
    // get latest time without estimate
    // check the number of none estimate results
    // if the hist is longer than it should be dfine the start phrase
    // fill from the start phrase the default number of phrases

    
    //zu_debug('phrase_list->time_useful -> '.$result->name(), $debug-12);
    return $result;    
  }

  // to review !!!!
  function assume_time ($debug) {
    $time_phr = Null;
    $wrd_lst = $this->wrd_lst_all ($debug-1);
    $time_wrd = $wrd_lst->assume_time($debug-1); 
    if (isset($time_wrd)) { $time_phr = $time_wrd->phrase($debug-1); }
    return $time_phr;    
  }
  
  // filter the measure phrases out of the list of phrases
  function measure_lst ($debug) {
    zu_debug('phrase_list->measure_lst('.$this->dsp_id(), $debug-10);

    $result = New phrase_list;
    $result->usr = $this->usr;
    $measure_type = cl(SQL_WORD_TYPE_MEASURE);
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      if (get_class($phr) <> 'phrase' AND get_class($phr) <> 'word' AND get_class($phr) <> 'word_dsp') {
        zu_warning('The phrase list contains '.$this->dsp_id.' of type '.get_class($phr).', which is not supoosed to be in the list.', 'phrase_list->measure_lst', '', (new Exception)->getTraceAsString(), $this->usr);
        zu_debug('phrase_list->measure_lst contains object '.get_class($phr).', which is not a phrase.', $debug-10);
      } else {
        if ($phr->type_id($debug-1) == $measure_type) { 
          $result->add($phr, $debug-10);
          zu_debug('phrase_list->measure_lst -> found ('.$phr->name.')', $debug-10);
        } else {
          zu_debug('phrase_list->measure_lst -> '.$phr->name.' has type id '.$phr->type_id($debug-1).', which is not the measure type id '.$measure_type, $debug-10);
        }
      }
    }
    zu_debug('phrase_list->measure_lst -> ('.count($result->lst).')', $debug-10);
    return $result;    
  }

  // filter the scaling phrases out of the list of phrases
  function scaling_lst ($debug) {
    zu_debug('phrase_list->scaling_lst('.$this->dsp_id(), $debug-10);

    $result = New phrase_list;
    $result->usr = $this->usr;
    $scale_type        = cl(SQL_WORD_TYPE_SCALING);
    $scale_hidden_type = cl(SQL_WORD_TYPE_SCALING_HIDDEN);
    // loop over the phrase ids and add only the time ids to the result array
    foreach ($this->lst as $phr) {
      if ($phr->type_id($debug-1) == $scale_type OR $phr->type_id($debug-1) == $scale_hidden_type) { 
        $result->add($phr, $debug-10);
        zu_debug('phrase_list->scaling_lst -> found ('.$phr->name.')', $debug-10);
      } else {
        zu_debug('phrase_list->scaling_lst -> not found ('.$phr->name.')', $debug-10);
      }
    }
    zu_debug('phrase_list->scaling_lst -> ('.count($result->lst).')', $debug-10);
    return $result;    
  }

  // Exclude all time phrases out of the list of phrases
  function ex_time ($debug) {
    zu_debug('phrase_list->ex_time '.$this->dsp_id(), $debug-10);
    $del_phr_lst = $this->time_lst ($debug-12);
    $this->diff($del_phr_lst, $debug-1);
    //$this->diff_by_ids($del_phr_lst->ids, $debug-12);
    zu_debug('phrase_list->ex_time '.$this->name().' (exclude times '.$del_phr_lst->name().').', $debug-12);
  }

  // Exclude all measure phrases out of the list of phrases
  function ex_measure ($debug) {
    $del_phr_lst = $this->measure_lst ($debug-1);
    $this->diff($del_phr_lst, $debug-1);
    zu_debug('phrase_list->ex_measure '.$this->name().' (exclude measure '.$del_phr_lst->name().').', $debug-10);
  }

  // Exclude all scaling phrases out of the list of phrases
  function ex_scaling ($debug) {
    $del_phr_lst = $this->scaling_lst ($debug-1);
    $this->diff($del_phr_lst, $debug-1);
    zu_debug('phrase_list->ex_scaling '.$this->name().' (exclude scaling '.$del_phr_lst->name().').', $debug-10);
  }

  // sort the phrase object list by name
  function osort ($debug) {
    zu_debug('phrase_list->wlsort '.$this->dsp_id().' and user '.$this->usr->name, $debug-12);
    $name_lst = array();
    $result   = array();
    $pos = 0;
    foreach ($this->lst AS $phr) {
      $name_lst[$pos] = $phr->name;
      $pos++;
    }
    asort($name_lst);
    zu_debug('phrase_list->wlsort names sorted "'.implode('","',$name_lst).'" ('.implode(',',array_keys($name_lst)).').', $debug-14);
    foreach (array_keys($name_lst) AS $sorted_id) {
      zu_debug('phrase_list->wlsort get '.$sorted_id, $debug-10);
      $phr_to_add = $this->lst[$sorted_id];
      zu_debug('phrase_list->wlsort got '.$phr_to_add->name, $debug-10);
      $result[] = $phr_to_add;
    }
    // check
    if (count($this->lst) <> count($result)) {
      zu_err("Sorting changed the number of phrases from ".count($this->lst)." to ".count($result).".", "phrase_list->wlsort", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $this->lst = $result;
      $this->ids();
    }  
    zu_debug('phrase_list->wlsort sorted '.$this->dsp_id(), $debug-10);
    return $result;    
  }
  
  // get the last time phrase of the phrase list
  function max_time ($debug) {
    zu_debug('phrase_list->max_time ('.$this->dsp_id().' and user '.$this->usr->name.')', $debug-10);
    $max_phr = new phrase; 
    $max_phr->usr = $this->usr;
    if (count($this->lst) > 0) {
      foreach ($this->lst AS $phr) {
        // to be replace by "is following"
        if ($phr->name > $max_phr->name) {
          zu_debug('phrase_list->max_time -> select ('.$phr->name.' instead of '.$max_phr->name.')', $debug-10);
          $max_phr = clone $phr;
        }
      }
    }
    return $max_phr;    
  }
  
  // get the best matching phrase group (but don't create a new group)
  function get_grp ($debug) {
    zu_debug('phrase_list->get_grp '.$this->dsp_id(), $debug-10);
    $grp = Null;

    // check the needed data consistency
    if ($this->ids == '') {
      if (count($this->lst) > 0) {
        $this->ids = $this->ids();
      }
    }
    
    // get or create the group
    if (count($this->ids) <= 0) {
      zu_err('Cannot create phrase group for an empty list.', 'phrase_list->get_grp', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $grp = New phrase_group;
      $grp->phr_lst = $this;
      $grp->ids     = $this->ids;
      $grp->usr     = $this->usr;
      $result = $grp->get($debug-1);
    }
    
    zu_debug('phrase_list->get_grp -> '.$this->dsp_id(), $debug-10);
    return $grp;
  }

  // return all phrases that are part of each phrase group of the list
  function common($filter_lst, $debug) {
    if (is_array($this->lst) and is_array($filter_lst->lst)) {
      zu_debug('phrase_list->common of '.$this->name().' and '.$filter_lst->name(), $debug-24);
      if (count($this->lst) > 0) {
        $result = array();
        foreach ($this->lst AS $phr) {
          if (isset($phr)) {
            zu_debug('phrase_list->common check if "'.$phr->name.'" is in '.$filter_lst->name(), $debug-26);
            if (in_array($phr, $filter_lst->lst)) {
              $result[] = $phr;
            }
          }
        }  
        $this->lst = $result;
        $this->ids();
      }
    }  
    zu_debug('phrase_list->common ('.count($this->lst).')', $debug-24);
    return $result; 
  }

  // combine two phrase lists 
  function concat_unique($join_phr_lst, $debug) {
    zu_debug('phrase_list->concat_unique.', $debug-14);
    $result = clone $this;
    if (isset($join_phr_lst->lst) AND isset($result->lst)) {
      foreach ($join_phr_lst->lst as $phr) {
        if (!in_array($phr, $result->lst)) {
          $result->lst[] = $phr;
          $result->ids[] = $phr->id;
        }
      }  
    }
    zu_debug('phrase_list->concat_unique ('.count($result->lst).')', $debug-14);
    return $result; 
  }

  /*
  
  data request function
  
  */
  
  // get all values related to this phrase list
  function val_lst($debug) {
    $val_lst = New value_list;
    $val_lst->phr_lst = $this;
    $val_lst->usr     = $this->usr;
    $val_lst->load_all($debug-1);
    
    return $val_lst; 
  }
  
  // get all formulas related to this phrase list
  function frm_lst($debug) {
    $frm_lst = New formula_list;
    $frm_lst->phr_lst = $this;
    $frm_lst->usr     = $this->usr;
    $frm_lst->load($debug-1);
    
    return $frm_lst; 
  }
  

  // get the best matching value or value list for this phrase list
  /*
  
  e.g. if for "ABB", "Sales" no direct number is found, 
       1) try to get a formula result, if also no formula result, 
       2) assume an additional phrase by getting the phrase with the most values for the phrase list
       which could be in this case "millions"
       3) repeat with 2(
    
   e.g. if many numbers matches the phrase list e.g. Nestlé Sales million, CHF (and Water, and Coffee)
        the value with the least additional phrases is selected
  
  */
  function value($debug) {
    $val = New value;
    $val->ids = $this->ids;
    $val->usr = $this->usr;
    $val->load($debug-1);
    
    return $val; 
  }
  
  function value_scaled($debug) {
    $val = $this->value($debug-1);
    $wrd_lst = $this->wrd_lst_all($debug-1);
    $val->number = $val->scale ($wrd_lst, $debug-1);
    
    return $val; 
  }
}

?>
