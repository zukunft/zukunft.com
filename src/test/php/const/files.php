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
     * CAUTION! auto fix setting -> set always to false after mass update!
     */

    CONST bool AUTO_UPDATE_TEST_FILES = false;

    /*
     * types and extensions
     */

    CONST string YAML = '.yaml';
    CONST string JSON = '.json';
    CONST string HTML = '.html';
    CONST string SQL = '.sql';
    CONST string TXT = '.txt';
    CONST string MD = '.md';
    CONST string CSV = '.csv';
    CONST string ZIP = '.zip';


    /*
     * unit test files
     */

    CONST string IP_BLACKLIST = test_paths::SYSTEM . 'ip_blacklist' . self::JSON;


    /*
     * docs
     */

    CONST string DOCS_OBJECTS = test_paths::DOCS . 'code_objects_all' . self::MD;
    CONST string DOCS_FUNCTIONS = test_paths::DOCS . 'code_functions_all' . self::MD;
    CONST string DOCS_NAME_EXCEPTIONS = test_paths::DOCS . 'code_object_name_exceptions' . self::MD;


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
    CONST string SYS_LOG_LIST_TEST = test_paths::API_SYS_LOG_RES . 'sys_log_list_test' . self::JSON;
    CONST string SYS_LOG_LIST_HTML = test_paths::WEB_SYSTEM_RES . 'sys_log_list' . self::HTML;
    CONST string SYS_LOG_LIST_PAGE = test_paths::WEB_SYSTEM_RES . 'sys_log_list_page' . self::HTML;
    CONST string SYS_LOG_ADMIN = test_paths::WEB_SYSTEM_RES . 'sys_log_admin' . self::HTML;
    CONST string FORMULA_COUNT = test_paths::DB_RES_FORMULA . 'formula_count' . self::SQL;
    CONST string USER_COUNT = test_paths::DB_USER . 'user_count' . self::SQL;
    CONST string WORD_LIST = test_paths::EXPORT_WORD . 'word_list' . self::JSON;


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
    CONST string IMPORT_RESULT_CALC = test_paths::IMPORT_UNIT . 'result_calc_simple';
    CONST string IMPORT_CALC_VALIDATION = test_paths::IMPORT_UNIT . 'calc_validation';
    CONST string IMPORT_CALC_VALIDATION_MISMATCH = test_paths::IMPORT_INCONSISTENCY . 'calc_validation_mismatch';
    CONST string IMPORT_CALC_VALIDATION_VALUE_MISSING = test_paths::IMPORT_INCONSISTENCY . 'calc_validation_value_missing';
    CONST string IMPORT_UPDATE_EXT = '_update';
    CONST string IMPORT_UNDO_EXT = '_undo';

    CONST string IMPORT_WARNING = test_paths::IMPORT . 'warning_and_error_test' . self::JSON;
    CONST string IMPORT_VERSION_NEWER_TEST = test_paths::IMPORT . 'version_newer_test' . self::JSON;

    CONST string IMPORT_COMPANIES = test_paths::IMPORT . 'companies' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO_WIKI = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-wiki' . self::JSON;
    CONST string IMPORT_COUNTRY_ISO_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'country-ISO-3166-context' . self::JSON;
    CONST string IMPORT_DEMOCRACY_INDEX = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;
    CONST string IMPORT_DEMOCRACY_INDEX_TXT = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::TXT;
    CONST string IMPORT_CURRENCY_CONVERT = test_paths::IMPORT_WIKIPEDIA . 'currency-convert' . self::JSON;
    CONST string IMPORT_CURRENCY_WIKI = test_paths::IMPORT_WIKIPEDIA . 'currency-wiki' . self::JSON;
    CONST string IMPORT_CURRENCY_CONTEXT = test_paths::IMPORT_WIKIPEDIA . 'currency-context' . self::JSON;
    CONST string IMPORT_TRAVEL_SCORING = test_paths::IMPORT . 'travel_scoring' . self::JSON;
    CONST string IMPORT_POPULISM_FERMI_ESTIMATE = test_paths::IMPORT . 'fermi_estimates' . self::JSON;
    CONST string IMPORT_PORTFOLIO_SPLIT_CALC = test_paths::IMPORT . 'portfolio_split_calc' . self::JSON;
    CONST string IMPORT_PORTFOLIO_INSTRUMENTS = test_paths::IMPORT . 'portfolio_instruments' . self::JSON;
    CONST string IMPORT_PORTFOLIO_REPORT = test_paths::IMPORT . 'portfolio_report' . self::JSON;
    CONST string IMPORT_PORTFOLIO_SPLIT_PARAMETERS_SAMPLE = test_paths::IMPORT . 'portfolio_split_parameters_sample' . self::JSON;
    CONST string IMPORT_TRAVEL_SCORING_VALUE_LIST = test_paths::IMPORT . 'travel_scoring_value_list' . self::JSON;
    CONST string IMPORT_WIKI_DEMOCRACY = test_paths::IMPORT_WIKIPEDIA . 'democracy_index_table' . self::JSON;

    // remaining JSON files in test/resources/import/, in alphabetical order
    CONST string IMPORT_ABB_2013 = test_paths::IMPORT . 'ABB_2013' . self::JSON;
    CONST string IMPORT_ABB_2017 = test_paths::IMPORT . 'ABB_2017' . self::JSON;
    CONST string IMPORT_ABB_2019 = test_paths::IMPORT . 'ABB_2019' . self::JSON;
    CONST string IMPORT_BASE_TEST_DATA = test_paths::IMPORT . 'base_test_data' . self::JSON;
    CONST string IMPORT_BUS_LINE_MEILEN_USTER = test_paths::IMPORT . 'BusLineMeilenUster' . self::JSON;
    CONST string IMPORT_CAR_COSTS = test_paths::IMPORT . 'car_costs' . self::JSON;
    CONST string IMPORT_CBAM_BLUEBERRY_PACKED = test_paths::IMPORT . 'CBAM_blueberry_packed' . self::JSON;
    CONST string IMPORT_COVID_19 = test_paths::IMPORT . 'COVID-19' . self::JSON;
    CONST string IMPORT_ELECTRICITY_PRICES = test_paths::IMPORT . 'electricity_prices' . self::JSON;
    CONST string IMPORT_FERMI_POLARISATION_US = test_paths::IMPORT . 'fermi_polarisation_us' . self::JSON;
    CONST string IMPORT_NESN_2019 = test_paths::IMPORT . 'NESN_2019' . self::JSON;
    CONST string IMPORT_PERSONAL_CLIMATE_GAS_EMISSIONS_TIMON = test_paths::IMPORT . 'personal_climate_gas_emissions_timon' . self::JSON;
    CONST string IMPORT_REAL_ESTATE = test_paths::IMPORT . 'real_estate' . self::JSON;
    CONST string IMPORT_REFERENCES = test_paths::IMPORT . 'references' . self::JSON;
    CONST string IMPORT_THOMY_TEST = test_paths::IMPORT . 'THOMY_test' . self::JSON;
    CONST string IMPORT_ULTIMATUM_GAME = test_paths::IMPORT . 'Ultimatum_game' . self::JSON;
    CONST string IMPORT_WORK = test_paths::IMPORT . 'work' . self::JSON;

    // XBRL filesets (zipped instance + taxonomy delivered by an issuer)
    CONST string IMPORT_XBRL_ABB_2013_ZIP = test_paths::IMPORT_XBRL_ZIP . 'abb-2013-xbrl_fileset-20131231' . self::ZIP;

    CONST string FIXED_DB_CSV = 'list' . self::CSV;

    // for the SQL formatter
    CONST string SQL_FORMAT_TEST = 'word_update_log_0022004000002_user' . self::SQL;
    CONST string SQL_FORMAT_TEST_MYSQL = 'word_update_log_0022004000002_user_mysql' . self::SQL;
    CONST string SQL_FORMAT_TEST_INSERT = 'word_insert_log_0111005000001' . self::SQL;
    CONST string SQL_FORMAT_TEST_INSERT_MYSQL = 'word_insert_log_0111005000001_mysql' . self::SQL;
    CONST string SQL_FORMAT_TEST_UPDATE = 'word_update_0022004000002' . self::SQL;
    CONST string SQL_FORMAT_TEST_UPDATE_MYSQL = 'word_update_0022004000002_mysql' . self::SQL;
    CONST string SQL_FORMAT_TEST_SELECT = 'word_by_id' . self::SQL;
    CONST string SQL_FORMAT_TEST_SELECT_MYSQL = 'word_by_id_mysql' . self::SQL;

    const array TEST_IMPORT_FILES = [
        self::IMPORT_POPULISM_FERMI_ESTIMATE,
        self::IMPORT_PORTFOLIO_SPLIT_CALC,
        self::IMPORT_PORTFOLIO_INSTRUMENTS,
        self::IMPORT_PORTFOLIO_REPORT,
        self::IMPORT_PORTFOLIO_SPLIT_PARAMETERS_SAMPLE,
    ];

    const array TEST_DIRECT_IMPORT_FILE_LIST = [
        self::IMPORT_TRAVEL_SCORING_VALUE_LIST,
    ];

    const array TEST_IMPORT_FILE_LIST_ALL = [
        self::IMPORT_TRAVEL_SCORING,
        self::IMPORT_TRAVEL_SCORING_VALUE_LIST,
        self::IMPORT_ABB_2013,
        self::IMPORT_ABB_2017,
        self::IMPORT_ABB_2019,
        self::IMPORT_NESN_2019,
        self::IMPORT_REAL_ESTATE,
        self::IMPORT_ULTIMATUM_GAME,
        self::IMPORT_COVID_19,
        self::IMPORT_PERSONAL_CLIMATE_GAS_EMISSIONS_TIMON,
        self::IMPORT_THOMY_TEST,
        self::IMPORT_BASE_TEST_DATA,
        self::IMPORT_BUS_LINE_MEILEN_USTER,
        self::IMPORT_CAR_COSTS,
        self::IMPORT_CBAM_BLUEBERRY_PACKED,
        self::IMPORT_ELECTRICITY_PRICES,
        self::IMPORT_FERMI_POLARISATION_US,
        self::IMPORT_REFERENCES,
        self::IMPORT_WORK,
    ];


    /*
     * cleanup
     */

    // queries to check if removing of the test rows is complete
    const string CLEAN_CHECK_WORDS = test_paths::DB_CLEANUP . 'test_words.sql';
    const string CLEAN_CHECK_VERBS = test_paths::DB_CLEANUP . 'test_verbs.sql';
    const string CLEAN_CHECK_TRIPLES = test_paths::DB_CLEANUP . 'test_triples.sql';
    const string CLEAN_CHECK_SOURCES = test_paths::DB_CLEANUP . 'test_sources.sql';
    const string CLEAN_CHECK_REFS = test_paths::DB_CLEANUP . 'test_refs.sql';
    const string CLEAN_CHECK_GROUPS = test_paths::DB_CLEANUP . 'test_groups.sql';
    const string CLEAN_CHECK_FORMULAS = test_paths::DB_CLEANUP . 'test_formulas.sql';
    const string CLEAN_CHECK_VIEWS = test_paths::DB_CLEANUP . 'test_views.sql';
    const string CLEAN_CHECK_COMPONENTS = test_paths::DB_CLEANUP . 'test_components.sql';
    const array CLEAN_CHECKS = array(
        self::CLEAN_CHECK_WORDS,
        self::CLEAN_CHECK_VERBS,
        self::CLEAN_CHECK_TRIPLES,
        self::CLEAN_CHECK_SOURCES,
        self::CLEAN_CHECK_REFS,
        self::CLEAN_CHECK_GROUPS,
        self::CLEAN_CHECK_FORMULAS,
        self::CLEAN_CHECK_VIEWS,
        self::CLEAN_CHECK_COMPONENTS,
    );
}
