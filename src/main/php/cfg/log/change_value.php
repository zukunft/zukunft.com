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

    // additional to user_log
    public string|float|int|null $old_value = null;      // the field value before the user change
    public ?int $old_id = null;            // the reference id before the user change e.g. for fields using a sub table such as status
    public string|float|int|null $new_value = null;      // the field value after the user change
    public ?int $new_id = null;            // the reference id after the user change e.g. for fields using a sub table such as status
    public string|float|int|null $std_value = null;  // the standard field value for all users that does not have changed it
    public ?int $std_id = null;        // the standard reference id for all users that does not have changed it

    public ?string $group_id = null;  // the reference id of the row in the database table


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
            $fvt_lst->add_field(self::FLD_OLD_VALUE, $this->old_value, $sc->get_sql_par_type($this->old_value));
        }
        if ($this->new_value !== null or ($sc_par_lst->is_update_part() and $this->old_value !== null)) {
            $fvt_lst->add_field(self::FLD_NEW_VALUE, $this->new_value, $sc->get_sql_par_type($this->new_value));
        }

        $fvt_lst->add_field(group::FLD_ID, $this->group_id, sql_par_type::INT);
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

}