<?php

/*

    model/system/system_utils.php - system ENUM definition for the log level
    -----------------------------

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

namespace cfg;

use ReflectionException;

class sys_log_level extends BasicEnum
{
    const UNDEFINED = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const FATAL = 4;

    /**
     * @throws ReflectionException
     */
    protected static function get_description($value): string {
        $result = parent::getDescription($value);

        switch ($value) {

            // system log
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

