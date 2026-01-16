<?php

/*

    shared/types/job_statuus.php - ENUM of the used job statuus
    ----------------------------

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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class job_statuus
{

    // list of the job statuus that have a coded functionality
    const string STATUS_NEW = 'new'; // the job is not yet assigned to any calc engine
    const string STATUS_ASSIGNED = 'assigned'; // the job has been assigned to a calc engine
    const string STATUS_WORKING = 'working'; // the calc engine is reporting the progress
    const string STATUS_NOT_RESPONDING = 'not_responding'; // the calc engine is not reporting the progress
    const string STATUS_WAITING = 'waiting'; // the task is waiting for user input of other jobs
    const string STATUS_DONE = 'done'; // the task has been completed successfully
    const string STATUS_FAILED = 'failed'; // the task has been completed unsuccessful

    const int PRIO_HIGHEST = 1;
    const int PRIO_LOWEST = 10;

}
