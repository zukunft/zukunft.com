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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
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
Because the calculation dependencies can be complex always both cases (user-specific and standard) are calculated but only the result needed is saved


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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id_user.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id_user.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'ref_db.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_SYSTEM . 'job_db.php';
include_once paths::MODEL_SYSTEM . 'job_status.php';
include_once paths::MODEL_SYSTEM . 'job_status_list.php';
include_once paths::MODEL_SYSTEM . 'job_type.php';
include_once paths::MODEL_SYSTEM . 'job_type_list.php';
include_once paths::MODEL_PHRASE . 'phrase_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED_TYPES . 'job_statuus.php';
include_once paths::SHARED_TYPES . 'job_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id_user;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_db;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuus;
use Zukunft\ZukunftCom\main\php\shared\types\job_types;
use DateTime;
use DateTimeInterface;

class job extends db_object_seq_id_user
{

    /*
     * database link
     */

    // object specific database object field names and comments
    const string TBL_COMMENT = 'for each concrete job run';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = job_db::FLD_ID;
    const array FLD_NAMES = job_db::FLD_NAMES;
    const array FLD_LST_ALL = job_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // database fields
    private ?int $type_id;                  // id of the job type e.g. "update value", "add formula", ... because getting the type is fast from the preloaded type list
    public ?int $status_id;                 // id of the job status e.g. "new", "running", "done", ...
    public ?DateTime $request_time = null;  // time when the job has been requested
    public ?DateTime $start_time = null;    // start time of the job execution
    public ?DateTime $end_time = null;      // end time of the job execution
    public ?string $parameter = null;       // id of the phrase with the snapped parameter set for this job start
    public ?int $change_field = null;       // if of e.g. for undo jobs the id of the field that should be changed
    public int|string|null $row_id = null;  // the id of the related object e.g. if a value has been updated the group_id
    public source|null $src = null;         // used for import to link the source
    public ref|null $ref = null;            // used for import to link the reference
    public int|null $priority = 0;

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
     * always set the type and the user
     * @param user $usr the user who requested to see this term
     * @param DateTime $request_time the time when the job has been requested
     */
    function __construct(user $usr, DateTime $request_time = new DateTime())
    {
        parent::__construct($usr);
        $this->request_time = $request_time;
        $this->status_id = job_statuus::STATUS_NEW_ID;
        $this->priority = job_statuus::PRIO_LOWEST;
        $this->type_id = null;
    }

    /**
     * clear all job object values e.g. to detect the changed fields
     * @param bool $keep_user set to true to keep the original user
     * @return void
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->type_id = null;
        $this->status_id = null;
        $this->request_time = null;
        $this->start_time = null;
        $this->end_time = null;
        $this->parameter = null;
        $this->change_field = null;
        $this->row_id = null;
        $this->src = null;
        $this->ref = null;
        $this->priority = null;
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
        $lib = new library();
        $result = parent::row_mapper($db_row, job_db::FLD_ID);
        if ($result) {
            if (array_key_exists(job_db::FLD_TYPE, $db_row)) {
                $this->type_id = $db_row[job_db::FLD_TYPE];
            }
            if (array_key_exists(job_db::FLD_STATUS, $db_row)) {
                $this->status_id = $db_row[job_db::FLD_STATUS];
            }
            if (array_key_exists(job_db::FLD_TIME_REQUEST, $db_row)) {
                $this->request_time = $lib->get_datetime($db_row[job_db::FLD_TIME_REQUEST], $this->dsp_id());
            }
            if (array_key_exists(job_db::FLD_TIME_START, $db_row)) {
                $this->start_time = $lib->get_datetime($db_row[job_db::FLD_TIME_START], $this->dsp_id());
            }
            if (array_key_exists(job_db::FLD_TIME_END, $db_row)) {
                $this->end_time = $lib->get_datetime($db_row[job_db::FLD_TIME_END], $this->dsp_id());
            }
            $this->parameter = $db_row[job_db::FLD_PARAMETER];
            $this->change_field = $db_row[job_db::FLD_CHANGE_FIELD];
            $this->row_id = $db_row[job_db::FLD_ROW];
            if (array_key_exists(source_db::FLD_ID, $db_row)) {
                $this->set_source_id($db_row[source_db::FLD_ID]);
            }
            if (array_key_exists(ref_db::FLD_ID, $db_row)) {
                $this->set_ref_id($db_row[ref_db::FLD_ID]);
            }
            $this->priority = $db_row[job_db::FLD_PRIO];
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
        $msg = new user_message();
        if ($usr_req->can_set_type_id()) {
            $this->type_id = $type_id;
        } else {
            // the type of a job can be set once if not defined already
            if ($type_id === null) {
                $this->type_id = $type_id;
            } else {
                $lib = new library();
                $msg->add(msg_id::NOT_ALLOWED_TO, [
                    msg_id::VAR_USER_NAME => $usr_req->name(),
                    msg_id::VAR_USER_PROFILE => $usr_req->profile_code_id(),
                    msg_id::VAR_NAME => sql_db::FLD_TYPE_NAME,
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
                ]);
            }
        }
        return $msg;
    }

    /**
     * set the database id of the status
     *
     * @param int|null $sta_id the database id of the status
     * @param user $usr_req the user who wants to change the type
     * @return user_message warning message for the user if the permissions are missing
     */
    function set_status_id(?int $sta_id = null, user $usr_req = new user()): user_message
    {
        $msg = new user_message();
        if ($usr_req->can_set_type_id()) {
            $this->type_id = $sta_id;
        } else {
            // the type of a job can be set once if not defined already
            if ($sta_id === null) {
                $this->status_id = $sta_id;
            } else {
                $lib = new library();
                $msg->add(msg_id::NOT_ALLOWED_TO, [
                    msg_id::VAR_USER_NAME => $usr_req->name(),
                    msg_id::VAR_USER_PROFILE => $usr_req->profile_code_id(),
                    msg_id::VAR_NAME => sql_db::FLD_TYPE_NAME,
                    msg_id::VAR_CLASS_NAME => $lib->class_to_name($this::class)
                ]);
            }
        }
        return $msg;
    }

    function type_id(): ?int
    {
        return $this->type_id;
    }

    function status_id(): ?int
    {
        return $this->status_id;
    }

    function set_type(string $code_id, user $usr_req): void
    {
        global $sys;
        $this->set_type_id($sys->typ_lst->job_typ->id($code_id), $usr_req);
    }

    function set_status(string $code_id, user $usr_req): void
    {
        global $sys;
        $this->set_status_id($sys->typ_lst->job_sta->id($code_id), $usr_req);
    }

    function type_code_id(): string
    {
        global $sys;
        $result = '';
        if ($this->type_id != 0) {
            $type = $sys->typ_lst->job_typ->get($this->type_id);
            if ($type != null) {
                $result = $type->get_code_id();
            }
        }
        return $result;
    }

    /**
     * @param int|null $id the id of the source use by the job
     */
    function set_source_id(int|null $id): void
    {
        if ($id == null) {
            $this->src = null;
        } else {
            if ($this->src == null) {
                $this->src = new source($this->get_user());
            }
            $this->src->id = $id;
        }
    }

    /**
     * @return int the id of the source for this job or zero if no source is defined
     */
    function get_source_id(): int
    {
        if ($this->src == null) {
            return 0;
        } else {
            return $this->src->id();
        }
    }

    /**
     * @param int|null $id the id of the reference use by the job
     */
    function set_ref_id(int|null $id): void
    {
        if ($id == null) {
            $this->ref = null;
        } else {
            if ($this->ref == null) {
                $this->ref = new ref($this->get_user());
            }
            $this->ref->id = $id;
        }
    }

    /**
     * @return int the id of the reference for this job or zero if no reference is defined
     */
    function get_ref_id(): int
    {
        if ($this->ref == null) {
            return 0;
        } else {
            return $this->ref->id();
        }
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
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql_multi($sc, $query_name, $class, new sql_type_list([sql_type::MOST]));
        $sc->set_class(job::class);

        $sc->set_name($qp->name);
        $sc->set_usr($this->get_user()->id);
        $sc->set_fields(job_db::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a batch job by id from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $id the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
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
     * @return string job_id instead of a job object
     */
    function id_field(): string
    {
        return job_db::FLD_ID;
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
        $vars[json_fields::USER_NAME] = $this->get_user()->name();
        $vars[json_fields::TYPE] = $this->type_id();
        $vars[json_fields::STATUS] = $this->status_id();
        // TODO use time zone?
        $vars[json_fields::TIME_REQUEST] = $this->request_time->format(DateTimeInterface::ATOM);
        if ($this->start_time != null) {
            $vars[json_fields::TIME_START] = $this->start_time->format(DateTimeInterface::ATOM);
        }
        if ($this->end_time != null) {
            $vars[json_fields::TIME_END] = $this->end_time->format(DateTimeInterface::ATOM);
        }
        $vars[json_fields::JOB_PARAMETER] = $this->parameter;
        $vars[json_fields::FIELD_ID] = $this->change_field;
        $vars[json_fields::ROW_ID] = $this->row_id;
        $vars[json_fields::SOURCE] = $this->src?->id();
        $vars[json_fields::REFERENCE] = $this->ref?->id();
        $vars[json_fields::PRIORITY] = $this->priority;

        return $vars;
    }


    /*
     * modify
     */

    /**
     * update all result depending on one value
     */
    function exe_val_upd(user_message $usr_msg): bool
    {
        log_debug();

        // load all depending formula results
        if (isset($this->obj)) {
            log_debug('get list for user ' . $this->obj->get_user()->name());
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

        $this->end_time = new DateTime();
        $this->save($usr_msg);

        log_debug('done with ' . $usr_msg->all_message_text());
        return $usr_msg->is_ok();
    }

    /**
     * execute all open requests
     */
    function exe(): void
    {
        $usr_msg = new user_message();

        $this->start_time = new DateTime();
        $this->save($usr_msg);

        if ($this->type_code_id() == job_types::VALUE_UPDATE) {
            $this->exe_val_upd($usr_msg);
        } else {
            log_err('Job type "' . $this->type_code_id() . '" not defined.', 'job->exe');
        }
    }

    /**
     * remove the old requests from the database if they are closed since a while
     * @param user_message $msg the message that should be shown to the user if something went wrong or an empty string if everything is fine
     * @return bool true if everything has been fine
     */
    function del(user_message $msg): bool
    {
        return $msg->is_ok();
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields that might be changed
     * excluding the internal fields e.g. the database id
     * field list must be corresponding to the db_fields_changed fields
     *
     * @param sql_type_list $sc_par_lst only used for link objects
     * @return array list of all database field names that have been updated
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(
            parent::db_fields_all(),
            [
                job_db::FLD_TYPE,
                job_db::FLD_STATUS,
                job_db::FLD_TIME_REQUEST,
                job_db::FLD_TIME_START,
                job_db::FLD_TIME_END,
                job_db::FLD_PARAMETER,
                job_db::FLD_CHANGE_FIELD,
                job_db::FLD_ROW,
                source_db::FLD_ID,
                ref_db::FLD_ID,
                job_db::FLD_PRIO,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param job|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        job|db_object_seq_id $obj,
        user_message         $msg,
        sql_type_list        $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        if ($obj->type_id() !== $this->type_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            if ($this->type_id() < 0) {
                $msg->add(msg_id::JOB_TYPE_MISSING, [
                    msg_id::VAR_TYPE => $this->type_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                job_db::FLD_TYPE,
                type_object::FLD_NAME,
                $this->type_id(),
                $obj->type_id(),
                $sys->typ_lst->job_typ);
        }
        if ($obj->status_id() !== $this->status_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_TYPE,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_TYPE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            global $sys;
            if ($this->status_id() < 0) {
                $msg->add(msg_id::JOB_STATUS_MISSING, [
                    msg_id::VAR_TYPE => $this->type_id(),
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
            $lst->add_type_field(
                job_db::FLD_STATUS,
                type_object::FLD_NAME,
                $this->status_id(),
                $obj->status_id(),
                $sys->typ_lst->job_sta);
        }
        // TODO Prio 2 maybe add the time zone to the formatting and move the format to a SQL const
        if ($obj->request_time !== $this->request_time) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_TIME_REQUEST,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_TIME_REQUEST),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_TIME_REQUEST,
                $this->request_time?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->request_time?->format(sql_db::DATE_FORMAT)
            );
        }
        if ($obj->start_time !== $this->start_time) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_TIME_START,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_TIME_START),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_TIME_START,
                $this->start_time?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->start_time?->format(sql_db::DATE_FORMAT)
            );
        }
        if ($obj->end_time !== $this->end_time) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_TIME_END,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_TIME_END),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_TIME_END,
                $this->end_time?->format(sql_db::DATE_FORMAT),
                sql_field_type::TIME,
                $obj->end_time?->format(sql_db::DATE_FORMAT)
            );
        }
        if ($obj->parameter !== $this->parameter) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_PARAMETER,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_PARAMETER),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_PARAMETER,
                $this->parameter,
                sql_field_type::INT,
                $obj->parameter
            );
        }
        if ($obj->change_field !== $this->change_field) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_CHANGE_FIELD,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_CHANGE_FIELD),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_CHANGE_FIELD,
                $this->change_field,
                sql_field_type::INT,
                $obj->change_field
            );
        }
        if ($obj->row_id !== $this->row_id) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_ROW,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_ROW),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_ROW,
                $this->row_id,
                sql_field_type::INT,
                $obj->row_id
            );
        }
        if ($obj->get_source_id() !== $this->get_source_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . source_db::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . source_db::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                source_db::FLD_ID,
                source_db::FLD_NAME,
                $this->src,
                $obj->src
            );
        }
        if ($obj->get_ref_id() !== $this->get_ref_id()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ref_db::FLD_ID,
                    $sys->typ_lst->cng_fld->id($table_id . ref_db::FLD_ID),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_link_field(
                ref_db::FLD_ID,
                ref_db::FLD_EX_KEY,
                $this->ref,
                $obj->ref
            );
        }
        if ($obj->priority !== $this->priority) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . job_db::FLD_PRIO,
                    $sys->typ_lst->cng_fld->id($table_id . job_db::FLD_PRIO),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                job_db::FLD_PRIO,
                $this->priority,
                sql_field_type::INT_SMALL,
                $obj->priority
            );
        }
        return $lst;
    }


    /*
     * db helper
     */

    /**
     * check if the user can add this object to the database
     * e.g. reject if a reserved name is used and the user is not a system test user or an admin user
     * to be overwritten by the child objects
     *
     * @param user_message $msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check(user_message $msg): bool
    {
        // the job type must be valid
        if ($this->type_id() <= 0) {
            $msg->add_err_with_vars(msg_id::JOB_TYPE_INVALID, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        } elseif ($this->type_code_id() != job_types::BASE_IMPORT) {
            if ($this->row_id <= 0) {
                $msg->add_err_with_vars(msg_id::JOB_ROW_MISSING, [
                    msg_id::VAR_NAME => $this->dsp_id()
                ]);
            }
        }
        return $msg->is_ok();
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