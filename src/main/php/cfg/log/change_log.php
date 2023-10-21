<?php

/*

    model/log/user_log.php - object to save the user changes in the database in a format, so that it can fast be displayed to the user
    ----------------------

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

namespace cfg;

include_once MODEL_HELPER_PATH . 'db_object_user.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once MODEL_VALUE_PATH . 'value_phrase_link.php';
include_once API_LOG_PATH . 'change_log.php';

use api\change_log_api;
use cfg\component\component;
use DateTime;
use DateTimeInterface;
use Exception;

class change_log extends db_object_user
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

    public ?string $action = null;     // text for the user action e.g. "add", "update" or "delete"
    protected ?int $action_id = null;  // database id for the action text
    public ?int $table_id = null;     // database id for the table text
    protected ?int $field_id = null;   // database id for the field text
    public ?int $row_id = null;        // the reference id of the row in the database table

    protected DateTime $change_time; // the date and time of the change


    /*
     * construct and map
     */

    /**
     * always set the user because a change log list is always user specific
     * @param user|null $usr the user who requested to see the log entries
     */
    function __construct(?user $usr)
    {
        parent::__construct($usr);
    }



    /*
     * cast
     */

    function fill_api_obj(change_log_api $api_obj): change_log_api
    {
        if ($this->user() != null) {
            $api_obj->usr = $this->user()->api_obj();
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

    /**
     * set the action of this change log object and to add a new action to the database if needed
     * @param string $action_name the name of the new action
     * @param sql_db|null $given_db_con the name of the new field
     * @return bool true if a new action has been added to the database
     */
    function set_action(string $action_name, ?sql_db $given_db_con = null): bool
    {
        global $change_log_actions;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        $this->action_id = $change_log_actions->id($action_name);
        if ($this->action_id <= 0) {
            $this->add_action($used_db_con);
            if ($this->action_id <= 0) {
                log_err("Cannot add action name " . $action_name);
            } else {
                $act = new type_object($action_name, $action_name, '', $this->action_id);
                $change_log_actions->add($act);
                $db_changed = true;
            }
        }
        return $db_changed;
    }

    /**
     * get the action name base on the action_id
     * @return string
     */
    function action(): string
    {
        global $change_log_actions;
        return $change_log_actions->name($this->action_id);
    }

    /**
     * set the table of this change log object and to add a new table to the database if needed
     * @param string $table_name the name of the new table
     * @param sql_db|null $given_db_con the name of the new field
     * @return bool true if a new table has been added to the database
     */
    function set_table(string $table_name, ?sql_db $given_db_con = null): bool
    {
        global $change_log_tables;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        $this->table_id = $change_log_tables->id($table_name);
        if ($this->table_id <= 0) {
            $this->add_table($used_db_con, $table_name);
            if ($this->table_id <= 0) {
                log_err("Cannot add table name " . $table_name);
            } else {
                $tbl = new type_object($table_name, $table_name, '', $this->table_id);
                $change_log_tables->add($tbl);
                $db_changed = true;
            }
        }
        return $db_changed;
    }

    /**
     * get the table name base on the table_id
     * @return string
     */
    function table(): string
    {
        global $change_log_tables;
        return $change_log_tables->name($this->table_id);
    }

    /**
     * set the field of this change log object and to add a new field to the database if needed
     * @param string $field_name the name of the new field
     * @param sql_db|null $given_db_con the name of the new field
     * @return bool true if a new table has been added to the database
     */
    function set_field(string $field_name, ?sql_db $given_db_con = null): bool
    {
        global $change_log_fields;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        if ($this->table_id > 0) {
            $this->field_id = $change_log_fields->id($this->table_id . $field_name);
            if ($this->field_id <= 0) {
                $this->add_field($used_db_con, $field_name);
                if ($this->field_id <= 0) {
                    log_err("Cannot add field name " . $field_name);
                } else {
                    $tbl = new type_object(
                        $this->table_id . $field_name,
                        $this->table_id . $field_name,
                        '',
                        $this->field_id);
                    $change_log_fields->add($tbl);
                    $db_changed = true;
                }
            }
        } else {
            log_err('Table not yet set whe trying to set the field ' . $field_name);
        }
        return $db_changed;
    }

    /**
     * get the field name base on the field_id
     * @return string
     */
    function field(): string
    {
        global $change_log_fields;

        $lib = new library();

        $field_key = $change_log_fields->name($this->field_id);
        return $lib->str_right_of($field_key, $this->table_id);
    }

    function set_time(DateTime $time): void
    {
        $this->change_time = $time;
    }

    function set_time_str(string $time_str): void
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

    function time(): DateTime
    {
        return $this->change_time;
    }


    /*
     * init
     */

    /**
     * create the change log references (tables, fields and actions)
     * needed for this program version
     * @return bool true if a new database entry has been added
     */
    function create_log_references(sql_db $db_con): bool
    {
        $db_changed = false;
        foreach (change_log_action::ACTION_LIST as $action_name) {
            $db_changed = $this->set_action($action_name, $db_con);
        }
        foreach (change_log_table::TABLE_LIST as $table_name) {
            $db_changed = $this->set_table($table_name, $db_con);
            if ($table_name == change_log_table::USER) {
                $db_con->set_class(sql_DB::TBL_USER);
                foreach (user::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::WORD) {
                $db_con->set_class(word::class);
                foreach (word::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::WORD_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_WORD);
                foreach (word::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VERB) {
                $db_con->set_class(sql_DB::TBL_VERB);
                foreach (verb::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::TRIPLE) {
                $db_con->set_class(sql_db::TBL_TRIPLE);
                foreach (triple::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::TRIPLE_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_db::TBL_TRIPLE);
                foreach (triple::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VALUE) {
                $db_con->set_class(value::class);
                foreach (value::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VALUE_USR) {
                $db_con->set_class(value::class, true);
                foreach (value::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VALUE_LINK) {
                $db_con->set_class(sql_DB::TBL_VALUE_PHRASE_LINK);
                foreach (value_phrase_link::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::FORMULA) {
                $db_con->set_class(sql_DB::TBL_FORMULA);
                foreach (formula::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::FORMULA_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_FORMULA_LINK);
                foreach (formula::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::FORMULA_LINK) {
                $db_con->set_class(sql_DB::TBL_FORMULA);
                foreach (formula_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::FORMULA_LINK_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_FORMULA_LINK);
                foreach (formula_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW) {
                $db_con->set_class(sql_DB::TBL_VIEW);
                foreach (view::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_VIEW);
                foreach (view::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_TERM_LINK) {
                $db_con->set_class(sql_DB::TBL_VIEW_TERM_LINK);
                foreach (view_term_link::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_COMPONENT) {
                $db_con->set_class(sql_DB::TBL_COMPONENT);
                foreach (component::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_COMPONENT_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_COMPONENT);
                foreach (component::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_LINK) {
                $db_con->set_class(sql_DB::TBL_COMPONENT_LINK);
                foreach (component_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::VIEW_LINK_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_COMPONENT_LINK);
                foreach (component_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::REF) {
                $db_con->set_class(sql_DB::TBL_REF);
                foreach (ref::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::REF_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_REF);
                foreach (ref::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::SOURCE) {
                $db_con->set_class(source::class);
                foreach (source::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_log_table::SOURCE_USR) {
                $db_con->set_class(sql_db::TBL_USER_PREFIX . sql_DB::TBL_SOURCE);
                foreach (source::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } else {
                $sys_usr = new user();
                $sys_usr->set_id(user::SYSTEM_ID);
                $sys_usr->set_name(user::SYSTEM_NAME);
                log_warning('Log field settings for table ' . $table_name . ' are missing',
                    '', '', '', $sys_usr, $db_con);
            }
        }
        return $db_changed;
    }



    /*
     * modify
     */

    /**
     * to save database space the table name is saved as a reference id in the log table
     */
    protected function add_table(sql_db $db_con, string $table_name = ''): int
    {
        // check parameter
        if ($table_name == "") {
            log_err("missing table name", "user_log->set_table");
        }

        // if e.g. a "value" is changed $table_name is "values" and the reference 1 is saved in the log to save space
        //$db_con = new mysql;
        $db_type = $db_con->get_class();
        $db_con->set_class(sql_db::TBL_CHANGE_TABLE);
        $table_id = $db_con->get_id($table_name);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($table_name);
            // save also the code_id
            if ($table_id > 0) {
                $db_con->set_class(sql_db::TBL_CHANGE_TABLE);
                $db_con->update($table_id, array('code_id'), array($table_name));
            }
        }
        if ($table_id > 0) {
            $this->table_id = $table_id;
        } else {
            log_fatal_db(
                "Insert to change log failed due to table id failure.",
                "user_log->add");
        }
        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $table_id;
    }

    /**
     * save the field name as a reference id in the log table
     */
    protected function add_field(sql_db $db_con, string $field_name = ''): int
    {
        // check parameter
        if ($this->table_id <= 0) {
            log_err("missing table_id", "user_log->set_field");
        }
        if ($field_name == "") {
            log_err("missing field name", "user_log->set_field");
        }

        $db_type = $db_con->get_class();
        $db_con->set_class(sql_db::TBL_CHANGE_FIELD);
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
        $db_con->set_class($db_type);
        return $field_id;
    }

    protected function add_action(sql_db $db_con): void
    {
        // if e.g. the action is "add" the reference 1 is saved in the log table to save space
        $db_type = $db_con->get_class();
        $db_con->set_class(sql_db::TBL_CHANGE_ACTION);
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
        $db_con->set_class($db_type);
    }


    /**
     * display the last change related to one object (word, formula, value, verb, ...)
     * mainly used for testing
     * TODO if changes on table values are requested include also the table "user_values"
     */
    function dsp_last(bool $ex_time = false): string
    {
        $msg = 'Error: either the named or link user log function should be used';
        log_err($msg);
        return $msg;
    }

    /**
     * TODO review
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


    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        return 'change log id ' . $this->id()
            . ' at ' . $this->change_time->format(DateTimeInterface::ATOM)
            . ' ' . $this->action()
            . ' ' . $this->table()
            . ' ' . $this->field()
            . ' row ' . $this->row_id;
    }

}