<?php

/*

    model/system/ip_range.php - a base object for a list of database IDs
    -------------------------


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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
include_once paths::EXPORT . 'export_type_list.php';
include_once paths::MODEL_HELPER . 'data_object.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'db_object_seq_id.php';
//include_once paths::MODEL_LOG . 'change.php';
include_once paths::MODEL_LOG . 'change_action.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_HELPER . 'IdObject.php';
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
use Zukunft\ZukunftCom\main\php\cfg\export\export_type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\data_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object;
use Zukunft\ZukunftCom\main\php\cfg\helper\db_object_seq_id;
use Zukunft\ZukunftCom\main\php\cfg\log\change;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\helper\IdObject;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;

class ip_range extends db_object_seq_id
{

    /*
     * database link
     */

    // database and JSON object field names and comments
    const string TBL_COMMENT = 'of ip addresses that should be blocked';

    // forward the const to enable usage of $this::CONST_NAME
    const string FLD_ID = ip_range_db::FLD_ID;
    const array FLD_NAMES = ip_range_db::FLD_NAMES;
    const array FLD_LST_ALL = ip_range_db::FLD_LST_ALL;


    /*
     * object vars
     */

    // database fields
    public ?string $from = null;
    public ?string $to = null;
    public ?string $reason = null;
    public bool $active = false;

    // in memory only fields
    private ?user $usr = null;             // just needed for logging the changes


    /*
     * construct and map
     */

    function reset(bool $keep_user = false): void
    {
        parent::reset($keep_user);
        $this->from = null;
        $this->to = null;
        $this->reason = null;
        $this->active = false;
    }

    /**
     * map the database fields to this ip range object
     * to be extended by the child functions
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if the user sandbox object is loaded and valid
     */
    function row_mapper(?array $db_row, string $id_fld = ''): bool
    {
        $result = parent::row_mapper($db_row, ip_range_db::FLD_ID);
        if ($result) {
            $this->from = $db_row[ip_range_db::FLD_FROM];
            $this->to = $db_row[ip_range_db::FLD_TO];
            $this->reason = $db_row[ip_range_db::FLD_REASON];
            $this->active = $db_row[ip_range_db::FLD_ACTIVE];
        }
        return $result;
    }

    /**
     * set the vars of this ip range object based on the given json without writing to the database
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_mapper(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        // set the object vars based on the json
        if (key_exists(json_fields::IP_FROM, $in_ex_json)) {
            $this->from = $in_ex_json[json_fields::IP_FROM];
        } else {
            $usr_msg->add_id_with_vars(msg_id::IMPORT_IP_MISSING, [
                msg_id::VAR_NAME => json_fields::IP_FROM,
                msg_id::VAR_IP_RANGE => json_encode($in_ex_json),
            ]);
        }
        if (key_exists(json_fields::IP_TO, $in_ex_json)) {
            $this->to = $in_ex_json[json_fields::IP_TO];
        } else {
            $usr_msg->add_id_with_vars(msg_id::IMPORT_IP_MISSING, [
                msg_id::VAR_NAME => json_fields::IP_TO,
                msg_id::VAR_IP_RANGE => json_encode($in_ex_json),
            ]);
        }
        if (key_exists(json_fields::REASON, $in_ex_json)) {
            $this->reason = $in_ex_json[json_fields::REASON];
        }
        if (key_exists(json_fields::IS_ACTIVE, $in_ex_json)) {
            $this->active = filter_var($in_ex_json[json_fields::IS_ACTIVE], FILTER_VALIDATE_BOOLEAN);
        }

        return $usr_msg->is_ok();
    }


    /*
     * set and get
     */

    /**
     * set the user of the ip range if needed
     *
     * @param user|null $usr the person who wants to use the ip range
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who uses the ip range and null if for all users
     */
    function get_user(): ?user
    {
        return $this->usr;
    }

    /**
     * @return string|null the unique key of the ip range
     */
    function key(): string|null
    {
        if ($this->from <> '' and $this->to <> '') {
            return $this->from . '-' . $this->to;
        } else {
            return null;
        }
    }


    /*
     * load
     */

    /**
     * load an ip range from the database selected by id
     * @param int $id the id of an ip range
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_id(int $id): int
    {
        global $db_con;

        $this->reset();
        $qp = $this->load_sql_by_id($db_con->sql_creator(), $id);
        return $this->load($qp);
    }

    /**
     * load an ip range from the database selected by the start and end ip address
     * @param string $ip_from the start ip address that should be used for the query
     * @param string $ip_to the end ip address that should be used for the query
     * @return int the id of the object found and zero if nothing is found
     */
    function load_by_ip_addresses(string $ip_from, string $ip_to): int
    {
        global $db_con;

        $this->reset();
        $qp = $this->load_sql_by_ip_addresses($db_con->sql_creator(), $ip_from, $ip_to);
        return $this->load($qp);
    }


    /*
     * load sql
     */

    /**
     * create the common part of an SQL statement to retrieve an ip range from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name of the selection fields to make the query name unique
     * @param string $class the name of the child class from where the call has been triggered
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name, string $class = self::class): sql_par
    {
        $qp = parent::load_sql($sc, $query_name, $class);
        $sc->set_class($class);

        $sc->set_name($qp->name);
        $sc->set_fields(ip_range_db::FLD_NAMES);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve the ip range from the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $ip_from the start ip address that should be used for the query
     * @param string $ip_to the end ip address that should be used for the query
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql_by_ip_addresses(sql_creator $sc, string $ip_from, string $ip_to): sql_par
    {
        $qp = $this->load_sql($sc, 'ip_addresses');
        $sc->add_where(ip_range_db::FLD_FROM, $ip_from);
        $sc->add_where(ip_range_db::FLD_TO, $ip_to);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }


    /*
     * im- and export
     */

    /**
     * import an ip range from an imported json object
     *
     * @param array $in_ex_json an array with the data of the json object
     * @param user_message $usr_msg to enrich with warnings, problems and solutions
     * @param data_object|null $dto cache of the objects imported until now for the primary references
     * @return bool true if everything was fine
     */
    function import_obj(
        array        $in_ex_json,
        user_message $usr_msg,
        ?data_object $dto = null
    ): bool
    {
        global $db_con;

        $this->import_mapper($in_ex_json, $usr_msg);

        // reset of object not needed, because the calling function has just created the object
        // TODO Prio 0 switch to a key_exists
        foreach ($in_ex_json as $key => $value) {
            if ($key == ip_range_db::FLD_FROM) {
                $this->from = $value;
            }
            if ($key == ip_range_db::FLD_TO) {
                $this->to = $value;
            }
            if ($key == ip_range_db::FLD_REASON) {
                $this->reason = $value;
            }
            if ($key == ip_range_db::FLD_ACTIVE) {
                $this->active = $value;
            }
        }

        // save the ip range in the database
        if ($db_con->is_open()) {
            if ($usr_msg->is_ok()) {
                $this->save($usr_msg);
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * create an array with the export json fields
     * @param export_type_list|array $exp_typ define the export format
     * @param bool $do_load to switch off the database load for unit tests
     * @return array the filled array used to create the user export json
     */
    function export_json(export_type_list|array $exp_typ = [], bool $do_load = true): array
    {
        $vars = [];

        // in this case simply map the fields
        $vars[json_fields::IP_FROM] = $this->from;
        $vars[json_fields::IP_TO] = $this->to;
        $vars[json_fields::REASON] = $this->reason;
        $vars[json_fields::IS_ACTIVE] = $this->active;

        return $vars;
    }


    /*
     * check
     */

    /**
     * check if an ip address is within this range
     *
     * @param string $ip_addr the ip address to check
     * @return bool true if the given ip address is within the ip range
     */
    function includes(string $ip_addr): bool
    {
        $result = false;
        if (ip2long(trim($this->from)) <= ip2long(trim($ip_addr))
            && ip2long(trim($ip_addr)) <= ip2long(trim($this->to))) {
            log_debug(' ip ' . $ip_addr . ' (' . ip2long(trim($ip_addr)) . ') is in range between ' .
                $this->from . ' (' . ip2long(trim($this->from)) . ') and  ' .
                $this->to . ' (' . ip2long(trim($this->to)) . ')');
            $result = true;
        }
        return $result;
    }

    protected function can_delete(user_message $usr_msg): bool
    {
        $can_del = false;
        if ($usr_msg->usr->is_admin() or $usr_msg->usr->is_system()) {
            $can_del = true;
        }
        return $can_del;
    }


    /*
     * save
     */

    /**
     * add an ip range to the database
     *
     * @param user_message $usr_msg with status OK
     *                              or if something went wrong
     *                              the message that should be shown to the user
     *                              including suggested solutions
     * @return bool true if everything has been fine
     */
    private function add(user_message $usr_msg): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc, $usr_msg, new sql_type_list([sql_type::LOG]));
        if ($usr_msg->is_ok()) {
            $msg = 'add and log ' . $this->dsp_id();
            if ($db_con->insert($qp, $msg, $usr_msg)) {
                $this->id = $usr_msg->get_row_id();
            }
        }

        return $usr_msg->is_ok();
    }

    /**
     * get a similar or overlapping ip range
     *
     * @return ip_range|db_object|null the ip range that matches e.g. to update the reason
     */
    function get_similar(user_message $usr_msg): ip_range|db_object|null
    {
        $result = null;

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->set_user($this->get_user());
        $db_chk->load_by_ip_addresses($this->from, $this->to);
        if ($db_chk->id() > 0) {
            log_debug('->get_similar an ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    /**
     * update an ip range in the database or update the existing
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @param sql_type_list|array $sc_par_lst the parameters for the sql statement creation
     * @return bool true if everything has been fine
     */
    function save(
        user_message        $usr_msg,
        sql_type_list|array $sc_par_lst = []
    ): bool
    {
        log_debug('ip_range->save ' . $this->dsp_id());

        global $db_con;

        // by default all verb changes are logged
        if (is_array($sc_par_lst)) {
            if ($sc_par_lst == []) {
                $sc_par_lst = new sql_type_list([sql_type::LOG]);
            } else {
                $sc_par_lst = new sql_type_list($sc_par_lst);
            }
        }

        // build the database object because this is needed anyway
        $db_con->set_usr($this->get_user()->id);
        $db_con->set_class($this::class);

        // check if the external reference is supposed to be added
        if ($this->id() <= 0) {
            // check possible duplicates before adding
            log_debug('->save check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar($usr_msg);
            if ($similar != null) {
                if ($similar->id() != 0) {
                    $this->id = $similar->id();
                }
            }
        }

        // create a new object or update an existing
        if ($this->id() <= 0) {
            if (!$this->add($usr_msg)) {
                log_warning('ip range add failed');
            }
        } else {
            log_debug('->save update');

            // read the database values to be able to check if something has been changed;
            // done first, because it needs to be done for user and general object values
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->set_user($this->get_user());
            $db_rec->load_by_id($this->id());
            if ($db_rec->id() > 0) {
                if ($this->needs_db_update($db_rec)) {
                    // ... create the prepared sql function ...
                    $sc = $db_con->sql_creator();
                    $qp = $this->sql_update($sc, $db_rec, $usr_msg, $sc_par_lst);

                    // ... and update the database row
                    $db_con->update($qp, 'update ' . $this->dsp_id(), $usr_msg);
                }
            }
        }
        return $usr_msg->is_ok();
    }

    /**
     * helper because the db id field differs from the class name
     * @return string the field name of the prime database index of the object
     */
    function id_field(): string
    {
        return ip_range_db::FLD_ID;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a object e.g. word to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_par $qp
     * @param sql_par_field_list $fvt_lst list of field names, values and sql types additional to the standard id and name fields
     * @param string $id_fld_new
     * @param user_message $usr_msg collect the messages for the user
     * @param sql_type_list $sc_par_lst_sub the parameters for the sql statement creation
     * @return sql_par the SQL insert statement, the name of the SQL statement, and the parameter list
     */
    function sql_insert_key_field(
        sql_creator        $sc,
        sql_par            $qp,
        sql_par_field_list $fvt_lst,
        string             $id_fld_new,
        user_message       $usr_msg,
        sql_type_list      $sc_par_lst_sub = new sql_type_list()
    ): sql_par
    {
        // set some var names to shorten the code lines
        $ext = sql::NAME_SEP . sql_creator::FILE_INSERT;

        // list of parameters actually used in order of the function usage
        $sql = '';
        $fvt_insert = $fvt_lst->get($this->name_field(), $usr_msg);

        // create the sql to insert the row
        if ($usr_msg->is_ok()) {
            $fvt_insert_list = new sql_par_field_list();
            $fvt_insert_list->add($fvt_insert);
            $sc_insert = clone $sc;
            $qp_insert = $this->sql_common($sc_insert, $sc_par_lst_sub, $ext);
            $sc_par_lst_sub->add(sql_type::SELECT_FOR_INSERT);
            if ($sc->db_type == sql_db::MYSQL) {
                $sc_par_lst_sub->add(sql_type::NO_ID_RETURN);
            }
            $qp_insert->sql = $sc_insert->create_sql_insert(
                $fvt_insert_list, $sc_par_lst_sub, true, '', '', '', $id_fld_new);
            $qp_insert->par = [$fvt_insert->value];

            // add the insert row to the function body
            $sql .= ' ' . $qp_insert->sql . '; ';

            // get the new row id for MySQL db
            if ($sc->db_type == sql_db::MYSQL) {
                $sql .= ' ' . sql::LAST_ID_MYSQL . $sc->var_name_row_id($sc_par_lst_sub) . '; ';
            }

            $qp->sql = $sql;
            $qp->par_fld = $fvt_insert;
        }

        return $qp;
    }


    /*
     * sql helper
     */

    /**
     * check if the named object in the database needs to be updated
     * is expected to be similar to the diff_msg function
     *
     * @param ip_range|IdObject $db_obj the word as saved in the database
     * @return bool true if this word has infos that should be saved in the database
     */
    function needs_db_update(ip_range|IdObject $db_obj): bool
    {
        $result = parent::needs_db_update($db_obj);
        // TODO Prio 3 review
        if ($db_obj->id() == $this->id()) {
            $result = false;
        }
        if ($db_obj->from !== $this->from) {
            $result = true;
        }
        if ($db_obj->to !== $this->to) {
            $result = true;
        }
        if ($db_obj->reason !== $this->reason) {
            $result = true;
        }
        if ($db_obj->active !== $this->active) {
            $result = true;
        }
        return $result;
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
                ip_range_db::FLD_KEY,
                ip_range_db::FLD_FROM,
                ip_range_db::FLD_TO,
                ip_range_db::FLD_REASON,
                ip_range_db::FLD_ACTIVE,
            ]
        );
    }

    /**
     * get a list of database field names, values and types that have been updated
     *
     * @param ip_range|db_object_seq_id $obj the compare value to detect the changed fields
     * @param user_message $usr_msg the user message object that collects any issues during the sql creation
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @return sql_par_field_list list 3 entry arrays with the database field name, the value and the sql type that have been updated
     */
    function db_fields_changed(
        ip_range|db_object_seq_id $obj,
        user_message              $usr_msg,
        sql_type_list             $sc_par_lst = new sql_type_list()
    ): sql_par_field_list
    {
        global $sys;

        $sc = new sql_creator();
        $do_log = $sc_par_lst->incl_log();
        $table_id = $sc->table_id($this::class);

        $lst = parent::db_fields_changed($obj, $usr_msg, $sc_par_lst);
        if ($obj->key() <> $this->key()) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ip_range_db::FLD_KEY,
                    $sys->typ_lst->cng_fld->id($table_id . ip_range_db::FLD_KEY),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                ip_range_db::FLD_KEY,
                $this->key(),
                sql_field_type::TEXT,
                $obj->key()
            );
        }
        if ($obj->from !== $this->from) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ip_range_db::FLD_FROM,
                    $sys->typ_lst->cng_fld->id($table_id . ip_range_db::FLD_FROM),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                ip_range_db::FLD_FROM,
                $this->from,
                sql_field_type::TEXT,
                $obj->from
            );
        }
        if ($obj->to !== $this->to) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ip_range_db::FLD_TO,
                    $sys->typ_lst->cng_fld->id($table_id . ip_range_db::FLD_TO),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                ip_range_db::FLD_TO,
                $this->to,
                sql_field_type::TEXT,
                $obj->to
            );
        }
        if ($obj->reason !== $this->reason) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ip_range_db::FLD_REASON,
                    $sys->typ_lst->cng_fld->id($table_id . ip_range_db::FLD_REASON),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            $lst->add_field(
                ip_range_db::FLD_REASON,
                $this->reason,
                sql_field_type::TEXT,
                $obj->reason
            );
        }
        if ($obj->active !== $this->active) {
            if ($do_log) {
                $lst->add_field(
                    sql::FLD_LOG_FIELD_PREFIX . ip_range_db::FLD_ACTIVE,
                    $sys->typ_lst->cng_fld->id($table_id . ip_range_db::FLD_ACTIVE),
                    change::FLD_FIELD_ID_SQL_TYP
                );
            }
            // TODO Prio 2 review and remove exception if possible
            $old_val = $obj->active;
            if ($obj->active === false) {
                $old_val = null;
            }
            $lst->add_field(
                ip_range_db::FLD_ACTIVE,
                $this->active,
                sql_field_type::BOOL,
                $old_val
            );
        }
        return $lst;
    }


    /*
     * sql fields
     */

    function name_field(): string
    {
        return ip_range_db::FLD_KEY;
    }


    /*
     * db helper
     */

    /**
     * check if the user can add this object to the database
     * e.g. reject if a reserved name is used and the user is not a system test user or an admin user
     * to be overwritten by the child objects
     *
     * @param user_message $usr_msg the message object that is enriched in case something went wrong to show the user the problem and the suggested solutions
     * @return bool true if everything has been fine
     */
    protected function check(user_message $usr_msg): bool
    {
        // to and from must be a valid ip address
        if ($this->from == '') {
            $usr_msg->add_err_with_vars(msg_id::IP_RANGE_FROM_MISSING, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        }
        if ($this->to == '') {
            $usr_msg->add_err_with_vars(msg_id::IP_RANGE_TO_MISSING, [
                msg_id::VAR_NAME => $this->dsp_id()
            ]);
        }
        return $usr_msg->is_ok();
    }



    /*
     * debug
     */

    /**
     * @return string to display the identifying ip range fields e.g. for a debug message
     */
    function dsp_id(): string
    {
        $lib = new library();
        $class_name = $lib->class_to_name($this::class);
        $result = $class_name . ' ' . $this->name();
        if ($result <> '') {
            if ($this->id() > 0) {
                $result .= ' (' . $this->id() . ')';
            }
        } else {
            $result .= $this->id();
        }
        return $result;
    }

    /**
     * @return string with the unique name of the ip range
     */
    function name(): string
    {
        return 'from ' . $this->from . ' to ' . $this->to;
    }

}

