<?php

/*

    model/user/user_log_sum_list.php - the users that have used most server time in the last days
    --------------------------------

    $usr_log_sum_lst is the suggested var name

    the user log sessions of the configured last days summed per user and the users with the most
    request and update milliseconds first (config.yaml pod > limits > user > log > dashboard),
    so that the admin dashboard can show the users that may overload the pod


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

namespace Zukunft\ZukunftCom\main\php\cfg\user;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::API_OBJECT . 'api_message.php';
include_once paths::DB . 'sql.php';
include_once paths::DB . 'sql_creator.php';
include_once paths::DB . 'sql_db.php';
include_once paths::DB . 'sql_par.php';
include_once paths::DB . 'sql_par_type.php';
include_once paths::MODEL_CONST . 'def.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_log.php';
include_once paths::MODEL_USER . 'user_log_sum.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_TYPES . 'api_type_list.php';
include_once paths::SHARED . 'library.php';

use Zukunft\ZukunftCom\main\php\api\api_message;
use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_par_type;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\library;
use DateTime;

class user_log_sum_list
{

    // the selection of the users with the most server time, which names the prepared statement
    const string QUERY_NAME = 'server_time';
    const int SECONDS_PER_DAY = 86400;

    // the config.yaml path of the last days whose sessions are summed for the admin dashboard
    const array DAYS_NAMES = [words::DAYS, words::DASHBOARD, words::LOG, words::USER, words::LIMITS, words::POD];
    // the config.yaml path of the number of users that are sent to the admin dashboard
    const array TOP_NAMES = [words::TOP, words::DASHBOARD, words::LOG, words::USER, words::LIMITS, words::POD];


    /*
     *  object vars
     */

    // the summed sessions per user with the most server time first
    private array $lst = [];


    /*
     * load
     */

    /**
     * load the users with the most server time of the configured last days
     *
     * @param user_message $msg to report a failed database read
     * @return bool true if at least one user has been found
     */
    function load(user_message $msg): bool
    {
        global $db_con;
        global $cfg;
        $days = (int)($cfg?->get_by(self::DAYS_NAMES, def::FALLBACK_USER_LOG_DASHBOARD_DAYS)
            ?? def::FALLBACK_USER_LOG_DASHBOARD_DAYS);
        $top = (int)($cfg?->get_by(self::TOP_NAMES, def::FALLBACK_USER_LOG_DASHBOARD_TOP)
            ?? def::FALLBACK_USER_LOG_DASHBOARD_TOP);
        $qp = $this->load_sql($db_con->sql_creator(), self::since(time(), $days), $top);
        $db_rows = $db_con->get($qp, $msg, 'users with the most server time');
        // false means that the query itself has failed, which the db layer has already reported
        if (is_array($db_rows)) {
            foreach ($db_rows as $db_row) {
                $usr_log_sum = new user_log_sum();
                if ($usr_log_sum->row_mapper($db_row)) {
                    $this->lst[] = $usr_log_sum;
                }
            }
        }
        return $this->lst != [];
    }

    /**
     * create the sql statement to select the users with the most request and update milliseconds
     * of the sessions that have ended since the given time
     *
     * @param sql_creator $sc with the target db_type set
     * @param DateTime $since only the sessions that have ended at this time or later
     * @param int $top the max number of users to select
     * @return sql_par the SQL statement, the name of the SQL statement, and the parameter list
     */
    function load_sql(sql_creator $sc, DateTime $since, int $top): sql_par
    {
        $qp = new sql_par(self::class);
        $qp->name .= self::QUERY_NAME;
        $sc->set_class(user::class);
        $sc->set_name($qp->name);
        $sc->set_fields([user_db::FLD_NAME]);
        // the period is the first parameter, because it is used in the sub query that sums the sessions
        $sc->add_where_par(user_log::FLD_SESSION_END, $since->format(sql_db::DATE_FORMAT), sql_par_type::TIME_HIGHER);
        $sc->set_join_sql($this->load_sql_sum($sc->par_name(1)), user_log_sum::FLD_NAMES, user_db::FLD_ID);
        // the users without a session in the period are not selected
        $sc->add_where(user_log_sum::FLD_SERVER_TIME, '', sql_par_type::NOT_NULL, sql_db::LNK_TBL);
        $sc->set_order(user_log_sum::FLD_SERVER_TIME, sql::ORDER_DESC, sql_db::LNK_TBL);
        $sc->set_page($top);
        $qp->sql = $sc->sql();
        $qp->par = $sc->get_par();
        return $qp;
    }

    /**
     * the sub query that sums the sessions per user, because the sql creator has no sum function
     *
     * @param string $since_par the placeholder of the period parameter in the sql dialect e.g. $1
     * @return string the sql of the sessions summed per user that have ended within the period
     */
    private function load_sql_sum(string $since_par): string
    {
        $lib = new library();
        $sum = ' ' . sql::FUNCTION_SUM . ' (';
        $sql = '(' . sql::SELECT . ' ' . user_db::FLD_ID . ',';
        $sql .= $sum . user_log::FLD_REQUESTS . ') ' . sql::AS . ' ' . user_log::FLD_REQUESTS . ',';
        $sql .= $sum . user_log::FLD_UPDATED . ') ' . sql::AS . ' ' . user_log::FLD_UPDATED . ',';
        $sql .= $sum . user_log::FLD_REQUEST_TIME . ') ' . sql::AS . ' ' . user_log::FLD_REQUEST_TIME . ',';
        $sql .= $sum . user_log::FLD_UPDATE_TIME . ') ' . sql::AS . ' ' . user_log::FLD_UPDATE_TIME . ',';
        $sql .= $sum . user_log::FLD_REQUEST_TIME . ' + ' . user_log::FLD_UPDATE_TIME . ') '
            . sql::AS . ' ' . user_log_sum::FLD_SERVER_TIME;
        $sql .= ' ' . sql::FROM . ' ' . $lib->class_to_table(user_log::class);
        $sql .= ' ' . sql::WHERE . ' ' . user_log::FLD_SESSION_END . ' >= ' . $since_par;
        $sql .= ' ' . sql::GROUP_BY . ' ' . user_db::FLD_ID . ')';
        return $sql;
    }

    /**
     * @param int $now the unix time of the request
     * @param int $days the number of last days whose sessions are summed
     * @return DateTime the start of the period
     */
    static function since(int $now, int $days): DateTime
    {
        return new DateTime()->setTimestamp($now - $days * self::SECONDS_PER_DAY);
    }


    /*
     * modify
     */

    /**
     * @param user_log_sum $usr_log_sum the summed sessions of a user that should be added to the list
     * @return bool true if the summed sessions have been added
     */
    function add(user_log_sum $usr_log_sum): bool
    {
        $this->lst[] = $usr_log_sum;
        return true;
    }


    /*
     * api
     */

    /**
     * @param api_type_list|array $typ_lst configuration for the api message e.g. to include the header
     * @param user|null $usr the user for whom the api message is created
     * @return string the api json message with the summed sessions per user
     */
    function api_json(api_type_list|array $typ_lst = [], user|null $usr = null): string
    {
        global $db_con;
        $api_msg = new api_message();
        $pod_name = $api_msg->api_site_name($db_con);
        if (is_array($typ_lst)) {
            $typ_lst = new api_type_list($typ_lst);
        }
        return $api_msg->api_json($pod_name, $this::class, $this->api_json_array(), $typ_lst, $usr);
    }

    /**
     * @return array the summed sessions per user as an array for the api json message
     */
    function api_json_array(): array
    {
        $result = [];
        foreach ($this->lst as $usr_log_sum) {
            $result[] = $usr_log_sum->api_json_array();
        }
        return $result;
    }

}
