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

namespace Zukunft\ZukunftCom\test\php\const;

class paths
{

    // repeat the path just for consistency
    const string CONST = TEST_PHP_PATH . 'const' . DIRECTORY_SEPARATOR;

    const string CREATE = TEST_PHP_PATH . 'create' . DIRECTORY_SEPARATOR;
    const string UTILS = TEST_PHP_PATH . 'utils' . DIRECTORY_SEPARATOR;

    // path for unit tests
    const string UNIT = TEST_PHP_PATH . 'unit' . DIRECTORY_SEPARATOR;              // for unit tests
    const string UNIT_READ = TEST_PHP_PATH . 'unit_read' . DIRECTORY_SEPARATOR;    // for the unit tests with database read only
    const string UNIT_API = TEST_PHP_PATH . 'unit_api' . DIRECTORY_SEPARATOR;      // for the unit tests of the api
    const string UNIT_DSP = self::UNIT . 'html' . DIRECTORY_SEPARATOR;             // for the unit tests that create HTML code
    const string UNIT_HTML = TEST_PHP_PATH . 'unit_display' . DIRECTORY_SEPARATOR; // for the unit tests that create HTML code
    const string UNIT_UI = TEST_PHP_PATH . 'unit_ui' . DIRECTORY_SEPARATOR;        // for the unit tests that create JSON messages for the frontend
    const string UNIT_WRITE = TEST_PHP_PATH . 'unit_write' . DIRECTORY_SEPARATOR;  // for the unit tests that save to database (and cleanup the test data after completion)
    const string UNIT_WORKFLOW = TEST_PHP_PATH . 'unit_workflow' . DIRECTORY_SEPARATOR;  // to check the url based user workflow
    const string UNIT_INT = TEST_PHP_PATH . 'integration' . DIRECTORY_SEPARATOR;   // for integration tests
    const string DEV = TEST_PHP_PATH . 'dev' . DIRECTORY_SEPARATOR;                // for test still in development
    const string DOCS = ROOT_PATH . 'docs' . DIRECTORY_SEPARATOR;                  // to check the doc consistency


    // main path for the test resources
    const string RESOURCE = TEST_PATH . 'resources' . DIRECTORY_SEPARATOR;

    // path for db tests
    const string SUB_DB = 'db' . DIRECTORY_SEPARATOR;
    const string DB_CLEANUP = self::SUB_DB . 'cleanup' . DIRECTORY_SEPARATOR;

    // path for resources to test the api
    const string API_RES = self::RESOURCE . 'api' . DIRECTORY_SEPARATOR;
    const string API_SYSTEM_RES = self::API_RES . 'system' . DIRECTORY_SEPARATOR;
    const string API_TYPE_LIST_RES = self::API_RES . 'type_lists' . DIRECTORY_SEPARATOR;
    const string API_SYS_LOG_RES = self::API_RES . 'sys_log_list' . DIRECTORY_SEPARATOR;
    const string DB_FORMULA = self::DB . self::FORMULA;
    const string DB_RES = self::RESOURCE . self::DB;
    const string DB_RES_FORMULA = self::DB_RES . self::FORMULA;
    const string DB_USER = self::DB_RES . 'user' . DIRECTORY_SEPARATOR;
    const string EXPORT = self::RESOURCE . 'export' . DIRECTORY_SEPARATOR;
    const string EXPORT_WORD = self::EXPORT . 'word' . DIRECTORY_SEPARATOR;
    const string IMPORT = self::RESOURCE . 'import' . DIRECTORY_SEPARATOR;
    const string IMPORT_UNIT = self::IMPORT . 'unit_tests' . DIRECTORY_SEPARATOR;
    const string IMPORT_WIKIPEDIA = self::IMPORT . 'wikipedia' . DIRECTORY_SEPARATOR;
    const string IMPORT_XBRL = self::IMPORT . 'xbrl' . DIRECTORY_SEPARATOR;
    const string IMPORT_XBRL_ZIP = self::IMPORT_XBRL . 'zip' . DIRECTORY_SEPARATOR;
    const string UNIT_RES = self::RESOURCE . 'unit' . DIRECTORY_SEPARATOR;
    const string SYSTEM = self::UNIT_RES . 'system' . DIRECTORY_SEPARATOR;
    const string SANDBOX = self::UNIT_RES . 'sandbox' . DIRECTORY_SEPARATOR;

    // path for resources to test the frontend
    const string WEB_RES = self::RESOURCE . 'web' . DIRECTORY_SEPARATOR;
    const string WEB_SYSTEM_RES = self::WEB_RES . 'system' . DIRECTORY_SEPARATOR;

    // path for resources to test the user interface
    const string UI_RES = self::WEB_RES . 'ui' . DIRECTORY_SEPARATOR;

    // resource paths
    const string HTML = self::WEB . 'html' . DIRECTORY_SEPARATOR;

    // path parts
    const string DB = 'db' . DIRECTORY_SEPARATOR;
    const string DB_FORMAT_TEST = self::DB . 'format_test' . DIRECTORY_SEPARATOR;
    const string FORMULA = 'formula' . DIRECTORY_SEPARATOR;
    // to test if all interface function of each web object word fine
    const string VIEW_FUNCTIONS = 'object_pages' . DIRECTORY_SEPARATOR;
    // to test if each object has all curl views including one or many read only view
    const string VIEWS = 'views_by_object' . DIRECTORY_SEPARATOR;
    // to test if all system view based on the id are fine including more static views like the about page
    const string VIEWS_BY_ID = self::HTML . 'views_by_id' . DIRECTORY_SEPARATOR;
    const string WEB = 'web' . DIRECTORY_SEPARATOR;

}
