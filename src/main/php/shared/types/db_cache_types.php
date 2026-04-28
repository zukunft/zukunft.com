<?php

/*

    shared/types/db_cache_types.php - ENUM of the used db_cache types
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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com
  
*/

namespace Zukunft\ZukunftCom\main\php\shared\types;

class db_cache_types
{

    // list of the db_cache types that have a coded functionality
    const string SYSTEM_CONFIG = "system_config";
    const int SYSTEM_CONFIG_ID = 1;
    const string SYSTEM_CONFIG_NAME = "system configuration";
    const string SYSTEM_CONFIG_COM = "the complete json of the system configuration";
    const string USER_CONFIG = "user_config";
    const string USER_FRONTEND_CONFIG = "user_ui_config";
    const string FRONTEND_CONFIG = "ui_config";
    const string PAGE_CACHE = "page_cache";
    const string UI_CACHE = "ui_cache";

}
