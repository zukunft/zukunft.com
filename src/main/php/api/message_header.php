<?php

/*

    api_message_header.php - the JSON header object for the frontend API
    -----------------------

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

class api_message
{
    // the message types for fast format detection
    const SYS_LOG = 'sys_log';

    // field names used for JSON creation
    public string $type;      // defines the message formal (just used for testing and easy debugging)
    public int $user_id;      // to double-check to the session user
    public string $user;      // for fast debugging
    public string $version;   // to prevent communication error due to incompatible program versions
    public string $timestamp; // for automatic delay problem detection

    function __construct()
    {
        global $usr;
        $this->type = '';
        if ($usr != null) {
            $this->user_id = $usr->id;
            $this->user = $usr->name;
        }
        $this->version = PRG_VERSION;
        // TODO make testing with timestamp useful
        //$this->timestamp = (new DateTime())->format('Y-m-d H:i:s');
    }

    /**
     * @return false|string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

    function get_html_header(string $title): string
    {
        if ($title == null) {
            $title = 'api message';
        } elseif ($title == '') {
            $title = 'api message';
        }
        $html = new html_base();
        return $html->header($title);
    }

    function get_html_footer(): string
    {
        $html = new html_base();
        return $html->footer();
    }

}
