<?php

/*

    system_error_log_dsp.php - the simple export object to create a json for the frontend API
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

class system_error_log_dsp
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
     * @return false|string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

    function get_html(user $usr, string $back): string
    {
        $result = '<tr>';
        $result .= '<td>' . $this->time . '</td>';
        $result .= '<td>' . $this->user . '</td>';
        $result .= '<td>' . $this->text . '</td>';
        $result .= '<td>' . $this->trace . '</td>';
        $result .= '<td>' . $this->prg_part . '</td>';
        $result .= '<td>' . $this->owner . '</td>';
        $result .= '<td>' . $this->status . '</td>';
        if ($usr->is_admin()) {
            $result .= '<td><a href="/http/error_update.php?id=' . $this->id .
                '&status=' . cl(db_cl::LOG_STATUS, sys_log_status::CLOSED) .
                '&back=' . $back . '">close</a></td>';
        }

        $result .= '</tr>';
        return $result;
    }

}
