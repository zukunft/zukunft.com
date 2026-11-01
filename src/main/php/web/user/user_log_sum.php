<?php

/*

    web/user/user_log_sum.php - the summed user log sessions of one user for the admin dashboard
    -------------------------

    $usr_log_sum is the suggested var name


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

namespace Zukunft\ZukunftCom\main\php\web\user;

use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once html_paths::HTML . 'html_base.php';
include_once html_paths::SHARED_ENUM . 'messages.php';
include_once html_paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\web\html\html_base;

class user_log_sum
{

    /*
     * object vars
     */

    public int $user_id = 0;        // the user whose sessions are summed
    public string $user_name = '';  // the name of the user
    public int $requests = 0;       // the number of api requests
    public int $updates = 0;        // the number of api requests that have changed data
    public int $request_ms = 0;     // the milliseconds of all api requests
    public int $update_ms = 0;      // the milliseconds of the api requests that have changed data


    /*
     * set and get
     */

    /**
     * set the vars of the summed sessions based on the api json array
     * @param array $json_array an api json message
     * @return bool true if the summed sessions have a user
     */
    function api_mapper(array $json_array): bool
    {
        $this->user_id = (int)($json_array[json_fields::ID] ?? 0);
        $this->user_name = (string)($json_array[json_fields::NAME] ?? '');
        $this->requests = (int)($json_array[json_fields::REQUESTS] ?? 0);
        $this->updates = (int)($json_array[json_fields::UPDATES] ?? 0);
        $this->request_ms = (int)($json_array[json_fields::REQUEST_MS] ?? 0);
        $this->update_ms = (int)($json_array[json_fields::UPDATE_MS] ?? 0);
        return $this->user_id > 0;
    }


    /*
     * info
     */

    /**
     * @return int the server time of the user, which decides the order of the users
     */
    function server_time(): int
    {
        return $this->request_ms + $this->update_ms;
    }

    /**
     * @param array $limits the thresholds as returned by user_log_sum_list::critical_limits
     * @return bool true if the user has reached at least one threshold
     */
    function is_critical(array $limits): bool
    {
        return ($this->requests >= $limits[json_fields::REQUESTS]
            or $this->updates >= $limits[json_fields::UPDATES]
            or $this->request_ms >= $limits[json_fields::REQUEST_MS]
            or $this->update_ms >= $limits[json_fields::UPDATE_MS]);
    }


    /*
     * display
     */

    /**
     * @return string the html table row of the summed sessions of the user
     */
    function display(): string
    {
        $html = new html_base();
        $row = $html->td($html->esc($this->user_name));
        $row .= $html->td((string)$this->requests);
        $row .= $html->td((string)$this->updates);
        $row .= $html->td((string)$this->request_ms);
        $row .= $html->td((string)$this->update_ms);
        return $html->tr($row);
    }

    /**
     * @return string the html table header row of the summed sessions
     */
    function header(): string
    {
        global $mtr;
        $html = new html_base();
        $row = $html->th($mtr->txt(msg_id::ADMIN_HEAD_USER_NAME));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_REQUESTS));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_UPDATES));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_REQUEST_MS));
        $row .= $html->th($mtr->txt(msg_id::ADMIN_HEAD_UPDATE_MS));
        return $html->tr($row);
    }

}
