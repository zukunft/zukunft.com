<?php

/*

    model/system/job.php - object to combine all parameters for one calculation or cleanup request
    --------------------

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
  
add, update or delete on an object always triggers all action from A) to D)
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

namespace cfg\system;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id_user.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id_user.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_REF . 'ref_db.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_SYSTEM . 'job_type.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_PHRASE . 'phrase.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id_user;
use cfg\formula\formula;
use cfg\helper\type_object;
use cfg\ref\ref_db;
use cfg\ref\source;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use cfg\ref\ref;
use cfg\ref\source_db;
use cfg\user\user;
use cfg\user\user_message;
use DateTime;
use DateTimeInterface;
use shared\enum\messages as msg_id;
use shared\types\api_type_list;
use shared\json_fields;
use shared\library;

class job extends db_object_seq_id_user
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

    // object specific database object field names and comments
    const TBL_COMMENT = 'for each concrete job run';
    const FLD_ID_COM = 'the unique internal id of the job';
    const FLD_ID = 'job_id';
    const FLD_USER_COM = 'the id of the user who has requested the job by editing the scheduler the last time';
    const FLD_TIME_REQUEST_COM = 'timestamp of the request for the job execution';
    const FLD_TIME_REQUEST = 'request_time';
    const FLD_TIME_START_COM = 'timestamp when the system has started the execution';
    const FLD_TIME_START = 'start_time';
    const FLD_TIME_END_COM = 'timestamp when the job has been completed or canceled';
    const FLD_TIME_END = 'end_time';
    const FLD_TYPE_COM = 'the id of the job type that should be started';
    const FLD_TYPE = 'job_type_id';
    const FLD_PARAMETER_COM = 'id of the phrase with the snapped parameter set for this job start';
    const FLD_PARAMETER = 'parameter';
    const FLD_CHANGE_FIELD_COM = 'e.g. for undo jobs the id of the field that should be changed';
    const FLD_CHANGE_FIELD = 'change_field_id';
    const FLD_ROW_COM = 'e.g. for undo jobs the id of the row that should be changed';
    const FLD_ROW = 'row_id';
    const FLD_SOURCE_COM = 'used for import to link the source';
    const FLD_REF_COM = 'used for import to link the reference';

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

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [job_type::FLD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, job_type::class, self::FLD_TYPE_COM],
        [self::FLD_TIME_REQUEST, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_REQUEST_COM],
        [self::FLD_TIME_START, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_START_COM],
        [self::FLD_TIME_END, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_TIME_END_COM],
        [self::FLD_PARAMETER, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_PARAMETER_COM, phrase::FLD_ID],
        [self::FLD_CHANGE_FIELD, type_object::FLD_ID_SQL_TYP, sql_field_default::NULL, sql::INDEX, '', self::FLD_CHANGE_FIELD_COM],
        [self::FLD_ROW, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, '', self::FLD_ROW_COM],
        [source_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, source::class, self::FLD_SOURCE_COM],
        [ref_db::FLD_ID, sql_field_type::INT, sql_field_default::NULL, sql::INDEX, ref::class, self::FLD_REF_COM],
    );


    /*
     * object vars
     */

    // database fields
    public ?DateTime $request_time = null;  // time when the job has been requested
    public ?DateTime $start_time = null;    // start time of the job execution
    public ?DateTime $end_time = null;      // end time of the job execution
    private ?int $type_id;                  // id of the job type e.g. "update value", "add formula", ... because getting the type is fast from the preloaded type list
    public int|string|null $row_id = null;             // the id of the related object e.g. if a value has been updated the group_id
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
        global $job_typ_cac;
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

    /**
     * set the database id of the type
     *
     * @param int|null $type_id the database id of the type
     * @param user $usr_req the user who wants to change the type
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_type_id(?int $type_id = null, user $usr_req = new user()): user_message
    {
        $usr_msg = new user_message();
        if ($usr_req->can_set_type_id()) {
            $this->type_id = $type_id;
        } else {
            // the type of a job can be set once if not defined already
            if ($type_id === null) {
                $this->type_id = $type_id;
            } else {
                $lib = new library();
                $usr_msg->add_id_with_vars(msg_id::NOT_ALLOWED_TO, [
                    msg_id::VAR_USER_NAME => $usr_req->name(),
                    msg_id::VAR_USER_PROFILE => $usr_req->profile_code_id(),
                    msg_id::VAR_NAME => sql_db::FLD_TYPE_NAME,
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
                ]);
            }
        }
        return $usr_msg;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }

    function set_type(string $code_id, user $usr_req): void
    {
        global $job_typ_cac;
        $this->set_type_id($job_typ_cac->id($code_id), $usr_req);
    }

    function type_code_id(): string
    {
        global $job_typ_cac;
        $result = '';
        if ($this->type_id != 0) {
            $type = $job_typ_cac->get($this->type_id);
            if ($type != null) {
                $result = $type->code_id();
            }
        }
        return $result;
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
        $qp = parent::load_sql_multi($sc, $query_name, $class, new sql_type_list([sql_type::MOST]));
        $sc->set_class(job::class);

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
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_id(sql_creator $sc, int $id): sql_par
    {
        return parent::load_sql_by_id($sc, $id);
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
     * TODO align the field name with the object
     * @return string job_id instead of job object
     */
    function id_field(): string
    {
        return self::FLD_ID;
    }


    /*
     * api
     */

    /**
     * create an array for the api json creation
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = [];

        $vars[json_fields::ID] = $this->id();
        $vars[json_fields::USER_NAME] = $this->user()->name();
        // TODO use time zone?
        $vars[json_fields::TIME_REQUEST] = $this->request_time->format(DateTimeInterface::ATOM);
        if ($this->start_time != null) {
            $vars[json_fields::TIME_START] = $this->start_time->format(DateTimeInterface::ATOM);
        }
        if ($this->end_time != null) {
            $vars[json_fields::TIME_END] = $this->end_time->format(DateTimeInterface::ATOM);
        }
        $vars[json_fields::TYPE] = $this->type_id();
        $vars[json_fields::STATUS] = $this->status;
        $vars[json_fields::PRIORITY] = $this->priority;

        return $vars;
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
        global $usr;

        $result = 0;
        log_debug();

        // create first the database entry to make sure the update is done
        if ($this->type_id() <= 0) {
            if ($code_id == '') {
                log_debug('invalid batch job type');
            } else {
                $this->set_type($code_id, $usr);
            }
        }

        if ($this->type_id() > 0) {
            log_debug('ok');
            if ($this->row_id <= 0) {
                if (isset($this->obj)) {
                    $this->row_id = $this->obj->id();
                }
            }
            if ($this->row_id <= 0 and $code_id != job_type_list::BASE_IMPORT) {
                log_debug('row id missing?');
            } else {
                log_debug('row_id ok');
                if (isset($this->obj) or $code_id == job_type_list::BASE_IMPORT) {
                    if (isset($this->obj)) {
                        $this->row_id = $this->obj->id();
                    }
                    log_debug('connect');
                    //$db_con = New mysql;
                    $db_type = $db_con->get_class();
                    $db_con->set_class(job::class);
                    $db_con->set_usr($this->user()->id());
                    $job_id = $db_con->insert_old(array(user::FLD_ID, self::FLD_TIME_REQUEST, self::FLD_TYPE, self::FLD_ROW),
                        array($this->user()->id(), sql::NOW, $this->type_id(), $this->row_id));
                    $this->request_time = new DateTime();

                    // execute the job if possible
                    if ($job_id > 0 and $code_id != job_type_list::BASE_IMPORT) {
                        $this->set_id($job_id);
                        $this->exe();
                        $result = $job_id;
                    }
                    $db_con->set_class($db_type);
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
        $db_type = $db_con->get_class();
        $db_con->set_class(job::class);
        $db_con->usr_id = $this->user()->id();
        $result = $db_con->update_old($this->id(), 'end_time', sql::NOW);
        $db_con->set_class($db_type);

        log_debug('done with ' . $result);
    }

    /**
     * execute all open requests
     */
    function exe(): void
    {
        global $db_con;
        //$db_con = New mysql;
        $db_type = $db_con->get_class();
        $db_con->usr_id = $this->user()->id();
        $db_con->set_class(job::class);
        $result = $db_con->update_old($this->id(), 'start_time', sql::NOW);

        log_debug($this->type_code_id() . ' with ' . $result);
        if ($this->type_code_id() == job_type_list::VALUE_UPDATE) {
            $this->exe_val_upd();
        } else {
            log_err('Job type "' . $this->type_code_id() . '" not defined.', 'job->exe');
        }
        $db_con->set_class($db_type);
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
        if ($this->id() > 0) {
            $result .= ' (' . $this->id() . ')';
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