<?php

/*

    model/system/ip_range_list.php - a list of internet protocol address ranges
    ------------------------------


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

include_once MODEL_SYSTEM_PATH . 'base_list.php';
include_once MODEL_SYSTEM_PATH . 'ip_range.php';
include_once MODEL_USER_PATH . 'user_message.php';

use cfg\db\sql_db;
use cfg\db\sql_par;
use html\system\messages;

class ip_range_list extends base_list
{
    /*
     * modify
     */

    /**
     * add an ip range to the list
     *
     * @param ip_range $range the ip range that should be added to the list
     * @return bool true if the object has been added
     */
    protected function add(ip_range $range): bool
    {
        return parent::add_obj($range);
    }

    /*
     * loading
     */

    /**
     * create an SQL statement to retrieve the all active ip ranges from the database
     *
     * @param sql_db $db_con the db connection object as a function parameter for unit testing
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql_obj_vars(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= 'active';

        $db_con->set_class(ip_range::class);
        $db_con->set_name($qp->name);
        $db_con->set_fields(ip_range::FLD_NAMES);
        $db_con->set_where_id(ip_range::FLD_ACTIVE, sql_db::VAL_BOOL_TRUE);
        $qp->sql = $db_con->select_all();
        $qp->par = $db_con->get_par();

        return $qp;
    }

    /**
     * load the active ip ranges
     *
     * @return true if at least one ip range has been loaded
     */
    function load(): bool
    {
        global $db_con;
        $result = false;

        $qp = $this->load_sql_obj_vars($db_con);
        $ip_lst = $db_con->get($qp);
        foreach ($ip_lst as $db_row) {
            $ip = new ip_range();
            $ip->row_mapper($db_row);
            if ($ip->id() > 0) {
                $this->add($ip);
                $result = true;
            }
        }

        return $result;
    }

    /*
     * using ip range list
     */

    /**
     * checks if the given ip range is within any of the ip range of this list
     *
     * @param string $ip_addr the ip address that should be checked
     * @return user_message explains which ip ranges has been violated and the given reason for the blocking
     */
    function includes(string $ip_addr): user_message
    {
        $result = new user_message;
        foreach ($this->lst() as $range) {
            if ($range->includes($ip_addr)) {
                $ui_msg = new messages();
                $msg = $ui_msg->txt(messages::IP_BLOCK_PRE_ADDR)
                    . $ip_addr
                    . $ui_msg->txt(messages::IP_BLOCK_POST_ADDR)
                    . $range->reason
                    . $ui_msg->txt(messages::IP_BLOCK_SOLUTION);
                $result->add_message($msg);
            }
        }
        return $result;
    }

}

