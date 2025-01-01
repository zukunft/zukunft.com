<?php

/*

    api/system/sys_log.php - the simple object to create a json for the frontend API
    ----------------------

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

namespace controller\system;

include_once API_USER_PATH . 'user.php';
include_once WEB_HTML_PATH . 'html_base.php';
include_once WEB_HTML_PATH . 'rest_ctrl.php';

use cfg\system\sys_log_status;
use cfg\user\user;
use html\rest_ctrl;
use html\html_base;

class sys_log
{

    CONST TV_TIME = '2023-01-03T20:59:59+0100'; // time for unit tests
    CONST TV_LOG_TEXT = 'the log text that describes the problem for the user or system admin';
    CONST TV_LOG_TRACE = 'the technical trace back description for debugging';
    CONST TV_FUNC_NAME = 'name of the function that has caused the exception';
    CONST TV_SOLVE_ID = 'code id of the suggested solver of the problem';
    CONST T2_TIME = '2023-01-03T21:45:01+0100'; // time for unit tests
    CONST T2_LOG_TEXT = 'the log 2 text that describes the problem for the user or system admin';
    CONST T2_LOG_TRACE = 'the technical trace 2 back description for debugging';
    CONST T2_FUNC_NAME = 'name 2 of the function that has caused the exception';
    CONST T2_SOLVE_ID = 'code id 2 of the suggested solver of the problem';

    // field names used for JSON creation
    public int $id;
    public string $time;
    public string $user;
    public string $text;
    public ?string $description;
    public string $trace;
    public ?string $prg_part;
    public string $owner;
    public string $status;

    function __construct()
    {
        $this->id = 0;
        $this->time = '';
        $this->user = '';
        $this->text = '';
        $this->description = '';
        $this->trace = '';
        $this->prg_part = '';
        $this->owner = '';
        $this->status = '';
    }

    /**
     * just used for unit testing
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

    function get_html(user $usr = null, string $back = ''): string
    {
        global $sys_log_sta_cac;

        $html = new html_base();
        $row_text = $html->td($this->time);
        $row_text .= $html->td($this->user);
        $row_text .= $html->td($this->text);
        $row_text .= $html->td($this->description);
        $row_text .= $html->td($this->trace);
        $row_text .= $html->td($this->prg_part);
        $row_text .= $html->td($this->owner);
        $row_text .= $html->td($this->status);
        if ($usr != null) {
            if ($usr->is_admin() or $usr->is_system()) {
                $par_status = rest_ctrl::PAR_LOG_STATUS. '=' . $sys_log_sta_cac->id(sys_log_status::CLOSED);
                $url = $html->url(rest_ctrl::ERROR_UPDATE, $this->id, $back, '', $par_status);
                $row_text .= $html->td($html->ref($url, 'close'));
            }
        }

        return $html->tr($row_text);
    }

}
