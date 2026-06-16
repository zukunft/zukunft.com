<?php

/*

    shared/enum/change_actions.php - enum of all change types allowed by a user
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\enum;

enum change_actions: string
{

    // the basic change types that are logged
    const string SHOW = 'show';
    const string ADD = 'add';
    const int ADD_ID = 1;
    const string ADD_NAME = 'add';
    const string ADD_COM = '';
    const string UPDATE = 'update';
    const string DELETE = 'del';
    const string SUB = 'sub'; // a sub part of another view?
    const string STEP = 'step'; // a process step that does only indirect updates an object e.g. the login form
    const string SEARCH = 'search'; // a form for interactive and complex object selection

}