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
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
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
  except the update of a value, which for which A) is not needed
  
If the change influences the standard result additional to the user value the standard value needs to be updated
If the user has done no modifications only the standard value needs to be updated
Because the calculation dependencies can be complex always both cases (user specific and standard) are calculated but only the result needed is saved


One Sample

A user updates a formula
 -> update the formula results for this user and this formula
    -> get all values and create a calculation request (phrase_group_list->get_by_val_with_one_phr_each)
      -> get based on the assigned words and used words
    -> get all formula results and create a calculation request
      -> get all depending formulas
      -> based on the formula
      -> exclude / delete formula results????
    -> create all depending calculation requests
    -> sort the calculation request by dependency and priority
    -> execute the calculation requests

*/

class batch_job
{

    // database fields
    public ?int $id = null;  // the database id of the request
    public ?DateTime $request_time = null;  // time when the job has been requested
    public ?DateTime $start_time = null;    // start time of the job execution
    public ?DateTime $end_time = null;      // end time of the job execution
    public ?user $usr = null;               // the user who has done the request and whose data needs to be updated
    public ?string $type = null;            // "update value", "add formula" or ... reference to the type table
    public ?int $row_id = null;             // the id of the related object e.g. if a value has been updated the value_id

    // in memory only fields
    public ?object $obj = null;             // the updated object

    // for calculation request a simple phrase list is used
    // not phrase groups and time because the phrase group and time splitting should only be used to save to the database
    public ?formula $frm = null;           // the formula object that should be used for updating the result
    public ?phrase_list $phr_lst = null;   //


    // request a new calculation
    function add(): int
    {

        global $db_con;

        $result = 0;
        log_debug('batch_job->add');
        // create first the database entry to make sure the update is done
        if ($this->type <= 0) {
            // invalid type?
            log_debug('batch_job->type invalid');
        } else {
            log_debug('batch_job->type ok');
            if ($this->row_id <= 0) {
                if (isset($this->obj)) {
                    $this->row_id = $this->obj->id;
                }
            }
            if ($this->row_id <= 0) {
                log_debug('batch_job->add row id missing?');
            } else {
                log_debug('batch_job->row_id ok');
                if (isset($this->obj)) {
                    if (!isset($this->usr)) {
                        $this->usr = $this->obj->usr;
                    }
                    $this->row_id = $this->obj->id;
                    log_debug('batch_job->add connect');
                    //$db_con = New mysql;
                    $db_type = $db_con->get_type();
                    $db_con->set_type(DB_TYPE_TASK);
                    $db_con->set_usr($this->usr->id);
                    $job_id = $db_con->insert(array('user_id', 'request_time', 'calc_and_cleanup_task_type_id', 'row_id'),
                        array($this->usr->id, 'Now()', $this->type, $this->row_id));
                    $this->request_time = new DateTime();

                    // execute the job if possible
                    if ($job_id > 0) {
                        $this->id = $job_id;
                        $this->exe();
                        $result = $job_id;
                    }
                    $db_con->set_type($db_type);
                }
            }
        }
        log_debug('batch_job->add done');
        return $result;
    }

    // update all result depending on one value
    function exe_val_upd()
    {
        log_debug('batch_job->exe_val_upd ...');
        global $db_con;

        // load all depending formula results
        if (isset($this->obj)) {
            log_debug('batch_job->exe_val_upd -> get list for user ' . $this->obj->usr->name);
            $fv_lst = $this->obj->fv_lst_depending();
            if ($fv_lst != null) {
                log_debug('batch_job->exe_val_upd -> got ' . $fv_lst->dsp_id());
                if ($fv_lst->lst != null) {
                    foreach ($fv_lst->lst as $fv) {
                        log_debug('batch_job->exe_val_upd -> update ' . get_class($fv) . ' ' . $fv->dsp_id());
                        $fv->update();
                        log_debug('batch_job->exe_val_upd -> update ' . get_class($fv) . ' ' . $fv->dsp_id() . ' done');
                    }
                }
            }
        }

        //$db_con = New mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_TASK);
        $db_con->usr_id = $this->usr->id;
        $result = $db_con->update($this->id, 'end_time', 'Now()');
        $db_con->set_type($db_type);

        log_debug('batch_job->exe_val_upd -> done with ' . $result);
    }

    // execute all open requests
    function exe()
    {
        global $db_con;
        //$db_con = New mysql;
        $db_type = $db_con->get_type();
        $db_con->usr_id = $this->usr->id;
        $db_con->set_type(DB_TYPE_TASK);
        $result = $db_con->update($this->id, 'start_time', 'Now()');

        log_debug('batch_job->exe -> ' . $this->type . ' with ' . $result);
        if ($this->type == cl(db_cl::JOB_TYPE, job_type_list::VALUE_UPDATE)) {
            $this->exe_val_upd();
        } else {
            log_err('Job type "' . $this->type . '" not defined.', 'batch_job->exe');
        }
        $db_con->set_type($db_type);
    }

    // remove the old requests from the database if they are closed since a while
    private function del()
    {
    }

    /*

    display functions

    */

    // return best possible identification for this formula mainly used for debugging
    function dsp_id(): string
    {
        $result = $this->type;

        if ($this->row_id > 0) {
            $result .= ' for id ' . $this->row_id;
        }
        if (isset($this->frm)) {
            if (get_class($this->frm) == 'formula') {
                $result .= ' ' . $this->frm->dsp_id();
            } else {
                $result .= ' ' . get_class($this->frm) . ' ' . $this->frm->dsp_id();
            }
        }
        if (isset($this->phr_lst)) {
            if (get_class($this->phr_lst) == 'phrase_list') {
                $result .= ' ' . $this->phr_lst->dsp_id();
            } else {
                $result .= ' ' . get_class($this->phr_lst) . ' ' . $this->phr_lst->dsp_id();
            }
        }
        if ($this->id > 0) {
            $result .= ' (' . $this->id . ')';
        }
        if (isset($this->usr)) {
            $result .= ' for user ' . $this->usr->id . ' (' . $this->usr->name . ')';
        }
        return $result;
    }

    function name(): string
    {
        $result = $this->type;

        if (isset($this->frm)) {
            if (get_class($this->frm) == 'formula') {
                $result .= $this->frm->name();
            } else {
                $result .= get_class($this->frm) . ' ' . $this->frm->name();
            }
        }
        if (isset($this->phr_lst)) {
            if (get_class($this->phr_lst) == 'phrase_list') {
                $result .= ' ' . $this->phr_lst->name();
            } else {
                $result .= ' ' . get_class($this->phr_lst) . ' ' . $this->phr_lst->name();
            }
        }
        return $result;
    }

}