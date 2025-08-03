<?php

/*

    model/const/paths.php - set the path const for the test php scripts
    ---------------------

    the paths for the test resources are in test/php/const/files.php
    the paths for the backend php scripts are in main/php/cfg/const/paths.php
    the paths for the frontend php scripts are in main/php/web/const/paths.php
    the paths for the resources are in main/php/cfg/const/files.php


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

namespace const;

class paths
{

    const UTILS = TEST_PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;

    // path for unit tests
    const UNIT = TEST_PHP_PATH . 'unit' . DIRECTORY_SEPARATOR;               // for unit tests
    const UNIT_READ = TEST_PHP_PATH . 'unit_read' . DIRECTORY_SEPARATOR;     // for the unit tests with database read only
    const UNIT_DSP = self::UNIT . 'html' . DIRECTORY_SEPARATOR;           // for the unit tests that create HTML code
    const UNIT_HTML = TEST_PHP_PATH . 'unit_display' . DIRECTORY_SEPARATOR; // for the unit tests that create HTML code
    const UNIT_UI = TEST_PHP_PATH . 'unit_ui' . DIRECTORY_SEPARATOR;        // for the unit tests that create JSON messages for the frontend
    const UNIT_WRITE = TEST_PHP_PATH . 'unit_write' . DIRECTORY_SEPARATOR;  // for the unit tests that save to database (and cleanup the test data after completion)
    const UNIT_INT = TEST_PHP_PATH . 'integration' . DIRECTORY_SEPARATOR;   // for integration tests
    const DEV = TEST_PHP_PATH . 'dev' . DIRECTORY_SEPARATOR;                // for test still in development


    // main path for the test resources
    const RESOURCE = TEST_PATH . 'resources' . DIRECTORY_SEPARATOR;

    // path for resources to test the api
    const API_RES = self::RESOURCE . 'api' . DIRECTORY_SEPARATOR;
    const API_SYSTEM_RES = self::API_RES . 'system' . DIRECTORY_SEPARATOR;
    const API_SYS_LOG_RES = self::API_RES . 'sys_log_list' . DIRECTORY_SEPARATOR;
    const DB_FORMULA = self::DB . self::FORMULA;
    const DB_RES = self::RESOURCE . self::DB;
    const DB_RES_FORMULA = self::DB_RES . self::FORMULA;
    const DB_USER = self::DB_RES . 'user' . DIRECTORY_SEPARATOR;
    const EXPORT = self::RESOURCE . 'export' . DIRECTORY_SEPARATOR;
    const EXPORT_WORD = self::EXPORT . 'word' . DIRECTORY_SEPARATOR;
    const IMPORT = self::RESOURCE . 'import' . DIRECTORY_SEPARATOR;
    const IMPORT_UNIT = self::IMPORT . 'unit_tests' . DIRECTORY_SEPARATOR;
    const IMPORT_WIKIPEDIA = self::IMPORT . 'wikipedia' . DIRECTORY_SEPARATOR;
    const UNIT_RES = self::RESOURCE . 'unit' . DIRECTORY_SEPARATOR;
    const SYSTEM = self::UNIT_RES . 'system' . DIRECTORY_SEPARATOR;

    // path for resources to test the frontend
    const WEB_RES = self::RESOURCE . 'web' . DIRECTORY_SEPARATOR;
    const WEB_SYSTEM_RES = self::WEB_RES . 'system' . DIRECTORY_SEPARATOR;

    // path for resources to test the user interface
    const UI_RES = self::WEB_RES . 'ui' . DIRECTORY_SEPARATOR;

    // resource paths
    const HTML = self::WEB . 'html' . DIRECTORY_SEPARATOR;

    // path parts
    const DB = 'db' . DIRECTORY_SEPARATOR;
    const FORMULA = 'formula' . DIRECTORY_SEPARATOR;
    const VIEWS = 'views' . DIRECTORY_SEPARATOR;
    const VIEWS_BY_ID = 'views_by_id' . DIRECTORY_SEPARATOR;
    const WEB = 'web' . DIRECTORY_SEPARATOR;

}
