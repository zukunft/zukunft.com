<?php

/*

    user_log_named.php - user_log object for logging changes in named objects such as words and formulas
    ------------------

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

class user_log_named extends user_log
{

    // user log database and JSON object field names for named user sandbox objects
    const FLD_FIELD_ID = 'change_field_id';
    const FLD_ROW_ID = 'row_id';
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

    // additional
    public ?string $old_value = null;      // the field value before the user change
    public ?int $old_id = null;            // the reference id before the user change e.g. for fields using a sub table such as status
    public ?string $new_value = null;      // the field value after the user change
    public ?int $new_id = null;            // the reference id after the user change e.g. for fields using a sub table such as status
    public ?string $std_value = null;  // the standard field value for all users that does not have changed it
    public ?int $std_id = null;        // the standard reference id for all users that does not have changed it

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

    function load_sql(sql_db $db_con, int $field_id, int $row_id): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'field_row';
        $db_con->set_type(sql_db::TBL_CHANGE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_join_fields(array(user::FLD_NAME),sql_db::TBL_USER);
        $db_con->set_where_text($db_con->where_par(array(self::FLD_FIELD_ID, self::FLD_ROW_ID), array($field_id, $row_id)));
        $db_con->set_order(self::FLD_ID, sql_db::ORDER_DESC);
        $qp->sql = $db_con->select_by_set_id();
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

        parent::set_table();
        parent::set_field();

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

    // log a user change of a word, value or formula
    function add(): bool
    {
        log_debug('user_log->add do "' . $this->action . '" in "' . $this->table . ',' . $this->field . '" log change from "' . $this->old_value . '" (id ' . $this->old_id . ') to "' . $this->new_value . '" (id ' . $this->new_id . ') in row ' . $this->row_id);

        global $db_con;

        parent::set_table();
        parent::set_field();
        parent::set_action();

        $sql_fields = array();
        $sql_values = array();
        $sql_fields[] = "user_id";
        $sql_values[] = $this->usr->id;
        $sql_fields[] = "change_action_id";
        $sql_values[] = $this->action_id;
        $sql_fields[] = "change_field_id";
        $sql_values[] = $this->field_id;

        $sql_fields[] = "old_value";
        $sql_values[] = $this->old_value;
        $sql_fields[] = "new_value";
        $sql_values[] = $this->new_value;

        if ($this->old_id > 0 or $this->new_id > 0) {
            $sql_fields[] = "old_id";
            $sql_values[] = $this->old_id;
            $sql_fields[] = "new_id";
            $sql_values[] = $this->new_id;
        }

        $sql_fields[] = "row_id";
        $sql_values[] = $this->row_id;

        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_CHANGE);
        $db_con->set_usr($this->usr->id);
        $log_id = $db_con->insert($sql_fields, $sql_values);

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->usr == null) {
                log_fatal("Insert to change log failed.", "user_log->add", 'Insert to change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Insert to change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table . "," . $this->field . ")", "user_log->add");
                log_fatal("Insert to change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table . "," . $this->field . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add");
            }
            $result = False;
        } else {
            $this->id = $log_id;
            // restore the type before saving the log
            $db_con->set_type($db_type);
            $result = True;
        }

        return $result;
    }

    /**
     * add the row id to an existing log entry
     * e.g. because the row id is known after the adding of the real record,
     * but the log entry has been created upfront to make sure that logging is complete
     */
    function add_ref($row_id): bool
    {
        log_debug("user_log->add_ref (" . $row_id . " to " . $this->id . " for user " . $this->usr->dsp_id() . ")");

        global $db_con;
        $result = false;

        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_CHANGE);
        $db_con->set_usr($this->usr->id);
        if ($db_con->update($this->id, "row_id", $row_id)) {
            // restore the type before saving the log
            $db_con->set_type($db_type);
            $result = True;
        } else {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->usr == null) {
                log_fatal("Update of reference in the change log failed.", "user_log->add_ref", 'Update of reference in the change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Update of reference in the change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table . "," . $this->field . ")", "user_log->add_ref");
                log_fatal("Update of reference in the change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table . "," . $this->field . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add_ref");
            }
        }
        return $result;
    }


}