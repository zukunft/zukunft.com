<?php

/*

    model/const/files.php - resource file names used in the backend
    ---------------------


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

include_once paths::SHARED_CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\shared\const\files as files_shared;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class files
{

    /*
     * types and extensions
     */

    const string JSON = '.json';
    const string YAML = '.yaml';
    const string CODE_LINK_TYPE = '.csv';
    const string SQL = '.sql';
    const string SEP = '_';


    /*
     * path
     */

    const string RESOURCE_PATH = paths::MAIN . 'resources' . DIRECTORY_SEPARATOR;
    const string MESSAGE_PATH = self::RESOURCE_PATH . 'messages' . DIRECTORY_SEPARATOR;
    const string SYSTEM_UNIT_TEST_DATA_PATH = self::MESSAGE_PATH . 'system_unit_test_data' . DIRECTORY_SEPARATOR;
    const string SYSTEM_UNIT_TEST_DATA_FOLDER = 'system_unit_test_data' . DIRECTORY_SEPARATOR;
    const string DATA_START_PAGE_PATH = self::MESSAGE_PATH . 'start_page' . DIRECTORY_SEPARATOR;
    const string DB_PATH = self::RESOURCE_PATH . 'db' . DIRECTORY_SEPARATOR;
    const string DB_UPGRADE_PATH = self::DB_PATH . 'upgrade' . DIRECTORY_SEPARATOR;
    const string DB_UPGRADE_V003_PATH = self::DB_UPGRADE_PATH . 'v0.0.3' . DIRECTORY_SEPARATOR;
    const string DB_SETUP_PATH = self::DB_PATH . 'setup' . DIRECTORY_SEPARATOR;
    const string DB_SETUP_PG_PATH = self::DB_SETUP_PATH . 'postgres' . DIRECTORY_SEPARATOR;
    const string DB_SETUP_MYSQL_PATH = self::DB_SETUP_PATH . 'mysql' . DIRECTORY_SEPARATOR;

    // TODO make the csv file list based on the class name
    const string CODE_LINK_PATH = self::RESOURCE_PATH . 'db_code_links' . DIRECTORY_SEPARATOR;


    /*
     * system config
     */

    // the system users as a zukunft.com user import json
    const string SYSTEM_USERS = self::RESOURCE_PATH . 'users' . self::JSON;

    // the default system config as a yaml including the pod and the user frontend config
    const string SYSTEM_CONFIG = self::RESOURCE_PATH . 'config' . self::YAML;

    // initial loading of words and triples used for unit, ui and db read tests
    // so that they have a low database id that does hopefully neven change
    // including some often used words and triples that are used for the offline phrase selection
    const string BASE_PHRASES_FILE = 'base_phrases' . self::JSON;

    // initial configuration of some views that the user can change
    const string BASE_VIEWS_FILE = 'base_views' . self::JSON;


    /*
     * cache
     */

    const string CACHE_CONFIG = 'cfg';

    /*
     * db config
     */

    const string DB_ROLE_FILE = 'db_create_user' . self::SQL;
    const string DB_CREATE_FILE = 'db_create_database' . self::SQL;
    const string DB_STRUCTURE_FILE = 'zukunft_structure' . self::SQL;
    const string DB_UPGRADE_POSTGRES = 'upgrade_postgres' . self::SQL;
    const string DB_UPGRADE_MYSQL = 'upgrade_mysql' . self::SQL;


    /*
     * initial pod data
     */

    // the initial verbs as a zukunft.com verb import json
    const string VERBS = self::RESOURCE_PATH . 'verbs' . self::JSON;


    // the initial list of blocked ip addresses
    const string IP_BLACKLIST_FILE = 'ip_blacklist' . self::JSON;

    // sources used for the initial pod setup and for system testing
    const string SOURCES_FILE = 'sources' . self::JSON;

    // some basic units including the SI units e.g. kilogram
    const string UNITS_FILE = 'units' . self::JSON;

    // some basic scaling formulas e.g. to scale millions to one
    const string SCALING_FILE = 'scaling' . self::JSON;

    // some basic time definition e.g. years
    const string TIME_FILE = 'time_definition' . self::JSON;

    // data for the default start page
    const string START_PAGE_DATA_FILE = 'solution_prio' . self::JSON;
    const string CONFORMITY_MRI_BERNS_FILE = self::DATA_START_PAGE_PATH . 'conformity_mri_berns' . self::JSON;

    // initial data just to add some sample data and for system testing
    const string COUNTRY_FILE = 'country' . self::JSON;
    const string COMPANY_FILE = 'company' . self::JSON;
    const string ZURICH_HTP_IMPACT_FILE = self::SYSTEM_UNIT_TEST_DATA_FOLDER . 'zurich_htp_impact' . self::JSON;

    // temp setup files that are loaded at the end not to change the id of objects used by the system tests
    const string MATH_FILE = 'math' . self::JSON;
    const string PHYSICS_FILE = 'physics' . self::JSON;
    const string CHEMISTRY_FILE = 'chemistry' . self::JSON;
    const string BIOLOGY_FILE = 'biology' . self::JSON;
    const string ENGINEERING_FILE = 'engineering' . self::JSON;
    const string ECONOMICS_FILE = 'economics' . self::JSON;
    const string GEOLOGY_FILE = 'geology' . self::JSON;
    const string METEOROLOGY_FILE = 'meteorology' . self::JSON;
    const string SOCIOLOGY_FILE = 'sociology' . self::JSON;
    const string PSYCHOLOGY_FILE = 'psychology' . self::JSON;
    const string ACCOUNTING_FILE = 'accounting' . self::JSON;

    // initial data import
    CONST string TEST_COUNTRIES = self::SYSTEM_UNIT_TEST_DATA_FOLDER . 'countries' . self::JSON;
    CONST string TEST_CURRENCIES = self::SYSTEM_UNIT_TEST_DATA_FOLDER . 'currencies' . self::JSON;
    CONST string TEST_COMPANIES = self::SYSTEM_UNIT_TEST_DATA_FOLDER . 'companies' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO = self::SYSTEM_UNIT_TEST_DATA_PATH . 'country-ISO-3166' . self::JSON;
    CONST string IMPORT_DEMOCRACY_INDEX = self::SYSTEM_UNIT_TEST_DATA_PATH . 'democracy_index_table' . self::JSON;
    CONST string IMPORT_WIND_INVESTMENT = self::SYSTEM_UNIT_TEST_DATA_PATH . 'wind_investment' . self::JSON;

    /*
     * file lists
     */

    // to load the data required for the system and the unit or db read tests
    const array SYSTEM_DATA_FILES = [
        files_shared::SYSTEM_VIEWS_FILE,
        self::SOURCES_FILE,
        self::UNITS_FILE,
        self::SCALING_FILE,
        self::TIME_FILE,
        self::BASE_PHRASES_FILE,
        self::BASE_VIEWS_FILE,
        self::START_PAGE_DATA_FILE,
        self::COMPANY_FILE,
        self::COUNTRY_FILE,
        self::TEST_COUNTRIES,
        self::TEST_CURRENCIES,
        self::TEST_COMPANIES,
        // TODO Prio 0 activate
        //self::ZURICH_HTP_IMPACT_FILE,
    ];

    // to load the default system data for a standard pod
    const array POD_CONFIG_FILES_DIRECT = [
        self::IP_BLACKLIST_FILE,
    ];

    // to load the default data for a pod
    const array BASE_DATA_FILES = [
        self::MATH_FILE,
        self::PHYSICS_FILE,
        self::CHEMISTRY_FILE,
        self::BIOLOGY_FILE,
        self::ENGINEERING_FILE,
        self::ECONOMICS_FILE,
        self::GEOLOGY_FILE,
        self::METEOROLOGY_FILE,
        self::SOCIOLOGY_FILE,
        self::PSYCHOLOGY_FILE,
        self::ACCOUNTING_FILE,
    ];

    // to load the default data for a pod
    const array BASE_DATA_PATH_FILES = [
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_WIND_INVESTMENT,
        self::CONFORMITY_MRI_BERNS_FILE,
    ];

}
