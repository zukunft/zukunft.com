<?php

/*

  system_log_list.php - a list of system error objects
  -------------------
  
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
  
  Copyright (c) 1995-2022 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/


use api\system_error_log_list_api;
use html\system_error_log_list_dsp;

class system_error_log_list
{

    // display types
    const DSP_ALL = 'all';
    const DSP_MY = 'my';
    const DSP_OTHER = 'other';

    public ?array $lst = null;      // a list of system error objects
    public ?user $usr = null;       // the user who wants to see the errors
    public ?string $dsp_type = '';  //
    public int $page = 0;       //
    public int $size = 0;       //
    public ?string $back = '';      //

    /*
     * casting objects
     */

    /**
     * @return system_error_log_list_api a filled frontend api object
     */
    function api_obj(): system_error_log_list_api
    {
        $api_obj = new system_error_log_list_api();
        foreach ($this->lst as $log) {
            $api_obj->system_errors[] = $log->get_dsp_obj();
        }
        return $api_obj;
    }

    /**
     * @return system_error_log_list_dsp a filled frontend display object
     */
    function dsp_obj(): system_error_log_list_dsp
    {
        $api_obj = new system_error_log_list_dsp();
        foreach ($this->lst as $log) {
            $api_obj->system_errors[] = $log->get_dsp_obj();
        }
        return $api_obj;
    }

    /*
     * loading / database access object (DAO) functions
     */

    /**
     * create the SQL statement to load a list of system log entries
     * @param sql_db $db_con the database link as parameter to be able to simulate the different SQL database in the unit tests
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        $qp = new sql_par(self::class);

        $sql_where = '';
        $sql_status = '(' . sql_db::STD_TBL . '.' . system_error_log::FLD_STATUS . ' <> ' . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED);
        $sql_status .= ' OR ' . sql_db::STD_TBL . '.' . system_error_log::FLD_STATUS . ' IS NULL)';
        if ($this->dsp_type == self::DSP_ALL) {
            $sql_where = $sql_status;
            $qp->name .= self::DSP_ALL;
        } elseif ($this->dsp_type == self::DSP_OTHER) {
            $db_con->add_par(sql_db::PAR_INT, $this->usr->id);
            $sql_where = $sql_status .
                ' AND (' . sql_db::STD_TBL . '.' . user_sandbox::FLD_USER . ' <> ' . $db_con->par_name() .
                ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
            $qp->name .= self::DSP_OTHER;
        } elseif ($this->dsp_type == self::DSP_MY) {
            $db_con->add_par(sql_db::PAR_INT, $this->usr->id);
            $sql_where = $sql_status .
                ' AND (' . sql_db::STD_TBL . '.' . user_sandbox::FLD_USER . ' = ' . $db_con->par_name() .
                ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
            $qp->name .= self::DSP_MY;
        } else {
            log_err('Unknown system log selection "' . $this->dsp_type . '"');
        }

        if ($sql_where <> '') {
            $db_con->set_type(DB_TYPE_SYS_LOG);
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->usr->id);
            $db_con->set_fields(system_error_log::FLD_NAMES);
            $db_con->set_join_fields(array(system_error_log::FLD_FUNCTION_NAME), DB_TYPE_SYS_LOG_FUNCTION);
            $db_con->set_join_fields(array(user_type::FLD_NAME), DB_TYPE_SYS_LOG_STATUS);
            $db_con->set_join_fields(array(user_sandbox::FLD_USER_NAME), DB_TYPE_USER);
            $db_con->set_join_fields(array(
                user_sandbox::FLD_USER_NAME . ' AS ' . system_error_log::FLD_SOLVER_NAME),
                DB_TYPE_USER, system_error_log::FLD_SOLVER);
            $db_con->set_where_text($sql_where);
            $db_con->set_order(system_error_log::FLD_TIME, sql_db::ORDER_DESC);
            $db_con->set_page_par($this->size, $this->page);
            $sql = $db_con->select_by_id();
            $qp->sql = $sql;
            $qp->par = $db_con->get_par();
        }

        return $qp;
    }

    /**
     * load a list of system errors from the database
     * @return bool true if everything was fine
     */
    function load(): bool
    {
        log_debug('system_error_log_list->load for user "' . $this->usr->name . '"');

        global $db_con;
        $result = false;

        $qp = $this->load_sql($db_con);
        $db_lst = $db_con->get($qp);

        if (count($db_lst) > 0) {
            foreach ($db_lst as $db_row) {
                $log = new system_error_log();
                $log->row_mapper($db_row);
                $this->lst[] = $log;
            }
            $result = true;
        }

        return $result;
    }

    /**
     * simple add another system log entry to the list
     */
    function add(system_error_log $log): void
    {
        $this->lst[] = $log;
    }

}