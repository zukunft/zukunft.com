<?php

/*

    model/system/sys_log_level.php - system ENUM definition for the log level
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

    Copyright (c) 1995-2023 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\cfg\system;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_SYSTEM . 'BasicEnum.php';

use ReflectionException;

class sys_log_level extends BasicEnum
{
    // TODO Prio 2 use shared sys_log_types
    const int UNDEFINED = 0;
    const int INFO = 1;
    const int REJECT = 2;
    const int WARNING = 3;
    const int ERROR = 4;
    const int FATAL = 5;

    /**
     * @throws ReflectionException
     */
    protected static function get_description($value): string {
        $result = parent::getDescription($value);

        switch ($value) {

            // system log
            case sys_log_level::REJECT:
                $result = 'Reject';
                break;
            case sys_log_level::WARNING:
                $result = 'Warning';
                break;
            case sys_log_level::ERROR:
                $result = 'Error';
                break;
            case sys_log_level::FATAL:
                $result = 'FATAL ERROR';
                break;
        }

        return $result;
    }
}

