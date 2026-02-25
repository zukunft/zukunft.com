<?php

/*

    shared/enum/sys_log_levels.php - enum of all possible system log types
    -----------------------------

    TODO Prio 2 rename to sys_log_level


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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

use ReflectionException;

enum sys_log_levels: string
{

    // list of all possible log statuum
    const string UNDEFINED = "undefined";
    const string DEBUG = "log_debug";
    const int DEBUG_ID = 2;
    const string DEBUG_NAME = "Debug";
    const string DEBUG_COM = "Additional information only message that can be switched on upon request for more debugging";
    const string INFO = "log_info";
    const int INFO_ID = 4;
    const string INFO_NAME = "Info";
    const string INFO_COM = "Information only message for debugging and execution time details";
    const string WARNING = "log_warning";
    const int WARNING_ID = 6;
    const string WARNING_NAME = "Warning";
    const string WARNING_COM = "if a message has been shown to the user";
    const string ERROR = "log_error";
    const int ERROR_ID = 8;
    const string ERROR_NAME = "Error";
    const string ERROR_COM = "if the process has not been completed";
    const string FATAL = "log_fatal";
    const int FATAL_ID = 10;

}