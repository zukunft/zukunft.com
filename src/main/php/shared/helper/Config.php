<?php

/*

    shared/helper/Config.php - const fallback configuration settings
    ------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared\helper;

class Config
{

    // fallback config values e.g. if the backend connection is lost
    const int ROW_LIMIT = 20;
    const string DEFAULT_DEC_POINT = ".";
    const int DEFAULT_PERCENT_DECIMALS = 2;
    const string DEFAULT_THOUSAND_SEP = "'";
    const string DEFAULT_DATE_TIME_FORMAT = 'd-m-Y H:i';

    // number of entries initial to show in a named list
    const int LIMIT_NAME_LIST = 10;


}
