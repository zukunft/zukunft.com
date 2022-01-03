<?php

/*

  batch_job_list.php - a list of calculation request
  ------------------
  
  This list in "in memory only" to wrap the communication between the classes
  E.g. if a formula is updated it may lead to many single formula result calculations, 
       which may lead to other batch jobs
       to have consistent results no updates after the cut-off time are included
  
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

class batch_job_list
{

    public ?array $lst = null;              // list of the batch jobs e.g. calculation requests
    public ?user $usr = null;               // the user who has done the request and whose data needs to be updated
    public ?DateTime $cut_off_time = null;  //


    /**
     * add another job to the list, but only if needed
     */
    function add($job)
    {
        $result = '';
        log_debug('batch_job_list->add');

        // check if the job to add has all needed parameters
        if (!isset($job->frm)) {
            log_err('Job ' . $job->dsp_id() . ' cannot be added, because formula is missing.', 'batch_job_list->add');
        } elseif (!isset($job->phr_lst)) {
            log_err('Job ' . $job->dsp_id() . ' cannot be added, because no words or triples are defined.', 'batch_job_list->add');
        } else {

            // check if a similar job is already in the list
            $found = false;
            // build the
            $chk_phr_lst_ids = array();
            if ($this->lst != null) {
                foreach ($this->lst as $chk_job) {
                    $chk_phr_lst_ids = $chk_job->phr_lst->id();
                }
                foreach ($this->lst as $chk_job) {
                    if ($chk_job->frm == $job->frm) {
                        if ($chk_job->usr == $job->usr) {
                            if (in_array($chk_job->phr_lst->id(), $chk_phr_lst_ids)) {
                                $found = true;
                            }
                        }
                    }
                }
            }
            if (!$found) {
                $this->lst[] = $job;
                $result = 1;
            }
        }
        log_debug('batch_job_list->add done');
        return $result;
    }

    function merge($job_lst)
    {
        foreach ($job_lst->lst as $job) {
            $this->add($job);
        }
    }

}