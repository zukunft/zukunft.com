<?php

/*

    api/system/sys_log_list.php - the simple export object to create a json for the frontend API
    ---------------------------

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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace controller\system;

include_once API_OBJECT_PATH . 'api_message.php';

use controller\api_message_old;
use cfg\db\sql_db;
use cfg\user\user;
use JsonSerializable;
use shared\api;

class sys_log_list extends api_message_old implements JsonSerializable
{

    // field names used for JSON creation
    public ?array $sys_log_list = null;      // a list of system error objects

    function __construct(sql_db $db_con, ?user $usr = null)
    {
        parent::__construct($db_con, self::class, $usr);
        $this->sys_log_list = null;
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
