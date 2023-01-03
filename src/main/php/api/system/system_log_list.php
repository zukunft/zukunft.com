<?php

/*

    api/system/system_log_list.php - the simple export object to create a json for the frontend API
    ------------------------------

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

use api_message;
use user;

class system_log_list_api extends api_message
{

    // field names used for JSON creation
    public ?array $system_log = null;      // a list of system error objects

    function __construct(?user $usr = null)
    {
        parent::__construct();
        $this->type = api_message::SYS_LOG;
        $this->system_log = null;
        if ($usr != null) {
            $this->user_id = $usr->id;
            $this->user = $usr->name;
        }
    }

    /**
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

}
