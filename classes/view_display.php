<?php

/*

  view_display.php - the extension of the view object to create html code
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
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view_dsp extends view {
 
  /*
  
  to display the header
  
  */

  // true if the view/view/page is used by the system and should only be changed by an administrator
  private function is_system ($debug) {
    $result = false;
    if ($this->code_id <> "") {
      $result = true;
    }
    return $result;
  }

  // the zukunft logo that should be show always
  private function top_logo() {
    $result  = '<table style="width:100%"><tr><td>';
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= '      <a href="/http/view.php"><img src="'.ZUH_IMG_LOGO.'" alt="zukunft.com" style="height: 5em;"></a>'; 
    $result .= '    </td>';
    return $result;
  }
  
  // the zukunft logo that should be show always
  private function top_logo_end() {
    $result  = '  </tr>';
    $result .= '</table>';
    return $result;
  }
  
  // same as top_right, but without the view change used for the view editors
  private function top_right_start($debug) {
    $result  = $this->top_logo();
    $result .= '<td align="right">';
    //$result  = '<div align="right">';
    return $result;
  }
  
  // same as top_right, but without the view change used for the view editors
  private function top_right_user($wrd, $debug) {
    $result = '';
    if ($_SESSION['logged']) { 
      zu_debug('view_dsp->top_right_user for user '.$_SESSION['user_name'].'.', $debug-12);
      $result .= '<a href="/http/user.php?id='.$_SESSION['usr_id'].'&back='.$wrd->id.'">'.$_SESSION['user_name'].'</a>';
    } else {  
      $url =  "//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
      $back_path = parse_url($url, PHP_URL_PATH);
      $parsed = parse_url($url);
      $query = $parsed['query'];
      parse_str($query, $params);
      unset($params['back']);
      $back = $back_path.'?'.http_build_query($params);
      //$back = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
      $result .= '<a href="/http/login.php?back='.$back.'">log in</a> or <a href="/http/signup.php">Create account</a>';
    }
    zu_debug('view_dsp->top_right_user done.', $debug-14);
    return $result;
  }

  private function top_right_logout() {
    if ($_SESSION['logged']) { 
      $result = ' <a href="/http/logout.php">log out</a>';
    } else {  
      $result = '';
    }
    return $result;
  }

  private function top_right_end() {
    $result = '</td>';
    return $result;
  }
    

  // show the standard top right corner, where the user can login or change the settings
  public function top_right($wrd, $debug) {
    $result = '';

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to display a view.", "view_dsp->top_right", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0) {  
      zu_err("The display ID (".$this->id.") must be set to display a view.", "view_dsp->top_right", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      if ($this->name == '') { 
        $this->load($debug-1);
      }
      $result .= $this->top_right_start($debug-1);
      if ($this->is_system() AND !$this->usr->is_admin($debug-1)) {
        $result .= btn_find ('find a word or formula', '/find.php').' - ';
        $result .= ''.$this->name.' ';
      } else {
        $result .= btn_find ('find a word or formula', '/http/find.php?word='.$wrd->id).' - ';
        $result .= 'view <a href="/http/view_select.php?id='.$this->id.'&word='.$wrd->id.'&back='.$wrd->id.'">'.$this->name.'</a> ';
        $result .= btn_edit ('adjust the view '.$this->name, '/http/view_edit.php?id='.$this->id.'&word='.$wrd->id.'&back='.$wrd->id).' ';
        $result .= btn_add  ('create a new view', '/http/view_add.php?word='.$wrd->id.'&back='.$wrd->id);
      }
      $result .= ' - ';
      zu_debug('view_dsp->top_right '.$this->name.' ('.$this->id.')', $debug-10);
      $result .= $this->top_right_user($wrd, $debug-1);
      $result .= ' ';
      $result .= $this->top_right_logout();
      $result .= $this->top_right_end();
      $result .= $this->top_logo_end();
    }
    zu_debug('view_dsp->top_right done.', $debug-14);
    return $result;
  }

  // same as top_right, but without the view change used for the view editors
  public function top_right_no_view($wrd, $debug) {
    $result = '';

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to display a view.", "view_dsp->top_right", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $result .= $this->top_right_start($debug-1);
      $result .= $this->top_right_user($wrd, $debug-1) ;
      $result .= $this->top_right_logout();
      $result .= $this->top_right_end();
      $result .= $this->top_logo_end();
    }
    return $result;
  }

  // the basic zukunft top elements that should be show always
  public function top() {
    $result  = $this->top_logo();
    $result .= $this->top_logo_end();
    return $result;
  }
  
  /*
  
  to display the view itself, so that the user can change it
  
  */

  // display the history of a view
  function dsp_hist($page, $size, $call, $back, $debug) {
    zu_debug("view_dsp->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist($debug-1);
    
    zu_debug("view_dsp->dsp_hist -> done", $debug-1);
    return $result;
  }

  // display the link history of a view
  function dsp_hist_links($page, $size, $call, $back, $debug) {
    zu_debug("view_dsp->dsp_hist_links for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".", $debug-10);
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links($debug-1);
    
    zu_debug("view_dsp->dsp_hist_links -> done", $debug-1);
    return $result;
  }

  // create the HTML code to edit a view
  public function edit($wrd, $debug) {
    $result = '';

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to display a view.", "view_dsp->top_right", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $result  = $this->top_right_user($wrd, $debug-1);
      $result .= $this->top_right_logout();
      $result .= $this->top_right_end();
      $result .= $this->top_logo_end();
    }
    return $result;
  }

  // lists of all view components which are used by this view
  private function linked_components($add_cmp, $wrd, $back, $debug) {
    $result = '';
    
    // show the view elements and allow the user to change them
    zu_debug('view_dsp->linked_components load.', $debug-1);
    $cmp_lst = $this->load_entries($debug-1);
    zu_debug('view_dsp->linked_components loaded.', $debug-1);
    $dsp_list = New dsp_list;
    $dsp_list->lst              = $cmp_lst;
    $dsp_list->id_field         = "view_entry_id";
    $dsp_list->script_name      = "view_edit.php";
    $dsp_list->script_parameter = $this->id."&back=".$back."&word=".$wrd->id;
    $result .= $dsp_list->display($back, $debug-1);
    zu_debug('view_dsp->linked_components displayed.', $debug-1);
    
    // check if the add button has been pressed and ask the user what to add
    if ($add_cmp > 0) {
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = 'view_edit';

      $result .= 'Name of the new display element: <input type="text" name="entry_name"> ';
      $sel->dummy_text = 'Select a type ...';
      $sel->name       = 'new_entry_type';  
      $sel->sql        = sql_lst ("view_entry_type", $debug-1);
      $sel->selected   = $this->type_id;  // ??? should this not be the default entry type
      $result .= $sel->display ($debug-1);
      $result .= '<br> ';
      $result .= ' ... or select an existing display element: ';
      $sel->dummy_text = 'Select a element ...';
      $sel->name       = 'add_view_entry';  
      $sel->sql        = sql_lst_usr ("view_entry", $this->usr, $debug-1);
      $sel->selected   = 0; // no default view component to add defined yet, maybe use the last???
      $result .= $sel->display ($debug-1);
      
      $result .= dsp_form_end();
    } else {  
      $result .= btn_add("add view component", "/http/view_edit.php?id=".$this->id."&word=".$wrd->id."&add_entry=1&back=".$back."");
    }
    
    return $result;
  }
  
  // display the type selector
  function dsp_type_selector ($script, $debug) {
    $result = '';
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->dummy_text = '';
    $sel->name       = 'type';  
    $sel->sql        = sql_lst("view_type", $debug-1); 
    $sel->selected   = $this->type_id;
    $result .= " type ".$sel->display ($debug-1);
    return $result;
  }
  
  // HTML code to edit all word fields
  function dsp_edit ($add_cmp, $wrd, $back, $debug) {
    $result = '';
    
    // the header for the add or edit form
    if ($this->id <= 0) {
      zu_debug('view_dsp->dsp_edit create a view.', $debug-10);
      $script = "view_add";
      $result .= dsp_text_h2 ('Create a new view (for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>)');
    } else {
      zu_debug('view_dsp->dsp_edit "'.$this->name.'" for user '.$this->usr->name.' (called from '.$back.').', $debug-10);
      $script = "view_edit";
      $result .= dsp_text_h2 ('Edit the view "'.$this->name.'" (used for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>)');
      $result .= btn_del ("delete the view", "/http/view_del.php?id=".$this->id."&back=".$back);
    }
    $result .= dsp_form_start($script);

    // use the default settings
    if ($this->type_id <= 0) {
      $this->type_id = cl(view_type_default);
    }

    $result .= dsp_form_id ($this->id);
    $result .= dsp_form_hidden ("word", $wrd->id);
    $result .= dsp_form_hidden ("back", $back);
    $result .= dsp_form_hidden ("confirm", '1');
    $result .= dsp_form_text   ("name", $this->name);
    $result .= $this->dsp_type_selector($script, $debug-1);
    $result .= '<br>';
    $result .= dsp_form_text_big ("comment", $this->comment);
    $result .= '<br>';
    if ($add_cmp <= 0) {
      $result .= dsp_form_end();
    }

    if ($this->id > 0) {

      // list all linked view components
      $result .= dsp_text_h3("Display elements");
      $result .= $this->linked_components($add_cmp, $wrd, $back, $debug-1);

      // display the user changes 
      $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $result .= dsp_text_h3("Latest changes of this view", "change_hist");
        $result .= $changes;
      }
      
      $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back, $debug-1);
      if (trim($changes) <> "") {
        $result .= dsp_text_h3("Latest link changes related to this view", "change_hist");
        $result .= $changes;
      }
      
      zu_debug('view_dsp->dsp_edit done.', $debug-1);
    }
    return $result;
  }  

}

?>
