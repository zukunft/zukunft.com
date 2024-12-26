<?php

/*

    cfg/system/session.php - to control the user frontend sessions
    ----------------------


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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

include_once DB_PATH . 'sql.php';
include_once MODEL_HELPER_PATH . 'db_object_seq_id.php';

use cfg\db\sql;
use cfg\db\sql_field_default;
use cfg\db\sql_field_type;

class session extends db_object_seq_id
{

    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to control the user frontend sessions';
    const FLD_UID_COM = 'the user session id as get by the frontend';
    const FLD_UID = 'uid';
    const FLD_HASH = 'hash';
    const FLD_EXPIRE = 'expire_date';
    const FLD_IP_ADDR = 'ip';
    const FLD_AGENT = 'agent';
    const FLD_COOKIE = 'cookie_crc';

    // field lists for the table creation
    const FLD_LST_ALL = array(
        [self::FLD_UID, sql_field_type::INT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_UID_COM],
        [self::FLD_HASH, sql_field_type::NAME_UNIQUE, sql_field_default::NOT_NULL, '', '', ''],
        [self::FLD_EXPIRE, sql_field_type::TIME, sql_field_default::NOT_NULL, '', '', ''],
        [self::FLD_IP_ADDR, sql_field_type::IP_ADDR, sql_field_default::NOT_NULL, '', '', ''],
        [self::FLD_AGENT, sql_field_type::NAME, sql_field_default::NULL, '', '', ''],
        [self::FLD_COOKIE, sql_field_type::TEXT, sql_field_default::NULL, '', '', ''],
    );

}
