<?php

/*

  expression.php - a text (usually in the database reference format) that implies a data selection and can calculate a number
  --------------
  
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

// parameters used also in other classes
define("EXP_ELM_SELECT_ALL",       "all");        // to get all formula elements
define("EXP_ELM_SELECT_PHRASE",    "phrases");    // to filter only the words from the expression element list
define("EXP_ELM_SELECT_VERB",      "verbs");      // to filter only the verbs from the expression element list
define("EXP_ELM_SELECT_FORMULA",   "formulas");   // to filter only the formulas from the expression element list
define("EXP_ELM_SELECT_VERB_WORD", "verb_words"); // to filter the words and the words implied by the verbs from the expression element list

class expression {

  public $usr_text   = '';      // the formula expression in the human readable format
  public $ref_text   = '';      // the formula expression with the database references
  public $num_text   = '';      // the formula expression with all numbers loaded (ready for R)
  public $err_text   = '';      // description of the problems that appeared during the conversion from the human readable to the database reference format
  public $usr        = Null;    // to get the user settings for the conversion
  public $fv_phr_lst = Null;    // list object of the words that should be added to the formula result
  public $phr_lst    = array(); // list of the word ids that are used for the formula result
  
  // returns a positive reference (word, verb or formula) id if the formula string in the database format contains a database reference link
  // uses the $ref_text as a parameter because to ref_text is in many cases only a part of the complete reference text
  private function get_ref_id ($ref_text, $start_maker, $end_maker, $debug) {
    zu_debug('expression->get_ref_id >'.$ref_text.'<.', $debug-12);
    $result = 0;

    $pos_start = strpos($ref_text, $start_maker);
    if ($pos_start === false) {
      $result = 0;
    } else {
      $r_part = zu_str_right_of($ref_text, $start_maker);
      $l_part = zu_str_left_of ($r_part,  $end_maker);
      if (is_numeric($l_part)) {
        $result = $l_part;
        zu_debug('expression->get_ref_id -> part "'.$result.'".', $debug-14);
      }
    }

    zu_debug('expression->get_ref_id -> "'.$result.'".', $debug-10);
    return $result;
  }

  // returns a positive word id if the formula string in the database format contains a word link
  private function get_wrd_id ($ref_text, $debug) {
    zu_debug('expression->get_wrd_id "'.$ref_text.'".', $debug-10);
    $result = $this->get_ref_id ($ref_text, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END, $debug-1);
    return $result;
  }

  private function get_frm_id ($ref_text, $debug) {
    zu_debug('expression->get_wrd_id "'.$ref_text.'".', $debug-10);
    $result = $this->get_ref_id ($ref_text, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END, $debug-1);
    return $result;
  }

  private function get_lnk_id ($ref_text, $debug) {
    zu_debug('expression->get_wrd_id "'.$ref_text.'".', $debug-10);
    $result = $this->get_ref_id ($ref_text, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END, $debug-1);
    return $result;
  }

  // find the position of the formula indicator "="
  // use the part left of it to add the words to the result
  function fv_part ($debug) {
    $result = zu_str_left_of($this->ref_text, ZUP_CHAR_CALC);
    $result = trim($result);
    return $result;
  }

  function fv_part_usr ($debug) {
    $result = zu_str_left_of($this->usr_text, ZUP_CHAR_CALC);
    $result = trim($result);
    return $result;
  }

  function r_part ($debug) {
    $result = zu_str_right_of($this->ref_text, ZUP_CHAR_CALC);
    $result = trim($result);
    return $result;
  }

  function r_part_usr ($debug) {
    $result = zu_str_right_of($this->usr_text, ZUP_CHAR_CALC);
    $result = trim($result);
    return $result;
  }

  // get the phrases that should be added to the result of a formula
  // e.g. for >"percent" = ( "this" - "prior" ) / "prior"< a list with the phrase "percent" will be returned
  function fv_phr_lst ($debug) {
    zu_debug('expression->fv_phr_lst >'.$this->ref_text.'< and user '.$this->usr->name.'".', $debug-11);
    $phr_lst = Null;
    $wrd_ids = array();
    
    // create a local copy of the reference text not to moditfy the original text
    $ref_text = $this->fv_part($debug-1);

    if ($ref_text <> "") {
      // add words to selection
      $new_wrd_id = $this->get_wrd_id($ref_text, $debug-1);
      while ($new_wrd_id > 0) {
        if (!in_array($new_wrd_id, $wrd_ids)) {
          $wrd_ids[] = $new_wrd_id; 
        }
        $ref_text = zu_str_right_of($ref_text, ZUP_CHAR_WORD_START.$new_wrd_id.ZUP_CHAR_WORD_END);
        $new_wrd_id = $this->get_wrd_id($ref_text, $debug-1);
      }
      $phr_lst = New phrase_list;
      $phr_lst->ids = $wrd_ids;
      $phr_lst->usr = $this->usr;
      $phr_lst->load($debug-10);
      zu_debug('expression->fv_phr_lst -> '.$phr_lst->name().'.', $debug-9);
    }

    zu_debug('expression->fv_phr_lst -> done.', $debug-19);
    $this->fv_phr_lst = $phr_lst;
    return $phr_lst;
  }

  // extracts an array with the words from a given formula text and load the words
  function phr_lst ($debug) {
    zu_debug('expression->phr_lst "'.$this->ref_text.',u'.$this->usr->name.'".', $debug-7);
    $phr_lst = Null;
    $wrd_ids = array();
    
    // create a local copy of the reference text not to moditfy the original text
    $ref_text = $this->r_part();

    if ($ref_text <> "") {
      // add words to selection
      $new_wrd_id = $this->get_wrd_id($ref_text, $debug-1);
      while ($new_wrd_id > 0) {
        if (!in_array($new_wrd_id, $wrd_ids)) {
          $wrd_ids[] = $new_wrd_id; 
        }
        $ref_text = zu_str_right_of($ref_text, ZUP_CHAR_WORD_START.$new_wrd_id.ZUP_CHAR_WORD_END);
        zu_debug('remaining: '.$ref_text.'', $debug-7);
        $new_wrd_id = $this->get_wrd_id($ref_text, $debug-1);
      }
      
      // load the word parameters
      $phr_lst = New phrase_list;
      $phr_lst->ids = $wrd_ids;
      $phr_lst->usr = $this->usr;
      if (!empty($wrd_ids)) {
        $phr_lst->load($debug-1);
      }
    }

    zu_debug('expression->phr_lst -> '.$phr_lst->name().'.', $debug-7);
    $this->phr_lst = $phr_lst;
    return $phr_lst;
  }

  // create a list of all formula elements
  // with the $type parameter the result list can be filtered
  // the filter is done within this function, because e.g. a verb can increase the number of words to return
  // if group it is true, element groups instead of single elements are returned
  private function element_lst_all ($type, $group_it, $back, $debug) {
    zu_debug('expression->element_lst_all get '.$type.' out of "'.$this->ref_text.'" for user '.$this->usr->name.'.', $debug-10);

    // init result and work vars
    $lst = array();                 
    if ($group_it) {
      $result  = New formula_element_group_list;                 
      $elm_grp = New formula_element_group;
      $elm_grp->usr = $this->usr;
    } else {
      $result = New formula_element_list;                 
    }
    $result->usr = $this->usr;
    $work = $this->r_part($debug-1);
    if (is_null($type) OR $type == "") {
      $type = EXP_ELM_SELECT_ALL;
    }

    if ($work == '') {
      // zu_warning ???
    } else {
      // loop over the formula text and replace ref by ref from left to right
      $found = true;
      $nbr = 0;
      while ($found AND $nbr < MAX_LOOP) {
        zu_debug('expression->element_lst_all -> in "'.$work.'".', $debug-18);
        $found = false;

        // $pos is the position von the next element
        // to list the elements from left to right, set it to the right most postion at the beginning of each replacement
        $pos = strlen($work); 
        
        // find the next word reference
        if ($type == EXP_ELM_SELECT_ALL OR $type == EXP_ELM_SELECT_PHRASE OR $type == EXP_ELM_SELECT_VERB_WORD) {
          $elm = New formula_element;
          $elm->usr = $this->usr;
          $elm_id = zu_str_between($work, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END, $debug-20);
          if (is_numeric($elm_id)) {
            if ($elm_id > 0) {
              $elm->type   = 'word';
              $elm->id     = $elm_id;
              $pos = strpos($work, ZUP_CHAR_WORD_START);
              zu_debug('expression->element_lst_all -> wrd pos '.$pos.'', $debug-20);
            }
          }
        }

        // find the next verb reference
        if ($type == EXP_ELM_SELECT_ALL OR $type == EXP_ELM_SELECT_VERB) {
          $new_pos = strpos($work, ZUP_CHAR_LINK_START);
          zu_debug('expression->element_lst_all -> verb pos '.$new_pos.'', $debug-20);
          if ($new_pos < $pos) {
            $elm_id = zu_str_between($work, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END, $debug-20);
            if (is_numeric($elm_id)) {
              if ($elm_id > 0) {
                $elm->type   = 'verb';
                $elm->id     = $elm_id;
                $pos = $new_pos;
              }
            }
          }
        }

        // find the next formula reference
        if ($type == EXP_ELM_SELECT_ALL OR $type == EXP_ELM_SELECT_FORMULA OR $type == EXP_ELM_SELECT_PHRASE OR $type == EXP_ELM_SELECT_VERB_WORD) {
          $new_pos = strpos($work, ZUP_CHAR_FORMULA_START);
          zu_debug('expression->element_lst_all -> frm pos '.$new_pos.'', $debug-20);
          if ($new_pos < $pos) {
            $elm_id = zu_str_between($work, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END, $debug-20);
            if (is_numeric($elm_id)) {
              if ($elm_id > 0) {
                $elm->type   = 'formula';
                $elm->id     = $elm_id;
                $pos = $new_pos;
              }
            }
          }
        } 

        // add reference to result
        if (is_numeric($elm->id)) {
          if ($elm->id > 0) {
            $elm->usr = $this->usr;
            $elm->back = $back;
            $elm->load($debug-12);

            // update work text
            $changed = str_replace($elm->symbol, $elm->name, $work);
            zu_debug('expression->element_lst_all -> found "'.$elm->name.'" for '.$elm->symbol.', so "'.$work.'" is now "'.$changed.'"', $debug-12);
            if ($changed <> $work) {
              $work = $changed;
              $found = true;
              $pos = $pos + strlen($elm->name);
            }
            
            // group the references if needed
            if ($group_it) {
              $elm_grp->lst[]  = $elm;
              zu_debug('expression->element_lst_all -> new group element "'.$elm->name.'".', $debug-18);
              
              if ($pos > 0) {
                // get the position of the next element to check if a new group should be created or added to the same
                $next_pos = strlen($work); 
                zu_debug('expression->element_lst_all -> next_pos '.$next_pos.'.', $debug-20);
                $new_pos = strpos($work, ZUP_CHAR_WORD_START);
                if ($new_pos < $next_pos) {
                  $elm_id = zu_str_between($work, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END, $debug-20);
                  if (is_numeric($elm_id)) {
                    if ($elm_id > 0) {
                      $next_pos = $new_pos;
                      zu_debug('expression->element_lst_all -> next_pos shorter by word '.$next_pos.'.', $debug-20);
                    }  
                  }  
                }  
                $new_pos = strpos($work, ZUP_CHAR_LINK_START);
                if ($new_pos < $next_pos) {
                  $elm_id = zu_str_between($work, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END, $debug-20);
                  if (is_numeric($elm_id)) {
                    if ($elm_id > 0) {
                      $next_pos = $new_pos;
                      zu_debug('expression->element_lst_all -> next_pos shorter by verb '.$next_pos.'.', $debug-20);
                    }  
                  }  
                }  
                $new_pos = strpos($work, ZUP_CHAR_FORMULA_START);
                if ($new_pos < $next_pos) {
                  $elm_id = zu_str_between($work, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END, $debug-20);
                  if (is_numeric($elm_id)) {
                    if ($elm_id > 0) {
                      $next_pos = $new_pos;
                      zu_debug('expression->element_lst_all -> next_pos shorter by formula  '.$next_pos.'.', $debug-20);
                    }  
                  }  
                }  

                // get the text between the references
                $len = $next_pos - $pos;
                zu_debug('expression->element_lst_all -> in "'.$work.'" after '.$pos.' len '.$len.' "'.$next_pos.' - '.$pos.').', $debug-18);
                $txt_between_elm = substr($work, $pos, $len);
                zu_debug('expression->element_lst_all -> between elements "'.$txt_between_elm.'" ("'.$work.'" from '.$pos.' to '.$next_pos.').', $debug-22);
                $txt_between_elm = str_replace('"','',$txt_between_elm);
                $txt_between_elm = trim($txt_between_elm);
              }
              // check if the references does not have any math symbol in between and therefor are use to retrieve one value
              if (strlen($txt_between_elm) > 0 OR $next_pos == strlen($work)) {
                $lst[]  = $elm_grp;
                zu_debug('expression->element_lst_all -> group finished with '.$elm->name.'.', $debug-10);
                $elm_grp = New formula_element_group;
                $elm_grp->usr = $this->usr;
              }
            } else {
              $lst[]  = $elm;
            }
            $nbr++;
          }
        } 
      } 

      // add last element group
      if ($group_it) {
        if (!empty($elm_grp->lst)) {
          $lst[]  = $elm_grp;
        }
      }
    } 
    $result->lst = $lst;
    
    zu_debug('expression->element_lst_all got -> '.count($result->lst).' elements.', $debug-6);
    return $result;
  }

  // get a list of all formula elements (don't use for number retrieval, use element_grp_lst instead, because )
  function element_lst ($back, $debug) {
    $result = $this->element_lst_all(EXP_ELM_SELECT_ALL, FALSE, $back, $debug-1);
    return $result;
  }
  
  // a formula element group is a group of words, verbs, phrases or formula that retrieve a value or a list of values
  // e.g. with "Sector" "differentiator" all 
  function element_grp_lst ($back, $debug) {
    $result = $this->element_lst_all(EXP_ELM_SELECT_ALL, TRUE, $back, $debug-1);
    return $result;
  }
  
  // similar to phr_lst, but (todo!) should also include the words implied by the verbs 
  // e.g. for "Sales" "differentiator" "Country" all "Country" words should be included
  function phr_verb_lst ($back, $debug) {
    zu_debug('expression->phr_verb_lst.', $debug-14);
    $elm_lst = $this->element_lst_all(EXP_ELM_SELECT_PHRASE, FALSE, $back, $debug-1);
    zu_debug('expression->phr_verb_lst -> got '.count($elm_lst->lst).' formula elements.', $debug-14);
    $phr_lst = New phrase_list;
    $phr_lst->usr = $this->usr;
    foreach ($elm_lst->lst AS $elm) {
      zu_debug('expression->phr_verb_lst -> check elements '.$elm->name().'.', $debug-14);
      if ($elm->type == 'formula') {
        if (isset($elm->wrd_obj)) {
          $phr = $elm->wrd_obj->phrase($debug-1);
          $phr_lst->lst[] = $phr;
          $phr_lst->ids[] = $phr->id;
        } else {  
          $result .= zu_err('Word missing for formula element '.$elm->dsp_id.'.', 'expression->phr_verb_lst', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } else {  
        $phr_lst->lst[] = $elm;
        $phr_lst->ids[] = $elm->id;
      }
    }
    $phr_lst->load($debug-1);
    zu_debug('expression->phr_verb_lst -> '.count($phr_lst->lst).'.', $debug-10);
    return $phr_lst;
  }
  
  // list of elements (in this case only formulas) that are of the predefined type "following", e.g. "this", "next" and "prior"
  function element_special_following ($back, $debug) {
    $elm_lst = $this->element_lst_all(EXP_ELM_SELECT_ALL, FALSE, $back, $debug-1);
    if (!empty($elm_lst->lst)) {
      $phr_lst = New phrase_list;
      $phr_lst->usr = $this->usr;
      foreach ($elm_lst->lst AS $elm) {
        if ($elm->frm_type == SQL_FORMULA_TYPE_THIS
        OR $elm->frm_type == SQL_FORMULA_TYPE_NEXT
        OR $elm->frm_type == SQL_FORMULA_TYPE_PREV) {
          $phr_lst->lst[] = $elm->wrd_obj;
          $phr_lst->ids[] = $elm->wrd_id;
        }
      }
      if (!empty($phr_lst->lst)) {
        $phr_lst->load($debug-1);
      }  
    }
    
    zu_debug('expression->element_special_following -> '.count($phr_lst->lst).'.', $debug-9);
    return $phr_lst;
  }
  
  // similar to element_special_following, but returns the formula and not the word
  function element_special_following_frm ($back, $debug) {
    $elm_lst = $this->element_lst_all(EXP_ELM_SELECT_ALL, FALSE, $back, $debug-1);
    if (!empty($elm_lst->lst)) {
      $frm_lst = New formula_list;
      $frm_lst->usr = $this->usr;
      foreach ($elm_lst->lst AS $elm) {
        if ($elm->frm_type == SQL_FORMULA_TYPE_THIS
        OR $elm->frm_type == SQL_FORMULA_TYPE_NEXT
        OR $elm->frm_type == SQL_FORMULA_TYPE_PREV) {
          $frm_lst->lst[] = $elm->obj;
          $frm_lst->ids[] = $elm->id;
        }
      }
      zu_debug('expression->element_special_following_frm -> pre load '.count($frm_lst->lst).'.', $debug-19);
      /*
      if (!empty($frm_lst->lst)) {
        $frm_lst->load($debug-1);
      } 
      */
    }
    
    zu_debug('expression->element_special_following_frm -> '.count($frm_lst->lst).'.', $debug-9);
    return $frm_lst;
  }
  
  // converts a formula from the database reference format to the human readable format
  // e.g. converts "={t6}{l12}/{f19}" to "='Sales' 'differentiator'/'Total Sales'"
  private function get_usr_part ($formula, $debug) {
    zu_debug('expression->get_usr_part >'.$formula.'< and user '.$this->usr->name.'.', $debug-10);
    $result = $formula;
    
    // replace the words
    $id = zu_str_between($result, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END, $debug-1);
    while ($id > 0) {
      $db_sym = ZUP_CHAR_WORD_START.$id.ZUP_CHAR_WORD_END;
      $wrd = new word_dsp;
      $wrd->id  = $id;
      $wrd->usr = $this->usr;
      $wrd->load($debug-1);
      $result = str_replace($db_sym, ZUP_CHAR_WORD.$wrd->name.ZUP_CHAR_WORD, $result);
      $id = zu_str_between($result, ZUP_CHAR_WORD_START, ZUP_CHAR_WORD_END, $debug-1);
    }

    // replace the formulas
    $id = zu_str_between($result, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END, $debug-1);
    while ($id > 0) {
      $db_sym = ZUP_CHAR_FORMULA_START.$id.ZUP_CHAR_FORMULA_END;
      $frm = New formula;
      $frm->id  = $id;
      $frm->usr = $this->usr;
      $frm->load($debug-1);
      $result = str_replace($db_sym, ZUP_CHAR_WORD.$frm->name.ZUP_CHAR_WORD, $result);
      $id = zu_str_between($result, ZUP_CHAR_FORMULA_START, ZUP_CHAR_FORMULA_END, $debug-1);
    }

    // replace the verbs
    $id = zu_str_between($result, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END, $debug-1);
    while ($id > 0) {
      $db_sym = ZUP_CHAR_LINK_START.$id.ZUP_CHAR_LINK_END;
      $vrb = New verb;
      $vrb->id  = $id;
      $vrb->usr = $this->usr;
      $vrb->load($debug-1);
      $result = str_replace($db_sym, ZUP_CHAR_WORD.$vrb->name.ZUP_CHAR_WORD, $result);
      $id = zu_str_between($result, ZUP_CHAR_LINK_START, ZUP_CHAR_LINK_END, $debug-1);
    }

    zu_debug('expression->get_usr_part -> "'.$result.'".', $debug-10);
    return $result;
  }

  // convert the database reference format to the user text 
  function get_usr_text ($debug) {
    zu_debug('expression->get_usr_text >'.$this->ref_text.'< and user '.$this->usr->name.'.', $debug-10);
    $result = '';

    // check the formula indicator "=" and convert the left and right part seperately
    $pos = strpos($this->ref_text, ZUP_CHAR_CALC);
    if ($pos > 0) {
      $left_part  = $this->fv_part();
      $right_part = $this->r_part();
      zu_debug('expression->get_usr_text -> (l:'.$left_part.',r:'.$right_part.'".', $debug-1);
      $left_part  = $this->get_usr_part($left_part, $debug-1);
      $right_part = $this->get_usr_part($right_part, $debug-1);
      $result = $left_part . ZUP_CHAR_CALC . $right_part;
    }

    zu_debug('expression->get_usr_text ... done "'.$result.'".', $debug-10);
    return $result; 
  }

  // converts a formula from the user text format to the database reference format
  // e.g. converts "='Sales' 'differentiator'/'Total Sales'" to "={t6}{l12}/{f19}"
  private function get_ref_part ($formula, $debug) {
    zu_debug('expression->get_ref_part "'.$formula.','.$this->usr->name.'".', $debug-8);
    $result = $formula;
    
    // find the first word
    $start = 0;
    $pos = strpos($result, ZUP_CHAR_WORD, $start);
    $end = strpos($result, ZUP_CHAR_WORD, $pos + 1);
    while ($end !== False) {
      // for 12'45'78: pos = 2, end = 5, name = 45, left = 12. right = 78
      $name  = substr($result, $pos + 1, $end - $pos - 1);
      $left  = substr($result, 0,               $pos);
      $right = substr($result, $end + 1);
      zu_debug('expression->get_ref_part -> name "'.$name.'" ('.$end.') left "'.$left.'" ('.$pos.') right "'.$right.'"', $debug-10);
      
      $db_sym = '';
      
      // check for formulas first, because for every formula a word is also existing
      // similar to a part in get_usr_part, maybe combine
      if ($db_sym == '') {
        $frm = New formula;
        $frm->name = $name;
        $frm->usr  = $this->usr;
        $frm->load($debug-1);
        if ($frm->id > 0) {
          $db_sym = ZUP_CHAR_FORMULA_START.$frm->id.ZUP_CHAR_FORMULA_END;
          zu_debug('expression->get_ref_part -> found formula "'.$db_sym.'" for "'.$name.'"', $debug-10);
        }  
      }  

      // check for words
      if ($db_sym == '') {
        $wrd = new word_dsp;
        $wrd->name = $name;
        $wrd->usr  = $this->usr;
        $wrd->load($debug-1);
        if ($wrd->id > 0) {
          $db_sym = ZUP_CHAR_WORD_START.$wrd->id.ZUP_CHAR_WORD_END;
          zu_debug('expression->get_ref_part -> found word "'.$db_sym.'" for "'.$name.'"', $debug-10);
        } 
      } 

      // check for verbs
      if ($db_sym == '') {
        $vrb = New verb;
        $vrb->name   = $name;
        $vrb->usr_id = $this->usr->id;
        $vrb->load($debug-1);
        if ($vrb->id > 0) {
          $db_sym = ZUP_CHAR_LINK_START.$vrb->id.ZUP_CHAR_LINK_END;
          zu_debug('expression->get_ref_part -> found verb "'.$db_sym.'" for "'.$name.'"', $debug-10);
        }  
      }  
      
      // if still not found report the missing link
      if ($db_sym == '' AND $name <> '') {
        $this->err_text .= 'No word, triple, formula or verb found for "'.$name.'". ';
      }

      $result = $left . $db_sym . $right;
      zu_debug('expression->get_ref_part -> changed to "'.$result.'"', $debug-10);

      // find the next word
      $start = strlen($left) + strlen($db_sym);
      zu_debug('expression->get_ref_part -> start "'.$start.'"', $debug-10);
      $pos = strpos($result, ZUP_CHAR_WORD, $start);
      zu_debug('expression->get_ref_part -> pos "'.$pos.'"', $debug-10);
      $end = strpos($result, ZUP_CHAR_WORD, $pos + 1);
    }

    zu_debug('expression->get_ref_part -> done "'.$result.'".', $debug-7);
    return $result;
  }

  // convert the user text to the database reference format
  function get_ref_text ($debug) {
    zu_debug('expression->get_ref_text '.$this->dsp_id().'.', $debug-12);
    $result = '';

    // check the formula indicator "=" and convert the left and right part seperately
    $pos = strpos($this->usr_text, ZUP_CHAR_CALC);
    if ($pos >= 0) {
      $left_part  = $this->fv_part_usr();
      $right_part = $this->r_part_usr();
      zu_debug('expression->get_ref_text -> (l:'.$left_part.',r:'.$right_part.'".', $debug-14);
      $left_part  = $this->get_ref_part($left_part, $debug-1);
      $right_part = $this->get_ref_part($right_part, $debug-1);
      $result = $left_part . ZUP_CHAR_CALC . $right_part;
    }

    // remove all spaces because they are not relevent for calculation and to avoid too much recalculation
    $result = str_replace(" ","",$result);
    
    zu_debug('expression->get_ref_text -> done "'.$result.'".', $debug-10);
    return $result; 
  }

  // returns true if the formula contains a word, verb or formula link
  function has_ref ($debug) {
    zu_debug('expression->has_ref '.$this->dsp_id().'.', $debug-12);
    $result = false;

    if ($this->get_wrd_id($this->ref_text, $debug-1) > 0 
     OR $this->get_frm_id($this->ref_text, $debug-1) > 0 
     OR $this->get_ref_id($this->ref_text, $debug-1) > 0) {
      $result = true;
    }

    zu_debug('expression->has_ref -> done '.zu_dsp_bool($result).'.', $debug-10);
    return $result; 
  }
  
  /*
  
  display functions
  
  */
  
  // format the expression name to use it for debugging
  function dsp_id ($debug) {
    $result = '"'.$this->usr_text.'" ('.$this->ref_text.')';
    /* the user is no most cases no extra info
    $result .= ' for user '.$this->usr->name.'';
    */
    return $result;
  }

}

?>
