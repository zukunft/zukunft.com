<?php

/*

    cfg/system/system_time_type.php - the areas of execution times
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace cfg;

class system_time_type extends type_object
{

    /*
     * code links
     */

    // list of the monitored areas
    const DEFAULT = "not_specified";
    const DB_WRITE = "db_write";
    const DB_READ = "db_read";


    /*
     * database link
     */

    // comments used for the database creation
    const TBL_COMMENT = 'to define the execution time groups';
    const FLD_ID = 'system_time_type_id'; // name of the id field as const for other const

}
