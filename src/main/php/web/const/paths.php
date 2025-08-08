<?php

/*

    web/const/paths.php - set the path const for the frontend php scripts
    -------------------

    the paths for the backend php scripts are in main/php/cfg/const/paths.php
    the paths for the resources are in main/php/cfg/const/files.php or test/php/const/files.php


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

namespace html\const;

class paths
{

    // path of the const and classes that are shared between the backend and the html frontend
    const SHARED = PHP_PATH . 'shared' . DIRECTORY_SEPARATOR;
    const SHARED_TYPES = self::SHARED . 'types' . DIRECTORY_SEPARATOR;

    // path of the pure html frontend objects
    const WEB = PHP_PATH . 'web' . DIRECTORY_SEPARATOR;

    const COMPONENT = self::WEB . 'component' . DIRECTORY_SEPARATOR;
    const SHEET = self::COMPONENT . 'sheet' . DIRECTORY_SEPARATOR;
    const FORM = self::COMPONENT . 'form' . DIRECTORY_SEPARATOR;

    const CONST = self::WEB . 'const' . DIRECTORY_SEPARATOR;
    const ELEMENT = self::WEB . 'element' . DIRECTORY_SEPARATOR;
    const FIGURE = self::WEB . 'figure' . DIRECTORY_SEPARATOR;
    const FORMULA = self::WEB . 'formula' . DIRECTORY_SEPARATOR;
    const GROUP = self::WEB . 'group' . DIRECTORY_SEPARATOR;
    const HELPER = self::WEB . 'helper' . DIRECTORY_SEPARATOR;
    const HIST = self::WEB . 'hist' . DIRECTORY_SEPARATOR;
    const HTML = self::WEB . 'html' . DIRECTORY_SEPARATOR;
    const LOG = self::WEB . 'log' . DIRECTORY_SEPARATOR;
    const PHRASE = self::WEB . 'phrase' . DIRECTORY_SEPARATOR;
    const REF = self::WEB . 'ref' . DIRECTORY_SEPARATOR;
    const RESULT = self::WEB . 'result' . DIRECTORY_SEPARATOR;
    const SANDBOX = self::WEB . 'sandbox' . DIRECTORY_SEPARATOR;
    const SYSTEM = self::WEB . 'system' . DIRECTORY_SEPARATOR;
    const TYPES = self::WEB . 'types' . DIRECTORY_SEPARATOR;
    const USER = self::WEB . 'user' . DIRECTORY_SEPARATOR;
    const VALUE = self::WEB . 'value' . DIRECTORY_SEPARATOR;
    const VERB = self::WEB . 'verb' . DIRECTORY_SEPARATOR;
    const VIEW = self::WEB . 'view' . DIRECTORY_SEPARATOR;
    const WORD = self::WEB . 'word' . DIRECTORY_SEPARATOR;

}
