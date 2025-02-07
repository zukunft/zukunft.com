<?php

/*

    model/log/user_log.php - object to save the user changes in the database in a format, so that it can fast be displayed to the user
    ----------------------

    for reading the user changes from the database and forwarding them to
    the API and frontend model/log/changeLog* should be used

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this word object
    - construct and map: including the mapping of the db row to this word object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - display:           internal support functions for debugging


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
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
        auto archive the log if the number of changes gets too big
        only log the changes of this pod and used the pod distribution table to get changes from other pods

TODO    if change table gets too big, rename the table to "_up_to_YYYY_MM_DD_HH:MM:SS.000"
        after that create a new table and start numbering from zero

TODO    rename to change_base

*/

namespace cfg\log;

include_once MODEL_HELPER_PATH . 'db_object_seq_id_user.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
//include_once MODEL_COMPONENT_PATH . 'component.php';
//include_once MODEL_COMPONENT_PATH . 'component_link.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_FORMULA_PATH . 'formula_link.php';
//include_once MODEL_SANDBOX_PATH . 'sandbox_link.php';
//include_once MODEL_REF_PATH . 'ref.php';
//include_once MODEL_REF_PATH . 'source.php';
//include_once MODEL_VERB_PATH . 'verb.php';
//include_once MODEL_USER_PATH . 'user.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_VIEW_PATH . 'view_term_link.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once MODEL_WORD_PATH . 'word_db.php';
//include_once MODEL_WORD_PATH . 'triple.php';
include_once SHARED_ENUM_PATH . 'change_actions.php';
include_once SHARED_ENUM_PATH . 'change_tables.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';
include_once SHARED_PATH . 'library.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_seq_id_user;
use cfg\helper\type_object;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\sandbox\sandbox_link;
use cfg\ref\ref;
use cfg\ref\source;
use cfg\value\value;
use cfg\verb\verb;
use cfg\word\triple;
use cfg\user\user;
use cfg\value\value_base;
use cfg\view\view;
use cfg\view\view_term_link;
use cfg\word\word;
use cfg\word\word_db;
use shared\enum\change_actions;
use shared\enum\change_tables;
use shared\json_fields;
use shared\library;
use DateTime;
use DateTimeInterface;
use Exception;
use shared\types\api_type_list;

class change_log extends db_object_seq_id_user
{

    /*
     * database link
     */

    // change log database and JSON object field names
    const FLD_ID_COM = 'the prime key to identify the change';
    const FLD_ID = 'change_id';
    const FLD_TIME_COM = 'time when the user has confirmed the change';
    const FLD_TIME = 'change_time';
    const FLD_USER_COM = 'reference to the user who has done the change';
    const FLD_ACTION_COM = 'the curl action';
    const FLD_ACTION = 'change_action_id';
    const FLD_ROW_ID_COM = 'the prime id in the table with the change';
    const FLD_ROW_ID = 'row_id';

    // sql table comments
    const TBL_COMMENT = 'to log all changes done by any user on all tables except value and link changes';

    // field lists for the sql table creation that are used for all change logs (incl. value and link changes)
    const FLD_LST_KEY = array(
        [self::FLD_ID, sql_field_type::KEY_INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_ID_COM],
        [self::FLD_TIME, sql_field_type::TIME, sql_field_default::TIME_NOT_NULL, sql::INDEX, '', self::FLD_TIME_COM],
        [user::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, user::class, self::FLD_USER_COM],
        [self::FLD_ACTION, sql_field_type::INT_SMALL, sql_field_default::NOT_NULL, '', change_action::class, self::FLD_ACTION_COM],
    );
    // field list to log the actual change that is overwritten by the child object e.g. for named, value and link tables
    const FLD_LST_CHANGE = array();
    // field list to identify the database row in the table that has been changed
    const FLD_LST_ROW_ID = array(
        [self::FLD_ROW_ID, sql_field_type::INT, sql_field_default::NULL, '', '', self::FLD_ROW_ID_COM],
    );

    // list of classes that store change log entries except log link because logging a link is too different
    const LOG_CLASSES = [
        change::class,
        changes_norm::class,
        changes_big::class,
        change_values_prime::class,
        change_values_norm::class,
        change_values_big::class,
        change_values_time_prime::class,
        change_values_time_norm::class,
        change_values_time_big::class,
        change_values_text_prime::class,
        change_values_text_norm::class,
        change_values_text_big::class,
        change_values_geo_prime::class,
        change_values_geo_norm::class,
        change_values_geo_big::class,
    ];


    /*
     * object vars
     */

    protected ?int $action_id = null;      // database id for the action text
    public ?int $table_id = null;          // database id for the table text
    protected ?int $field_id = null;       // database id for the field text
    public int|string|null $row_id = null; // the reference id of the row in the database table

    protected DateTime $change_time;       // the date and time of the change


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
        $this->change_time = new DateTime();
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
        global $cng_act_cac;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        $this->action_id = $cng_act_cac->id($action_name);
        if ($this->action_id <= 0) {
            $this->add_action($used_db_con, $action_name);
            if ($this->action_id <= 0) {
                log_err("Cannot add action name " . $action_name);
            } else {
                $act = new type_object($action_name, $action_name, '', $this->action_id);
                $cng_act_cac->add($act);
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
        global $cng_act_cac;
        return $cng_act_cac->name($this->action_id);
    }

    /**
     * set the table of this change log object by the class name
     * @param string $class the class name
     * @return bool true if the table/class is part of the log table
     */
    function set_class(string $class): bool
    {
        $lib = new library();
        $name = $lib->class_to_table($class);
        return $this->set_table($name);
    }

    /**
     * set the table of this change log object and to add a new table to the database if needed
     * @param string $table_name the name of the new table
     * @param sql_db|null $given_db_con the name of the new field
     * @return bool true if a new table has been added to the database
     */
    function set_table(string $table_name, ?sql_db $given_db_con = null): bool
    {
        global $cng_tbl_cac;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        $this->table_id = $cng_tbl_cac->id($table_name);
        if ($this->table_id <= 0) {
            $this->add_table($used_db_con, $table_name);
            if ($this->table_id <= 0) {
                log_err("Cannot add table name " . $table_name);
            } else {
                $tbl = new type_object($table_name, $table_name, '', $this->table_id);
                $cng_tbl_cac->add($tbl);
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
        global $cng_tbl_cac;
        return $cng_tbl_cac->name($this->table_id);
    }

    /**
     * set the field of this change log object and to add a new field to the database if needed
     * @param string $field_name the name of the new field
     * @param sql_db|null $given_db_con the name of the new field
     * @return bool true if a new table has been added to the database
     */
    function set_field(string $field_name, ?sql_db $given_db_con = null): bool
    {
        global $cng_fld_cac;
        global $db_con;

        $used_db_con = $db_con;
        if ($given_db_con != null) {
            $used_db_con = $given_db_con;
        }

        $db_changed = false;
        if ($this->table_id > 0) {
            $this->field_id = $cng_fld_cac->id($this->table_id . $field_name);
            if ($this->field_id <= 0) {
                if ($used_db_con->connected()) {
                    $this->add_field($used_db_con, $field_name);
                    if ($this->field_id <= 0) {
                        log_err("Cannot add field name " . $field_name);
                    } else {
                        $tbl = new type_object(
                            $this->table_id . $field_name,
                            $this->table_id . $field_name,
                            '',
                            $this->field_id);
                        $cng_fld_cac->add($tbl);
                        $db_changed = true;
                    }
                } else {
                    log_err("Cannot add field name " . $field_name . ' for table id ' . $this->table_id);
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
        global $cng_fld_cac;

        $lib = new library();

        $field_key = $cng_fld_cac->name($this->field_id);
        return $lib->str_right_of_or_all($field_key, $this->table_id);
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
     * sql create
     */

    /**
     * the sql statements to create a change log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        return $this->sql_creator($sc, 0);
    }


    /**
     * the sql statements to create all indices for a change log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices of the change log tables
     */
    function sql_index(sql_creator $sc): string
    {
        return $this->sql_creator($sc, 1);
    }

    /**
     * the sql statements to create all foreign keys for a change log table
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the foreign keys of a change log table
     */
    function sql_foreign_key(sql_creator $sc): string
    {
        return $this->sql_creator($sc, 2);
    }

    /**
     * the sql statements to create either
     * all tables ($pos = 0),
     * the indices ($pos = 1)
     * or the foreign keys ($pos = 2)
     * used to store values in the database
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    protected function sql_creator(sql_creator $sc, int $pos): string
    {

        $sql_array = $this->sql_one_type(
            $sc,
            $this::FLD_LST_ROW_ID,
            '', ''
        );
        return $sql_array[$pos];
    }

    /**
     * create the sql statements for one or a set of change tables
     * e.g. for bigint or text row id
     *
     * @param sql_creator $sc
     * @param array $fld_row_id the parameters for the value field e.g. for a numeric field, text, time or geo
     * @param string $ext_type the additional table extension for the field type
     * @param string $type_name the name of the value type
     * @return array the sql statements to create the tables, indices and foreign keys
     */
    protected function sql_one_type(
        sql_creator $sc,
        array       $fld_row_id,
        string      $ext_type = '',
        string      $type_name = ''): array
    {
        $lib = new library();
        $type_name .= ' ' . $lib->class_to_name($this::class);

        $sql = $sc->sql_separator();
        $sql_index = $sc->sql_separator();
        $sql_foreign = $sc->sql_separator();

        $sc->set_class($this::class, new sql_type_list(), $ext_type);
        $fields = array_merge($this::FLD_LST_KEY, $fld_row_id, $this::FLD_LST_CHANGE);
        $sql .= $sc->table_create($fields, $type_name, $this::TBL_COMMENT, $this::class);
        $sql_index .= $sc->index_create($fields);
        $sql_foreign .= $sc->foreign_key_create($fields);

        return [$sql, $sql_index, $sql_foreign];
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
        foreach (change_action::ACTION_LIST as $action_name) {
            $db_changed = $this->set_action($action_name, $db_con);
        }
        foreach (change_table_list::TABLE_LIST as $table_name) {
            $db_changed = $this->set_table($table_name, $db_con);
            if ($table_name == change_tables::USER) {
                $db_con->set_class(user::class);
                foreach (user::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::WORD) {
                $db_con->set_class(word::class);
                foreach (word_db::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::WORD_USR) {
                $db_con->set_class(word::class, true);
                foreach (word_db::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VERB) {
                $db_con->set_class(verb::class);
                foreach (verb::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::TRIPLE) {
                $db_con->set_class(triple::class);
                foreach (triple::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::TRIPLE_USR) {
                $db_con->set_class(triple::class, true);
                foreach (triple::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VALUE) {
                $db_con->set_class(value::class);
                foreach (value_base::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VALUE_USR) {
                $db_con->set_class(value::class, true);
                foreach (value_base::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::FORMULA) {
                $db_con->set_class(formula::class);
                foreach (formula::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::FORMULA_USR) {
                $db_con->set_class(formula::class, true);
                foreach (formula::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::FORMULA_LINK) {
                $db_con->set_class(formula_link::class);
                foreach (formula_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::FORMULA_LINK_USR) {
                $db_con->set_class(formula_link::class, true);
                foreach (formula_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW) {
                $db_con->set_class(view::class);
                foreach (view::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_USR) {
                $db_con->set_class(view::class, true);
                foreach (view::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_TERM_LINK) {
                $db_con->set_class(view_term_link::class);
                foreach (view_term_link::FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_COMPONENT) {
                $db_con->set_class(component::class);
                foreach (component::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_COMPONENT_USR) {
                $db_con->set_class(component::class, true);
                foreach (component::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_LINK) {
                $db_con->set_class(component_link::class);
                foreach (component_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::VIEW_LINK_USR) {
                $db_con->set_class(component_link::class, true);
                foreach (component_link::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::REF) {
                $db_con->set_class(ref::class);
                foreach (ref::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::REF_USR) {
                $db_con->set_class(ref::class, true);
                foreach (ref::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::SOURCE) {
                $db_con->set_class(source::class);
                foreach (source::ALL_SANDBOX_FLD_NAMES as $field_name) {
                    $db_changed = $this->set_field($field_name, $db_con);
                }
            } elseif ($table_name == change_tables::SOURCE_USR) {
                $db_con->set_class(source::class, true);
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
        if ($this->user() != null) {
            $vars[json_fields::USR] = $this->user()->api_json_array_core($typ_lst, $usr);
        }
        $vars[json_fields::ACTION_ID] = $this->action_id;
        $vars[json_fields::TABLE_ID] = $this->table_id;
        $vars[json_fields::FIELD_ID] = $this->field_id;
        $vars[json_fields::ROW_ID] = $this->row_id;
        $vars[json_fields::CHANGE_TIME] = $this->time();

        return $vars;
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
        $db_con->set_class(change_table::class);
        $table_id = $db_con->get_id($table_name);

        // add new table name if needed
        if ($table_id <= 0) {
            $table_id = $db_con->add_id($table_name);
            // save also the code_id
            if ($table_id > 0) {
                $db_con->set_class(change_table::class);
                $db_con->update_old($table_id, array('code_id'), array($table_name));
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
        $db_con->set_class(change_field::class);
        $field_id = $db_con->get_id_2key($field_name, "table_id", $this->table_id);

        // add new field name if needed
        if ($field_id <= 0) {
            // TODO use a "normal" insert statement
            // TODO do not log NOW() field
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

    protected function add_action(sql_db $db_con, string $action_name): void
    {
        // if e.g. the action is "add" the reference 1 is saved in the log table to save space
        $db_type = $db_con->get_class();
        $db_con->set_class(change_action::class);
        $action_id = $db_con->get_id($action_name);

        // add new action name if needed
        if ($action_id <= 0) {
            $action_id = $db_con->add_id($action_name);
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

    // add the row id to an existing log entry
    // e.g. because the row id is known after the adding of the real record,
    // but the log entry has been created upfront to make sure that logging is complete
    function add_ref($row_id): bool
    {
        return true;
    }


    /*
     * sql write
     */

    /**
     * create the sql statement to add a log entry to the database
     *
     * @param sql_creator $sc with the target db_type set
     * @param sql_type_list $sc_par_lst the parameters for the sql statement creation
     * @param string $ext the name extension that should be used
     * @param string $val_tbl name of the table to select the values to insert
     * @param string $add_fld name of the database key field
     * @param string $row_fld name of the database id field
     * @param string $par_name name of the database name parameter field
     * @return sql_par the SQL insert statement, the name of the SQL statement and the parameter list
     */
    function sql_insert(
        sql_creator   $sc,
        sql_type_list $sc_par_lst = new sql_type_list(),
        string        $ext = '',
        string        $val_tbl = '',
        string        $add_fld = '',
        string        $row_fld = '',
        string        $par_name = ''
    ): sql_par
    {
        if ($this::class == change_link::class) {
            return $this->sql_insert_link($sc, $sc_par_lst);
        } else {
            // clone the sql parameter list to avoid changing the given list
            $sc_par_lst_used = clone $sc_par_lst;
            // set the sql query type
            $sc_par_lst_used->add($this->sql_type());
            $sc_par_lst_used->add($this->sql_sub_type());
            // do not use the user extension for the change table name
            $sc_par_lst_chg = $sc_par_lst_used->remove(sql_type::USER);
            $qp = $sc->sql_par($this::class, $sc_par_lst_chg);
            $sc->set_class($this::class, $sc_par_lst_chg);
            if ($sc_par_lst_used->is_list_tbl()) {
                $lib = new library();
                $qp->name = $lib->class_to_name($this::class) . $ext;
            }
            $sc->set_name($qp->name);
            $qp->sql = $sc->create_sql_insert(
                $this->db_field_values_types($sc, $sc_par_lst_used), $sc_par_lst_used, true, $val_tbl, $add_fld, $row_fld, '', $par_name);
            $qp->par = $this->db_values();

            return $qp;
        }
    }

    /**
     * @return sql_type the sql type of the change e.g. if a value is changes it returns sql_type::UPDATE
     *                  is in most cases overwritten by the child object
     */
    function sql_type(): sql_type
    {
        return sql_type::INSERT;
    }

    /**
     * @return sql_type an addition sql type for the change e.g. if the phrase type is changed REF is added
     *                  is in most cases overwritten by the child object
     */
    function sql_sub_type(): sql_type
    {
        return sql_type::NULL;
    }

    /**
     * dummy function overwritten by the child object
     * @param sql_creator $sc
     * @param sql_type_list $sc_par_lst
     * @param sandbox_link|null $sbx
     * @return sql_par
     */
    function sql_insert_link(
        sql_creator   $sc,
        sql_type_list $sc_par_lst,
        ?sandbox_link $sbx = null
    ): sql_par
    {
        return new sql_par($this::class);
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields
     * list must be corresponding to the db_values fields
     *
     * @param sql_creator $sc the sql creation script with preset parameters
     * @param sql_type_list $sc_par_lst the internal parameters to create the sql
     * @param sql_par_type $val_typ the type of the value field
     * @return sql_par_field_list list of the database field names
     */
    function db_field_values_types(
        sql_creator $sc,
        sql_type_list $sc_par_lst,
        sql_par_type $val_typ = sql_par_type::FLOAT
    ): sql_par_field_list
    {
        $fvt_lst = new sql_par_field_list();
        $fvt_lst->add_field(user::FLD_ID, $this->user()->id(), user::FLD_ID_SQL_TYP);
        $fvt_lst->add_field(change_action::FLD_ID, $this->action_id, type_object::FLD_ID_SQL_TYP);
        if ($this->field_id != null) {
            $fvt_lst->add_field(change_field::FLD_ID, $this->field_id, type_object::FLD_ID_SQL_TYP);
        }

        return $fvt_lst;
    }

    /**
     * get a list of all database fields
     * list must be corresponding to the db_values fields
     * TODO deprecate
     *
     * @return array list of the database field names
     */
    function db_fields(): array
    {
        $sql_fields = array();
        $sql_fields[] = user::FLD_ID;
        $sql_fields[] = change_action::FLD_ID;
        $sql_fields[] = change_field::FLD_ID;

        return $sql_fields;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @return array list of the database field values
     */
    function db_values(): array
    {
        $sql_values = array();
        $sql_values[] = $this->user()->id();
        $sql_values[] = $this->action_id;
        $sql_values[] = $this->field_id;

        return $sql_values;
    }


    /*
     * save
     */

    /**
     * log a user change of a word, value or formula
     * @return true if the change has been logged successfully
     */
    function add(): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        $db_type = $db_con->get_class();
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc);
        $usr_msg = $db_con->insert($qp, 'log change');
        if ($usr_msg->is_ok()) {
            $log_id = $usr_msg->get_row_id();
        }

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->user() == null) {
                log_fatal("Insert to change log failed.", "user_log->add", 'Insert to change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Insert to change log failed with (" . $this->user()->dsp_id() . "," . $this->action() . "," . $this->table() . "," . $this->field() . ")", "user_log->add");
                log_fatal("Insert to change log failed with (" . $this->user()->dsp_id() . "," . $this->action() . "," . $this->table() . "," . $this->field() . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add");
            }
            $result = False;
        } else {
            $this->set_id($log_id);
            // restore the type before saving the log
            $db_con->set_class($db_type);
            $result = True;
        }

        return $result;
    }


    /*
     * debug
     */

    /**
     * @return string with the unique database id mainly for child dsp_id() functions
     */
    function dsp_id(): string
    {

        $result = 'log ' . $this->action() . ' ';
        $result .= $this->table() . ',' . $this->field();
        $result .= ' db row ' . $this->row_id;
        $result .= ' at ' . $this->change_time->format(DateTimeInterface::ATOM);
        return $result;
    }

}