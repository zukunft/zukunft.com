<?php

/*

    model/system/batch_job.php - object to combine all parameters for one calculation or cleanup request
    --------------------------

    This may lead to several results,

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

/*

Changes on these objects can trigger a batch job:
  1. values
  2. formulas
  3. formula links
  4. word links
  
To update the formula results the main actions are
  A) create the formula results (or delete formula results not valid anymore)
  B) calculate und update the formula results
  C) create the depending on formula results (or delete if not valid anymore)
  D) calculate und update the depending on formula results
  
A add, update or delete on an object always triggers all action from A) to D)
  except the update of a value, which for which A) is not needed
  
If the change influences the standard result additional to the user value the standard value needs to be updated
If the user has done no modifications only the standard value needs to be updated
Because the calculation dependencies can be complex always both cases (user specific and standard) are calculated but only the result needed is saved


One Sample

A user updates a formula
 -> update the formula results for this user and this formula
    -> get all values and create a calculation request (group_list->get_by_val_with_one_phr_each)
      -> get based on the assigned words and used words
    -> get all formula results and create a calculation request
      -> get all depending formulas
      -> based on the formula
      -> exclude / delete formula results????
    -> create all calculation depending on requests
    -> sort the calculation request by dependency and priority
    -> execute the calculation requests

*/

namespace cfg;

include_once MODEL_HELPER_PATH . 'db_object_user.php';
include_once API_SYSTEM_PATH . 'batch_job.php';

use api\batch_job_api;
use cfg\db\sql_creator;
use DateTime;
use DateTimeInterface;

class batch_job extends db_object_user
{

    const STATUS_NEW = 'new'; // the job is not yet assigned to any calc engine
    const STATUS_ASSIGNED = 'assigned'; // the job has been assigned to a calc engine
    const STATUS_WORKING = 'working'; // the calc engine is reporting the progress
    const STATUS_NOT_RESPONDING = 'not_responding'; // the calc engine is not reporting the progress
    const STATUS_WAITING = 'waiting'; // the task is waiting for user input of other jobs
    const STATUS_DONE = 'done'; // the task has been completed successfully
    const STATUS_FAILED = 'failed'; // the task has been completed unsuccessful

    const PRIO_HIGHEST = 1;
    const PRIO_LOWEST = 10;


    /*
     * database link
     */

    // object specific database object field names
    const FLD_ID = 'calc_and_cleanup_task_id';
    const FLD_TIME_REQUEST = 'request_time';
    const FLD_TIME_START = 'start_time';
    const FLD_TIME_END = 'end_time';
    const FLD_TYPE = 'calc_and_cleanup_task_type_id';
    const FLD_ROW = 'row_id';
    const FLD_CHANGE_FIELD = 'change_field_id';

    // all database field names excluding the id used to identify if there are some user specific changes
    const FLD_NAMES = array(
        self::FLD_ID,
        self::FLD_TIME_REQUEST,
        self::FLD_TIME_START,
        self::FLD_TIME_END,
        self::FLD_TYPE,
        self::FLD_ROW,
        self::FLD_CHANGE_FIELD
    );


    /*
     * object vars
     */

    // database fields
    public ?DateTime $request_time = null;  // time when the job has been requested
    public ?DateTime $start_time = null;    // start time of the job execution
    public ?DateTime $end_time = null;      // end time of the job execution
    private ?int $type_id;                  // id of the batch type e.g. "update value", "add formula", ... because getting the type is fast from the preloaded type list
    public ?int $row_id = null;             // the id of the related object e.g. if a value has been updated the group_id
    public string $status;
    public string $priority;

    // in memory only fields
    public ?object $obj = null;             // the updated object

    // for calculation request a simple phrase list is used
    // not phrase groups and time because the phrase group and time splitting should only be used to save to the database
    public ?formula $frm = null;           // the formula object that should be used for updating the result
    public ?phrase_list $phr_lst = null;   //

    /*
     * construct and map
     */

    /**
     * always set the user because a term is always user specific
     * @param user $usr the user who requested to see this term
     */
    function __construct(user $usr, DateTime $request_time = new DateTime())
    {
        parent::__construct($usr);
        $this->request_time = $request_time;
        $this->status = self::STATUS_NEW;
        $this->priority = self::PRIO_LOWEST;
        $this->type_id = 0;
    }

    /**
     * map the database fields to one change log entry to this log object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a job is found
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        global $job_types;
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            //$this->request_time = $db_row[self::FLD_TIME_REQUEST];
            //$this->start_time = $db_row[self::FLD_TIME_START];
            //$this->end_time = $db_row[self::FLD_TIME_END];
            $this->type_id = $db_row[self::FLD_TYPE];
            //$this->status = $db_row[self::FLD_ID];
            //$this->priority = $db_row[self::FLD_ID];
            log_debug('Batch job ' . $this->id() . ' loaded');
        }
        return $result;
    }


    /*
     * set and get
     */

    function set_type_id(?int $type_id = null): void
    {
        $this->type_id = $type_id;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }

    function set_type(string $code_id): void
    {
        global $job_types;
        $this->set_type_id($job_types->id($code_id));
    }

    function type_code_id(): string
    {
        global $job_types;
        $result = '';
        if ($this->type_id != 0) {
            $type = $job_types->get($this->type_id);
            if ($type != null) {
                $result = $type->code_id();
            }
        }
        return $result;
    }

    /*
     * cast
     */

    /**
     * @return batch_job_api the batch job frontend api object
     */
    function api_obj(): batch_job_api
    {
        $api_obj = new batch_job_api($this->user());
        $api_obj->id = $this->id;
        // TODO use time zone?
        $api_obj->request_time = $this->request_time->format(DateTimeInterface::ATOM);
        if ($this->start_time != null) {
            $api_obj->start_time = $this->start_time->format(DateTimeInterface::ATOM);
        }
        if ($this->end_time != null) {
            $api_obj->end_time = $this->end_time->format(DateTimeInterface::ATOM);
        }
        $api_obj->type_id = $this->type_id();
        $api_obj->status = $this->status;
        $api_obj->priority = $this->priority;
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(): string
    {
        return $this->api_obj()->get_json();
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of a batch job from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class);
        $sc->set_type(sql_db::TBL_TASK);

        $sc->set_name($qp->name);
        $sc->set_usr($this->user()->id());
        $sc->set_fields(self::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a batch job by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id, string $class = self::class): sql_par
    {
        return parent::load_sql_by_id($sc, $id, $class);
    }

    /**
     * load a batch job from the database
     * @param sql_par $qp the query parameters created by the calling function
     * @return int the id of the object found and zero if nothing is found
     */
    protected function load(sql_par $qp): int
    {
        global $db_con;

        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        return $this->id();
    }

    /**
     * load a batch job by database id
     * @param int $id the id of the batch job
     * @param string $class the name of the child class from where the call has been triggered
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id, string $class = self::class): int
    {
        global $db_con;

        log_debug($id);
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id, $class);
        return $this->load($qp);
    }

    /**
     * TODO align the field name with the object
     * @return string calc_and_cleanup_task_id instead of batch_job
     */
    function id_field(): string
    {
        return self::FLD_ID;
    }


    /*
     * modify
     */

    /**
     * request a new calculation
     * @return int the id of the added batch job
     */
    function add(string $code_id = ''): int
    {

        global $db_con;

        $result = 0;
        log_debug();

        // create first the database entry to make sure the update is done
        if ($this->type_id() <= 0) {
            if ($code_id == '') {
                log_debug('invalid batch job type');
            } else {
                $this->set_type($code_id);
            }
        }

        if ($this->type_id() > 0) {
            log_debug('ok');
            if ($this->row_id <= 0) {
                if (isset($this->obj)) {
                    $this->row_id = $this->obj->id();
                }
            }
            if ($this->row_id <= 0 and $code_id != batch_job_type_list::BASE_IMPORT) {
                log_debug('row id missing?');
            } else {
                log_debug('row_id ok');
                if (isset($this->obj) or $code_id == batch_job_type_list::BASE_IMPORT) {
                    if (isset($this->obj)) {
                        $this->row_id = $this->obj->id();
                    }
                    log_debug('connect');
                    //$db_con = New mysql;
                    $db_type = $db_con->get_type();
                    $db_con->set_type(sql_db::TBL_TASK);
                    $db_con->set_usr($this->user()->id());
                    $job_id = $db_con->insert(array(user::FLD_ID, self::FLD_TIME_REQUEST, self::FLD_TYPE, self::FLD_ROW),
                        array($this->user()->id(), sql_creator::NOW, $this->type_id(), $this->row_id));
                    $this->request_time = new DateTime();

                    // execute the job if possible
                    if ($job_id > 0 and $code_id != batch_job_type_list::BASE_IMPORT) {
                        $this->id = $job_id;
                        $this->exe();
                        $result = $job_id;
                    }
                    $db_con->set_type($db_type);
                }
            }
        }
        log_debug('done');
        return $result;
    }

    /**
     * update all result depending on one value
     */
    function exe_val_upd(): void
    {
        log_debug();
        global $db_con;

        // load all depending formula results
        if (isset($this->obj)) {
            log_debug('get list for user ' . $this->obj->user()->name());
            $res_lst = $this->obj->res_lst_depending();
            if ($res_lst != null) {
                log_debug('got ' . $res_lst->dsp_id());
                if ($res_lst->lst() != null) {
                    foreach ($res_lst->lst() as $res) {
                        log_debug('update ' . get_class($res) . ' ' . $res->dsp_id());
                        $res->update();
                        log_debug('update ' . get_class($res) . ' ' . $res->dsp_id() . ' done');
                    }
                }
            }
        }

        //$db_con = New mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_TASK);
        $db_con->usr_id = $this->user()->id();
        $result = $db_con->update($this->id, 'end_time', sql_creator::NOW);
        $db_con->set_type($db_type);

        log_debug('done with ' . $result);
    }

    /**
     * execute all open requests
     */
    function exe(): void
    {
        global $db_con;
        //$db_con = New mysql;
        $db_type = $db_con->get_type();
        $db_con->usr_id = $this->user()->id();
        $db_con->set_type(sql_db::TBL_TASK);
        $result = $db_con->update($this->id, 'start_time', sql_creator::NOW);

        log_debug($this->type_code_id() . ' with ' . $result);
        if ($this->type_code_id() == batch_job_type_list::VALUE_UPDATE) {
            $this->exe_val_upd();
        } else {
            log_err('Job type "' . $this->type_code_id() . '" not defined.', 'batch_job->exe');
        }
        $db_con->set_type($db_type);
    }

    // remove the old requests from the database if they are closed since a while
    private function del()
    {
    }


    /*
     * debug
     */

    /**
     * @return string best possible identification for this formula mainly used for debugging
     */
    function dsp_id(): string
    {
        $result = $this->type_code_id();

        if ($this->row_id > 0) {
            $result .= ' for id ' . $this->row_id;
        }
        if (isset($this->frm)) {
            if (get_class($this->frm) == formula::class) {
                $result .= ' ' . $this->frm->dsp_id();
            } else {
                $result .= ' ' . get_class($this->frm) . ' ' . $this->frm->dsp_id();
            }
        }
        if (isset($this->phr_lst)) {
            if (get_class($this->phr_lst) == phrase_list::class) {
                $result .= ' ' . $this->phr_lst->dsp_id();
            } else {
                $result .= ' ' . get_class($this->phr_lst) . ' ' . $this->phr_lst->dsp_id();
            }
        }
        if ($this->id > 0) {
            $result .= ' (' . $this->id . ')';
        }
        $result .= $this->dsp_id_user();
        return $result;
    }

    function name(): string
    {
        $result = $this->type_code_id();

        if (isset($this->frm)) {
            if (get_class($this->frm) == formula::class) {
                $result .= $this->frm->name();
            } else {
                $result .= get_class($this->frm) . ' ' . $this->frm->name();
            }
        }
        if (isset($this->phr_lst)) {
            if (get_class($this->phr_lst) == phrase_list::class) {
                $result .= ' ' . $this->phr_lst->dsp_name();
            } else {
                $result .= ' ' . get_class($this->phr_lst) . ' ' . $this->phr_lst->dsp_name();
            }
        }
        return $result;
    }

}