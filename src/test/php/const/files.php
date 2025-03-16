<?php

/*

    test/const/files.php - names of all test resource files
    --------------------


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

class files
{

    /*
     * types and extensions
     */

    CONST YAML = '.yaml';
    CONST JSON = '.json';
    CONST HTML = '.html';
    CONST SQL = '.sql';
    CONST TXT = '.txt';

    /*
     * path of test files
     */

    // main path for the test resources
    const RESOURCE_PATH = TEST_PATH . 'resources' . DIRECTORY_SEPARATOR;

    // path for resources to test the api
    const API_RES_PATH = self::RESOURCE_PATH . 'api' . DIRECTORY_SEPARATOR;
    const API_SYSTEM_RES_PATH = self::API_RES_PATH . 'system' . DIRECTORY_SEPARATOR;
    const DB_PATH = self::RESOURCE_PATH . 'db' . DIRECTORY_SEPARATOR;
    const DB_FORMULA_PATH = self::DB_PATH . 'formula' . DIRECTORY_SEPARATOR;
    const EXPORT_PATH = self::RESOURCE_PATH . 'export' . DIRECTORY_SEPARATOR;
    const EXPORT_WORD_PATH = self::EXPORT_PATH . 'word' . DIRECTORY_SEPARATOR;
    const IMPORT_PATH = self::RESOURCE_PATH . 'import' . DIRECTORY_SEPARATOR;
    const IMPORT_UNIT_PATH = self::IMPORT_PATH . 'unit_tests' . DIRECTORY_SEPARATOR;
    const IMPORT_WIKIPEDIA_PATH = self::IMPORT_PATH . 'wikipedia' . DIRECTORY_SEPARATOR;
    const TRANSLATION_PATH = self::RESOURCE_PATH . 'translation' . DIRECTORY_SEPARATOR;

    // path for resources to test the frontend
    const WEB_RES_PATH = self::RESOURCE_PATH . 'web' . DIRECTORY_SEPARATOR;
    const WEB_SYSTEM_RES_PATH = self::WEB_RES_PATH . 'system' . DIRECTORY_SEPARATOR;

    // path for resources to test the user interface
    const UI_RES_PATH = self::WEB_RES_PATH . 'ui' . DIRECTORY_SEPARATOR;


    /*
     * api test files
     */

    const SYS_LOG = self::API_SYSTEM_RES_PATH . 'sys_log' . self::JSON;
    const SYS_LOG_HTML = self::WEB_SYSTEM_RES_PATH . 'sys_log' . self::HTML;
    const SYSTEM_CONFIG_SAMPLE = self::RESOURCE_PATH . 'config_sample' . self::YAML;
    const SYS_LOG_ADMIN = self::WEB_SYSTEM_RES_PATH . 'sys_log_admin' . self::HTML;
    const FORMULA_COUNT = self::DB_FORMULA_PATH . 'formula_count' . self::SQL;
    const WORD_LIST = self::EXPORT_WORD_PATH . 'word_list' . self::JSON;


    /*
     * import test files
     */

    const IMPORT_WORDS = self::IMPORT_UNIT_PATH . 'words' . self::JSON;
    const IMPORT_TRIPLES = self::IMPORT_UNIT_PATH . 'triples' . self::JSON;
    const IMPORT_SOURCES = self::IMPORT_UNIT_PATH . 'sources' . self::JSON;
    const IMPORT_VALUES = self::IMPORT_UNIT_PATH . 'values' . self::JSON;
    const IMPORT_FORMULAS = self::IMPORT_UNIT_PATH . 'formulas' . self::JSON;

    const IMPORT_COUNTRIES = self::IMPORT_PATH . 'countries' . self::JSON;
    const IMPORT_COMPANIES = self::IMPORT_PATH . 'companies' . self::JSON;
    const IMPORT_WIND_INVESTMENT = self::IMPORT_PATH . 'wind_investment' . self::JSON;
    const IMPORT_COUNTRY_ISO = self::IMPORT_WIKIPEDIA_PATH . 'country-ISO-3166' . self::JSON;
    const IMPORT_COUNTRY_ISO_WIKI = self::IMPORT_WIKIPEDIA_PATH . 'country-ISO-3166-wiki' . self::JSON;
    const IMPORT_COUNTRY_ISO_CONTEXT = self::IMPORT_WIKIPEDIA_PATH . 'country-ISO-3166-context' . self::JSON;
    const IMPORT_DEMOCRACY_INDEX = self::IMPORT_WIKIPEDIA_PATH . 'democracy_index_table' . self::JSON;
    const IMPORT_DEMOCRACY_INDEX_TXT = self::IMPORT_WIKIPEDIA_PATH . 'democracy_index_table' . self::TXT;
    const IMPORT_CURRENCY = self::IMPORT_WIKIPEDIA_PATH . 'currency' . self::JSON;
    const IMPORT_CURRENCY_CONVERT = self::IMPORT_WIKIPEDIA_PATH . 'currency-convert' . self::JSON;
    const IMPORT_CURRENCY_WIKI = self::IMPORT_WIKIPEDIA_PATH . 'currency-wiki' . self::JSON;
    const IMPORT_CURRENCY_CONTEXT = self::IMPORT_WIKIPEDIA_PATH . 'currency-context' . self::JSON;

    const TEST_IMPORT_FILE_LIST = [
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_CURRENCY
    ];

    const TEST_DIRECT_IMPORT_FILE_LIST = [
        self::IMPORT_COMPANIES,
        self::IMPORT_WIND_INVESTMENT,
    ];

    const TEST_IMPORT_FILE_LIST_ALL = [
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_CURRENCY,
        'travel_scoring.json',
        'travel_scoring_value_list.json',
        self::IMPORT_COMPANIES,
        self::IMPORT_WIND_INVESTMENT,
        'ABB_2013.json',
        'ABB_2017.json',
        'ABB_2019.json',
        'NESN_2019.json',
        'real_estate.json',
        'Ultimatum_game.json',
        'COVID-19.json',
        'personal_climate_gas_emissions_timon.json',
        'THOMY_test.json'
    ];

}
