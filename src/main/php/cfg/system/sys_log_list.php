<?php

/*

  model/system/sys_log_list.php - a list of system error objects
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

namespace cfg\system;

use cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'base_list.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_HELPER . 'db_object.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_SYSTEM . 'base_list.php';
include_once paths::MODEL_SYSTEM . 'sys_log.php';
include_once paths::MODEL_SYSTEM . 'sys_log_function.php';
include_once paths::MODEL_SYSTEM . 'sys_log_type.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status_list.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';

use cfg\db\sql;
use cfg\db\sql_db;
use cfg\db\sql_par;
use cfg\db\sql_par_type;
use cfg\helper\type_object;
use cfg\sandbox\sandbox;
use cfg\user\user;
use shared\enum\sys_log_statuus;

class sys_log_list extends base_list
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
     * loading / database access object (DAO) functions
     */

    /**
     * create the SQL statement to load a list of system log entries
     * @param sql_db $db_con the database link as parameter to be able to simulate the different SQL database in the unit tests
     * @return sql_par the SQL statement, the name of the SQL statement and the parameter list
     */
    function load_sql(sql_db $db_con): sql_par
    {
        global $sys_log_sta_cac;
        $qp = new sql_par(self::class);

        $sql_where = '';
        $sql_status = '(' . sql_db::STD_TBL . '.' . sys_log_status::FLD_ID . ' <> ' . $sys_log_sta_cac->id(sys_log_statuus::CLOSED);
        $sql_status .= ' OR ' . sql_db::STD_TBL . '.' . sys_log_status::FLD_ID . ' IS NULL)';
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
            $db_con->set_class(sys_log::class);
            $db_con->set_name($qp->name);
            $db_con->set_usr($this->user()->id());
            $db_con->set_fields(sys_log::FLD_NAMES);
            $db_con->set_join_fields(array(sys_log_function::FLD_NAME), sys_log_function::class);
            $db_con->set_join_fields(array(type_object::FLD_NAME), sys_log_status::class);
            $db_con->set_join_fields(array(sandbox::FLD_USER_NAME), user::class);
            $db_con->set_join_fields(array(
                sandbox::FLD_USER_NAME . ' AS ' . sys_log::FLD_SOLVER_NAME),
                user::class, sys_log::FLD_SOLVER);
            $db_con->set_where_text($sql_where);
            $db_con->set_order(sys_log::FLD_TIME, sql::ORDER_DESC);
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
                $log = new sys_log();
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
     * @param sys_log $log_to_add the log entry that should be added to the list
     * @returns bool true the log entry has been added
     */
    function add(sys_log $log_to_add): void
    {
        $this->add_obj($log_to_add);
    }

}