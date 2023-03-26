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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace model;

class ip_range
{

    const OBJ_NAME = 'ip range';

    /*
     * database link
     */

    // database and JSON object field names
    const FLD_ID = 'user_blocked_id';
    const FLD_FROM = 'ip_from';
    const FLD_TO = 'ip_to';
    const FLD_REASON = 'reason';
    const FLD_ACTIVE = 'is_active';

    const FLD_NAMES = array(
        self::FLD_FROM,
        self::FLD_TO,
        self::FLD_REASON,
        self::FLD_ACTIVE
    );


    /*
     * object vars
     */

    // database fields
    public ?int $id = null;               // the database id of the reference
    public string $from = '';
    public string $to = '';
    public ?string $reason = null;
    public bool $active = false;

    // in memory only fields
    private ?user $usr = null;             // just needed for logging the changes


    /*
     * construct and map
     */

    function reset(): void
    {
        $this->id = null;
        $this->from = '';
        $this->to = '';
        $this->reason = null;
        $this->active = false;

        $this->set_user(null);
    }

    function row_mapper(array $db_row): bool
    {
        $result = true;
        if ($db_row != null) {
            if ($db_row[self::FLD_ID] > 0) {
                $this->id = $db_row[self::FLD_ID];
                $this->from = $db_row[self::FLD_FROM];
                $this->to = $db_row[self::FLD_TO];
                $this->reason = $db_row[self::FLD_REASON];
                $this->active = $db_row[self::FLD_ACTIVE];
            } else {
                $result = false;
                $this->id = 0;
            }
        } else {
            $result = false;
            $this->id = 0;
        }
        return $result;
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
     * @returns int the protected id of the ip range
     */
    function id(): int
    {
        return $this->id;
    }

    /**
     * @return user|null the person who uses the ip range and null if for all users
     */
    function user(): ?user
    {
        return $this->usr;
    }


    /*
     * loading
     */

    /**
     * create an SQL statement to retrieve the ip range from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $db_con->set_type(sql_db::TBL_IP);
        $lib = new library();
        $class = $lib->str_right_of_or_all(self::class, '\\');
        $qp = new sql_par($class);
        $qp->name = $class . '_by_';
        $sql_where = '';
        if ($this->id != 0) {
            $qp->name .= 'id';
            $db_con->add_par(sql_db::PAR_INT, $this->id);
            $sql_where .= self::FLD_ID . ' = ' . $db_con->par_name();
        } elseif ($this->from != '' and $this->to != '') {
            $qp->name .= 'range';
            $db_con->add_par(sql_db::PAR_TEXT, $this->from);
            $sql_where .= self::FLD_FROM . " = " . $db_con->par_name();
            $db_con->add_par(sql_db::PAR_TEXT, $this->to);
            $sql_where .= " and " . self::FLD_TO . " = " . $db_con->par_name();
        } else {
            $qp->name = '';
            log_err("Either the database ID (" . $this->id .
                ") or the ip range (" . $this->dsp_id() .
                ") must be set to load an ip range.", $class . '->load_sql');
        }

        if ($qp->name != '') {
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(self::FLD_NAMES);
            $db_con->set_where_text($sql_where);
            $qp->sql = $db_con->select_by_set_id();
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * load an existing ip range to update the reason or switch it off
     *
     * @return bool true if an ip range has been found and loaded
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql($db_con);

        if ($qp->sql <> '') {
            $db_row = $db_con->get1($qp);
            $result = $this->row_mapper($db_row);
        }

        return $result;
    }

    function load_by_id(int $id): bool
    {
        $this->reset();
        $this->id = $id;
        return $this->load();
    }


    /*
     * im- and export
     */

    /**
     * import an ip range from an imported json object
     *
     * @param array $json_obj an array with the data of the json object
     * @param bool $do_save can be set to false for unit testing
     * @return user_message the status of the import and if needed the error messages that should be shown to the user
     */
    function import_obj(array $json_obj, bool $do_save = true): user_message
    {
        $result = new user_message();

        // reset of object not needed, because the calling function has just created the object
        foreach ($json_obj as $key => $value) {
            if ($key == self::FLD_FROM) {
                $this->from = $value;
            }
            if ($key == self::FLD_TO) {
                $this->to = $value;
            }
            if ($key == self::FLD_REASON) {
                $this->reason = $value;
            }
            if ($key == self::FLD_ACTIVE) {
                $this->active = $value;
            }
        }
        if ($result->is_ok() and $do_save) {
            $result->add_message($this->save());
        }

        return $result;
    }

    /**
     * create an object for the export
     */
    function export_obj(): ip_range_exp
    {
        $result = new ip_range_exp();

        // in this case simply map the fields
        $result->ip_from = $this->from;
        $result->ip_to = $this->to;
        $result->reason = $this->reason;
        $result->is_active = $this->active;

        return $result;
    }


    /*
     * display
     */

    /**
     * @return string with the unique name of the ip range
     */
    function name(): string
    {
        return 'from ' . $this->from . ' to ' . $this->to;
    }

    /**
     * @return string to display the identifying ip range fields e.g. for a debug message
     */
    function dsp_id(): string
    {
        $result = self::OBJ_NAME . ' ' . $this->name();
        if ($result <> '') {
            if ($this->id > 0) {
                $result .= ' (' . $this->id . ')';
            }
        } else {
            $result .= $this->id;
        }
        return $result;
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


    /*
     * save
     */

    /**
     * actually update a formula field in the main database record
     * @param sql_db $db_con the active database connection
     * @param change_log_named $log with the action and table already set
     * @return string string any message that should be shown to the user or an empty string if everything is fine
     */
    private function save_field_do(sql_db $db_con, change_log_named $log): string
    {
        $result = '';
        if ($log->add()) {
            $db_con->set_type(sql_db::TBL_IP);
            if (!$db_con->update($this->id, $log->field(), $log->new_value)) {
                $result .= 'updating ' . $log->field() . ' to ' . $log->new_value . ' for ' . self::OBJ_NAME . ' ' . $this->dsp_id() . ' failed';
            }

        }
        return $result;
    }

    /**
     * set the update parameters for the block reason
     * @param sql_db $db_con the active database connection
     * @param ip_range $db_rec the ip range reason as saved in the database before the change
     * @return string string any message that should be shown to the user or an empty string if everything is fine
     */
    private function save_field_reason(sql_db $db_con, ip_range $db_rec): string
    {
        $result = '';
        if ($db_rec->reason <> $this->reason) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->reason;
            $log->new_value = $this->reason;
            $log->std_value = $db_rec->reason;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_REASON);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the update parameters for the block reason
     * @param sql_db $db_con the active database connection
     * @param ip_range $db_rec the ip range active flag as saved in the database before the change
     * @return string string any message that should be shown to the user or an empty string if everything is fine
     */
    private function save_field_active(sql_db $db_con, ip_range $db_rec): string
    {
        $result = '';
        if ($db_rec->active <> $this->active) {
            $log = $this->log_upd();
            $log->old_value = $db_rec->active;
            $log->new_value = $this->active;
            $log->std_value = $db_rec->active;
            $log->row_id = $this->id;
            $log->set_field(self::FLD_ACTIVE);
            $result .= $this->save_field_do($db_con, $log);
        }
        return $result;
    }

    /**
     * set the log entry parameter for a new ip range
     * @return change_log_named with the action set to add
     */
    function log_add(): change_log_named
    {
        log_debug('->log_add ' . $this->dsp_id());

        $log = new change_log_named;
        $log->usr = $this->user();
        $log->action = change_log_action::ADD;
        $log->set_table(sql_db::TBL_IP);
        $log->set_field(self::FLD_FROM . '_' . self::FLD_TO);
        $log->row_id = 0;
        $log->add();

        return $log;
    }

    /**
     * set the main log entry parameters for updating one verb field
     * @return change_log_named with the action set to update
     */
    private function log_upd(): change_log_named
    {
        log_debug('->log_upd ' . $this->dsp_id());
        $log = new change_log_named;
        $log->usr = $this->user();
        $log->action = change_log_action::UPDATE;
        $log->set_table(sql_db::TBL_IP);

        return $log;
    }

    /**
     * save all updated verb fields excluding the name, because already done when adding a verb
     * @param sql_db $db_con the active database connection
     * @param ip_range $db_rec the ip range entry as saved in the database before the change
     * @return string string any message that should be shown to the user or an empty string if everything is fine
     */
    private function save_fields(sql_db $db_con, ip_range $db_rec): string
    {
        $result = $this->save_field_reason($db_con, $db_rec);
        $result .= $this->save_field_active($db_con, $db_rec);
        return $result;
    }

    /**
     * add an ip range to the database
     *
     * @return string the database id of the created reference or 0 if not successful
     */
    private function add(): string
    {
        global $db_con;
        $result = '';

        // log the insert attempt first
        $log = $this->log_add();
        if ($log->id() > 0) {
            // insert the new ip range
            $db_con->set_type(sql_db::TBL_IP);
            $db_con->set_usr($this->user()->id());

            $this->id = $db_con->insert(
                array(self::FLD_FROM, self::FLD_TO, self::FLD_REASON, self::FLD_ACTIVE),
                array($this->from, $this->to, $this->reason, $this->active));
            if ($this->id > 0) {
                // update the id in the log for the correct reference
                if (!$log->add_ref($this->id)) {
                    $result .= 'Adding reference for ' . $this->dsp_id() . ' in the log failed.';
                    log_err($result, self::class . '->add');
                }
            } else {
                $result .= 'Adding reference ' . $this->dsp_id() . ' failed.';
                log_err($result, self::class . '->add');
            }
        }

        return $result;
    }

    /**
     * get a similar or overlapping ip range
     *
     * @return ip_range|null the ip range that matches e.g. to update the reason
     */
    function get_similar(): ?ip_range
    {
        $result = null;

        $db_chk = clone $this;
        $db_chk->reset();
        $db_chk->id = $this->id;
        $db_chk->from = $this->from;
        $db_chk->to = $this->to;
        $db_chk->set_user($this->user());
        $db_chk->load();
        if ($db_chk->id > 0) {
            log_debug('->get_similar an ' . $this->dsp_id() . ' already exists');
            $result = $db_chk;
        }

        return $result;
    }

    /**
     * update an ip range in the database or update the existing
     * @return string the error message for the user if it has failed or an empty string
     */
    function save(): string
    {
        log_debug('ip_range->save ' . $this->dsp_id());

        global $db_con;
        $result = '';

        // build the database object because this is needed anyway
        $db_con->set_usr($this->user()->id());
        $db_con->set_type(sql_db::TBL_IP);

        // check if the external reference is supposed to be added
        if ($this->id <= 0) {
            // check possible duplicates before adding
            log_debug('->save check possible duplicates before adding ' . $this->dsp_id());
            $similar = $this->get_similar();
            if (isset($similar)) {
                if ($similar->id <> 0) {
                    $this->id = $similar->id;
                }
            }
        }

        // create a new object or update an existing
        if ($this->id <= 0) {
            $result .= $this->add();
        } else {
            log_debug('->save update');

            // read the database values to be able to check if something has been changed;
            // done first, because it needs to be done for user and general object values
            $db_rec = clone $this;
            $db_rec->reset();
            $db_rec->id = $this->id;
            $db_rec->set_user($this->user());
            if ($db_rec->load()) {
                $result .= $this->save_fields($db_con, $db_rec);
            }
        }
        return $result;
    }

}

/**
 * the helper class to im- and export an ip range filter
 */
class ip_range_exp
{

    // field names used for JSON creation
    public string $ip_from = '';
    public string $ip_to = '';
    public ?string $reason = null;
    public bool $is_active = false;

    function reset(): void
    {
        $this->ip_from = '';
        $this->ip_to = '';
        $this->reason = null;
        $this->is_active = false;
    }

}
