<?php

/*

    model/system/ip_range_db.php - the database const for ip_range tables
    ----------------------------

    The main sections of this object are
    - db const:          const for the database link


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/


namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_field_default.php';
include_once paths::DB . 'sql_field_type.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_default;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_field_type;

class ip_range_db
{

    /*
     * database link
     */

    // object specific database object field names and comments
    const string FLD_ID = 'ip_range_id';
    const string FLD_KEY = 'ip_range_key'; // combines the ip from and to fields for easy lookup
    const string FLD_KEY_COM = 'combines the from and to ip address to one key';

    const string FLD_FROM = 'ip_from';
    const string FLD_TO = 'ip_to';
    const string FLD_REASON = 'reason';
    const string FLD_ACTIVE = 'is_active';

    const array FLD_NAMES = array(
        self::FLD_KEY,
        self::FLD_FROM,
        self::FLD_TO,
        self::FLD_REASON,
        self::FLD_ACTIVE
    );

    // field lists for the table creation
    const array FLD_LST_ALL = array(
        [self::FLD_KEY, sql_field_type::TEXT, sql_field_default::NOT_NULL, sql::INDEX, '', self::FLD_KEY_COM],
        [self::FLD_FROM, sql_field_type::IP_ADDR, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [self::FLD_TO, sql_field_type::IP_ADDR, sql_field_default::NOT_NULL, sql::INDEX, '', ''],
        [self::FLD_REASON, sql_field_type::TEXT, sql_field_default::NOT_NULL, '', '', ''],
        [self::FLD_ACTIVE, sql_field_type::INT_SMALL, sql_field_default::ONE, '', '', ''],
    );

}