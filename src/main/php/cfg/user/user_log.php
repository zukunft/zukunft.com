<?php

/*

    model/user/user_log.php - one session of a user with the number and the time of the api requests
    -----------------------

    $usr_log is the suggested var name

    a session is the time from the first api request of a user until the last request that follows
    the previous one within the configured gap (config.yaml pod > limits > user > log > period > max),
    so that the backend can count the requests without a login or logout hook (see application::end_api)

    the pod can switch the log off for all users or for the users without login, and the sessions
    older than the configured max age are moved to csv files in the archive directory (see job_db_cleanup)

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

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_field_list;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\system\system_time_list;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTime;

class user_log extends db_object_seq_id
{

    /*
     * db const
     */

    // database object field names and comments
    const string TBL_COMMENT = 'for the sessions of the users with the number and the time of their api requests';
    const string FLD_ID = 'user_log_id';
    const string FLD_USER_COM = 'the user whose api requests are counted';
    const string FLD_SESSION_START_COM = 'the start time of the first api request of the session';
    const string FLD_SESSION_START = 'session_start';
    const string FLD_SESSION_END_COM = 'the end time of the last api request of the session';
    const string FLD_SESSION_END = 'session_end';
    const string FLD_REQUESTS_COM = 'the number of api requests of the session';
    const string FLD_REQUESTS = 'requests';
    const string FLD_UPDATED_COM = 'the number of api requests of the session that have changed data';
    const string FLD_UPDATED = 'updated';
    const string FLD_REQUEST_TIME_COM = 'the sum of the milliseconds of all api requests of the session';
    const string FLD_REQUEST_TIME = 'request_time';
    const string FLD_UPDATE_TIME_COM = 'the sum of the milliseconds of the api requests of the session that have changed data';
    const string FLD_UPDATE_TIME = 'update_time';

    // all database field names excluding the id
    const array FLD_NAMES = array(
        user_db::FLD_ID,
        self::FLD_SESSION_START,
        self::FLD_SESSION_END,
        self::FLD_REQUESTS,
        self::FLD_UPDATED,
        self::FLD_REQUEST_TIME,
        self::FLD_UPDATE_TIME
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [user_db::FLD_ID, user_db::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_SESSION_START, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_SESSION_START_COM],
        [self::FLD_SESSION_END, sql_field_type::TIME, sql_field_default::NULL, sql::INDEX, '', self::FLD_SESSION_END_COM],
        [self::FLD_REQUESTS, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_REQUESTS_COM],
        [self::FLD_UPDATED, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_UPDATED_COM],
        [self::FLD_REQUEST_TIME, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_REQUEST_TIME_COM],
        [self::FLD_UPDATE_TIME, sql_field_type::INT, sql_field_default::NOT_NULL, '', '', self::FLD_UPDATE_TIME_COM],
    );

    // the query name to select the session of a user that is still open
    const string QUERY_OPEN = 'open_session';
    // the query name to select the sessions that should be moved to the archive
    const string QUERY_OLD = 'old_session';

    // the config.yaml path of the max seconds between two requests of the same session
    const array SESSION_GAP_NAMES = [words::MAX, words::PERIOD, words::LOG, words::USER, words::LIMITS, words::POD];
    // the config.yaml path of the days a session is kept in the database before it is archived
    const array MAX_AGE_NAMES = [words::DAYS, words::MAX, words::AGE, words::LOG, words::USER, words::LIMITS, words::POD];
    // the config.yaml path of the directory of the archived sessions
    const array ARCHIVE_PATH_NAMES = [words::PATH, words::ARCHIVE, words::LOG, words::USER, words::LIMITS, words::POD];

    // the max number of sessions moved to the archive in one run to keep the run within the time budget
    const int ARCHIVE_LIMIT = 1000;
    // the start of the name of the archive files, followed by the archive time and the first session id
    const string ARCHIVE_FILE = 'user_log_';
    const string ARCHIVE_TIME_FORMAT = 'Ymd_His';
    const string ARCHIVE_FILE_TYPE = '.csv';


    /*
     * object vars
     */

    public ?int $user_id = null;            // the user whose api requests are counted
    public ?DateTime $session_start = null; // when the first api request of the session has started
    public ?DateTime $session_end = null;   // when the last api request of the session has ended
    public ?int $requests = null;           // the number of api requests
    public ?int $updated = null;            // the number of api requests that have changed data
    public ?int $request_time = null;       // the milliseconds of all api requests
    public ?int $update_time = null;        // the milliseconds of the api requests that have changed data


    /*
     * construct and map
     */

    /**
     * reset the vars of this session row
     * @param bool $keep_user not used, because the user of a session is a field and not the owner
     */
    function reset(bool $keep_user = false): void
    {
        parent::reset();
        $this->user_id = null;
        $this->session_start = null;
        $this->session_end = null;
        $this->requests = null;
        $this->updated = null;
        $this->request_time = null;
        $this->update_time = null;
    }

    /**
     * map one row of the user log table to this session row
     *
     * @param array|null $db_row with the data directly from the database
     * @param user_message $msg to report a problem of the mapping
     * @param string $id_fld not used, because the id field of the user log is always the same
     * @return bool true if the mapping has been done without problems
     */
    function row_mapper(?array $db_row, user_message $msg, string $id_fld = ''): bool
    {
        $lib = new library();
        parent::row_mapper($db_row, $msg, self::FLD_ID);
        // map the fields if the id has been set from a found row, independent of the message state
        if ($this->id() != 0) {
            $this->user_id = $db_row[user_db::FLD_ID] ?? null;
            $this->session_start = $lib->get_datetime($db_row[self::FLD_SESSION_START] ?? null, $this->dsp_id());
            // the end time can be missing, which must not be shown as now
            if (($db_row[self::FLD_SESSION_END] ?? null) !== null) {
                $this->session_end = $lib->get_datetime($db_row[self::FLD_SESSION_END], $this->dsp_id());
            }
            $this->requests = $db_row[self::FLD_REQUESTS] ?? null;
            $this->updated = $db_row[self::FLD_UPDATED] ?? null;
            $this->request_time = $db_row[self::FLD_REQUEST_TIME] ?? null;
            $this->update_time = $db_row[self::FLD_UPDATE_TIME] ?? null;
        }
        return $msg->is_ok();
    }


    /*
     * modify
     */

    /**
     * add one api request to this session, which starts a new session if this row has no id
     *
     * @param int $usr_id the user whose request is counted
     * @param float $start the unix time with microseconds when the request has started
     * @param float $end the unix time with microseconds when the request has ended
     * @param bool $is_update true if the request has changed data e.g. a post, put or delete
     * @return void
     */
    function count_request(int $usr_id, float $start, float $end, bool $is_update): void
    {
        $ms = (int)round(($end - $start) * system_time_list::MILLISECONDS_PER_SECOND);
        if ($this->id() == 0) {
            $this->user_id = $usr_id;
            $this->session_start = new DateTime()->setTimestamp((int)$start);
            $this->requests = 0;
            $this->updated = 0;
            $this->request_time = 0;
            $this->update_time = 0;
        }
        $this->session_end = new DateTime()->setTimestamp((int)$end);
        $this->requests++;
        $this->request_time += $ms;
        if ($is_update) {
            $this->updated++;
            $this->update_time += $ms;
        }
    }


    /*
     * info
     */

    /**
     * @param float $end the unix time when the request has ended
     * @return DateTime the earliest end of the last request that still continues the session of the user
     */
    static function since(float $end): DateTime
    {
        global $cfg;
        $gap = (int)($cfg?->get_by(self::SESSION_GAP_NAMES, def::FALLBACK_USER_LOG_SESSION_GAP)
            ?? def::FALLBACK_USER_LOG_SESSION_GAP);
        return new DateTime()->setTimestamp((int)$end - $gap);
    }

    /**
     * @param user $usr the user whose request should be counted
     * @return bool false if the pod has switched off the user log for all users or for this user without login
     */
    static function allowed(user $usr): bool
    {
        global $cfg;
        // during the initial setup the config is not yet loaded, so the request is counted
        return $cfg?->user_log_allowed($usr->is_ip_user()) ?? true;
    }

    /**
     * @return DateTime the sessions that have ended before this time are moved to the archive
     */
    static function archive_before(): DateTime
    {
        global $cfg;
        $days = (int)($cfg?->get_by(self::MAX_AGE_NAMES, def::FALLBACK_USER_LOG_MAX_AGE_DAYS)
            ?? def::FALLBACK_USER_LOG_MAX_AGE_DAYS);
        return new DateTime()->modify('-' . $days . ' days');
    }

    /**
     * @return string the directory where the archived sessions are written to without the closing slash
     */
    static function archive_dir(): string
    {
        global $cfg;
        $path = $cfg?->get_by_names(self::ARCHIVE_PATH_NAMES)?->get_value();
        if (!is_string($path) or $path == '') {
            $path = def::FALLBACK_USER_LOG_ARCHIVE_PATH;
        }
        return rtrim($path, '/');
    }


    /*
     * load
     */

    /**
     * load the session of the user whose last request has ended after the given time
     *
     * @param int $usr_id the user whose session should be continued
     * @param DateTime $since the earliest end of the last request of the session
     * @param user_message $msg to report a failed database read
     * @return int the id of the open session or zero if the user has no open session
     */
    function load_open(int $usr_id, DateTime $since, user_message $msg): int
    {
        global $db_con;
        $qp = $this->load_sql_open($db_con->sql_creator(), $usr_id, $since);
        return $this->load($qp, $msg);
    }

    /**
     * create the sql statement to select the open session of a user, the latest first
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $usr_id the user whose session should be continued
     * @param DateTime $since the earliest end of the last request of the session
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_open(sql_creator $sc, int $usr_id, DateTime $since): sql_par
    {
        $qp = $this->load_sql($sc, self::QUERY_OPEN);
        $sc->add_where(user_db::FLD_ID, $usr_id);
        $sc->add_where(self::FLD_SESSION_END, $since->format(sql_db::DATE_FORMAT), sql_par_type::TIME_HIGHER);
        $sc->set_order(self::FLD_SESSION_END, sql::ORDER_DESC);
        $sc->set_page(1);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the sql statement to select the oldest sessions that have ended before the given time
     *
     * @param sql_creator $sc with the target db_type set
     * @param DateTime $before the sessions that have ended before this time are selected
     * @param int $limit the max number of sessions to select
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_old(sql_creator $sc, DateTime $before, int $limit): sql_par
    {
        $qp = $this->load_sql($sc, self::QUERY_OLD);
        $sc->add_where(self::FLD_SESSION_END, $before->format(sql_db::DATE_FORMAT), sql_par_type::TIME_LOWER);
        $sc->set_order(self::FLD_SESSION_END, '', '', self::FLD_ID);
        $sc->set_page($limit);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }


    /*
     * save
     */

    /**
     * count one api request in the open session of the user or start a new session;
     * without permission check and without change log, because every request is counted,
     * also the one of a user without login
     *
     * @param int $usr_id the user whose request is counted
     * @param float $start the unix time with microseconds when the request has started
     * @param float $end the unix time with microseconds when the request has ended
     * @param bool $is_update true if the request has changed data e.g. a post, put or delete
     * @param user_message $msg to report why the request could not be counted
     * @return bool true if the request has been counted
     */
    function log_request(int $usr_id, float $start, float $end, bool $is_update, user_message $msg): bool
    {
        global $db_con;

        $load_msg = new user_message($msg->usr); // merged back below
        $this->load_open($usr_id, self::since($end), $load_msg);
        $msg->merge($load_msg);
        // a failed read could hide the open session, so a second session row is not written
        if ($load_msg->is_ok()) {
            $db_row = clone $this;
            $this->count_request($usr_id, $start, $end, $is_update);
            $sc = $db_con->sql_creator();
            if ($db_row->id() == 0) {
                $qp = $this->sql_insert($sc, $msg);
                $db_con->insert($qp, 'add user log', $msg);
            } else {
                $qp = $this->sql_update($sc, $db_row, $msg);
                if ($qp !== null) {
                    $db_con->update($qp, 'count request in user log', $msg);
                }
            }
        }
        return $msg->is_ok();
    }

    /**
     * move the oldest sessions that have ended before the configured max age to a csv file
     * in the configured archive directory and remove them from the database;
     * the sessions are removed only after the csv file has been written completely
     * and without change log, because the csv file is the log of the removed sessions
     *
     * @param user_message $msg to report why the sessions could not be archived
     * @return int the number of sessions that have been moved to the archive
     */
    function archive(user_message $msg): int
    {
        global $db_con;

        $result = 0;
        $qp = $this->load_sql_old($db_con->sql_creator(), self::archive_before(), self::ARCHIVE_LIMIT);
        $rows = $db_con->get($qp, $msg) ?: [];
        $ids = array_column($rows, self::FLD_ID);
        if ($ids != [] and $this->write_csv($rows, $msg)) {
            $qp = $this->del_sql_without_log($db_con->sql_creator(), $ids);
            if ($db_con->delete($qp, 'archived user log sessions', $msg)) {
                $result = count($ids);
            }
        }
        return $result;
    }

    /**
     * write the given session rows with a header line to a new csv file in the archive directory
     *
     * @param array $rows the session rows as selected from the database
     * @param user_message $msg to report why the file could not be written
     * @return bool true if all rows have been written to the file
     */
    private function write_csv(array $rows, user_message $msg): bool
    {
        $dir = self::archive_dir();
        $time = new DateTime()->format(self::ARCHIVE_TIME_FORMAT);
        $file = $dir . '/' . self::ARCHIVE_FILE . $time . '_' . $rows[0][self::FLD_ID] . self::ARCHIVE_FILE_TYPE;
        $fields = [self::FLD_ID, ...self::FLD_NAMES];
        $result = is_dir($dir) || mkdir($dir, 0750, true);
        // x to never overwrite an archive file
        $handle = $result ? fopen($file, 'x') : false;
        $result = $handle !== false && fputcsv($handle, $fields, escape: '') !== false;
        foreach ($rows as $row) {
            $values = array_map(fn(string $fld) => $row[$fld], $fields);
            $result = $result && fputcsv($handle, $values, escape: '') !== false;
        }
        if ($handle !== false) {
            $result = fclose($handle) && $result;
        }
        if (!$result) {
            // a partly written file would archive the sessions twice, because they are archived again on the next run
            if ($handle !== false) {
                unlink($file);
            }
            $msg->add(msg_id::FILE_WRITE_FAILED, [msg_id::VAR_FILE_NAME => $file]);
        }
        return $result;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to remove the archived sessions without change log,
     * because the user log is in def::CLASSES_NO_CHANGE_LOG and the archive file is the log
     *
     * @param sql_creator $sc with the target db_type set
     * @param array $ids the database ids of the sessions that have been written to the archive
     * @return sql_par the sql statement to delete the archived sessions
     */
    function del_sql_without_log(sql_creator $sc, array $ids): sql_par
    {
        return $sc->del_sql_list_without_log(self::class, self::FLD_ID, $ids);
    }

    /**
     * remove the sessions of one user, because they reference the user row and must not block
     * the delete of the user, which is only possible for a user that has never changed data
     *
     * @param int $usr_id the id of the user whose sessions are removed
     * @param user_message $msg to report a problem of the delete
     * @return bool true if the sessions of the user have been removed
     */
    function del_by_user(int $usr_id, user_message $msg): bool
    {
        global $db_con;

        $qp = $this->del_sql_by_user_without_log($db_con->sql_creator(), $usr_id);
        return $db_con->delete($qp, 'user log sessions of user ' . $usr_id, $msg);
    }

    /**
     * create the sql statement to remove the sessions of one user without change log
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $usr_id the id of the user whose sessions are removed
     * @return sql_par the sql statement to delete the sessions of the user
     */
    function del_sql_by_user_without_log(sql_creator $sc, int $usr_id): sql_par
    {
        return $sc->del_sql_by_user_without_log(self::class, $usr_id);
    }


    /*
     * sql write fields
     */

    /**
     * @param sql_type_list $sc_par_lst not used, because the fields are the same for all writes
     * @return array list of all database field names that might be written in the order of the insert
     */
    function db_fields_all(sql_type_list $sc_par_lst = new sql_type_list()): array
    {
        return array_merge(parent::db_fields_all(), self::FLD_NAMES);
    }

    /**
     * get the database field names, values and types that differ from the given row;
     * an update always writes the end and adds the counts of the request to the database values
     * without an old value, so that two requests written at the same time are both counted
     * and every update uses the same prepared statement
     *
     * @param user_log|db_object_seq_id $obj the row as it is in the database e.g. empty for an insert
     * @param user_message $msg not used, because a session row has no field that the user can set wrong
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list the fields that need to be written in the order of db_fields_all
     */
    function db_fields_changed(
        user_log|db_object_seq_id $obj,
        user_message              $msg,
        sql_type_list             $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        $lst = parent::db_fields_changed($obj, $msg, $sc_par_lst);
        $end = $this->session_end?->format(sql_db::DATE_FORMAT);
        if ($sc_par_lst->is_update()) {
            $lst->add_field(self::FLD_SESSION_END, $end, sql_field_type::TIME);
            $lst->add_increment(self::FLD_REQUESTS, $this->requests - $obj->requests, sql_field_type::INT);
            $lst->add_increment(self::FLD_UPDATED, $this->updated - $obj->updated, sql_field_type::INT);
            $lst->add_increment(self::FLD_REQUEST_TIME, $this->request_time - $obj->request_time, sql_field_type::INT);
            $lst->add_increment(self::FLD_UPDATE_TIME, $this->update_time - $obj->update_time, sql_field_type::INT);
        } else {
            if ($this->user_id !== $obj->user_id) {
                $lst->add_field(user_db::FLD_ID, $this->user_id, user_db::FLD_ID_SQL_TYP, $obj->user_id);
            }
            $start = $this->session_start?->format(sql_db::DATE_FORMAT);
            $start_db = $obj->session_start?->format(sql_db::DATE_FORMAT);
            if ($start !== $start_db) {
                $lst->add_field(self::FLD_SESSION_START, $start, sql_field_type::TIME, $start_db);
            }
            $end_db = $obj->session_end?->format(sql_db::DATE_FORMAT);
            if ($end !== $end_db) {
                $lst->add_field(self::FLD_SESSION_END, $end, sql_field_type::TIME, $end_db);
            }
            if ($this->requests !== $obj->requests) {
                $lst->add_field(self::FLD_REQUESTS, $this->requests, sql_field_type::INT, $obj->requests);
            }
            if ($this->updated !== $obj->updated) {
                $lst->add_field(self::FLD_UPDATED, $this->updated, sql_field_type::INT, $obj->updated);
            }
            if ($this->request_time !== $obj->request_time) {
                $lst->add_field(self::FLD_REQUEST_TIME, $this->request_time, sql_field_type::INT, $obj->request_time);
            }
            if ($this->update_time !== $obj->update_time) {
                $lst->add_field(self::FLD_UPDATE_TIME, $this->update_time, sql_field_type::INT, $obj->update_time);
            }
        }
        return $lst;
    }

}
