<?php

/*

  word_display.php - the extension of the word object to create word base html code
  ----------------
  
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

class word_dsp extends word {
 
  // display a word as the view header
  function dsp_header ($debug) {
    zu_debug('word_dsp->dsp_header ('.$this->id.')', $debug-10);
    $result  = '';
    
    if ($this->id <= 0) {
      $result .= 'no word selected';
    } else {
      // load the word parameters if not yet done 
      if ($this->name == "") {
        $this->load($debug-1);
      }
      
      $is_part_of = $this->is_mainly($debug-1);
      $default_view_id = cl(SQL_VIEW_WORD);
      $title = '';
      //$title .= '<a href="/http/view.php?words='.$this->id.'&view='.$default_view_id.'" title="'.$this->description.'">'.$this->name.'</a>';
      $title .= $this->name;
      if ($is_part_of->name <> '' and $is_part_of->name <> 'not set') {
        $title .= ' (<a href="/http/view.php?words='.$is_part_of->id.'">'.$is_part_of->name.'</a>)';
      }
/*      $title .= '  '.'<a href="/http/word_edit.php?id='.$this->id.'&back='.$this->id.'" title="Rename word"><img src="'.ZUH_IMG_EDIT.'" alt="Rename word" style="height: 0.65em;"></a>'; */
      $title .= '  '.'<a href="/http/word_edit.php?id='.$this->id.'&back='.$this->id.'" title="Rename word"><span class="glyphicon glyphicon-pencil"></a>';
      $title .= '</h2>'; 
      $result .= dsp_text_h1 ($title, '');
    }
      
    return $result;
  }


  // simply to display a single word link
  function dsp_link ($debug) {
    $result = '<a href="/http/view.php?words='.$this->id.'" title="'.$this->description.'">'.$this->name.'</a>';
    return $result;
  }

  // similar to dsp_link, but using s CSS style; used by ??? to ???
  function dsp_link_style ($style, $debug) {
    $result = '<a href="/http/view.php?words='.$this->id.'" title="'.$this->description.'" class="'.$style.'">'.$this->name.'</a>';
    return $result;
  }

  // simply to display a single word in a table as a header
  function dsp_tbl_head_right ($debug) {
    zu_debug('word_dsp->dsp_tbl_head_right', $debug-10);
    $result  = '    <th align="right">'."\n";
    $result .= '      '.$this->dsp_link($debug-1)."\n";
    $result .= '    </th>'."\n";
    return $result;
  }

  // simply to display a single word in a table cell
  function dsp_tbl_cell ($intent, $debug) {
    zu_debug('word_dsp->dsp_tbl_cell', $debug-10);
    $result  = '    <td>'."\n";
    while ($intent > 0) {
      $result .= '&nbsp;';
      $intent = $intent - 1;
    }
    $result .= '      '.$this->dsp_link ($debug-1).''."\n";
    $result .= '    </td>'."\n";
    return $result;
  }

  // simply to display a single word in a table
  // rename and jion to dsp_tbl_cell to have a more specific name
  function dsp_tbl ($intent, $debug) {
    zu_debug('word_dsp->dsp_tbl', $debug-10);
    $result  = '    <td>'."\n";
    while ($intent > 0) {
      $result .= '&nbsp;';
      $intent = $intent - 1;
    }
    $result .= '      '.$this->dsp_link($debug-1).''."\n";
    $result .= '    </td>'."\n";
    return $result;
  }

  function dsp_tbl_row ($debug) {
    $result  = '  <tr>'."\n";
    $result .= $this->dsp_tbl(0, $debug-1);
    $result .= '  </tr>'."\n";
    return $result;
  }
  
  // simply to display a single word and allow to delete it
  // used by value->dsp_edit
  function dsp_name_del ($del_call, $debug) {
    zu_debug('word_dsp->dsp_name_del', $debug-10);
    $result  = '  <tr>'."\n";
    $result .= $this->dsp_tbl_cell(0, $debug-1);
    $result .= '    <td>'."\n";
    $result .= '      '.btn_del ("delete", $del_call).'<br> ';
    $result .= '    </td>'."\n";
    $result .= '  </tr>'."\n";
    return $result;
  }

  //
  function dsp_selector ($type, $form_name, $pos, $class, $back, $debug) {
    $phr = $this->phrase();
    return $phr->dsp_selector ($type, $form_name, $pos, $class, $back, $debug) ;
  }
  
  // create a selector that contains the time words
  function dsp_time_selector ($type, $form_name, $pos, $back, $debug) {
    zu_debug('word_dsp->dsp_selector -> for form '.$form_name.''.$pos, $debug-10);
    $result = '';
    
    if ($pos > 0) {
      $field_name = "phrase".$pos;
      //$field_name = "time".$pos;
    } else {
      $field_name = "phrase";
      //$field_name = "time";
    }
    if ($type->id > 0) {
      $sql = "SELECT id, name 
              FROM ( SELECT w.word_id AS id, 
                            IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,    
                            IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                       FROM word_links l, words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = ".$this->usr->id." 
                      WHERE w.word_type_id = ".cl(SQL_WORD_TYPE_TIME)."
                        AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = ".cl(SQL_LINK_TYPE_IS)."              
                        AND l.to_phrase_id = ".$type->id."            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
    } else {
      $sql = "SELECT id, name 
              FROM ( SELECT w.word_id AS id, 
                            IF(u.word_name IS NULL, w.word_name, u.word_name) AS name,    
                            IF(u.excluded IS NULL, COALESCE(w.excluded, 0), COALESCE(u.excluded, 0)) AS excluded
                       FROM words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = ".$this->usr->id."
                      WHERE w.word_type_id = ".cl(SQL_WORD_TYPE_TIME)."
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                   
          ORDER BY name;";
    }               
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form_name;
    $sel->name       = $field_name;  
    $sel->sql        = $sql;
    $sel->selected   = $this->id;
    $sel->dummy_text = '... please select';
    $result .= $sel->display ($debug-1);
    
    zu_debug('word_dsp->dsp_selector -> done ', $debug-10);
    return $result;
  }
    
  // display the history of a word
  // maybe move this to a new object user_log_display
  // because this is very similar to a value linked function
  private function dsp_hist($page, $size, $call, $back, $debug) {
    zu_debug("word_dsp->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'word';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);

    zu_debug('word_dsp->dsp_hist -> done', $debug-10);
    return $result;
  }

  // show the changes of the view
  function dsp_log_view ($back, $debug) {
    zu_debug('word_dsp->dsp_log_view ('.$this->id.')', $debug-10);
    $result  = '';
    
    // if ($this->id <= 0 OR !is_null($this->usr_id)) {
    if ($this->id <= 0) {
      $result .= 'no word selected';
    } else {
      // load the word parameters if not yet done 
      if ($this->name == "") {
        $this->load($debug-1);
      }
      
      $changes = $this->dsp_hist(1, 20, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $result .= dsp_text_h3("Latest view changes related to this word", "change_hist");
        $result .= $changes;
      }
    }
      
    return $result;
  }

  // list of related words and values filtered by a link type
  function dsp_val_list ($col_wrd, $back, $debug) {
    zu_debug('word_dsp->dsp_val_list for '.$this->dsp_id().' with "'.$col_wrd->name.'" columns for user '.$this->usr->name, $debug-10);
    $result = '';
    
    $result .= $this->dsp_header ($debug-1);
    
    //$result .= $this->name."<br>";
    //$result .= $col_wrd->name."<br>";
    
    $row_lst = $this->children($debug-1);    // not $this->are($debug-1), because e.g. for "Company" the word "Company" itself should not be included in the list
    $col_lst = $col_wrd->children($debug-1);
    zu_debug('word_dsp->dsp_val_list -> columns '.$col_lst->name, $debug-10);

    $row_lst->wlsort($debug-1);
    $col_lst->wlsort($debug-1);
    
    // to do: use this for fast loading
    $val_matrix = $row_lst->val_matrix($col_lst, $this->usr->id, $debug-1);
    $result    .= $row_lst->dsp_val_matrix($val_matrix, $this->usr->id, $debug-1);
    
    zu_debug('word_dsp->dsp_val_list -> table', $debug-10);

    // display the words
    $row_nbr = 0;
    $result .= dsp_tbl_start();
    foreach ($row_lst->lst AS $row_phr) {
      // display the column headers
      // not needed any more if wrd lst is created based on word_display elements
      // to review
      $row_phr_dsp = New word_dsp;
      $row_phr_dsp->usr = $this->usr;
      $row_phr_dsp->id = $row_phr->id;
      $row_phr_dsp->load($debug-1);
      if ($row_nbr == 0) {
        $result .= '  <tr>'."\n";
        $result .= '    <th>'."\n";
        $result .= '    </th>'."\n";
        foreach ($col_lst->lst AS $col_lst_wrd) {
          zu_debug('word_dsp->dsp_val_list -> column '.$col_lst_wrd->name, $debug-10);
          $result .= $col_lst_wrd->dsp_tbl_head_right ($debug-1);
        }
        $result .= '  </tr>'."\n";
      }

      // display the rows
      zu_debug('word_dsp->dsp_val_list -> row', $debug-10);
      $result .= '  <tr>'."\n";
      $result .= '      '.$row_phr_dsp->dsp_tbl(0, $debug-1).''."\n";
      foreach ($col_lst->lst AS $col_lst_wrd) {
        $result .= '    <td>'."\n";
        $val_wrd_ids = array();
        $val_wrd_ids[] = $row_phr->id;
        $val_wrd_ids[] = $col_lst_wrd->id;
        asort($val_wrd_ids);
        $val_wrd_lst = New word_list;
        $val_wrd_lst->usr = $this->usr;
        $val_wrd_lst->ids = $val_wrd_ids;
        $val_wrd_lst->load($debug-1);
        zu_debug('word_dsp->dsp_val_list -> get group '.implode(",",$val_wrd_ids), $debug-10);
        $wrd_grp = $val_wrd_lst->get_grp($debug-1);
        if ($wrd_grp->id > 0) {
          zu_debug('word_dsp->dsp_val_list -> got group '.$wrd_grp->id, $debug-10);
          $in_value = $wrd_grp->result(0, $debug-1);
          $value = $in_value['num'];
          $fv_text = '';   
          // temp solution to be reviewed
          if ($in_value['id'] > 0) {
            $fv = New formula_value;
            $fv->id = $in_value['id'];
            $fv->usr = $this->usr;
            $fv->load($debug-1);          
            if ($fv->value <> 0) {
              $fv_text = $fv->val_formatted($debug-1);   
            } else {
              $fv_text = '';   
            }
          }
          if ($fv_text <> '') {
            //$back = $row_phr->id;
            if (!isset($back)) { $back = $this->id; }
            if ($in_value['usr'] > 0) {
              $result .= '      <p align="right"><a href="/http/formula_result.php?id='.$in_value['id'].'&phrase='.$row_phr->id.'&group='.$wrd_grp->id.'&back='.$back.'" class="user_specific">'.$fv_text.'</a></p>'."\n";
            } else {  
              $result .= '      <p align="right"><a href="/http/formula_result.php?id='.$in_value['id'].'&phrase='.$row_phr->id.'&group='.$wrd_grp->id.'&back='.$back.'">'.$fv_text.'</a></p>'."\n";
            }  
          }
        }
        $result .= '    </td>'."\n";
      }
      $result .= '  </tr>'."\n";
      $row_nbr++;
    }
    
    // display an add button to offer the user to add one row
    $result .= '<tr><td>'.$this->btn_add($back, $debug-1).'</td></tr>';
    
    $result .= dsp_tbl_end ();
    
    return $result;
  }

  // shows all words the link to the given word
  // returns the html code to select a word that can be edit
  // database link must be open
  // ??? identical to word_list ???
  function dsp_graph ($direction, $back, $debug) {
    zu_debug('word_dsp->dsp_graph of '.$this->dsp_id().' '.$direction.' for user '.$this->usr->name, $debug-10);
    $result  = '';

    // get the link types related to the word
    $vrb_lst = $this->link_types ($direction, $debug-1);
    
    // loop over the link types
    foreach ($vrb_lst->lst AS $vrb) {
      zu_debug('word_dsp->dsp_graph verb '.$vrb->name, $debug-14);

      // show the RDF graph for this verb
      $graph = New word_link_list;
      $graph->wrd = $this;
      $graph->vrb = $vrb;
      $graph->usr = $this->usr;
      $graph->direction = $direction;
      $graph->load($debug-1);
      $result .= $graph->display($back, $debug-1);

    }

    return $result;
  }

  // allow the user to unlick a word
  function dsp_unlink ($link_id, $debug) {
    zu_debug('word_dsp->dsp_unlink('.$link_id.')', $debug-10);
    $result  = '    <td>'."\n";
    $result .= btn_del ("unlink word", "/http/link_del.php?id=".$link_id."&back=".$this->id);
    $result .= '    </td>'."\n";

    return $result;
  }

  // to select a existing word to be added
  private function selector_add ($id, $form, $debug) {
    zu_debug('word_dsp->selector_add ... word id '.$id, $debug-10);
    $result = '';
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form;
    $sel->name       = 'add';  
    $sel->label      = "Word:";  
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst_usr("word", $this->usr, $debug-1);
    $sel->selected   = $id;
    $sel->dummy_text = '... or select an existing word to link it';
    $result .= $sel->display ($debug-1);

    return $result;
  }

  // returns the html code to select a word link type
  // database link must be open
  private function selector_type ($id, $form, $debug) {
    zu_debug('word_dsp->selector_type ... word id '.$id, $debug-10);
    $result = '';
    
    if ($id <= 0) {
      $id = DEFAULT_WORD_TYPE_ID;
    }

    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form;
    $sel->name       = 'type';  
    $sel->sql        = sql_lst("word_type", $debug-1);
    $sel->selected   = $id;
    $sel->dummy_text = '';
    $result .= $sel->display ($debug-1);

    return $result;
  }

  // returns the html code to select a word link type
  // database link must be open
  // todo: similar to verb->dsp_selector maybe combine???
  function selector_link ($id, $form, $back, $debug) {
    zu_debug('word_dsp->selector_link ... verb id '.$id, $debug-10);
    $result = '';
    
    $sql = "SELECT * FROM (
            SELECT verb_id AS id, 
                   IF (name_reverse <> '' AND name_reverse <> verb_name, CONCAT(verb_name, ' (', name_reverse, ')'), verb_name) AS name,
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form;
    $sel->name       = 'verb';  
    $sel->sql        = $sql;
    $sel->selected   = $id;
    $sel->dummy_text = '';
    $result .= $sel->display ($debug-1);

    if ($this->usr->is_admin ($debug-1)) {
      // admin users should always have the possibility to create a new link type
      $result .= btn_add ('add new link type', '/http/verb_add.php?back='.$back);
    }

    return $result;
  }

  // returns the html code to select a word
  // database link must be open
  function selector_word ($id, $pos, $form_name, $debug) {
    zu_debug('word_dsp->selector_word ... word id '.$id, $debug-10);
    $result = '';
    
    if ($pos > 0) {
      $field_id = "word".$pos;
    } else {
      $field_id = "word";
    }
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form_name;
    $sel->name       = $field_id;  
    $sel->sql        = sql_lst_usr("word", $this->usr, $debug-1);
    $sel->selected   = $id;
    $sel->dummy_text = '';
    $result .= $sel->display ($debug-1);
    
    zu_debug('word_dsp->selector_word ... done '.$id, $debug-10);
    return $result;
  }

  // 
  private function dsp_type_selector ($script, $class, $debug) {
    $result = '';
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->name       = 'type';  
    $sel->label      = "Word type:";  
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst("word_type", $debug-1);
    $sel->selected   = $this->type_id;
    $sel->dummy_text = '';
    $result .= $sel->display ($debug-1);
    return $result;
  }
  
  // HTML code to edit all word fields
  function dsp_add ($wrd_id, $wrd_to, $vrb_id, $back, $debug) {
    zu_debug('word_dsp->dsp_add '.$this->dsp_id().' (type '.$this->type_id.') or link the existing word with id '.$wrd_id.' to '.$wrd_to.' by verb '.$vrb_id.' for user '.$this->usr->name.' (called by '.$back.')', $debug-10);
    $result = '';
  
    $form = "word_add";
    $result .= dsp_text_h2('Add a new word');
    $result .= dsp_form_start($form);
    $result .= dsp_form_hidden ("back",    $back);
    $result .= dsp_form_hidden ("confirm", '1');
    $result .= '<div class="form-row">';
    $result .= dsp_form_text("word_name", $this->name, "Name:", "col-sm-4");
    $result .= $this->dsp_type_selector ($form, "col-sm-4", $debug-1);
    $result .= $this->selector_add ($wrd_id, $form, $debug-1).' ';
    $result .= '</div>';
    $result .= 'which ';
    $result .= '<div class="form-row">';
    $result .= $this->selector_link ($vrb_id, $form, $back, $debug-1);
    $result .= $this->selector_word ($wrd_to, 0, $form, $debug-1);
    $result .= '</div>';
    $result .= dsp_form_end('', $back);

    zu_debug('word_dsp->dsp_add ... done', $debug-10);
    return $result;
  }
  
  // HTML code to edit all word fields
  function dsp_edit ($back, $debug) {
    zu_debug('word_dsp->dsp_edit '.$this->dsp_id(), $debug-10);
    $result = '';
    
    if ($this->id > 0) {
      $form = "word_edit";
      $result .= dsp_text_h2('Change "'.$this->name.'"');
      $result .= dsp_form_start($form);
      $result .= dsp_form_hidden ("id",      $this->id);
      $result .= dsp_form_hidden ("back",    $back);
      $result .= dsp_form_hidden ("confirm", '1');
      $result .= '<div class="form-row">';
      if ($this->type_id == cl (SQL_WORD_TYPE_FORMULA_LINK)) {
        $result .= dsp_form_hidden ("name", $this->name);
        $result .= '  to change the name of "'.$this->name.'" rename the ';
        $frm = $this->formula($debug-1);
        $result .= $frm->name_linked($back, $debug-1);
        $result .= '.<br> ';
      } else {
        $result .= dsp_form_text("name", $this->name, "Name:", "col-sm-4");
      }
      $result .= dsp_form_text("plural", $this->plural, "Plural:", "col-sm-4");
      if ($this->type_id == cl (SQL_WORD_TYPE_FORMULA_LINK)) {
        $result .= ' type: '.$this->type_name;
      } else {
        $result .= $this->dsp_type_selector ('word_edit', "col-sm-4", $debug-1);
      }
      $result .= '</div>';
      $result .= '<br>';
      $result .= dsp_form_text("description", $this->description, "Description:");
      $result .= dsp_form_end('', $back);
      $result .= '<br>';
      $result .= $this->dsp_graph ("up",   $debug-1);
      $result .= $this->dsp_graph ("down", $debug-1);
    }

    // display the user changes 
    $changes = $this->dsp_hist(1, SQL_ROW_LIMIT, '', $back, $debug-1);
    if (trim($changes) <> "") {
      $result .= dsp_text_h3("Latest changes related to this word", "change_hist");
      $result .= $changes;
    }
    $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back, $debug-1);
    if (trim($changes) <> "") {
      $result .= dsp_text_h3("Latest link changes related to this word", "change_hist");
      $result .= $changes;
    }

    zu_debug('word_dsp->dsp_edit -> done', $debug-1);
    return $result;
  }

  // display the history of a word
  function dsp_hist_links($page, $size, $call, $back, $debug) {
    zu_debug("word_dsp->dsp_hist_links (".$this->id.",size".$size.",b".$size.")", $debug-10);
    $result = ''; // reset the html code var

    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'word';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links($debug-1);
    
    zu_debug('word_dsp->dsp_hist_links -> done', $debug-1);
    return $result;
  }
  
  
}

?>
