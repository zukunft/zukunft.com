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

namespace api\log;

include_once API_PATH . 'api_message.php';

use api\api_message;
use cfg\sql_db;
use cfg\user;
use controller\controller;
use JsonSerializable;

class system_log_list extends api_message implements JsonSerializable
{

    // field names used for JSON creation
    public ?array $system_log = null;      // a list of system error objects

    function __construct(sql_db $db_con, ?user $usr = null)
    {
        parent::__construct($db_con, controller::API_BODY_SYS_LOG, $usr);
        $this->type = api_message::SYS_LOG;
        $this->system_log = null;
        if ($usr != null) {
            $this->user_id = $usr->id();
            $this->user = $usr->name;
        }
    }

    /**
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * @return array with the sandbox vars without empty values that are not needed
     * the message from the backend to the frontend does not need to include empty fields
     * the message from the frontend to the backend on the other side must include empty fields
     * to be able to unset fields in the backend
     */
    function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
