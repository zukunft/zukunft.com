<?php

/*

    model/system/sys_log_status_list.php - list of the system log statuus
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
include_once paths::MODEL_SYSTEM . 'sys_log_level.php';
include_once paths::MODEL_SYSTEM . 'sys_log_status.php';
include_once paths::DB . 'sql_db.php';
include_once paths::SHARED_ENUM . 'sys_log_statuus.php';

use Zukunft\ZukunftCom\main\php\cfg\helper\type_list;
use Zukunft\ZukunftCom\main\php\cfg\helper\type_object;
use Zukunft\ZukunftCom\main\php\shared\enum\sys_log_statuus;

class sys_log_status_list extends type_list
{

    /**
     * adding the system log statuus used for unit tests to the dummy list
     *  TODO Prio 3: load from csv
     */
    function load_dummy(): void
    {
        parent::load_dummy();
        $type = new type_object(
            sys_log_statuus::OPEN,
            sys_log_statuus::OPEN,
            sys_log_statuus::OPEN_COM,
            sys_log_statuus::OPEN_ID);
        $this->add($type);
        $type = new type_object(
            sys_log_statuus::ASSIGNED,
            sys_log_statuus::ASSIGNED,
            sys_log_statuus::ASSIGNED_COM,
            sys_log_statuus::ASSIGNED_ID);
        $this->add($type);
        $type = new type_object(
            sys_log_statuus::RESOLVED,
            sys_log_statuus::RESOLVED,
            sys_log_statuus::RESOLVED_COM,
            sys_log_statuus::RESOLVED_ID);
        $this->add($type);
        $type = new type_object(
            sys_log_statuus::CLOSED,
            sys_log_statuus::CLOSED,
            sys_log_statuus::CLOSED_COM,
            sys_log_statuus::CLOSED_ID);
        $this->add($type);
    }

    /**
     * return the database id of the default system log status
     */
    function default_id(): int
    {
        return parent::id(sys_log_statuus::OPEN);
    }

}