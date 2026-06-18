<?php

/*

    test/php/const/triple_names.php - predefined triples used only for system testing
    --------------------------------

    the triples used in the backend and frontend are in main/php/shared/const/triples.php
    this separate class holds the test-only triples, ids and lists
    and references the shared config triples via the shared_triples alias


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

use Zukunft\ZukunftCom\main\php\shared\const\triples as shared_triples;

class triple_names
{

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
    const string SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the city of Zurich';
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
    // income statement concepts that the base setup defines as a triple (re-declared on XBRL import)
    const string GROSS_PROFIT = 'gross profit';
    const string COST_OF_REVENUE = 'cost of revenue';
    // the US GAAP accounting standard, built from the words "US" and "GAAP",
    // and its lower case XBRL taxonomy namespace prefix used in the concept ids e.g. "us-gaap_Revenues"
    const string US_GAAP = 'US GAAP';
    const string US_GAAP_XBRL = 'us-gaap';

    const string SECOND = 'second (time)';
    const int SECOND_ID = 20;
    const string TN_CUBIC_METER = 'm3';

    const string CANTON_ZURICH = 'Zurich (canton)';
    const int CANTON_ZURICH_ID = 99;
    const string CITY_ZH = 'Zurich (city)';
    const int CITY_ZH_ID = 98;
    const string CITY_ZH_NAME = 'city of Zurich';
    const string CITY_ZH_COM = 'the city of Zurich';
    const string CITY_BE = 'Bern (city)';
    const int CITY_BE_ID = 100;
    const string CITY_GE = 'Geneva (city)';
    const int CITY_GE_ID = 101;
    const string CANTON_ZURICH_NAME = 'canton Zurich';
    const string COMPANY_ZURICH = "Zurich Insurance";
    const int COMPANY_ZURICH_ID = 140;
    const string CHF_SYMBOL = "CHF is symbol for Swiss franc";
    const int CHF_SYMBOL_ID = 235;
    const int US_DOLLAR_ID = 123;
    const int EURO_ID = 122;
    const string USD_SYMBOL = "USD is symbol for US dollar";
    const int USD_SYMBOL_ID = 2866;
    const string DOLLAR_ALIAS = "$ is alias of US dollar";
    const int DOLLAR_ALIAS_ID = 2846;
    const string U_S_DOLLAR_ALIAS = "U.S. dollar is alias of US dollar";
    const int U_S_DOLLAR_ALIAS_ID = 2847;
    const string IN_USD = "in USD";
    const int IN_USD_ID = 2842;
    const string COMPANY_VESTAS = "Vestas SA";
    const int COMPANY_VESTAS_ID = 142;
    const string COMPANY_ABB = "ABB (company)";
    const int COMPANY_ABB_ID = 141;
    const string YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const string TAXES_OF_CF = "income taxes is part of cash flow statement";

    // list of often used triples used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up

    const array TEST_TRIPLES = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_RENAMED,
        self::SYSTEM_TEST_EXCLUDED,
    );

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
        shared_triples::SYSTEM_CONFIG_ID => shared_triples::SYSTEM_CONFIG,
        self::TIME_POINTS_ID => self::TIME_POINTS,
    );

}
