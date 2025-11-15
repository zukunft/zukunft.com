<?php

/*

    shared/const/files.php - resource file names used in backend and frontend
    ----------------------


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

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

class files
{

    /*
     * types and extensions
     */

    const string JSON = '.json';


    /*
     * path
     */

    const string RESOURCE_PATH = paths::MAIN . 'resources' . DIRECTORY_SEPARATOR;
    const string MESSAGE_PATH = self::RESOURCE_PATH . 'messages' . DIRECTORY_SEPARATOR;


    /*
     * system config
     */

    // the system views as a zukunft.com user import json
    const string SYSTEM_VIEWS_FILE = 'system_views' . self::JSON;
    const string SYSTEM_VIEWS = self::MESSAGE_PATH . self::SYSTEM_VIEWS_FILE;
    const string BASE_VIEWS_FILE = 'base_views' . self::JSON;
    const string BASE_VIEWS = self::MESSAGE_PATH . self::BASE_VIEWS_FILE;
    const string TRANSLATION_PATH = self::RESOURCE_PATH . 'translations' . DIRECTORY_SEPARATOR;

}
