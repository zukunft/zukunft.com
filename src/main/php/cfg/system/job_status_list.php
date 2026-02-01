<?php

/*

    model/system/job_status_list.php - list of predefined system batch jobs
    ------------------------------------

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

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_HELPER . 'type_list.php';
include_once paths::MODEL_HELPER . 'type_object.php';
include_once paths::SHARED_TYPES . 'job_statuus.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuus;

class job_status_list extends type_list
{
    // list of the predefined system batch jobs
    const string VALUE_UPDATE = "value_update";

    /**
     * adding the job type used for unit tests to a dummy list
     *  TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(job_statuus::STATUS_NEW, job_statuus::STATUS_NEW, '', 2);
        $this->add($type);
        $type = new type_object(job_statuus::STATUS_DONE, job_statuus::STATUS_NEW, '', 11);
        $this->add($type);
    }

    /**
     * return the database id of the default job type
     */
    function default_id(): int
    {
        return parent::id(job_statuus::STATUS_NEW);
    }

}