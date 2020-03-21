<?php

/*

  view_component_display.php - to display a single display component like a headline or a table
  --------------------------
  
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

class view_component_dsp extends view_component {

    
  // just to display a simple text
  function text($debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_COMPONENT_TEXT)) {
      zu_debug('view_component_dsp->text ('.$this->dsp_id().')', $debug-10);
      $result .= " " . $this->name;
    }
    return $result;
  }

  // show the word name and give the user the possibility to change the word name
  function word_name ($wrd, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_WORD_NAME)) {
      if (!isset($wrd)) {
        $result .= zu_err('No word selected for "'.$this->name.'".', "view_component_dsp->word_name", '', (new Exception)->getTraceAsString(), $this->usr);  
      } else {
        zu_debug('view_component_dsp->word_name in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
        $wrd_dsp = New word_dsp;
        $wrd_dsp->id  = $wrd->id;
        $wrd_dsp->usr = $wrd->usr;
        $result .= $wrd_dsp->dsp_header ($debug-1);
      }
    }
    
    return $result;
  }

  // display a table with the values of the first word, that are also linked to the second word (e.g. ABB as first word, Cash Flow Statment as second word)
  // $wrd is the word that the user has selected to see e.g. "Company" to see a list of the main companies
  // $this->word_id_col is the related word defined on the view component e.g. "Company main ratio" to see a "word value list" with all word related to "Company main ratio"

  // view type table with parameters:
  // row start words (build a tree )
  // col word (if time word set newest value to the right

  function table($phr, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_VALUES_RELATED)) {
      zu_debug('view_component_dsp->table of view component '.$this->dsp_id().' for "'.$phr->name.'" with columns "'.$this->wrd_row->name.'" and user "'.$this->usr->name.'".', $debug-10);
      $val_lst = New value_list_dsp;
      $val_lst->phr = $phr;
      $val_lst->usr = $this->usr;
      $result .= $val_lst->dsp_table ($this->wrd_row, $phr->id, $debug-1);
    }
    return $result;
  }

  // show a list of words and some values related to the words e.g. all companies with the main ratios
  function num_list($wrd, $back, $debug) {
    $result = '';

    if ($this->type_id == cl(SQL_VIEW_TYPE_WORD_VALUE)) {
      zu_debug('view_component_dsp->num_list in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);

      // check the parameters
      if (get_class($wrd) <> 'word_dsp') {
        $result .= zu_warning('The word parameter has type '.get_class($wrd).', but should be word_dsp.', "view_component_dsp->num_list", '', (new Exception)->getTraceAsString(), $this->usr);  
        $wrd_dsp = New word_dsp;
        $wrd_dsp->id = $wrd->id;
        $wrd_dsp->usr = $this->usr;
        $wrd_dsp->load($debug-1);
        $wrd = $wrd_dsp;
      }
    
      $this->load_phrases($debug-1); // to make sure that the column word object is loaded
      if (isset($this->wrd_col)) {
        $result .= $wrd->dsp_val_list ($this->wrd_col, $back, $debug-1);
      } else {
        $result .= zu_err('Column definition is missing for '.$this->dsp_id().'.', "view_component_dsp->num_list", '', (new Exception)->getTraceAsString(), $this->usr);  
      }
    }
    return $result;
  }

  private function formula_list($wrd, $debug) {
    $frm_lst = New formula_list;
    $frm_lst->wrd  = $wrd;
    $frm_lst->usr  = $this->usr;
    $frm_lst->back = $wrd->id;
    $frm_lst->load($debug-1);
    return $frm_lst;
  }
  
  // display all formulas related to the given word
  function formulas($wrd, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_FORMULAS)) {
      zu_debug('view_component_dsp->formulas in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= dsp_text_h2('Formulas');

      $frm_lst = $this->formula_list($wrd, $debug-1);
      $result .= $frm_lst->display($back, $debug-1);
      
      $parent_word_lst = $wrd->parents($debug-1);
      foreach ($parent_word_lst->lst AS $parent_wrd) {
        zu_debug('view_component_dsp->formulas -> parent ('.$parent_wrd->name.')', $debug-10);
        $result .= dsp_text_h3('Formulas inherented by '.$parent_wrd->name);

        $frm_lst = $this->formula_list($parent_wrd, $debug-1);
        $result .= $frm_lst->display($back, $debug-1);
        // adding formulas direct to a parent word may not be intuitive
        //$result .= btn_add ('Add formuls', "/http/formula_add.php?word=".$parent_id."");
      }  
      $result .= btn_add ('Add formula', "/http/formula_add.php?word=".$wrd->id."&back=".$wrd->id."");
      $result .= '<br>';
    }
    return $result;
  }

  // show a list of formula results related to a word
  function formula_values($wrd, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_FORMULA_RESULTS)) {
      zu_debug('view_component_dsp->formula_values in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= "<br><br>calculated values<br>";
      $frm_val_lst = New formula_value_list;
      $frm_val_lst->phr_id = $wrd->id;
      $frm_val_lst->usr = $this->usr;
      $frm_val_lst->load (SQL_ROW_LIMIT, $debug-1);
      $result .= $frm_val_lst->display($back, $debug-1) ;
    }
    return $result;
  }

  // show all words that are based on the given start word
  // and related to the main word
  // later the start word should be selected automatically based on what most users has clicked on
  function word_childs($wrd, $debug) {
    $result = '';

    if ($this->type_id == cl(SQL_VIEW_TYPE_WORDS_DOWN)) {
      zu_debug('view_component_dsp->word_childs in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= $wrd->dsp_graph ("down", $debug-1);
    }
    
    return $result;
  }

  // show all word that this words is based on
  function word_parents($wrd, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_WORDS_DOWN)) {
      zu_debug('view_component_dsp->word_parents in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= $wrd->dsp_graph ("up",   $debug-1);
    }
    return $result;
  }

  // configure the json export
  function json_export($wrd, $back, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_JSON_EXPORT)) {
      zu_debug('view_component_dsp->json_export in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= '<br>';
      $result .= $wrd->config_json_export ($back, $debug-1);
      $result .= '<br>';
    }
    return $result;
  }

  // configure the xml export
  function xml_export($wrd, $back, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_XML_EXPORT)) {
      zu_debug('view_component_dsp->xml_export in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= '<br>';
      $result .= $wrd->config_xml_export ($back, $debug-1);
      $result .= '<br>';
    }
    return $result;
  }

  // configure the csv export
  function csv_export($wrd, $back, $debug) {
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_CSV_EXPORT)) {
      zu_debug('view_component_dsp->csv_export in view '.$this->dsp_id().' for word '.$wrd->name.' and user '.$this->usr->name, $debug-10);
      $result .= '<br>';
      $result .= $wrd->config_csv_export ($back, $debug-1);
      $result .= '<br>';
    }
    return $result;
  }

  // shows all: all words that link to the given word and all values related to the given word
  function all($phr, $back, $debug) {
    zu_debug('view_component_dsp->all for word '.$phr->name, $debug-10);
    $result = '';
    if ($this->type_id == cl(SQL_VIEW_TYPE_VALUES_ALL)) {
      zu_debug('view_component_dsp->all in view '.$this->dsp_id().' for word '.$phr->name.' and user '.$this->usr->name, $debug-10);
      $result .= '<br>';
      $phrases_down .= $phr->dsp_graph ("down", $debug-1);
      $phrases_up   .= $phr->dsp_graph ("up",   $debug-1);
      if ($phrases_down <> '' OR $phrases_up <> '') {
        $result .= $phrases_down.$phrases_up;
      } else {
        $result .= "The type of ".$phr->name." is not jet defined. Please define what it is: ";
        $type_is = cl(SQL_LINK_TYPE_IS);
        $result .= btn_add ("Please link ".$phr->name." to an existing word to include it in the lists", '/http/link_add.php?from='.$phr->id.'&verb='.$type_is.'&back='.$phr->id);
      }
      $result .= '<br><br>values<br>';
      $val_lst = New value_list;
      $val_lst->phr = $phr;
      $val_lst->usr = $this->usr;
      zu_debug('view_component_dsp->all load values for word "'.$phr->name.'" and user "'.$this->usr->name.'".', $debug-10);
      $val_lst->load_by_phr($debug-1);
      $result .= $val_lst->html($back, $debug-1);
    }
    return $result;
  }

  /*
  
  to display the view component itself, so that the user can change it
  
  */

  
  // allow the user to unlick a view
  function btn_unlink ($view_id, $wrd, $back, $debug) {
    zu_debug('view_component_dsp->btn_unlink(me'.$this->id.',m'.$view_id.',t'.$wrd->id.')', $debug-10);
    $result  = '    <td>'."\n";
    $result .= btn_del ("unlink view", "/http/view_component_edit.php?id=".$this->id."&unlink_view=".$view_id."&word=".$wrd->id."&back=".$back);
    $result .= '    </td>'."\n";
    return $result;
  }

  // lists of all views where a view component is used
  private function linked_views($add_link, $wrd, $back, $debug) {
    zu_debug("view_component_dsp->linked_view componet id ".$this->id." and user ".$this->usr->id." (word ".$wrd->id.", add ".$add_link.").", $debug-10);
    $result = '';

    if (UI_USE_BOOTSTRAP) { $result .= dsp_tbl_start_hist (); } else { $result .= dsp_tbl_start_half(); }
    
    $sql = "SELECT m.view_id, m.view_name 
              FROM view_component_links l, views m 
             WHERE l.view_component_id = ".$this->id." 
               AND l.view_id = m.view_id;";
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $view_lst = $db_con->get($sql, $debug-5);  
    foreach ($view_lst AS $view) {
      $result .= '  <tr>'."\n";
      $result .= '    <td>'."\n";
      $dsp = new view_dsp;
      $dsp->id = $view['view_id'];
      $dsp->name = $view['view_name'];
      $result .= '      '.$dsp->name_linked ($wrd, $back, $debug-1).''."\n";
      $result .= '    </td>'."\n";
      $result .= $this->btn_unlink ($view['view_id'], $wrd, $back, $debug-1);
      $result .= '  </tr>'."\n";
    }

    // give the user the possibility to add a view
    $result .= '  <tr>';
    $result .= '    <td>';
    if ($add_link == 1) {
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = 'view_component_edit';
      $sel->name       = 'link_view';  
      $sel->sql        = sql_lst_usr("view", $this->usr, $debug-1);
      $sel->selected   = 0;
      $sel->dummy_text = 'select a view where the view component should also be used';
      $result .= $sel->display ($debug-1);
      
      $result .= dsp_form_end();
    } else {
      $result .= '      '.btn_add ('add new', '/http/view_component_edit.php?id='.$this->id.'&add_link=1&word='.$wrd->id.'&back='.$back);
    }
    $result .= '    </td>';
    $result .= '  </tr>';

    $result .= dsp_tbl_end ();
    $result .= '  <br>';
      
    return $result;
  }
    
  // display the component type selector
  private function dsp_type_selector($script, $class, $debug) {
    $result = ''; 
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->dummy_text = 'not set';
    $sel->name       = 'type';  
    $sel->label      = "Type:";  
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst("view_component_type", $debug-1); 
    $sel->selected   = $this->type_id;
    $result .= $sel->display ($debug-1).' ';
    return $result;
  }

  // display the component word_row selector
  private function dsp_word_row_selector($script, $class, $debug) {
    $result = ''; 
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->dummy_text = 'not set';
    $sel->name       = 'word_row';  
    if (isset($this->wrd_row)) {
      $sel->label    = "Rows taken from ".$this->wrd_row->dsp_link().":";  
    } else {
      $sel->label    = "Take rows from:";  
    }  
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst_usr("word", $this->usr, $debug-1);
    $sel->selected   = $this->word_id_row;
    $result .= $sel->display ($debug-1).' ';
    return $result;
  }

  // display the component word_col selector
  private function dsp_word_col_selector($script, $class, $debug) {
    $result = ''; 
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->dummy_text = 'not set';
    $sel->name       = 'word_col';  
    if (isset($this->wrd_col)) {
      $sel->label    = "Columns taken from ".$this->wrd_col->dsp_link().":";  
    } else {
      $sel->label    = "Take columns from:";  
    }
    $sel->bs_class   = $class;  
    $sel->sql        = sql_lst_usr("word", $this->usr, $debug-1);
    $sel->selected   = $this->word_id_col;
    $result .= $sel->display ($debug-1).' ';
    return $result;
  }

  // display the history of a view component
  function dsp_hist($page, $size, $call, $back, $debug) {
    zu_debug("view_component_dsp->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view_component';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);
    
    zu_debug("view_component_dsp->dsp_hist -> done", $debug-1);
    return $result;
  }

  // display the link history of a view component
  function dsp_hist_links($page, $size, $call, $back, $debug) {
    zu_debug("view_component_dsp->dsp_hist_links for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view_component';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links($debug-1);
    
    zu_debug("view_component_dsp->dsp_hist_links -> done", $debug-1);
    return $result;
  }

  // HTML code to edit all word fields
  function dsp_edit ($add_link, $wrd, $back, $debug) {
    zu_debug('view_component_dsp->dsp_edit '.$this->dsp_id().' for user '.$this->usr->name.' (called from '.$back.').', $debug-10);
    $result = '';
    
    // show the view component name
    if ($this->id <= 0) {
      $script = "view_component_add";
      $result .= dsp_text_h2 ('Create a view element for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>');
    } else {  
      $script = "view_component_edit";
      $result .= dsp_text_h2 ('Edit the view element "'.$this->name.'" (used for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>) ');
    }
    $result .= '<div class="row">';

    // when changing a view component show the fields only on the left side
    if ($this->id > 0) {
      $result .= '<div class="col-sm-7">';
    }  

    $result .= dsp_form_start($script);
    if ($this->id > 0) { $result .= dsp_form_id ($this->id); }
    $result .= dsp_form_hidden ("word", $wrd->id);
    $result .= dsp_form_hidden ("back", $back);
    $result .= dsp_form_hidden ("confirm", 1);
    $result .= '<div class="form-row">';
    $result .= dsp_form_fld ("name", $this->name, "Component name:", "col-sm-8");
    $result .= $this->dsp_type_selector($script, "col-sm-4", $debug); // allow to change the type
    $result .= '</div>';
    $result .= '<div class="form-row">';
    $result .= $this->dsp_word_row_selector($script, "col-sm-6", $debug); // allow to change the word_row word
    $result .= $this->dsp_word_col_selector($script, "col-sm-6", $debug); // allow to change the word col word
    $result .= '</div>';
    $result .= dsp_form_fld ("comment", $this->description, "Comment:");
    if ($add_link <= 0) {
      if ($this->id > 0) {
        $result .= dsp_form_end('', $back, "/http/view_component_del.php?id=".$this->id."&back=".$back);
      } else {
        $result .= dsp_form_end('', $back);
      }
    }  

    if ($this->id > 0) {
      $result .= '</div>';

      $view_html = $this->linked_views($add_link, $wrd, $back, $debug-1);
      $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $hist_html = $changes;
      } else {
        $hist_html = 'Nothing changed yet.';
      }
      $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $link_html = $changes;
      } else {
        $link_html = 'No component have been added or removed yet.';
      }
      $result .= dsp_link_hist_box ('Views',        $view_html,
                                    '',             '',
                                    'Changes',      $hist_html,
                                    'Link changes', $link_html, $debug-1);
    }
    
    $result .= '</div>';   // of row
    $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away
    
    return $result;
  }  

}

?>
