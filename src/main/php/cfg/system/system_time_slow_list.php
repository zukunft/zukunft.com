<?php

/*

    model/system/system_time_slow_list.php - the slow api requests of the last days for the admin dashboard
    --------------------------------------

    $slw_lst is the suggested var name

    a request is written to the system times only if it has been slower than the configured log limit
    (see application::write_time), so this list selects the requests of the last days that are still
    slower than the log limit with the slowest first

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

include_once paths::MODEL_SYSTEM . 'list_db_read.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_SYSTEM . 'system_time.php';
include_once paths::MODEL_SYSTEM . 'system_time_list.php';
include_once paths::MODEL_SYSTEM . 'system_time_type_list.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'system_time_type.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\system_time_type;
use DateTime;

class system_time_slow_list extends list_db_read
{

    // the max number of slow requests sent to the admin dashboard, which counts the ones it does not show directly
    const int ADMIN_READ_SIZE = 1000;
    const int SECONDS_PER_DAY = 86400;
    // the selection of the slow requests by the configured period, which names the prepared statement
    const string QUERY_NAME = 'period';


    /*
     * load
     */

    /**
     * load the slow api requests of the configured last days that are slower than the log limit
     *
     * @param user_message $msg to report a failed database read
     * @return bool true if at least one slow request has been found
     */
    function load(user_message $msg): bool
    {
        global $db_con;
        $sys_tim_typ_lst = new system_time_type_list();
        $sys_tim_typ_lst->load($db_con, $msg);
        $type_id = $sys_tim_typ_lst->id(system_time_type::API_REQUEST);
        $min_ms = (int)round(system_time_list::log_limit() * system_time_list::MILLISECONDS_PER_SECOND);
        $qp = $this->load_sql($db_con->sql_creator(), $type_id, self::since(time()), $min_ms);
        $db_rows = $db_con->get($qp, $msg, 'slow requests');
        // false means that the query itself has failed, which the db layer has already reported
        if (is_array($db_rows)) {
            foreach ($db_rows as $db_row) {
                $sys_tim = new system_time();
                $sys_tim->row_mapper($db_row, $msg);
                $this->add_obj($sys_tim);
            }
        }
        return !$this->is_empty();
    }

    /**
     * create the sql statement to select the slow api requests, the slowest first
     *
     * @param sql_creator $sc with the target db_type set
     * @param int $type_id the id of the api request area, because the steps of a request are not shown
     * @param DateTime $since only the requests that have started at this time or later
     * @param int $min_ms only the requests that have taken at least these milliseconds
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, int $type_id, DateTime $since, int $min_ms): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= self::QUERY_NAME;
        $sc->set_class(system_time::class);
        $sc->set_name($qp->name);
        $sc->set_fields(system_time::FLD_NAMES);
        $sc->add_where(system_time::FLD_GROUP, $type_id);
        $sc->add_where(system_time::FLD_TIME_START, $since->format(sql_db::DATE_FORMAT), sql_par_type::TIME_HIGHER);
        $sc->add_where(system_time::FLD_MILLISECONDS, $min_ms, sql_par_type::INT_HIGHER);
        $sc->set_order(system_time::FLD_MILLISECONDS, sql::ORDER_DESC);
        $sc->set_page(self::ADMIN_READ_SIZE);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * @param int $now the unix time of the request
     * @return DateTime the start of the configured period (config.yaml pod > limits > usage > response time > period)
     */
    static function since(int $now): DateTime
    {
        global $cfg;
        $names = [words::PERIOD, triples::RESPONSE_TIME, words::USAGE, words::LIMITS, words::POD];
        $days = (int)($cfg?->get_by($names, def::FALLBACK_SLOW_REQUEST_DAYS) ?? def::FALLBACK_SLOW_REQUEST_DAYS);
        return new DateTime()->setTimestamp($now - $days * self::SECONDS_PER_DAY);
    }

}
