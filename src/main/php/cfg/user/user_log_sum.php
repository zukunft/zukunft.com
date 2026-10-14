<?php

/*

    model/user/user_log_sum.php - the summed user log sessions of one user e.g. of the last days
    ---------------------------

    $usr_log_sum is the suggested var name

    the sum of the requests and the request times of all sessions of one user that have ended
    within the given period, so that the admin can see which users use most of the server time
    (see user_log_sum_list)


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

include_once paths::MODEL_USER . 'user_db.php';
include_once paths::MODEL_USER . 'user_log.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\json_fields;

class user_log_sum
{

    /*
     * db const
     */

    // the sum of the request and the update milliseconds of a user, which decides the order of the users
    const string FLD_SERVER_TIME = 'server_time';

    // the fields of the summed sessions of one user
    const array FLD_NAMES = array(
        user_log::FLD_REQUESTS,
        user_log::FLD_UPDATED,
        user_log::FLD_REQUEST_TIME,
        user_log::FLD_UPDATE_TIME,
        self::FLD_SERVER_TIME
    );


    /*
     * object vars
     */

    public int $user_id = 0;        // the user whose sessions are summed
    public string $user_name = '';  // the name of the user to show it to the admin
    public int $requests = 0;       // the number of api requests
    public int $updated = 0;        // the number of api requests that have changed data
    public int $request_time = 0;   // the milliseconds of all api requests
    public int $update_time = 0;    // the milliseconds of the api requests that have changed data


    /*
     * construct and map
     */

    /**
     * map one row of the summed sessions of a user
     *
     * @param array $db_row with the user id, the user name and the sums directly from the database
     * @return bool true if the row has a user
     */
    function row_mapper(array $db_row): bool
    {
        $this->user_id = (int)($db_row[user_db::FLD_ID] ?? 0);
        $this->user_name = (string)($db_row[user_db::FLD_NAME] ?? '');
        $this->requests = (int)($db_row[user_log::FLD_REQUESTS] ?? 0);
        $this->updated = (int)($db_row[user_log::FLD_UPDATED] ?? 0);
        $this->request_time = (int)($db_row[user_log::FLD_REQUEST_TIME] ?? 0);
        $this->update_time = (int)($db_row[user_log::FLD_UPDATE_TIME] ?? 0);
        return $this->user_id > 0;
    }


    /*
     * api
     */

    /**
     * @return array the summed sessions of the user for the api json message to the admin dashboard
     */
    function api_json_array(): array
    {
        $vars = [];
        $vars[json_fields::ID] = $this->user_id;
        $vars[json_fields::NAME] = $this->user_name;
        $vars[json_fields::REQUESTS] = $this->requests;
        $vars[json_fields::UPDATES] = $this->updated;
        $vars[json_fields::REQUEST_MS] = $this->request_time;
        $vars[json_fields::UPDATE_MS] = $this->update_time;
        return $vars;
    }

}
