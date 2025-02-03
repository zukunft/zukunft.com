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
    const CHANGES = 'changes';
    const PERCENT = 'percent';
    const DECIMAL = 'decimal';
    // e.g. the geolocation of the development of zukunft.com
    const POINT = 'point';
    const POINT_ID = 243;

    // general words used also for the system configuration that have a fixed tooltip
    const TIME = 'time';
    const TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
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
    const NAME = 'name';
    const PHRASE = 'phrase';
    const MILLISECOND = 'millisecond';
    const SELECT = 'select';
    const INITIAL = 'initial';
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
    const TN_2013 = '2013';
    const TI_2013 = 326;
    const TN_2014 = '2014';
    const TI_2014 = 325;
    const TN_2015 = '2015';
    const TI_2015 = 205;
    const TN_2016 = '2016';
    const TI_2016 = 206;
    const TN_2017 = '2017';
    const TI_2017 = 207;
    const TN_2018 = '2018';
    const TI_2018 = 208;
    const TN_2019 = '2019';
    const TI_2019 = 142;
    const TN_2020 = '2020';
    const TI_2020 = 209;
    const TN_PCT = 'percent';
    const TI_PCT = 172;
    // _PRE are the predefined words
    const THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const THIS_ID = 192;
    const PRIOR_NAME = 'prior';
    const PRIOR_ID = 194;
    const TN_PARTS = 'parts';
    const TI_PARTS = 265;
    const TN_TOTAL_PRE = 'total';
    const TI_TOTAL = 266;
    const TN_COMPANY = 'Company';
    const TI_COMPANY = 322;
    const TN_ABB = 'ABB';
    const TI_ABB = 323;
    const TN_VESTAS = 'Vestas';
    const TI_VESTAS = 324;
    const TN_CHF = 'CHF';
    const TI_CHF = 316;
    const TN_SALES = 'Sales';
    const TI_SALES = 317;
    const TN_CASH_FLOW = 'cash flow statement';
    const TI_CASH_FLOW = 274;
    const TN_TAX = 'Income taxes';
    const TI_TAX = 273;
    const TN_GWP = 'global warming potential';
    const TI_GWP = 1070;

    // persevered word names for unit and integration tests based on the database
    // TWN_* - is a Test Word Name for words created only for testing (see also TN_*)
    const TN_ADD = 'System Test Word';
    const TN_ADD_TO = 'System Test Word To';
    const TN_ADD_VIA_FUNC = 'System Test Word added via sql function';
    const TN_ADD_VIA_SQL = 'System Test Word added via sql insert';
    const TN_ADD_GROUP_PRIME_FUNC = 'System Test Word for prime group add via sql function';
    const TN_ADD_GROUP_PRIME_SQL = 'System Test Word for prime group add via sql insert';
    const TN_ADD_GROUP_MOST_FUNC = 'System Test Word for main group add via sql function';
    const TN_ADD_GROUP_MOST_SQL = 'System Test Word for main group add via sql insert';
    const TN_ADD_GROUP_BIG_FUNC = 'System Test Word for big group add via sql function';
    const TN_ADD_GROUP_BIG_SQL = 'System Test Word for big group add via sql insert';
    const TN_RENAMED = 'System Test Word Renamed';
    const TN_RENAMED_GROUP_PRIME_FUNC = 'System Test Word for prime group RENAMED via sql function';
    const TN_RENAMED_GROUP_PRIME_SQL = 'System Test Word for prime group RENAMED via sql insert';
    const TN_RENAMED_GROUP_MOST_FUNC = 'System Test Word for main group RENAMED via sql function';
    const TN_RENAMED_GROUP_MOST_SQL = 'System Test Word for main group RENAMED via sql insert';
    const TN_RENAMED_GROUP_BIG_FUNC = 'System Test Word for big group RENAMED via sql function';
    const TN_RENAMED_GROUP_BIG_SQL = 'System Test Word for big group RENAMED via sql insert';
    const TN_PARENT = 'System Test Word Parent';
    const TN_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const TWN_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TN_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const TN_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const TN_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const TN_SECTOR = 'System Test Word with differentiator e.g. Sector';
    const TN_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const TN_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const TN_CASH = 'System Test Word multi levels e.g. Cash';
    const TN_2021 = 'System Test Time Word e.g. 2021';
    const TN_2022 = 'System Test Another Time Word e.g. 2022';
    const TWN_CHF = 'System Test Measure Word e.g. CHF';
    const TN_SHARE = 'System Test Word Share';
    const TN_PRICE = 'System Test Word Share Price';
    const TN_EARNING = 'System Test Word Earnings';
    const TN_PE = 'System Test Word PE Ratio';
    const TN_IN_K = 'System Test Scaling Word e.g. thousands';
    const TN_BIL = 'System Test Scaling Word e.g. billions';
    const TN_TOTAL = 'System Test Word Total';
    const TN_INCREASE = 'System Test Word Increase';
    const TN_THIS = 'System Test Word This';
    const TN_PRIOR = 'System Test Word Prior';
    const TN_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const TN_LATEST = 'System Test Word Latest';
    const TN_SCALING_PCT = 'System Test Word Scaling Percent';
    const TN_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const TN_CALC = 'System Test Word Calc';
    const TN_LAYER = 'System Test Word Layer';

    const TN_ADD_API = 'System Test Word API';
    const TD_ADD_API = 'System Test Word API Description';
    const TN_UPD_API = 'System Test Word API Renamed';
    const TD_UPD_API = 'System Test Word API Description Renamed';


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
        self::TN_2015,
        self::TN_2016,
        self::TN_2017,
        self::TN_2018,
        self::TN_2019,
        self::TN_2020,
        self::TN_PCT,
        self::TN_COMPANY,
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_GROUP_MOST_FUNC,
        self::TN_ADD_GROUP_MOST_SQL,
        self::TN_ADD_GROUP_PRIME_FUNC,
        self::TN_ADD_GROUP_PRIME_SQL,
        self::TN_ADD_GROUP_BIG_FUNC,
        self::TN_ADD_GROUP_BIG_SQL,
        self::TN_RENAMED,
        self::TN_RENAMED_GROUP_MOST_FUNC,
        self::TN_RENAMED_GROUP_MOST_SQL,
        self::TN_RENAMED_GROUP_PRIME_FUNC,
        self::TN_RENAMED_GROUP_PRIME_SQL,
        self::TN_RENAMED_GROUP_BIG_FUNC,
        self::TN_RENAMED_GROUP_BIG_SQL,
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );

    // array of word names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::MATH
    );

    // list of words that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_WORDS = array(
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_GROUP_PRIME_FUNC,
        self::TN_ADD_GROUP_PRIME_SQL,
        self::TN_ADD_GROUP_MOST_FUNC,
        self::TN_ADD_GROUP_MOST_SQL,
        self::TN_ADD_GROUP_BIG_FUNC,
        self::TN_ADD_GROUP_BIG_SQL,
        self::TN_RENAMED,
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );
    // list of words that are used for system testing and that should be created before the system test starts
    const TEST_WORDS_CREATE = array(
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );
    const TEST_WORDS_MEASURE = array(self::TWN_CHF);
    const TEST_WORDS_SCALING_HIDDEN = array(self::ONE);
    const TEST_WORDS_SCALING = array(self::TN_IN_K, self::MIO, self::MIO_SHORT, self::TN_BIL);
    const TEST_WORDS_PERCENT = array(self::TN_PCT);
    // the time words must be in correct order because the following is set during creation
    const TEST_WORDS_TIME_YEAR = array(
        self::TN_2015,
        self::TN_2016,
        self::TN_2017,
        self::TN_2018,
        self::TN_2021,
        self::TN_2022
    );

}
