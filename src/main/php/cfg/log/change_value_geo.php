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

include_once MODEL_LOG_PATH . 'change_log.php';
include_once DB_PATH . 'sql.php';
include_once DB_PATH . 'sql_creator.php';
include_once DB_PATH . 'sql_field_default.php';
include_once DB_PATH . 'sql_field_type.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_field_list.php';
include_once DB_PATH . 'sql_par_type.php';
include_once DB_PATH . 'sql_type.php';
include_once DB_PATH . 'sql_type_list.php';
//include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_USER_PATH . 'user.php';
include_once WEB_LOG_PATH . 'change_log_named.php';
include_once SHARED_ENUM_PATH . 'change_fields.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_par;
use cfg\db\sql_par_field_list;
use cfg\db\sql_par_type;
use cfg\db\sql_type_list;
use cfg\helper\type_object;
use cfg\user\user;
use shared\enum\change_fields;

class change_value_geo extends change_value
{

    /*
     * database link
     */

    // user log database and JSON object field names for named user sandbox objects
    const TBL_COMMENT = 'to log all geo value changes done by any user on all kind of values (table, prime, big and standard';

    // field list to log the actual change of the value with a standard group id
    const FLD_LST_CHANGE = array(
        [change::FLD_FIELD_ID, type_object::FLD_ID_SQL_TYP, sql_field_default::NOT_NULL, '', change_field::class, ''],
        [change::FLD_OLD_VALUE, sql_field_type::POINT, sql_field_default::NULL, '', '', ''],
        [change::FLD_NEW_VALUE, sql_field_type::POINT, sql_field_default::NULL, '', '', ''],
    );


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
        if ($this::class == change_values_geo_prime::class
            or $this::class == change_values_geo_norm::class
            or $this::class == change_values_geo_big::class) {
            $qp = new sql_par(change_value_geo::class);
        } else {
            $qp = new sql_par($this::class);
        }
        $sc->set_class($this::class);
        $qp->name .= $query_name;
        $sc->set_name($qp->name);
        $sc->set_fields($this::FLD_NAMES);
        $sc->set_join_fields(array(user::FLD_NAME), user::class);
        $sc->set_join_fields(array(change_fields::FLD_TABLE), change_field::class);
        $sc->set_order(change_log::FLD_TIME, sql::ORDER_DESC);

        return $qp;
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
        sql_par_type $val_typ = sql_par_type::POINT
    ): sql_par_field_list
    {
        return parent::db_field_values_types($sc, $sc_par_lst, $val_typ);
    }

}