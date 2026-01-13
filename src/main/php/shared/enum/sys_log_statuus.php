<?php

/*

    shared/enum/sys_log_statuus.php - enum of all possible log statuus
    -------------------------------


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

enum sys_log_statuus: string
{

    // list of all possible log statuus
    const string OPEN = "new";
    const int OPEN_ID = 1;
    const string OPEN_NAME = "new";
    const string OPEN_COM = "the error has just being logged and no one has yet looked at it";
    const string ASSIGNED = "assigned";
    const string RESOLVED = "resolved";
    const string CLOSED = "closed";

}