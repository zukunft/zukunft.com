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

    // keyword to select the system configuration
    const THIS_SYSTEM = 'zukunft.com';
    const SYSTEM = 'system';
    const CONFIGURATION = 'configuration';

    // words used to select parts of the system configuration where the normal name should not be changed
    const TOOLTIP_COMMENT_COM = 'keyword to read the word or triple description from the config.yaml';
    const TOOLTIP_COMMENT = 'tooltip-comment';
    const SYS_CONF_VALUE_COM = 'keyword to read the numeric value from the config.yaml';
    const SYS_CONF_VALUE = 'sys-conf-value';

    // for the system setup and all pods of zukunft.com
    const POD = 'pod';
    const POD_ID = 298;
    const MASTER_POD_NAME = 'zukunft.com';
    const MASTER_POD_NAME_ID = 314;
    const JOB = 'job';
    const USER = 'user';
    const FRONTEND = 'frontend';
    const BACKEND = 'backend';
    const LANGUAGE = 'language';

    // e.g. one instance / pod of zukunft.com
    const URL = 'url';
    const URL_ID = 309;
    // e.g. the launch date of the first beta version of zukunft.com
    const LAUNCH = 'launch';
    const LAUNCH_ID = 309;

    // for the user settings
    const ROW = 'row';
    const LIMIT = 'limit';
    const WORD = 'word';
    const TRIPLE = 'triple';
    const SOURCE = 'source';
    const FORMULA = 'formula';
    const CHANGES = 'changes';
    const PERCENT = 'percent';
    const DECIMAL = 'decimal';
    // e.g. the geolocation of the development of zukunft.com
    const POINT = 'point';
    const POINT_ID = 243;

    // general words used also for the system configuration that have a fixed tooltip
    const TIME = 'time';
    const TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const TIME_ID = 100;
    const YEAR = 'year';
    const YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const YEAR_CAP = 'Year';
    const YEAR_CAP_ID = 137;
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
    const EXPECTED = 'expected';
    const ENTRY = 'entry';
    const PRESELECT = 'preselect';
    const FUTURE = 'future';
    const COLUMNS = 'columns';
    const AUTOMATIC = 'automatic';
    const CREATE = 'create';
    const VIEW = 'view';
    const FREEZE = 'freeze';
    const CHANGE = 'change';
    const DAILY = 'daily';
    const IP = 'ip';

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
    const MATH = 'Mathematics';
    const MATH_COM = 'Mathematics is an area of knowledge that includes the topics of numbers and formulas';
    const MATH_ID = 1;
    const MATH_PLURAL = 'Mathematics';
    const CONST_NAME = 'constant';
    const CONST_COM = 'fixed and well-defined number';
    const CONST_ID = 2;
    const ONE = 'one';
    const ONE_ID = 3;
    const PI = 'Pi';
    const PI_ID = 4;
    const PI_COM = 'ratio of the circumference of a circle to its diameter';
    const CIRCUMFERENCE = 'circumference';
    const CIRCUMFERENCE_ID = 5;
    const DIAMETER = 'diameter';
    const DIAMETER_ID = 6;
    const E = "Euler's constant";
    const E_ID = 6;
    const SECOND = 'second';
    const SECOND_ID = 19;
    const MINUTE = 'minute';
    const MINUTE_ID = 101;
    const MIO = 'million';
    const MIO_ID = 170;
    const MIO_SHORT = 'mio';
    const COUNTRY = 'Country';
    const CH = 'Switzerland';
    const CH_ID = 197;
    const GERMANY = 'Germany';
    const CANTON = 'Canton';
    const CANTON_ID = 198;
    const CITY = 'City';
    const CITY_ID = 199;
    const ZH = 'Zurich';
    const ZH_ID = 200;
    const BE = 'Bern';
    const BE_ID = 201;
    const GE = 'Geneva';
    const GE_ID = 202;
    const INHABITANT = 'inhabitant';
    const INHABITANT_ID = 204;
    // TODO add test to search for words in all language forms e.g. plural
    const INHABITANTS = 'inhabitants';
    const YEAR_2013 = '2013';
    const YEAR_2013_ID = 326;
    const YEAR_2014 = '2014';
    const YEAR_2014_ID = 325;
    const YEAR_2015 = '2015';
    const YEAR_2015_ID = 205;
    const YEAR_2016 = '2016';
    const YEAR_2016_ID = 206;
    const YEAR_2017 = '2017';
    const YEAR_2017_ID = 207;
    const YEAR_2018 = '2018';
    const YEAR_2018_ID = 208;
    const YEAR_2019 = '2019';
    const YEAR_2019_ID = 142;
    const YEAR_2020 = '2020';
    const YEAR_2020_ID = 209;
    const YEAR_2020_COM = 'the year 2020';
    const PCT = 'percent';
    const PCT_ID = 172;
    // _PRE are the predefined words
    const THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const THIS_ID = 192;
    const PRIOR_NAME = 'prior';
    const PRIOR_ID = 194;
    const PARTS = 'parts';
    const PARTS_ID = 265;
    const TOTAL_PRE = 'total';
    const TOTAL_ID = 266;
    const COMPANY = 'Company';
    const COMPANY_ID = 322;
    const ABB = 'ABB';
    const ABB_ID = 323;
    const VESTAS = 'Vestas';
    const VESTAS_ID = 324;
    const CHF = 'CHF';
    const CHF_ID = 316;
    const SALES = 'Sales';
    const SALES_ID = 317;
    const CASH_FLOW = 'cash flow statement';
    const CASH_FLOW_ID = 274;
    const TAX = 'Income taxes';
    const TAX_ID = 273;

    const GLOBAL = 'global';
    const GLOBAL_ID = 216;

    const PROBLEM = 'problem';
    const PROBLEM_ID = 215;
    const CLIMATE = 'climate';
    const CLIMATE_ID = 222;
    const WARMER = 'warmer';
    const WARMER_ID = 225;
    const HEALTH = 'health';
    const HEALTH_ID = 235;
    const POPULISM = 'populism';
    const POPULISM_ID = 229;
    const POVERTY = 'poverty';
    const POVERTY_ID = 237;
    const EDUCATION = 'education';
    const EDUCATION_ID = 239;
    const HAPPY = 'happy';
    const HAPPY_ID = 242;
    const POINTS = 'points';
    const POINTS_ID = 244;
    const TRILLION = 'trillion';
    const TRILLION_ID = 248;
    const BILLION = 'billion';
    const BILLION_ID = 171;
    const USD = 'USD';
    const USD_ID = 251;
    const HTP = 'htp';
    const HTP_ID = 247;

    const GWP = 'global warming potential';
    const GWP_ID = 1070;

    // persevered word names for unit and integration tests based on the database
    // TWN_* - is a Test Word Name for words created only for testing (see also TN_*)
    const TEST_ADD = 'System Test Word';
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
    const TEST_SECTOR = 'System Test Word with differentiator e.g. Sector';
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
        self::INHABITANT,
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

}
