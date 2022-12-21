<?php

/*

    api/system/batch_job.php - the simple object to create a batch job json for the frontend API
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

use DateTime;

class batch_job_api
{

    // field names used for JSON creation
    public int $id;
    public ?DateTime $request_time;
    public ?DateTime $start_time;
    public ?DateTime $end_time;
    public string $user;
    public string $type;
    public string $status;
    public string $priority;

    function __construct()
    {
        $this->id = 0;
        $this->request_time = null;
        $this->start_time = null;
        $this->end_time = null;
        $this->user = '';
        $this->type = '';
        $this->status = '';
        $this->priority = '';
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
