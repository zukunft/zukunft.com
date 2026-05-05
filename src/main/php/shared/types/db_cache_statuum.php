<?php

/*

    shared/types/db_cache_statuum.php - ENUM of the used db_cache statuum
    ---------------------------------

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

namespace Zukunft\ZukunftCom\main\php\shared\types;

class db_cache_statuum
{

    // list of the db_cache status entries that have a coded functionality
    const string CLEAN = "clean";
    const int CLEAN_ID = 1;
    const string CLEAN_NAME = "clean";
    const string CLEAN_COM = "no reason known why the cache should NOT be used";
    const string DIRTY = "dirty";
    const string OUTDATED = "outdated";
    const string UPDATING = "updating";
    const string UNUSED = "unused";

}
