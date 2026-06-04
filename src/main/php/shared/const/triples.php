<?php

/*

    shared/const/triples.php - predefined triples used in the backend and frontend as code id
    ------------------------

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

class triples
{

    // this list includes all preserved triple names
    // *_COM is the tooltip for the triple; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing

    // keyword to select the system configuration
    const string SYSTEM_CONFIG = 'system configuration';
    const int SYSTEM_CONFIG_ID = 97;

    // triple names used for the system configuration
    const string NUMBER_FORMAT = 'number format';
    const string PERCENT_DECIMAL = 'percent decimal';
    const string BLOCK_SIZE = 'block size';
    const string AVERAGE_DELAY = 'average delay';
    const string START_DELAY = 'start delay';
    const string MAX_DELAY = 'max delay';
    const string FILE_READ = 'file read';
    const string OBJECT_CREATION = 'object creation';
    const string OBJECT_STORING = 'object storing';
    const string TIME_PERCENT = 'time percent';
    const string EXPECTED_TIME = 'expected time';
    const string BYTES_PER_SECOND = 'bytes second';
    const string OBJECTS_PER_SECOND = 'objects second';
    const string MAX_LEVELS = 'max levels';
    const string AUTOMATIC_CREATE = 'automatic create';
    const string ROW_LIMIT = 'row limit';
    const string LINK_LIST = 'link list';
    const string RESPONSE_TIME = 'response time';
    const string OUTPUT_FORMAT = 'output format';

    // triples included in the initial setup that are used for system testing
    const string MATH_CONST = 'mathematical constant';
    const string MATH_CONST_GIVEN = 'math const';
    const int MATH_CONST_ID = 1;
    const string MATH_CONST_COM = 'A mathematical constant that never changes e.g. Pi';
    const string PI = 'Pi (math)';
    const string PI_NAME = 'Pi (math)';
    const int PI_ID = 2;
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';
    const string PI_SYMBOL = 'π (unit symbol)';
    const string PI_SYMBOL_NAME = 'π (unit symbol)';
    const int PI_SYMBOL_ID = 4;
    const string PI_SYMBOL_COM = 'ratio of the circumference of a circle to its diameter';
    const string E = '𝑒 (unit symbol)';
    const int E_ID = 5;
    const string E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';

    // si units
    const string SPEED_OF_LIGHT = 'speed of light';
    const int SPEED_OF_LIGHT_ID = 50;
    const string SPEED_OF_LIGHT_COM = 'The speed of light in a vacuum is a universal physical constant defined exactly by the distance light travels in a specific fraction of a second.';
    const string M_PER_S = 'm/s';
    const int M_PER_S_ID = 67;
    const string M_PER_S_COM = 'The metre per second is the unit of both speed (a scalar quantity) and velocity (a vector quantity, which has direction and magnitude) in the International System of Units (SI), equal to the speed of a body covering a distance of one metre in a time of one second.';
    const string TRANSITION_CS = 'hyperfine transition frequency of Cs';
    const int TRANSITION_CS_ID = 83;
    const string TRANSITION_FREQUENCY = 'hyperfine transition frequency';
    const int TRANSITION_FREQUENCY_ID = 82;
    const string HYPERFINE_TRANSITION = 'hyperfine transition';
    const int HYPERFINE_TRANSITION_ID = 81;
    const string DEFINITION_YEAR_1983 = '1983 (year of definition)';
    const int DEFINITION_YEAR_1983_ID = 91;
    const string DEFINITION_YEAR_1967 = '1967 (year of definition)';
    const int DEFINITION_YEAR_1967_ID = 89;
    const string DEFINITION_YEAR = 'year of definition';
    const int DEFINITION_YEAR_ID = 88;
    const string YEAR_1983 = '1983 (year)';
    const int YEAR_1983_ID = 86;
    const string YEAR_1967 = '1967 (year)';
    const int YEAR_1967_ID = 84;
    const string YEAR_2019 = '2019 (year)';
    const int YEAR_2019_ID = 87;
    const string YEAR_2020 = '2020 (year)';
    const int YEAR_2020_ID = 147;
    const string YEAR_2021 = '2021 (year)';
    const int YEAR_2021_ID = 1156;
    const string YEAR_2022 = '2022 (year)';
    const int YEAR_2022_ID = 1155;
    const string YEAR_2023 = '2023 (year)';
    const int YEAR_2023_ID = 1154;
    const string YEAR_2024 = '2024 (year)';
    const int YEAR_2024_ID = 1153;
    const string YEAR_2025 = '2025 (year)';
    const int YEAR_2025_ID = 1152;
    const string YEAR_2026 = '2026 (year)';
    const int YEAR_2026_ID = 1151;
    const string YEAR_2027 = '2027 (year)';
    const int YEAR_2027_ID = 1150;
    const string YEAR_2028 = '2028 (year)';
    const int YEAR_2028_ID = 1149;
    const string YEAR_2029 = '2029 (year)';
    const int YEAR_2029_ID = 1148;
    const string YEAR_2030 = '2030 (year)';
    const int YEAR_2030_ID = 1147;


    const string SYSTEM_TEST_ADD = 'System Test Triple';
    const string SYSTEM_TEST_ADD_COM = 'System Test Triple Description';
    const string SYSTEM_TEST_ADD_AUTO = 'System Test Triple';
    const string SYSTEM_TEST_ADD_CODE_ID = 'System Test Triple Code Id';
    const int SYSTEM_TEST_ADD_USAGE = 12;
    const float SYSTEM_TEST_ADD_IMPACT = 23.4;
    const string SYSTEM_TEST_RENAMED = 'System Test Triple renamed';
    const string SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const string SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Triple added via sql function';

    // triple used in the default start view
    const string GLOBAL_PROBLEM = 'global problem';
    const int GLOBAL_PROBLEM_ID = 96;
    const string GLOBAL_WARMING = 'global warming';
    const int GLOBAL_WARMING_ID = 104;
    const string GWP = 'global warming potential';
    const int GWP_ID = 105;
    const string TIME_POINTS = 'time points';
    const int TIME_POINTS_ID = 108;
    const string HAPPY_TIME_POINTS = 'happy time points';
    const int HAPPY_TIME_POINTS_ID = 109;
    const string GLOBAL_WARMING_PROBLEM = 'global warming (global problem)';
    const int GLOBAL_WARMING_PROBLEM_ID = 112;
    const string POPULISM_PROBLEM = 'populism (global problem)';
    const int POPULISM_PROBLEM_ID = 113;
    const string POTENTIAL_HEALTH_PROBLEM = 'health can be a global problem';
    const int POTENTIAL_HEALTH_PROBLEM_ID = 114;
    const string POVERTY_PROBLEM = 'poverty (global problem)';
    const int POVERTY_PROBLEM_ID = 116;
    const string POTENTIAL_EDUCATION_PROBLEM = 'education can be global problem';
    const int POTENTIAL_EDUCATION_PROBLEM_ID = 117;
    const string CASH_FLOW = 'cash flow';
    const int CASH_FLOW_ID = 131;
    const string CASH_FLOW_STATEMENT = 'cash flow statement';
    const int CASH_FLOW_STATEMENT_ID = 132;
    const string INCOME_TAX = 'income taxes';
    const int INCOME_TAX_ID = 133;

    const string SECOND = 'second (time)';
    const int SECOND_ID = 20;
    const string TN_CUBIC_METER = 'm3';

    const string CANTON_ZURICH = 'Zurich (Canton)';
    const int CANTON_ZURICH_ID = 99;
    const string CITY_ZH = 'Zurich (City)';
    const int CITY_ZH_ID = 98;
    const string CITY_ZH_NAME = 'City of Zurich';
    const string CITY_ZH_COM = 'the city of Zurich';
    const string CITY_BE = 'Bern (City)';
    const int CITY_BE_ID = 100;
    const string CITY_GE = 'Geneva (City)';
    const int CITY_GE_ID = 101;
    const string CANTON_ZURICH_NAME = 'Canton Zurich';
    const string COMPANY_ZURICH = "Zurich Insurance";
    const int COMPANY_ZURICH_ID = 140;
    const string CHF_SYMBOL = "CHF is symbol for Swiss franc";
    const int CHF_SYMBOL_ID = 2333;
    const string COMPANY_VESTAS = "Vestas SA";
    const string COMPANY_ABB = "ABB (company)";
    const string YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const string TAXES_OF_CF = "income taxes is part of cash flow statement";

    // triples used in the system and user config.yaml
    const string API_USER = 'api user';
    const string BY_IP_ADDRESS = 'by ip-address';
    const string BY_LOCATION = 'by location';
    const string CHECK_PERIOD = 'check period';
    const string DECREASE_DAYS = 'decrease days';
    const string EMAIL_SERVER = 'email server';
    const string FACTOR_WRONG = 'factor wrong';
    const string FILE_SIZE = 'file size';
    const string FUTURE_PERCENT = 'future percent';
    const string INCREASE_LIST = 'increase list';
    const string IP_USER = 'ip user';
    const string MAX_CHANGE = 'max change';
    const string MAX_CHANGES = 'max changes';
    const string MAX_COLUMNS = 'max columns';
    const string MAX_LIFETIME = 'max lifetime';
    const string MAX_LOGIN = 'max login';
    const string MAX_PHRASE = 'max phrase';
    const string MAX_TABLES = 'max tables';
    const string MIN_COLUMNS = 'min columns';
    const string MIN_NAMES = 'min names';
    const string MIN_NUMBERS = 'min numbers';
    const string MIN_VALUES = 'min values';
    const string NAME_LIST = 'name list';
    const string NOT_TRUSTED = 'not trusted';
    const string PER_DAY = 'per day';
    const string PER_MONTH = 'per month';
    const string PER_VERB = 'per verb';
    const string PER_WEEK = 'per week';
    const string PER_YEAR = 'per year';
    const string PHRASE_DISTRIBUTION = 'phrase distribution';
    const string SOURCE_TABLE = 'source table';
    const string STORAGE_SIZE = 'storage size';
    const string TABLE_NAME = 'table name';
    const string TOP_LEVEL = 'top level';
    const string VALUE_TABLE = 'value table';
    const string WEB_MOBILE = 'web mobile';
    const string WORD_CHANGES = 'word changes';

    // list of often used triples used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up
    const array BASE_TRIPLES = [
        [self::MATH_CONST, self::MATH_CONST_ID],
        [self::CANTON_ZURICH, self::CANTON_ZURICH_ID],
        [self::CHF_SYMBOL, self::CHF_SYMBOL_ID],
        [self::CASH_FLOW, self::CASH_FLOW_ID],
        [self::CASH_FLOW_STATEMENT, self::CASH_FLOW_STATEMENT_ID],
        [self::CITY_BE, self::CITY_BE_ID],
        [self::CITY_GE, self::CITY_GE_ID],
        [self::CITY_ZH, self::CITY_ZH_ID],
        [self::COMPANY_ZURICH, self::COMPANY_ZURICH_ID],
        [self::E, self::E_ID],
        [self::GLOBAL_PROBLEM, self::GLOBAL_PROBLEM_ID],
        [self::GLOBAL_WARMING, self::GLOBAL_WARMING_ID],
        [self::GLOBAL_WARMING_PROBLEM, self::GLOBAL_WARMING_PROBLEM_ID],
        [self::GWP, self::GWP_ID],
        [self::HAPPY_TIME_POINTS, self::HAPPY_TIME_POINTS_ID],
        [self::INCOME_TAX, self::INCOME_TAX_ID],
        [self::PI, self::PI_ID],
        [self::PI_SYMBOL, self::PI_SYMBOL_ID],
        [self::POPULISM_PROBLEM, self::POPULISM_PROBLEM_ID],
        [self::POTENTIAL_EDUCATION_PROBLEM, self::POTENTIAL_EDUCATION_PROBLEM_ID],
        [self::POTENTIAL_HEALTH_PROBLEM, self::POTENTIAL_HEALTH_PROBLEM_ID],
        [self::POVERTY_PROBLEM, self::POVERTY_PROBLEM_ID],
        [self::TIME_POINTS, self::TIME_POINTS_ID],
    ];

    // list of predefined triple used for system testing that are expected to be never renamed
    const array RESERVED_NAMES = array(
        self::SYSTEM_CONFIG,
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_RENAMED,
        self::SYSTEM_TEST_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        self::MATH_CONST
    );

    // list of triples that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const array TEST_TRIPLES = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_RENAMED,
        self::SYSTEM_TEST_EXCLUDED,
    );

    // list of triples where the id is used for system testing
    const array TEST_TRIPLE_IDS = array(
        self::CANTON_ZURICH_ID => self::CANTON_ZURICH,
        self::CHF_SYMBOL_ID => self::CHF_SYMBOL,
        self::CASH_FLOW_ID => self::CASH_FLOW,
        self::CASH_FLOW_STATEMENT_ID => self::CASH_FLOW_STATEMENT,
        self::CITY_BE_ID => self::CITY_BE,
        self::CITY_GE_ID => self::CITY_GE,
        self::CITY_ZH_ID => self::CITY_ZH,
        self::COMPANY_ZURICH_ID => self::COMPANY_ZURICH,
        self::E_ID => self::E,
        self::SPEED_OF_LIGHT_ID => self::SPEED_OF_LIGHT,
        self::M_PER_S_ID => self::M_PER_S,
        self::TRANSITION_CS_ID => self::TRANSITION_CS,
        self::TRANSITION_FREQUENCY_ID => self::TRANSITION_FREQUENCY,
        self::HYPERFINE_TRANSITION_ID => self::HYPERFINE_TRANSITION,
        self::DEFINITION_YEAR_1983_ID => self::DEFINITION_YEAR_1983,
        self::DEFINITION_YEAR_1967_ID => self::DEFINITION_YEAR_1967,
        self::DEFINITION_YEAR_ID => self::DEFINITION_YEAR,
        self::YEAR_1983_ID => self::YEAR_1983,
        self::YEAR_1967_ID => self::YEAR_1967,
        self::GLOBAL_PROBLEM_ID => self::GLOBAL_PROBLEM,
        self::GLOBAL_WARMING_ID => self::GLOBAL_WARMING,
        self::GLOBAL_WARMING_PROBLEM_ID => self::GLOBAL_WARMING_PROBLEM,
        self::GWP_ID => self::GWP,
        self::HAPPY_TIME_POINTS_ID => self::HAPPY_TIME_POINTS,
        self::INCOME_TAX_ID => self::INCOME_TAX,
        self::MATH_CONST_ID => self::MATH_CONST,
        self::PI_ID => self::PI_NAME,
        self::PI_SYMBOL_ID => self::PI_SYMBOL_NAME,
        self::POPULISM_PROBLEM_ID => self::POPULISM_PROBLEM,
        self::POTENTIAL_EDUCATION_PROBLEM_ID => self::POTENTIAL_EDUCATION_PROBLEM,
        self::POTENTIAL_HEALTH_PROBLEM_ID => self::POTENTIAL_HEALTH_PROBLEM,
        self::POVERTY_PROBLEM_ID => self::POVERTY_PROBLEM,
        self::SYSTEM_CONFIG_ID => self::SYSTEM_CONFIG,
        self::TIME_POINTS_ID => self::TIME_POINTS,
    );

}
