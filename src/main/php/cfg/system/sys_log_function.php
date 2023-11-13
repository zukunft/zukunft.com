<?php

/*

    model/system/sys_log_function.php - to group the system log entries by function
    ---------------------------------

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

use cfg\db\sql_db;

include_once MODEL_HELPER_PATH . 'type_list.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once DB_PATH . 'sql_db.php';

global $sys_log_stati;

class sys_log_function extends type_list
{
    // list predefined function groups e.g. to group the execution times to find possible improvements
    const UNDEFINED = "undefined";
    const DB_READ = "db_read";
    const DB_WRITE = "db_write";

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = self::class): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the system log functions used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(self::DB_READ, self::DB_READ, '', 2);
        $this->add($type);
        $type = new type_object(self::DB_WRITE, self::DB_WRITE, '', 3);
        $this->add($type);
    }

    /**
     * return the database id of the default system log function
     */
    function default_id(): int
    {
        return parent::id(self::UNDEFINED);
    }

}