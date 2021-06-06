<?php

/*

  view_component.php - a single display object like a headline or a table
  ------------------
  
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

class view_component extends user_sandbox {

  // database fields additional to the user sandbox fields for the view component
  public $comment        = '';   // the view component description that is shown as a mouseover explain to the user
  public $order_nbr      = NULL; // the position in the linked view
  public $type_id        = NULL; // the predefined entry type e.g. "formula results"
  public $word_id_row    = NULL; // if the view component uses a related word tree this is the start node 
                                 // e.g. for "company" the start node could be "cash flow statement" to show the cash flow for any company
  public $link_type_id   = NULL; // the word link type used to build the word tree started with the $start_word_id
  public $formula_id     = NULL; // to select a formula (no used case at the moment)
  public $word_id_col    = NULL; // for a table to defined which columns should be used (if not defined by the calling word)
  public $word_id_col2   = NULL; // for a table to defined second columns layer or the second axis in case of a chart
                                 // e.g. for a "company cash flow statement" the "col word" could be "Year" 
                                 // "col2 word" could be "Quarter" to show the Quarters between the year upon request 
                               
  // linked fields                               
  public $wrd_row        = NULL; // the word object for $word_id_row
  public $wrd_col        = NULL; // the word object for $word_id_col
  public $wrd_col2       = NULL; // the word object for $word_id_col2
  public $frm            = NULL; // the formula object for $formula_id
  public $link_type_name = '';   // 
  public $type_name      = '';   // 
  public $code_id        = '';   // the entry type code id
  public $back           = NULL; // the calling stack
  
  function __construct() {
    $this->type      = 'named';
    $this->obj_name  = 'view_component';

    $this->rename_can_switch = UI_CAN_CHANGE_VIEW_COMPONENT_NAME;
  }
    
  function reset() {
    $this->id         = NULL;
    $this->usr_cfg_id = NULL;
    $this->usr        = NULL;
    $this->owner_id   = NULL;
    $this->excluded   = NULL;
    
    $this->name       = '';

    $this->comment        = '';  
    $this->order_nbr      = NULL;
    $this->type_id        = NULL;
    $this->word_id_row    = NULL;
    $this->link_type_id   = NULL; 
    $this->formula_id     = NULL;
    $this->word_id_col    = NULL;
    $this->word_id_col2   = NULL;
    $this->wrd_row        = NULL;
    $this->wrd_col        = NULL;
    $this->wrd_col2       = NULL;
    $this->frm            = NULL;
    $this->link_type_name = '';  
    $this->type_name      = '';  
    $this->code_id        = '';  
    $this->back           = NULL;
  }

  // load the view component parameters for all users
  function load_standard($debug) {

    global $db_con;
    $result = '';
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "m.view_component_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "m.view_component_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= log_err("ID missing to load the standard view component.", "view_component->load_standard", '', (new Exception)->getTraceAsString(), $this->usr);
    } else{  
      $sql = "SELECT m.view_component_id,
                     m.user_id,
                     m.view_component_name,
                     m.comment,
                     m.view_component_type_id,
                     m.word_id_row,
                     m.link_type_id,
                     m.formula_id,
                     m.word_id_col,
                     m.word_id_col2,
                     m.excluded
                FROM view_components m 
               WHERE ".$sql_where.";";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_cmp = $db_con->get1($sql, $debug-5);  
      if ($db_cmp['view_component_id'] > 0) {
        $this->id           = $db_cmp['view_component_id'];
        $this->owner_id     = $db_cmp['user_id'];
        $this->name         = $db_cmp['view_component_name'];
        $this->comment      = $db_cmp['comment'];
        $this->type_id      = $db_cmp['view_component_type_id'];
        $this->word_id_row  = $db_cmp['word_id_row'];
        $this->link_type_id = $db_cmp['link_type_id'];
        $this->formula_id   = $db_cmp['formula_id'];
        $this->word_id_col  = $db_cmp['word_id_col'];
        $this->word_id_col2 = $db_cmp['word_id_col2'];
        $this->excluded     = $db_cmp['excluded'];

        // TODO try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE view_components SET user_id = ".$this->usr->id." WHERE view_component_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "view_component->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
        
        $this->load_phrases($debug-1);
      } 
    }  
    return $result;
  }
  
  // load the missing view component parameters from the database
  function load($debug) {
    log_debug('view_component->load', $debug);

    global $db_con;
    $result = '';

    // check the minimal input parameters
    if (!isset($this->usr)) {
      log_err("The user id must be set to load a view component.", "view_component->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0 AND $this->name == '') {  
      log_err("Either the database ID (".$this->id.") or the display item name (".$this->name.") and the user (".$this->usr->id.") must be set to find a display item.", "view_component->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = "e.view_component_id = ".$this->id;
      } elseif ($this->name <> '' AND !is_null($this->usr->id)) {
        $sql_where = "e.view_component_name = ".sf($this->name);
      }
      //zu_debug('view_component->load where '.$sql_where, $debug-10); 

      if ($sql_where == '') {
        log_err("Internal error in the where clause.", "view_component->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else{  
        $sql = "SELECT e.view_component_id,
                       u.view_component_id AS user_component_id,
                       e.user_id,
                       IF(u.view_component_name IS NULL,    e.view_component_name,    u.view_component_name)    AS view_component_name,
                       IF(u.comment IS NULL,                e.comment,                u.comment)                AS comment,
                       IF(u.view_component_type_id IS NULL, e.view_component_type_id, u.view_component_type_id) AS view_component_type_id,
                       IF(c.code_id IS NULL,                t.code_id,                c.code_id)                AS code_id,
                       IF(u.word_id_row IS NULL,            e.word_id_row,            u.word_id_row)            AS word_id_row,
                       IF(u.link_type_id IS NULL,           e.link_type_id,           u.link_type_id)           AS link_type_id,
                       IF(u.formula_id IS NULL,             e.formula_id,             u.formula_id)             AS formula_id,
                       IF(u.word_id_col IS NULL,            e.word_id_col,            u.word_id_col)            AS word_id_col,
                       IF(u.word_id_col2 IS NULL,           e.word_id_col2,           u.word_id_col2)           AS word_id_col2,
                       IF(u.excluded IS NULL,               e.excluded,               u.excluded)               AS excluded
                  FROM view_components e
             LEFT JOIN user_view_components u ON u.view_component_id = e.view_component_id 
                                             AND u.user_id = ".$this->usr->id." 
             LEFT JOIN view_component_types t ON e.view_component_type_id = t.view_component_type_id
             LEFT JOIN view_component_types c ON u.view_component_type_id = c.view_component_type_id
                 WHERE ".$sql_where.";";
        //zu_debug('view_component->load with "'.$sql.'"', $debug); 
        //$db_con = new mysql;
        $db_con->usr_id = $this->usr->id;         
        $db_item = $db_con->get1($sql, $debug-5);  
        //zu_debug('view_component->level-22 '.$debug.' done.', 10); 
        log_debug('view_component->load with '.$sql, $debug-10);
        //zu_debug('view_component->level-2 '.$debug.' done.', 10); 
        if ($db_item['view_component_id'] > 0) {
          $this->id           = $db_item['view_component_id'];
          $this->usr_cfg_id   = $db_item['user_component_id'];
          $this->owner_id     = $db_item['user_id'];
          $this->name         = $db_item['view_component_name'];
          $this->comment      = $db_item['comment'];
          $this->type_id      = $db_item['view_component_type_id'];
          $this->word_id_row  = $db_item['word_id_row'];
          $this->link_type_id = $db_item['link_type_id'];
          $this->formula_id   = $db_item['formula_id'];
          $this->word_id_col  = $db_item['word_id_col'];
          $this->word_id_col2 = $db_item['word_id_col2'];
          $this->excluded     = $db_item['excluded'];
          $this->load_phrases($debug-1);
          log_debug('view_component->load of '.$this->dsp_id().' done', $debug-16);
        } else {  
          // TODO add this part to all load functions
          // if the database object is not found any more, reset the object
          $this->reset($debug-1);
        }
      }  
    }  
    log_debug('view_component->load of '.$this->dsp_id().' quit', $debug-14);
    return $result;
  }
  
  // load the related word and formula objects
  function load_phrases($debug) {
    $this->load_wrd_row($debug-1);
    $this->load_wrd_col($debug-1);
    $this->load_wrd_col2($debug-1);
    $this->load_formula($debug-1);
    log_debug('view_component->load_phrases done for '.$this->dsp_id(), $debug-18);
  }
  
  // 
  function load_wrd_row($debug) {
    $result = '';
    if ($this->word_id_row > 0) {
      $wrd_row = New word_dsp;
      $wrd_row->id  = $this->word_id_row;
      $wrd_row->usr = $this->usr;
      $wrd_row->load($debug-1); 
      $this->wrd_row = $wrd_row;
      $result = $wrd_row->name;
    }
    return $result;    
  }
  
  // 
  function load_wrd_col($debug) {
    $result = '';
    if ($this->word_id_col > 0) {
      $wrd_col = New word_dsp;
      $wrd_col->id  = $this->word_id_col;
      $wrd_col->usr = $this->usr;
      $wrd_col->load($debug-1); 
      $this->wrd_col = $wrd_col;
      $result = $wrd_col->name;
    }
    return $result;    
  }
  
  // 
  function load_wrd_col2($debug) {
    $result = '';
    if ($this->word_id_col2 > 0) {
      $wrd_col2 = New word_dsp;
      $wrd_col2->id  = $this->word_id_col2;
      $wrd_col2->usr = $this->usr;
      $wrd_col2->load($debug-1); 
      $this->wrd_col2 = $wrd_col2;
      $result = $wrd_col2->name;
    }
    return $result;    
  }
  
  // 
  function load_formula($debug) {
    $result = '';
    if ($this->formula_id > 0) {
      $frm = New formula;
      $frm->id  = $this->formula_id;
      $frm->usr = $this->usr;
      $frm->load($debug-1); 
      $this->frm = $frm;
      $result = $frm->name;
    }
    return $result;    
  }
  
  // list of all view ids that are directly assigned to this view component
  function assign_dsp_ids ($debug) {

    global $db_con;
    $result = array();

    if ($this->id > 0 AND isset($this->usr)) {
      log_debug('view_component->assign_dsp_ids for view_component "'.$this->id.'" and user "'.$this->usr->name.'"', $debug-12);
      // this sql is similar to the load statement in view_links.php, maybe combine
      $sql = "SELECT l.view_component_link_id,
                     u.view_component_link_id AS user_link_id,
                     l.user_id,
                     l.view_id, 
                     l.view_component_id,
                     IF(u.excluded IS NULL,      l.excluded,      u.excluded)      AS excluded,
                     IF(u.order_nbr IS NULL,     l.order_nbr,     u.order_nbr)     AS order_nbr,
                     IF(u.position_type IS NULL, l.position_type, u.position_type) AS position_type
                FROM view_component_position_types t, view_component_links l
           LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                            AND u.user_id = ".$this->usr->id."  
               WHERE l.view_component_id = ".$this->id.";";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_lst = $db_con->get($sql, $debug-9);
      foreach ($db_lst AS $db_row) {
        log_debug('view_component->assign_dsp_ids -> check exclusion ', $debug-16);
        if (is_null($db_row['excluded']) OR $db_row['excluded'] == 0) {
          $result[] = $db_row['view_id'];
        }  
      } 
      log_debug('view_component->assign_dsp_ids -> number of views '. count ($result), $debug-10);
    } else {
      log_err("The user id must be set to list the view_component links.", "view_component->assign_dsp_ids", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // return the html code to display a view name with the link
  function name_linked ($back, $debug) {
    $result = '';
  
    $result .= '<a href="/http/view_component_edit.php?id='.$this->id.'&back='.$back.'">'.$this->name.'</a>';
    
    return $result;    
  }

  // 
  function type_name($debug) {
    log_debug('view_component->type_name do', $debug-16);

    global $db_con;

    if ($this->type_id > 0) {
      $sql = "SELECT view_component_type_name, description
                FROM view_component_types
               WHERE view_component_type_id = ".$this->type_id.";";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    log_debug('view_component->type_name done', $debug-16);
    return $this->type_name;    
  }

  // create an object for the export
  function export_obj ($debug) {
    log_debug('view_component->export_obj '.$this->dsp_id(), $debug-10);
    $result = New view_component();

    // add the component parameters
    $this->load_phrases($debug-1);
    if ($this->order_nbr >= 0)            { $result->pos      = $this->order_nbr; }
    $result->name    = $this->name;
    if ($this->type_name($debug-1) <> '') { $result->type     = $this->type_name($debug-1); }
    if ($this->code_id <> '')             { $result->code_id  = $this->code_id; }
    if (isset($this->wrd_row))            { $result->row      = $this->wrd_row->name; }
    if (isset($this->wrd_col))            { $result->column   = $this->wrd_col->name; }
    if (isset($this->wrd_col2))           { $result->column2  = $this->wrd_col2->name; }
    if ($this->comment <> '')             { $result->comment  = $this->comment; }

    log_debug('view_component->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a view from an object
  function import_obj ($debug) {
  }
  
  /*
  
  display functions
  
  */

  // display the unique id fields
  function dsp_id () {
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

  function name ($debug) {
    $result = '"'.$this->name.'"';
    return $result;
  }

  // not used at the moment
/*  private function link_type_name($debug) {
    if ($this->type_id > 0) {
      $sql = "SELECT view_component_type_name
                FROM view_component_types
               WHERE view_component_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    return $this->type_name;    
  } */
  
  /*
  
    to link and unlink a view_component 
  
  */
  
  // returns the next free order number for a new view component
  function next_nbr($view_id, $debug) {
    log_debug('view_component->next_nbr for view "'.$view_id.'"', $debug-10);

    global $db_con;

    If ($view_id == '' OR $view_id == Null OR $view_id == 0) {
      log_err('Cannot get the next position, because the view_id is not set','view_component->next_nbr', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      $sql = " SELECT max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT IF(u.order_nbr IS NULL,     l.order_nbr,     u.order_nbr)     AS order_nbr
                          FROM view_component_links l 
                    LEFT JOIN user_view_component_links u ON u.view_component_link_id = l.view_component_link_id 
                                                      AND u.user_id = ".$this->usr->id." 
                        WHERE l.view_id = ".$view_id." ) AS m;";
      //$db_con = new mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      $result = $db_row["max_order_nbr"];
      
      // if nothing is found, assume one as the next free number
      if ($result <= 0) {
        $result = 1;
      } else {
        $result++;
      }
    }

    log_debug("view_component->next_nbr -> (".$result.")", $debug-10);
    return $result;
  }

  // set the log entry parameters for a value update
  function log_link($dsp, $debug) {
    log_debug('view_component->log_link '.$this->dsp_id().' to "'.$dsp->name.'"  for user '.$this->usr->id, $debug-10);
    $log = New user_log_link;
    $log->usr      = $this->usr;
    $log->action   = 'add';
    $log->table    = 'view_component_links';
    $log->new_from = clone $this;
    $log->new_to   = clone $dsp;
    $log->row_id   = $this->id; 
    $result = $log->add_link_ref($debug-1);
    
    log_debug('view_component -> link logged '.$log->id.'', $debug-10);
    return $result;    
  }
  
  // set the log entry parameters to unlink a display component ($cmp) from a view ($dsp)
  function log_unlink($dsp, $debug) {
    log_debug('view_component->log_unlink '.$this->dsp_id().' from "'.$dsp->name.'" for user '.$this->usr->id, $debug-10);
    $log = New user_log_link;
    $log->usr      = $this->usr;
    $log->action   = 'del';
    $log->table    = 'view_component_links';
    $log->old_from = clone $this;
    $log->old_to   = clone $dsp;
    $log->row_id   = $this->id; 
    $result = $log->add_link_ref($debug-1);
    
    log_debug('view_component -> unlink logged '.$log->id, $debug-14);
    return $result;    
  }
  
  // link a view component to a view
  function link ($dsp, $order_nbr, $debug) {
    log_debug('view_component->link '.$this->dsp_id().' to '.$dsp->dsp_id().' at pos '.$order_nbr, $debug-10);
    $result = '';
    
    $dsp_lnk = new view_component_link;
    $dsp_lnk->fob         = $dsp;
    $dsp_lnk->tob         = $this;
    $dsp_lnk->usr         = $this->usr;
    $dsp_lnk->order_nbr   = $order_nbr;
    $dsp_lnk->pos_type_id = 1; // to be reviewed
    $result = '';
    $result .= $dsp_lnk->save($debug-1);

    return $result;
  }

  // remove a view component from a view
  // to do: check if the view component is not linked anywhere else
  // and if yes, delete the view component after confirmation
  function unlink ($dsp, $debug) {
    $result = '';
    
    if (isset($dsp) AND isset($this->usr)) {
      log_debug('view_component->unlink '.$this->dsp_id().' from "'.$dsp->name.'" ('.$dsp->id.')', $debug-10);
      $dsp_lnk = new view_component_link;
      $dsp_lnk->fob       = $dsp;
      $dsp_lnk->tob       = $this;
      $dsp_lnk->usr       = $this->usr;
      $result .= $dsp_lnk->del($debug-1);
    } else {  
      $result .= log_err("Cannot unlink view component, because view is not set.", "view_component.php", '', (new Exception)->getTraceAsString(), $this->usr);
    }

    return $result;
  }

  // create a database record to save user specific settings for this view_component
  function add_usr_cfg($debug) {

    global $db_con;
    $result = '';

    if (!$this->has_usr_cfg) {
      log_debug('view_component->add_usr_cfg for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);

      // check again if there ist not yet a record
      $sql = 'SELECT user_id 
                FROM user_view_components
               WHERE view_component_id = '.$this->id.' 
                 AND user_id = '.$this->usr->id.';';
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-10);  
      $usr_db_id = $db_row['user_id']; 
      if ($usr_db_id <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_view_component';
        $log_id = $db_con->insert(array('view_component_id','user_id'), array($this->id,$this->usr->id), $debug-10);
        if ($log_id <= 0) {
          $result .= 'Insert of user_view_component failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  function del_usr_cfg_if_not_needed($debug) {
    log_debug('view_component->del_usr_cfg_if_not_needed pre check for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-12);

    global $db_con;
    $result = '';

    //if ($this->has_usr_cfg) {

      // check again if there is not yet a record
      $sql = "SELECT view_component_id,
                     view_component_name,
                     comment,
                     view_component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM user_view_components
               WHERE view_component_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      //$db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_cfg = $db_con->get1($sql, $debug-5);  
      log_debug('view_component->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' und user '.$this->usr->name.' with ('.$sql.')', $debug-12);
      if ($usr_cfg['view_component_id'] > 0) {
        if ($usr_cfg['comment']            == ''
        AND $usr_cfg['view_component_type_id'] == Null
        AND $usr_cfg['word_id_row']        == Null
        AND $usr_cfg['link_type_id']       == Null
        AND $usr_cfg['formula_id']         == Null
        AND $usr_cfg['word_id_col']        == Null
        AND $usr_cfg['word_id_col2']       == Null
        AND $usr_cfg['excluded']           == Null) {
          // delete the entry in the user sandbox
          log_debug('view_component->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
          $result .= $this->del_usr_cfg_exe($db_con, $debug-10);
        }  
      }  
    //}  
    return $result;
  }

  // set the update parameters for the view component comment
  function save_field_comment($db_con, $db_rec, $std_rec, $debug) {
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
  function save_field_type($db_con, $db_rec, $std_rec, $debug) {
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
      $log->field     = 'view_component_type_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word row
  function save_field_wrd_row($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->word_id_row <> $this->word_id_row) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->load_wrd_row($debug-1);
      $log->old_id    = $db_rec->word_id_row;
      $log->new_value = $this->load_wrd_row($debug-1);
      $log->new_id    = $this->word_id_row; 
      $log->std_value = $std_rec->load_wrd_row($debug-1);
      $log->std_id    = $std_rec->word_id_row; 
      $log->row_id    = $this->id; 
      $log->field     = 'word_id_row';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word col
  function save_field_wrd_col($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->word_id_col <> $this->word_id_col) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->load_wrd_col($debug-1);
      $log->old_id    = $db_rec->word_id_col;
      $log->new_value = $this->load_wrd_col($debug-1);
      $log->new_id    = $this->word_id_col; 
      $log->std_value = $std_rec->load_wrd_col($debug-1);
      $log->std_id    = $std_rec->word_id_col; 
      $log->row_id    = $this->id; 
      $log->field     = 'word_id_col';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the word col2
  function save_field_wrd_col2($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->word_id_col2 <> $this->word_id_col2) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->load_wrd_col2($debug-1);
      $log->old_id    = $db_rec->word_id_col2;
      $log->new_value = $this->load_wrd_col2($debug-1);
      $log->new_id    = $this->word_id_col2; 
      $log->std_value = $std_rec->load_wrd_col2($debug-1);
      $log->std_id    = $std_rec->word_id_col2; 
      $log->row_id    = $this->id; 
      $log->field     = 'word_id_col2';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the formula
  function save_field_formula($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->formula_id <> $this->formula_id) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->load_formula($debug-1);
      $log->old_id    = $db_rec->formula_id;
      $log->new_value = $this->load_formula($debug-1);
      $log->new_id    = $this->formula_id; 
      $log->std_value = $std_rec->load_formula($debug-1);
      $log->std_id    = $std_rec->formula_id; 
      $log->row_id    = $this->id; 
      $log->field     = 'formula_id';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // save all updated view_component fields excluding the name, because already done when adding a view_component
  function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    log_debug('view_component->save_fields for '.$std_rec->dsp_id(), $debug-18);

    $result .= $this->save_field_comment  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type     ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_wrd_row  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_wrd_col  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_wrd_col2 ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_formula  ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-1);
    log_debug('view_component->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
  
}

?>
