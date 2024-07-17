<?php

/*

    cfg/log/change_value.php - log object for changes of all kind of values (table, prime, big and standard)
    ------------------------

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

namespace cfg\log;

include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_LOG_PATH . 'change_log.php';
include_once API_LOG_PATH . 'change_log_named.php';
include_once WEB_LOG_PATH . 'change_log_named.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type_list;
use cfg\group\group;
use cfg\type_object;
use cfg\user;

class change_value extends change_log
{

    /*
     * database link
     */

    // user log database and JSON object field names for named user sandbox objects
    const TBL_COMMENT = 'to log all changes done by any user on all kind of values (table, prime, big and standard';
    const FLD_FIELD_ID = 'change_field_id';
    const FLD_GROUP_ID = 'group_id';
    const FLD_OLD_VALUE = 'old_value';
    const FLD_NEW_VALUE = 'new_value';

    // all database field names
    const FLD_NAMES = array(
        user::FLD_ID,
        self::FLD_TIME,
        self::FLD_ACTION,
        self::FLD_FIELD_ID,
        self::FLD_ROW_ID,
        self::FLD_OLD_VALUE,
        self::FLD_NEW_VALUE,
    );

    // field list to log the actual change of the value with a standard group id
    const FLD_LST_CHANGE = array(
        [self::FLD_FIELD_ID, type_object::FLD_ID_SQLTYP, sql_field_default::NOT_NULL, '', change_field::class, ''],
        [self::FLD_OLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', ''],
        [self::FLD_NEW_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    // additional to user_log TODO change to float|int|null
    public string|float|int|null $old_value = null; // the field value before the user change
    public string|float|int|null $new_value = null; // the field value after the user change
    public string|float|int|null $std_value = null; // the standard field value for all users that does not have changed it

    public int|string|null $group_id = null;  // the reference id of the row in the database table

    // TODO deprecate
    public ?int $old_id = null;                     // the reference id before the user change e.g. for fields using a sub table such as status
    public ?int $new_id = null;                     // the reference id after the user change e.g. for fields using a sub table such as status
    public ?int $std_id = null;                     // the standard reference id for all users that does not have changed it


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields
     * list must be corresponding to the db_values fields
     *
     * @return sql_par_field_list list of the database field names
     */
    function db_field_values_types(sql $sc, sql_type_list $sc_par_lst): sql_par_field_list
    {
        $fvt_lst = parent::db_field_values_types($sc, $sc_par_lst);

        if ($this->old_value !== null or ($sc_par_lst->is_update_part() and $this->new_value !== null)) {
            $fvt_lst->add_field(self::FLD_OLD_VALUE, $this->old_value, sql_par_type::FLOAT);
        }
        if ($this->new_value !== null or ($sc_par_lst->is_update_part() and $this->old_value !== null)) {
            $fvt_lst->add_field(self::FLD_NEW_VALUE, $this->new_value, sql_par_type::FLOAT);
        }

        $grp_typ = sql_par_type::INT;
        if ($this::class == change_values_norm::class) {
            $grp_typ = sql_par_type::KEY_512;
        } elseif ($this::class == change_values_big::class) {
            $grp_typ = sql_par_type::TEXT;
        }
        $fvt_lst->add_field(group::FLD_ID, $this->group_id, $grp_typ);
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
            $sql_fields[] = self::FLD_OLD_VALUE;
        }
        if ($this->new_value !== null) {
            $sql_fields[] = self::FLD_NEW_VALUE;
        }

        $sql_fields[] = group::FLD_ID;
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

        $sql_values[] = $this->group_id;
        return $sql_values;
    }


    /*
     * save
     */

    /**
     * log a user change of a value or result
     * @return true if the change has been logged successfully
     */
    function add(): bool
    {
        log_debug($this->dsp_id());

        global $db_con;

        $db_type = $db_con->get_class();
        $sc = $db_con->sql_creator();
        $qp = $this->sql_insert($sc);
        $usr_msg = $db_con->insert($qp, 'log value');
        if ($usr_msg->is_ok()) {
            $log_id = $usr_msg->get_row_id();
        }

        if ($log_id <= 0) {
            // write the error message in steps to get at least some message if the parameters has caused the error
            if ($this->user() == null) {
                log_fatal("Insert to change log failed.", "user_log->add", 'Insert to change log failed', (new Exception)->getTraceAsString());
            } else {
                log_fatal("Insert to change log failed with (" . $this->user()->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . ")", "user_log->add");
                log_fatal("Insert to change log failed with (" . $this->user()->dsp_id() . "," . $this->action . "," . $this->table() . "," . $this->field() . "," . $this->old_value . "," . $this->new_value . "," . $this->row_id . ")", "user_log->add");
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

    function dsp_id(): string
    {
        $result = 'log ' . $this->action() . ' ';
        $result .= $this->table() . ',' . $this->field() . ' ';
        $result .= $this->name() . ' ';
        if ($this->old_value != null) {
            if ($this->new_value != null) {
                $result .= 'from ' . $this->old_value;
                $result .= 'to ' . $this->new_value;
            } else {
                $result .= $this->old_value;
            }
        } else {
            if ($this->new_value != null) {
                $result .= $this->new_value;
            }
        }
        return $result;
    }

    /**
     * @return string with the best possible id for this value mainly used for debugging
     */
    function name(): string
    {
        if ($this->group_id == null) {
            return '';
        } else {
            $grp = new group($this->user(), $this->group_id);
            return $grp->dsp_id_medium();
        }
    }

}