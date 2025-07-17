<?php

/*

    shared/const/words.php - predefined words used in the backend and frontend as code id
    ----------------------

    all preserved words must always be owned by an administrator so that the standard cannot be renamed


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

namespace shared\const;

class words
{

    /*
     * config
     */

    // words used in the frontend and backend for the system configuration
    // code_id and name of a words used by the system for its own configuration
    // e.g. the number of decimal places related to the user specific words
    // system configuration that are core for the database setup and update check are using the flat cfg methods
    // *_COM is the tooltip for the word; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    // this list is included in all preserved word names
    //
    // if words have a predefined behaviour instead of the code_id the phrase type is used

    // the standard word displayed to the user if she/he as not yet viewed any other word
    const DEFAULT_WORD_ID = 1;

    // keywords to select the system configuration
    const THIS_SYSTEM = 'zukunft.com';
    const SYSTEM = 'system';
    const CONFIGURATION = 'configuration';

    // words used to select parts of the system configuration where the normal name should not be changed
    const TOOLTIP_COMMENT_COM = 'keyword to read the word or triple description from the config.yaml';
    const TOOLTIP_COMMENT = 'tooltip-comment';
    const SYS_CONF_VALUE_COM = 'keyword to read the numeric value from the config.yaml';
    const SYS_CONF_VALUE = 'sys-conf-value';
    const SYS_CONF_SOURCE = 'source-name';
    const SYS_CONF_SOURCE_COM = 'source-description';
    const SYS_CONF_USER = 'pod-user-config';
    const SYS_CONF_USER_COM = 'keyword to read the user configuration for a specific user';

    // for the system setup and all pods of zukunft.com
    const POD = 'pod';
    const POD_ID = 313;
    const MASTER_POD_NAME = 'zukunft.com';
    const MASTER_POD_NAME_ID = 331;
    const JOB = 'job';
    const USER = 'user';
    const FRONTEND = 'frontend';
    const BACKEND = 'backend';
    const LANGUAGE = 'language';
    const BYTE = 'byte';

    // e.g. one instance / pod of zukunft.com
    const URL = 'url';
    const URL_ID = 326;
    // e.g. the launch date of the first beta version of zukunft.com
    const LAUNCH = 'launch';
    const LAUNCH_ID = 375;

    // for the user settings
    const ROW = 'row';
    const LIMIT = 'limit';
    const WORD = 'word';
    const TRIPLE = 'triple';
    const SOURCE = 'source';
    const FORMULA = 'formula';
    const WORDS = 'words';
    const VERBS = 'verbs';
    const TRIPLES = 'triples';
    const SOURCES = 'sources';
    const REFERENCES = 'references';
    const VALUES = 'values';
    const FORMULAS = 'formulas';
    const VIEWS = 'views';
    const COMPONENTS = 'components';
    const CHANGES = 'changes';
    const PERCENT = 'percent';
    const DECIMAL = 'decimal';

    // to exchange system configurations
    const USERS = 'users';
    const IP_RANGES = 'ip-ranges';

    // e.g. the geolocation of the development of zukunft.com
    const POINT = 'point';
    const POINT_ID = 376;

    // general words used also for the system configuration that have a fixed tooltip
    const TIME = 'time';
    const TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const TIME_ID = 103;
    const YEAR = 'year';
    const YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const YEAR_CAP = 'Year';
    const YEAR_CAP_ID = 134;
    const CALCULATION = 'calculation';
    const CALCULATION_COM = 'A calculation is a deliberate mathematical process that transforms one or more inputs into one or more outputs or results';
    const MIN = 'min';
    const MIN_COM = 'The minimal numeric value.';
    const MAX = 'max';
    const MAX_COM = 'The maximal numeric value.';
    const AVERAGE = 'average';
    const AVERAGE_COM = 'The arithmetic mean – the sum of the numbers divided by how many numbers are in the list.';
    const DEFAULT = 'default';
    const DEFAULT_COM = 'The setting used if nothing else is specified.';
    const DATABASE = 'database';
    const DATABASE_COM = 'An organized collection of data stored and accessed electronically.';

    // general words used also for the system configuration where the initial tooltip is in the config.yaml
    const VALUE = 'value';
    const VERSION = 'version';
    const RETRY = 'retry';
    const START = 'start';
    const DELAY = 'delay';
    const SEC = 'sec';
    const BLOCK = 'block';
    const SIZE = 'size';
    const INSERT = 'insert';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const LOAD = 'load';
    const TABLE = 'table';
    const FILE = 'file';
    const READ = 'read';
    const NAME = 'name';
    const PHRASE = 'phrase';
    const MILLISECOND = 'millisecond';
    const SELECT = 'select';
    const INITIAL = 'initial';
    const IMPORT = 'import';
    const DECODE = 'decode';
    const COUNT = 'count';
    const EXPECTED = 'expected';
    const ENTRY = 'entry';
    const PRESELECT = 'preselect';
    const FUTURE = 'future';
    const COLUMNS = 'columns';
    const AUTOMATIC = 'automatic';
    const CREATE = 'create';
    const STORE = 'store';
    const VIEW = 'view';
    const FREEZE = 'freeze';
    const CHANGE = 'change';
    const DAILY = 'daily';
    const IP = 'ip';
    const BEHAVIOUR = 'behaviour';

    // for the configuration of a single job
    // TODO complete the concrete setup
    const IMPORT_TYPE = 'import type';
    const API_WORD = 'API';
    // to group the user data and configuration within the system configuration
    const PASSWORD = 'password';
    const OPEN_API = 'OpenAPI';
    const DEFINITION = 'definition';


    /*
     * const for system testing
     */

    // word names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the word used for testing created with the initial setup (see also TWN_*)
    // TI_* is the database id based on the initial load
    // TD_* is the tooltip/description of the word

    // words from import file units.json in order of appearance
    const MATH = 'mathematics';
    const MATH_COM = 'Mathematics is an area of knowledge that includes the topics of numbers and formulas';
    const MATH_ID = 1;
    const MATH_PLURAL = 'mathematics';
    const CONST_NAME = 'constant';
    const CONST_COM = 'fixed and well-defined number';
    const CONST_ID = 2;
    const ONE = 'one';
    const ONE_ID = 4;
    const PI_SYMBOL = 'π';
    const PI_SYMBOL_ID = 5;
    const PI_SYMBOL_COM = 'Symbol for the ratio of the circumference of a circle to its diameter';
    const PI = 'Pi';
    const PI_ID = 17;
    const PI_COM = 'ratio of the circumference of a circle to its diameter';
    const E_SYMBOL = "𝑒";
    const E_SYMBOL_ID = 6;
    const E = "Euler's number";
    const E_ID = 18;
    const CIRCUMFERENCE = 'circumference';
    const CIRCUMFERENCE_ID = 15;
    const DIAMETER = 'diameter';
    const DIAMETER_ID = 16;
    const SECOND = 'second';
    const SECOND_ID = 24;
    const FLOW = 'flow';
    const FLOW_ID = 101;
    const MINUTE = 'minute';
    const MINUTE_ID = 104;
    const YEAR_2019 = '2019';
    const YEAR_2019_ID = 139;
    const YEAR_2020 = '2020';
    const YEAR_2020_ID = 140;
    const YEAR_2020_COM = 'the year 2020';

    // words from import file scaling.json in order of appearance
    const MIO = 'million';
    const MIO_ID = 157;
    const MIO_SHORT = 'mio';
    const BILLION = 'billion';
    const BILLION_ID = 158;
    const PCT = 'percent';
    const PCT_ID = 159;

    // words from import file time_definition.json in order of appearance
    const THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const THIS_ID = 179;
    const PRIOR_NAME = 'prior';
    const PRIOR_ID = 181;

    // words from import file solution_prio.json used for the start page in order of appearance
    const PROBLEM = 'problem';
    const PROBLEM_ID = 183;
    const GLOBAL = 'global';
    const GLOBAL_ID = 184;
    const POTENTIAL = 'potential';
    const POTENTIAL_ID = 187;
    const CLIMATE = 'climate';
    const CLIMATE_ID = 190;
    const WARMER = 'warmer';
    const WARMER_ID = 193;
    const POPULISM = 'populism';
    const POPULISM_ID = 197;
    const HEALTH = 'health';
    const HEALTH_ID = 213;
    const POVERTY = 'poverty';
    const POVERTY_ID = 215;
    const EDUCATION = 'education';
    const EDUCATION_ID = 217;
    const HAPPY = 'happy';
    const HAPPY_ID = 220;
    const POINTS = 'points';
    const POINTS_ID = 222;
    const HTP = 'htp';
    const HTP_ID = 225;
    const TRILLION = 'trillion';
    const TRILLION_ID = 226;
    const CHF = 'CHF';
    const CHF_ID = 228;
    const USD = 'USD';
    const USD_ID = 229;

    // words from import file company.json used for the start page in order of appearance
    const SALES = 'sales';
    const SALES_ID = 252;
    const CASH = 'cash';
    const CASH_ID = 254;
    const STATEMENT = 'statement';
    const STATEMENT_ID = 255;
    const PARTS = 'parts';
    const PARTS_ID = 257;
    const TOTAL_PRE = 'total';
    const TOTAL_ID = 258;
    const INCOME = 'income';
    const INCOME_ID = 259;
    const TAX = 'tax';
    const TAX_ID = 260;

    // words from import file country.json used for the start page in order of appearance
    const COUNTRY = 'Country';
    const CH = 'Switzerland';
    const CH_ID = 264;
    const GERMANY = 'Germany';
    const CANTON = 'Canton';
    const CANTON_ID = 265;
    const CITY = 'City';
    const CITY_ID = 266;
    const ZH = 'Zurich';
    const ZH_ID = 267;
    const BE = 'Bern';
    const BE_ID = 268;
    const GE = 'Geneva';
    const GE_ID = 269;
    const INHABITANT_ID = 271;
    // TODO add test to search for words in all language forms e.g. plural
    const INHABITANTS = 'inhabitants';
    const COMPANY = 'Company';
    const COMPANY_ID = 272;
    const YEAR_2013 = '2013';
    const YEAR_2013_ID = 273;
    const YEAR_2014 = '2014';
    const YEAR_2014_ID = 274;
    const YEAR_2015 = '2015';
    const YEAR_2015_ID = 275;
    const YEAR_2016 = '2016';
    const YEAR_2016_ID = 276;
    const YEAR_2017 = '2017';
    const YEAR_2017_ID = 277;
    const YEAR_2018 = '2018';
    const YEAR_2018_ID = 278;

    // words from import test file companies.json used for the start page in order of appearance
    const ABB = 'ABB';
    const ABB_ID = 1009;
    const VESTAS = 'Vestas';
    const VESTAS_ID = 1012;

    // for the config.yaml
    const TEST = 'test';
    const TEXT = 'text';
    const HTML = 'html';
    const LEVEL = 'level';
    const ALL = 'all';
    const TIMEOUTS = 'timeouts';
    const WARNINGS = 'warnings';
    const ERRORS = 'errors';

    // persevered word names for unit and integration tests based on the database
    // TWN_* - is a Test Word Name for words created only for testing (see also TN_*)
    const TEST_ADD = 'System Test Word';
    const TEST_ADD_COM = 'test description added to the word via import';
    const TEST_ADD_TO = 'System Test Word To';
    const TEST_ADD_VIA_FUNC = 'System Test Word added via sql function';
    const TEST_ADD_VIA_SQL = 'System Test Word added via sql insert';
    const TEST_ADD_GROUP_PRIME_FUNC = 'System Test Word for prime group add via sql function';
    const TEST_ADD_GROUP_PRIME_SQL = 'System Test Word for prime group add via sql insert';
    const TEST_ADD_GROUP_MOST_FUNC = 'System Test Word for main group add via sql function';
    const TEST_ADD_GROUP_MOST_SQL = 'System Test Word for main group add via sql insert';
    const TEST_ADD_GROUP_BIG_FUNC = 'System Test Word for big group add via sql function';
    const TEST_ADD_GROUP_BIG_SQL = 'System Test Word for big group add via sql insert';
    const TEST_RENAMED = 'System Test Word Renamed';
    const TEST_RENAMED_GROUP_PRIME_FUNC = 'System Test Word for prime group RENAMED via sql function';
    const TEST_RENAMED_GROUP_PRIME_SQL = 'System Test Word for prime group RENAMED via sql insert';
    const TEST_RENAMED_GROUP_MOST_FUNC = 'System Test Word for main group RENAMED via sql function';
    const TEST_RENAMED_GROUP_MOST_SQL = 'System Test Word for main group RENAMED via sql insert';
    const TEST_RENAMED_GROUP_BIG_FUNC = 'System Test Word for big group RENAMED via sql function';
    const TEST_RENAMED_GROUP_BIG_SQL = 'System Test Word for big group RENAMED via sql insert';
    const TEST_PARENT = 'System Test Word Parent';
    const TEST_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const TEST_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TEST_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const TEST_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const TEST_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const TEST_SECTOR = 'System Test Word with differentiator e.g. sector';
    const TEST_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const TEST_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const TEST_CASH = 'System Test Word multi levels e.g. Cash';
    const TEST_2021 = 'System Test Time Word e.g. 2021';
    const TEST_2022 = 'System Test Another Time Word e.g. 2022';
    const TEST_CHF = 'System Test Measure Word e.g. CHF';
    const TEST_SHARE = 'System Test Word Share';
    const TEST_PRICE = 'System Test Word Share Price';
    const TEST_EARNING = 'System Test Word Earnings';
    const TEST_PE = 'System Test Word PE Ratio';
    const TEST_IN_K = 'System Test Scaling Word e.g. thousands';
    const TEST_BIL = 'System Test Scaling Word e.g. billions';
    const TEST_TOTAL = 'System Test Word Total';
    const TEST_INCREASE = 'System Test Word Increase';
    const TEST_THIS = 'System Test Word This';
    const TEST_PRIOR = 'System Test Word Prior';
    const TEST_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const TEST_LATEST = 'System Test Word Latest';
    const TEST_SCALING_PCT = 'System Test Word Scaling Percent';
    const TEST_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const TEST_CALC = 'System Test Word Calc';
    const TEST_LAYER = 'System Test Word Layer';

    const TEST_ADD_API = 'System Test Word API';
    const TEST_ADD_API_COM = 'System Test Word API Description';
    const TEST_UPD_API = 'System Test Word API Renamed';
    const TEST_UPD_API_COM = 'System Test Word API Description Renamed';


    // list of predefined word names used for system testing that are expected to be never renamed
    const RESERVED_NAMES = array(
        triples::SYSTEM_CONFIG,
        self::MATH,
        self::CONST_NAME,
        self::PI,
        self::ONE,
        self::MIO,
        self::MIO_SHORT,
        self::COUNTRY,
        self::CH,
        self::GERMANY,
        self::CANTON,
        self::CITY,
        self::ZH,
        self::BE,
        self::GE,
        self::INHABITANTS,
        self::INHABITANTS,
        self::YEAR_CAP,
        self::YEAR_2015,
        self::YEAR_2016,
        self::YEAR_2017,
        self::YEAR_2018,
        self::YEAR_2019,
        self::YEAR_2020,
        self::PCT,
        self::COMPANY,
        self::TEST_ADD,
        self::TEST_ADD_VIA_FUNC,
        self::TEST_ADD_VIA_SQL,
        self::TEST_ADD_GROUP_MOST_FUNC,
        self::TEST_ADD_GROUP_MOST_SQL,
        self::TEST_ADD_GROUP_PRIME_FUNC,
        self::TEST_ADD_GROUP_PRIME_SQL,
        self::TEST_ADD_GROUP_BIG_FUNC,
        self::TEST_ADD_GROUP_BIG_SQL,
        self::TEST_RENAMED,
        self::TEST_RENAMED_GROUP_MOST_FUNC,
        self::TEST_RENAMED_GROUP_MOST_SQL,
        self::TEST_RENAMED_GROUP_PRIME_FUNC,
        self::TEST_RENAMED_GROUP_PRIME_SQL,
        self::TEST_RENAMED_GROUP_BIG_FUNC,
        self::TEST_RENAMED_GROUP_BIG_SQL,
        self::TEST_PARENT,
        self::TEST_FIN_REPORT,
        self::TEST_CASH_FLOW,
        self::TEST_TAX_REPORT,
        self::TEST_ASSETS,
        self::TEST_ASSETS_CURRENT,
        self::TEST_SECTOR,
        self::TEST_ENERGY,
        self::TEST_WIND_ENERGY,
        self::TEST_CASH,
        self::TEST_2021,
        self::TEST_2022,
        self::TEST_CHF,
        self::TEST_SHARE,
        self::TEST_PRICE,
        self::TEST_EARNING,
        self::TEST_PE,
        self::TEST_IN_K,
        self::TEST_BIL,
        self::TEST_TOTAL,
        self::TEST_INCREASE,
        self::TEST_THIS,
        self::TEST_PRIOR,
        self::TEST_TIME_JUMP,
        self::TEST_LATEST,
        self::TEST_SCALING_PCT,
        self::TEST_SCALING_MEASURE,
        self::TEST_CALC,
        self::TEST_LAYER,
        self::TEST_ADD_API,
        self::TEST_UPD_API
    );

    // array of word names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::MATH
    );

    // list of words that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_WORDS = array(
        self::TEST_ADD,
        self::TEST_ADD_VIA_FUNC,
        self::TEST_ADD_VIA_SQL,
        self::TEST_ADD_GROUP_PRIME_FUNC,
        self::TEST_ADD_GROUP_PRIME_SQL,
        self::TEST_ADD_GROUP_MOST_FUNC,
        self::TEST_ADD_GROUP_MOST_SQL,
        self::TEST_ADD_GROUP_BIG_FUNC,
        self::TEST_ADD_GROUP_BIG_SQL,
        self::TEST_RENAMED,
        self::TEST_PARENT,
        self::TEST_FIN_REPORT,
        self::TEST_CASH_FLOW,
        self::TEST_TAX_REPORT,
        self::TEST_ASSETS,
        self::TEST_ASSETS_CURRENT,
        self::TEST_SECTOR,
        self::TEST_ENERGY,
        self::TEST_WIND_ENERGY,
        self::TEST_CASH,
        self::TEST_2021,
        self::TEST_2022,
        self::TEST_CHF,
        self::TEST_SHARE,
        self::TEST_PRICE,
        self::TEST_EARNING,
        self::TEST_PE,
        self::TEST_IN_K,
        self::TEST_BIL,
        self::TEST_TOTAL,
        self::TEST_INCREASE,
        self::TEST_THIS,
        self::TEST_PRIOR,
        self::TEST_TIME_JUMP,
        self::TEST_LATEST,
        self::TEST_SCALING_PCT,
        self::TEST_SCALING_MEASURE,
        self::TEST_CALC,
        self::TEST_LAYER,
        self::TEST_ADD_API,
        self::TEST_UPD_API
    );
    // list of words that are used for system testing and that should be created before the system test starts
    const TEST_WORDS_CREATE = array(
        self::TEST_PARENT,
        self::TEST_FIN_REPORT,
        self::TEST_CASH_FLOW,
        self::TEST_TAX_REPORT,
        self::TEST_ASSETS,
        self::TEST_ASSETS_CURRENT,
        self::TEST_SECTOR,
        self::TEST_ENERGY,
        self::TEST_WIND_ENERGY,
        self::TEST_CASH,
        self::TEST_2021,
        self::TEST_2022,
        self::TEST_CHF,
        self::TEST_SHARE,
        self::TEST_PRICE,
        self::TEST_EARNING,
        self::TEST_PE,
        self::TEST_IN_K,
        self::TEST_BIL,
        self::TEST_TOTAL,
        self::TEST_INCREASE,
        self::TEST_THIS,
        self::TEST_PRIOR,
        self::TEST_TIME_JUMP,
        self::TEST_LATEST,
        self::TEST_SCALING_PCT,
        self::TEST_SCALING_MEASURE,
        self::TEST_CALC,
        self::TEST_LAYER,
        self::TEST_ADD_API,
        self::TEST_UPD_API
    );
    const TEST_WORDS_MEASURE = array(self::TEST_CHF);
    const TEST_WORDS_SCALING_HIDDEN = array(self::ONE);
    const TEST_WORDS_SCALING = array(self::TEST_IN_K, self::MIO, self::MIO_SHORT, self::TEST_BIL);
    const TEST_WORDS_PERCENT = array(self::PCT);
    // the time words must be in correct order because the following is set during creation
    const TEST_WORDS_TIME_YEAR = array(
        self::YEAR_2015,
        self::YEAR_2016,
        self::YEAR_2017,
        self::YEAR_2018,
        self::TEST_2021,
        self::TEST_2022
    );

    // list of words where the id is used for system testing
    const TEST_WORD_IDS = array(
        [self::ABB_ID, self::ABB],
        [self::BE_ID, self::BE],
        [self::BILLION_ID, self::BILLION],
        [self::CANTON_ID, self::CANTON],
        [self::CASH_ID, self::CASH],
        [self::FLOW_ID, self::FLOW],
        [self::STATEMENT_ID, self::STATEMENT],
        [self::CH_ID, self::CH],
        [self::CHF_ID, self::CHF],
        [self::CIRCUMFERENCE_ID, self::CIRCUMFERENCE],
        [self::CITY_ID, self::CITY],
        [self::CLIMATE_ID, self::CLIMATE],
        [self::COMPANY_ID, self::COMPANY],
        [self::CONST_ID, self::CONST_NAME],
        [self::DIAMETER_ID, self::DIAMETER],
        [self::E_ID, self::E],
        [self::EDUCATION_ID, self::EDUCATION],
        [self::GE_ID, self::GE],
        [self::GLOBAL_ID, self::GLOBAL],
        [self::HAPPY_ID, self::HAPPY],
        [self::HEALTH_ID, self::HEALTH],
        [self::HTP_ID, self::HTP],
        [self::INCOME_ID, self::INCOME],
        [self::INHABITANT_ID, self::INHABITANTS],
        [self::LAUNCH_ID, self::LAUNCH],
        [self::MASTER_POD_NAME_ID, self::MASTER_POD_NAME],
        [self::MATH_ID, self::MATH],
        [self::MINUTE_ID, self::MINUTE],
        [self::MIO_ID, self::MIO],
        [self::ONE_ID, self::ONE],
        [self::PARTS_ID, self::PARTS],
        [self::PCT_ID, self::PCT],
        [self::PI_ID, self::PI],
        [self::POD_ID, self::POD],
        [self::POINT_ID, self::POINT],
        [self::POINTS_ID, self::POINTS],
        [self::POPULISM_ID, self::POPULISM],
        [self::POVERTY_ID, self::POVERTY],
        [self::PRIOR_ID, self::PRIOR_NAME],
        [self::POTENTIAL_ID, self::POTENTIAL],
        [self::PROBLEM_ID, self::PROBLEM],
        [self::SALES_ID, self::SALES],
        [self::SECOND_ID, self::SECOND],
        [self::TAX_ID, self::TAX],
        [self::THIS_ID, self::THIS_NAME],
        [self::TIME_ID, self::TIME],
        [self::TOTAL_ID, self::TOTAL_PRE],
        [self::TRILLION_ID, self::TRILLION],
        [self::URL_ID, self::URL],
        [self::USD_ID, self::USD],
        [self::VESTAS_ID, self::VESTAS],
        [self::WARMER_ID, self::WARMER],
        [self::YEAR_2013_ID, self::YEAR_2013],
        [self::YEAR_2014_ID, self::YEAR_2014],
        [self::YEAR_2015_ID, self::YEAR_2015],
        [self::YEAR_2016_ID, self::YEAR_2016],
        [self::YEAR_2017_ID, self::YEAR_2017],
        [self::YEAR_2018_ID, self::YEAR_2018],
        [self::YEAR_2019_ID, self::YEAR_2019],
        [self::YEAR_2020_ID, self::YEAR_2020],
        [self::YEAR_CAP_ID, self::YEAR_CAP],
        [self::ZH_ID, self::ZH],
    );

}
