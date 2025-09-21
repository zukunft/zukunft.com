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

namespace Zukunft\ZukunftCom\main\php\shared\const;

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
    const int DEFAULT_WORD_ID = 1;

    // keywords to select the system configuration
    const string THIS_SYSTEM = 'zukunft.com';
    const string SYSTEM = 'system';
    const string CONFIGURATION = 'configuration';

    // words used to select parts of the system configuration where the normal name should not be changed
    const string TOOLTIP_COMMENT_COM = 'keyword to read the word or triple description from the config.yaml';
    const string TOOLTIP_COMMENT = 'tooltip-comment';
    const string SYS_CONF_VALUE_COM = 'keyword to read the numeric value from the config.yaml';
    const string SYS_CONF_VALUE = 'sys-conf-value';
    const string SYS_CONF_SOURCE = 'source-name';
    const string SYS_CONF_SOURCE_COM = 'source-description';
    const string SYS_CONF_USER = 'pod-user-config';
    const string SYS_CONF_USER_COM = 'keyword to read the user configuration for a specific user';

    // for the system setup and all pods of zukunft.com
    const string POD = 'pod';
    const int POD_ID = 203;
    const string MASTER_POD_NAME = 'zukunft.com';
    const int MASTER_POD_NAME_ID = 212;
    const string JOB = 'job';
    const string USER = 'user';
    const string FRONTEND = 'frontend';
    const string BACKEND = 'backend';
    const string LANGUAGE = 'language';
    const string BYTE = 'byte';

    // e.g. one instance / pod of zukunft.com
    const string URL = 'url';
    const int URL_ID = 207;
    // e.g. the launch date of the first beta version of zukunft.com
    const string LAUNCH = 'launch';
    const int LAUNCH_ID = 197;

    // for the user settings
    const string ROW = 'row';
    const string LIMIT = 'limit';
    const string WORD = 'word';
    const string TRIPLE = 'triple';
    const string SOURCE = 'source';
    const string FORMULA = 'formula';
    const string WORDS = 'words';
    const string VERBS = 'verbs';
    const string TRIPLES = 'triples';
    const string SOURCES = 'sources';
    const string REFERENCES = 'references';
    const string VALUES = 'values';
    const string FORMULAS = 'formulas';
    const string VIEWS = 'views';
    const string COMPONENTS = 'components';
    const string CHANGES = 'changes';
    const string PERCENT = 'percent';
    const string DECIMAL = 'decimal';

    // to exchange system configurations
    const string USERS = 'users';
    const string IP_RANGES = 'ip-ranges';

    // e.g. the geolocation of the development of zukunft.com
    const string POINT = 'point';
    const int POINT_ID = 204;

    // general words used also for the system configuration that have a fixed tooltip
    const string TIME = 'time';
    const string TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const int TIME_ID = 103;
    const string YEAR = 'year';
    const string YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const string YEAR_CAP = 'Year';
    const int YEAR_CAP_ID = 134;
    const string CALCULATION = 'calculation';
    const string CALCULATION_COM = 'A calculation is a deliberate mathematical process that transforms one or more inputs into one or more outputs or results';
    const string MIN = 'min';
    const string MIN_COM = 'The minimal numeric value.';
    const string MAX = 'max';
    const string MAX_COM = 'The maximal numeric value.';
    const string AVERAGE = 'average';
    const string AVERAGE_COM = 'The arithmetic mean – the sum of the numbers divided by how many numbers are in the list.';
    const string DEFAULT = 'default';
    const string DEFAULT_COM = 'The setting used if nothing else is specified.';
    const string DATABASE = 'database';
    const string DATABASE_COM = 'An organized collection of data stored and accessed electronically.';
    const string LISTS = 'lists';
    const string LISTS_COM = 'general parameters for lists e.g. the number of default entries';

    // words with a predefined functionality
    const string MOST = 'most';
    const string MOST_COM = 'also used by the system to force selecting only one value';
    const string RELEVANT = 'relevant';
    const string RELEVANT_COM = 'also used by the system to select some highlighted entries';

    // general words used also for the system configuration where the initial tooltip is in the config.yaml
    const string VALUE = 'value';
    const string VERSION = 'version';
    const string RETRY = 'retry';
    const string START = 'start';
    const string DELAY = 'delay';
    const string SEC = 'sec';
    const string BLOCK = 'block';
    const string SIZE = 'size';
    const string INSERT = 'insert';
    const string UPDATE = 'update';
    const string DELETE = 'delete';
    const string LOAD = 'load';
    const string TABLE = 'table';
    const string FILE = 'file';
    const string READ = 'read';
    const string NAME = 'name';
    const string PHRASE = 'phrase';
    const string MILLISECOND = 'millisecond';
    const string SELECT = 'select';
    const string INITIAL = 'initial';
    const string IMPORT = 'import';
    const string DECODE = 'decode';
    const string COUNT = 'count';
    const string EXPECTED = 'expected';
    const string ENTRY = 'entry';
    const string PRESELECT = 'preselect';
    const string FUTURE = 'future';
    const string COLUMNS = 'columns';
    const string AUTOMATIC = 'automatic';
    const string CREATE = 'create';
    const string STORE = 'store';
    const string VIEW = 'view';
    const string FREEZE = 'freeze';
    const string CHANGE = 'change';
    const string DAILY = 'daily';
    const string IP = 'ip';
    const string BEHAVIOUR = 'behaviour';

    // for the configuration of a single job
    // TODO complete the concrete setup
    const string IMPORT_TYPE = 'import type';
    const string API_WORD = 'API';
    // to group the user data and configuration within the system configuration
    const string PASSWORD = 'password';
    const string OPEN_API = 'OpenAPI';
    const string DEFINITION = 'definition';


    /*
     * const string for system testing
     */

    // word names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the word used for testing created with the initial setup (see also TWN_*)
    // TI_* is the database id based on the initial load
    // TD_* is the tooltip/description of the word

    // words from import file units.json in order of appearance
    const string MATH = 'mathematics';
    const string MATH_COM = 'Mathematics is an area of knowledge that includes the topics of numbers and formulas';
    const int MATH_ID = 1;
    const string MATH_PLURAL = 'mathematics';
    const string CONST_NAME = 'constant';
    const string CONST_COM = 'fixed and well-defined number';
    const int CONST_ID = 2;
    const string ONE = 'one';
    const int ONE_ID = 4;
    const string PI_SYMBOL = 'π';
    const int PI_SYMBOL_ID = 5;
    const string PI_SYMBOL_COM = 'Symbol for the ratio of the circumference of a circle to its diameter';
    const string PI = 'Pi';
    const int PI_ID = 17;
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';
    const string E_SYMBOL = "𝑒";
    const int E_SYMBOL_ID = 6;
    const string E = "Euler's number";
    const int E_ID = 18;
    const string CIRCUMFERENCE = 'circumference';
    const int CIRCUMFERENCE_ID = 15;
    const string DIAMETER = 'diameter';
    const int DIAMETER_ID = 16;
    const string SECOND = 'second';
    const int SECOND_ID = 24;
    const string FLOW = 'flow';
    const int FLOW_ID = 101;
    const string MINUTE = 'minute';
    const int MINUTE_ID = 104;
    const string YEAR_2019 = '2019';
    const int YEAR_2019_ID = 139;
    const string YEAR_2020 = '2020';
    const int YEAR_2020_ID = 140;
    const string YEAR_2020_COM = 'the year 2020';

    // words from import file scaling.json in order of appearance
    const string MIO = 'million';
    const int MIO_ID = 157;
    const string MIO_SHORT = 'mio';
    const string BILLION = 'billion';
    const int BILLION_ID = 158;
    const string PCT = 'percent';
    const int PCT_ID = 159;

    // words from import file time_definition.json in order of appearance
    const string THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const int THIS_ID = 179;
    const string PRIOR_NAME = 'prior';
    const int PRIOR_ID = 181;

    // words from import file base_phrases.json used for the offline phrase selection
    const string DAY = 'day';
    const int DAY_ID = 106;
    const string FACT = 'fact';
    const int FACT_ID = 190;
    const string GOVERNMENT = 'government';
    const int GOVERNMENT_ID = 192;
    const string GROUP = 'group';
    const int GROUP_ID = 194;
    const string HAND = 'hand';
    const int HAND_ID = 195;
    const string LIFE = 'life';
    const int LIFE_ID = 12;
    const string MAN = 'man';
    const int MAN_ID = 198;
    const string NUMBER = 'number';
    const int NUMBER_ID = 199;
    const string PART = 'part';
    const int PART_ID = 200;
    const string PERSON = 'person';
    const int PERSON_ID = 201;
    const string PLACE = 'place';
    const int PLACE_ID = 202;
    const string WAY = 'way';
    const int WAY_ID = 208;
    const string WEEK = 'week';
    const int WEEK_ID = 107;
    const string WOMAN = 'woman';
    const int WOMAN_ID = 209;
    const string WORK = 'work';
    const int WORK_ID = 210;
    const string WORLD = 'world';
    const int WORLD_ID = 211;

    // words from import file solution_prio.json used for the start page in order of appearance
    const string PROBLEM = 'problem';
    const int PROBLEM_ID = 205;
    const string GLOBAL = 'global';
    const int GLOBAL_ID = 193;
    const string POTENTIAL = 'potential';
    const int POTENTIAL_ID = 216;
    const string CLIMATE = 'climate';
    const int CLIMATE_ID = 219;
    const string WARMER = 'warmer';
    const int WARMER_ID = 222;
    const string POPULISM = 'populism';
    const int POPULISM_ID = 226;
    const string HEALTH = 'health';
    const int HEALTH_ID = 242;
    const string POVERTY = 'poverty';
    const int POVERTY_ID = 244;
    const string EDUCATION = 'education';
    const int EDUCATION_ID = 246;
    const string HAPPY = 'happy';
    const int HAPPY_ID = 249;
    const string POINTS = 'points';
    const int POINTS_ID = 251;
    const string HTP = 'htp';
    const int HTP_ID = 254;
    const string TRILLION = 'trillion';
    const int TRILLION_ID = 255;
    const string CHF = 'CHF';
    const int CHF_ID = 257;
    const string USD = 'USD';
    const int USD_ID = 258;

    // words from import file company.json used for the start page in order of appearance
    const string SALES = 'sales';
    const int SALES_ID = 280;
    const string CASH = 'cash';
    const int CASH_ID = 282;
    const string STATEMENT = 'statement';
    const int STATEMENT_ID = 283;
    const string PARTS = 'parts';
    const int PARTS_ID = 285;
    const string TOTAL_PRE = 'total';
    const int TOTAL_ID = 286;
    const string INCOME = 'income';
    const int INCOME_ID = 287;
    const string TAX = 'tax';
    const int TAX_ID = 288;

    // words from import file country.json used for the start page in order of appearance
    const string COUNTRY = 'Country';
    const string CH = 'Switzerland';
    const int CH_ID = 206;
    const string GERMANY = 'Germany';
    const string CANTON = 'Canton';
    const int CANTON_ID = 185;
    const string CITY = 'City';
    const int CITY_ID = 187;
    const string ZH = 'Zurich';
    const int ZH_ID = 213;
    const string BE = 'Bern';
    const int BE_ID = 184;
    const string GE = 'Geneva';
    const int GE_ID = 191;
    const int INHABITANT_ID = 196;
    // TODO add test to search for words in all language forms e.g. plural
    const string INHABITANTS = 'inhabitants';
    const string YEAR_2013 = '2013';
    const int YEAR_2013_ID = 293;
    const string YEAR_2014 = '2014';
    const int YEAR_2014_ID = 294;
    const string YEAR_2015 = '2015';
    const int YEAR_2015_ID = 295;
    const string YEAR_2016 = '2016';
    const int YEAR_2016_ID = 296;
    const string YEAR_2017 = '2017';
    const int YEAR_2017_ID = 297;
    const string YEAR_2018 = '2018';
    const int YEAR_2018_ID = 298;

    // words from import test file companies.json used for the start page in order of appearance
    const string COMPANY = 'company';
    const int COMPANY_ID = 188;
    const string ABB = 'ABB';
    const int ABB_ID = 1029;
    const string VESTAS = 'Vestas';
    const int VESTAS_ID = 1032;

    // for the config.yaml
    const string TEST = 'test';
    const string TEXT = 'text';
    const string HTML = 'html';
    const string LEVEL = 'level';
    const string ALL = 'all';
    const string TIMEOUTS = 'timeouts';
    const string WARNINGS = 'warnings';
    const string ERRORS = 'errors';

    // persevered word names for unit and integration tests based on the database
    // TWN_* - is a Test Word Name for words created only for testing (see also TN_*)
    const string TEST_ADD = 'System Test Word';
    const string TEST_ADD_COM = 'test description added to the word via import';
    const string TEST_ADD_TO = 'System Test Word To';
    const string TEST_ADD_VIA_FUNC = 'System Test Word added via sql function';
    const string TEST_ADD_VIA_SQL = 'System Test Word added via sql insert';
    const string TEST_ADD_GROUP_PRIME_FUNC = 'System Test Word for prime group add via sql function';
    const string TEST_ADD_GROUP_PRIME_SQL = 'System Test Word for prime group add via sql insert';
    const string TEST_ADD_GROUP_MOST_FUNC = 'System Test Word for main group add via sql function';
    const string TEST_ADD_GROUP_MOST_SQL = 'System Test Word for main group add via sql insert';
    const string TEST_ADD_GROUP_BIG_FUNC = 'System Test Word for big group add via sql function';
    const string TEST_ADD_GROUP_BIG_SQL = 'System Test Word for big group add via sql insert';
    const string TEST_RENAMED = 'System Test Word Renamed';
    const string TEST_RENAMED_GROUP_PRIME_FUNC = 'System Test Word for prime group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_PRIME_SQL = 'System Test Word for prime group RENAMED via sql insert';
    const string TEST_RENAMED_GROUP_MOST_FUNC = 'System Test Word for main group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_MOST_SQL = 'System Test Word for main group RENAMED via sql insert';
    const string TEST_RENAMED_GROUP_BIG_FUNC = 'System Test Word for big group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_BIG_SQL = 'System Test Word for big group RENAMED via sql insert';
    const string TEST_PARENT = 'System Test Word Parent';
    const string TEST_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const string TEST_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const string TEST_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const string TEST_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const string TEST_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const string TEST_SECTOR = 'System Test Word with differentiator e.g. sector';
    const string TEST_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const string TEST_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const string TEST_CASH = 'System Test Word multi levels e.g. Cash';
    const string TEST_2021 = 'System Test Time Word e.g. 2021';
    const string TEST_2022 = 'System Test Another Time Word e.g. 2022';
    const string TEST_CHF = 'System Test Measure Word e.g. CHF';
    const string TEST_SHARE = 'System Test Word Share';
    const string TEST_PRICE = 'System Test Word Share Price';
    const string TEST_EARNING = 'System Test Word Earnings';
    const string TEST_PE = 'System Test Word PE Ratio';
    const string TEST_IN_K = 'System Test Scaling Word e.g. thousands';
    const string TEST_BIL = 'System Test Scaling Word e.g. billions';
    const string TEST_TOTAL = 'System Test Word Total';
    const string TEST_INCREASE = 'System Test Word Increase';
    const string TEST_THIS = 'System Test Word This';
    const string TEST_PRIOR = 'System Test Word Prior';
    const string TEST_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const string TEST_LATEST = 'System Test Word Latest';
    const string TEST_SCALING_PCT = 'System Test Word Scaling Percent';
    const string TEST_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const string TEST_CALC = 'System Test Word Calc';
    const string TEST_LAYER = 'System Test Word Layer';

    const string TEST_ADD_API = 'System Test Word API';
    const string TEST_ADD_API_COM = 'System Test Word API Description';
    const string TEST_UPD_API = 'System Test Word API Renamed';
    const string TEST_UPD_API_COM = 'System Test Word API Description Renamed';
    const string TEST_ADD_VALUE = 'System Test Word for value curl testing';


    // list of often used words used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up
    const array BASE_WORDS = [
        [self::MATH, self::MATH_ID],
        [self::ABB, self::ABB_ID],
        [self::BE, self::BE_ID],
        [self::BILLION, self::BILLION_ID],
        [self::CANTON, self::CANTON_ID],
        [self::CASH, self::CASH_ID],
        [self::CH, self::CH_ID],
        [self::CHF, self::CHF_ID],
        [self::CIRCUMFERENCE, self::CIRCUMFERENCE_ID],
        [self::CITY, self::CITY_ID],
        [self::CLIMATE, self::CLIMATE_ID],
        [self::COMPANY, self::COMPANY_ID],
        [self::DAY, self::DAY_ID],
        [self::DIAMETER, self::DIAMETER_ID],
        [self::E, self::E_ID],
        [self::E_SYMBOL, self::E_SYMBOL_ID],
        [self::EDUCATION, self::EDUCATION_ID],
        [self::FACT, self::FACT_ID],
        [self::FLOW, self::FLOW_ID],
        [self::GE, self::GE_ID],
        [self::GLOBAL, self::GLOBAL_ID],
        [self::GOVERNMENT, self::GOVERNMENT_ID],
        [self::GROUP, self::GROUP_ID],
        [self::HAND, self::HAND_ID],
        [self::HAPPY, self::HAPPY_ID],
        [self::HEALTH, self::HEALTH_ID],
        [self::HTP, self::HTP_ID],
        [self::INCOME, self::INCOME_ID],
        [self::INHABITANTS, self::INHABITANT_ID],
        [self::LIFE, self::LIFE_ID],
        [self::MAN, self::MAN_ID],
        [self::MINUTE, self::MINUTE_ID],
        [self::MIO, self::MIO_ID],
        [self::NUMBER, self::NUMBER_ID],
        [self::ONE, self::ONE_ID],
        [self::PART, self::PART_ID],
        [self::PARTS, self::PARTS_ID],
        [self::PCT, self::PCT_ID],
        [self::PERSON, self::PERSON_ID],
        [self::PI, self::PI_ID],
        [self::PI_SYMBOL, self::PI_SYMBOL_ID],
        [self::PLACE, self::PLACE_ID],
        [self::POINTS, self::POINTS_ID],
        [self::POPULISM, self::POPULISM_ID],
        [self::POTENTIAL, self::POTENTIAL_ID],
        [self::POVERTY, self::POVERTY_ID],
        [self::PROBLEM, self::PROBLEM_ID],
        [self::SALES, self::SALES_ID],
        [self::SECOND, self::SECOND_ID],
        [self::STATEMENT, self::STATEMENT_ID],
        [self::TAX, self::TAX_ID],
        [self::TOTAL_PRE, self::TOTAL_ID],
        [self::TRILLION, self::TRILLION_ID],
        [self::USD, self::USD_ID],
        [self::VESTAS, self::VESTAS_ID],
        [self::WARMER, self::WARMER_ID],
        [self::WAY, self::WAY_ID],
        [self::WEEK, self::WEEK_ID],
        [self::WOMAN, self::WOMAN_ID],
        [self::WORK, self::WORK_ID],
        [self::WORLD, self::WORLD_ID],
        [self::YEAR_2013, self::YEAR_2013_ID],
        [self::YEAR_2014, self::YEAR_2014_ID],
        [self::YEAR_2015, self::YEAR_2015_ID],
        [self::YEAR_2016, self::YEAR_2016_ID],
        [self::YEAR_2017, self::YEAR_2017_ID],
        [self::YEAR_2018, self::YEAR_2018_ID],
        [self::YEAR_2019, self::YEAR_2019_ID],
        [self::YEAR_2020, self::YEAR_2020_ID],
        [self::ZH, self::ZH_ID],
    ];

    // list of predefined word names used for system testing that are expected to be never renamed
    const array RESERVED_NAMES = array(
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
    const array FIXED_NAMES = array(
        self::MATH
    );

    // list of words that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const array TEST_WORDS = array(
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
    const array TEST_WORDS_CREATE = array(
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
    const array TEST_WORDS_MEASURE = array(self::TEST_CHF);
    const array TEST_WORDS_SCALING_HIDDEN = array(self::ONE);
    const array TEST_WORDS_SCALING = array(self::TEST_IN_K, self::MIO, self::MIO_SHORT, self::TEST_BIL);
    const array TEST_WORDS_PERCENT = array(self::PCT);
    // the time words must be in correct order because the following is set during creation
    const array TEST_WORDS_TIME_YEAR = array(
        self::YEAR_2015,
        self::YEAR_2016,
        self::YEAR_2017,
        self::YEAR_2018,
        self::TEST_2021,
        self::TEST_2022
    );

    // list of words where the id is used for system testing
    const array TEST_WORD_IDS = array(
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
