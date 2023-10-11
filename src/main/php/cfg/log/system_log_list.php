<?php

/*

  model/system/system_log_list.php - a list of system error objects
  --------------------------------
  
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

include_once DB_PATH . 'sql_db.php';
include_once DB_PATH . 'sql_par.php';
include_once DB_PATH . 'sql_par_type.php';
include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_HELPER_PATH . 'type_object.php';
include_once MODEL_SYSTEM_PATH . 'base_list.php';
include_once MODEL_SANDBOX_PATH . 'sandbox.php';
include_once MODEL_SYSTEM_PATH . 'sys_log_status.php';
include_once MODEL_LOG_PATH . 'system_log.php';
include_once API_LOG_PATH . 'system_log.php';
include_once API_LOG_PATH . 'system_log_list.php';
include_once WEB_LOG_PATH . 'system_log_list.php';
include_once WEB_LOG_PATH . 'system_log_list_old.php';

use cfg\db\sql_par_type;
use cfg\log\system_log;
use controller\log\system_log_list_api;
use html\log\system_log_list_dsp_old;

class system_log_list extends base_list
{

    // display types
    const DSP_ALL = 'all';
    const DSP_MY = 'my';
    const DSP_OTHER = 'other';

    private ?user $usr = null;      // the user who wants to see the errors
    public ?string $dsp_type = '';  //
    public int $page = 0;           //
    public int $size = 0;           //
    public ?string $back = '';      //


    /*
     * set and get
     */

    /**
     * set the user of the error log
     *
     * @param user|null $usr the person who wants to see the error log
     * @return void
     */
    function set_user(?user $usr): void
    {
        $this->usr = $usr;
    }

    /**
     * @return user|null the person who wants to see the error log
     */
    function user(): ?user
    {
        return $this->usr;
    }


    /*
     * cast
     */

    /**
     * @return system_log_list_api a filled frontend api object
     */
    function api_obj(user $usr): system_log_list_api
    {
        global $db_con;
        $api_obj = new system_log_list_api($db_con, $usr);
        foreach ($this->lst() as $log) {
            //$api_obj->add($log->api_obj());
            $api_obj->system_log[] = $log->get_api_obj();
        }
        return $api_obj;
    }

    /**
     * @returns string the api json message for the object as a string
     */
    function api_json(user $usr): string
    {
        return $this->api_obj($usr)->get_json();
    }

    /**
     * @return system_log_list_dsp_old a filled frontend display object
     */
    function dsp_obj(): system_log_list_dsp_old
    {
        global $usr;
        global $db_con;
        $dsp_obj = new system_log_list_dsp_old($db_con, $usr);
        foreach ($this->lst() as $log) {
            //$dsp_obj->add($log->dsp_obj());
            $dsp_obj->system_log[] = $log->get_api_obj();
        }
        return $dsp_obj;
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
        global $sys_log_stati;
        $qp = new sql_par(self::class);

        $sql_where = '';
        $sql_status = '(' . sql_db::STD_TBL . '.' . system_log::FLD_STATUS . ' <> ' . $sys_log_stati->id(sys_log_status::CLOSED);
        $sql_status .= ' OR ' . sql_db::STD_TBL . '.' . system_log::FLD_STATUS . ' IS NULL)';
        if ($this->dsp_type == self::DSP_ALL) {
            $sql_where = $sql_status;
            $qp->name .= self::DSP_ALL;
        } elseif ($this->dsp_type == self::DSP_OTHER) {
            $db_con->add_par(sql_par_type::INT, $this->user()->id());
            $sql_where = $sql_status .
                ' AND (' . sql_db::STD_TBL . '.' . user::FLD_ID . ' <> ' . $db_con->par_name() .
                ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
            $qp->name .= self::DSP_OTHER;
        } elseif ($this->dsp_type == self::DSP_MY) {
            $db_con->add_par(sql_par_type::INT, $this->user()->id());
            $sql_where = $sql_status .
                ' AND (' . sql_db::STD_TBL . '.' . user::FLD_ID . ' = ' . $db_con->par_name() .
                ' OR ' . sql_db::STD_TBL . '.user_id IS NULL) ';
            $qp->name .= self::DSP_MY;
        } else {
            log_err('Unknown system log selection "' . $this->dsp_type . '"');
        }

        if ($sql_where <> '') {
            $db_con->set_type(sql_db::TBL_SYS_LOG);
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(system_log::FLD_NAMES);
            $db_con->set_join_fields(array(system_log::FLD_FUNCTION_NAME), sys_log_function::class);
            $db_con->set_join_fields(array(type_object::FLD_NAME), sql_db::TBL_SYS_LOG_STATUS);
            $db_con->set_join_fields(array(sandbox::FLD_USER_NAME), sql_db::TBL_USER);
            $db_con->set_join_fields(array(
                sandbox::FLD_USER_NAME . ' AS ' . system_log::FLD_SOLVER_NAME),
                sql_db::TBL_USER, system_log::FLD_SOLVER);
            $db_con->set_where_text($sql_where);
            $db_con->set_order(system_log::FLD_TIME, sql_db::ORDER_DESC);
            $db_con->set_page_par($this->size, $this->page);
            $sql = $db_con->select_by_set_id();
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
        log_debug('for user "' . $this->user()->name . '"');

        global $db_con;
        $result = false;

        $qp = $this->load_sql($db_con);
        $db_lst = $db_con->get($qp);

        if (count($db_lst) > 0) {
            foreach ($db_lst as $db_row) {
                $log = new system_log();
                $log->row_mapper($db_row);
                $this->add_obj($log);
            }
            $result = true;
        }

        return $result;
    }

    /**
     * load a list of all system errors from the database
     * @return bool true if everything was fine
     */
    function load_all(): bool
    {
        $this->dsp_type = self::DSP_ALL;
        return $this->load();
    }

    /**
     * simple add another system log entry to the list
     * @param system_log $log_to_add the log entry that should be added to the list
     * @returns bool true the log entry has been added
     */
    function add(system_log $log_to_add): void
    {
        $this->add_obj($log_to_add);
    }

}