<?php

/*

    shared/words.php - predefined words used for in the backend and frontend as code id
    ----------------

    all words must always be owned by an administrator so that the standard cannot be renamed


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

namespace shared;

class words
{

    /*
     * config
     */

    // words used in the frontend and backend for the system configuration
    // keyword to select the system configuration
    const SYSTEM_CONFIG = 'system configuration';
    const SYSTEM_CONFIG_ID = 73;
    const MASTER_POD_NAME = 'zukunft.com';
    const MASTER_POD_NAME_ID = 314;

    // e.g. an instance of zukunft.com
    const POD = 'pod';
    const POD_ID = 298;

    // e.g. an instance of zukunft.com
    const URL = 'url';
    const URL_ID = 309;
    // e.g. the launch date of the first beta version of zukunft.com
    const LAUNCH = 'launch';
    const LAUNCH_ID = 309;
    // e.g. an instance of zukunft.com
    const ROW = 'row';
    const LIMIT = 'limit';
    const WORD = 'word';
    const CHANGES = 'changes';
    const PERCENT = 'percent';
    const DECIMAL = 'decimal';
    // e.g. the geolocation of the development of zukunft.com
    const POINT = 'point';
    const POINT_ID = 243;

}
