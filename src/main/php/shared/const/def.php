<?php

/*

    shared/const/def.php - general system definitions used in frontend and backend
    --------------------


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

namespace Zukunft\ZukunftCom\main\php\shared\const;


class def
{

    /*
     * fallback
     */

    // configuration values used as fallback if the value is missing in the system configuration
    const int FALLBACK_DB_PAGE_ROWS = 20; // the number of database rows that should be loaded at once
    const string ENCODING = 'utf-8'; // the default encoding for the backend
    const string FILE_PHP = '.php'; // the file extension for the code scripts

}
