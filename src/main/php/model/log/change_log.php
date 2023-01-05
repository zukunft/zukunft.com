<?php

/*

    user_log.php - object to save the user changes in the database in a format, so that it can fast be displayed to the user
    ------------

    for reading the user changes from the database and forwarding them to
    the API and frontend model/log/changeLog* should be used

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


use controller\log\change_log_api;

class change_log extends db_object
{

    /*
     * database link
     */

    // user log database and JSON object field names
    const FLD_ID = 'change_id';
    const FLD_CHANGE_TIME = 'change_time';
    const FLD_ACTION = 'change_action_id';


    /*
     * object vars
     */

    public ?user $usr = null;          // the user who has done the change
    public ?string $action = null;     // text for the user action e.g. "add", "update" or "delete"
    protected ?int $action_id = null;  // database id for the action text
    public ?int $table_id = null;     // database id for the table text
    protected ?int $field_id = null;   // database id for the field text
    public ?int $row_id = null;        // the reference id of the row in the database table

    protected DateTime $change_time; // the date and time of the change


    /*
     * cast
     */

    public function fill_api_obj(change_log_api $api_obj): change_log_api
    {
        if ($this->usr != null) {
            $api_obj->usr = $this->usr->api_obj();
        }
        $api_obj->action_id = $this->action_id;
        $api_obj->table_id = $this->table_id;
        $api_obj->field_id = $this->field_id;
        $api_obj->row_id = $this->row_id;
        $api_obj->change_time = $this->time();
        return $api_obj;

    }

    /*
     * set and get
     */

    public function set_user(user $usr): void
    {
        $this->usr = $usr;
    }

    public function user(): user
    {
        return $this->usr;
    }

    /**
     * set the action of this change log object and to add a new action to the database if needed
     * @param string $action_name the name of the new action
     * @return void
     */
    public function set_action(string $action_name): void
    {
        global $change_log_actions;
        $this->action_id = $change_log_actions->id($action_name);
        if ($this->action_id <= 0) {
            $this->add_action($action_name);
            if ($this->action_id <= 0) {
                log_err("Cannot add action name " . $action_name);
            } else {
                $tbl = new user_type($action_name, $action_name, '', $this->action_id);
                $change_log_actions->add($tbl);
            }
        }
    }

    /**
     * get the action name base on the action_id
     * @return string
     */
    public function action(): string
    {
        global $change_log_actions;
        return $change_log_actions->name($this->action_id);
    }

    /**
     * set the table of this change log object and to add a new table to the database if needed
     * @param string $table_name the name of the new table
     * @return void
     */
    public function set_table(string $table_name): void
    {
        global $change_log_tables;
        $this->table_id = $change_log_tables->id($table_name);
        if ($this->table_id <= 0) {
            $this->add_table($table_name);
            if ($this->table_id <= 0) {
                log_err("Cannot add table name " . $table_name);
            } else {
                $tbl = new user_type($table_name, $table_name, '', $this->table_id);
                $change_log_tables->add($tbl);
            }
        }
    }

    /**
     * get the table name base on the table_id
     * @return string
     */
    public function table(): string
    {
        global $change_log_tables;
        return $change_log_tables->name($this->table_id);
    }

    /**
     * set the field of this change log object and to add a new field to the database if needed
     * @param string $field_name the name of the new field
     * @return void
     */
    public function set_field(string $field_name): void
    {
        global $change_log_fields;
        if ($this->table_id > 0) {
            $this->field_id = $change_log_fields->id($this->table_id . $field_name);
            if ($this->field_id <= 0) {
                $this->add_field($field_name);
                if ($this->field_id <= 0) {
                    log_err("Cannot add field name " . $field_name);
                } else {
                    $tbl = new user_type(
                        $this->table_id . $field_name,
                        $this->table_id . $field_name,
                        '',
                        $this->field_id);
                    $change_log_fields->add($tbl);
                }
            }
        } else {
            log_err('Table not yet set whe trying to set the field ' . $field_name);
        }
    }

    /**
     * get the field name base on the field_id
     * @return string
     */
    public function field(): string
    {
        global $change_log_fields;

        $lib = new library();

        $field_key = $change_log_fields->name($this->field_id);
        return $lib->str_right_of($field_key, $this->table_id);
    }

    public function set_time(DateTime $time): void
    {
        $this->change_time = $time;
    }

    public function set_time_str(string $time_str): void
    {
        global $debug;
        try {
            log_debug('Convert ' . $time_str . ' to time', $debug - 12);
            $this->set_time((new DateTime($time_str)));
            log_debug('Converted ' . $time_str . ' to time', $debug - 12);
        } catch (Exception $e) {
            log_err('Cannot convert ' . $time_str . ' to time');
        }
    }

    public function time(): DateTime
    {
        return $this->change_time;
    }


    /*
     * modify
     */

    /**
     * to save database space the table name is saved as a reference id in the log table
     */
    protected function add_table(string $table_name = ''): int
    {
        if ($this->usr == null) {
            log_warning(' "' . $table_name . '" but user is missing');
        } else {
            log_debug(' "' . $table_name . '" for ' . $this->usr->dsp_id());
        }

        global $db_con;

        // check parameter
        if ($table_name == "") {
            log_err("missing table name", "user_log->set_table");
        }
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log->set_table");
        }

        // if e.g. a "value" is changed $table_name is "values" and the reference 1 is saved in the log to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_CHANGE_TABLE);
        $db_con->set_usr($this->usr->id);
        $table_id = $db_con->get_id($table_name);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($table_name);
            // save also the code_id
            if ($table_id > 0) {
                $db_con->set_type(sql_db::TBL_CHANGE_TABLE);
                $db_con->set_usr($this->usr->id);
                $db_con->update($table_id, array('code_id'), array($table_name));
            }
        }
        if ($table_id > 0) {
            $this->table_id = $table_id;
        } else {
            log_fatal("Insert to change log failed due to table id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
        return $table_id;
    }

    /**
     * save the field name as a reference id in the log table
     */
    protected function add_field(string $field_name = ''): int
    {
        global $usr;
        if ($this->usr == null) {
            log_warning('user_log->set_field "' . $field_name . '" but user is missing');
        } else {
            log_debug('user_log->set_field "' . $field_name . '" for table "' . $this->table() . '" (' . $this->table_id . ') and user ' . $this->usr->dsp_id());
        }

        global $db_con;

        // check parameter
        if ($this->table_id <= 0) {
            log_err("missing table_id", "user_log->set_field");
        }
        if ($field_name == "") {
            log_err("missing field name", "user_log->set_field");
        }
        /*
        if ($this->usr->id <= 0) {
            log_err("missing user", "user_log->set_field");
        }
        */

        //$db_con = new mysql;
        $db_type = $db_con->get_type();
        $db_con->set_type(sql_db::TBL_CHANGE_FIELD);
        $db_con->usr_id = $usr->id;
        $field_id = $db_con->get_id_2key($field_name, "table_id", $this->table_id);

        // add new field name if needed
        if ($field_id <= 0) {
            $field_id = $db_con->add_id_2key($field_name, "table_id", $this->table_id);
        }
        if ($field_id > 0) {
            $this->field_id = $field_id;
        } else {
            log_fatal("Insert to change log failed due to field id failure.", "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_type($db_type);
        return $field_id;
    }

    protected function add_action(): void
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
        $db_con->set_type(sql_db::TBL_CHANGE_ACTION);
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


    /**
     * display the last change related to one object (word, formula, value, verb, ...)
     * mainly used for testing
     * TODO if changes on table values are requested include also the table "user_values"
     */
    function dsp_last(bool $ex_time): string
    {
        return 'Error: either the named or link user log function should be used';
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