<?php

/*

    model/log/change.php - for logging changes in named objects such as words and formulas
    ------------------

    The main sections of this object are
    - db const:          const for the database link
    - object vars:       the variables of this change log object
    - construct and map: including the mapping of the db row to this change log object
    - cast:              create an api object and set the vars from an api json
    - load:              database access object (DAO) functions
    - save:              manage to update the database
    - sql write:         sql statement creation to write to the database
    - sql write fields:  field list for writing to the database
    - display:           TODO to be move to frontend


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

namespace cfg\log;

include_once MODEL_LOG_PATH . 'change_log.php';
//include_once MODEL_COMPONENT_PATH . 'component.php';
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
include_once MODEL_LOG_PATH . 'change_log.php';
//include_once MODEL_FORMULA_PATH . 'formula.php';
//include_once MODEL_GROUP_PATH . 'group.php';
//include_once MODEL_USER_PATH . 'user.php';
//include_once MODEL_VALUE_PATH . 'value.php';
//include_once MODEL_VALUE_PATH . 'value_base.php';
//include_once MODEL_VIEW_PATH . 'view.php';
//include_once MODEL_WORD_PATH . 'word.php';
include_once WEB_LOG_PATH . 'change_log_named.php';
//include_once WEB_HELPER_PATH . 'config.php';
include_once SHARED_TYPES_PATH . 'api_type_list.php';
include_once SHARED_PATH . 'json_fields.php';

use cfg\component\component;
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
use cfg\formula\formula;
use cfg\group\group;
use cfg\user\user;
use cfg\value\value;
use cfg\view\view;
use cfg\word\word;
use html\helper\config;
use html\log\change_log_named as change_log_named_dsp;
use DateTime;
use DateTimeInterface;
use Exception;
use shared\json_fields;
use shared\types\api_type_list;

class change extends change_log
{

    /*
     * messages
     */

    // TODO replace by a language specific message id
    const MSG_ADD = 'added';
    const MSG_UPDATE = 'changed';
    const MSG_DEL = 'deleted';
    const MSG_LINK = 'linked';

    /*
     * db const
     */

    // user log database and JSON object field names for named user sandbox objects
    // *_COM is the description of the field
    // *_SQL_TYP is the sql data type used for the field
    const FLD_FIELD_ID = 'change_field_id';
    const FLD_FIELD_ID_SQL_TYP = sql_field_type::INT_SMALL;
    const FLD_ROW_ID = 'row_id';
    const FLD_OLD_VALUE = 'old_value';
    const FLD_OLD_VALUE_SQL_TYP = sql_field_type::TEXT;
    const FLD_OLD_ID_COM = 'old value id';
    const FLD_OLD_ID = 'old_id';
    const FLD_OLD_ID_SQL_TYP = sql_field_type::INT;
    const FLD_OLD_ID_NORM_SQL_TYP = sql_field_type::REF_512;
    const FLD_OLD_ID_BIG_SQL_TYP = sql_field_type::TEXT;
    const FLD_NEW_VALUE = 'new_value';
    const FLD_NEW_VALUE_SQL_TYP = sql_field_type::TEXT;
    const FLD_NEW_ID_COM = 'new value id';
    const FLD_NEW_ID = 'new_id';
    const FLD_NEW_ID_SQL_TYP = sql_field_type::INT;
    const FLD_OLD_EXT = '_old';

    // all database field names
    const FLD_NAMES = array(
        user::FLD_ID,
        self::FLD_TIME,
        self::FLD_ACTION,
        self::FLD_FIELD_ID,
        self::FLD_ROW_ID,
        change::FLD_OLD_VALUE,
        self::FLD_OLD_ID,
        change::FLD_NEW_VALUE,
        self::FLD_NEW_ID
    );

    // field list to log the actual change of the named user sandbox object
    const FLD_LST_CHANGE = array(
        [self::FLD_FIELD_ID, self::FLD_FIELD_ID_SQL_TYP, sql_field_default::NOT_NULL, '', change_field::class, ''],
        [change::FLD_OLD_VALUE, change::FLD_OLD_VALUE_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [change::FLD_NEW_VALUE, change::FLD_NEW_VALUE_SQL_TYP, sql_field_default::NULL, '', '', ''],
        [self::FLD_OLD_ID, self::FLD_OLD_ID_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_OLD_ID_COM],
        [self::FLD_NEW_ID, self::FLD_NEW_ID_SQL_TYP, sql_field_default::NULL, '', '', self::FLD_NEW_ID_COM],
    );


    /*
     * object vars
     */

    // additional to user_log
    public string|float|int|DateTime|null $old_value = null;      // the field value before the user change
    public ?int $old_id = null;            // the reference id before the user change e.g. for fields using a sub table such as status
    public string|float|int|DateTime|null $new_value = null;      // the field value after the user change
    public ?int $new_id = null;            // the reference id after the user change e.g. for fields using a sub table such as status
    public string|float|int|DateTime|null $std_value = null;  // the standard field value for all users that does not have changed it
    public ?int $std_id = null;        // the standard reference id for all users that does not have changed it


    /*
     * construct and map
     */

    /**
     * map the database fields to one change log entry to this log object
     *
     * @param array|null $db_row with the data directly from the database
     * @param string $id_fld the name of the id field as set in the child class
     * @return bool true if a change log entry is found
     */
    function row_mapper(?array $db_row, string $id_fld = '', ?user $usr = null): bool
    {
        global $debug;
        global $cng_fld_cac;
        $result = parent::row_mapper($db_row, self::FLD_ID);
        if ($result) {
            $this->action_id = $db_row[self::FLD_ACTION];
            $this->field_id = $db_row[self::FLD_FIELD_ID];
            if (array_key_exists(self::FLD_ROW_ID, $db_row)) {
                $this->row_id = $db_row[self::FLD_ROW_ID];
            } elseif (array_key_exists(group::FLD_ID, $db_row)) {
                $this->row_id = $db_row[group::FLD_ID];
            }
            $this->set_time_str($db_row[self::FLD_TIME]);
            $this->old_value = $db_row[change::FLD_OLD_VALUE];
            if (array_key_exists(self::FLD_OLD_ID, $db_row)) {
                $this->old_id = $db_row[self::FLD_OLD_ID];
            }
            $this->new_value = $db_row[change::FLD_NEW_VALUE];
            if (array_key_exists(self::FLD_NEW_ID, $db_row)) {
                $this->new_id = $db_row[self::FLD_NEW_ID];
            }

            $fld_tbl = $cng_fld_cac->get($this->field_id);
            $this->table_id = preg_replace("/[^0-9]/", '', $fld_tbl->name);
            // TODO check if not the complete user should be loaded
            $usr_set = false;
            if ($usr != null) {
                if ($db_row[user::FLD_ID] == $usr->id()) {
                    $this->set_user($usr);
                    $usr_set = true;
                }
            }
            if (!$usr_set) {
                $row_usr = new user();
                $row_usr->set_id($db_row[user::FLD_ID]);
                $row_usr->name = $db_row[user::FLD_NAME];
                $this->set_user($row_usr);
            }
            log_debug('Change ' . $this->id() . ' loaded', $debug - 8);
        }
        return $result;
    }


    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of the change log
     * TODO use class name instead of TBL_CHANGE
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        if ($this::class == changes_norm::class
            or $this::class == changes_big::class) {
            $qp = new sql_par(change::class);
        } else {
            $qp = new sql_par($this::class);
        }
        $sc->set_class($this::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_fields(self::FLD_NAMES);
        $sc->set_join_fields(array(user::FLD_NAME), user::class);
        $sc->set_join_fields(array(change_field_list::FLD_TABLE), change_field::class);
        $sc->set_order(self::FLD_TIME, sql::ORDER_DESC);

        return $qp;
    }

    /**
     * create an SQL statement to retrieve a change long entry by the changing user
     *
     * @param sql_creator $sc with the target db_type set
     * @param user|null $usr the id of the user sandbox object
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_user(sql_creator $sc, ?user $usr = null): sql_par
    {
        $qp = $this->load_sql($sc, 'user_last', self::class);

        if ($usr == null) {
            $usr = $this->user();
        }

        $sc->add_where(user::FLD_ID, $usr->id());
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * create the SQL statement to retrieve the parameters of the change log by field and row id
     *
     * @param sql_creator $sc with the target db_type set
     * @param int|null $field_id the database id of the database field (and table) of the changes that the user wants to see
     * @param int|null $row_id the database id of the database row of the changes that the user wants to see
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_by_field_row(sql_creator $sc, ?int $field_id = null, ?int $row_id = null): sql_par
    {
        $qp = $this->load_sql($sc, 'field_row', self::class);
        if ($field_id != null) {
            $sc->add_where(change::FLD_FIELD_ID, $field_id);
        }
        if ($field_id != null) {
            $sc->add_where(change::FLD_ROW_ID, $row_id);
        }
        // TODO check!
        //$fields[] = user::FLD_ID;
        $sc->set_page();
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();

        return $qp;
    }

    /**
     * create the SQL statement to retrieve the parameters of the change log by name
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_by_user_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'user';
        $db_con->set_class(change::class);
        $db_con->set_name($qp->name);
        $db_con->set_usr($this->user()->id());
        $db_con->set_fields(self::FLD_NAMES);
        $qp->sql = $db_con->select_by_set_id();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    function load_sql_old(string $type, int $limit = 0): sql_par
    {
        global $db_con;
        global $cng_tbl_cac;

        $result = ''; // reset the html code var

        $qp = new sql_par(self::class);

        // set default values
        if ($limit <= 0) {
            $limit = sql_db::ROW_LIMIT;
        }

        // select the change table to use
        $sql_where = '';
        $sql_row = '';
        $sql_user = '';
        // the setting for most cases
        $sql_row = ' s.row_id  = $2 ';
        // the class specific settings
        if ($type == user::class) {
            $sql_where = " (f.table_id = " . $cng_tbl_cac->id(change_table_list::WORD) . " 
                   OR f.table_id = " . $cng_tbl_cac->id(change_table_list::WORD_USR) . ") AND ";
            $sql_row = '';
            $sql_user = 's.user_id = u.user_id
                AND s.user_id = ' . $this->user()->id() . ' ';
        } elseif ($type == word::class) {
            //$db_con->add_par(sql_par_type::INT, $cng_tbl_cac->id(change_table_list::WORD));
            //$db_con->add_par(sql_par_type::INT, $cng_tbl_cac->id(change_table_list::WORD_USR));
            $sql_where = " s.change_field_id = $1 ";
        } elseif ($type == value::class) {
            $sql_where = " (f.table_id = " . $cng_tbl_cac->id(change_table_list::VALUE) . " 
                     OR f.table_id = " . $cng_tbl_cac->id(change_table_list::VALUE_USR) . ") AND ";
        } elseif ($type == formula::class) {
            $sql_where = " (f.table_id = " . $cng_tbl_cac->id(change_table_list::FORMULA) . " 
                     OR f.table_id = " . $cng_tbl_cac->id(change_table_list::FORMULA_USR) . ") AND ";
        } elseif ($type == view::class) {
            $sql_where = " (f.table_id = " . $cng_tbl_cac->id(change_table_list::VIEW) . " 
                     OR f.table_id = " . $cng_tbl_cac->id(change_table_list::VIEW_USR) . ") AND ";
        } elseif ($type == component::class) {
            $sql_where = " (f.table_id = " . $cng_tbl_cac->id(change_table_list::VIEW_COMPONENT) . " 
                     OR f.table_id = " . $cng_tbl_cac->id(change_table_list::VIEW_COMPONENT_USR) . ") AND ";
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
               LIMIT " . $limit . ";";
            log_debug('user_log_display->dsp_hist ' . $qp->sql);
            $db_con->usr_id = $this->user()->id();
        }
        return $qp;
    }


    /*
     * api
     */

    /**
     * create an array for the json api message
     *
     * differs from the export array by using the internal id instead of the names
     * @param api_type_list $typ_lst configuration for the api message e.g. if phrases should be included
     * @param user|null $usr the user for whom the api message should be created which can differ from the session user
     * @return array the filled array used to create the api json message to the frontend
     */
    function api_json_array(api_type_list $typ_lst, user|null $usr = null): array
    {
        $vars = parent::api_json_array($typ_lst, $usr);
        $vars[json_fields::OLD_VALUE] = $this->old_value;
        $vars[json_fields::OLD_ID] = $this->old_id;
        $vars[json_fields::NEW_VALUE] = $this->new_value;
        $vars[json_fields::NEW_ID] = $this->new_id;
        $vars[json_fields::STD_VALUE] = $this->std_value;
        $vars[json_fields::STD_ID] = $this->std_id;

        return $vars;
    }


    /*
     * save
     */

    /**
     * add the row id to an existing log entry
     * e.g. because the row id is known after the adding of the real record,
     * but the log entry has been created upfront to make sure that logging is complete
     * TODO: accept also strings as row_id for values and results
     */
    function add_ref($row_id): bool
    {
        log_debug("user_log->add_ref (" . $row_id . " to " . $this->id() . " for user " . $this->user()->dsp_id() . ")");

        global $db_con;
        $result = false;

        $db_type = $db_con->get_class();
        if ($this::class == changes_big::class) {
            $db_con->set_class(changes_big::class);
        } elseif ($this::class == changes_norm::class) {
            $db_con->set_class(changes_norm::class);
        } else {
            $db_con->set_class(change::class);
        }
        $db_con->set_usr($this->user()->id());
        if ($db_con->update_old($this->id(), self::FLD_ROW_ID, $row_id)) {
            // restore the type before saving the log
            $db_con->set_class($db_type);
            $result = True;
        } else {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->user() == null) {
                log_fatal("Update of reference in the change log failed.", "user_log->add_ref", 'Update of reference in the change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Update of reference in the change log failed with (" . $this->user()->dsp_id() . "," . $this->action() . "," . $this->table() . "," . $this->field() . ")", "user_log->add_ref");
                log_fatal("Update of reference in the change log failed with (" . $this->user()->dsp_id() . "," . $this->action() . "," . $this->table() . "," . $this->field() . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add_ref");
            }
        }
        return $result;
    }


    /*
     * sql write
     */

    /**
     * @return sql_type the sql type of the change e.g. if a name is changes it returns sql_type::UPDATE
     */
    function sql_type(): sql_type
    {
        $typ = sql_type::UPDATE;
        if ($this->old_id == null and $this->new_id == null) {
            if ($this->old_value == null) {
                $typ = sql_type::INSERT;
            } elseif ($this->new_value == null) {
                $typ = sql_type::DELETE;
            }
        } elseif ($this->old_id == null) {
            $typ = sql_type::INSERT;
        } elseif ($this->new_id == null) {
            $typ = sql_type::DELETE;
        }
        return $typ;
    }

    /**
     * @return sql_type an addition sql type for the change e.g. if the phrase type is changed REF is added
     */
    function sql_sub_type(): sql_type
    {
        $typ = sql_type::NULL;
        if ($this->old_id != null or $this->new_id != null) {
            $typ = sql_type::REF;
        }
        return $typ;
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
        sql_par_type $val_typ = sql_par_type::TEXT
    ): sql_par_field_list
    {
        $fvt_lst = parent::db_field_values_types($sc, $sc_par_lst, $val_typ);

        if ($this->old_value !== null or ($sc_par_lst->is_update_part() and $this->new_value !== null)) {
            $fvt_lst->add_field(change::FLD_OLD_VALUE, $this->old_value, $sc->get_sql_par_type($this->old_value));
        }
        if ($this->new_value !== null or ($sc_par_lst->is_update_part() and !$sc_par_lst->exclude_name_only() and $this->old_value !== null)) {
            $fvt_lst->add_field(change::FLD_NEW_VALUE, $this->new_value, $sc->get_sql_par_type($this->new_value));
        }

        if ($this->old_id > 0 or ($sc_par_lst->is_update_part() and $this->new_id > 0)) {
            $fvt_lst->add_field(self::FLD_OLD_ID, $this->old_id, sql_par_type::INT);
        }
        if ($this->new_id > 0 or ($sc_par_lst->is_update_part() and $this->old_id > 0)) {
            $fvt_lst->add_field(self::FLD_NEW_ID, $this->new_id, sql_par_type::INT);
        }

        $row_typ = sql_par_type::INT;
        if ($this::class == changes_norm::class) {
            $row_typ = sql_par_type::KEY_512;
        } elseif ($this::class == changes_big::class) {
            $row_typ = sql_par_type::TEXT;
        }
        $fvt_lst->add_field(self::FLD_ROW_ID, $this->row_id, $row_typ);
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
        $sql_fields = parent::db_fields();

        if ($this->old_value !== null) {
            $sql_fields[] = change::FLD_OLD_VALUE;
        }
        if ($this->new_value !== null) {
            $sql_fields[] = change::FLD_NEW_VALUE;
        }

        if ($this->old_id > 0) {
            $sql_fields[] = self::FLD_OLD_ID;
        }
        if ($this->new_id > 0) {
            $sql_fields[] = self::FLD_NEW_ID;
        }

        $sql_fields[] = self::FLD_ROW_ID;
        return $sql_fields;
    }

    /**
     * get a list of database field values that have been updated
     *
     * @return array list of the database field values
     */
    function db_values(): array
    {
        $sql_values = parent::db_values();

        if ($this->old_value !== null) {
            $sql_values[] = $this->old_value;
        }
        if ($this->new_value !== null) {
            $sql_values[] = $this->new_value;
        }

        if ($this->old_id > 0) {
            $sql_values[] = $this->new_id;
        }
        if ($this->new_id > 0) {
            $sql_values[] = $this->new_id;
        }

        $sql_values[] = $this->row_id;
        return $sql_values;
    }


    /*
     * debug
     */

    function dsp_id(): string
    {
        $result = 'log ' . $this->action() . ' ';
        $result .= $this->table() . ',' . $this->field() . ' ';
        if ($this->old_value != null) {
            if ($this->new_value != null) {
                $result .= 'from ' . $this->old_value . ' (id ' . $this->old_id . ')';
                $result .= 'to ' . $this->new_value . ' (id ' . $this->new_id . ')';
            } else {
                $result .= $this->old_value . ' (id ' . $this->old_id . ')';
            }
        } else {
            if ($this->new_value != null) {
                $result .= $this->new_value . ' (id ' . $this->new_id . ')';
            }
        }
        $result .= ' in row ' . $this->row_id;
        $result .= ' at ' . $this->change_time->format(DateTimeInterface::ATOM);
        return $result;
    }


    /*
     * TODO deprecate
     */

    function dsp_obj(): change_log_named_dsp
    {
        $dsp_obj = new change_log_named_dsp();
        $this->fill_obj($dsp_obj);
        return $dsp_obj;

    }


    /*
      * display
      */

    // TODO to be move to frontend

    /**
     * @return string the last change of given user
     *                optional without time for automatic testing
     */
    function dsp_last_user(bool $ex_time = false, ?user $usr = null): string
    {

        global $db_con;

        $db_type = $db_con->get_class();
        $qp = $this->load_sql_by_user($db_con->sql_creator(), $usr);
        $db_row = $db_con->get1($qp);

        $this->row_mapper($db_row);
        $result = $this->dsp($db_row, $ex_time);

        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $result;
    }

    /**
     * display the last change related to one object (word, formula, value, verb, ...)
     * mainly used for testing
     * TODO if changes on table values are requested include also the table "user_values"
     */
    function dsp_last(bool $ex_time = false): string
    {

        global $db_con;

        $db_type = $db_con->get_class();
        $qp = $this->load_sql_by_field_row($db_con->sql_creator(), $this->field_id, $this->row_id);
        $db_row = $db_con->get1($qp);

        $this->row_mapper($db_row);
        $result = $this->dsp($db_row, $ex_time);

        // restore the type before saving the log
        $db_con->set_class($db_type);
        return $result;
    }

    /**
     * @return string the current change as a human-readable text
     *                optional without time for automatic testing
     */
    private function dsp(array $db_row, bool $ex_time = false): string
    {
        $result = '';
        $usr_cfg = new config();

        if ($db_row) {
            if (!$ex_time) {
                $result .= date_format($this->time(), $usr_cfg->date_time_format()) . ' ';
            }
            if ($this->user() != null) {
                if ($this->user()->name() <> '') {
                    $result .= $this->user()->name() . ' ';
                }
            }
            if ($this->old_value <> '') {
                if ($this->new_value <> '') {
                    $result .= self::MSG_UPDATE . ' "' . $this->old_value . '" to "' . $this->new_value . '"';
                } else {
                    $result .= self::MSG_DEL . ' "' . $this->old_value . '"';;
                }
            } else {
                $result .= self::MSG_ADD . ' "' . $this->new_value . '"';;
            }
        }
        return $result;
    }

}