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
    const string PI_SYMBOL = 'π (unit symbol)';
    const string PI_SYMBOL_NAME = 'π (unit symbol)';
    const int PI_SYMBOL_ID = 5;
    const string PI_SYMBOL_COM = 'ratio of the circumference of a circle to its diameter';
    // "Euler's number" is built in units.json as the triple Euler 'name of' number,
    // so it is a triple phrase and no longer a word; it also covers the >'< handling
    // "Caesium-133" is built in units.json as the triple Caesium 'kind of' 133,
    // so it is a triple phrase and no longer a word
    const string CS_133 = 'Caesium-133';
    const int CS_133_ID = 49;
    const string CS_133_COM = 'Caesium-133 is the only stable isotope of caesium. The SI base unit of time, the second, is defined by the unperturbed ground-state hyperfine transition frequency of this atom, set at 9192631770 Hz.';
    const string EULER_NUMBER = "Euler's number";
    const int EULER_NUMBER_ID = 2;
    const string EULER_NUMBER_COM = 'The number e is a mathematical constant approximately equal to 2.71828 that is the base of the natural logarithm and exponential function.';
    const string E = '𝑒 (unit symbol)';
    const int E_ID = 58;
    const string E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const string PI = 'Pi (math)';
    const string PI_NAME = 'Pi (math)';
    const int PI_ID = 56;
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';

    // si units
    const string SPEED_OF_LIGHT = 'speed of light';
    const int SPEED_OF_LIGHT_ID = 29;
    const string SPEED_OF_LIGHT_COM = 'The speed of light in a vacuum is a universal physical constant defined exactly by the distance light travels in a specific fraction of a second.';
    const string M_PER_S = 'm/s';
    const int M_PER_S_ID = 71;
    const string M_PER_S_COM = 'The metre per second is the unit of both speed (a scalar quantity) and velocity (a vector quantity, which has direction and magnitude) in the International System of Units (SI), equal to the speed of a body covering a distance of one metre in a time of one second.';
    const string TRANSITION_CS = 'hyperfine transition frequency of Cs';
    const int TRANSITION_CS_ID = 89;
    const string TRANSITION_FREQUENCY = 'hyperfine transition frequency';
    const int TRANSITION_FREQUENCY_ID = 75;
    const string HYPERFINE_TRANSITION = 'hyperfine transition';
    const int HYPERFINE_TRANSITION_ID = 48;
    const string DEFINITION_YEAR_1983 = '1983 (year of definition)';
    const int DEFINITION_YEAR_1983_ID = 78;
    const string DEFINITION_YEAR_1967 = '1967 (year of definition)';
    const int DEFINITION_YEAR_1967_ID = 76;
    const string DEFINITION_YEAR = 'year of definition';
    const int DEFINITION_YEAR_ID = 54;
    const string YEAR_1983 = '1983 (year)';
    const int YEAR_1983_ID = 52;
    const string YEAR_1967 = '1967 (year)';
    const int YEAR_1967_ID = 50;
    const string YEAR_2019 = '2019 (year)';
    const int YEAR_2019_ID = 53;
    const string YEAR_2020 = '2020 (year)';
    const int YEAR_2020_ID = 218;
    const string YEAR_2021 = '2021 (year)';
    const int YEAR_2021_ID = 560;
    const string YEAR_2022 = '2022 (year)';
    const int YEAR_2022_ID = 559;
    const string YEAR_2023 = '2023 (year)';
    const int YEAR_2023_ID = 558;
    const string YEAR_2024 = '2024 (year)';
    const int YEAR_2024_ID = 557;
    const string YEAR_2025 = '2025 (year)';
    const int YEAR_2025_ID = 556;
    const string YEAR_2026 = '2026 (year)';
    const int YEAR_2026_ID = 555;
    const string YEAR_2027 = '2027 (year)';
    const int YEAR_2027_ID = 554;
    const string YEAR_2028 = '2028 (year)';
    const int YEAR_2028_ID = 553;
    const string YEAR_2029 = '2029 (year)';
    const int YEAR_2029_ID = 552;
    const string YEAR_2030 = '2030 (year)';
    const int YEAR_2030_ID = 551;

    const string SYSTEM_TEST_ADD = 'System Test Triple';
    const int SYSTEM_TEST_ADD_ID = 998; // fixed snapshot id of the add/del workflow triple (like word_names::TEST_ADD_ID)
    const string SYSTEM_TEST_ADD_COM = 'System Test Triple Description';
    // the given name that overwrites the generated '<from> <verb> <to>' name of the test triple
    const string SYSTEM_TEST_ADD_GIVEN = 'System Test Triple Name Given';
    const string SYSTEM_TEST_ADD_AUTO = 'System Test Triple';
    const string SYSTEM_TEST_ADD_CODE_ID = 'System Test Triple Code Id';
    const int SYSTEM_TEST_ADD_USAGE = 12;
    const float SYSTEM_TEST_ADD_IMPACT = 23.4;
    const string SYSTEM_TEST_RENAMED = 'System Test Triple renamed';
    const string SYSTEM_TEST_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the city of Zurich';
    const string SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Triple added via sql function';

    // triple used in the default start view
    const string GLOBAL_PROBLEM = 'global problem';
    const int GLOBAL_PROBLEM_ID = 102;
    const string GLOBAL_WARMING = 'global warming';
    const int GLOBAL_WARMING_ID = 110;
    const string GWP = 'global warming potential';
    const int GWP_ID = 111;
    const string TIME_POINTS = 'time points';
    const int TIME_POINTS_ID = 114;
    const string HAPPY_TIME_POINTS = 'happy time points';
    const int HAPPY_TIME_POINTS_ID = 150;
    const string GLOBAL_WARMING_PROBLEM = 'global warming (global problem)';
    const int GLOBAL_WARMING_PROBLEM_ID = 151;
    const string POPULISM_PROBLEM = 'populism (global problem)';
    const int POPULISM_PROBLEM_ID = 116;
    const string POTENTIAL_HEALTH_PROBLEM = 'health can be a global problem';
    const int POTENTIAL_HEALTH_PROBLEM_ID = 117;
    const string POVERTY_PROBLEM = 'poverty (global problem)';
    const int POVERTY_PROBLEM_ID = 119;
    const string POTENTIAL_EDUCATION_PROBLEM = 'education can be global problem';
    const int POTENTIAL_EDUCATION_PROBLEM_ID = 120;
    // the solution triples and the table column definitions of solution_prio.json
    const string REDUCE_EMISSIONS = 'reduce climate gas emissions';
    const int REDUCE_EMISSIONS_ID = 168;
    const string AVOID_WRONG_DECISIONS = 'avoid wrong decisions';
    const int AVOID_WRONG_DECISIONS_ID = 158;
    // the triples that link a solution to "solution", so that the table can name the solution
    // of a problem row in a column of its own (like the problem links for the rows)
    const string REDUCE_EMISSIONS_SOLUTION = 'reduce climate gas emissions (solution)';
    const int REDUCE_EMISSIONS_SOLUTION_ID = 186;
    const string AVOID_WRONG_DECISIONS_SOLUTION = 'avoid wrong decisions (solution)';
    const int AVOID_WRONG_DECISIONS_SOLUTION_ID = 169;
    const string RESEARCH_SOLUTION = 'research (solution)';
    const int RESEARCH_SOLUTION_ID = 135;
    const string TAXES_SOLUTION = 'taxes (solution)';
    const int TAXES_SOLUTION_ID = 136;
    const string SPENDING_SOLUTION = 'spending (solution)';
    const int SPENDING_SOLUTION_ID = 137;
    // the problems of solution_prio.json that are a triple, ordered like the start page ranking
    const string WEALTH_CONCENTRATION = 'wealth concentration';
    const int WEALTH_CONCENTRATION_ID = 127;
    const string MARKET_POWER = 'market power';
    const int MARKET_POWER_ID = 128;
    const string BIASED_INFORMATION = 'biased information';
    const int BIASED_INFORMATION_ID = 129;
    const string BLACK_BOX_AI = 'black-box AI';
    const int BLACK_BOX_AI_ID = 156;
    const string CITIZEN_PARTICIPATION = 'citizen participation';
    const int CITIZEN_PARTICIPATION_ID = 131;
    const string GDP_MISMEASUREMENT = 'GDP mismeasurement';
    const int GDP_MISMEASUREMENT_ID = 132;
    const string PROPRIETARY_SOFTWARE = 'proprietary software';
    const int PROPRIETARY_SOFTWARE_ID = 133;
    // the solutions that solution_prio.json assigns to these problems
    const string BASIC_INCOME = 'basic income';
    const int BASIC_INCOME_ID = 140;
    const string PLATFORM_REGULATION = 'platform regulation';
    const int PLATFORM_REGULATION_ID = 138;
    const string MARKET_SHARE_TAX = 'market share tax';
    const int MARKET_SHARE_TAX_ID = 160;
    const string DELPHI_METHOD = 'Delphi method';
    const int DELPHI_METHOD_ID = 139;
    const string PUBLIC_AI = 'public AI';
    const int PUBLIC_AI_ID = 159;
    const string FLUID_DEMOCRACY = 'fluid democracy';
    const int FLUID_DEMOCRACY_ID = 143;
    const string GROSS_DOMESTIC_USAGE = 'gross domestic usage';
    const int GROSS_DOMESTIC_USAGE_ID = 161;
    const string FREE_SOFTWARE = 'free software';
    const int FREE_SOFTWARE_ID = 142;
    const string SYSTEM_COLUMN_MAYOR = 'mayor column (system)';
    const int SYSTEM_COLUMN_MAYOR_ID = 165;
    const string SYSTEM_COLUMN_MAIN = 'main column (system)';
    const int SYSTEM_COLUMN_MAIN_ID = 166;
    const string COLUMN_PROBLEM = 'column problem (high prio)';
    const int COLUMN_PROBLEM_ID = 170;
    const string COLUMN_SOLUTION = 'column solution (high prio)';
    const int COLUMN_SOLUTION_ID = 171;
    // the main column chain and the explaining columns of solution_prio.json that order a table
    const string COLUMN_SOLUTION_AFTER_PROBLEM = 'column solution (high prio) is next main column after column problem (high prio)';
    const int COLUMN_SOLUTION_AFTER_PROBLEM_ID = 192;
    const string COLUMN_LOSS_EXPLAINS_PROBLEM = 'column loss is explaining column for column problem (high prio)';
    const int COLUMN_LOSS_EXPLAINS_PROBLEM_ID = 197;
    const string COLUMN_COST_EXPLAINS_PROBLEM = 'column cost is explaining column for column problem (high prio)';
    const int COLUMN_COST_EXPLAINS_PROBLEM_ID = 198;
    const string COLUMN_GAIN_EXPLAINS_SOLUTION = 'column gain is explaining column for column solution (high prio)';
    const int COLUMN_GAIN_EXPLAINS_SOLUTION_ID = 201;
    // a circular main column chain that no import file contains, so that the fallback can be tested
    const string COLUMN_PROBLEM_AFTER_SOLUTION = 'column problem (high prio) is next main column after column solution (high prio)';
    const int COLUMN_PROBLEM_AFTER_SOLUTION_ID = 997;
    const string COLUMN_COST = 'column cost';
    const int COLUMN_COST_ID = 172;
    const string COLUMN_GAIN = 'column gain';
    const int COLUMN_GAIN_ID = 173;
    const string COLUMN_LOSS = 'column loss';
    const int COLUMN_LOSS_ID = 174;
    // the measure that the values name with the words "potential" and "loss", and its column
    const string POTENTIAL_LOSS = 'potential loss';
    const int POTENTIAL_LOSS_ID = 123;
    const string COLUMN_POTENTIAL_LOSS = 'column potential loss';
    const int COLUMN_POTENTIAL_LOSS_ID = 183;
    // a unit triple typed "measure", so that a table header puts it behind the "in" like a
    // measure word (see pv_switzerland_co2.json)
    const string GRAM_PER_KWH = 'gram per kWh';
    const int GRAM_PER_KWH_ID = 544;
    // TODO use the name and not the id for the use cases
    // the subject of the use case pv_switzerland_co2.json; a use case is user data, so its
    // objects are selected by the name only and never by a database id or a code id
    // (docs/llm/json_structure.md "Use case files"), which is why no id const exists
    const string PV_IN_SWITZERLAND = 'PV in Switzerland';
    const string CASH_FLOW = 'cash flow';
    const int CASH_FLOW_ID = 534;
    const string CASH_FLOW_STATEMENT = 'cash flow statement';
    const int CASH_FLOW_STATEMENT_ID = 537;
    const string INCOME_TAX = 'income taxes';
    const int INCOME_TAX_ID = 535;
    // income statement concepts that the base setup defines as a triple (re-declared on XBRL import)
    const string GROSS_PROFIT = 'gross profit';
    const string COST_OF_REVENUE = 'cost of revenue';
    // the US GAAP accounting standard, built from the words "US" and "GAAP",
    // and its lower case XBRL taxonomy namespace prefix used in the concept ids e.g. "us-gaap_Revenues"
    const string US_GAAP = 'US GAAP';
    const string US_GAAP_XBRL = 'us-gaap';

    const string SECOND = 'second (time)';
    const int SECOND_ID = 64;
    const string TN_CUBIC_METER = 'm3';

    const string CANTON_ZURICH = 'Zurich (canton)';
    const int CANTON_ZURICH_ID = 105;
    const string CITY_ZH = 'Zurich (city)';
    const int CITY_ZH_ID = 104;
    const string CITY_ZH_NAME = 'city of Zurich';
    const string CITY_ZH_COM = 'Zurich is the largest city in Switzerland and the capital of the canton of Zurich. It is in north-central Switzerland, at the northwestern tip of Lake Zurich.';
    const string CITY_BE = 'Bern (city)';
    const int CITY_BE_ID = 106;
    const string CITY_GE = 'Geneva (city)';
    const int CITY_GE_ID = 107;
    const string CANTON_ZURICH_NAME = 'canton Zurich';
    const string CANTON_ZURICH_COM = 'The canton of Zurich is an administrative unit (canton) of Switzerland, situated in the northeastern part of the country.';
    const string COMPANY_ZURICH = "Zurich Insurance";
    const int COMPANY_ZURICH_ID = 210;
    const string MIO_SYMBOL = 'mio is symbol for million';
    const int MIO_SYMBOL_ID = 115;
    const string CHF_SYMBOL = "CHF is symbol for Swiss franc";
    const int CHF_SYMBOL_ID = 368;
    // the currency names are built in currencies.json from the genus word and the issuing
    // country (e.g. franc 'kind of' Swiss), so they are triple phrases and no longer words
    const string SWISS_FRANC = 'Swiss franc';
    const int SWISS_FRANC_ID = 276;
    const string SWISS_FRANC_COM = 'The Swiss franc (symbol: Fr. or CHF; currency code: CHF) is the official currency and legal tender of Switzerland and Liechtenstein, and is also used in the Italian exclave of Campione d\'Italia. Issued by the Swiss National Bank, it is widely regarded as a safe-haven currency due to Switzerland\'s political stability and low inflation.';
    const string SWISS_FRANC_CURRENCY = 'Swiss franc (currency)';
    const int SWISS_FRANC_CURRENCY_ID = 366;
    const string US_DOLLAR_NAME = 'US dollar';
    const string U_S_DOLLAR_NAME = 'U.S. dollar';
    const int U_S_DOLLAR_ID = 269;
    const int US_DOLLAR_ID = 268;
    const string US_DOLLAR_COM = 'The United States dollar (symbol: $; currency code: USD) is the official currency of the United States and several other countries. It is the world\'s primary reserve currency and the most-traded currency on the foreign exchange market. The dollar is divided into 100 cents.';
    const string US_DOLLAR_CURRENCY = 'US dollar (currency)';
    const int US_DOLLAR_CURRENCY_ID = 339;
    const string EURO_CURRENCY = 'Euro (currency)';
    const int EURO_CURRENCY_ID = 317;
    const string USD_SYMBOL = "USD is symbol for US dollar";
    const int USD_SYMBOL_ID = 341;
    const string DOLLAR_ALIAS = "$ is alias of US dollar";
    const int DOLLAR_ALIAS_ID = 521;
    const string U_S_DOLLAR_ALIAS = "U.S. dollar is alias of US dollar";
    const int U_S_DOLLAR_ALIAS_ID = 338;
    const string IN_USD = "in USD";
    const int IN_USD_ID = 335;
    const string EUR_SYMBOL = "EUR is symbol for Euro";
    const int EUR_SYMBOL_ID = 318;
    const string EURO_SIGN_ALIAS = "€ is alias of Euro";
    const int EURO_SIGN_ALIAS_ID = 329;
    const string IN_EUR = "in EUR";
    const int IN_EUR_ID = 328;
    const string COMPANY_VESTAS = "Vestas SA";
    const int COMPANY_VESTAS_ID = 543;
    const string COMPANY_ABB = "ABB (company)";
    const int COMPANY_ABB_ID = 540;
    const string YEAR_2013_FOLLOW = "2014 is follower of 2013";
    const string TAXES_OF_CF = "income taxes is part of cash flow statement";

    // list of often used triples used as a default selection e.g. for the phrase selection
    // TODO Prio 2 to be filled up

    const array TEST_TRIPLES = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_GIVEN,
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
        self::BASIC_INCOME_ID => self::BASIC_INCOME,
        self::BIASED_INFORMATION_ID => self::BIASED_INFORMATION,
        self::BLACK_BOX_AI_ID => self::BLACK_BOX_AI,
        self::CITIZEN_PARTICIPATION_ID => self::CITIZEN_PARTICIPATION,
        self::DELPHI_METHOD_ID => self::DELPHI_METHOD,
        self::FLUID_DEMOCRACY_ID => self::FLUID_DEMOCRACY,
        self::FREE_SOFTWARE_ID => self::FREE_SOFTWARE,
        self::GDP_MISMEASUREMENT_ID => self::GDP_MISMEASUREMENT,
        self::GLOBAL_PROBLEM_ID => self::GLOBAL_PROBLEM,
        self::GLOBAL_WARMING_ID => self::GLOBAL_WARMING,
        self::GROSS_DOMESTIC_USAGE_ID => self::GROSS_DOMESTIC_USAGE,
        self::MARKET_POWER_ID => self::MARKET_POWER,
        self::MARKET_SHARE_TAX_ID => self::MARKET_SHARE_TAX,
        self::PLATFORM_REGULATION_ID => self::PLATFORM_REGULATION,
        self::PROPRIETARY_SOFTWARE_ID => self::PROPRIETARY_SOFTWARE,
        self::PUBLIC_AI_ID => self::PUBLIC_AI,
        self::WEALTH_CONCENTRATION_ID => self::WEALTH_CONCENTRATION,
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
