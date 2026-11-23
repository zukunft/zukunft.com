<?php

/*

    model/system/job_status_list.php - list of predefined system batch jobs
    ------------------------------------

    $job_sta_lst is the suggested var name

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
include_once paths::SHARED_TYPES . 'job_statuum.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\types\job_statuum;

class job_status_list extends type_list
{
    // list of the predefined system batch jobs
    const string VALUE_UPDATE = "value_update";

    /**
     * fill the list of the job statuum for the unit tests
     */
    function load_dummy(): void
    {
        // from db_code_links/job_statuum.csv, so that e.g. the default status of a new job is found by its database id
        $this->load_from_csv_resource();
    }

    /**
     * return the database id of the default job type
     */
    function default_id(): int
    {
        return parent::id(job_statuum::STATUS_NEW);
    }

}