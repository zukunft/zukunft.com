<?php

/*

  batch_job_list.php - a list of calculation request
  ------------------
  
  This list in "in memory only" to wrap the communication between the classes
  E.g. if a formula is updated it may lead to many single formula result calculations, 
       which may lead to other batch jobs
       to have consistent results no updates after the cut off time are included
  
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

class batch_job_list {

  public $lst          = array(); // list of the batch jobs e.g. calulation requests
  public $cut_off_time = Null;    // 
  
  
  // add another job to the list, but only if needed
  function add($job, $debug) {
    $result = '';
    zu_debug('batch_job_list->add.', $debug-18);    
    
    // check if the job to add has all needed parameters
    if (!isset($job->frm)) {
      zu_err('Job '.$job->dsp_id().' cannot be added, because formula is missing.','batch_job_list->add', '', (new Exception)->getTraceAsString(), $this->usr);
    } elseif (!isset($job->phr_lst)) {
      zu_err('Job '.$job->dsp_id().' cannot be added, because no words or triples are defined.','batch_job_list->add', '', (new Exception)->getTraceAsString(), $this->usr);
    } else {

      // check if a similar job is already in the list
      $found = false;
      // build the
      $chk_phr_lst_ids = array();
      foreach ($this->lst AS $chk_job) {
        $chk_phr_lst_ids = $chk_job->phr_lst->id($debug-1);
      }  
      foreach ($this->lst AS $chk_job) {
        if ($chk_job->frm == $job->frm) {
          if ($chk_job->usr == $job->usr) {
            if (in_array($chk_job->phr_lst->id($debug-1),$chk_phr_lst_ids)) {
              $found = true;
            }
          }
        }
      }
      if (!$found) {
        $this->lst[] = $job;
        $result = 1;
      }  
    }
    zu_debug('batch_job_list->add done.', $debug-18);      
    return $result;
  }
    
  function merge($job_lst, $debug) {
    foreach ($job_lst->lst AS $job) {
      $this->add($job, $debug-1);
    }
  }
  
}

?>
