<?php

/*

    api/user/user_log.php - the common change log object for the frontend API
    ---------------------


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

include_once API_USER_PATH . 'user.php';
include_once API_SANDBOX_PATH . 'sandbox.php';

use api\sandbox\sandbox_api;
use api\user\user_api;
use DateTime;

class change_log_api extends sandbox_api
{


    /*
     * object vars
     */

    public ?user_api $usr = null;  // the user who has done the change
    public ?int $action_id;        // database id for the change type (add, change or del)
    public ?int $table_id;         // database id of the table used to get the name from the preloaded hash
    public ?int $field_id;         // database id of the table used to get the name from the preloaded hash
    public ?int $row_id;           // prime database key of the row that has been changed
    public DateTime $change_time;  // the time of the change

}
