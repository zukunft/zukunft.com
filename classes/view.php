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
  
  Copyright (c) 1995-2020 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class view extends user_sandbox {

  // database fields additional to the user sandbox fields for the view component
  public $comment     = '';   // the view description that is shown as a mouseover explain to the user
  public $type_id     = NULL; // the id of the view type
  public $code_id     = '';   // to select internal predefined views
  
  // in memory only fields
  public $type_name   = '';   // 
  public $cmp_lst     = NULL;   // array of the view component objects
  public $back        = NULL;   // the calling stack
  
  function __construct() {
    $this->type      = 'named';
    $this->obj_name  = 'view';

    $this->rename_can_switch = UI_CAN_CHANGE_VIEW_NAME;
  }
    
  function reset($debug) {
    $this->id         = NULL;
    $this->usr_cfg_id = NULL;
    $this->usr        = NULL;
    $this->owner_id   = NULL;
    $this->excluded   = NULL;
    
    $this->name       = '';

    $this->comment    = '';   
    $this->type_id    = NULL; 
    $this->code_id    = '';   
  
    $this->type_name  = '';  
    $this->cmp_lst    = NULL; 
    $this->back       = NULL; 
  }

  // load the view parameters for all users
  function load_standard($debug) {
    $result = '';
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "m.view_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "m.view_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= zu_err('Cannot load standard view because ID and name are missing', 'view->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
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
      if ($db_dsp['view_id'] <= 0) {
        $this->reset($debug-1);
      } else {
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
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE views SET user_id = ".$this->usr->id." WHERE view_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "view->load_standard", (new Exception)->getTraceAsString(), $debug-10);
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
        if ($db_view['view_id'] <= 0) {
          $this->reset($debug-1);
        } else {
          $this->id         = $db_view['view_id'];
          $this->usr_cfg_id = $db_view['user_view_id'];
          $this->owner_id   = $db_view['user_id'];
          $this->name       = $db_view['view_name'];
          $this->comment    = $db_view['comment'];
          $this->type_id    = $db_view['view_type_id'];
          $this->excluded   = $db_view['excluded'];
          // because system masks can be created 
        } 
        zu_debug('view->load '.$this->dsp_id(), $debug-10);
      }  
    }  
  }
    
  // load all parts of this view for this user
  function load_components($debug) {
    zu_debug('view->load_components for '.$this->dsp_id(), $debug-10);  

    // TODO make the order user specific
    $sql = " SELECT e.view_component_id, 
                    u.view_component_id AS user_entry_id,
                    e.user_id, 
                    IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr) AS order_nbr,
                    IF(u.view_component_name IS NULL,    e.view_component_name,    u.view_component_name)    AS view_component_name,
                    IF(u.view_component_type_id IS NULL, e.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
                    IF(c.code_id IS NULL,            t.code_id,            c.code_id)            AS code_id,
                    IF(u.word_id_row IS NULL,        e.word_id_row,        u.word_id_row)        AS word_id_row,
                    IF(u.link_type_id IS NULL,       e.link_type_id,       u.link_type_id)       AS link_type_id,
                    IF(u.formula_id IS NULL,         e.formula_id,         u.formula_id)         AS formula_id,
                    IF(u.word_id_col IS NULL,        e.word_id_col,        u.word_id_col)        AS word_id_col,
                    IF(u.word_id_col2 IS NULL,       e.word_id_col2,       u.word_id_col2)       AS word_id_col2,
                    IF(y.excluded IS NULL,           l.excluded,           y.excluded)           AS link_excluded,
                    IF(u.excluded IS NULL,           e.excluded,           u.excluded)           AS excluded
               FROM view_component_links l            
          LEFT JOIN user_view_component_links y ON y.view_component_link_id = l.view_component_link_id 
                                               AND y.user_id = ".$this->usr->id.", 
                    view_components e             
          LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id 
                                          AND u.user_id = ".$this->usr->id." 
          LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
          LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
              WHERE l.view_id = ".$this->id." 
                AND l.view_component_id = e.view_component_id 
           ORDER BY IF(y.order_nbr IS NULL, l.order_nbr, y.order_nbr);";
    zu_debug("view->load_components ... ".$sql, $debug-12);
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_lst = $db_con->get($sql, $debug-8);  
    $this->cmp_lst = array();
    foreach ($db_lst AS $db_entry) {
      // this is only for the view of the active user, so a direct exclude can be done
      if ((is_null($db_entry['excluded'])      OR $db_entry['excluded'] == 0)
      AND (is_null($db_entry['link_excluded']) OR $db_entry['link_excluded'] == 0)) {
        $new_entry = new view_component_dsp;
        $new_entry->id            = $db_entry['view_component_id'];
        $new_entry->usr           = $this->usr;
        $new_entry->owner_id      = $db_entry['user_id'];
        $new_entry->order_nbr     = $db_entry['order_nbr'];
        $new_entry->name          = $db_entry['view_component_name'];
        $new_entry->word_id_row   = $db_entry['word_id_row'];
        $new_entry->link_type_id  = $db_entry['link_type_id'];
        $new_entry->type_id       = $db_entry['view_component_type_id'];
        $new_entry->formula_id    = $db_entry['formula_id'];
        $new_entry->word_id_col   = $db_entry['word_id_col'];
        $new_entry->word_id_col2  = $db_entry['word_id_col2'];
        $new_entry->code_id       = $db_entry['code_id'];
        $new_entry->load_phrases($debug-1);
        $this->cmp_lst[]          = $new_entry;
      }
    }
    zu_debug('view->load_components '.count($this->cmp_lst).' loaded for '.$this->dsp_id(), $debug-8);

    return $this->cmp_lst;
  }

  // return the beginning html code for the view_type; 
  // the view type defines something like the basic setup of a view
  // e.g. the catch view does not have the header, whereas all other views have
  function dsp_type_open($debug) {
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

  function dsp_type_close($debug) {
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

  // TODO review (get the object instead)
  function type_name($debug) {
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
  function dsp_entries($wrd, $back, $debug) {
    zu_debug('view->dsp_entries "'.$wrd->name.'" with the view '.$this->dsp_id().' for user "'.$this->usr->name.'"', $debug-10);

    $result = '';
    $word_array = array();
    $this->load_components($debug-1);
    foreach ($this->cmp_lst AS $cmp) {
      zu_debug('view->dsp_entries ... "'.$cmp->name.'" type "'.$cmp->type_id.'"', $debug-6);
      
      // list of all possible view components
      $result .= $cmp->text            ($debug-1);        // just to display a simple text
      $result .= $cmp->word_name       ($wrd, $debug-1); // show the word name and give the user the possibility to change the word name
      $result .= $cmp->table           ($wrd, $debug-1); // display a table (e.g. ABB as first word, Cash Flow Statment as second word)
      $result .= $cmp->num_list        ($wrd, $back, $debug-1); // a word list with some key numbers e.g. all companies with the PE ratio
      $result .= $cmp->formulas        ($wrd, $debug-1); // display all formulas related to the given word
      $result .= $cmp->formula_values  ($wrd, $debug-1); // show a list of formula results related to a word
      $result .= $cmp->word_childs     ($wrd, $debug-1); // show all words that are based on the given start word
      $result .= $cmp->word_parents    ($wrd, $debug-1); // show all word that this words is based on
      $result .= $cmp->json_export     ($wrd, $back, $debug-1); // offer to configure and create an JSON file
      $result .= $cmp->xml_export      ($wrd, $back, $debug-1); // offer to configure and create an XML file
      $result .= $cmp->csv_export      ($wrd, $back, $debug-1); // offer to configure and create an CSV file
      $result .= $cmp->all             ($wrd, $back, $debug-1); // shows all: all words that link to the given word and all values related to the given word
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
    zu_debug('view->display "'.$wrd->name.'" with the view '.$this->dsp_id().' (type '.$this->type_id.')  for user "'.$this->usr->name.'"', $debug-10);
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
      $result .= $this->dsp_navbar($back, $debug-1);
      $result .= $this->dsp_entries($wrd, $back, $debug-1);
      $result .= $this->dsp_type_close($debug-1);
    }
    zu_debug('view->display ... done', $debug-18);
    
    return $result;
  }
  
  // create an object for the export
  function export_obj ($debug) {
    zu_debug('view->export_obj '.$this->dsp_id(), $debug-10);
    $result = Null;

    // add the view parameters
    $result->name    = $this->name;
    $result->comment = $this->comment;
    $result->type    = $this->type_name($debug-1);
    if ($this->code_id <> '') { $result->code_id = $this->code_id; }

    // add the view components used
    $this->load_components($debug-1);
    $exp_cmp_lst = array();
    foreach ($this->cmp_lst AS $cmp) {
      $exp_cmp_lst[] = $cmp->export_obj($debug-1);
    }
    $result->view_components = $exp_cmp_lst;

    zu_debug('view->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a view from an object
  function import_obj ($json_obj, $debug) {
    zu_debug('view->import_obj', $debug-10);
    $result = '';
    
    foreach ($json_obj AS $key => $value) {

      if ($key == 'name')    { $this->name    = $value; }
      if ($key == 'comment') { $this->comment = $value; }
      /* TODO
      if ($key == 'type')    { $this->type_id = cl($value); }
      if ($key == 'code_id') {
      }
      if ($key == 'view_components') {
      }
      */
    }
    
    if ($result == '') {
      $this->save($debug-1);
      zu_debug('view->import_obj -> '.$this->dsp_id(), $debug-18);
    } else {
      zu_debug('view->import_obj -> '.$result, $debug-18);
    }

    return $result;
  }
  
  /*
  
  display functions
  
  */
  
  // display the unique id fields
  function dsp_id ($debug) {
    $result = ''; 

    if ($this->name <> '') {
      $result .= '"'.$this->name.'"'; 
      if ($this->id > 0) {
        $result .= ' ('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  // move one view component one place up
  // in case of an error the error message is returned
  // if everything is fine an empty string is returned
  function entry_up ($view_component_id, $debug) {
    $result = '';
    // check the all minimal input parameters
    if ($view_component_id <= 0) {
      zu_err("The view component id must be given to move it.", "view->entry_up", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $cmp = new view_component_dsp;
      $cmp->id  = $view_component_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1);
      $cmp_lnk = new view_component_link;
      $cmp_lnk->fob = $this;
      $cmp_lnk->tob = $cmp;
      $cmp_lnk->usr = $this->usr;
      $cmp_lnk->load($debug-1);
      $result .= $cmp_lnk->move_up($debug-1);
    }
    return $result;
  }
  
  // move one view component one place down
  function entry_down ($view_component_id, $debug) {
    $result = '';
    // check the all minimal input parameters
    if ($view_component_id <= 0) {
      zu_err("The view component id must be given to move it.", "view->entry_down", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $cmp = new view_component_dsp;
      $cmp->id  = $view_component_id;
      $cmp->usr = $this->usr;
      $cmp->load($debug-1);
      $cmp_lnk = new view_component_link;
      $cmp_lnk->fob = $this;
      $cmp_lnk->tob = $cmp;
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

    zu_debug('view->selector_page ... done', $debug-1);
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
  
  // create a database record to save user specific settings for this view
  function add_usr_cfg($debug) {
    $result = '';
    zu_debug('view->add_usr_cfg '.$this->dsp_id(), $debug-10);

    if (!$this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = 'SELECT user_id 
                FROM user_views 
               WHERE view_id = '.$this->id.' 
                 AND user_id = '.$this->usr->id.';';
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      $usr_db_id = $db_row['user_id'];
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
  function del_usr_cfg_if_not_needed($debug) {
    $result = false;
    zu_debug('view->del_usr_cfg_if_not_needed pre check for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-12);

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
      zu_debug('view->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' und user '.$this->usr->name.' with ('.$sql.')', $debug-12);
      if ($usr_cfg['view_id'] > 0) {
        if ($usr_cfg['comment']      == ''
        AND $usr_cfg['view_type_id'] == Null
        AND $usr_cfg['excluded']     == Null) {
          // delete the entry in the user sandbox
          zu_debug('view->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-1);
        }  
      }  
    //}  
    return $result;
  }

  // set the update parameters for the view comment
  function save_field_comment($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->comment <> $this->comment) {
      $log = $this->log_upd_field($debug-1);
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
  function save_field_type($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->type_id <> $this->type_id) {
      $log = $this->log_upd_field($debug-1);
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
  
  // save all updated view fields excluding the name, because already done when adding a view
  function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_comment  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('view->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
  
}

?>
