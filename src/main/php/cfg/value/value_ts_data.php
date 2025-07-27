<?php

/*

    model/value/value_ts_data.php - for a single time series value data entry
    ---------------------------


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

namespace cfg\value;

use cfg\const\paths;

include_once paths::MODEL_HELPER . 'db_object_no_id.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';
include_once paths::DB . 'sql_type_list.php';

use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
use cfg\db\sql_type_list;
use cfg\helper\db_object_no_id;
use DateTime;

class value_ts_data extends db_object_no_id
{

    /*
     * database link
     */

    // comment used for the database creation
    const TBL_COMMENT = 'for a single time series value data entry and efficient saving of daily or intra-day values';
    const FLD_ID_COM = 'link to the value time series';
    const FLD_TIME_COM = 'short name of the configuration entry to be shown to the admin';
    const FLD_TIME = 'val_time';
    const FLD_VALUE_COM = 'the configuration value as a string';
    const FLD_VALUE = 'number';

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [value_time_series::FLD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_ID_COM],
        [self::FLD_TIME, sql_field_type::TIME, sql_field_default::NOT_NULL, '', '', self::FLD_TIME_COM],
        [self::FLD_VALUE, sql_field_type::NUMERIC_FLOAT, sql_field_default::NULL, '', '', self::FLD_VALUE_COM],
    );

    /*
     * object vars
     */

    // related database objects
    // public value_time_series $ts;
    public DateTime $timestamp;
    public float $value;


    /*
     * sql create
     */

    /**
     * the sql statement to create the table
     * is e.g. overwritten for the user sandbox objects
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the table
     */
    function sql_table(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_table_create($sc);
        return $sql;
    }

    /**
     * the sql statement to create the database indices
     * is e.g. overwritten for the user sandbox objects
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the indices
     */
    function sql_index(sql_creator $sc): string
    {
        $sql = $sc->sql_separator();
        $sql .= $this->sql_index_create($sc);
        return $sql;
    }

    /**
     * the sql statements to create all foreign keys
     * is e.g. overwritten for the user sandbox objects
     *
     * @param sql_creator $sc with the target db_type set
     * @return string the sql statement to create the foreign keys
     */
    function sql_foreign_key(sql_creator $sc): string
    {
        return $this->sql_foreign_key_create($sc, new sql_type_list(), [],false);
    }

}
