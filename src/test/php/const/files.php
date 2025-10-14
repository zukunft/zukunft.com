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

namespace Zukunft\ZukunftCom\test\php\const;

use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

class files
{

    /*
     * types and extensions
     */

    CONST string YAML = '.yaml';
    CONST string JSON = '.json';
    CONST string HTML = '.html';
    CONST string SQL = '.sql';
    CONST string TXT = '.txt';


    /*
     * unit test files
     */

    CONST string IP_BLACKLIST = test_paths::SYSTEM . 'ip_blacklist' . self::JSON;


    /*
     * config
     */

    CONST string SYSTEM_CONFIG_SAMPLE = test_paths::RESOURCE . 'config_sample' . self::YAML;


    /*
     * api test files
     */

    CONST string TYPE_LISTS_CACHE = test_paths::API_TYPE_LIST_RES . 'type_lists' . self::JSON;
    CONST string SYS_LOG = test_paths::API_SYSTEM_RES . 'sys_log' . self::JSON;
    CONST string SYS_LOG_HTML = test_paths::WEB_SYSTEM_RES . 'sys_log' . self::HTML;
    CONST string SYS_LOG_LIST_API = test_paths::API_SYS_LOG_RES . 'sys_log_list' . self::JSON;
    CONST string SYS_LOG_LIST_HTML = test_paths::WEB_SYSTEM_RES . 'sys_log_list' . self::HTML;
    CONST string SYS_LOG_LIST_PAGE = test_paths::WEB_SYSTEM_RES . 'sys_log_list_page' . self::HTML;
    CONST string SYS_LOG_ADMIN = test_paths::WEB_SYSTEM_RES . 'sys_log_admin' . self::HTML;
    CONST string FORMULA_COUNT = test_paths::DB_RES_FORMULA . 'formula_count' . self::SQL;
    CONST string USER_COUNT = test_paths::DB_USER . 'user_count' . self::SQL;
    CONST string WORD_LIST = test_paths::EXPORT_WORD . 'word_list' . self::JSON;


    /*
     * curl test files
     */

    CONST string WIKIDATA_ZURICH = test_paths::IMPORT_WIKIDATA . 'Q72' . self::JSON;

    /*
     * sql
     */

    CONST string SQL_CREATE_EXT = '_create';
    CONST string SQL_INDEX_EXT = '_index';
    CONST string SQL_FOREIGN_KEY_EXT = '_foreign_key';

    /*
     * import test files
     */

    CONST string IMPORT_USERS = test_paths::IMPORT_UNIT . 'users';
    CONST string IMPORT_WORDS = test_paths::IMPORT_UNIT . 'words';
    CONST string IMPORT_VERBS = test_paths::IMPORT_UNIT . 'verbs';
    CONST string IMPORT_TRIPLES = test_paths::IMPORT_UNIT . 'triples';
    CONST string IMPORT_SOURCES = test_paths::IMPORT_UNIT . 'sources';
    CONST string IMPORT_VALUES = test_paths::IMPORT_UNIT . 'values';
    CONST string IMPORT_FORMULAS = test_paths::IMPORT_UNIT . 'formulas';
    CONST string IMPORT_VIEWS = test_paths::IMPORT_UNIT . 'views';
    CONST string IMPORT_COMPONENTS = test_paths::IMPORT_UNIT . 'components';
    CONST string IMPORT_UPDATE_EXT = '_update';
    CONST string IMPORT_UNDO_EXT = '_undo';

    CONST string IMPORT_WARNING = test_paths::IMPORT . 'warning_and_error_test' . self::JSON;

    CONST string IMPORT_COUNTRIES = test_paths::IMPORT . 'countries' . self::JSON;
    CONST string IMPORT_COMPANIES = test_paths::IMPORT . 'companies' . self::JSON;
    CONST string IMPORT_WIND_INVESTMENT = test_paths::IMPORT . 'wind_investment' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO_WIKI = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-wiki' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-context' . self::JSON;
    CONST string IMPORT_DEMOCRACY_INDEX = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;
    CONST string IMPORT_DEMOCRACY_INDEX_TXT = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::TXT;
    CONST string IMPORT_CURRENCY = test_paths::IMPORT_WIKIPEDIA . 'currency' . self::JSON;
    CONST string IMPORT_CURRENCY_CONVERT = test_paths::IMPORT_WIKIPEDIA . 'currency-convert' . self::JSON;
    CONST string IMPORT_CURRENCY_WIKI = test_paths::IMPORT_WIKIPEDIA . 'currency-wiki' . self::JSON;
    CONST string IMPORT_CURRENCY_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'currency-context' . self::JSON;
    CONST string IMPORT_CURRENCIES = test_paths::IMPORT . 'currencies' . self::JSON;
    CONST string IMPORT_TRAVEL_SCORING = test_paths::IMPORT . 'travel_scoring' . self::JSON;
    CONST string IMPORT_TRAVEL_SCORING_VALUE_LIST = test_paths::IMPORT . 'travel_scoring_value_list' . self::JSON;
    CONST string IMPORT_WIKI_DEMOCRACY = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;

    const array TEST_IMPORT_FILE_LIST = [
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_CURRENCY,
        self::IMPORT_COMPANIES,
        self::IMPORT_CURRENCIES,
        self::IMPORT_WIND_INVESTMENT
    ];

    const array TEST_DIRECT_IMPORT_FILE_LIST = [
        self::IMPORT_TRAVEL_SCORING_VALUE_LIST,
    ];

    const array TEST_IMPORT_FILE_LIST_ALL = [
        self::IMPORT_CURRENCY,
        self::IMPORT_COUNTRIES,
        self::IMPORT_COUNTRY_ISO,
        self::IMPORT_DEMOCRACY_INDEX,
        self::IMPORT_CURRENCIES,
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
