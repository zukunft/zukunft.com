<?php

/*

    shared/results.php - results used by the system for testing only in the backend
    ------------------


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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg\result;

class results
{

    // a list of fixed values that are used for system tests
    // *_ID is the group id of the value
    // *_FORM is the default formatted value
    CONST TV_INT = 123456;
    CONST TV_FLOAT = 12.3456;
    CONST TV_PCT = 0.01234;
    CONST TV_INCREASE_LONG = '0.0078718332961637'; // the increase of the swiss inhabitants from 2019 to 2020

}
