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

use api\change_log_named_dsp;
use controller\log\change_log_named_api;

class change_log_named extends change_log
{

    /*
      * database link
      */

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
        self::FLD_CHANGE_TIME,
        self::FLD_ACTION,
        self::FLD_FIELD_ID,
        self::FLD_ROW_ID,
        self::FLD_OLD_VALUE,
        self::FLD_OLD_ID,
        self::FLD_NEW_VALUE,
        self::FLD_NEW_ID
    );

    /*
     * object vars
     */

    // additional to user_log
    public ?string $old_value = null;      // the field value before the user change
    public ?int $old_id = null;            // the reference id before the user change e.g. for fields using a sub table such as status
    public ?string $new_value = null;      // the field value after the user change
    public ?int $new_id = null;            // the reference id after the user change e.g. for fields using a sub table such as status
    public ?string $std_value = null;  // the standard field value for all users that does not have changed it
    public ?int $std_id = null;        // the standard reference id for all users that does not have changed it

    /*
     * construct and map
     */

    /**
     * @return bool true if a row is found
     */
    function row_mapper(array $db_row): bool
    {
        global $change_log_fields;
        if ($db_row[self::FLD_ID] > 0) {
            $this->set_id($db_row[self::FLD_ID]);
            $this->field_id = $db_row[self::FLD_FIELD_ID];
            $this->row_id = $db_row[self::FLD_ROW_ID];
            $this->change_time = $db_row[self::FLD_CHANGE_TIME];
            $this->old_value = $db_row[self::FLD_OLD_VALUE];
            $this->old_id = $db_row[self::FLD_OLD_ID];
            $this->new_value = $db_row[self::FLD_NEW_VALUE];
            $this->new_id = $db_row[self::FLD_NEW_ID];

            $fld_tbl = $change_log_fields->get_by_id($this->field_id);
            $this->table_id = preg_replace("/[^0-9]/", '', $fld_tbl->name);
            $usr = new user();
            $usr->id = $db_row[user::FLD_ID];
            $this->usr = $usr;
            $this->user_name = $db_row[user::FLD_NAME];
            return true;
        } else {
            return false;
        }
    }


    /*
     * cast
     */

    public function api_obj(): change_log_named_api
    {
        $api_obj = new change_log_named_api();
        parent::fill_api_obj($api_obj);
        $api_obj->old_value = $this->old_value;
        $api_obj->old_id = $this->old_id;
        $api_obj->new_value = $this->new_value;
        $api_obj->new_id = $this->new_id;
        $api_obj->std_value = $this->std_value;
        $api_obj->std_id = $this->std_id;
        return $api_obj;

    }

    public function dsp_obj(): change_log_named_dsp
    {
        $api_obj = new change_log_named_dsp();
        parent::fill_api_obj($api_obj);
        $api_obj->old_value = $this->old_value;
        $api_obj->old_id = $this->old_id;
        $api_obj->new_value = $this->new_value;
        $api_obj->new_id = $this->new_id;
        $api_obj->std_value = $this->std_value;
        $api_obj->std_id = $this->std_id;
        return $api_obj;

    }


    /*
     * loading
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of the change log
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con, string $name): sql_par
    {
        $qp = new sql_par(self::class);
        $db_con->set_type(sql_db::TBL_CHANGE);
        $qp->name .= $name;
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $db_con->set_join_fields(array(user::FLD_NAME), sql_db::TBL_USER);
        $db_con->set_join_fields(array(change_log_field::FLD_TABLE), sql_db::TBL_CHANGE_FIELD);
        $db_con->set_order(self::FLD_CHANGE_TIME, sql_db::ORDER_DESC);

        return $qp;
    }

    /**
     * create the SQL statement to retrieve the parameters of the change log by field and row id
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @param int $field_id the database id of the database field (and table) of the changes that the user wants to see
     * @param int $row_id the database id of the database row of the changes that the user wants to see
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    public function load_sql_by_field_row(sql_db $db_con, int $field_id, int $row_id): sql_par
    {
        $qp = $this->load_sql($db_con, 'field_row');
        $db_con->set_page();
        $db_con->add_par(sql_db::PAR_INT, $field_id);
        $db_con->add_par(sql_db::PAR_INT, $row_id);
        $qp->sql = $db_con->select_by_field_list(
            array(
                change_log_named::FLD_FIELD_ID,
                change_log_named::FLD_ROW_ID,
                user_sandbox::FLD_USER
            ));
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * create the SQL statement to retrieve the parameters of the change log by name
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    public function load_by_user_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'user';
        $db_con->set_type(sql_db::TBL_CHANGE);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->usr->id);
        $db_con->set_fields(self::FLD_NAMES);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    function load_sql_old(string $type): sql_par
    {
        global $db_con;
        $result = ''; // reset the html code var

        $qp = new sql_par(self::class);

        // set default values
        if (!isset($this->size)) {
            $this->size = SQL_ROW_LIMIT;
        } else {
            if ($this->size <= 0) {
                $this->size = SQL_ROW_LIMIT;
            }
        }

        // select the change table to use
        $sql_where = '';
        $sql_row = '';
        $sql_user = '';
        // the setting for most cases
        $sql_row = ' s.row_id  = $2 ';
        // the class specific settings
        if ($type == user::class) {
            $sql_where = " (f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::WORD) . " 
                   OR f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::WORD_USR) . ") AND ";
            $sql_row = '';
            $sql_user = 's.user_id = u.user_id
                AND s.user_id = ' . $this->usr->id . ' ';
        } elseif ($type == word::class) {
            //$db_con->add_par(sql_db::PAR_INT, cl(db_cl::LOG_TABLE, change_log_table::WORD));
            //$db_con->add_par(sql_db::PAR_INT, cl(db_cl::LOG_TABLE, change_log_table::WORD_USR));
            $sql_where = " s.change_field_id = $1 ";
        } elseif ($type == value::class) {
            $sql_where = " (f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VALUE) . " 
                     OR f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VALUE_USR) . ") AND ";
        } elseif ($type == formula::class) {
            $sql_where = " (f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::FORMULA) . " 
                     OR f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::FORMULA_USR) . ") AND ";
        } elseif ($type == view::class) {
            $sql_where = " (f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VIEW) . " 
                     OR f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VIEW_USR) . ") AND ";
        } elseif ($type == view_cmp::class) {
            $sql_where = " (f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VIEW_COMPONENT) . " 
                     OR f.table_id = " . cl(db_cl::LOG_TABLE, change_log_table::VIEW_COMPONENT_USR) . ") AND ";
        }

        if ($sql_where == '') {
            log_err("Internal error: object not defined for showing the changes.", "user_log_display->dsp_hist");
        } else {
            // get word changes by the user that are not standard
            $qp->sql = "SELECT s.change_id, 
                     s.user_id, 
                     s.change_time, 
                     s.change_action_id, 
                     s.change_field_id, 
                     s.row_id, 
                     s.old_value, 
                     s.old_id, 
                     s.new_value,
                     s.new_id, 
                     l.user_name,
                     l2.table_id
                FROM changes s 
           LEFT JOIN users l ON s.user_id = l.user_id
           LEFT JOIN change_fields l2 ON s.change_field_id = l2.change_field_id
               WHERE " . $sql_where . " AND " . $sql_row . " 
            ORDER BY s.change_time DESC
               LIMIT " . $this->size . ";";
            log_debug('user_log_display->dsp_hist ' . $qp->sql);
            $db_con->usr_id = $this->usr->id;
        }
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

        //parent::add_table();
        //parent::add_field();

        $db_type = $db_con->get_type();
        $qp = $this->load_sql_by_field_row($db_con, $this->field_id, $this->row_id);
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
     * @return true if the change has been logged successfully
     */
    function add(): bool
    {
        log_debug(' do "' . $this->action
            . '" in "' . $this->table()
            . ',' . $this->field()
            . '" log change from "'
            . $this->old_value . '" (id ' . $this->old_id . ')' .
            ' to "' . $this->new_value . '" (id ' . $this->new_id . ') in row ' . $this->row_id);

        global $db_con;

        //parent::add_table();
        //parent::add_field();
        parent::add_action();

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
                log_fatal("Insert to change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . ")", "user_log->add");
                log_fatal("Insert to change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add");
            }
            $result = False;
        } else {
            $this->set_id($log_id);
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
        log_debug("user_log->add_ref (" . $row_id . " to " . $this->id() . " for user " . $this->usr->dsp_id() . ")");

        global $db_con;
        $result = false;

        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_CHANGE);
        $db_con->set_usr($this->usr->id);
        if ($db_con->update($this->id(), "row_id", $row_id)) {
            // restore the type before saving the log
            $db_con->set_type($db_type);
            $result = True;
        } else {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->usr == null) {
                log_fatal("Update of reference in the change log failed.", "user_log->add_ref", 'Update of reference in the change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Update of reference in the change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . ")", "user_log->add_ref");
                log_fatal("Update of reference in the change log failed with (" . $this->usr->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add_ref");
            }
        }
        return $result;
    }


}