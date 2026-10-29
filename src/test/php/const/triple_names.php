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
    // only used in memory by the frontend unit tests, because the seed import has no triple plural
    const string MATH_CONST_PLURAL = 'mathematical constants';
    const string PI_SYMBOL = 'π (unit symbol)';
    const string PI_SYMBOL_NAME = 'π (unit symbol)';
    const int PI_SYMBOL_ID = 5;
    const string PI_SYMBOL_COM = 'ratio of the circumference of a circle to its diameter';
    // "Euler's number" is built in units.json as the triple Euler 'name of' number,
    // so it is a triple phrase and no longer a word; it also covers the >'< handling
    // "Caesium-133" is built in units.json as the triple Caesium 'kind of' 133,
    // so it is a triple phrase and no longer a word
    const string CS_133 = 'Caesium-133';
    const int CS_133_ID = 50;
    const string CS_133_COM = 'Caesium-133 is the only stable isotope of caesium. The SI base unit of time, the second, is defined by the unperturbed ground-state hyperfine transition frequency of this atom, set at 9192631770 Hz.';
    const string EULER_NUMBER = "Euler's number";
    const int EULER_NUMBER_ID = 2;
    const string EULER_NUMBER_COM = 'The number e is a mathematical constant approximately equal to 2.71828 that is the base of the natural logarithm and exponential function.';
    const string EULER_CONST = "Euler's constant";
    const int EULER_CONST_ID = 3;
    const string E = '𝑒 (unit symbol)';
    const int E_ID = 59;
    const string E_NUM = '𝑒 (math)';
    const int E_NUM_ID = 58;
    const string E_COM = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const string PI = 'Pi (math)';
    const string PI_NAME = 'Pi (math)';
    const int PI_ID = 57;
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';
    // the only seeded value with a share and a protection type that are not the default (companies.json)
    const string TARGET_PE_RATIO = 'Target Price Earning Ratio';
    const int TARGET_PE_RATIO_ID = 752;

    // si units
    const string SPEED_OF_LIGHT = 'speed of light';
    const int SPEED_OF_LIGHT_ID = 30;
    const string SPEED_OF_LIGHT_COM = 'The speed of light in a vacuum is a universal physical constant defined exactly by the distance light travels in a specific fraction of a second.';
    const string M_PER_S = 'm/s';
    const int M_PER_S_ID = 72;
    const string M_PER_S_COM = 'The metre per second is the unit of both speed (a scalar quantity) and velocity (a vector quantity, which has direction and magnitude) in the International System of Units (SI), equal to the speed of a body covering a distance of one metre in a time of one second.';
    const string TRANSITION_CS = 'hyperfine transition frequency of Cs';
    const int TRANSITION_CS_ID = 90;
    const string TRANSITION_FREQUENCY = 'hyperfine transition frequency';
    const int TRANSITION_FREQUENCY_ID = 76;
    const string HYPERFINE_TRANSITION = 'hyperfine transition';
    const int HYPERFINE_TRANSITION_ID = 49;
    const string DEFINITION_YEAR_1983 = '1983 (year of definition)';
    const int DEFINITION_YEAR_1983_ID = 79;
    const string DEFINITION_YEAR_1967 = '1967 (year of definition)';
    const int DEFINITION_YEAR_1967_ID = 77;
    const string DEFINITION_YEAR = 'year of definition';
    const int DEFINITION_YEAR_ID = 55;
    const string YEAR_1983 = '1983 (year)';
    const int YEAR_1983_ID = 53;
    const string YEAR_1967 = '1967 (year)';
    const int YEAR_1967_ID = 51;
    const string YEAR_2019 = '2019 (year)';
    const int YEAR_2019_ID = 54;
    const string YEAR_2020 = '2020 (year)';
    const int YEAR_2020_ID = 345;
    const string YEAR_2021 = '2021 (year)';
    const int YEAR_2021_ID = 687;
    const string YEAR_2022 = '2022 (year)';
    const int YEAR_2022_ID = 686;
    const string YEAR_2023 = '2023 (year)';
    const int YEAR_2023_ID = 685;
    const string YEAR_2024 = '2024 (year)';
    const int YEAR_2024_ID = 684;
    const string YEAR_2025 = '2025 (year)';
    const int YEAR_2025_ID = 683;
    const string YEAR_2026 = '2026 (year)';
    const int YEAR_2026_ID = 682;
    const string YEAR_2027 = '2027 (year)';
    const int YEAR_2027_ID = 681;
    const string YEAR_2028 = '2028 (year)';
    const int YEAR_2028_ID = 680;
    const string YEAR_2029 = '2029 (year)';
    const int YEAR_2029_ID = 679;
    const string YEAR_2030 = '2030 (year)';
    const int YEAR_2030_ID = 678;

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
    // the frontend needs the name of this triple too, so it is defined in the shared const
    const string GLOBAL_PROBLEM = shared_triples::GLOBAL_PROBLEM;
    const int GLOBAL_PROBLEM_ID = 139;
    const string GLOBAL_WARMING = 'global warming';
    const int GLOBAL_WARMING_ID = 162;
    const string GWP = 'global warming potential';
    const int GWP_ID = 163;
    const string TIME_POINTS = 'time points';
    const int TIME_POINTS_ID = 166;
    const string HAPPY_TIME_POINTS = 'happy time points';
    const int HAPPY_TIME_POINTS_ID = 230;
    const string GLOBAL_WARMING_PROBLEM = 'global warming (global problem)';
    const int GLOBAL_WARMING_PROBLEM_ID = 233;
    const string POPULISM_PROBLEM = 'populism (global problem)';
    const int POPULISM_PROBLEM_ID = 171;
    const string POTENTIAL_HEALTH_PROBLEM = 'health can be a global problem';
    const int POTENTIAL_HEALTH_PROBLEM_ID = 172;
    const string POVERTY_PROBLEM = 'poverty (global problem)';
    const int POVERTY_PROBLEM_ID = 174;
    const string POTENTIAL_EDUCATION_PROBLEM = 'education can be global problem';
    const int POTENTIAL_EDUCATION_PROBLEM_ID = 175;
    // the links of the other problems of solution_prio.json to "global problem"
    const string DISINFORMATION_PROBLEM = 'disinformation (global problem)';
    const int DISINFORMATION_PROBLEM_ID = 205;
    const string WEALTH_CONCENTRATION_PROBLEM = 'wealth concentration (global problem)';
    const int WEALTH_CONCENTRATION_PROBLEM_ID = 250;
    const string MARKET_POWER_PROBLEM = 'market power (global problem)';
    const int MARKET_POWER_PROBLEM_ID = 251;
    const string BIASED_INFORMATION_PROBLEM = 'biased information (global problem)';
    const int BIASED_INFORMATION_PROBLEM_ID = 252;
    const string CITIZEN_PARTICIPATION_PROBLEM = 'citizen participation (global problem)';
    const int CITIZEN_PARTICIPATION_PROBLEM_ID = 253;
    const string GDP_MISMEASUREMENT_PROBLEM = 'GDP mismeasurement (global problem)';
    const int GDP_MISMEASUREMENT_PROBLEM_ID = 254;
    const string PROPRIETARY_SOFTWARE_PROBLEM = 'proprietary software (global problem)';
    const int PROPRIETARY_SOFTWARE_PROBLEM_ID = 255;
    const string BLACK_BOX_AI_PROBLEM = 'black-box AI (global problem)';
    const int BLACK_BOX_AI_PROBLEM_ID = 301;
    // the solution triples and the table column definitions of solution_prio.json
    const string REDUCE_EMISSIONS = 'reduce climate gas emissions';
    const int REDUCE_EMISSIONS_ID = 294;
    const string AVOID_WRONG_DECISIONS = 'avoid wrong decisions';
    const int AVOID_WRONG_DECISIONS_ID = 245;
    // the triples that link a solution to "solution", so that the table can name the solution
    // of a problem row in a column of its own (like the problem links for the rows)
    const string REDUCE_EMISSIONS_SOLUTION = 'reduce climate gas emissions (solution)';
    const int REDUCE_EMISSIONS_SOLUTION_ID = 314;
    const string AVOID_WRONG_DECISIONS_SOLUTION = 'avoid wrong decisions (solution)';
    const int AVOID_WRONG_DECISIONS_SOLUTION_ID = 295;
    const string RESEARCH_SOLUTION = 'research (solution)';
    const int RESEARCH_SOLUTION_ID = 190;
    const string TAXES_SOLUTION = 'taxes (solution)';
    const int TAXES_SOLUTION_ID = 191;
    const string SPENDING_SOLUTION = 'spending (solution)';
    const int SPENDING_SOLUTION_ID = 192;
    const string BASIC_INCOME_SOLUTION = 'basic income (solution)';
    const int BASIC_INCOME_SOLUTION_ID = 256;
    const string PLATFORM_REGULATION_SOLUTION = 'platform regulation (solution)';
    const int PLATFORM_REGULATION_SOLUTION_ID = 257;
    const string MARKET_SHARE_TAX_SOLUTION = 'market share tax (solution)';
    const int MARKET_SHARE_TAX_SOLUTION_ID = 302;
    const string DELPHI_METHOD_SOLUTION = 'Delphi method (solution)';
    const int DELPHI_METHOD_SOLUTION_ID = 258;
    const string PUBLIC_AI_SOLUTION = 'public AI (solution)';
    const int PUBLIC_AI_SOLUTION_ID = 303;
    const string FLUID_DEMOCRACY_SOLUTION = 'fluid democracy (solution)';
    const int FLUID_DEMOCRACY_SOLUTION_ID = 259;
    const string GROSS_DOMESTIC_USAGE_SOLUTION = 'gross domestic usage (solution)';
    const int GROSS_DOMESTIC_USAGE_SOLUTION_ID = 304;
    const string FREE_SOFTWARE_SOLUTION = 'free software (solution)';
    const int FREE_SOFTWARE_SOLUTION_ID = 260;
    // the problems of solution_prio.json that are a triple, ordered like the start page ranking
    const string WEALTH_CONCENTRATION = 'wealth concentration';
    const int WEALTH_CONCENTRATION_ID = 182;
    const string MARKET_POWER = 'market power';
    const int MARKET_POWER_ID = 183;
    const string BIASED_INFORMATION = 'biased information';
    const int BIASED_INFORMATION_ID = 184;
    const string BLACK_BOX_AI = 'black-box AI';
    const int BLACK_BOX_AI_ID = 243;
    const string CITIZEN_PARTICIPATION = 'citizen participation';
    const int CITIZEN_PARTICIPATION_ID = 186;
    const string GDP_MISMEASUREMENT = 'GDP mismeasurement';
    const int GDP_MISMEASUREMENT_ID = 187;
    const string PROPRIETARY_SOFTWARE = 'proprietary software';
    const int PROPRIETARY_SOFTWARE_ID = 188;
    // the solutions that solution_prio.json assigns to these problems
    const string BASIC_INCOME = 'basic income';
    const int BASIC_INCOME_ID = 195;
    const string PLATFORM_REGULATION = 'platform regulation';
    const int PLATFORM_REGULATION_ID = 193;
    const string MARKET_SHARE_TAX = 'market share tax';
    const int MARKET_SHARE_TAX_ID = 247;
    const string DELPHI_METHOD = 'Delphi method';
    const int DELPHI_METHOD_ID = 194;
    const string PUBLIC_AI = 'public AI';
    const int PUBLIC_AI_ID = 246;
    const string FLUID_DEMOCRACY = 'fluid democracy';
    const int FLUID_DEMOCRACY_ID = 198;
    const string GROSS_DOMESTIC_USAGE = 'gross domestic usage';
    const int GROSS_DOMESTIC_USAGE_ID = 248;
    const string FREE_SOFTWARE = 'free software';
    const int FREE_SOFTWARE_ID = 197;
    const string SYSTEM_COLUMN = shared_triples::SYSTEM_COLUMN;
    const int SYSTEM_COLUMN_ID = 179;
    const string SYSTEM_COLUMN_MAYOR = 'mayor column (system)';
    const int SYSTEM_COLUMN_MAYOR_ID = 236;
    // the building block of the process catch field keyword of process_catch_fields.json, which has no code id
    const string PROCESS_CATCH_FIELD = 'process catch field';
    // the value phrase of the geolocator component of process_catch_views.json, an unnamed "of" triple
    const string PLACE_OF_USER = 'place of user';
    // the base phrase of the table editor example (problem_main_reasons.json)
    const string POPULISM_SOLUTION = 'populism solution';
    const int POPULISM_SOLUTION_ID = 1256;
    const string SYSTEM_COLUMN_MAIN = 'main column (system)';
    const int SYSTEM_COLUMN_MAIN_ID = 237;
    // the config.yaml switch of the javascript of the calculator pages
    const string USE_JAVASCRIPT = shared_triples::USE_JAVASCRIPT;
    const int USE_JAVASCRIPT_ID = 808;
    const string FOR_CALCULATOR = shared_triples::FOR_CALCULATOR;
    const int FOR_CALCULATOR_ID = 809;
    // the chart type tiers of solution_prio.json and the two charts of the start page ranking
    // (see triples::SYSTEM_CHART_TYPE and docs/llm/frontend.md "A table as a chart")
    const string SYSTEM_CHART_TYPE = shared_triples::SYSTEM_CHART_TYPE;
    const int SYSTEM_CHART_TYPE_ID = 228;
    const string SYSTEM_CHART_TYPE_DEFAULT = shared_triples::SYSTEM_CHART_TYPE_DEFAULT;
    const int SYSTEM_CHART_TYPE_DEFAULT_ID = 271;
    const string SYSTEM_CHART_TYPE_ALTERNATIVE = shared_triples::SYSTEM_CHART_TYPE_ALTERNATIVE;
    const int SYSTEM_CHART_TYPE_ALTERNATIVE_ID = 272;
    // the chart of the table with the mayor columns only from start_page_charts.json, which the
    // db setup imports last, so the ids follow the highest triple id of the seed data in the
    // order of the import levels; check them in the triple list.csv
    const string SYSTEM_CHART_TYPE_MAYOR = shared_triples::SYSTEM_CHART_TYPE_MAYOR;
    const int SYSTEM_CHART_TYPE_MAYOR_ID = 2627;
    const string GAIN_AND_LOSS = 'potential gain and potential loss';
    const int GAIN_AND_LOSS_ID = 2628;
    const string LEVERAGE_OF_GAIN_AND_LOSS = 'leverage plot of potential gain and potential loss';
    const int LEVERAGE_OF_GAIN_AND_LOSS_ID = 2629;
    const string CHART_LEVERAGE_OF_GAIN_AND_LOSS = 'chart leverage plot of potential gain and potential loss';
    const int CHART_LEVERAGE_OF_GAIN_AND_LOSS_ID = 2630;
    // the field types and properties of process_catch_fields.json, appended like the chart
    // triples above, so they move with them; check them in the triple list.csv
    const string FIELD_NUMBER_WITH_LIMITS = shared_triples::FIELD_NUMBER_WITH_LIMITS;
    const int FIELD_NUMBER_WITH_LIMITS_ID = 2658;
    const string FIELD_PHRASE_SELECTOR_TYPE = shared_triples::FIELD_PHRASE_SELECTOR_TYPE;
    const int FIELD_PHRASE_SELECTOR_TYPE_ID = 2660;
    const string FIELD_LIMIT_DOWN = shared_triples::FIELD_LIMIT_DOWN;
    const int FIELD_LIMIT_DOWN_ID = 2644;
    const string FIELD_STEP = shared_triples::FIELD_STEP;
    const int FIELD_STEP_ID = 2645;
    // the fields of the input field test (unit_ui/use_case_ui_tests.php), which exist in no
    // database, so their ids are fixed like SYSTEM_TEST_ADD_ID
    const string FIELD_INHABITANTS = 'field inhabitants';
    const int FIELD_INHABITANTS_ID = 989;
    const string FIELD_CITY = 'field city';
    const int FIELD_CITY_ID = 988;
    const string ZH_USES_BERN = 'Zurich uses Bern';
    const int ZH_USES_BERN_ID = 987;
    // the field value phrase 2019 of the inhabitants field, so that the field shows the 2019
    // value and not the value of another year (like the annual household consumption of the PV page)
    const string FIELD_VALUE_PHRASES = shared_triples::FIELD_VALUE_PHRASES;
    const int FIELD_VALUE_PHRASES_ID = 986;
    const string INHABITANTS_INPUT_YEAR = 'inhabitants input year';
    const int INHABITANTS_INPUT_YEAR_ID = 985;
    const string INHABITANTS_FIELD_VALUE_PHRASE = 'inhabitants field value phrase';
    const int INHABITANTS_FIELD_VALUE_PHRASE_ID = 984;
    // the calculator of the reader test (unit_ui/use_case_ui_tests.php), which exists in no
    // database, so its triples count down from this fixed id
    const int CALC_FIRST_ID = 983;
    const string CALC_ZH_INPUTS = 'Zurich inputs';
    const string CALC_ZH_INPUT_INHABITANTS = 'Zurich input inhabitants';
    const string CALC_ZH_INPUT_CANTON = 'Zurich input canton';
    const string CALC_ZH_INPUT_YEAR = 'Zurich input 2019';
    const string CALC_ZH_ROW_BE = 'Zurich row Bern';
    const string CALC_ZH_ROW_INHABITANTS = 'Zurich row inhabitants';
    const string CALC_ZH_LABEL_COM = 'City calculator';
    const string CALC_ZH_HINT_COM = 'The inhabitants of a city';
    const string CALC_INHABITANTS_LABEL_COM = 'People';
    const string CALC_INHABITANTS_HINT_COM = 'living in the city';
    const string CALC_CANTON_LABEL_COM = 'Canton of the city';
    const string CALC_ZH_INPUT_CANTON_LABEL_COM = 'Region';
    // the triples of the calculator input test, which follow the 24 triples of the reader test
    const int CALC_INPUT_FIRST_ID = 959;
    // the triples of the calculation result test, which follow the 5 triples of the input test
    const int CALC_RESULT_FIRST_ID = 954;
    const string CALC_ZH_COLUMNS_LABEL_COM = 'Main numbers';
    // the calculator keyword root of the proposed keyword test, which follows the 12 triples of the result test
    const int CALC_ROOT_ID = 942;
    // the other triples of the proposed keyword test
    const int CALC_PROPOSED_FIRST_ID = 941;
    // a proposed keyword, which the calculator does not show yet, but is a triple from the calculator keyword
    const string CALC_PROPOSED_KEYWORD = 'calculator download';
    const string CALC_ZH_INPUTS_DOWNLOAD = 'Zurich inputs download';
    const string INITIAL_EFFORT = 'initial effort';
    const int INITIAL_EFFORT_ID = 201;
    // the minor and marginal columns of the ranking and the unit of the initial effort
    const string LOSS_REDUCTION = 'loss reduction';
    const int LOSS_REDUCTION_ID = 200;
    const string REWARD_RATIO = 'reward ratio';
    const int REWARD_RATIO_ID = 202;
    const string PERSON_YEAR = 'person year';
    const int PERSON_YEAR_ID = 203;
    const string SYSTEM_COLUMN_MARGINAL = shared_triples::SYSTEM_COLUMN_MARGINAL;
    const int SYSTEM_COLUMN_MARGINAL_ID = 239;
    const string COLUMN_LOSS_REDUCTION = 'column loss reduction';
    const int COLUMN_LOSS_REDUCTION_ID = 297;
    const string COLUMN_INITIAL_EFFORT = 'column initial effort';
    const int COLUMN_INITIAL_EFFORT_ID = 298;
    const string COLUMN_REWARD_RATIO = 'column reward ratio';
    const int COLUMN_REWARD_RATIO_ID = 299;
    const string RANGE_BARS_OF_LOSS = 'range bars of potential loss';
    const int RANGE_BARS_OF_LOSS_ID = 273;
    const string GAIN_AND_EFFORT = 'potential gain and initial effort';
    const int GAIN_AND_EFFORT_ID = 274;
    const string SCATTER_OF_GAIN_AND_EFFORT = 'scatter plot of potential gain and initial effort';
    const int SCATTER_OF_GAIN_AND_EFFORT_ID = 308;
    const string CHART_RANGE_BARS_OF_LOSS = 'chart range bars of potential loss';
    const int CHART_RANGE_BARS_OF_LOSS_ID = 309;
    const string CHART_SCATTER_OF_GAIN_AND_EFFORT = 'chart scatter plot of potential gain and initial effort';
    const int CHART_SCATTER_OF_GAIN_AND_EFFORT_ID = 328;
    // the default sort order of the start page ranking (see triples::SYSTEM_SORT_ORDER)
    const string SYSTEM_SORT_ORDER = shared_triples::SYSTEM_SORT_ORDER;
    const int SYSTEM_SORT_ORDER_ID = 229;
    const string SYSTEM_SORT_ORDER_DEFAULT = shared_triples::SYSTEM_SORT_ORDER_DEFAULT;
    const int SYSTEM_SORT_ORDER_DEFAULT_ID = 275;
    // the bounds of the variance of a value (see triples::SYSTEM_VARIANCE_HIGH)
    const string SYSTEM_VARIANCE_HIGH = shared_triples::SYSTEM_VARIANCE_HIGH;
    const int SYSTEM_VARIANCE_HIGH_ID = 148;
    const string SYSTEM_VARIANCE_LOW = shared_triples::SYSTEM_VARIANCE_LOW;
    const int SYSTEM_VARIANCE_LOW_ID = 149;
    const string NUMERIC_DESC_OF_LOSS = 'numeric descending of potential loss';
    const int NUMERIC_DESC_OF_LOSS_ID = 276;
    const string SORT_NUMERIC_DESC_OF_LOSS = 'sort numeric descending of potential loss';
    const int SORT_NUMERIC_DESC_OF_LOSS_ID = 310;
    const string COLUMN_PROBLEM = 'column problem (high prio)';
    const int COLUMN_PROBLEM_ID = 280;
    const string COLUMN_SOLUTION = 'column solution (high prio)';
    const int COLUMN_SOLUTION_ID = 282;
    // the main column chain and the explaining columns of solution_prio.json that order a table
    const string COLUMN_SOLUTION_AFTER_REASON = 'column solution (high prio) is next main column after column reason';
    const int COLUMN_SOLUTION_AFTER_REASON_ID = 331;
    // the chain of a table without the reason column, which no import file contains
    const string COLUMN_SOLUTION_AFTER_PROBLEM = 'column solution (high prio) is next main column after column problem (high prio)';
    const int COLUMN_SOLUTION_AFTER_PROBLEM_ID = 996;
    // the loss and the gain column and their explaining triples, which no import file contains
    // any more, so that the column order can be tested with columns named by a word
    const string COLUMN_LOSS_EXPLAINS_PROBLEM = 'column loss is explaining column for column problem (high prio)';
    const int COLUMN_LOSS_EXPLAINS_PROBLEM_ID = 992;
    const string COLUMN_COST_EXPLAINS_PROBLEM = 'column cost is explaining column for column problem (high prio)';
    const int COLUMN_COST_EXPLAINS_PROBLEM_ID = 320;
    const string COLUMN_GAIN_EXPLAINS_SOLUTION = 'column gain is explaining column for column solution (high prio)';
    const int COLUMN_GAIN_EXPLAINS_SOLUTION_ID = 993;
    // a circular main column chain that no import file contains, so that the fallback can be tested
    const string COLUMN_PROBLEM_AFTER_SOLUTION = 'column problem (high prio) is next main column after column solution (high prio)';
    const int COLUMN_PROBLEM_AFTER_SOLUTION_ID = 997;
    const string COLUMN_COST = 'column cost';
    const int COLUMN_COST_ID = 285;
    const string COLUMN_GAIN = 'column gain';
    const int COLUMN_GAIN_ID = 991;
    const string COLUMN_LOSS = 'column loss';
    const int COLUMN_LOSS_ID = 990;
    // the measures of the start page values, their columns and the triples that order them
    const string POTENTIAL_LOSS = 'potential loss';
    const int POTENTIAL_LOSS_ID = 177;
    const string POTENTIAL_GAIN = 'potential gain';
    const int POTENTIAL_GAIN_ID = 178;
    const string COLUMN_POTENTIAL_LOSS = 'column potential loss';
    const int COLUMN_POTENTIAL_LOSS_ID = 281;
    const string COLUMN_POTENTIAL_GAIN = 'column potential gain';
    const int COLUMN_POTENTIAL_GAIN_ID = 283;
    // the gain columns per unit
    const string POTENTIAL_GAIN_IN_EUR = 'potential gain in EUR';
    const int POTENTIAL_GAIN_IN_EUR_ID = 240;
    const string POTENTIAL_GAIN_IN_HTP = 'potential gain in htp';
    const int POTENTIAL_GAIN_IN_HTP_ID = 241;
    const string COLUMN_POTENTIAL_GAIN_IN_EUR = 'column potential gain in EUR';
    const int COLUMN_POTENTIAL_GAIN_IN_EUR_ID = 292;
    const string COLUMN_POTENTIAL_GAIN_IN_HTP = 'column potential gain in htp';
    const int COLUMN_POTENTIAL_GAIN_IN_HTP_ID = 296;
    const string SYSTEM_COLUMN_MINOR = 'minor column (system)';
    const int SYSTEM_COLUMN_MINOR_ID = 238;
    // the reason of a problem and the loss it causes, the two main columns of the ranking
    const string CLIMATE_GAS_EMISSIONS = 'climate gas emissions';
    const int CLIMATE_GAS_EMISSIONS_ID = 244;
    const string CLIMATE_GAS_EMISSIONS_REASON = 'climate gas emissions (reason)';
    const int CLIMATE_GAS_EMISSIONS_REASON_ID = 311;
    const string COLUMN_REASON = 'column reason';
    const int COLUMN_REASON_ID = 312;
    const string POTENTIAL_LOSS_OF_REASON = 'potential loss of reason';
    const int POTENTIAL_LOSS_OF_REASON_ID = 277;
    const string COLUMN_POTENTIAL_LOSS_OF_REASON = 'column potential loss of reason';
    const int COLUMN_POTENTIAL_LOSS_OF_REASON_ID = 313;
    const string COLUMN_REASON_AFTER_PROBLEM = 'column reason is next main column after column problem (high prio)';
    const int COLUMN_REASON_AFTER_PROBLEM_ID = 329;
    const string COLUMN_LOSS_OF_REASON_EXPLAINS_REASON = 'column potential loss of reason is explaining column for column reason';
    const int COLUMN_LOSS_OF_REASON_EXPLAINS_REASON_ID = 330;
    // the reasons of global warming of problem_global_warming_reasons.json, whose table page lists them
    const string GLOBAL_WARMING_REASON = 'global warming reason';
    const int GLOBAL_WARMING_REASON_ID = 1702;
    const string HEAT_MORTALITY = 'heat mortality';
    const int HEAT_MORTALITY_ID = 1181;
    const string HEAT_MORTALITY_REASON = 'heat mortality (reason)';
    const int HEAT_MORTALITY_REASON_ID = 1704;
    const string CLIMATE_GAS_EMISSIONS_GLOBAL_WARMING_REASON = 'climate gas emissions (global warming reason)';
    const int CLIMATE_GAS_EMISSIONS_GLOBAL_WARMING_REASON_ID = 1723;
    const string HEAT_MORTALITY_GLOBAL_WARMING_REASON = 'heat mortality (global warming reason)';
    const int HEAT_MORTALITY_GLOBAL_WARMING_REASON_ID = 1725;
    // the solutions of global warming of problem_global_warming_reasons.json, whose table page lists them
    const string CLIMATE_ADAPTATION = 'climate adaptation';
    const int CLIMATE_ADAPTATION_ID = 1706;
    const string CLIMATE_ADAPTATION_SOLUTION = 'climate adaptation (solution)';
    const int CLIMATE_ADAPTATION_SOLUTION_ID = 1727;
    const string GLOBAL_WARMING_SOLUTION = 'global warming solution';
    const int GLOBAL_WARMING_SOLUTION_ID = 1707;
    const string REDUCE_EMISSIONS_GLOBAL_WARMING_SOLUTION = 'reduce climate gas emissions (global warming solution)';
    const int REDUCE_EMISSIONS_GLOBAL_WARMING_SOLUTION_ID = 1728;
    const string CLIMATE_ADAPTATION_GLOBAL_WARMING_SOLUTION = 'climate adaptation (global warming solution)';
    const int CLIMATE_ADAPTATION_GLOBAL_WARMING_SOLUTION_ID = 1730;
    const string COLUMN_POTENTIAL_LOSS_EXPLAINS_PROBLEM = 'column potential loss is explaining column for column problem (high prio)';
    const int COLUMN_POTENTIAL_LOSS_EXPLAINS_PROBLEM_ID = 321;
    const string COLUMN_POTENTIAL_GAIN_EXPLAINS_SOLUTION = 'column potential gain is explaining column for column solution (high prio)';
    const int COLUMN_POTENTIAL_GAIN_EXPLAINS_SOLUTION_ID = 325;
    // a value quality of solution_prio.json that is a triple, so it is typed "value quality" like the word "assumed"
    const string PEER_REVIEWED = 'peer reviewed by quality journal';
    // a unit triple typed "measure unit", so that a table header puts it behind the "in" like a
    // unit word (see pv_switzerland_co2.json)
    const string GRAM_PER_KWH = 'gram per kWh';
    const int GRAM_PER_KWH_ID = 2027;
    // TODO Prio 2 use the name and not the id for the use cases
    // the subject of the use case pv_switzerland_co2.json; a use case is user data, so its
    // objects are selected by the name only and never by a database id or a code id
    // (docs/llm/json_structure.md "Use case files"), which is why no id const exists
    const string PV_IN_SWITZERLAND = 'PV in Switzerland';
    // the worked example of the use case pv_lifetime_balance.json, selected by the name only as well
    const string EXAMPLE_PV_INSTALLATION = 'example PV installation';
    // the label of an input of the calculator editor from pv_lifetime_balance.json, changed by the update put test
    const string PV_CAPACITY_LABEL = 'PV capacity label';
    const string PV_CAPACITY_LABEL_COM = 'System size';
    const string PV_CAPACITY_LABEL_COM_CHANGED = 'Installation size';
    // the calculator triples of pv_lifetime_balance.json that the calculator editor scenarios change
    // (src/test/resources/web/calculator_editor/), selected by the name only
    const string PV_CAPACITY = 'PV capacity';
    const string PV_CAPACITY_INPUT = 'PV installation input PV capacity';
    const string PV_CAPACITY_INPUT_RENAMED = 'PV installation input installation size';
    const string YIELD_FACTOR_ORDER = 'PV installation input yield factor order';
    const string GRID_IMPORT_ROW = 'PV installation row grid import';
    const string SELF_CONSUMPTION_ROW = 'PV installation row self-consumption share';
    const string SELF_CONSUMPTION_ORDER = 'PV installation row self-consumption share order';
    const string AUTARKY_ROW = 'PV installation row autarky';
    const string AUTARKY_ORDER = 'PV installation row autarky order';
    const string PRODUCTION_EMISSIONS_ORDER = 'PV installation rows production emissions order';
    const string SAVING_ORDER = 'PV installation rows saving order';
    const string HOUSEHOLD_CONSUMPTION_INPUT = 'PV installation input household consumption';
    // the input field of the test word that the scenario add_input adds and its order triple
    const string TEST_ADD_INPUT = 'PV installation input System Test Word';
    const string TEST_ADD_INPUT_ORDER = 'PV installation input System Test Word order';
    // a general triple of electricity, a row phrase of the PV page, from cbam_calculator.json that is no calculator triple
    const string COAL_ELECTRICITY = 'coal electricity';
    // the phrase id of a triple that no test database reaches, to test the answer for a missing phrase
    const int MISSING_PHRASE_ID = -2147483647;
    // the phrase that the worked example is built from and that has the calculator triples of the page
    const string PV_INSTALLATION = 'PV installation';
    // the shipment of the part tree example of the use case cbam_calculator.json, selected by the name only
    const string EXAMPLE_TUBE_IMPORT = 'example tube import';
    // the worked example of the use case portfolio_split_calculator.json, selected by the name only
    const string EXAMPLE_PORTFOLIO_SPLIT = 'example portfolio split';
    // the beneficiary whose allocation the use case portfolio_split_calculator.json models
    const string FIRST_CHILD_PORTFOLIO = 'first child portfolio';
    // an input of the use case cbam_calculator.json that two results of its chain depend on
    const string CBAM_CERTIFICATE_PRICE = 'CBAM certificate price';
    // an input of the use case ahv_vat_vote.json that the extra VAT revenue and its chain depend on
    const string AHV_VAT_YIELD = 'VAT yield';
    // an input of the use case share_buy_sell_abb.json that the earnings multiple target depends on
    const string ABB_FAIR_PE = 'fair P/E';
    const string CASH_FLOW = 'cash flow';
    const int CASH_FLOW_ID = 661;
    const string CASH_FLOW_STATEMENT = 'cash flow statement';
    const int CASH_FLOW_STATEMENT_ID = 664;
    const string INCOME_TAX = 'income taxes';
    const int INCOME_TAX_ID = 662;
    // income statement concepts that the base setup defines as a triple (re-declared on XBRL import)
    const string GROSS_PROFIT = 'gross profit';
    const string COST_OF_REVENUE = 'cost of revenue';
    // the US GAAP accounting standard, built from the words "US" and "GAAP",
    // and its lower case XBRL taxonomy namespace prefix used in the concept ids e.g. "us-gaap_Revenues"
    const string US_GAAP = 'US GAAP';
    const string US_GAAP_XBRL = 'us-gaap';

    const string SECOND = 'second (time)';
    const int SECOND_ID = 65;
    const string TN_CUBIC_METER = 'm3';

    const string CANTON_ZURICH = 'Zurich (canton)';
    const int CANTON_ZURICH_ID = 142;
    const string CITY_ZH = 'Zurich (city)';
    const int CITY_ZH_ID = 141;
    const string CITY_ZH_NAME = 'city of Zurich';
    const string CITY_ZH_COM = 'Zurich is the largest city in Switzerland and the capital of the canton of Zurich. It is in north-central Switzerland, at the northwestern tip of Lake Zurich.';
    const string CITY_BE = 'Bern (city)';
    const int CITY_BE_ID = 143;
    const string CITY_GE = 'Geneva (city)';
    const int CITY_GE_ID = 144;
    const string CANTON_ZURICH_NAME = 'canton Zurich';
    const string CANTON_ZURICH_COM = 'The canton of Zurich is an administrative unit (canton) of Switzerland, situated in the northeastern part of the country.';
    const string COMPANY_ZURICH = "Zurich Insurance";
    const int COMPANY_ZURICH_ID = 337;
    const string MIO_SYMBOL = 'mio is symbol for million';
    const int MIO_SYMBOL_ID = 167;
    // the second symbol of million, which is not used behind a number, because "m" is a unit too
    const string M_SYMBOL = 'm is symbol for million';
    const int M_SYMBOL_ID = 101;
    // the triple of scaling.json that defines "x" as the symbol shown behind a factor number
    const string FACTOR_SYMBOL = 'x is symbol for factor';
    const int FACTOR_SYMBOL_ID = 103;
    // the triple of scaling.json that defines "%" as the symbol shown behind a percent number
    const string PERCENT_SYMBOL = '% is symbol for percent';
    const int PERCENT_SYMBOL_ID = 104;
    const string CHF_SYMBOL = "CHF is symbol for Swiss franc";
    const int CHF_SYMBOL_ID = 495;
    // the currency names are built in currencies.json from the genus word and the issuing
    // country (e.g. franc 'kind of' Swiss), so they are triple phrases and no longer words
    const string SWISS_FRANC = 'Swiss franc';
    const int SWISS_FRANC_ID = 403;
    const string SWISS_FRANC_COM = 'The Swiss franc (symbol: Fr. or CHF; currency code: CHF) is the official currency and legal tender of Switzerland and Liechtenstein, and is also used in the Italian exclave of Campione d\'Italia. Issued by the Swiss National Bank, it is widely regarded as a safe-haven currency due to Switzerland\'s political stability and low inflation.';
    const string SWISS_FRANC_CURRENCY = 'Swiss franc (currency)';
    const int SWISS_FRANC_CURRENCY_ID = 493;
    const string US_DOLLAR_NAME = 'US dollar';
    const string U_S_DOLLAR_NAME = 'U.S. dollar';
    const int U_S_DOLLAR_ID = 396;
    const int US_DOLLAR_ID = 395;
    const string US_DOLLAR_COM = 'The United States dollar (symbol: $; currency code: USD) is the official currency of the United States and several other countries. It is the world\'s primary reserve currency and the most-traded currency on the foreign exchange market. The dollar is divided into 100 cents.';
    const string US_DOLLAR_CURRENCY = 'US dollar (currency)';
    const int US_DOLLAR_CURRENCY_ID = 466;
    const string EURO_CURRENCY = 'Euro (currency)';
    const int EURO_CURRENCY_ID = 444;
    const string USD_SYMBOL = "USD is symbol for US dollar";
    const int USD_SYMBOL_ID = 468;
    const string DOLLAR_ALIAS = "$ is alias of US dollar";
    const int DOLLAR_ALIAS_ID = 648;
    const string U_S_DOLLAR_ALIAS = "U.S. dollar is alias of US dollar";
    const int U_S_DOLLAR_ALIAS_ID = 465;
    const string IN_USD = "in USD";
    const int IN_USD_ID = 462;
    const string EUR_SYMBOL = "EUR is symbol for Euro";
    const int EUR_SYMBOL_ID = 445;
    const string EURO_SIGN_SYMBOL = "€ is symbol for Euro";
    const int EURO_SIGN_SYMBOL_ID = 446;
    // the percent typed share unit of use_cases/pv_lifetime_balance.json and its symbol "share"
    const string SHARE_RATIO = 'share (ratio)';
    const int SHARE_RATIO_ID = 2749;
    const string SHARE_SYMBOL = 'share is symbol for share (ratio)';
    const int SHARE_SYMBOL_ID = 2800;
    const string EURO_SIGN_ALIAS = "€ is alias of Euro";
    const int EURO_SIGN_ALIAS_ID = 456;
    const string IN_EUR = "in EUR";
    const int IN_EUR_ID = 455;
    const string COMPANY_VESTAS = "Vestas SA";
    const int COMPANY_VESTAS_ID = 670;
    const string COMPANY_ABB = "ABB (company)";
    const int COMPANY_ABB_ID = 667;
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
