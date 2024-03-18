<?php

/*

    model/system/system_error_log_status_list.php - list of the system log statuus
    ---------------------------------------------

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
include_once MODEL_SYSTEM_PATH . 'sys_log_type.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status.php';
include_once DB_PATH . 'sql_db.php';

global $sys_log_stati;

class sys_log_status_list extends type_list
{

    /**
     * overwrite the general user type list load function to keep the link to the table type capsuled
     * @param sql_db $db_con the database connection that can be either the real database connection or a simulation used for testing
     * @return bool true if load was successful
     */
    function load(sql_db $db_con, string $db_type = sql_db::TBL_SYS_LOG_STATUS): bool
    {
        return parent::load($db_con, $db_type);
    }

    /**
     * adding the system log stati used for unit tests to the dummy list
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(sys_log_status::OPEN, sys_log_status::OPEN, '', 2);
        $this->add($type);
        $type = new type_object(sys_log_status::CLOSED, sys_log_status::CLOSED, '', 3);
        $this->add($type);
    }

    /**
     * return the database id of the default system log status
     */
    function default_id(): int
    {
        return parent::id(sys_log_status::OPEN);
    }

}