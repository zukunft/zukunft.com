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
    const int SYSTEM_CONFIG_ID = 92;

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
    const int PI_ID = 51;
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';
    const string PI_SYMBOL = 'π (unit symbol)';
    const string PI_SYMBOL_NAME = 'π (unit symbol)';
    const int PI_SYMBOL_ID = 2;
    const string PI_SYMBOL_COM = 'ratio of the circumference of a circle to its diameter';
    const string E = '𝑒 (unit symbol)';
    const int E_ID = 3;
    const string E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';

    // si units
    const string SPEED_OF_LIGHT = 'speed of light';
    const int SPEED_OF_LIGHT_ID = 26;
    const string SPEED_OF_LIGHT_COM = 'The speed of light in a vacuum is a universal physical constant defined exactly by the distance light travels in a specific fraction of a second.';
    const string M_PER_S = 'm/s';
    const int M_PER_S_ID = 63;
    const string M_PER_S_COM = 'The metre per second is the unit of both speed (a scalar quantity) and velocity (a vector quantity, which has direction and magnitude) in the International System of Units (SI), equal to the speed of a body covering a distance of one metre in a time of one second.';
    const string TRANSITION_CS = 'hyperfine transition frequency of Cs';
    const int TRANSITION_CS_ID = 78;
    const string TRANSITION_FREQUENCY = 'hyperfine transition frequency';
    const int TRANSITION_FREQUENCY_ID = 66;
    const string HYPERFINE_TRANSITION = 'hyperfine transition';
    const int HYPERFINE_TRANSITION_ID = 45;
    const string DEFINITION_YEAR_1983 = '1983 (year of definition)';
    const int DEFINITION_YEAR_1983_ID = 69;
    const string DEFINITION_YEAR_1967 = '1967 (year of definition)';
    const int DEFINITION_YEAR_1967_ID = 67;
    const string DEFINITION_YEAR = 'year of definition';
    const int DEFINITION_YEAR_ID = 50;
    const string YEAR_1983 = '1983 (year)';
    const int YEAR_1983_ID = 48;
    const string YEAR_1967 = '1967 (year)';
    const int YEAR_1967_ID = 46;


    const string SYSTEM_TEST_ADD = 'System Test Triple';
    const string SYSTEM_TEST_ADD_COM = 'System Test Triple Description';
    const string SYSTEM_TEST_ADD_AUTO = 'System Test Triple';
    const int SYSTEM_TEST_ADD_USAGE = 12;
    const float SYSTEM_TEST_ADD_IMPACT = 23.4;
    const string SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const string SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Triple added via sql function';
    const string SYSTEM_TEST_ADD_VIA_SQL = 'System Test Triple added via prepared sql insert';

    // triple used in the default start view
    const string GLOBAL_PROBLEM = 'global problem';
    const int GLOBAL_PROBLEM_ID = 91;
    const string GLOBAL_WARMING = 'global warming';
    const int GLOBAL_WARMING_ID = 99;
    const string GWP = 'global warming potential';
    const int GWP_ID = 100;
    const string TIME_POINTS = 'time points';
    const int TIME_POINTS_ID = 103;
    const string HAPPY_TIME_POINTS = 'happy time points';
    const int HAPPY_TIME_POINTS_ID = 111;
    const string GLOBAL_WARMING_PROBLEM = 'global warming (global problem)';
    const int GLOBAL_WARMING_PROBLEM_ID = 112;
    const string POPULISM_PROBLEM = 'populism (global problem)';
    const int POPULISM_PROBLEM_ID = 105;
    const string POTENTIAL_HEALTH_PROBLEM = 'health can be a global problem';
    const int POTENTIAL_HEALTH_PROBLEM_ID = 106;
    const string POVERTY_PROBLEM = 'poverty (global problem)';
    const int POVERTY_PROBLEM_ID = 108;
    const string POTENTIAL_EDUCATION_PROBLEM = 'education can be global problem';
    const int POTENTIAL_EDUCATION_PROBLEM_ID = 109;
    const string CASH_FLOW = 'cash flow';
    const int CASH_FLOW_ID = 126;
    const string CASH_FLOW_STATEMENT = 'cash flow statement';
    const int CASH_FLOW_STATEMENT_ID = 129;
    const string INCOME_TAX = 'income taxes';
    const int INCOME_TAX_ID = 127;

    const string TN_CUBIC_METER = 'm3';

    const string CANTON_ZURICH = 'Zurich (Canton)';
    const int CANTON_ZURICH_ID = 94;
    const string CITY_ZH = 'Zurich (City)';
    const int CITY_ZH_ID = 93;
    const string CITY_ZH_NAME = 'City of Zurich';
    const string CITY_ZH_COM = 'the city of Zurich';
    const string CITY_BE = 'Bern (City)';
    const int CITY_BE_ID = 95;
    const string CITY_GE = 'Geneva (City)';
    const int CITY_GE_ID = 96;
    const string CANTON_ZURICH_NAME = 'Canton Zurich';
    const string COMPANY_ZURICH = "Zurich Insurance";
    const string COMPANY_VESTAS = "Vestas SA";
    const string COMPANY_ABB = "ABB (company)";
    const string YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const string TAXES_OF_CF = "income taxes is part of cash flow statement";

    // list of often used triples used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up
    const array BASE_TRIPLES = [
        [self::MATH_CONST, self::MATH_CONST_ID],
        [self::CANTON_ZURICH, self::CANTON_ZURICH_ID],
        [self::CASH_FLOW, self::CASH_FLOW_ID],
        [self::CASH_FLOW_STATEMENT, self::CASH_FLOW_STATEMENT_ID],
        [self::CITY_BE, self::CITY_BE_ID],
        [self::CITY_GE, self::CITY_GE_ID],
        [self::CITY_ZH, self::CITY_ZH_ID],
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
        self::SYSTEM_TEST_ADD_VIA_SQL
    );

    const array TEST_TRIPLE_STANDARD = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_EXCLUDED
    );

    // list of words where the id is used for system testing
    const array TEST_TRIPLE_IDS = array(
        self::CANTON_ZURICH_ID => self::CANTON_ZURICH,
        self::CASH_FLOW_ID => self::CASH_FLOW,
        self::CASH_FLOW_STATEMENT_ID => self::CASH_FLOW_STATEMENT,
        self::CITY_BE_ID => self::CITY_BE,
        self::CITY_GE_ID => self::CITY_GE,
        self::CITY_ZH_ID => self::CITY_ZH,
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
