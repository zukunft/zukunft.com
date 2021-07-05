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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view_dsp extends view {
 
  /*
  
  internal functions to display the navbar for the bootstrap and the pure HTML version
  
  */

  // true if the view/page is used by the system and should only be changed by an administrator
  private function is_system () {
    $result = false;
    if ($this->code_id <> "") {
      $result = true;
    }
    return $result;
  }

  // show the name of the used view and allow to change it  
  private function dsp_view_name($back) {
    $result = 'view <a href="/http/view_select.php?id='.$this->id.'&word='.$back.'&back='.$back.'">'.$this->name.'</a> ';
    return $result;
  }
    
  // either the user name or the link to create an account
  private function dsp_user($back) {
    $result = '';
    if ($_SESSION['logged']) { 
      log_debug('view_dsp->dsp_user for user '.$_SESSION['user_name']);
      log_debug('view_dsp->dsp_user for user '.$_SESSION['usr_id']);
      log_debug('view_dsp->dsp_user for user '.$back);
      $result .= '<a href="/http/user.php?id='.$_SESSION['usr_id'].'&back='.$back.'">'.$_SESSION['user_name'].'</a>';
      log_debug('view_dsp->dsp_user user done');
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
    log_debug('view_dsp->dsp_user done');
    return $result;
  }

  private function dsp_logout() {
    if ($_SESSION['logged']) { 
      $result = ' <a href="/http/logout.php">log out</a>';
    } else {  
      $result = '';
    }
    return $result;
  }

  /*
  
  pure HTML functions that do not need JavaScript
  
  */

  private function html_navbar_start() {
    $result  = dsp_tbl_start();
    $result  = '<tr><td>';
    $result .= '  <tr>';
    $result .= '    <td>';
    $result .= dsp_logo(); 
    $result .= '    </td>';
    return $result;
  }
  
  // the zukunft logo that should be show always
  private function html_navbar_end() {
    $result  = '  </tr>';
    $result .= dsp_tbl_end();
    return $result;
  }

  // show the standard top right corner, where the user can login or change the settings
  private function dsp_navbar_html($back) {
    $result = '';

    $result .= $this->html_navbar_start();
    $result .= '<td class="right_ref">';
    if ($this->is_system() AND !$this->usr->is_admin()) {
      $result .= btn_find ('find a word or formula', '/find.php').' - ';
      $result .= ''.$this->name.' ';
    } else {
      $result .= btn_find ('find a word or formula', '/http/find.php?word='.$back).' - ';
      $result .= $this->dsp_view_name($back);
      $result .= btn_edit ('adjust the view '.$this->name, '/http/view_edit.php?id='.$this->id.'&word='.$back.'&back='.$back).' ';
      $result .= btn_add  ('create a new view', '/http/view_add.php?word='.$back.'&back='.$back);
    }
    $result .= ' - ';
    log_debug('view_dsp->dsp_navbar '.$this->dsp_id().' ('.$this->id.')');
    $result .= $this->dsp_user($back);
    $result .= ' ';
    $result .= $this->dsp_logout();
    $result .= '</td>';
    $result .= $this->html_navbar_end();
    
    return $result;
  }

  // same as dsp_navbar, but without the view change used for the view editors
  public function dsp_navbar_html_no_view($back) {
    $result = '';

    $result .= $this->html_navbar_start();
    $result .= '<td class="right_ref">';
    $result .= $this->dsp_user($back) ;
    $result .= $this->dsp_logout();
    $result .= '</td>';
    $result .= $this->html_navbar_end();

    return $result;
  }

  /*
  
  java script functions using bootstrap
  
  */

  // same as dsp_navbar_html, but using bootstrap
  private function dsp_navbar_bs($show_view, $back) {
    $result  = '<nav class="navbar bg-light fixed-top">';
    $result .= dsp_logo();
    $result .= '  <form action="/http/find.php" class="form-inline my-2 my-lg-0">';
    $result .= '    <input name="pattern" class="form-control mr-sm-2" type="search" placeholder="word or formula">';
    $result .= '    <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Get numbers</button>';
    $result .= '  </form>';
    $result .= '  <div class="col-sm-2">';
    $result .= '    <ul class="nav navbar-nav">';
    $result .= '      <li class="active">';
    $result .=          $this->dsp_user($back);
    $result .= '      </li>';
    $result .= '      <li class="active">';
    $result .=          $this->dsp_logout();
    $result .= '      </li>';
    if ($show_view) {
      $result .= '      <li class="active">';
      $result .=          $this->dsp_view_name($back);
      $result .=          btn_edit ('adjust the view '.$this->name, '/http/view_edit.php?id='.$this->id.'&word='.$back.'&back='.$back).' ';
      $result .=          btn_add  ('create a new view', '/http/view_add.php?word='.$back.'&back='.$back);
      $result .= '      </li>';
    }
    $result .= '    </ul>';
    $result .= '  </div>';
    /*
    $result .= '  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $result .= '    <span class="navbar-toggler-icon"></span>';
    $result .= '  </button>';
    $result .= '  <div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $result .= '    <ul class="navbar-nav mr-auto">';
    // $result .= '      <li><a href="/http/find.php?word='.$back).'"><span class="glyphicon glyphicon-search"></span></a></li>';
    $result .= '      <li class="nav-item dropdown">';
    $result .= '        <a class="nav-link dropdown-toggle" ';
    $result .= '          href="/http/view_select.php?id='.$this->id.'&word='.$back.'&back='.$back.'"';
    $result .= '          id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $result .= '          '.$this->name.'';
    $result .= '        </a>';
    $result .= '        <div class="dropdown-menu" aria-labelledby="navbarDropdown">';
    $result .= '          <a class="dropdown-item" href="/http/view_edit.php?id='.$this->id.'&word='.$back.'&back='.$back.'">Edit</a>';
    $result .= '          <a class="dropdown-item" href="#">New</a>';
    $result .= '        </div>';
    $result .= '      </li>';
    $result .= '    </ul>';
    $result .= '  </div>';
    */
    $result .= '</nav>'; 
    // to avoid that the first data line is below the navbar
    $result .= '<br>'; 
    $result .= '<br>'; 
    $result .= '<br>'; 
    $result .= '<br>'; 
    $result .= '<br>'; 
    return $result;
  }
  
  /*
  
  public functions that switch between the bootstrap and the pure HTML version
  
  */

  // show the navigation bar, which allow the user to search, to login or change the settings
  // without javascript this is the top right corner
  // with    javascript this is a bar on the top
  public function dsp_navbar($back) {
    log_debug('view_dsp->dsp_navbar '.$back);

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      log_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
    } elseif ($this->id <= 0) {  
      log_err("The display ID (".$this->id.") must be set to display a view.", "view_dsp->dsp_navbar");
    } else {
      if ($this->name == '') { 
        $this->load();
      }
      if (UI_USE_BOOTSTRAP) {
        $result = $this->dsp_navbar_bs(TRUE, $back);
      } else {
        $result = $this->dsp_navbar_html($back);
      }
    }
    
    log_debug('view_dsp->dsp_navbar done');
    return $result;
  }
  
  // same as dsp_navbar, but without the view change used for the view editors
  public function dsp_navbar_no_view($back) {
    $result = '';

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      log_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
    } else {
      if (UI_USE_BOOTSTRAP) {
        $result .= $this->dsp_navbar_bs(FALSE, $back);
      } else {
        $result .= $this->dsp_navbar_html_no_view($back);
      }
    }
    return $result;
  }

  // the basic zukunft top elements that should be show always
  public function dsp_navbar_simple() {
    if (UI_USE_BOOTSTRAP) {
      $result  = $this->dsp_navbar_bs(FALSE, 0, 0);
    } else {
      $result  = $this->html_navbar_start();
      $result .= $this->html_navbar_end();
    }
    return $result;
  }
  
  /*
  
  to display the view itself, so that the user can change it
  
  */

  // display the history of a view
  function dsp_hist($page, $size, $call, $back) {
    log_debug("view_dsp->dsp_hist for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".");
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist();
    
    log_debug("view_dsp->dsp_hist -> done");
    return $result;
  }

  // display the link history of a view
  function dsp_hist_links($page, $size, $call, $back) {
    log_debug("view_dsp->dsp_hist_links for id ".$this->id." page ".$size.", size ".$size.", call ".$call.", back ".$back.".");
    $result = ''; // reset the html code var
    
    $log_dsp = New user_log_display;
    $log_dsp->id   = $this->id;
    $log_dsp->usr  = $this->usr;
    $log_dsp->type = 'view';
    $log_dsp->page = $page;
    $log_dsp->size = $size;
    $log_dsp->call = $call;
    $log_dsp->back = $back;
    $result .= $log_dsp->dsp_hist_links();
    
    log_debug("view_dsp->dsp_hist_links -> done");
    return $result;
  }

  /*
  // create the HTML code to edit a view
  public function edit($wrd) {
    $result = '';

    // check the all minimal input parameters are set
    if (!isset($this->usr)) {
      zu_err("The user id must be set to display a view.", "view_dsp->dsp_navbar");
    } else {
      $result  = $this->dsp_user($wrd);
      $result .= $this->dsp_logout();
      $result .= '</td>';
      $result .= $this->html_navbar_end();
    }
    return $result;
  }
*/
  // lists of all view components which are used by this view
  private function linked_components($add_cmp, $wrd, $back) {
    $result = '';
    
    if (UI_USE_BOOTSTRAP) { $result .= dsp_tbl_start_hist (); }    
    
    // show the view elements and allow the user to change them
    log_debug('view_dsp->linked_components load');
    $cmp_lst = $this->load_components();
    log_debug('view_dsp->linked_components loaded');
    $dsp_list = New dsp_list;
    $dsp_list->lst              = $cmp_lst;
    $dsp_list->id_field         = "view_component_id";
    $dsp_list->script_name      = "view_edit.php";
    $dsp_list->script_parameter = $this->id."&back=".$back."&word=".$wrd->id;
    $result .= $dsp_list->display($back);
    log_debug('view_dsp->linked_components displayed');
    if (UI_USE_BOOTSTRAP) { $result .= '<tr><td>'; }
    
    // check if the add button has been pressed and ask the user what to add
    if ($add_cmp > 0) {
      $result .= 'View component to add: ';
      $result .= btn_add("add view component", "/http/view_edit.php?id=".$this->id."&word=".$wrd->id."&add_entry=-1&back=".$back."");
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = 'view_edit';
      $sel->dummy_text = 'Select a view component ...';
      $sel->name       = 'add_view_component';  
      $sel->sql        = sql_lst_usr ("view_component", $this->usr);
      $sel->selected   = 0; // no default view component to add defined yet, maybe use the last???
      $result .= $sel->display ();
      
      $result .= dsp_form_end('', "/http/view_edit.php?id=".$this->id."&word=".$wrd->id."&back=".$back);
    } elseif ($add_cmp < 0) { 
      $result .= 'Name of the new display element: <input type="text" name="entry_name"> ';
      $sel = New selector;
      $sel->usr        = $this->usr;
      $sel->form       = 'view_edit';
      $sel->dummy_text = 'Select a type ...';
      $sel->name       = 'new_entry_type';  
      $sel->sql        = sql_lst ("view_component_type");
      $sel->selected   = $this->type_id;  // ??? should this not be the default entry type
      $result .= $sel->display ();
      $result .= dsp_form_end('', "/http/view_edit.php?id=".$this->id."&word=".$wrd->id."&back=".$back);
    } else { 
      $result .= btn_add("add view component", "/http/view_edit.php?id=".$this->id."&word=".$wrd->id."&add_entry=1&back=".$back."");
    }
    
    if (UI_USE_BOOTSTRAP) { $result .= '</td></tr>'; }
    if (UI_USE_BOOTSTRAP) { $result .= dsp_tbl_end (); }
    
    return $result;
  }
  
  // display the type selector
  private function dsp_type_selector ($script, $class, $attribute) {
    $result = '';
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $script;
    $sel->name       = 'type';  
    $sel->label      = "View type:";  
    $sel->bs_class   = $class;  
    $sel->attribute  = $attribute;  
    $sel->sql        = sql_lst("view_type"); 
    $sel->selected   = $this->type_id;
    $sel->dummy_text = '';
    $result .= $sel->display ();
    return $result;
  }
  
  // HTML code to edit all word fields
  function dsp_edit ($add_cmp, $wrd, $back) {
    $result = '';
    
    // use the default settings if needed
    if ($this->type_id <= 0) { $this->type_id = cl(view_type_default); } 
    
    // the header to add or change a view
    if ($this->id <= 0) {
      log_debug('view_dsp->dsp_edit create a view');
      $script = "view_add";
      $result .= dsp_text_h2 ('Create a new view (for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>)');
    } else {
      log_debug('view_dsp->dsp_edit '.$this->dsp_id().' for user '.$this->usr->name.' (called from '.$back.')');
      $script = "view_edit";
      $result .= dsp_text_h2 ('Edit view "'.$this->name.'" (used for <a href="/http/view.php?words='.$wrd->id.'">'.$wrd->name.'</a>)');
    }    
    $result .= '<div class="row">';

    // when changing a view show the fields only on the left side
    if ($this->id > 0) {
      $result .= '<div class="col-sm-7">';
    }  

    // show the edit fields
    $result .= dsp_form_start($script);
    $result .= dsp_form_id ($this->id);
    $result .= dsp_form_hidden ("word", $wrd->id);
    $result .= dsp_form_hidden ("back", $back);
    $result .= dsp_form_hidden ("confirm", '1');
    $result .= '<div class="form-row">';
    if ($add_cmp < 0 OR $add_cmp > 0) {
      // show the fields inactive, because the assign fields are active
      $result .= dsp_form_text   ("name", $this->name, "Name:", "col-sm-8", "disabled");
      $result .= $this->dsp_type_selector($script, "col-sm-4", "disabled");
      $result .= '</div>';
      $result .= dsp_form_text_big ("comment", $this->comment, "Comment:", "", "disabled");
    } else {
      // show the fields inactive, because the assign fields are active
      $result .= dsp_form_text   ("name", $this->name, "Name:", "col-sm-8");
      $result .= $this->dsp_type_selector($script, "col-sm-4", "");
      $result .= '</div>';
      $result .= dsp_form_text_big ("comment", $this->comment, "Comment:");
      $result .= dsp_form_end('', $back, "/http/view_del.php?id=".$this->id."&back=".$back);
    }

    // in edit mode show the assigned words and the hist on the right
    if ($this->id > 0) {
      $result .= '</div>';
      
      $comp_html = $this->linked_components($add_cmp, $wrd, $back);
      
      // collect the history
      $changes = $this->dsp_hist(0, SQL_ROW_LIMIT, '', $back);
      if (trim($changes) <> "") {
        $hist_html = $changes;
      } else {
        $hist_html = 'Nothing changed yet.';
      }
      $changes = $this->dsp_hist_links(0, SQL_ROW_LIMIT, '', $back);
      if (trim($changes) <> "") {
        $link_html = $changes;
      } else {
        $link_html = 'No component have been added or removed yet.';
      }
      
      // display the tab box with the links and changes
      $result .= dsp_link_hist_box ('Components',        $comp_html,
                                    '',                 '',
                                    'Changes',          $hist_html,
                                    'Component changes', $link_html);
      
      log_debug('view_dsp->dsp_edit done');
    }
    
    $result .= '</div>';   // of row
    $result .= '<br><br>'; // this a usually a small for, so the footer can be moved away
    
    return $result;
  }  

}

?>
