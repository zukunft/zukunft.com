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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

use api\batch_job_list_api;

class batch_job_list extends base_list
{

    /*
     * object vars
     */

    // array $lst is the list of the batch jobs e.g. calculation requests
    protected user $usr; // the user who has done the request and whose data needs to be updated
    public ?DateTime $cut_off_time = null;  //


    /*
     * construct and map
     */

    /**
     * always set the user because a batch list either user specific or linked to the system user
     * @param user $usr the user who requested to see the formulas
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->lst = array();
        $this->usr = $usr;
    }


    /*
     * cast
     */

    /**
     * @return batch_job_list_api the job list object with the display interface functions
     */
    function api_obj(): batch_job_list_api
    {
        $api_obj = new batch_job_list_api();
        foreach ($this->lst as $wrd) {
            $api_obj->add($wrd->api_obj());
        }
        return $api_obj;
    }


    /*
     * load interface
     */

    /**
     * load a list of batch jobs of the given type
     * @param string $type_code_id the code id of the job type that should be loaded
     * @return bool true if at least one open job found
     */
    function load_by_type(string $type_code_id = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_type($db_con, $type_code_id);
        return $this->load($qp);
    }


    /*
     * load internals
     */

    /**
     * prepare sql to get all open job of one type
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param string $type_code_id the code id of the job type that should be loaded
     * @return sql_par
     */
    function load_sql_by_type(sql_db $db_con, string $type_code_id = ''): sql_par
    {
        global $job_types;
        $type_id = $job_types->id($type_code_id);
        $job = new batch_job($this->usr);
        $qp = $job->load_sql($db_con, 'job_type', self::class);
        $db_con->set_page($this->limit, $this->offset());
        $db_con->add_par(sql_db::PAR_INT, $type_id);
        $qp->sql = $db_con->select_by_field(batch_job::FLD_TYPE);
        $qp->par = $db_con->get_par();
        return $qp;
    }

    /**
     * load this list of jobs
     * @param sql_par $qp the SQL statement, the unique name of the SQL statement and the parameter list
     * @return bool true if at least one change found
     */
    private function load(sql_par $qp): bool
    {
        global $db_con;
        $result = false;

        if ($qp->name == '') {
            log_err('The query name cannot be created to load a ' . self::class, self::class . '->load');
        } else {
            $db_rows = $db_con->get($qp);
            if ($db_rows != null) {
                foreach ($db_rows as $db_row) {
                    $job = new batch_job($this->usr);
                    $job->row_mapper($db_row);
                    $this->lst[] = $job;
                    $result = true;
                }
            }
        }

        return $result;
    }


    /*
     * modify
     */

    /**
     * add another job to the list, but only if needed
     * @param batch_job $job the batch job, that should be added to the list
     * @return user_message either the status ok or the error message that should be shown to the user
     */
    function add(batch_job $job): user_message
    {
        $result = new user_message();
        log_debug('batch_job_list->add');

        // check if the job to add has all needed parameters
        if ($job->type_code_id() != job_type_list::BASE_IMPORT) {
            if (!isset($job->frm)) {
                $msg = 'Job ' . $job->dsp_id() . ' cannot be added, because formula is missing.';
                $result->add_message($msg);
            } elseif (!isset($job->phr_lst)) {
                $msg = 'Job ' . $job->dsp_id() . ' cannot be added, because no words or triples are defined.';
                $result->add_message($msg);
            }
        }

        // do not add similar jobs
        if ($result->is_ok()) {
            $result->add($this->has_similar($job));
        }

        // finally add the job to the list if everything has been fine
        if ($result->is_ok()) {
            $this->lst[] = $job;
        }

        log_debug('done');
        return $result;
    }

    /**
     * check if a similar job is already in the list
     * @param batch_job $job the batch job, that should be checked
     * @return user_message ok if no similar job has been in the list
     *                      or the message for the user
     */
    private function has_similar(batch_job $job): user_message
    {
        $result = new user_message();

        // build the list of phrase ids
        $chk_phr_lst_ids = array();
        foreach ($this->lst as $chk_job) {
            $chk_phr_lst_ids = $chk_job->phr_lst->id();
        }

        foreach ($this->lst as $chk_job) {
            if ($chk_job->frm == $job->frm) {
                if ($chk_job->usr == $job->usr) {
                    if (in_array($chk_job->phr_lst->id(), $chk_phr_lst_ids)) {
                        $msg = 'Job for phrases ' . $chk_job->phr_lst->name() . ' is already in the list of active jobs';
                        $result->add_message($msg);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * merge all jobs of the given batch job list to this list
     */
    function merge(batch_job_list $job_lst): void
    {
        foreach ($job_lst->lst as $job) {
            $this->add($job);
        }
    }

}