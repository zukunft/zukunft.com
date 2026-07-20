<?php

/*

    model/system/job_exe.php - the common interface of the non-interactive backend job handlers
    ------------------------

    each backend job type (e.g. the proactive cache refresh sweep and the database cleanup) is
    implemented in its own class that implements this interface, so that the job_runner and job->exe
    can dispatch a job to its handler by the job type code id (see docs/llm/pending.md)


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

interface job_exe
{

    /**
     * @return string the code id of the job type this handler executes e.g. job_types::DB_CLEANUP
     */
    public function type_code_id(): string;

    /**
     * execute the job as a system process and report any problem on the message
     * the handler is a global maintenance sweep, so it does not depend on a single queued job row
     * @param user_message $msg the message buffer that collects any problem for the log
     * @return int the number of items the handler has processed (e.g. invalidated or deleted)
     */
    public function execute(user_message $msg): int;

}
