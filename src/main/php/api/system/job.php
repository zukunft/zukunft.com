<?php

/*

    api/system/job.php - the simple object to create a batch job json for the frontend API
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

namespace api\system;

include_once MODEL_HELPER_PATH . 'db_object.php';
include_once MODEL_USER_PATH . 'user.php';

use cfg\db_object_seq_id;
use cfg\user;
use DateTime;

class job
{

    /*
     * const for the api
     */

    const API_NAME = 'job';


    // field names used for JSON creation
    public int $id;
    public string $request_time;
    public string $start_time;
    public string $end_time;
    public string $user;
    public int $type_id;
    public string $status;
    public int $priority;

    function __construct(user $usr)
    {
        $this->set_user($usr);
        $this->id = 0;
        $this->request_time = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->type_id = 0;
        $this->status = '';
        $this->priority = 0;
    }

    /**
     * set the user of the phrase
     *
     * @param user $usr the person who wants to access the phrase
     * @return void
     */
    function set_user(user $usr): void
    {
        $this->user = $usr->name;
    }

    /**
     * just used for unit testing
     * @return string the frontend API JSON string
     */
    function get_json(): string
    {
        return json_encode($this);
    }

}
