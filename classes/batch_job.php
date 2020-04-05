<?php

/*

  batch_job.php - object to combine all parameters for one calculation or cleanup request
  -------------
  
  This may lead to several formula values, 
  
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

/*

Changes on these objects can trigger a batch job:
  1. values
  2. formulas
  3. formula links
  4. word links
  
To update the formula results the main actions are
  A) create the formula results (or delete formula results not valid any more)
  B) calculate und update the formula results
  C) create the depending formula results (or delete if not valid any more)
  D) calculate und update the depending formula results
  
A add, update or delete on an object always triggers all action from A) to D)
  excecpt the update of a value, which for which A) is not needed
  
If the change influences the standard result additional to the user value the standard value needs to be updated
If the user has done no modifications only the standard value needs to be updated
Because the calculation dependencies can be complex always both cases (userspecific and standard) are calculated but onöy the result needed is saved


One Sample

A user updates a formula
 -> update the formula results for this user and this formula
    -> get all values and create a calculation request (phrase_group_list->get_by_val_with_one_phr_each)
      -> get based on the assigned words and used words
    -> get all formula results and create a calculation request
      -> get all depending formulas
      -> based on the formula
      -> exclude / delete formula results????
    -> create all depending calculatio requests
    -> sort the calculation request by dependency and priority
    -> execute the calculation requests

*/

class batch_job {

  // database fields
  public $id           = NULL;  // the database id of the request
  public $request_time = NULL;  // time when the job has been requested
  public $start_time   = NULL;  // start time of the job execution 
  public $end_time     = NULL;  // end time of the job execution 
  public $usr          = NULL;  // the user who as done the request and whos data needs to be updated
  public $type         = NULL;  // "update value", "add formula" or ... reference to the type table
  public $row_id       = NULL;  // the id of the related object e.g. if a value has been updated the value_id
  
  // in memory only fields
  public $obj          = NULL;  // the updated object 

  // for calculation request a simple phrase list is used 
  // not phrase groups and time because the phrase group and time splitting should only be used to save to the database
  public $frm     = NULL; // the formula object that should be used for updating the result 
  public $phr_lst = NULL; // 

  
  // request a new calculation 
  function add($debug) {
    $result = '';
    zu_debug('batch_job->add', $debug-18);      
    // create first the database entry to make sure the update is done
    if ($this->type <= 0) {
      // invalid type?
    } else {
      zu_debug('batch_job->type ok', $debug-18);      
      if ($this->row_id <= 0) {
        if (isset($this->obj)) {
          $this->row_id = $this->obj->id;
        }  
      } 
      if ($this->row_id <= 0) {
        // row_id missing
      } else {
        zu_debug('batch_job->row_id ok', $debug-18);      
        if (isset($this->obj)) {
          if (!isset($this->usr)) { $this->usr = $this->obj->usr; }
          $this->row_id = $this->obj->id;
          zu_debug('batch_job->add connect', $debug-18);      
          $db_con = New mysql;
          $db_con->usr_id = $this->usr->id;         
          $db_con->type = 'calc_and_cleanup_task';         
          $job_id = $db_con->insert(array('user_id','request_time','calc_and_cleanup_task_type_id','row_id'), 
                                    array($this->usr->id, 'Now()', $this->type, $this->row_id), $debug-1);
          $this->request_time = new DateTime();
          
          // execute the job if possible
          if ($job_id > 0) {
            $this->id = $job_id;
            $this->exe($debug-1);
            $result = $job_id;
          }
        }
      }
    }
    zu_debug('batch_job->add done', $debug-18);  
    return $result;
  }
  
  // update all result depending on one value
  function exe_val_upd($debug) {
    zu_debug('batch_job->exe_val_upd ...', $debug-18);      
    // load all depending formula results
    if (isset($this->obj)) {
      zu_debug('batch_job->exe_val_upd -> get list for user '.$this->obj->usr->name, $debug-16);      
      $fv_lst = $this->obj->fv_lst_depending($debug-1);
      if (isset($fv_lst)) {
        zu_debug('batch_job->exe_val_upd -> got '.$fv_lst->dsp_id(), $debug-14);      
        foreach ($fv_lst->lst AS $fv) {
          zu_debug('batch_job->exe_val_upd -> update '.get_class($fv).' '.$fv->dsp_id(), $debug-12);      
          $fv->update($debug-1);
          zu_debug('batch_job->exe_val_upd -> update '.get_class($fv).' '.$fv->dsp_id().' done', $debug-12);      
        }
      }
    }
    
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_con->type = 'calc_and_cleanup_task';         
    $result .= $db_con->update($this->id, 'end_time', 'Now()', $debug-1);
  
    zu_debug('batch_job->exe_val_upd -> done', $debug-10);      
  }
  
  // execute all open requests
  function exe($debug) {
    $db_con = New mysql;
    $db_con->usr_id = $this->usr->id;         
    $db_con->type = 'calc_and_cleanup_task';         
    $result .= $db_con->update($this->id, 'start_time', 'Now()', $debug-1);
      
    zu_debug('batch_job->exe -> '.$this->type, $debug-14);      
    if ($this->type == cl(DBL_JOB_VALUE_UPDATE)) {
      $this->exe_val_upd($debug-1);
    } else {
      zu_err('Job type "'.$this->type.'" not defined.','batch_job->exe', '', (new Exception)->getTraceAsString(), $this->usr);
    }
  }
  
  // remove the old requests from the database if they are closed since a while
  private function del($debug) {
  }
  
  /*
  
  display functions
  
  */
  
  // return best possible identification for this formula mainly used for debugging
  function dsp_id ($debug) {
    $result = $this->type; 

    if ($this->row_id > 0) {
      $result .= ' for id '.$this->row_id;
    }
    if (isset($this->frm)) {
      $result .= ' '.$this->frm->dsp_id($debug-1);
    }
    if (isset($this->phr_lst)) {
      $result .= ' '.$this->phr_lst->dsp_id($debug-1);
    }
    if ($this->id > 0) {
      $result .= ' ('.$this->id.')';
    }
    if (isset($this->usr)) {
      $result .= ' for user '.$this->usr->id.' ('.$this->usr->name.')';
    }
    return $result;
  }

}

?>
