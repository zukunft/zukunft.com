<?php

/*

    user_log.php - object to save the user changes in the database in a format, so that it can fast be displayed to the user
    ------------

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

/*

Rules:
Never change a ID
never delete a word

Every user has its sandbox, means a list of all his changes


The normal word table contain the value, word, formula, verb or links that is used by most users
for each normal table there is an overwrite table with the user changes/overwrites
maybe for each huge table is also a log table with the hist of the user changes

TODO:

cache table, field and action id to speed up, because this will never change

*/


class user_log
{
    // the basic change types that are logged
    const ACTION_ADD = 'add';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'del';

    /*
     * database link
     */

    // user log database and JSON object field names
    const FLD_ID = 'change_id';
    const FLD_FIELD_ID = 'change_field_id';
    const FLD_ROW_ID = 'row_id';
    const FLD_CHANGE_TIME = 'change_time';
    const FLD_USER_NAME = 'user_name';
    const FLD_OLD_VALUE = 'old_value';
    const FLD_OLD_ID = 'old_id';
    const FLD_NEW_VALUE = 'new_value';
    const FLD_NEW_ID = 'new_id';

    // all database field names
    const FLD_NAMES = array(
        user::FLD_ID,
        self::FLD_FIELD_ID,
        self::FLD_ROW_ID,
        self::FLD_CHANGE_TIME,
        self::FLD_OLD_VALUE,
        self::FLD_OLD_ID,
        self::FLD_NEW_VALUE,
        self::FLD_NEW_ID
    );

    /*
     * object vars
     */

    public ?int $id = null;            // the database id of the log entry (used to update a log entry in case of an insert where the ref id is not yet know at insert)
    public ?user $usr = null;          // the user who has done the change
    public ?string $action = null;     // text for the user action e.g. "add", "update" or "delete"
    protected ?int $action_id = null;  // database id for the action text
    public ?string $table = null;      // name of the table that has been updated
    private ?int $table_id = null;     // database id for the table text
    public ?string $field = null;      // name of the field that has been updated
    protected ?int $field_id = null;   // database id for the field text
    public ?int $row_id = null;        // the reference id of the row in the database table


    protected ?string $user_name = null;   //
    protected ?string $change_time = null; //
    protected ?string $old_value = null;   //
    protected ?int $old_id = null;         //
    protected ?string $new_value = null;   //
    protected ?int $new_id = null;         //

    /**
     * @return bool true if a row is found
     */
    function row_mapper(array $db_row): bool
    {
        if ($db_row[self::FLD_ID] > 0) {
            $this->id = $db_row[self::FLD_ID];
            $this->field_id = $db_row[self::FLD_FIELD_ID];
            $this->row_id = $db_row[self::FLD_ROW_ID];
            $this->change_time = $db_row[self::FLD_CHANGE_TIME];
            $this->old_value = $db_row[self::FLD_OLD_VALUE];
            $this->old_id = $db_row[self::FLD_OLD_ID];
            $this->new_value = $db_row[self::FLD_NEW_VALUE];
            $this->new_id = $db_row[self::FLD_NEW_ID];
            $this->user_name = $db_row[user::FLD_NAME];
            return true;
        } else {
            return false;
        }
    }


    /**
     * to save database space the table name is saved as a reference id in the log table
     */
    protected function set_table(): void
    {
        if ($this->usr == null) {
            log_warning('user_log->set_table "' . $this->table . '" but user is missing');
        } else {
            log_debug('user_log->set_table "' . $this->table . '" for ' . $this->usr->dsp_id());
        }

        global $db_con;

        // check parameter
        if ($this->table == "") {
            log_err("missing table name", "user_log->set_table");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log->set_table");
        }

        // if e.g. a "value" is changed $this->table is "values" and the reference 1 is saved in the log to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_TABLE);
        $db_con->set_usr($this->usr->id);
        $table_id = $db_con->get_id($this->table);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($this->table);
            // save also the code_id
            if ($table_id > 0) {
                $db_con->set_type(DB_TYPE_CHANGE_TABLE);
                $db_con->set_usr($this->usr->id);
                $db_con->update($table_id, array('code_id'), array($this->table));
            }
        }
        if ($table_id > 0) {
            $this->table_id = $table_id;
        } else {
            log_fatal("Insert to change log failed due to table id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
    }

    /**
     * save the field name as a reference id in the log table
     */
    protected function set_field(): void
    {
        if ($this->usr == null) {
            log_warning('user_log->set_field "' . $this->table . '" but user is missing');
        } else {
            log_debug('user_log->set_field "' . $this->field . '" for table "' . $this->table . '" (' . $this->table_id . ') and user ' . $this->usr->dsp_id());
        }

        global $db_con;

        // check parameter
        if ($this->table_id <= 0) {
            log_err("missing table_id", "user_log->set_field");
        }
        if ($this->field == "") {
            log_err("missing field name", "user_log->set_field");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log->set_field");
        }

        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_FIELD);
        $db_con->usr_id = $this->usr->id;
        $field_id = $db_con->get_id_2key($this->field, "table_id", $this->table_id);

        // add new field name if needed
        if ($field_id <= 0) {
            $field_id = $db_con->add_id_2key($this->field, "table_id", $this->table_id);
        }
        if ($field_id > 0) {
            $this->field_id = $field_id;
        } else {
            log_fatal("Insert to change log failed due to field id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
    }

    protected function set_action(): void
    {
        log_debug('user_log->set_action "' . $this->action . '" for ' . $this->usr->id);

        global $db_con;

        // check parameter
        if ($this->action == "") {
            log_err("missing action name", "user_log->set_action");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log->set_action");
        }

        // if e.g. the action is "add" the reference 1 is saved in the log table to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(DB_TYPE_CHANGE_ACTION);
        $db_con->usr_id = $this->usr->id;
        $action_id = $db_con->get_id($this->action);

        // add new action name if needed
        if ($action_id <= 0) {
            $action_id = $db_con->add_id($this->action);
        }
        if ($action_id > 0) {
            $this->action_id = $action_id;
        } else {
            log_fatal("Insert to change log failed due to action id failure.", "user_log->set_action");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
    }

    function load_sql(sql_db $db_con, int $field_id, int $row_id): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'field_row';
        $db_con->set_type(DB_TYPE_CHANGE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_join_fields(array(user::FLD_NAME),DB_TYPE_USER);
        $db_con->set_where_text($db_con->where_par(array(self::FLD_FIELD_ID, self::FLD_ROW_ID), array($field_id, $row_id)));
        $db_con->set_order(self::FLD_ID, sql_db::ORDER_DESC);
        $qp->sql = $db_con->select_by_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }


    /**
     * display the last change related to one object (word, formula, value, verb, ...)
     * mainly used for testing
     * TODO if changes on table values are requested include also the table "user_values"
     */
    function dsp_last(bool $ex_time): string
    {

        global $db_con;
        $result = '';

        $this->set_table();
        $this->set_field();

        $db_type = $db_con->get_type();
        $qp = $this->load_sql($db_con, $this->field_id, $this->row_id);
        $db_row = $db_con->get1($qp);
        $this->row_mapper($db_row);
        if ($db_row) {
            if (!$ex_time) {
                $result .= $this->change_time . ' ';
            }
            if ($this->user_name <> '') {
                $result .= $this->user_name . ' ';
            }
            if ($this->old_value <> '') {
                if ($this->new_value <> '') {
                    $result .= 'changed ' . $this->old_value . ' to ' . $this->new_value;
                } else {
                    $result .= 'deleted ' . $this->old_value;
                }
            } else {
                $result .= 'added ' . $this->new_value;
            }
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
        return $result;
    }

    /**
     * log a user change of a word, value or formula
     */
    function add(): bool
    {
        return true;
    }

    // add the row id to an existing log entry
    // e.g. because the row id is known after the adding of the real record,
    // but the log entry has been created upfront to make sure that logging is complete
    function add_ref($row_id): bool
    {
        return true;
    }


}