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
    const SYSTEM_CONFIG_ID = 73;

    // triple names used for the system configuration
    const NUMBER_FORMAT = 'number format';
    const PERCENT_DECIMAL = 'percent decimal';
    const BLOCK_SIZE = 'block size';
    const AVERAGE_DELAY = 'average delay';
    const START_DELAY = 'start delay';
    const MAX_DELAY = 'max delay';

    // triples included in the initial setup that are used for system testing
    const MATH_CONST = 'Mathematical constant';
    const MATH_CONST_ID = 1;
    const MATH_CONST_COM = 'A mathematical constant that never changes e.g. Pi';
    const PI = 'Pi';
    const PI_NAME = 'Pi (math)';
    const PI_ID = 2;
    const PI_COM = 'ratio of the circumference of a circle to its diameter';
    const E = '𝑒 (math)';
    const E_ID = 3;
    const E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const SYSTEM_TEST_ADD = 'System Test Triple';
    const SYSTEM_TEST_ADD_AUTO = 'System Test Triple';
    const SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Triple added via sql function';
    const SYSTEM_TEST_ADD_VIA_SQL = 'System Test Triple added via prepared sql insert';

    // triple used in the default start view
    const GLOBAL_PROBLEM = 'global problem';
    const GLOBAL_PROBLEM_ID = 55;
    const GLOBAL_WARMING = 'climate warming';
    const GLOBAL_WARMING_ID = 58;
    const GLOBAL_WARMING_PROBLEM_ID = 65;
    const POPULISM_PROBLEM_ID = 66;
    const POTENTIAL_HEALTH_PROBLEM_ID = 67;
    const POVERTY_PROBLEM_ID = 68;
    const POTENTIAL_EDUCATION_PROBLEM_ID = 69;
    const TIME_POINTS = 'time points';
    const TIME_POINTS_ID = 61;
    const HAPPY_TIME_POINTS = 'happy time points';
    const HAPPY_TIME_POINTS_ID = 62;

    const TN_CUBIC_METER = 'm3';

    const CITY_ZH = 'Zurich (City)';
    const CITY_ZH_ID = 38;
    const CITY_ZH_NAME = 'City of Zurich';
    const CITY_ZH_COM = 'the city of Zurich';
    const CITY_BE = 'Bern (City)';
    const CITY_BE_ID = 39;
    const CITY_GE = 'Geneva (City)';
    const CITY_GE_ID = 40;
    const CANTON_ZURICH = 'Zurich (Canton)';
    const CANTON_ZURICH_ID = 37;
    const CANTON_ZURICH_NAME = 'Canton Zurich';
    const COMPANY_ZURICH = "Zurich Insurance";
    const COMPANY_VESTAS = "Vestas SA";
    const COMPANY_ABB = "ABB (Company)";
    const YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const TAXES_OF_CF = "Income taxes is part of cash flow statement";

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

}
