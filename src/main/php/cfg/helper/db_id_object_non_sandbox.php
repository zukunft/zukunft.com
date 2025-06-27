<?php

/*

    model/helper/db_object_non_sandbox.php - a base object for not user specific database id objects
    -------------------------------------

    used for the user, ip_range, pod and job

    The main sections of this object are
    - set and get:       to capsule the single variables from unexpected changes
    - load:              database access object (DAO) functions
    - del:               database access object (DAO) functions
    - sql:               create the curl sql statements based on the sql creator
    - overwrite:         declared functions that must be overwritten by the child objects


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

namespace cfg\helper;

include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
include_once MODEL_LOG_PATH . 'change.php';
include_once MODEL_LOG_PATH . 'change_action.php';
include_once MODEL_USER_PATH . 'user.php';
include_once MODEL_USER_PATH . 'user_message.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'messages.php';
include_once SHARED_PATH . 'library.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\log\change;
use cfg\log\change_action;
use cfg\user\user;
use cfg\user\user_message;
use shared\enum\change_actions;
use shared\enum\messages as msg_id;
use shared\library;
use Exception;

class db_id_object_non_sandbox extends db_object_seq_id
{

    /*
     * set and get
     */

    /**
     * get the most relevant unique value of the object
     * e.g. the ip address of a user if the username an email are missing
     *
     * @return string with the most relevant unique key
     */
    function unique_value(): string
    {
        return strval($this->id());
    }


    /*
     * settings
     */

    /**
     * @return bool true if this sandbox object is a value or result
     * final function overwritten by the child object
     */
    function is_value_obj(): bool
    {
        return false;
    }


    /*
     * load
     */

    /**
     * load the object by the given unique key
     *
     * @param string $key the unique value the select one db row
     * @param string $key_name the name of the key as defined by object const
     * @return bool true if the object has been loaded
     */
    function load_by_key(string $key, string $key_name): bool
    {
        if ($this:: class == user::class) {
            if ($key_name == user::KEY_IP) {
                return $this->load_by_ip($key);
            } elseif ($key_name == user::KEY_NAME) {
                return $this->load_by_name($key);
            } elseif ($key_name == user::KEY_EMAIL) {
                return $this->load_by_email($key);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /*
     * del
     */

    /**
     * delete the related db row and log the deletion
     *
     * @param user $usr_req the user who has requested the deletion
     * @return user_message if the deletion cannot be done the reason why for the user
     */
    function del(user $usr_req): user_message
    {
        $usr_msg = new user_message();
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        if ($this->id() == 0) {
            $usr_msg->add_id_with_vars(msg_id::ID_MISSING_FOR_DEL, [
                msg_id::VAR_CLASS_NAME => $class_name,
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        } else {
            // refresh the object with the database to include all updates utils now
            $reloaded = false;
            $reloaded_id = $this->load_by_id($this->id());
            if ($reloaded_id != 0) {
                $reloaded = true;
            }
            if (!$reloaded) {
                log_warning('Reload of for deletion has failed',
                    $class_name . '->del',
                    'Reload of ' . $class_name . ' ' . $this->dsp_id()
                    . ' for deletion has failed.',
                    (new Exception)->getTraceAsString(), $usr_req);
            } else {
                log_debug('reloaded ' . $this->dsp_id());
                // check if the object is still valid
                if ($this->id() <= 0) {
                    log_warning('Delete failed',
                        $class_name . '->del',
                        'Delete failed, because it seems that the ' . $class_name . ' ' . $this->dsp_id()
                        . ' has been deleted in the meantime.', (new Exception)->getTraceAsString(), $usr_req);
                } else {
                    $usr_msg->add($this->del_exe($usr_req));
                }
            }
        }
        return $usr_msg;
    }

    /**
     * delete the complete object (the calling function del must have checked that no one uses this object)
     * @param user $usr_req the user who has requested the deletion
     * @returns user_message the message that should be shown to the user if something went wrong or an empty string if everything is fine
     */
    private function del_exe(user $usr_req): user_message
    {
        log_debug($this->dsp_id());

        global $db_con;

        $usr_msg = new user_message();

        $sc = $db_con->sql_creator();
        $qp = $this->sql_delete($sc, $usr_req, new sql_type_list([sql_type::LOG]));
        $del_msg = $db_con->delete($qp, 'del and log ' . $this->dsp_id());
        $usr_msg->add($del_msg);

        return $usr_msg;
    }


    /*
     * sql
     */

    /**
     * create the sql statement to delete or exclude a named sandbox object e.g. word to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param user $usr_req the user who has requested the deletion
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par the SQL update statement, the name of the SQL statement and the parameter list
     */
    function sql_delete(
        sql_creator   $sc,
        user          $usr_req,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        // clone the sql parameter list to avoid changing the given list
        $sc_par_lst_used = clone $sc_par_lst;
        // set the sql query type
        $sc_par_lst_used->add(sql_type::DELETE);
        // set the query name
        $qp = $this->sql_common($sc, $sc_par_lst_used);
        $sc->set_name($qp->name);
        // delete the user overwrite
        // but if the excluded user overwrites should be deleted the overwrites for all users should be deleted
        if ($sc_par_lst_used->incl_log()) {
            // log functions must always use named parameters
            $sc_par_lst_used->add(sql_type::NAMED_PAR);
            $qp = $this->sql_delete_and_log($sc, $qp, $usr_req, $sc_par_lst_used);
        } else {
            $par_lst = [$this->id()];
            $qp->sql = $sc->create_sql_delete($this->id_field(), $this->id(), $sc_par_lst_used);
            $qp->par = $par_lst;
        }

        return $qp;
    }

    /**
     * @param sql_creator $sc the sql creator object with the db type set
     * @param sql_par $qp the query parameter with the name already set
     * @param user $usr_req the user who has requested the deletion
     * @param sql_type_list $sc_par_lst
     * @return sql_par
     */
    private function sql_delete_and_log(
        sql_creator   $sc,
        sql_par       $qp,
        user          $usr_req,
        sql_type_list $sc_par_lst = new sql_type_list()
    ): sql_par
    {
        global $cng_act_cac;
        global $cng_fld_cac;
        $table_id = $sc->table_id($this::class);

        // set some var names to shorten the code lines
        $ext = sql::NAME_SEP . sql_creator::FILE_DELETE;
        $id_fld = $sc->id_field_name();
        $id_val = '_' . $id_fld;
        $key_fld = $this->key_field();

        // list of parameters actually used in order of the function usage
        $fvt_lst_out = new sql_par_field_list();

        // init the function body
        $sql = $sc->sql_func_start('', $sc_par_lst);

        // don't use the log parameter for the sub queries
        $sc_par_lst_sub = clone $sc_par_lst;
        $sc_par_lst_sub->add(sql_type::LIST);
        $sc_par_lst_sub->add(sql_type::NAMED_PAR);
        $sc_par_lst_sub->add(sql_type::DELETE_PART);
        $sc_par_lst_log = $sc_par_lst_sub->remove(sql_type::LOG);
        $sc_par_lst_log->add(sql_type::SELECT_FOR_INSERT);
        $sc_par_lst_log->add(sql_type::REQUESTING_USER);

        // create the queries for the log entries
        $func_body_change = '';

        // add the user_id if needed
        $fvt_lst_out->add_field(
            sql::FLD_LOG_REQ_USER,
            $usr_req->id(),
            sql_par_type::INT);

        // add the change_action_id if needed
        $fvt_lst_out->add_field(
            change_action::FLD_ID,
            $cng_act_cac->id(change_actions::DELETE),
            sql_par_type::INT_SMALL);

        if ($key_fld != '') {
            // add the field_id of the field actually changed if needed
            $fvt_lst_out->add_field(
                sql::FLD_LOG_FIELD_PREFIX . $key_fld,
                $cng_fld_cac->id($table_id . $key_fld),
                sql_par_type::INT_SMALL);

            // add the db field value of the field actually changed if needed
            $fvt_lst_out->add_field(
                $key_fld,
                $this->unique_value(),
                sql_par_type::TEXT);
        }

        // create the insert log statement
        $sc_log = clone $sc;
        if ($key_fld != '') {
            $log = new change($usr_req);
            $log->set_class($this::class);
            $log->set_field($key_fld);
            $log->old_value = $this->unique_value();
            $log->new_value = null;
            $qp_log = $log->sql_insert(
                $sc_log, $sc_par_lst_log, $ext . '_' . $key_fld, '', $key_fld, $id_val);
        } else {
            $qp_log = new sql_par($this::class, $sc_par_lst);
            log_warning('No key found for the logging in db_id_object_non_sandbox::sql_delete_and_log');
        }

        // TODO get the fields used in the change log sql from the sql
        $func_body_change .= ' ' . $qp_log->sql . ';';

        // add the row id of the standard table for user overwrites
        $fvt_lst_out->add_field(
            $this->id_field(),
            $this->id(),
            sql_par_type::INT);

        $sql .= ' ' . $func_body_change;

        // create the actual delete or exclude statement
        $sc_delete = clone $sc;
        $sc_par_lst_del = clone $sc_par_lst;
        $sc_par_lst_del->add(sql_type::DELETE);
        $sc_par_lst_del->add(sql_type::NAMED_PAR);
        $qp_delete = $this->sql_common($sc_delete, $sc_par_lst_log);
        $qp_delete->sql = $sc_delete->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_sub);
        // add the delete statement to the function body
        $sql .= ' ' . $qp_delete->sql . ' ';

        $sql .= $sc->sql_func_end();

        // create the query parameters for the call
        $sc_par_lst_func = clone $sc_par_lst;
        $sc_par_lst_func->add(sql_type::FUNCTION);
        $sc_par_lst_func->add(sql_type::DELETE);
        $sc_par_lst_func->add(sql_type::NO_ID_RETURN);
        if ($sc_par_lst->exclude_sql()) {
            $sc_par_lst_func->add(sql_type::EXCLUDE);
        }
        $qp_func = $this->sql_common($sc_delete, $sc_par_lst_func);
        $qp_func->sql = $sc->create_sql_delete(
            $id_fld, $id_val, $sc_par_lst_func, $fvt_lst_out);
        $qp_func->par = $fvt_lst_out->values();

        // merge all together and create the function
        $qp->sql = $qp_func->sql . ' ' . $sql . ';';
        $qp->par = $fvt_lst_out->values();

        // create the function call
        $qp->call_sql = ' ' . sql::SELECT . ' ' . $qp_func->name . ' (';

        $call_val_str = $fvt_lst_out->par_sql($sc);

        $qp->call_sql .= $call_val_str . ');';

        return $qp;
    }

    /**
     * the common part of the sql_insert, sql_update and sql_delete functions
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the query name extension to differ the queries based on the fields changed
     * @return sql_par prepared sql parameter object with the name set
     */
    protected function sql_common(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        string        $ext = ''): sql_par
    {
        $qp = new sql_par($this::class, $sc_par_lst, $ext);

        // update the sql creator settings
        $sc->set_class($this::class, $sc_par_lst);
        $sc->set_name($qp->name);

        return $qp;
    }


    /*
     * overwrite
     */

    function load_by_ip(string $ip): bool
    {
        log_err('load_by_ip used but not overwritten in ' . $this::class);
        return false;
    }

    function load_by_email(string $email): bool
    {
        log_err('load_by_email used but not overwritten in ' . $this::class);
        return false;
    }

    function key_field(): string
    {
        log_err('key_field used but not overwritten in ' . $this::class);
        return false;
    }

    function import_mapper_user(
        array $in_ex_json,
        user $usr_req,
        data_object $dto = null,
        object $test_obj = null
    ): user_message
    {
        $msg = 'import_mapper_user used but not overwritten in ' . $this::class;
        log_err($msg);
        $usr_msg = new user_message();
        $usr_msg->add_message_text($msg);
        return $usr_msg;
    }

}
