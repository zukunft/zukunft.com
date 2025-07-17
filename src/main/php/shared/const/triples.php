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

namespace shared\const;

class triples
{

    // this list includes all preserved triple names
    // *_COM is the tooltip for the triple; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing

    // keyword to select the system configuration
    const SYSTEM_CONFIG = 'system configuration';
    const SYSTEM_CONFIG_ID = 129;

    // triple names used for the system configuration
    const NUMBER_FORMAT = 'number format';
    const PERCENT_DECIMAL = 'percent decimal';
    const BLOCK_SIZE = 'block size';
    const AVERAGE_DELAY = 'average delay';
    const START_DELAY = 'start delay';
    const MAX_DELAY = 'max delay';
    const FILE_READ = 'file read';
    const OBJECT_CREATION = 'object creation';
    const OBJECT_STORING = 'object storing';
    const TIME_PERCENT = 'time percent';
    const EXPECTED_TIME = 'expected time';
    const BYTES_PER_SECOND = 'bytes second';
    const OBJECTS_PER_SECOND = 'objects second';
    const AUTOMATIC_CREATE = 'automatic create';
    const ROW_LIMIT = 'row limit';
    const RESPONSE_TIME = 'response time';
    const OUTPUT_FORMAT = 'output format';

    // triples included in the initial setup that are used for system testing
    const MATH_CONST = 'mathematical constant';
    const MATH_CONST_ID = 1;
    const MATH_CONST_COM = 'A mathematical constant that never changes e.g. Pi';
    const PI = 'Pi (math)';
    const PI_NAME = 'Pi (math)';
    const PI_ID = 44;
    const PI_COM = 'ratio of the circumference of a circle to its diameter';
    const PI_SYMBOL = 'π (unit symbol)';
    const PI_SYMBOL_NAME = 'π (unit symbol)';
    const PI_SYMBOL_ID = 2;
    const PI_SYMBOL_COM = 'ratio of the circumference of a circle to its diameter';
    const E = '𝑒 (unit symbol)';
    const E_ID = 3;
    const E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const SYSTEM_TEST_ADD = 'System Test Triple';
    const SYSTEM_TEST_ADD_COM = 'System Test Triple Description';
    const SYSTEM_TEST_ADD_AUTO = 'System Test Triple';
    const SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Triple added via sql function';
    const SYSTEM_TEST_ADD_VIA_SQL = 'System Test Triple added via prepared sql insert';

    // triple used in the default start view
    const GLOBAL_PROBLEM = 'global problem';
    const GLOBAL_PROBLEM_ID = 69;
    const GLOBAL_WARMING = 'global warming';
    const GLOBAL_WARMING_ID = 72;
    const GWP = 'global warming potential';
    const GWP_ID = 73;
    const TIME_POINTS = 'time points';
    const TIME_POINTS_ID = 76;
    const HAPPY_TIME_POINTS = 'happy time points';
    const HAPPY_TIME_POINTS_ID = 80;
    const GLOBAL_WARMING_PROBLEM = 'global warming (global problem)';
    const GLOBAL_WARMING_PROBLEM_ID = 81;
    const POPULISM_PROBLEM = 'populism (global problem)';
    const POPULISM_PROBLEM_ID = 82;
    const POTENTIAL_HEALTH_PROBLEM = 'health can be a global problem';
    const POTENTIAL_HEALTH_PROBLEM_ID = 83;
    const POVERTY_PROBLEM = 'poverty (global problem)';
    const POVERTY_PROBLEM_ID = 84;
    const POTENTIAL_EDUCATION_PROBLEM = 'education can be global problem';
    const POTENTIAL_EDUCATION_PROBLEM_ID = 85;
    const CASH_FLOW = 'cash flow';
    const CASH_FLOW_ID = 100;
    const CASH_FLOW_STATEMENT = 'cash flow statement';
    const CASH_FLOW_STATEMENT_ID = 101;
    const INCOME_TAX = 'income taxes';
    const INCOME_TAX_ID = 102;

    const TN_CUBIC_METER = 'm3';

    const CANTON_ZURICH = 'Zurich (Canton)';
    const CANTON_ZURICH_ID = 106;
    const CITY_ZH = 'Zurich (City)';
    const CITY_ZH_ID = 107;
    const CITY_ZH_NAME = 'City of Zurich';
    const CITY_ZH_COM = 'the city of Zurich';
    const CITY_BE = 'Bern (City)';
    const CITY_BE_ID = 108;
    const CITY_GE = 'Geneva (City)';
    const CITY_GE_ID = 109;
    const CANTON_ZURICH_NAME = 'Canton Zurich';
    const COMPANY_ZURICH = "Zurich Insurance";
    const COMPANY_VESTAS = "Vestas SA";
    const COMPANY_ABB = "ABB (Company)";
    const YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const TAXES_OF_CF = "income taxes is part of cash flow statement";

    // list of predefined triple used for system testing that are expected to be never renamed
    const RESERVED_NAMES = array(
        self::SYSTEM_CONFIG,
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::MATH_CONST
    );

    // list of triples that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_TRIPLES = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_ADD_VIA_SQL
    );

    const TEST_TRIPLE_STANDARD = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_EXCLUDED
    );

    // list of words where the id is used for system testing
    const TEST_TRIPLE_IDS = array(
        [self::CANTON_ZURICH_ID, self::CANTON_ZURICH],
        [self::CASH_FLOW_ID, self::CASH_FLOW],
        [self::CASH_FLOW_STATEMENT_ID, self::CASH_FLOW_STATEMENT],
        [self::CITY_BE_ID, self::CITY_BE],
        [self::CITY_GE_ID, self::CITY_GE],
        [self::CITY_ZH_ID, self::CITY_ZH],
        [self::E_ID, self::E],
        [self::GLOBAL_PROBLEM_ID, self::GLOBAL_PROBLEM],
        [self::GLOBAL_WARMING_ID, self::GLOBAL_WARMING],
        [self::GLOBAL_WARMING_PROBLEM_ID, self::GLOBAL_WARMING_PROBLEM],
        [self::GWP_ID, self::GWP],
        [self::HAPPY_TIME_POINTS_ID, self::HAPPY_TIME_POINTS],
        [self::INCOME_TAX_ID, self::INCOME_TAX],
        [self::MATH_CONST_ID, self::MATH_CONST],
        [self::PI_ID, self::PI_NAME],
        [self::PI_SYMBOL_ID, self::PI_SYMBOL_NAME],
        [self::POPULISM_PROBLEM_ID, self::POPULISM_PROBLEM],
        [self::POTENTIAL_EDUCATION_PROBLEM_ID, self::POTENTIAL_EDUCATION_PROBLEM],
        [self::POTENTIAL_HEALTH_PROBLEM_ID, self::POTENTIAL_HEALTH_PROBLEM],
        [self::POVERTY_PROBLEM_ID, self::POVERTY_PROBLEM],
        [self::SYSTEM_CONFIG_ID, self::SYSTEM_CONFIG],
        [self::TIME_POINTS_ID, self::TIME_POINTS],
    );

}
