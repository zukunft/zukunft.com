<?php

/*

  view.php - the main display object
  --------
  
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

class view {

  // database fields
  public $id          = NULL; // the database id of the view, which is the same for the standard and the user specific view
  public $usr         = NULL; // the person who wants to see something
  public $owner_id    = NULL; // the user id of the person who created the view, which is the default view
  public $name        = '';   // simply the view name, which cannot be empty
  public $comment     = '';   // the view description that is shown as a mouseover explain to the user
  public $type_id     = NULL; // the id of the view type
  public $code_id     = '';   // to select internal predefined views
  public $excluded    = NULL; // for this object the excluded field is handled as a normal user sandbox field, but for the list excluded row are like deleted
  
  // in memory only fields
  public $type_name    = '';   // 
  public $entry_lst   = NULL;   // array of the view component objects
  public $back        = NULL;   // the calling stack
  
  // load the view parameters for all users
  private function load_standard($debug) {
    $result = '';
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "m.view_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "m.view_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= zu_err("ID missing to load the standard view.", "view->load_standard", '', (new Exception)->getTraceAsString(), $this->usr);
    } else{  
      $sql = "SELECT m.view_id,
                     m.user_id,
                     m.view_name,
                     m.comment,
                     m.view_type_id,
                     m.excluded
                FROM views m 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_dsp = $db_con->get1($sql, $debug-5);  
      if ($db_dsp['view_id'] > 0) {
        $this->id           = $db_dsp['view_id'];
        $this->owner_id     = $db_dsp['user_id'];
        $this->name         = $db_dsp['view_name'];
        $this->comment      = $db_dsp['comment'];
        $this->type_id      = $db_dsp['view_type_id'];
        $this->excluded     = $db_dsp['excluded'];

        // to review: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
    return $result;
  }
  
  // load the missing view parameters from the database
  function load($debug) {

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a view.", "view->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0 AND $this->code_id == '' AND $this->name == '') {  
      zu_err("Either the database ID (".$this->id."), the name (".$this->name.") or the code_id (".$this->code_id.") and the user (".$this->usr->id.") must be set to load a view.", "view->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = "m.view_id = ".$this->id;
      } elseif ($this->code_id <> '' AND !is_null($this->usr->id)) {
        $sql_where = "m.code_id = ".sf($this->code_id);
      } elseif ($this->name <> '' AND !is_null($this->usr->id)) {
        $sql_where = "m.view_name = ".sf($this->name);
      }

      if ($sql_where == '') {
        zu_err("Internal error on the where clause.", "view->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else{  
        $sql = "SELECT m.view_id,
                       u.view_id AS user_view_id,
                       m.user_id,
                       IF(u.view_name IS NULL,    m.view_name,    u.view_name)    AS view_name,
                       IF(u.comment IS NULL,      m.comment,      u.comment)      AS comment,
                       IF(u.view_type_id IS NULL, m.view_type_id, u.view_type_id) AS view_type_id,
                       IF(u.excluded IS NULL,     m.excluded,     u.excluded)     AS excluded
                  FROM views m 
             LEFT JOIN user_views u ON u.view_id = m.view_id 
                                   AND u.user_id = ".$this->usr->id." 
                 WHERE ".$sql_where.";";
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_view = $db_con->get1($sql, $debug-5);  
        if ($db_view['view_id'] > 0) {
          $this->id         = $db_view['view_id'];
          $this->usr_cfg_id = $db_view['user_view_id'];
          $this->owner_id   = $db_view['user_id'];
          $this->name       = $db_view['view_name'];
          $this->comment    = $db_view['comment'];
          $this->type_id    = $db_view['view_type_id'];
          $this->excluded   = $db_view['excluded'];
        } 
        zu_debug('view->load '.$this->dsp_id().'.', $debug-10);
      }  
    }  
  }
    
  // load all parts of this view for this user
  function load_entries($debug) {
    zu_debug('view->load_entries for "'.$this->name.'".', $debug-10);  

    $sql = " SELECT e.view_entry_id, 
                    u.view_entry_id AS user_entry_id,
                    e.user_id, 
                    IF(u.view_entry_name IS NULL,    e.view_entry_name,    u.view_entry_name)    AS view_entry_name,
                    IF(u.view_entry_type_id IS NULL, e.view_entry_type_id, u.view_entry_type_id) AS view_entry_type_id,
                    IF(c.code_id IS NULL,            t.code_id,            c.code_id)            AS code_id,
                    IF(u.word_id_row IS NULL,        e.word_id_row,        u.word_id_row)        AS word_id_row,
                    IF(u.link_type_id IS NULL,       e.link_type_id,       u.link_type_id)       AS link_type_id,
                    IF(u.formula_id IS NULL,         e.formula_id,         u.formula_id)         AS formula_id,
                    IF(u.word_id_col IS NULL,        e.word_id_col,        u.word_id_col)        AS word_id_col,
                    IF(u.word_id_col2 IS NULL,       e.word_id_col2,       u.word_id_col2)       AS word_id_col2,
                    IF(y.excluded IS NULL,           l.excluded,           y.excluded)           AS link_excluded,
                    IF(u.excluded IS NULL,           e.excluded,           u.excluded)           AS excluded
               FROM view_entry_links l            
          LEFT JOIN user_view_entry_links y ON y.view_entry_link_id = l.view_entry_link_id 
                                           AND y.user_id = ".$this->usr->id.", 
                    view_entries e             
          LEFT JOIN user_view_entries u ON u.view_entry_id = e.view_entry_id 
                                       AND u.user_id = ".$this->usr->id." 
          LEFT JOIN view_entry_types t ON e.view_entry_type_id = t.view_entry_type_id
          LEFT JOIN view_entry_types c ON u.view_entry_type_id = c.view_entry_type_id
              WHERE l.view_id = ".$this->id." 
                AND l.view_entry_id = e.view_entry_id 
           ORDER BY l.order_nbr;";
    zu_debug("view->load_entries ... ".$sql, $debug-12);
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($sql, $debug-8);  
    $this->entry_lst = array();
    foreach ($db_lst AS $db_entry) {
      // this is only for the view of the active user, so a direct exclude can be done
      if ((is_null($db_entry['excluded'])      OR $db_entry['excluded'] == 0)
      AND (is_null($db_entry['link_excluded']) OR $db_entry['link_excluded'] == 0)) {
        $new_entry = new view_component_dsp;
        $new_entry->id            = $db_entry['view_entry_id'];
        $new_entry->usr           = $this->usr;
        $new_entry->owner_id      = $db_entry['user_id'];
        $new_entry->name          = $db_entry['view_entry_name'];
        $new_entry->word_id_row   = $db_entry['word_id_row'];
        $new_entry->link_type_id  = $db_entry['link_type_id'];
        $new_entry->type_id       = $db_entry['view_entry_type_id'];
        $new_entry->formula_id    = $db_entry['formula_id'];
        $new_entry->word_id_col   = $db_entry['word_id_col'];
        $new_entry->word_id_col2  = $db_entry['word_id_col2'];
        $new_entry->code_id       = $db_entry['code_id'];
        $new_entry->load_phrases($debug-1);
        $this->entry_lst[]        = $new_entry;
      }
    }
    zu_debug('view->load_entries '.count($this->entry_lst).' loaded for "'.$this->name.'".', $debug-8);

    return $this->entry_lst;
  }

  // return the beginning html code for the view_type; 
  // the view type defines something like the basic setup of a view
  // e.g. the catch view does not have the header, whereas all other views have
  private function dsp_type_open($debug) {
    zu_debug('view->dsp_type_open ('.$this->type_id.')', $debug-10);
    $result = '';
    // move to database !!
    // but avoid security leaks
    // maybe use a view component for that
    if ($this->type_id == 1) {
      $result .= '<h1>';
    }
    return $result;
  }

  private function dsp_type_close($debug) {
    zu_debug('view->dsp_type_close ('.$this->type_id.')', $debug-10);
    $result = '';
    // move to a view component function
    // for the word array build an object
    if ($this->type_id == 1) {
      $result = $result . '<br><br>';
      //$result = $result . '<a href="/http/view.php?words='.implode (",", $word_array).'&type=3">Really?</a>';
      $result = $result . '</h1>';
    }
    return $result;
  }

  // 
  private function type_name($debug) {
    if ($this->type_id > 0) {
      $sql = "SELECT type_name, description
                FROM view_types
               WHERE view_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    return $this->type_name;    
  }
  
  // return the html code of all view components
  private function dsp_entries($wrd, $back, $debug) {
    zu_debug('view->dsp_entries "'.$wrd->name.'" with the view "'.$this->name.'" for user "'.$this->usr->name.'".', $debug-10);

    $result = '';
    $word_array = array();
    $this->load_entries($debug-1);
    foreach ($this->entry_lst AS $entry) {
      zu_debug('view->dsp_entries ... "'.$entry->name.'" type "'.$entry->type_id.'"', $debug-6);
      
      // list of all possible view components
      $result .= $entry->text            ($debug-1);        // just to display a simple text
      $result .= $entry->word_name       ($wrd, $debug-1); // show the word name and give the user the possibility to change the word name
      $result .= $entry->table           ($wrd, $debug-1); // display a table (e.g. ABB as first word, Cash Flow Statment as second word)
      $result .= $entry->num_list        ($wrd, $back, $debug-1); // a word list with some key numbers e.g. all companies with the PE ratio
      $result .= $entry->formulas        ($wrd, $debug-1); // display all formulas related to the given word
      $result .= $entry->formula_values  ($wrd, $debug-1); // show a list of formula results related to a word
      $result .= $entry->word_childs     ($wrd, $debug-1); // show all words that are based on the given start word
      $result .= $entry->word_parents    ($wrd, $debug-1); // show all word that this words is based on
      $result .= $entry->xml_export      ($wrd, $back, $debug-1); // offer to configure and create an XML file
      $result .= $entry->csv_export      ($wrd, $back, $debug-1); // offer to configure and create an CSV file
      $result .= $entry->all             ($wrd, $back, $debug-1); // shows all: all words that link to the given word and all values related to the given word
    }

    zu_debug('view->dsp_entries ... done', $debug-10);
    return $result;
  }

  // return the html code to display a view name with the link
  function name_linked ($wrd, $back, $debug) {
    $result = '';
  
    $result .= '<a href="/http/view_edit.php?id='.$this->id;
    if (isset($wrd)) {
      $result .= '&word='.$wrd->id;
    }
    $result .= '&back='.$back.'">'.$this->name.'</a>';
    
    return $result;    
  }

  // returns the hmtl code for a view: this is the main function of this lib 
  // view_id is used to force the dislay to a set form; e.g. display the sectors of a company instead of the balance sheet
  // view_type_id is used to .... remove???
  // word_id - id of the starting word to display; can be a single word, a comma seperated list of word ids, a word group or a word tripple
  function display ($wrd, $back, $debug) {
    zu_debug('view->display "'.$wrd->name.'" with the view "'.$this->name.'" (type '.$this->type_id.')  for user "'.$this->usr->name.'".', $debug-10);
    $result = '';
    
    // check and correct the parameters
    if ($back == '') {
      $back = $wrd->id;
    }

    if ($this->id <= 0) {
      zu_err("The view id must be loaded to display it.", "view->display", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      // display always the view name in the top right corner and allow the user to edit the view
      $result .= $this->dsp_type_open($debug-1);
      $result .= $this->top_right($wrd, $debug-1);
      $result .= $this->dsp_entries($wrd, $back, $debug-1);
      $result .= $this->dsp_type_close($debug-1);
    }
    zu_debug('view->display ... done.', $debug-1);
    
    return $result;
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id ($debug) {
    $result = ''; 

    if ($this->name <> '') {
      $result .= $this->name.' '; 
      if ($this->id > 0) {
        $result .= '('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->name;
    }
    return $result;
  }

  // move one view component one place up
  // in case of an error the error message is returned
  // if everything is fine an empty string is returned
  function entry_up ($view_entry_id, $debug) {
    $result = '';
    // check the all minimal input parameters
    if ($view_entry_id <= 0) {
      zu_err("The view component id must be given to move it.", "view->entry_up", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $cmp = new view_component_dsp;
      $cmp->id  = $view_entry_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1);
      $cmp_lnk = new view_component_link;
      $cmp_lnk->dsp = $this;
      $cmp_lnk->cmp = $cmp;
      $cmp_lnk->usr = $this->usr;
      $cmp_lnk->load($debug-1);
      $result .= $cmp_lnk->move_up($debug-1);
    }
    return $result;
  }
  
  // move one view component one place down
  function entry_down ($view_entry_id, $debug) {
    $result = '';
    // check the all minimal input parameters
    if ($view_entry_id <= 0) {
      zu_err("The view component id must be given to move it.", "view->entry_down", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $cmp = new view_component_dsp;
      $cmp->id  = $view_entry_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1);
      $cmp_lnk = new view_component_link;
      $cmp_lnk->dsp = $this;
      $cmp_lnk->cmp = $cmp;
      $cmp_lnk->usr = $this->usr;
      $cmp_lnk->load($debug-1);
      $result .= $cmp_lnk->move_down($debug-1);
    }
    return $result;
  }
  
  // create a selection page where the user can select a view that should be used for a word
  function selector_page ($wrd_id, $back, $debug) {
    zu_debug('view->selector_page ('.$this->id.','.$wrd_id.')', $debug-10);
    $result  = '';

    /*
    $sql = "SELECT view_id, view_name 
              FROM views 
             WHERE code_id IS NULL
          ORDER BY view_name;";
          */
    $sql = sql_lst_usr ("view", $this->usr, $debug-1);
    $call = '/http/view.php?words='.$wrd_id;
    $field = 'new_id';
    
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $dsp_lst = $db_con->get($sql, $debug-5);  
    foreach ($dsp_lst AS $dsp) {
      $view_id   = $dsp['id'];
      $view_name = $dsp['name'];
      if ($view_id == $this->id) {
        $result .= '<b><a href="'.$call.'&'.$field.'='.$view_id.'">'.$view_name.'</a></b> ';
      } else {  
        $result .=    '<a href="'.$call.'&'.$field.'='.$view_id.'">'.$view_name.'</a> ';
      }
      $call_edit = '/http/view_edit.php?id='.$view_id.'&word='.$wrd_id.'&back='.$back;
      $result .= btn_edit ('design the view', $call_edit).' ';
      $call_del = '/http/view_del.php?id='.$view_id.'&word='.$wrd_id.'&back='.$back;
      $result .= btn_del ('delete the view', $call_del).' ';
      $result .= '<br>';
    }

    zu_debug('view->selector_page ... done.', $debug-1);
    return $result;
  }

  // true if the view is part of the view element list
  function is_in_list($dsp_lst, $debug) {
    $result = false; 
    
    foreach ($dsp_lst AS $dsp_id) {
      zu_debug('view->is_in_list '.$dsp_id.' = '.$this->id.'?', $debug-12);
      if ($dsp_id == $this->id) {
        $result = true; 
      }
    }

    return $result; 
  }
  
  // true if noone has used this view
  private function not_used($debug) {
    zu_debug('view->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    // to review: maybe replace by a database foreign key check
    $result = $this->not_changed($debug-1);
    return $result;
  }

  // true if no other user has modified the view
  private function not_changed($debug) {
    zu_debug('view->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.').', $debug-10);  
    $result = true;
    
    $change_user_id = 0;
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_views 
               WHERE view_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND excluded <> 1";
    } else {
      $sql = "SELECT user_id 
                FROM user_views 
               WHERE view_id = ".$this->id."
                 AND excluded <> 1";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $change_user_id = $db_con->get1($sql, $debug-5);  
    if ($change_user_id > 0) {
      $result = false;
    }
    zu_debug('view->not_changed for '.$this->id.' is '.zu_dsp_bool($result).'.', $debug-10);  
    return $result;
  }

  // true if the user is the owner and noone else has changed the view
  // because if another user has changed the view and the original value is changed, maybe the user view also needs to be updated
  function can_change($debug) {
    zu_debug('view->can_change ('.$this->id.',u'.$this->usr->id.')', $debug-10);  
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  

    zu_debug('view->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
    return $can_change;
  }

  // true if a record for a user specific configuration already exists in the database
  private function has_usr_cfg($debug) {
    $has_cfg = false;
    if ($this->usr_cfg_id > 0) {
      $has_cfg = true;
    }  
    return $has_cfg;
  }

  // create a database record to save user specific settings for this view
  private function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      zu_debug('view->add_usr_cfg for "'.$this->name.' und user '.$this->usr->name.'.', $debug-10);

      // check again if there ist not yet a record
      $sql = "SELECT view_id FROM `user_views` WHERE view_id = ".$this->id." AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_db_id = $db_con->get1($sql, $debug-5);  
      if ($usr_db_id <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_view';
        $log_id = $db_con->insert(array('view_id','user_id'), array($this->id,$this->usr->id), $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_view failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    $result = false;
    zu_debug('view->del_usr_cfg_if_not_needed pre check for "'.$this->name.' und user '.$this->usr->name.'.', $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT view_id,
                     view_name,
                     comment,
                     view_type_id,
                     excluded
                FROM user_views
               WHERE view_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_cfg = $db_con->get1($sql, $debug-5);  
      zu_debug('view->del_usr_cfg_if_not_needed check for "'.$this->name.' und user '.$this->usr->name.' with ('.$sql.').', $debug-12);
      if ($usr_cfg['view_id'] > 0) {
        if ($usr_cfg['comment']      == ''
        AND $usr_cfg['view_type_id'] == Null
        AND $usr_cfg['excluded']     == Null) {
          // delete the entry in the user sandbox
          zu_debug('view->del_usr_cfg_if_not_needed any more for "'.$this->name.' und user '.$this->usr->name.'.', $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }

  // simply remove a user adjustment without check
  private function del_usr_cfg_exe($db_con, $debug) {
    $result = '';

    $db_con->type = 'user_view';
    $result .= $db_con->delete(array('view_id','user_id'), array($this->id,$this->usr->id), $debug-1);
    if (str_replace('1','',$result) <> '') {
      $result .= 'Deletion of user view '.$this->id.' failed for '.$this->usr->name.'.';
    }
    
    return $result;
  }
  
  // remove user adjustment and log it (used by user.php to undo the user changes)
  function del_usr_cfg($debug) {
    $result = '';

    if ($this->id > 0 AND $this->usr->id > 0) {
      zu_debug('view->del_usr_cfg  "'.$this->id.' und user '.$this->usr->name.'.', $debug-12);

      $db_type = 'user_view';
      $log = $this->log_del($debug-1);
      if ($log->id > 0) {
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
      }  

    } else {
      zu_err("The view database ID and the user must be set to remove a user specific modification.", "view->del_usr_cfg", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // set the log entry parameter for a new value
  private function log_add($debug) {
    zu_debug('view->log_add "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'views';
    $log->field     = 'view_name';
    $log->old_value = '';
    $log->new_value = $this->name;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one view field
  private function log_upd($debug) {
    zu_debug('view->log_upd "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'views';
    } else {  
      $log->table   = 'user_views';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a view
  private function log_del($debug) {
    zu_debug('view->log_del "'.$this->name.'" for user '.$this->usr->name.'.', $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'views';
    $log->field     = 'view_name';
    $log->old_value = $this->name;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // actually update a view field in the main database record or the user sandbox
  private function save_field_do($db_con, $log, $debug) {
    $result = '';
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
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_view';
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
  
  // set the update parameters for the view comment
  private function save_field_comment($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->comment <> $this->comment) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->comment;
      $log->new_value = $this->comment;
      $log->std_value = $std_rec->comment;
      $log->row_id    = $this->id; 
      $log->field     = 'comment';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word type
  private function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->type_id <> $this->type_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->type_name($debug-1);
      $log->old_id    = $db_rec->type_id;
      $log->new_value = $this->type_name($debug-1);
      $log->new_id    = $this->type_id; 
      $log->std_value = $std_rec->type_name($debug-1);
      $log->std_id    = $std_rec->type_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'view_type_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula word link excluded
  private function save_field_excluded($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->excluded <> $this->excluded) {
      if ($this->excluded == 1) {
        $log = $this->log_del($debug-1);
      } else {
        $log = $this->log_add($debug-1);
      }
      $new_value  = $this->excluded;
      $std_value  = $std_rec->excluded;
      $log->field = 'excluded';
      // similar to $this->save_field_do
      if ($this->can_change($debug-1)) {
        $db_con->type = 'view';
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_view';
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
  
  // save all updated view fields excluding the name, because already done when adding a view
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_comment  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('view->save_fields all fields for "'.$this->name.'" has been saved.', $debug-12);
    return $result;
  }
  
  // updated the view name (which is the id field)  // save updated the word_link id fields (dsp and cmp)
  // should only be called if the user is the owner and nobody has used the display component link
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      zu_debug('view->save_id_fields to "'.$this->dsp_id().'" from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().').', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $std_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'view_name';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("view_name"),
                                              array($this->name), $debug-1);
      }
    }
    zu_debug('view->save_id_fields for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->name <> $this->name) {
      //$this->reset_objects($debug-1);
      // check if target link already exists
      zu_debug('view->save_id_if_updated check if target link already exists "'.$this->dsp_id().'" (has been "'.$db_rec->dsp_id().'").', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        if (UI_CAN_CHANGE_VIEW_NAME) {
          // ... if yes request to delete or exclude the record with the id parameters before the change
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and use it for the update
          $this->id = $db_chk->id;
          $this->owner_id = $db_chk->owner_id;
          // force the reinclude
          $this->excluded = Null;
          $db_rec->excluded = '1';
          $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-20);
          zu_debug('view->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add "'.$this->dsp_id().'".', $debug-14);
        } else {
          $result .= 'A view with the name "'.$this->name.'" already exists. Please use a new name.';
        }  
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('view->save_id_if_updated change the existing display component link "'.$this->dsp_id().'" (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'").', $debug-14);
          //$this->load_objects($debug-1);
          $result .= $this->save_id_fields($db_con, $db_rec, $std_rec, $debug-20);
        } else {
          // if the target link has not yet been created
          // ... request to delete the old
          $to_del = clone $db_rec;
          $result .= $to_del->del($debug-20);        
          // .. and create a deletion request for all users ???
          
          // ... and create a new display component link
          $this->id = 0;
          $this->owner_id = $this->usr->id;
          $result .= $this->add($db_con, $debug-20);
          zu_debug('view->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add "'.$this->dsp_id().'" (standard "'.$std_rec->dsp_id().'").', $debug-14);
        }
      }
    }  

    zu_debug('view->save_id_if_updated for "'.$this->name.'" has been done.', $debug-12);
    return $result;
  }
  
  // create a new view
  private function add($db_con, $debug) {
    zu_debug('view->add the view "'.$this->name.'".', $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new view
      $this->id = $db_con->insert(array("view_name","user_id"), array($this->name,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = new view_dsp;
        $db_rec->name = $this->name;
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the view fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err("Adding view ".$this->name." failed.", "view->save");
      }
    }  
        
    return $result;
  }
  
  // update a view in the database or create a user view
  function save($debug) {
    zu_debug('view->save "'.$this->name.'" for user '.$this->usr->id.'.', $debug-10);
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'view';         
    
    // if a new view is supposed to be added check if the name is used already
    if ($this->id <= 0) {
      // check if a view, formula or verb with the same name is already in the database
      zu_debug('view->save check if a view named "'.$this->name.'" already exists.', $debug-12);
      $db_chk = new view_dsp;
      $db_chk->name = $this->name;
      $db_chk->usr  = $this->usr;
      $db_chk->load($debug-1);
      if ($db_chk->id > 0) {
        $this->id = $db_chk->id;
      }
    }  
      
    // create a new view or update an existing
    if ($this->id <= 0) {
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('view->save update "'.$this->id.'".', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = new view_dsp;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      zu_debug('view->save -> database view "'.$db_rec->name.'" ('.$db_rec->id.') loaded.', $debug-14);
      $std_rec = new view_dsp;
      $std_rec->id = $this->id;
      $std_rec->load_standard($debug-1);
      zu_debug('view->save -> standard view settings for "'.$std_rec->name.'" ('.$std_rec->id.') loaded.', $debug-14);
      
      // for a correct user view detection (function can_change) set the owner even if the view has not been loaded before the save 
      if ($this->owner_id <= 0) {
        $this->owner_id = $std_rec->owner_id;
      }
      
      // check if the id parameters are supposed to be changed 
      $result .= $this->save_id_if_updated($db_con, $db_rec, $std_rec, $debug-1);

      // if a problem has appeared up to here, don't try to save the values
      // the problem is shown to the user by the calling interactive script
      if (str_replace ('1','',$result) == '') {
        $result .= $this->save_fields     ($db_con, $db_rec, $std_rec, $debug-1);        
      }
    }  
    
    return $result;    
  }

  // delete the complete view (the calling function del must have checked that no one uses this view)
  private function del_exe($debug) {
    zu_debug('view->del_exe.', $debug-16);
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // try to unlink the view components first
      // to do ....
      // delete also all user configuration that have also been excluded
      $db_con->type = 'user_view';
      $result .= $db_con->delete(array('view_id','excluded'), array($this->id,'1'), $debug-1);
      // finally delete the actual view
      $db_con->type   = 'view';         
      $result .= $db_con->delete('view_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a view
  function del($debug) {
    zu_debug('view->del.', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('view->del "'.$this->name.'".', $debug-14);
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
