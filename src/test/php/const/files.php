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

use const\paths as test_paths;

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
     * unit test files
     */

    const IP_BLACKLIST = test_paths::SYSTEM . 'ip_blacklist' . self::JSON;


    /*
     * config
     */

    const SYSTEM_CONFIG_SAMPLE = test_paths::RESOURCE . 'config_sample' . self::YAML;


    /*
     * api test files
     */

    const TYPE_LISTS_CACHE = test_paths::API_TYPE_LIST_RES . 'type_lists' . self::JSON;
    const SYS_LOG = test_paths::API_SYSTEM_RES . 'sys_log' . self::JSON;
    const SYS_LOG_HTML = test_paths::WEB_SYSTEM_RES . 'sys_log' . self::HTML;
    const SYS_LOG_LIST_API = test_paths::API_SYS_LOG_RES . 'sys_log_list' . self::JSON;
    const SYS_LOG_LIST_HTML = test_paths::WEB_SYSTEM_RES . 'sys_log_list' . self::HTML;
    const SYS_LOG_LIST_PAGE = test_paths::WEB_SYSTEM_RES . 'sys_log_list_page' . self::HTML;
    const SYS_LOG_ADMIN = test_paths::WEB_SYSTEM_RES . 'sys_log_admin' . self::HTML;
    const FORMULA_COUNT = test_paths::DB_RES_FORMULA . 'formula_count' . self::SQL;
    const USER_COUNT = test_paths::DB_USER . 'user_count' . self::SQL;
    const WORD_LIST = test_paths::EXPORT_WORD . 'word_list' . self::JSON;


    /*
     * sql
     */

    const SQL_CREATE_EXT = '_create';
    const SQL_INDEX_EXT = '_index';
    const SQL_FOREIGN_KEY_EXT = '_foreign_key';

    /*
     * import test files
     */

    const IMPORT_USERS = test_paths::IMPORT_UNIT . 'users';
    const IMPORT_WORDS = test_paths::IMPORT_UNIT . 'words';
    const IMPORT_VERBS = test_paths::IMPORT_UNIT . 'verbs';
    const IMPORT_TRIPLES = test_paths::IMPORT_UNIT . 'triples';
    const IMPORT_SOURCES = test_paths::IMPORT_UNIT . 'sources';
    const IMPORT_VALUES = test_paths::IMPORT_UNIT . 'values';
    const IMPORT_FORMULAS = test_paths::IMPORT_UNIT . 'formulas';
    const IMPORT_VIEWS = test_paths::IMPORT_UNIT . 'views';
    const IMPORT_COMPONENTS = test_paths::IMPORT_UNIT . 'components';
    const IMPORT_UPDATE_EXT = '_update';
    const IMPORT_UNDO_EXT = '_undo';

    const IMPORT_WARNING = test_paths::IMPORT . 'warning_and_error_test' . self::JSON;

    const IMPORT_COUNTRIES = test_paths::IMPORT . 'countries' . self::JSON;
    const IMPORT_COMPANIES = test_paths::IMPORT . 'companies' . self::JSON;
    const IMPORT_WIND_INVESTMENT = test_paths::IMPORT . 'wind_investment' . self::JSON;
    const IMPORT_COUNTRY_ISO = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166' . self::JSON;
    const IMPORT_COUNTRY_ISO_WIKI = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-wiki' . self::JSON;
    const IMPORT_COUNTRY_ISO_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-context' . self::JSON;
    const IMPORT_DEMOCRACY_INDEX = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;
    const IMPORT_DEMOCRACY_INDEX_TXT = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::TXT;
    const IMPORT_CURRENCY = test_paths::IMPORT_WIKIPEDIA . 'currency' . self::JSON;
    const IMPORT_CURRENCY_CONVERT = test_paths::IMPORT_WIKIPEDIA . 'currency-convert' . self::JSON;
    const IMPORT_CURRENCY_WIKI = test_paths::IMPORT_WIKIPEDIA . 'currency-wiki' . self::JSON;
    const IMPORT_CURRENCY_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'currency-context' . self::JSON;
    const IMPORT_TRAVEL_SCORING = test_paths::IMPORT . 'travel_scoring' . self::JSON;
    const IMPORT_TRAVEL_SCORING_VALUE_LIST = test_paths::IMPORT . 'travel_scoring_value_list' . self::JSON;
    const IMPORT_WIKI_DEMOCRACY = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;

    const TEST_IMPORT_FILE_LIST = [
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_CURRENCY,
        self::IMPORT_COMPANIES,
        self::IMPORT_WIND_INVESTMENT
    ];

    const TEST_DIRECT_IMPORT_FILE_LIST = [
        self::IMPORT_TRAVEL_SCORING_VALUE_LIST,
    ];

    const TEST_IMPORT_FILE_LIST_ALL = [
        self::IMPORT_CURRENCY,
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_WIND_INVESTMENT,
        self::IMPORT_TRAVEL_SCORING,
        self::IMPORT_TRAVEL_SCORING_VALUE_LIST,
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
