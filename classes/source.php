<?php

/*

  source.php - the source object to define the source for the values
  ----------
  
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

class source {

  // database fields
  public $id          = NULL; // the database id of the source, which is the same for the standard and the user specific source
  public $usr         = NULL; // the person who wants to see something
  public $owner_id    = NULL; // the user id of the person who created the source, which is the default source
  public $name        = '';   // simply the source name, which cannot be empty
  public $url         = '';   // the internet link to the source
  public $comment     = '';   // the source description that is shown as a mouseover explain to the user
  public $type_id     = NULL; // the id of the source type
  public $code_id     = '';   // to select internal predefined sources
  
  // in memory only fields
  public $type_name   = '';   // 
  public $back        = NULL;   // the calling stack
  
  // load the source parameters for all users
  private function load_standard($debug) {
    $result = '';
    
    // set the where clause depending on the values given
    $sql_where = '';
    if ($this->id > 0) {
      $sql_where = "s.source_id = ".$this->id;
    } elseif ($this->name <> '') {
      $sql_where = "s.source_name = ".sf($this->name);
    }

    if ($sql_where == '') {
      $result .= zu_err("ID missing to load the standard source.", "source->load_standard", '', (new Exception)->getTraceAsString(), $this->usr);
    } else{  
      $sql = "SELECT s.source_id,
                     s.user_id,
                     s.source_name,
                     s.`url`,
                     s.comment,
                     s.source_type_id,
                     s.code_id
                FROM sources s 
               WHERE ".$sql_where.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_src = $db_con->get1($sql, $debug-5);  
      if ($db_src['source_id'] > 0) {
        $this->id           = $db_src['source_id'];
        $this->owner_id     = $db_src['user_id'];
        $this->name         = $db_src['source_name'];
        $this->url          = $db_src['url'];
        $this->comment      = $db_src['comment'];
        $this->type_id      = $db_src['source_type_id'];
        $this->code_id      = $db_src['code_id'];

        // TODO: try to avoid using load_test_user
        if ($this->owner_id > 0) {
          $usr = New user;
          $usr->id = $this->owner_id;
          $usr->load_test_user($debug-1);
          $this->usr = $usr; 
        } else {
          // take the ownership if it is not yet done. The ownership is probably missing due to an error in an older program version.
          $sql_set = "UPDATE sources SET user_id = ".$this->usr->id." WHERE source_id = ".$this->id.";";
          $sql_result = $db_con->exe($sql_set, DBL_SYSLOG_ERROR, "source->load_standard", (new Exception)->getTraceAsString(), $debug-10);
          //zu_err('Value owner missing for value '.$this->id.'.', 'value->load_standard', '', (new Exception)->getTraceAsString(), $this->usr);
        }
      } 
    }  
    return $result;
  }
  
  // load the missing source parameters from the database
  function load($debug) {

    // check the all minimal input parameters
    if (!isset($this->usr)) {
      zu_err("The user id must be set to load a source.", "source->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif ($this->id <= 0 AND $this->code_id == '' AND $this->name == '') {  
      zu_err("Either the database ID (".$this->id."), the name (".$this->name.") or the code_id (".$this->code_id.") and the user (".$this->usr->id.") must be set to load a source.", "source->load", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // set the where clause depending on the values given
      $sql_where = '';
      if ($this->id > 0) {
        $sql_where = "s.source_id = ".$this->id;
      } elseif ($this->code_id <> '' AND !is_null($this->usr->id)) {
        $sql_where = "s.code_id = ".sf($this->code_id);
      } elseif ($this->name <> '' AND !is_null($this->usr->id)) {
        $sql_where = "s.source_name = ".sf($this->name);
      }

      if ($sql_where == '') {
        zu_err("Internal error on the where clause.", "source->load", '', (new Exception)->getTraceAsString(), $this->usr);
      } else{  
        $sql = "SELECT s.source_id,
                       u.source_id AS user_source_id,
                       s.user_id,
                       IF(u.source_name IS NULL,    s.source_name,    u.source_name)    AS source_name,
                       IF(u.`url` IS NULL,          s.`url`,          u.`url`)          AS `url`,
                       IF(u.comment IS NULL,        s.comment,        u.comment)        AS comment,
                       IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id,
                       s.code_id
                  FROM sources s 
             LEFT JOIN user_sources u ON u.source_id = s.source_id 
                                   AND u.user_id = ".$this->usr->id." 
                 WHERE ".$sql_where.";";
        $db_con = new mysql;         
        $db_con->usr_id = $this->usr->id;         
        $db_source = $db_con->get1($sql, $debug-5);  
        if ($db_source['source_id'] > 0) {
          $this->id         = $db_source['source_id'];
          $this->usr_cfg_id = $db_source['user_source_id'];
          $this->owner_id   = $db_source['user_id'];
          $this->name       = $db_source['source_name'];
          $this->url        = $db_source['url'];
          $this->comment    = $db_source['comment'];
          $this->type_id    = $db_source['source_type_id'];
          $this->code_id    = $db_source['code_id'];
        } 
        zu_debug('source->load ('.$this->dsp_id().')', $debug-10);
      }  
    }  
  }
    

  // 
  private function type_name($debug) {
    if ($this->type_id > 0) {
      $sql = "SELECT type_name, description
                FROM source_types
               WHERE source_type_id = ".$this->type_id.";";
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      $db_type = $db_con->get1($sql, $debug-5);  
      $this->type_name = $db_type['type_name'];
    }
    return $this->type_name;    
  }
  
  // create an object for the export
  function export_obj ($debug) {
    zu_debug('source->export_obj', $debug-10);
    $result = Null;

    // add the source parameters
    $result->name    = $this->name;
    if ($this->url <> '')                 { $result->url     = $this->url;                 }
    if ($this->comment <> '')             { $result->comment = $this->comment;             }
    if ($this->type_name($debug-1) <> '') { $result->type    = $this->type_name($debug-1); }
    if ($this->code_id <> '')             { $result->code_id = $this->code_id;             }

    zu_debug('source->export_obj -> '.json_encode($result), $debug-18);
    return $result;
  }
  
  // import a source from an object
  function import_obj ($json_obj, $debug) {
    zu_debug('source->import_obj', $debug-10);
    $result = '';
    
    foreach ($json_obj AS $key => $value) {

      if ($key == 'name')    { $this->name    = $value; }
      if ($key == 'url')     { $this->url    = $value; }
      if ($key == 'comment') { $this->comment = $value; }
      /* TODO
      if ($key == 'type')    { $this->type_id = cl($value); }
      if ($key == 'code_id') {
      }
      */
    }
    
    if ($result == '') {
      $this->save($debug-1);
      zu_debug('source->import_obj -> '.$this->dsp_id(), $debug-18);
    } else {
      zu_debug('source->import_obj -> '.$result, $debug-18);
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
      $result .= $this->name.' '; 
      if ($this->id > 0) {
        $result .= '('.$this->id.')';
      }  
    } else {
      $result .= $this->id;
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

  // return the html code to display a source name with the link
  function name_linked ($wrd, $back, $debug) {
    $result = '<a href="/http/source_edit.php?id='.$this->id.'&word='.$wrd->id.'&back='.$back.'">'.$this->name.'</a>';
    return $result;    
  }

  // returns the hmtl code for a source: this is the main function of this lib 
  // source_id is used to force the dislay to a set form; e.g. display the sectors of a company instead of the balance sheet
  // source_type_id is used to .... remove???
  // word_id - id of the starting word to display; can be a single word, a comma seperated list of word ids, a word group or a word tripple
  function display ($wrd, $debug) {
    zu_debug('source->display "'.$wrd->name.'" with the view '.$this->dsp_id().' (type '.$this->type_id.')  for user "'.$this->usr->name.'"', $debug-10);
    $result = '';

    if ($this->id <= 0) {
      zu_err("The source id must be loaded to display it.", "source->display", '', (new Exception)->getTraceAsString(), $this->usr);
    } else {
      // display always the source name in the top right corner and allow the user to edit the source
      $result .= $this->dsp_type_open($debug-1);
      $result .= $this->dsp_navbar($wrd->id, $debug-1);
      $result .= $this->dsp_entries($wrd, $debug-1);
      $result .= $this->dsp_type_close($debug-1);
    }
    zu_debug('source->display ... done', $debug-1);
    
    return $result;
  }
  
  // display a selector for the value source
  function dsp_select($form_name, $back, $debug) {
    zu_debug('source->dsp_select '.$this->dsp_id(), $debug-10);
    $result = ''; // reset the html code var

    // for new values assume the last source used, but not for existing values to enable only changing the value, but not setting the source
    if ($this->id <= 0 and $form_name == "value_add") {
      $this->id = $this->usr->source_id;
    }

    zu_debug("source->dsp_select -> source id used (".$this->id.")", $debug-5);
    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form_name;
    $sel->name       = "source";  
    $sel->sql        = sql_lst_usr ("source", $this->usr, $debug-1);
    $sel->selected   = $this->id;
    $sel->dummy_text = 'please define the source';
    $result .= '      taken from '.$sel->display ($debug-1).' ';
    $result .= '    <td>'.btn_edit ("Rename ".$this->name, '/http/source_edit.php?id='.$this->id.'&back='.$back).'</td>';
    $result .= '    <td>'.btn_add  ("Add new source", '/http/source_add.php?back='.$back).'</td>';
    return $result;
  }

  // display a selector for the source type
  private function dsp_select_type($form_name, $back, $debug) {
    zu_debug("source->dsp_select_type (".$this->id.",".$form_name.",b".$back." and user ".$this->usr->name.")", $debug-10);

    $result = ''; // reset the html code var

    $sel = New selector;
    $sel->usr        = $this->usr;
    $sel->form       = $form_name;
    $sel->name       = "source_type";  
    $sel->sql        = sql_lst ("source_type", $debug-1);
    $sel->selected   = $this->type_id;
    $sel->dummy_text = 'please select the source type';
    $result .= $sel->display ($debug-1);
    return $result;
  }

  // display a html view to change the source name and url
  function dsp_edit ($back, $debug) {
    zu_debug('source->dsp_edit '.$this->dsp_id().' by user '.$this->usr->name, $debug-10);
    $result = '';
    
    if ($this->id <= 0) {
      $script = "source_add";
      $result .= dsp_text_h2("Add source");
    } else {
      $script = "source_edit";
      $result .= dsp_text_h2('Edit source "'.$this->name.'"');
    }  
    $result .= dsp_form_start($script);
    //$result .= dsp_tbl_start();
    $result .= dsp_form_hidden ("id",      $this->id);
    $result .= dsp_form_hidden ("back",    $back);
    $result .= dsp_form_hidden ("confirm", 1);
    $result .= dsp_form_fld    ("name",    $this->name,    "Source name:");
    $result .= '<tr><td>type   </td><td>'. $this->dsp_select_type($script, $back, $debug-1).                    '</td></tr>';
    $result .= dsp_form_fld    ("url",     $this->url,     "URL:");
    $result .= dsp_form_fld    ("comment", $this->comment, "Comment:");
    //$result .= dsp_tbl_end ();
    $result .= dsp_form_end('', $back);

    zu_debug('source->dsp_edit -> done', $debug-1);
    return $result;
  }

  /*
  
  save functions
  
  */

  // true if noone has used this source
  private function not_used($debug) {
    zu_debug('source->not_used ('.$this->id.')', $debug-10);  
    $result = true;
    
    // to review: maybe replace by a database foreign key check
    $result = $this->not_changed($debug-1);
    return $result;
  }

  // true if no other user has modified the source
  private function not_changed($debug) {
    zu_debug('source->not_changed ('.$this->id.') by someone else than the onwer ('.$this->owner_id.')', $debug-10);  
    $result = true;
    
    $change_user_id = 0;
    if ($this->owner_id > 0) {
      $sql = "SELECT user_id 
                FROM user_sources 
               WHERE source_id = ".$this->id."
                 AND user_id <> ".$this->owner_id."
                 AND (excluded <> 1 OR excluded is NULL)";
    } else {
      $sql = "SELECT user_id 
                FROM user_sources 
               WHERE source_id = ".$this->id."
                 AND (excluded <> 1 OR excluded is NULL)";
    }
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_row = $db_con->get1($sql, $debug-5);  
    $change_user_id = $db_row['user_id'];
    if ($change_user_id > 0) {
      $result = false;
    }
    zu_debug('source->not_changed for '.$this->id.' is '.zu_dsp_bool($result), $debug-10);  
    return $result;
  }

  // true if the user is the owner and noone else has changed the source
  // because if another user has changed the source and the original value is changed, maybe the user source also needs to be updated
  function can_change($debug) {
    zu_debug('source->can_change ('.$this->id.',u'.$this->usr->id.')', $debug-10);  
    $can_change = false;
    if ($this->owner_id == $this->usr->id OR $this->owner_id <= 0) {
      $can_change = true;
    }  

    zu_debug('source->can_change -> ('.zu_dsp_bool($can_change).')', $debug-10);  
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

  // create a database record to save user specific settings for this source
  private function add_usr_cfg($debug) {
    $result = '';

    if (!$this->has_usr_cfg) {
      zu_debug('source->add_usr_cfg for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);

      // check again if there ist not yet a record
      $sql = "SELECT source_id FROM `user_sources` WHERE source_id = ".$this->id." AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $db_row = $db_con->get1($sql, $debug-5);  
      $usr_db_id = $db_row['user_id'];
      if ($usr_db_id <= 0) {
        // create an entry in the user sandbox
        $db_con->type = 'user_source';
        $log_id = $db_con->insert('source_id, user_id', $this->id.",".$this->usr->id, $debug-1);
        if ($log_id <= 0) {
          $result .= 'Insert of user_source failed.';
        }
      }  
    }  
    return $result;
  }

  // check if the database record for the user specific settings can be removed
  private function del_usr_cfg_if_not_needed($debug) {
    $result = '';
    zu_debug('source->del_usr_cfg_if_not_needed pre check for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-12);

    //if ($this->has_usr_cfg) {

      // check again if there ist not yet a record
      $sql = "SELECT source_id,
                     source_name,
                     comment,
                     source_type_id
                FROM user_sources
               WHERE source_id = ".$this->id." 
                 AND user_id = ".$this->usr->id.";";
      $db_con = New mysql;
      $db_con->usr_id = $this->usr->id;         
      $usr_wrd_cfg = $db_con->get1($sql, $debug-5);  
      zu_debug('source->del_usr_cfg_if_not_needed check for "'.$this->dsp_id().' und user '.$this->usr->name.' with ('.$sql.')', $debug-12);
      if ($usr_wrd_cfg['source_id'] > 0) {
        if ($usr_wrd_cfg['comment']      == ''
        AND $usr_wrd_cfg['source_type_id'] == Null) {
          // delete the entry in the user sandbox
          zu_debug('source->del_usr_cfg_if_not_needed any more for "'.$this->dsp_id().' und user '.$this->usr->name, $debug-10);
          $db_con->type = 'user_source';
          $result .= $db_con->delete(array('source_id','user_id'), array($this->id,$this->usr->id), $debug-1);
          if (str_replace('1','',$result) <> '') {
            $result .= 'Deletion of user_source failed.';
          }
        }  
      }  
    //}  
    return $result;
  }

  // set the log entry parameter for a new value
  private function log_add($debug) {
    zu_debug('source->log_add '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'add';
    $log->table     = 'sources';
    $log->field     = 'source_name';
    $log->old_value = '';
    $log->new_value = $this->name;
    $log->row_id    = 0; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // set the main log entry parameters for updating one source field
  private function log_upd($debug) {
    zu_debug('source->log_upd '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'update';
    if ($this->can_change($debug-1)) {
      $log->table   = 'sources';
    } else {  
      $log->table   = 'user_sources';
    }
    
    return $log;    
  }
  
  // set the log entry parameter to delete a source
  private function log_del($debug) {
    zu_debug('source->log_del '.$this->dsp_id().' for user '.$this->usr->name, $debug-10);
    $log = New user_log;
    $log->usr_id    = $this->usr->id;  
    $log->action    = 'del';
    $log->table     = 'sources';
    $log->field     = 'source_name';
    $log->old_value = $this->name;
    $log->new_value = '';
    $log->row_id    = $this->id; 
    $log->add($debug-1);
    
    return $log;    
  }
  
  // actually update a formula field in the main database record or the user sandbox
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
        $db_con->type = 'user_source';
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
  
  // set the update parameters for the source url
  private function save_field_url($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->url <> $this->url) {
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->url;
      $log->new_value = $this->url;
      $log->std_value = $std_rec->url;
      $log->row_id    = $this->id; 
      $log->field     = 'url';
      $result .= $this->save_field_do($db_con, $log, $debug-1);
    }
    return $result;
  }
  
  // set the update parameters for the source comment
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
      $log->field     = 'source_type_id';
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
        $db_con->type = 'source';
        $result .= $db_con->update($this->id, $log->field, $new_value, $debug-1);
      } else {
        if (!$this->has_usr_cfg($debug-1)) { $this->add_usr_cfg($debug-1); }
        $db_con->type = 'user_source';
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
  
  // save all updated source fields excluding the name, because already done when adding a source
  private function save_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    $result .= $this->save_field_url    ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_comment($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_type   ($db_con, $db_rec, $std_rec, $debug-1);
    $result .= $this->save_field_excluded ($db_con, $db_rec, $std_rec, $debug-1);
    zu_debug('source->save_fields all fields for '.$this->dsp_id().' has been saved', $debug-12);
    return $result;
  }
  
  // updated the source name (which is the id field)
  // should only be called if the user is the owner and nobody has used the display component link
  private function save_id_fields($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    if ($db_rec->name <> $this->name) {
      zu_debug('source->save_id_fields to '.$this->dsp_id().' from "'.$db_rec->dsp_id().'" (standard '.$std_rec->dsp_id().')', $debug-10);
      $log = $this->log_upd($debug-1);
      $log->old_value = $db_rec->name;
      $log->new_value = $this->name;
      $log->std_value = $std_rec->name;
      $log->row_id    = $this->id; 
      $log->field     = 'source_name';
      if ($log->add($debug-1)) {
        $result .= $db_con->update($this->id, array("source_name"),
                                              array($this->name), $debug-1);
      }
    }
    zu_debug('source->save_id_fields for '.$this->dsp_id().' has been done', $debug-12);
    return $result;
  }
  
  // check if the id parameters are supposed to be changed 
  private function save_id_if_updated($db_con, $db_rec, $std_rec, $debug) {
    $result = '';
    
    if ($db_rec->name <> $this->name) {
      // check if target link already exists
      zu_debug('source->save_id_if_updated check if target link already exists '.$this->dsp_id().' (has been "'.$db_rec->dsp_id().'")', $debug-14);
      $db_chk = clone $this;
      $db_chk->id = 0; // to force the load by the id fields
      $db_chk->load_standard($debug-10);
      if ($db_chk->id > 0) {
        if (UI_CAN_CHANGE_VIEW_COMPONENT_NAME) {
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
          zu_debug('source->save_id_if_updated found a display component link with target ids "'.$db_chk->dsp_id().'", so del "'.$db_rec->dsp_id().'" and add '.$this->dsp_id(), $debug-14);
        } else {
          $result .= 'A source with the name "'.$this->name.'" already exists. Please use another name.';
        }  
      } else {
        if ($this->can_change($debug-1) AND $this->not_used($debug-1)) {
          // in this case change is allowed and done
          zu_debug('source->save_id_if_updated change the existing display component link '.$this->dsp_id().' (db "'.$db_rec->dsp_id().'", standard "'.$std_rec->dsp_id().'")', $debug-14);
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
          zu_debug('source->save_id_if_updated recreate the display component link del "'.$db_rec->dsp_id().'" add '.$this->dsp_id().' (standard "'.$std_rec->dsp_id().'")', $debug-14);
        }
      }
    }  

    zu_debug('source->save_id_if_updated for '.$this->dsp_id().' has been done', $debug-12);
    return $result;
  }
  
  // create a new source
  private function add($db_con, $debug) {
    zu_debug('source->add the source '.$this->dsp_id(), $debug-12);
    $result = '';
    
    // log the insert attempt first
    $log = $this->log_add($debug-1);
    if ($log->id > 0) {
      // insert the new source
      $this->id = $db_con->insert(array("source_name","user_id"), array($this->name,$this->usr->id), $debug-1);
      if ($this->id > 0) {
        // update the id in the log
        $result .= $log->add_ref($this->id, $debug-1);

        // create an empty db_rec element to force saving of all set fields
        $db_rec = New source;
        $db_rec->name = $this->name;
        $db_rec->usr  = $this->usr;
        $std_rec = clone $db_rec;
        // save the source fields
        $result .= $this->save_fields($db_con, $db_rec, $std_rec, $debug-1);

      } else {
        zu_err("Adding source ".$this->name." failed.", "source->save");
      }
    }  
        
    return $result;
  }
  
  // update a source in the database or create a user source
  function save($debug) {
    zu_debug('source->save '.$this->dsp_id().' for user '.$this->usr->id, $debug-10);
    $result = "";
    
    // build the database object because the is anyway needed
    $db_con = new mysql;         
    $db_con->usr_id = $this->usr->id;         
    $db_con->type   = 'source';         
    
    // check if a new value is supposed to be added
    if ($this->id <= 0) {
      // check if a source with the same name is already in the database
      zu_debug('source->save check if a source named '.$this->dsp_id().' already exists', $debug-12);
      $db_chk = New source;
      $db_chk->name = $this->name;
      $db_chk->usr  = $this->usr;
      $db_chk->load($debug-1);
      if ($db_chk->id > 0) {
        $this->id = $db_chk->id;
      }
    }  
      
    // create a new source or update an existing
    if ($this->id <= 0) {
      $result .= $this->add($db_con, $debug-1);
    } else {  
      zu_debug('source->save update "'.$this->id.'"', $debug-12);
      // read the database values to be able to check if something has been changed; done first, 
      // because it needs to be done for user and general formulas
      $db_rec = New source;
      $db_rec->id  = $this->id;
      $db_rec->usr = $this->usr;
      $db_rec->load($debug-1);
      zu_debug('source->save -> database source "'.$db_rec->name.'" ('.$db_rec->id.') loaded', $debug-14);
      $std_rec = New source;
      $std_rec->id = $this->id;
      $std_rec->usr = $this->usr; // must also be set to allow to take the ownership
      $std_rec->load_standard($debug-1);
      zu_debug('source->save -> standard source settings for "'.$std_rec->name.'" ('.$std_rec->id.') loaded', $debug-14);
      
      // for a correct user source detection (function can_change) set the owner even if the source has not been loaded before the save 
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

  // delete the complete source (the calling function del must have checked that no one uses this source)
  private function del_exe($debug) {
    zu_debug('source->del_exe', $debug-16);
    $result = '';

    $log = $this->log_del($debug-1);
    if ($log->id > 0) {
      $db_con = new mysql;         
      $db_con->usr_id = $this->usr->id;         
      // delete first all user configuration that have also been excluded
      $db_con->type = 'user_source';
      $result .= $db_con->delete(array('source_id','excluded'), array($this->id,'1'), $debug-1);
      $db_con->type   = 'source';         
      $result .= $db_con->delete('source_id', $this->id, $debug-1);
    }
    
    return $result;    
  }
  
  // exclude or delete a source
  function del($debug) {
    zu_debug('source->del', $debug-16);
    $result = '';
    $result .= $this->load($debug-1);
    if ($this->id > 0 AND $result == '') {
      zu_debug('source->del '.$this->dsp_id(), $debug-14);
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
