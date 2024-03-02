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

use cfg\db\sql_field_default;
use cfg\db\sql_field_type;
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
        [self::FLD_FIELD_ID, sql_field_type::INT, sql_field_default::NOT_NULL, '', change_field::class, ''],
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

}