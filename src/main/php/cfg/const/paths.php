<?php

/*

    model/const/paths.php - set the path const for the backend php scripts
    ---------------------

    the paths for the frontend php scripts are in main/php/web/const/paths.php
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

namespace Zukunft\ZukunftCom\main\php\cfg\const;

class paths
{

    // set all path for the program code here at once
    const string SRC = ROOT_PATH . 'src' . DIRECTORY_SEPARATOR;
    const string MAIN = self::SRC . 'main' . DIRECTORY_SEPARATOR;
    // recreation of the PHP for library use only
    const string PHP_LIB = self::MAIN . 'php' . DIRECTORY_SEPARATOR;
    // path of the main model objects for db saving, api feed and processing
    const string MODEL = self::PHP_LIB . 'cfg' . DIRECTORY_SEPARATOR;
    // the prefix for the code section for the documentation of the object overview
    const string MODEL_SECTION = '';
    const string DB = self::MODEL . 'db' . DIRECTORY_SEPARATOR;
    const string UTIL = self::PHP_LIB . 'utils' . DIRECTORY_SEPARATOR;
    const string SERVICE = self::PHP_LIB . 'service' . DIRECTORY_SEPARATOR;
    const string MODEL_IMPORT = self::MODEL . 'import' . DIRECTORY_SEPARATOR;
    const string EXPORT = self::MODEL . 'export' . DIRECTORY_SEPARATOR;
    const string SERVICE_MATH = self::SERVICE . 'math' . DIRECTORY_SEPARATOR;
    const string MODEL_CONST = self::MODEL . 'const' . DIRECTORY_SEPARATOR;
    const string MODEL_HELPER = self::MODEL . 'helper' . DIRECTORY_SEPARATOR;
    const string MODEL_SYSTEM = self::MODEL . 'system' . DIRECTORY_SEPARATOR;
    const string MODEL_LOG = self::MODEL . 'log' . DIRECTORY_SEPARATOR;
    const string MODEL_LOG_TEXT = self::MODEL . 'log_text' . DIRECTORY_SEPARATOR;
    const string MODEL_DB = self::MODEL . 'db' . DIRECTORY_SEPARATOR;
    const string MODEL_LANGUAGE = self::MODEL . 'language' . DIRECTORY_SEPARATOR;
    const string MODEL_USER = self::MODEL . 'user' . DIRECTORY_SEPARATOR;
    const string MODEL_SANDBOX = self::MODEL . 'sandbox' . DIRECTORY_SEPARATOR;
    const string MODEL_WORD = self::MODEL . 'word' . DIRECTORY_SEPARATOR;
    const string MODEL_PHRASE = self::MODEL . 'phrase' . DIRECTORY_SEPARATOR;
    const string MODEL_GROUP = self::MODEL . 'group' . DIRECTORY_SEPARATOR;
    const string MODEL_VERB = self::MODEL . 'verb' . DIRECTORY_SEPARATOR;
    const string MODEL_VALUE = self::MODEL . 'value' . DIRECTORY_SEPARATOR;
    const string MODEL_REF = self::MODEL . 'ref' . DIRECTORY_SEPARATOR;
    const string MODEL_ELEMENT = self::MODEL . 'element' . DIRECTORY_SEPARATOR;
    const string MODEL_FORMULA = self::MODEL . 'formula' . DIRECTORY_SEPARATOR;
    const string MODEL_RESULT = self::MODEL . 'result' . DIRECTORY_SEPARATOR;
    const string MODEL_VIEW = self::MODEL . 'view' . DIRECTORY_SEPARATOR;
    const string MODEL_COMPONENT = self::MODEL . 'component' . DIRECTORY_SEPARATOR;
    const string MODEL_SHEET = self::MODEL_COMPONENT . 'sheet' . DIRECTORY_SEPARATOR;

    // for the backend cache
    const string CACHE = ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR;

    const string SHARED = self::PHP_LIB . 'shared' . DIRECTORY_SEPARATOR;
    const string SHARED_SECTION = 'Shared';
    const string SHARED_CALC = self::SHARED . 'calc' . DIRECTORY_SEPARATOR;
    const string SHARED_CONST = self::SHARED . 'const' . DIRECTORY_SEPARATOR;
    const string SHARED_ENUM = self::SHARED . 'enum' . DIRECTORY_SEPARATOR;
    const string SHARED_HELPER = self::SHARED . 'helper' . DIRECTORY_SEPARATOR;
    const string SHARED_TYPES = self::SHARED . 'types' . DIRECTORY_SEPARATOR;

    const string API = ROOT_PATH . 'api' . DIRECTORY_SEPARATOR; // path of the api objects for the message creation to the frontend

    const string API_OBJECT = self::PHP_LIB . 'api' . DIRECTORY_SEPARATOR; // path of the api objects for the message creation to the frontend
    const string API_SANDBOX = self::API_OBJECT . 'sandbox' . DIRECTORY_SEPARATOR;
    const string API_SYSTEM = self::API_OBJECT . 'system' . DIRECTORY_SEPARATOR;
    const string API_USER = self::API_OBJECT . 'user' . DIRECTORY_SEPARATOR;
    const string API_LOG = self::API_OBJECT . 'log' . DIRECTORY_SEPARATOR;
    const string API_LANGUAGE = self::API_OBJECT . 'language' . DIRECTORY_SEPARATOR;
    const string API_WORD = self::API_OBJECT . 'word' . DIRECTORY_SEPARATOR;
    const string API_PHRASE = self::API_OBJECT . 'phrase' . DIRECTORY_SEPARATOR;
    const string API_VERB = self::API_OBJECT . 'verb' . DIRECTORY_SEPARATOR;
    const string API_VALUE = self::API_OBJECT . 'value' . DIRECTORY_SEPARATOR;
    const string API_FORMULA = self::API_OBJECT . 'formula' . DIRECTORY_SEPARATOR;
    const string API_RESULT = self::API_OBJECT . 'result' . DIRECTORY_SEPARATOR;
    const string API_VIEW = self::API_OBJECT . 'view' . DIRECTORY_SEPARATOR;
    const string API_COMPONENT = self::API_OBJECT . 'component' . DIRECTORY_SEPARATOR;
    const string API_REF = self::API_OBJECT . 'ref' . DIRECTORY_SEPARATOR;

    // path of the pure html frontend objects
    const string WEB = self::PHP_LIB . 'web' . DIRECTORY_SEPARATOR;
    const string WEB_SECTION = 'Ui';
    // only used for initial loading
    const string WEB_CONST = self::WEB . 'const' . DIRECTORY_SEPARATOR;

    // resource paths
    const string RES = self::MAIN . 'resources' . DIRECTORY_SEPARATOR;
    const string IMAGE_RES = self::RES . 'images' . DIRECTORY_SEPARATOR;
    const string DB_RES_SUB = 'db' . DIRECTORY_SEPARATOR;
    const string DB_SETUP_SUB = 'setup' . DIRECTORY_SEPARATOR;

    // resource paths used for testing to avoid local paths in the test resources
    const string REL_ROOT = DIRECTORY_SEPARATOR;
    const string REL_SRC = self::REL_ROOT . 'src' . DIRECTORY_SEPARATOR;
    const string REL_MAIN = self::REL_SRC . 'main' . DIRECTORY_SEPARATOR;
    const string REL_RES = self::REL_MAIN . 'resources' . DIRECTORY_SEPARATOR;
    const string REL_IMAGE = self::REL_RES . 'images' . DIRECTORY_SEPARATOR;

    // external libraries
    const string EXT_LIB = DIRECTORY_SEPARATOR . 'external_lib' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_BS = self::EXT_LIB . 'bootstrap' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_BS_CSS = self::EXT_LIB_BS . 'css' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_BS_JS = self::EXT_LIB_BS . 'js' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_FONT = self::EXT_LIB . 'fontawesome' . DIRECTORY_SEPARATOR;
    const string EXT_LIB_FONT_CSS = self::EXT_LIB_FONT . 'css' . DIRECTORY_SEPARATOR;

}
