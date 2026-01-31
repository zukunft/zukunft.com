<?php

/*

    model/system/job_list.php - a list of calculation request
    -------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'list_db_write.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::MODEL_SYSTEM . 'list_db_write.php';
include_once paths::MODEL_SYSTEM . 'job.php';
include_once paths::MODEL_SYSTEM . 'job_status_list.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'job_types.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use DateTime;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;

class job_list extends list_db_write
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
     * always set the user because a job list either user-specific or linked to the system user
     * @param user $usr the user who requested to see the formulas
     */
    function __construct(user $usr)
    {
        parent::__construct();
        $this->reset();
        $this->usr = $usr;
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
        $qp = $this->load_sql_by_type($db_con->sql_creator(), $type_code_id);
        return $this->load($qp);
    }

    /**
     * load a list of batch jobs of the given status
     * @param string $status_code_id the code id of the job status that should be loaded
     * @return bool true if at least one open job found
     */
    function load_by_status(string $status_code_id = ''): bool
    {
        global $db_con;
        $qp = $this->load_sql_by_status($db_con->sql_creator(), $status_code_id);
        return $this->load($qp);
    }


    /*
     * load internals
     */

    /**
     * prepare sql to get all open jobs of one type
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $type_code_id the code id of the job type that should be loaded
     * @return sql_par
     */
    function load_sql_by_type(sql_creator $sc, string $type_code_id = ''): sql_par
    {
        global $sys;
        $type_id = $sys->typ_lst->job_typ->id($type_code_id);
        $job = new job($this->usr);
        $qp = $job->load_sql($sc, 'job_type', self::class);
        $sc->add_where(job_db::FLD_TYPE, $type_id);
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * prepare sql to get all open jobs of one status
     *
     * @param sql_creator $sc with the target db_status set
     * @param string $status_code_id the code id of the job status that should be loaded
     * @return sql_par
     */
    function load_sql_by_status(sql_creator $sc, string $status_code_id = ''): sql_par
    {
        global $sys;
        $status_id = $sys->typ_lst->job_sta->id($status_code_id);
        $job = new job($this->usr);
        $qp = $job->load_sql($sc, 'job_status', self::class);
        $sc->add_where(job_db::FLD_STATUS, $status_id);
        $sc->set_page($this->limit, $this->offset());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
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
                    $job = new job($this->usr);
                    $job->row_mapper($db_row);
                    $this->add_obj($job);
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
     * @param job $job the batch job, that should be added to the list
     * @return user_message either the status ok or the error message that should be shown to the user
     */
    function add(job $job): user_message
    {
        $usr_msg = new user_message();
        log_debug('job_list->add');

        // check if the job to add has all needed parameters
        if ($job->type_code_id() != job_types::BASE_IMPORT) {
            if (!isset($job->frm)) {
                $usr_msg->add_id_with_vars(msg_id::JOB_FORMULA_MISSING, [msg_id::VAR_ID => $job->dsp_id()]);
            } elseif (!isset($job->phr_lst)) {
                $usr_msg->add_id_with_vars(msg_id::JOB_WORD_MISSING, [msg_id::VAR_ID => $job->dsp_id()]);
            }
        }

        // do not add similar jobs
        if ($usr_msg->is_ok()) {
            $usr_msg->merge($this->has_similar($job));
        }

        // finally add the job to the list if everything has been fine
        if ($usr_msg->is_ok()) {
            $this->add_obj($job);
        }

        log_debug('done');
        return $usr_msg;
    }

    /**
     * check if a similar job is already in the list
     * @param job $job the batch job, that should be checked
     * @return user_message ok if no similar job has been in the list
     *                      or the message for the user
     */
    private function has_similar(job $job): user_message
    {
        $usr_msg = new user_message();

        // build the list of phrase ids
        $chk_phr_lst_ids = array();
        foreach ($this->lst() as $chk_job) {
            $chk_phr_lst_ids = $chk_job->phr_lst->id();
        }

        foreach ($this->lst() as $chk_job) {
            if ($chk_job->frm == $job->frm) {
                if ($chk_job->usr == $job->get_user()) {
                    if (in_array($chk_job->phr_lst->id(), $chk_phr_lst_ids)) {
                        $usr_msg->add_id_with_vars(msg_id::JOB_ALREADY_ACTIVE, [msg_id::VAR_NAME => $chk_job->phr_lst->name()]);
                    }
                }
            }
        }
        return $usr_msg;
    }

    /**
     * merge all jobs of the given batch job list to this list
     */
    function merge(job_list $job_lst): void
    {
        foreach ($job_lst->lst() as $job) {
            $this->add($job);
        }
    }

}