<?php

/*

    shared/enum/sys_log_statuum.php - enum of all possible log statuum
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

enum sys_log_statuum: string
{

    // list of all possible log statuum
    const string OPEN = "new";
    const int OPEN_ID = 1;
    const string OPEN_NAME = "new";
    const string OPEN_COM = "the error has just being logged and no one has yet looked at it";
    const string ASSIGNED = "assigned";
    const int ASSIGNED_ID = 2;
    const string ASSIGNED_COM = "A developer is looking at the error.";
    const string RESOLVED = "resolved";
    const int RESOLVED_ID = 3;
    const string RESOLVED_COM = "the error is supposed to be corrected";
    const string CLOSED = "closed";
    const int CLOSED_ID = 4;
    const string CLOSED_COM = "a second person (other than the developer) has confirmed that the problem is solved.";
    const string REJECTED = "rejected";
    const string REJECTED_COM = "the assignment has been rejected and the issue needs to be assigned again";

}