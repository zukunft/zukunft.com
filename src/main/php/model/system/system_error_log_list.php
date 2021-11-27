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
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2021 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/


class system_error_log_list
{

    // display types
    const DSP_ALL = 'all';
    const DSP_MY = 'my';
    const DSP_OTHER = 'other';

    public ?array $lst = null;      // a list of system error objects
    public ?user $usr = null;       // the user who wants to see the errors
    public ?string $dsp_type = '';  //
    public ?int $page = null;       //
    public ?int $size = null;       //
    public ?string $back = '';      //

    /**
     * @return system_error_log_list_dsp a filled frontend api object
     */
    function get_dsp_obj(): system_error_log_list_dsp
    {
        $dsp_obj = new system_error_log_list_dsp();
        foreach ($this->lst AS $log) {
            $dsp_obj->system_errors[] = $log->get_dsp_obj();
        }
        return $dsp_obj;
    }

    /**
     * create the SQL statement to load a list of system log entries
     * @param sql_db $db_con the database link as parameter to be able to simulate the different SQL database in the unit tests
     * @param bool $get_name to receive the unique name to be able to precompile the statement to prevent code injections
     * @return string the database depending on sql statement to load a system error from the log table
     *                or the unique name for the query
     */
    function load_sql(sql_db $db_con, bool $get_name = false): string
    {
        $sql_name = self::class . '_';
        $sql_where = '';
        $sql_status = '(' . sql_db::STD_TBL . '.' . system_error_log::FLD_STATUS . ' <> ' . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED);
        $sql_status .= ' OR ' . sql_db::STD_TBL . '.' . system_error_log::FLD_STATUS . ' IS NULL)';
        if ($this->dsp_type == self::DSP_ALL) {
            $sql_where = $sql_status;
            $sql_name .= self::DSP_ALL;
        } elseif ($this->dsp_type == self::DSP_OTHER) {
            $sql_where = $sql_status . ' AND (' . sql_db::STD_TBL . '.' . user_sandbox::FLD_USER . ' <> ' . $this->usr->id . ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
        } elseif ($this->dsp_type == self::DSP_MY) {
            $sql_where = $sql_status . ' AND (' . sql_db::STD_TBL . '.' . user_sandbox::FLD_USER . ' = ' . $this->usr->id . ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
        } else {
            log_err('Unknown system log selection "' . $this->dsp_type . '"');
        }

        if ($sql_where <> '') {
            $db_con->set_type(DB_TYPE_SYS_LOG);
            $db_con->set_fields(system_error_log::FLD_NAMES);
            $db_con->set_join_fields(array(system_error_log::FLD_FUNCTION_NAME), DB_TYPE_SYS_LOG_FUNCTION);
            $db_con->set_join_fields(array(user_type::FLD_NAME), DB_TYPE_SYS_LOG_STATUS);
            $db_con->set_join_fields(array(user_sandbox::FLD_USER_NAME), DB_TYPE_USER);
            $db_con->set_join_fields(array(user_sandbox::FLD_USER_NAME . ' AS ' . system_error_log::FLD_SOLVER_NAME), DB_TYPE_USER, system_error_log::FLD_SOLVER);
            $db_con->set_where_text($sql_where);
            $db_con->set_order(system_error_log::FLD_TIME, sql_db::ORDER_DESC);
            $db_con->set_page($this->page, $this->size);
            $sql = $db_con->select();

            if ($get_name) {
                return $sql_name;
            } else {
                return $sql;
            }
        } else {
            return '';
        }
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

        $sql = $this->load_sql($db_con);
        $db_con->usr_id = $this->usr->id;
        $db_lst = $db_con->get($sql);

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
    function add(system_error_log $log) {
        $this->lst[] = $log;
    }

}