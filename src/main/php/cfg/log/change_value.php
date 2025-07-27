<?php

/*

    model/log/change_value.php - log object for changes of all kind of values (table, prime, big and standard)
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

use cfg\const\paths;

include_once paths::MODEL_LOG . 'change_log.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_field_list.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_type.php';
include_once paths::DB . 'sql_type_list.php';
//include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'json_fields.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type;
use cfg\db\sql_type_list;
use cfg\group\group;
use cfg\helper\type_object;
use cfg\user\user;
use cfg\user\user_db;
use DateTime;
use shared\enum\change_fields;
use shared\json_fields;
use shared\types\api_type_list;

class change_value extends change_log
{

    /*
     * database link
     */

    // user log database and JSON object field names for named user sandbox objects
    const TBL_COMMENT = 'to log all numeric value changes done by any user on all kind of values (table, prime, big and standard';
    const FLD_FIELD_ID = 'change_field_id';
    const FLD_GROUP_ID = 'group_id';
    const FLD_ROW_ID = self::FLD_GROUP_ID;

    // all database field names
    const FLD_NAMES = array(
        user::FLD_ID,
        self::FLD_TIME,
        self::FLD_ACTION,
        change::FLD_FIELD_ID,
        change_value::FLD_GROUP_ID,
        change::FLD_OLD_VALUE,
        change::FLD_NEW_VALUE,
    );

    // field list to log the actual change of the value with a standard group id
    const FLD_LST_CHANGE = array(
        [change::FLD_FIELD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, '', change_field::class, ''],
        [change::FLD_OLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', ''],
        [change::FLD_NEW_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', ''],
    );


    /*
     * object vars
     */

    // additional to user_log TODO change to float|int|null
    public string|float|int|DateTime|null $old_value = null; // the field value before the user change
    public string|float|int|DateTime|null $new_value = null; // the field value after the user change
    public string|float|int|DateTime|null $std_value = null; // the standard field value for all users that does not have changed it

    public int|string|null $group_id = null;  // the reference id of the row in the database table

    // TODO deprecate
    public ?int $old_id = null;                     // the reference id before the user change e.g. for fields using a sub table such as status
    public ?int $new_id = null;                     // the reference id after the user change e.g. for fields using a sub table such as status
    public ?int $std_id = null;                     // the standard reference id for all users that does not have changed it


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
            $this->new_value = $db_row[change::FLD_NEW_VALUE];

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
                $row_usr->name = $db_row[user_db::FLD_NAME];
                $this->set_user($row_usr);
            }
        }
        return $result;
    }

    /*
     * load
     */

    /**
     * create the common part of an SQL statement to retrieve the parameters of the value change log
     *
     * @param sql_creator $sc with the target db_type set
     * @param string $query_name the name extension to make the query name unique
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_creator $sc, string $query_name): sql_par
    {
        if ($this::class == change_values_prime::class
            or $this::class == change_values_norm::class
            or $this::class == change_values_big::class) {
            $qp = new sql_par(change_value::class);
        } else {
            $qp = new sql_par($this::class);
        }
        $sc->set_class($this::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_fields($this::FLD_NAMES);
        $sc->set_join_fields(array(user_db::FLD_NAME), user::class);
        $sc->set_join_fields(array(change_fields::FLD_TABLE), change_field::class);
        $sc->set_order(change_log::FLD_TIME, sql::ORDER_DESC);

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
        $vars[json_fields::NEW_VALUE] = $this->new_value;
        $vars[json_fields::STD_VALUE] = $this->std_value;

        return $vars;
    }


    /*
     * sql write
     */

    /**
     * @return sql_type the sql type of the change e.g. if a value is changes it returns sql_type::UPDATE
     */
    function sql_type(): sql_type
    {
        if ($this->old_value === null) {
            return sql_type::INSERT;
        } elseif ($this->new_value === null) {
            return sql_type::DELETE;
        } else {
            return sql_type::UPDATE;
        }
    }


    /*
     * sql write fields
     */

    /**
     * get a list of all database fields
     * list must be corresponding to the db_values fields
     *
     * @return sql_par_field_list list of the database field names
     */
    function db_field_values_types(
        sql_creator $sc,
        sql_type_list $sc_par_lst,
        sql_par_type $val_typ = sql_par_type::FLOAT
    ): sql_par_field_list
    {
        $fvt_lst = parent::db_field_values_types($sc, $sc_par_lst, $val_typ);

        if ($this->old_value !== null
            or ($sc_par_lst->is_update_part() and $this->new_value !== null)) {
            $fvt_lst->add_field(change::FLD_OLD_VALUE, $this->old_value, $val_typ);
        }
        if ($this->new_value !== null
            or ($sc_par_lst->is_update_part() and $this->old_value !== null)) {
            $fvt_lst->add_field(change::FLD_NEW_VALUE, $this->new_value, $val_typ);
        }

        $grp_typ = sql_par_type::INT;
        if ($this::class == change_values_norm::class
            or $this::class == change_values_time_norm::class
            or $this::class == change_values_text_norm::class
            or $this::class == change_values_geo_norm::class) {
            $grp_typ = sql_par_type::KEY_512;
        } elseif ($this::class == change_values_big::class
            or $this::class == change_values_time_big::class
            or $this::class == change_values_text_big::class
            or $this::class == change_values_geo_big::class) {
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
            $sql_fields[] = change::FLD_OLD_VALUE;
        }
        if ($this->new_value !== null) {
            $sql_fields[] = change::FLD_NEW_VALUE;
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