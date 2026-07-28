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

namespace Zukunft\ZukunftCom\main\php\web\const;

// until the frontend backend split is done load also the backend path const class,
// because the backend code that the frontend still includes uses it internally
include_once PHP_PATH . 'cfg' . DIRECTORY_SEPARATOR . 'const' . DIRECTORY_SEPARATOR . 'paths.php';

class paths
{

    // path of the const and classes that are shared between the backend and the html frontend
    const string SHARED = PHP_PATH . 'shared' . DIRECTORY_SEPARATOR;
    const string SHARED_CALC = self::SHARED . 'calc' . DIRECTORY_SEPARATOR;
    const string SHARED_CONST = self::SHARED . 'const' . DIRECTORY_SEPARATOR;
    const string SHARED_CONST_FIELDS = self::SHARED_CONST . 'fields' . DIRECTORY_SEPARATOR;
    const string SHARED_ENUM = self::SHARED . 'enum' . DIRECTORY_SEPARATOR;
    const string SHARED_HELPER = self::SHARED . 'helper' . DIRECTORY_SEPARATOR;
    const string SHARED_TYPES = self::SHARED . 'types' . DIRECTORY_SEPARATOR;

    // path of the backend code that the frontend still needs until the frontend backend split is done
    // (copies of the same named const in main/php/cfg/const/paths.php)
    const string MODEL = PHP_PATH . 'cfg' . DIRECTORY_SEPARATOR;
    const string API_OBJECT = PHP_PATH . 'api' . DIRECTORY_SEPARATOR;
    const string DB = self::MODEL . 'db' . DIRECTORY_SEPARATOR;
    const string MODEL_CONST = self::MODEL . 'const' . DIRECTORY_SEPARATOR;
    const string MODEL_FORMULA = self::MODEL . 'formula' . DIRECTORY_SEPARATOR;
    const string MODEL_GROUP = self::MODEL . 'group' . DIRECTORY_SEPARATOR;
    const string MODEL_HELPER = self::MODEL . 'helper' . DIRECTORY_SEPARATOR;
    const string MODEL_IMPORT = self::MODEL . 'import' . DIRECTORY_SEPARATOR;
    const string MODEL_LOG = self::MODEL . 'log' . DIRECTORY_SEPARATOR;
    const string MODEL_LOG_TEXT = self::MODEL . 'log_text' . DIRECTORY_SEPARATOR;
    const string MODEL_PHRASE = self::MODEL . 'phrase' . DIRECTORY_SEPARATOR;
    const string MODEL_REF = self::MODEL . 'ref' . DIRECTORY_SEPARATOR;
    const string MODEL_SANDBOX = self::MODEL . 'sandbox' . DIRECTORY_SEPARATOR;
    const string MODEL_SYSTEM = self::MODEL . 'system' . DIRECTORY_SEPARATOR;
    const string MODEL_USER = self::MODEL . 'user' . DIRECTORY_SEPARATOR;
    const string MODEL_VERB = self::MODEL . 'verb' . DIRECTORY_SEPARATOR;
    const string MODEL_VIEW = self::MODEL . 'view' . DIRECTORY_SEPARATOR;
    const string MODEL_WORD = self::MODEL . 'word' . DIRECTORY_SEPARATOR;

    // relative resource paths for the html pages
    // (copies of the same named const in main/php/cfg/const/paths.php)
    const string REL_RES = DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'main' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
    const string REL_IMAGE = self::REL_RES . 'images' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_BS = DIRECTORY_SEPARATOR . 'external_lib' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_BS_JS = self::EXT_LIB_BS . 'js' . DIRECTORY_SEPARATOR;

    // path of the pure html frontend objects
    // (the leading backslash selects the global const of the entry scripts, not this class const)
    const string WEB = \WEB;

    // path of the html frontend entry scripts and the deprecated predecessor scripts
    const string HTTP = ROOT_PATH . 'http' . DIRECTORY_SEPARATOR;
    const string HTTP_OLD = ROOT_PATH . 'http_old' . DIRECTORY_SEPARATOR;

    const string COMPONENT = self::WEB . 'component' . DIRECTORY_SEPARATOR;
    const string SHEET = self::COMPONENT . 'sheet' . DIRECTORY_SEPARATOR;
    const string EXECUTE = self::COMPONENT . 'execute' . DIRECTORY_SEPARATOR;

    const string CONST = self::WEB . 'const' . DIRECTORY_SEPARATOR;
    const string ELEMENT = self::WEB . 'element' . DIRECTORY_SEPARATOR;
    const string FIGURE = self::WEB . 'figure' . DIRECTORY_SEPARATOR;
    const string FORMULA = self::WEB . 'formula' . DIRECTORY_SEPARATOR;
    const string GROUP = self::WEB . 'group' . DIRECTORY_SEPARATOR;
    const string HELPER = self::WEB . 'helper' . DIRECTORY_SEPARATOR;
    const string HIST = self::WEB . 'hist' . DIRECTORY_SEPARATOR;
    const string HTML = self::WEB . 'html' . DIRECTORY_SEPARATOR;
    const string LOG = self::WEB . 'log' . DIRECTORY_SEPARATOR;
    const string LOG_TEXT = self::WEB . 'log_text' . DIRECTORY_SEPARATOR;
    const string PHRASE = self::WEB . 'phrase' . DIRECTORY_SEPARATOR;
    const string REF = self::WEB . 'ref' . DIRECTORY_SEPARATOR;
    const string RESULT = self::WEB . 'result' . DIRECTORY_SEPARATOR;
    const string SANDBOX = self::WEB . 'sandbox' . DIRECTORY_SEPARATOR;
    const string SYSTEM = self::WEB . 'system' . DIRECTORY_SEPARATOR;
    const string TYPES = self::WEB . 'types' . DIRECTORY_SEPARATOR;
    const string USER = self::WEB . 'user' . DIRECTORY_SEPARATOR;
    const string VALUE = self::WEB . 'value' . DIRECTORY_SEPARATOR;
    const string VERB = self::WEB . 'verb' . DIRECTORY_SEPARATOR;
    const string VIEW = self::WEB . 'view' . DIRECTORY_SEPARATOR;
    const string WORD = self::WEB . 'word' . DIRECTORY_SEPARATOR;

}
