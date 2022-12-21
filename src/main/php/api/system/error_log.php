<?php

/*

    api/system/error_log.php - the simple object to create a json for the frontend API
    ------------------------

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

namespace api;

use db_cl;
use html\api;
use html\html_base;
use sys_log_status;
use user;

class system_error_log_api
{

    // field names used for JSON creation
    public int $id;
    public string $time;
    public string $user;
    public string $text;
    public string $trace;
    public string $prg_part;
    public string $owner;
    public string $status;

    function __construct()
    {
        $this->id = 0;
        $this->time = '';
        $this->user = '';
        $this->text = '';
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
        $html = new html_base();
        $row_text = $html->td($this->time);
        $row_text .= $html->td($this->user);
        $row_text .= $html->td($this->text);
        $row_text .= $html->td($this->trace);
        $row_text .= $html->td($this->prg_part);
        $row_text .= $html->td($this->owner);
        $row_text .= $html->td($this->status);
        if ($usr != null) {
            if ($usr->is_admin()) {
                $par_status = api::PAR_LOG_STATUS. '=' . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED);
                $url = $html->url(api::ERROR_UPDATE, $this->id, $back, '', $par_status);
                $row_text .= $html->td($html->ref($url, 'close'));
            }
        }

        return $html->tr($row_text);
    }

}
