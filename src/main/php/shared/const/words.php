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

use Zukunft\ZukunftCom\test\php\const\word_names;

class words
{

    /*
     * config
     */

    // words used in the frontend and backend for the system configuration
    // code_id and name of a words used by the system for its own configuration
    // e.g. the number of decimal places related to the user-specific words
    // system configuration that are core for the database setup and update check are using the flat cfg methods
    //
    // *_COM is the tooltip for the word; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    //
    // this list is included in all preserved word names
    // if words have a predefined behavior instead of the code_id the phrase type is used

    // the standard word displayed to the user if she/he as not yet viewed any other word
    const int DEFAULT_WORD_ID = 1;

    // keywords to select the system configuration
    const string THIS_SYSTEM = 'zukunft.com';
    // TODO Prio 1 add a code_id to all words that are used by the system
    const string SYSTEM = 'system';
    const string SYSTEM_CODE_ID = 'system';
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
    const int POD_ID = 204;
    const string MASTER_POD_NAME = 'zukunft.com';
    const int MASTER_POD_NAME_ID = 213;
    const string JOB = 'job';
    const string USER = 'user';
    const string FRONTEND = 'frontend';
    const string BACKEND = 'backend';
    const string LANGUAGE = 'language';
    const string BYTE = 'byte';

    // for the user settings
    const string TRIPLE = 'triple';
    const string FORMULA = 'formula';

    // to exchange system configurations
    const string USERS = 'users';

    // words with a predefined functionality
    const string MOST = 'most';
    const string MOST_COM = 'also used by the system to force selecting only one value';
    const string RELEVANT = 'relevant';
    const string RELEVANT_COM = 'also used by the system to select some highlighted entries';

    /*
     * const string for system testing
     */

    // fixed word names used for the system configuration
    // in alphabetic order except the main keywords listed above
    const string ACCESS = 'access';
    const string ACCOUNT = 'account';
    const string API = 'api';
    const string API_WORD = 'API';
    const string AUTOMATIC = 'automatic';
    const string AVERAGE = 'average';
    const string AVERAGE_COM = 'The arithmetic mean – the sum of the numbers divided by how many numbers are in the list.';
    const string BEHAVIOUR = 'behaviour';
    const string BLOCK = 'block';
    const string BOTTOM = 'bottom';
    const string CACHE = 'cache';
    const string CALCULATION = 'calculation';
    const string CALCULATION_COM = 'A calculation is a deliberate mathematical process that transforms one or more inputs into one or more outputs or results';
    const string CATEGORY = 'category';
    const string CH = 'Switzerland';
    const int CH_ID = 207;
    const string CHANGE = 'change';
    const string CHANGES = 'changes';
    const string CHECK = 'check';
    const string CHF = 'CHF';
    const int CHF_ID = 258;
    const string COLUMNS = 'columns';
    const string COMBINATION = 'combination';
    const string COMPONENT = 'component';
    const string COMPONENTS = 'components';
    const string CONFIG = 'config';
    const string CONTRIBUTION = 'contribution';
    const string COUNTRY = 'country';
    const string CPU = 'CPU';
    const string CREATE = 'create';
    const string DAILY = 'daily';
    const string DATABASE = 'database';
    const string DATABASE_COM = 'An organized collection of data stored and accessed electronically.';
    const string DAY = 'day';
    const int DAY_ID = 106;
    const string DAYS = 'days';
    const string DECIMAL = 'decimal';
    const string DECODE = 'decode';
    const string DEFAULT = 'default';
    const string DEFAULT_COM = 'The setting used if nothing else is specified.';
    const string DELAY = 'delay';
    const string DELETE = 'delete';
    const string DETAIL = 'detail';
    const string ELEMENTS = 'elements';
    const string ENTRIES = 'entries';
    const string ENTRY = 'entry';
    const string ERRORS = 'errors';
    const string EXPECTED = 'expected';
    const string FACTORS = 'factors';
    const string FILE = 'file';
    const string FORMAT = 'format';
    const string FORMULAS = 'formulas';
    const string FREEZE = 'freeze';
    const string FUTURE = 'future';
    const string HAPPY = 'happy';
    const int HAPPY_ID = 250;
    const string HARDWARE = 'hardware';
    const string IMPACT = 'impact';
    const string IMPORT = 'import';
    const string INFO = 'info';
    const string INITIAL = 'initial';
    const string INSERT = 'insert';
    const string IP = 'ip';
    const string IP_RANGES = 'ip-ranges';
    const string LAUNCH = 'launch';
    const int LAUNCH_ID = 199;
    const string LAYOUT = 'layout';
    const string LAYOUT_COM = 'the settings to position the components on the screen';
    const string LEVEL = 'level';
    const string LIFETIME = 'lifetime';
    const string LIMIT = 'limit';
    const string LIMITS = 'limits';
    const string LIST = 'list';
    const string LISTS = 'lists';
    const string LISTS_COM = 'general parameters for lists e.g. the number of default entries';
    const string LOAD = 'load';
    const string LOG = 'log';
    const string MAX = 'max';
    const string MAX_COM = 'The maximal numeric value.';
    const string MB = 'MB';
    const string MEMORY = 'memory';
    const string MESSAGE = 'message';
    const string MILLISECOND = 'millisecond';
    const string MIN = 'min';
    const string MIN_COM = 'The minimal numeric value.';
    const string MONTH = 'month';
    const string MORE = 'more';
    const string NAME = 'name';
    const string NETWORK = 'network';
    const string NUMBER = 'number';
    const int NUMBER_ID = 201;
    const string PCT = 'percent';
    const int PCT_ID = 161;
    const string PEERS = 'peers';
    const string PERCENT = 'percent';
    const string PERIOD = 'period';
    const string PERMISSIONS = 'permissions';
    const string PHRASE = 'phrase';
    const string PODS = 'pods';
    const string POINT = 'point';
    const int POINT_ID = 205;
    const string POINTS = 'points';
    const int POINTS_ID = 252;
    const string PREDICTION = 'prediction';
    const string PRESELECT = 'preselect';
    const string RANK = 'rank';
    const string RANKING = 'ranking';
    const string READ = 'read';
    const string REFERENCES = 'references';
    const string RELATED = 'related';
    const string REMOVE = 'remove';
    const string RESULTS = 'results';
    const string RETRY = 'retry';
    const string RISK = 'risk';
    const string ROW = 'row';
    const string SAME = 'same';
    const string SEC = 'sec';
    const string SECOND = 'second';
    const int SECOND_ID = 24;
    const string SELECT = 'select';
    const string SELECTIONS = 'selections';
    const string SEPARATOR = 'separator';
    const string SIZE = 'size';
    const string SOURCE = 'source';
    const string SOURCES = 'sources';
    const string START = 'start';
    const string STORAGE = 'storage';
    const string STORE = 'store';
    const string SUGGESTED = 'suggested';
    const string TABLE = 'table';
    const string TARGET = 'target';
    const string TEST = 'test';
    const string THRESHOLD = 'threshold';
    const string TIME = 'time';
    const string TIME_COM = 'Time is the continued sequence of existence and events that occurs in an apparently irreversible succession from the past, through the present, into the future';
    const int TIME_ID = 102;
    const string TOLERANCE = 'tolerance';
    const string TOP = 'top';
    const string TOTAL_PRE = 'total';
    const int TOTAL_ID = 287;
    const string TRIPLES = 'triples';
    const string TRUSTED = 'trusted';
    const string TYPE = 'type';
    const string UPDATE = 'update';
    const string URL = 'url';
    const int URL_ID = 208;
    const string USAGE = 'usage';
    const string VALIDATE = 'validate';
    const string VALUE = 'value';
    const string VALUES = 'values';
    const string VERBS = 'verbs';
    const int VERBS_ID = 419;
    const string VERSION = 'version';
    const string VIEW = 'view';
    const string VIEWS = 'views';
    const string WARNING = 'warning';
    const string WEB = 'web';
    const string WEEK = 'week';
    const int WEEK_ID = 107;
    const string WEIGHTS = 'weights';
    const string WORD = 'word';
    const string WORDS = 'words';
    const string YEAR = 'year';
    const string YEAR_COM = 'A year is the time taken for astronomical objects to complete one orbit. For example, a year on Earth is the time taken for Earth to revolve around the Sun.';
    const string YEAR_CAP = 'year';
    const int YEAR_CAP_ID = 108;


    // list of often used words used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up
    const array BASE_WORDS = [
        [word_names::MATH, word_names::MATH_ID],
        [word_names::ABB, word_names::ABB_ID],
        [word_names::BE, word_names::BE_ID],
        [word_names::BILLION, word_names::BILLION_ID],
        [word_names::CANTON, word_names::CANTON_ID],
        [word_names::CASH, word_names::CASH_ID],
        [self::CH, self::CH_ID],
        [self::CHF, self::CHF_ID],
        [word_names::SWISS_FRANC, word_names::SWISS_FRANC_ID],
        [word_names::CIRCUMFERENCE, word_names::CIRCUMFERENCE_ID],
        [word_names::CITY, word_names::CITY_ID],
        [word_names::CLIMATE, word_names::CLIMATE_ID],
        [word_names::COMPANY, word_names::COMPANY_ID],
        [self::DAY, self::DAY_ID],
        [word_names::DIAMETER, word_names::DIAMETER_ID],
        [word_names::E, word_names::E_ID],
        [word_names::E_SYMBOL, word_names::E_SYMBOL_ID],
        [word_names::EDUCATION, word_names::EDUCATION_ID],
        [word_names::FACT, word_names::FACT_ID],
        [word_names::FLOW, word_names::FLOW_ID],
        [word_names::GE, word_names::GE_ID],
        [word_names::GLOBAL, word_names::GLOBAL_ID],
        [word_names::GOVERNMENT, word_names::GOVERNMENT_ID],
        [word_names::GROUP, word_names::GROUP_ID],
        [word_names::HAND, word_names::HAND_ID],
        [self::HAPPY, self::HAPPY_ID],
        [word_names::HEALTH, word_names::HEALTH_ID],
        [word_names::HTP, word_names::HTP_ID],
        [word_names::INCOME, word_names::INCOME_ID],
        [word_names::INHABITANTS, word_names::INHABITANT_ID],
        [word_names::LIFE, word_names::LIFE_ID],
        [word_names::MAN, word_names::MAN_ID],
        [word_names::MINUTE, word_names::MINUTE_ID],
        [word_names::MIO, word_names::MIO_ID],
        [self::NUMBER, self::NUMBER_ID],
        [word_names::ONE, word_names::ONE_ID],
        [word_names::PART, word_names::PART_ID],
        [word_names::PARTS, word_names::PARTS_ID],
        [self::PCT, self::PCT_ID],
        [word_names::PERSON, word_names::PERSON_ID],
        [word_names::PI, word_names::PI_ID],
        [word_names::PI_SYMBOL, word_names::PI_SYMBOL_ID],
        [word_names::PLACE, word_names::PLACE_ID],
        [self::POINTS, self::POINTS_ID],
        [word_names::POPULISM, word_names::POPULISM_ID],
        [word_names::POTENTIAL, word_names::POTENTIAL_ID],
        [word_names::POVERTY, word_names::POVERTY_ID],
        [word_names::PROBLEM, word_names::PROBLEM_ID],
        [word_names::SALES, word_names::SALES_ID],
        [self::SECOND, self::SECOND_ID],
        [word_names::STATEMENT, word_names::STATEMENT_ID],
        [word_names::TAX, word_names::TAX_ID],
        [self::TOTAL_PRE, self::TOTAL_ID],
        [word_names::TRILLION, word_names::TRILLION_ID],
        [word_names::USD, word_names::USD_ID],
        [word_names::VESTAS, word_names::VESTAS_ID],
        [word_names::WARMER, word_names::WARMER_ID],
        [word_names::WAY, word_names::WAY_ID],
        [self::WEEK, self::WEEK_ID],
        [word_names::WOMAN, word_names::WOMAN_ID],
        [word_names::WORK, word_names::WORK_ID],
        [word_names::WORLD, word_names::WORLD_ID],
        [word_names::YEAR_2013, word_names::YEAR_2013_ID],
        [word_names::YEAR_2014, word_names::YEAR_2014_ID],
        [word_names::YEAR_2015, word_names::YEAR_2015_ID],
        [word_names::YEAR_2016, word_names::YEAR_2016_ID],
        [word_names::YEAR_2017, word_names::YEAR_2017_ID],
        [word_names::YEAR_2018, word_names::YEAR_2018_ID],
        [word_names::YEAR_2019, word_names::YEAR_2019_ID],
        [word_names::YEAR_2020, word_names::YEAR_2020_ID],
        [word_names::ZH, word_names::ZH_ID],
    ];

    // list of predefined word names used for system testing that are expected to be never renamed
    const array RESERVED_NAMES = array(
        triples::SYSTEM_CONFIG,
        word_names::MATH,
        word_names::CONST_NAME,
        word_names::PI,
        word_names::ONE,
        word_names::MIO,
        word_names::MIO_SHORT,
        self::COUNTRY,
        self::CH,
        word_names::GERMANY,
        word_names::CANTON,
        word_names::CITY,
        word_names::ZH,
        word_names::BE,
        word_names::GE,
        word_names::INHABITANTS,
        word_names::INHABITANTS,
        self::YEAR_CAP,
        word_names::YEAR_2015,
        word_names::YEAR_2016,
        word_names::YEAR_2017,
        word_names::YEAR_2018,
        word_names::YEAR_2019,
        word_names::YEAR_2020,
        self::PCT,
        word_names::COMPANY,
        word_names::TEST_ADD,
        word_names::TEST_ADD_VIA_FUNC,
        word_names::TEST_ADD_GROUP_MOST_FUNC,
        word_names::TEST_ADD_GROUP_MOST_SQL,
        word_names::TEST_ADD_GROUP_PRIME,
        word_names::TEST_ADD_GROUP_PRIME_FUNC,
        word_names::TEST_ADD_GROUP_PRIME_SQL,
        word_names::TEST_ADD_GROUP_BIG_FUNC,
        word_names::TEST_ADD_GROUP_BIG_SQL,
        word_names::TEST_RENAMED,
        word_names::TEST_RENAMED_GROUP_MOST_FUNC,
        word_names::TEST_RENAMED_GROUP_MOST_SQL,
        word_names::TEST_RENAMED_GROUP_PRIME_FUNC,
        word_names::TEST_RENAMED_GROUP_PRIME_SQL,
        word_names::TEST_RENAMED_GROUP_BIG_FUNC,
        word_names::TEST_RENAMED_GROUP_BIG_SQL,
        word_names::TEST_PARENT,
        word_names::TEST_FIN_REPORT,
        word_names::TEST_CASH_FLOW,
        word_names::TEST_TAX_REPORT,
        word_names::TEST_ASSETS,
        word_names::TEST_ASSETS_CURRENT,
        word_names::TEST_SECTOR,
        word_names::TEST_ENERGY,
        word_names::TEST_WIND_ENERGY,
        word_names::TEST_CASH,
        word_names::TEST_2021,
        word_names::TEST_2022,
        word_names::TEST_CHF,
        word_names::TEST_SHARE,
        word_names::TEST_PRICE,
        word_names::TEST_EARNING,
        word_names::TEST_PE,
        word_names::TEST_IN_K,
        word_names::TEST_BIL,
        word_names::TEST_TOTAL,
        word_names::TEST_INCREASE,
        word_names::TEST_THIS,
        word_names::TEST_PRIOR,
        word_names::TEST_TIME_JUMP,
        word_names::TEST_LATEST,
        word_names::TEST_SCALING_PCT,
        word_names::TEST_SCALING_MEASURE,
        word_names::TEST_CALC,
        word_names::TEST_LAYER,
        word_names::TEST_ADD_API,
        word_names::TEST_UPD_API
    );

    // array of word names that used for system configuration or db read testing
    // and that should not be renamed for all users
    const array FIXED_NAMES = array(
        word_names::MATH,
        self::POD,
        self::JOB,
        self::USER,
        self::FRONTEND,
        self::BACKEND,
        self::LANGUAGE,
        self::BYTE,
        self::URL,
        self::LAUNCH,
        self::LIMITS,
        self::LIFETIME,
        self::CHECK,
        self::PERIOD,
        self::TOLERANCE,
    );

    // list of words that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const array TEST_WORDS = array(
        word_names::TEST_ADD,
        word_names::TEST_ADD_VIA_FUNC,
        word_names::TEST_ADD_GROUP_PRIME,
        word_names::TEST_ADD_GROUP_PRIME_FUNC,
        word_names::TEST_ADD_GROUP_PRIME_SQL,
        word_names::TEST_ADD_GROUP_MOST_FUNC,
        word_names::TEST_ADD_GROUP_MOST_SQL,
        word_names::TEST_ADD_GROUP_BIG_FUNC,
        word_names::TEST_ADD_GROUP_BIG_SQL,
        word_names::TEST_RENAMED,
        word_names::TEST_PARENT,
        word_names::TEST_FIN_REPORT,
        word_names::TEST_CASH_FLOW,
        word_names::TEST_TAX_REPORT,
        word_names::TEST_ASSETS,
        word_names::TEST_ASSETS_CURRENT,
        word_names::TEST_SECTOR,
        word_names::TEST_ENERGY,
        word_names::TEST_WIND_ENERGY,
        word_names::TEST_CASH,
        word_names::TEST_2021,
        word_names::TEST_2022,
        word_names::TEST_CHF,
        word_names::TEST_SHARE,
        word_names::TEST_PRICE,
        word_names::TEST_EARNING,
        word_names::TEST_PE,
        word_names::TEST_IN_K,
        word_names::TEST_BIL,
        word_names::TEST_TOTAL,
        word_names::TEST_INCREASE,
        word_names::TEST_THIS,
        word_names::TEST_PRIOR,
        word_names::TEST_TIME_JUMP,
        word_names::TEST_LATEST,
        word_names::TEST_SCALING_PCT,
        word_names::TEST_SCALING_MEASURE,
        word_names::TEST_CALC,
        word_names::TEST_LAYER,
        word_names::TEST_ADD_API,
        word_names::TEST_UPD_API
    );
    // list of words that are used for system testing and that should be created before the system test starts
    const array TEST_WORDS_CREATE = array(
        word_names::TEST_PARENT,
        word_names::TEST_FIN_REPORT,
        word_names::TEST_CASH_FLOW,
        word_names::TEST_TAX_REPORT,
        word_names::TEST_ASSETS,
        word_names::TEST_ASSETS_CURRENT,
        word_names::TEST_SECTOR,
        word_names::TEST_ENERGY,
        word_names::TEST_WIND_ENERGY,
        word_names::TEST_CASH,
        word_names::TEST_2021,
        word_names::TEST_2022,
        word_names::TEST_CHF,
        word_names::TEST_SHARE,
        word_names::TEST_PRICE,
        word_names::TEST_EARNING,
        word_names::TEST_PE,
        word_names::TEST_IN_K,
        word_names::TEST_BIL,
        word_names::TEST_TOTAL,
        word_names::TEST_INCREASE,
        word_names::TEST_THIS,
        word_names::TEST_PRIOR,
        word_names::TEST_TIME_JUMP,
        word_names::TEST_LATEST,
        word_names::TEST_SCALING_PCT,
        word_names::TEST_SCALING_MEASURE,
        word_names::TEST_CALC,
        word_names::TEST_LAYER,
        word_names::TEST_ADD_API,
        word_names::TEST_UPD_API
    );
    const array TEST_WORDS_MEASURE = array(word_names::TEST_CHF);
    const array TEST_WORDS_SCALING_HIDDEN = array(word_names::ONE);
    const array TEST_WORDS_SCALING = array(word_names::TEST_IN_K, word_names::MIO, word_names::MIO_SHORT, word_names::TEST_BIL);
    const array TEST_WORDS_PERCENT = array(self::PCT);
    // the time words must be in correct order because the following is set during creation
    const array TEST_WORDS_TIME_YEAR = array(
        word_names::YEAR_2015,
        word_names::YEAR_2016,
        word_names::YEAR_2017,
        word_names::YEAR_2018,
        word_names::TEST_2021,
        word_names::TEST_2022
    );

    // list of words where the id is used for system testing
    const array TEST_WORD_IDS = array(
        word_names::ABB_ID => word_names::ABB,
        word_names::BE_ID => word_names::BE,
        word_names::BILLION_ID => word_names::BILLION,
        word_names::CANTON_ID => word_names::CANTON,
        word_names::CASH_ID => word_names::CASH,
        word_names::FLOW_ID => word_names::FLOW,
        word_names::STATEMENT_ID => word_names::STATEMENT,
        self::CH_ID => self::CH,
        self::CHF_ID => self::CHF,
        word_names::SWISS_FRANC_ID => word_names::SWISS_FRANC,
        word_names::CIRCUMFERENCE_ID => word_names::CIRCUMFERENCE,
        word_names::CITY_ID => word_names::CITY,
        word_names::CLIMATE_ID => word_names::CLIMATE,
        word_names::COMPANY_ID => word_names::COMPANY,
        word_names::CONST_ID => word_names::CONST_NAME,
        word_names::DIAMETER_ID => word_names::DIAMETER,
        word_names::E_ID => word_names::E,
        word_names::EDUCATION_ID => word_names::EDUCATION,
        word_names::GE_ID => word_names::GE,
        word_names::GLOBAL_ID => word_names::GLOBAL,
        self::HAPPY_ID => self::HAPPY,
        word_names::HEALTH_ID => word_names::HEALTH,
        word_names::HTP_ID => word_names::HTP,
        word_names::INCOME_ID => word_names::INCOME,
        word_names::INHABITANT_ID => word_names::INHABITANTS,
        self::LAUNCH_ID => self::LAUNCH,
        self::MASTER_POD_NAME_ID => self::MASTER_POD_NAME,
        word_names::MATH_ID => word_names::MATH,
        word_names::MINUTE_ID => word_names::MINUTE,
        word_names::MIO_ID => word_names::MIO,
        word_names::ONE_ID => word_names::ONE,
        word_names::PARTS_ID => word_names::PARTS,
        self::PCT_ID => self::PCT,
        word_names::PI_ID => word_names::PI,
        self::POD_ID => self::POD,
        self::POINT_ID => self::POINT,
        self::POINTS_ID => self::POINTS,
        word_names::POPULISM_ID => word_names::POPULISM,
        word_names::POVERTY_ID => word_names::POVERTY,
        word_names::PRIOR_ID => word_names::PRIOR_NAME,
        word_names::POTENTIAL_ID => word_names::POTENTIAL,
        word_names::PROBLEM_ID => word_names::PROBLEM,
        word_names::SALES_ID => word_names::SALES,
        self::SECOND_ID => self::SECOND,
        word_names::TAX_ID => word_names::TAX,
        word_names::THIS_ID => word_names::THIS_NAME,
        self::TIME_ID => self::TIME,
        self::TOTAL_ID => self::TOTAL_PRE,
        word_names::TRILLION_ID => word_names::TRILLION,
        self::URL_ID => self::URL,
        word_names::USD_ID => word_names::USD,
        word_names::VESTAS_ID => word_names::VESTAS,
        word_names::WARMER_ID => word_names::WARMER,
        word_names::YEAR_2013_ID => word_names::YEAR_2013,
        word_names::YEAR_2014_ID => word_names::YEAR_2014,
        word_names::YEAR_2015_ID => word_names::YEAR_2015,
        word_names::YEAR_2016_ID => word_names::YEAR_2016,
        word_names::YEAR_2017_ID => word_names::YEAR_2017,
        word_names::YEAR_2018_ID => word_names::YEAR_2018,
        word_names::YEAR_2019_ID => word_names::YEAR_2019,
        word_names::YEAR_2020_ID => word_names::YEAR_2020,
        word_names::LIGHT_ID => word_names::LIGHT,
        word_names::SPEED_ID => word_names::SPEED,
        word_names::METRE_ID => word_names::METRE,
        word_names::HYPERFINE_ID => word_names::HYPERFINE,
        word_names::TRANSITION_ID => word_names::TRANSITION,
        word_names::FREQUENCY_ID => word_names::FREQUENCY,
        word_names::CS_133_ID => word_names::CS_133,
        word_names::HZ_ID => word_names::HZ,
        word_names::DEFINITION_ID => word_names::DEFINITION,
        word_names::YEAR_1983_ID => word_names::YEAR_1983,
        word_names::YEAR_1967_ID => word_names::YEAR_1967,
        self::YEAR_CAP_ID => self::YEAR_CAP,
        word_names::ZH_ID => word_names::ZH,
    );

}
