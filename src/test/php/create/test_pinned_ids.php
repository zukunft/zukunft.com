<?php

/*

    test/php/create/test_pinned_ids.php - check the pinned db ids of the tests
    -----------------------------------

    the tests pin the seed database id of many objects as *_ID consts, which shift as soon as
    an object is added to or removed from a seed import; this check takes the name or the code id
    of each pinned object and compares the pinned id with the id in the list.csv of the class
    and in the database, so that a shift is reported with the new id


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\create;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_COMPONENT . 'component.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_LOG . 'change_field.php';
include_once paths::MODEL_LOG . 'change_table.php';
include_once paths::MODEL_REF . 'ref.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_USER . 'user.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::SHARED_CONST . 'components.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'users.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'change_fields.php';
include_once paths::SHARED_ENUM . 'change_tables.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'formula_names.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_cleanup.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\log\change_field;
use Zukunft\ZukunftCom\main\php\cfg\log\change_table;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\enum\change_fields;
use Zukunft\ZukunftCom\main\php\shared\enum\change_tables;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\formula_names;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use ReflectionClass;

class test_pinned_ids
{

    // the pinned ids of the seed objects as pairs of the name or code id and the id;
    // the ids of the test objects that exist in no database e.g. word_names::TEST_ADD_ID are not listed
    const array WORDS = [
        [word_names::ONE, word_names::ONE_ID],
        [word_names::PI_SYMBOL, word_names::PI_SYMBOL_ID],
        [word_names::PI, word_names::PI_ID],
        [word_names::E_SYMBOL, word_names::E_SYMBOL_ID],
        [word_names::CIRCUMFERENCE, word_names::CIRCUMFERENCE_ID],
        [word_names::DIAMETER, word_names::DIAMETER_ID],
        [word_names::FLOW, word_names::FLOW_ID],
        [word_names::MINUTE, word_names::MINUTE_ID],
        [word_names::YEAR_2019, word_names::YEAR_2019_ID],
        [word_names::YEAR_2020, word_names::YEAR_2020_ID],
        [word_names::MIO, word_names::MIO_ID],
        [word_names::MIO_SHORT, word_names::MIO_SHORT_ID],
        [word_names::BILLION, word_names::BILLION_ID],
        [word_names::FACTOR_SYMBOL, word_names::FACTOR_SYMBOL_ID],
        [word_names::PERCENT_SYMBOL, word_names::PERCENT_SYMBOL_ID],
        [word_names::FACT, word_names::FACT_ID],
        [word_names::GOVERNMENT, word_names::GOVERNMENT_ID],
        [word_names::GROUP, word_names::GROUP_ID],
        [word_names::HAND, word_names::HAND_ID],
        [word_names::LIFE, word_names::LIFE_ID],
        [word_names::MAN, word_names::MAN_ID],
        [word_names::PART, word_names::PART_ID],
        [word_names::PERSON, word_names::PERSON_ID],
        [word_names::PLACE, word_names::PLACE_ID],
        [word_names::WAY, word_names::WAY_ID],
        [word_names::WOMAN, word_names::WOMAN_ID],
        [word_names::WORK, word_names::WORK_ID],
        [word_names::WORLD, word_names::WORLD_ID],
        [word_names::PROBLEM, word_names::PROBLEM_ID],
        [word_names::GLOBAL, word_names::GLOBAL_ID],
        [word_names::HUMAN, word_names::HUMAN_ID],
        [word_names::POPULATION, word_names::POPULATION_ID],
        [word_names::POTENTIAL, word_names::POTENTIAL_ID],
        [word_names::SOLUTION, word_names::SOLUTION_ID],
        [word_names::LOSS, word_names::LOSS_ID],
        [word_names::GAIN, word_names::GAIN_ID],
        [word_names::CLIMATE, word_names::CLIMATE_ID],
        [word_names::WARMER, word_names::WARMER_ID],
        [word_names::GRAM, word_names::GRAM_ID],
        [word_names::KWH, word_names::KWH_ID],
        [word_names::POPULISM, word_names::POPULISM_ID],
        [word_names::HEALTH, word_names::HEALTH_ID],
        [word_names::POVERTY, word_names::POVERTY_ID],
        [word_names::EDUCATION, word_names::EDUCATION_ID],
        [word_names::ASSUMED, word_names::ASSUMED_ID],
        [word_names::LOW, word_names::LOW_ID],
        [word_names::HIGH, word_names::HIGH_ID],
        [word_names::CONFIDENCE, word_names::CONFIDENCE_ID],
        [word_names::DISINFORMATION, word_names::DISINFORMATION_ID],
        [word_names::RESEARCH, word_names::RESEARCH_ID],
        [word_names::TAXES, word_names::TAXES_ID],
        [word_names::SPENDING, word_names::SPENDING_ID],
        [word_names::HTP, word_names::HTP_ID],
        [word_names::TRILLION, word_names::TRILLION_ID],
        [word_names::USD, word_names::USD_ID],
        [word_names::CHART_TYPE, word_names::CHART_TYPE_ID],
        [word_names::DEFAULT, word_names::DEFAULT_ID],
        [word_names::MAYOR, word_names::MAYOR_ID],
        [word_names::MINOR, word_names::MINOR_ID],
        [word_names::LEVERAGE_PLOT, word_names::LEVERAGE_PLOT_ID],
        [word_names::ALTERNATIVE, word_names::ALTERNATIVE_ID],
        [word_names::RANGE_BARS, word_names::RANGE_BARS_ID],
        [word_names::SCATTER_PLOT, word_names::SCATTER_PLOT_ID],
        [word_names::SORT_ORDER, word_names::SORT_ORDER_ID],
        [word_names::NUMERIC_DESCENDING, word_names::NUMERIC_DESCENDING_ID],
        [word_names::REASON, word_names::REASON_ID],
        [word_names::SALES, word_names::SALES_ID],
        [word_names::CASH, word_names::CASH_ID],
        [word_names::STATEMENT, word_names::STATEMENT_ID],
        [word_names::PARTS, word_names::PARTS_ID],
        [word_names::INCOME, word_names::INCOME_ID],
        [word_names::TAX, word_names::TAX_ID],
        [word_names::CANTON, word_names::CANTON_ID],
        [word_names::CITY, word_names::CITY_ID],
        [word_names::ZH, word_names::ZH_ID],
        [word_names::BE, word_names::BE_ID],
        [word_names::GE, word_names::GE_ID],
        [word_names::INHABITANTS, word_names::INHABITANT_ID],
        [word_names::FIFTEEN_YEAR, word_names::FIFTEEN_YEAR_ID],
        [word_names::YEAR_2013, word_names::YEAR_2013_ID],
        [word_names::YEAR_2014, word_names::YEAR_2014_ID],
        [word_names::YEAR_2015, word_names::YEAR_2015_ID],
        [word_names::YEAR_2016, word_names::YEAR_2016_ID],
        [word_names::YEAR_2017, word_names::YEAR_2017_ID],
        [word_names::YEAR_2018, word_names::YEAR_2018_ID],
        [word_names::COMPANY, word_names::COMPANY_ID],
        [word_names::ABB, word_names::ABB_ID],
        [word_names::VESTAS, word_names::VESTAS_ID],
        [word_names::COST, word_names::COST_ID],
        [word_names::CONST_NAME, word_names::CONST_ID],
        [word_names::HOUR, word_names::HOUR_ID],
        [word_names::YEAR_2021, word_names::YEAR_2021_ID],
        [word_names::YEAR_2022, word_names::YEAR_2022_ID],
        [word_names::YEAR_2023, word_names::YEAR_2023_ID],
        [word_names::YEAR_2024, word_names::YEAR_2024_ID],
        [word_names::YEAR_2025, word_names::YEAR_2025_ID],
        [word_names::YEAR_2026, word_names::YEAR_2026_ID],
        [word_names::YEAR_2027, word_names::YEAR_2027_ID],
        [word_names::YEAR_2028, word_names::YEAR_2028_ID],
        [word_names::YEAR_2029, word_names::YEAR_2029_ID],
        [word_names::YEAR_2030, word_names::YEAR_2030_ID],
        [word_names::LIGHT, word_names::LIGHT_ID],
        [word_names::SPEED, word_names::SPEED_ID],
        [word_names::METRE, word_names::METRE_ID],
        [word_names::M, word_names::M_ID],
        [word_names::JOULE, word_names::JOULE_ID],
        [word_names::KG, word_names::KG_ID],
        [word_names::HYPERFINE, word_names::HYPERFINE_ID],
        [word_names::TRANSITION, word_names::TRANSITION_ID],
        [word_names::FREQUENCY, word_names::FREQUENCY_ID],
        [word_names::HZ, word_names::HZ_ID],
        [word_names::DEFINITION, word_names::DEFINITION_ID],
        [word_names::YEAR_1983, word_names::YEAR_1983_ID],
        [word_names::YEAR_1967, word_names::YEAR_1967_ID],
        [word_names::THIS_NAME, word_names::THIS_ID],
        [word_names::PRIOR_NAME, word_names::PRIOR_ID],
        [word_names::EUR, word_names::EUR_ID],
        [word_names::GDP, word_names::GDP_ID],
        [word_names::EURO, word_names::EURO_ID],
        [word_names::DOLLAR, word_names::DOLLAR_ID],
        [word_names::EURO_SIGN, word_names::EURO_SIGN_ID],
        [word_names::SHARE, word_names::SHARE_ID],
        [word_names::RATIO, word_names::RATIO_ID],
        [word_names::CURRENCY, word_names::CURRENCY_ID],
        [words::POD, words::POD_ID],
        [words::MASTER_POD_NAME, words::MASTER_POD_NAME_ID],
        [words::CH, words::CH_ID],
        [words::CHF, words::CHF_ID],
        [words::DAY, words::DAY_ID],
        [words::FACTOR, words::FACTOR_ID],
        [words::HAPPY, words::HAPPY_ID],
        [words::LAUNCH, words::LAUNCH_ID],
        [words::MATH, words::MATH_ID],
        [words::NUMBER, words::NUMBER_ID],
        [words::PCT, words::PCT_ID],
        [words::POINT, words::POINT_ID],
        [words::POINTS, words::POINTS_ID],
        [words::SECOND, words::SECOND_ID],
        [words::TIME, words::TIME_ID],
        [words::TOTAL_PRE, words::TOTAL_ID],
        [words::URL, words::URL_ID],
        [words::VERBS, words::VERBS_ID],
        [words::WEEK, words::WEEK_ID],
        [words::YEAR_CAP, words::YEAR_CAP_ID],
    ];

    const array TRIPLES = [
        [triple_names::MATH_CONST, triple_names::MATH_CONST_ID],
        [triple_names::PI_SYMBOL, triple_names::PI_SYMBOL_ID],
        [triple_names::CS_133, triple_names::CS_133_ID],
        [triple_names::EULER_NUMBER, triple_names::EULER_NUMBER_ID],
        [triple_names::E, triple_names::E_ID],
        [triple_names::E_NUM, triple_names::E_NUM_ID],
        [triple_names::PI, triple_names::PI_ID],
        [triple_names::SPEED_OF_LIGHT, triple_names::SPEED_OF_LIGHT_ID],
        [triple_names::M_PER_S, triple_names::M_PER_S_ID],
        [triple_names::TRANSITION_CS, triple_names::TRANSITION_CS_ID],
        [triple_names::TRANSITION_FREQUENCY, triple_names::TRANSITION_FREQUENCY_ID],
        [triple_names::HYPERFINE_TRANSITION, triple_names::HYPERFINE_TRANSITION_ID],
        [triple_names::DEFINITION_YEAR_1983, triple_names::DEFINITION_YEAR_1983_ID],
        [triple_names::DEFINITION_YEAR_1967, triple_names::DEFINITION_YEAR_1967_ID],
        [triple_names::DEFINITION_YEAR, triple_names::DEFINITION_YEAR_ID],
        [triple_names::YEAR_1983, triple_names::YEAR_1983_ID],
        [triple_names::YEAR_1967, triple_names::YEAR_1967_ID],
        [triple_names::YEAR_2019, triple_names::YEAR_2019_ID],
        [triple_names::YEAR_2020, triple_names::YEAR_2020_ID],
        [triple_names::YEAR_2021, triple_names::YEAR_2021_ID],
        [triple_names::YEAR_2022, triple_names::YEAR_2022_ID],
        [triple_names::YEAR_2023, triple_names::YEAR_2023_ID],
        [triple_names::YEAR_2024, triple_names::YEAR_2024_ID],
        [triple_names::YEAR_2025, triple_names::YEAR_2025_ID],
        [triple_names::YEAR_2026, triple_names::YEAR_2026_ID],
        [triple_names::YEAR_2027, triple_names::YEAR_2027_ID],
        [triple_names::YEAR_2028, triple_names::YEAR_2028_ID],
        [triple_names::YEAR_2029, triple_names::YEAR_2029_ID],
        [triple_names::YEAR_2030, triple_names::YEAR_2030_ID],
        [triple_names::GLOBAL_PROBLEM, triple_names::GLOBAL_PROBLEM_ID],
        [triple_names::GLOBAL_WARMING, triple_names::GLOBAL_WARMING_ID],
        [triple_names::GWP, triple_names::GWP_ID],
        [triple_names::TIME_POINTS, triple_names::TIME_POINTS_ID],
        [triple_names::HAPPY_TIME_POINTS, triple_names::HAPPY_TIME_POINTS_ID],
        [triple_names::GLOBAL_WARMING_PROBLEM, triple_names::GLOBAL_WARMING_PROBLEM_ID],
        [triple_names::POPULISM_PROBLEM, triple_names::POPULISM_PROBLEM_ID],
        [triple_names::POTENTIAL_HEALTH_PROBLEM, triple_names::POTENTIAL_HEALTH_PROBLEM_ID],
        [triple_names::POVERTY_PROBLEM, triple_names::POVERTY_PROBLEM_ID],
        [triple_names::POTENTIAL_EDUCATION_PROBLEM, triple_names::POTENTIAL_EDUCATION_PROBLEM_ID],
        [triple_names::DISINFORMATION_PROBLEM, triple_names::DISINFORMATION_PROBLEM_ID],
        [triple_names::WEALTH_CONCENTRATION_PROBLEM, triple_names::WEALTH_CONCENTRATION_PROBLEM_ID],
        [triple_names::MARKET_POWER_PROBLEM, triple_names::MARKET_POWER_PROBLEM_ID],
        [triple_names::BIASED_INFORMATION_PROBLEM, triple_names::BIASED_INFORMATION_PROBLEM_ID],
        [triple_names::CITIZEN_PARTICIPATION_PROBLEM, triple_names::CITIZEN_PARTICIPATION_PROBLEM_ID],
        [triple_names::GDP_MISMEASUREMENT_PROBLEM, triple_names::GDP_MISMEASUREMENT_PROBLEM_ID],
        [triple_names::PROPRIETARY_SOFTWARE_PROBLEM, triple_names::PROPRIETARY_SOFTWARE_PROBLEM_ID],
        [triple_names::BLACK_BOX_AI_PROBLEM, triple_names::BLACK_BOX_AI_PROBLEM_ID],
        [triple_names::REDUCE_EMISSIONS, triple_names::REDUCE_EMISSIONS_ID],
        [triple_names::AVOID_WRONG_DECISIONS, triple_names::AVOID_WRONG_DECISIONS_ID],
        [triple_names::REDUCE_EMISSIONS_SOLUTION, triple_names::REDUCE_EMISSIONS_SOLUTION_ID],
        [triple_names::AVOID_WRONG_DECISIONS_SOLUTION, triple_names::AVOID_WRONG_DECISIONS_SOLUTION_ID],
        [triple_names::RESEARCH_SOLUTION, triple_names::RESEARCH_SOLUTION_ID],
        [triple_names::TAXES_SOLUTION, triple_names::TAXES_SOLUTION_ID],
        [triple_names::SPENDING_SOLUTION, triple_names::SPENDING_SOLUTION_ID],
        [triple_names::BASIC_INCOME_SOLUTION, triple_names::BASIC_INCOME_SOLUTION_ID],
        [triple_names::PLATFORM_REGULATION_SOLUTION, triple_names::PLATFORM_REGULATION_SOLUTION_ID],
        [triple_names::MARKET_SHARE_TAX_SOLUTION, triple_names::MARKET_SHARE_TAX_SOLUTION_ID],
        [triple_names::DELPHI_METHOD_SOLUTION, triple_names::DELPHI_METHOD_SOLUTION_ID],
        [triple_names::PUBLIC_AI_SOLUTION, triple_names::PUBLIC_AI_SOLUTION_ID],
        [triple_names::FLUID_DEMOCRACY_SOLUTION, triple_names::FLUID_DEMOCRACY_SOLUTION_ID],
        [triple_names::GROSS_DOMESTIC_USAGE_SOLUTION, triple_names::GROSS_DOMESTIC_USAGE_SOLUTION_ID],
        [triple_names::FREE_SOFTWARE_SOLUTION, triple_names::FREE_SOFTWARE_SOLUTION_ID],
        [triple_names::WEALTH_CONCENTRATION, triple_names::WEALTH_CONCENTRATION_ID],
        [triple_names::MARKET_POWER, triple_names::MARKET_POWER_ID],
        [triple_names::BIASED_INFORMATION, triple_names::BIASED_INFORMATION_ID],
        [triple_names::BLACK_BOX_AI, triple_names::BLACK_BOX_AI_ID],
        [triple_names::CITIZEN_PARTICIPATION, triple_names::CITIZEN_PARTICIPATION_ID],
        [triple_names::GDP_MISMEASUREMENT, triple_names::GDP_MISMEASUREMENT_ID],
        [triple_names::PROPRIETARY_SOFTWARE, triple_names::PROPRIETARY_SOFTWARE_ID],
        [triple_names::BASIC_INCOME, triple_names::BASIC_INCOME_ID],
        [triple_names::PLATFORM_REGULATION, triple_names::PLATFORM_REGULATION_ID],
        [triple_names::MARKET_SHARE_TAX, triple_names::MARKET_SHARE_TAX_ID],
        [triple_names::DELPHI_METHOD, triple_names::DELPHI_METHOD_ID],
        [triple_names::PUBLIC_AI, triple_names::PUBLIC_AI_ID],
        [triple_names::FLUID_DEMOCRACY, triple_names::FLUID_DEMOCRACY_ID],
        [triple_names::GROSS_DOMESTIC_USAGE, triple_names::GROSS_DOMESTIC_USAGE_ID],
        [triple_names::FREE_SOFTWARE, triple_names::FREE_SOFTWARE_ID],
        [triple_names::SYSTEM_COLUMN, triple_names::SYSTEM_COLUMN_ID],
        [triple_names::SYSTEM_COLUMN_MAYOR, triple_names::SYSTEM_COLUMN_MAYOR_ID],
        [triple_names::POPULISM_SOLUTION, triple_names::POPULISM_SOLUTION_ID],
        [triple_names::SYSTEM_COLUMN_MAIN, triple_names::SYSTEM_COLUMN_MAIN_ID],
        [triple_names::USE_JAVASCRIPT, triple_names::USE_JAVASCRIPT_ID],
        [triple_names::FOR_CALCULATOR, triple_names::FOR_CALCULATOR_ID],
        [triple_names::SYSTEM_CHART_TYPE, triple_names::SYSTEM_CHART_TYPE_ID],
        [triple_names::SYSTEM_CHART_TYPE_DEFAULT, triple_names::SYSTEM_CHART_TYPE_DEFAULT_ID],
        [triple_names::SYSTEM_CHART_TYPE_ALTERNATIVE, triple_names::SYSTEM_CHART_TYPE_ALTERNATIVE_ID],
        [triple_names::SYSTEM_CHART_TYPE_MAYOR, triple_names::SYSTEM_CHART_TYPE_MAYOR_ID],
        [triple_names::GAIN_AND_LOSS, triple_names::GAIN_AND_LOSS_ID],
        [triple_names::LEVERAGE_OF_GAIN_AND_LOSS, triple_names::LEVERAGE_OF_GAIN_AND_LOSS_ID],
        [triple_names::CHART_LEVERAGE_OF_GAIN_AND_LOSS, triple_names::CHART_LEVERAGE_OF_GAIN_AND_LOSS_ID],
        [triple_names::FIELD_NUMBER_WITH_LIMITS, triple_names::FIELD_NUMBER_WITH_LIMITS_ID],
        [triple_names::FIELD_PHRASE_SELECTOR_TYPE, triple_names::FIELD_PHRASE_SELECTOR_TYPE_ID],
        [triple_names::FIELD_LIMIT_DOWN, triple_names::FIELD_LIMIT_DOWN_ID],
        [triple_names::FIELD_STEP, triple_names::FIELD_STEP_ID],
        [triple_names::INITIAL_EFFORT, triple_names::INITIAL_EFFORT_ID],
        [triple_names::LOSS_REDUCTION, triple_names::LOSS_REDUCTION_ID],
        [triple_names::REWARD_RATIO, triple_names::REWARD_RATIO_ID],
        [triple_names::PERSON_YEAR, triple_names::PERSON_YEAR_ID],
        [triple_names::SYSTEM_COLUMN_MARGINAL, triple_names::SYSTEM_COLUMN_MARGINAL_ID],
        [triple_names::COLUMN_LOSS_REDUCTION, triple_names::COLUMN_LOSS_REDUCTION_ID],
        [triple_names::COLUMN_INITIAL_EFFORT, triple_names::COLUMN_INITIAL_EFFORT_ID],
        [triple_names::COLUMN_REWARD_RATIO, triple_names::COLUMN_REWARD_RATIO_ID],
        [triple_names::RANGE_BARS_OF_LOSS, triple_names::RANGE_BARS_OF_LOSS_ID],
        [triple_names::GAIN_AND_EFFORT, triple_names::GAIN_AND_EFFORT_ID],
        [triple_names::SCATTER_OF_GAIN_AND_EFFORT, triple_names::SCATTER_OF_GAIN_AND_EFFORT_ID],
        [triple_names::CHART_RANGE_BARS_OF_LOSS, triple_names::CHART_RANGE_BARS_OF_LOSS_ID],
        [triple_names::CHART_SCATTER_OF_GAIN_AND_EFFORT, triple_names::CHART_SCATTER_OF_GAIN_AND_EFFORT_ID],
        [triple_names::SYSTEM_SORT_ORDER, triple_names::SYSTEM_SORT_ORDER_ID],
        [triple_names::SYSTEM_SORT_ORDER_DEFAULT, triple_names::SYSTEM_SORT_ORDER_DEFAULT_ID],
        [triple_names::SYSTEM_VARIANCE_HIGH, triple_names::SYSTEM_VARIANCE_HIGH_ID],
        [triple_names::SYSTEM_VARIANCE_LOW, triple_names::SYSTEM_VARIANCE_LOW_ID],
        [triple_names::NUMERIC_DESC_OF_LOSS, triple_names::NUMERIC_DESC_OF_LOSS_ID],
        [triple_names::SORT_NUMERIC_DESC_OF_LOSS, triple_names::SORT_NUMERIC_DESC_OF_LOSS_ID],
        [triple_names::COLUMN_PROBLEM, triple_names::COLUMN_PROBLEM_ID],
        [triple_names::COLUMN_SOLUTION, triple_names::COLUMN_SOLUTION_ID],
        [triple_names::COLUMN_SOLUTION_AFTER_REASON, triple_names::COLUMN_SOLUTION_AFTER_REASON_ID],
        [triple_names::COLUMN_COST_EXPLAINS_PROBLEM, triple_names::COLUMN_COST_EXPLAINS_PROBLEM_ID],
        [triple_names::COLUMN_COST, triple_names::COLUMN_COST_ID],
        [triple_names::POTENTIAL_LOSS, triple_names::POTENTIAL_LOSS_ID],
        [triple_names::POTENTIAL_GAIN, triple_names::POTENTIAL_GAIN_ID],
        [triple_names::COLUMN_POTENTIAL_LOSS, triple_names::COLUMN_POTENTIAL_LOSS_ID],
        [triple_names::COLUMN_POTENTIAL_GAIN, triple_names::COLUMN_POTENTIAL_GAIN_ID],
        [triple_names::POTENTIAL_GAIN_IN_EUR, triple_names::POTENTIAL_GAIN_IN_EUR_ID],
        [triple_names::POTENTIAL_GAIN_IN_HTP, triple_names::POTENTIAL_GAIN_IN_HTP_ID],
        [triple_names::COLUMN_POTENTIAL_GAIN_IN_EUR, triple_names::COLUMN_POTENTIAL_GAIN_IN_EUR_ID],
        [triple_names::COLUMN_POTENTIAL_GAIN_IN_HTP, triple_names::COLUMN_POTENTIAL_GAIN_IN_HTP_ID],
        [triple_names::SYSTEM_COLUMN_MINOR, triple_names::SYSTEM_COLUMN_MINOR_ID],
        [triple_names::CLIMATE_GAS_EMISSIONS, triple_names::CLIMATE_GAS_EMISSIONS_ID],
        [triple_names::CLIMATE_GAS_EMISSIONS_REASON, triple_names::CLIMATE_GAS_EMISSIONS_REASON_ID],
        [triple_names::COLUMN_REASON, triple_names::COLUMN_REASON_ID],
        [triple_names::POTENTIAL_LOSS_OF_REASON, triple_names::POTENTIAL_LOSS_OF_REASON_ID],
        [triple_names::COLUMN_POTENTIAL_LOSS_OF_REASON, triple_names::COLUMN_POTENTIAL_LOSS_OF_REASON_ID],
        [triple_names::COLUMN_REASON_AFTER_PROBLEM, triple_names::COLUMN_REASON_AFTER_PROBLEM_ID],
        [triple_names::COLUMN_LOSS_OF_REASON_EXPLAINS_REASON, triple_names::COLUMN_LOSS_OF_REASON_EXPLAINS_REASON_ID],
        [triple_names::GLOBAL_WARMING_REASON, triple_names::GLOBAL_WARMING_REASON_ID],
        [triple_names::HEAT_MORTALITY, triple_names::HEAT_MORTALITY_ID],
        [triple_names::HEAT_MORTALITY_REASON, triple_names::HEAT_MORTALITY_REASON_ID],
        [triple_names::CLIMATE_GAS_EMISSIONS_GLOBAL_WARMING_REASON, triple_names::CLIMATE_GAS_EMISSIONS_GLOBAL_WARMING_REASON_ID],
        [triple_names::HEAT_MORTALITY_GLOBAL_WARMING_REASON, triple_names::HEAT_MORTALITY_GLOBAL_WARMING_REASON_ID],
        [triple_names::CLIMATE_ADAPTATION, triple_names::CLIMATE_ADAPTATION_ID],
        [triple_names::CLIMATE_ADAPTATION_SOLUTION, triple_names::CLIMATE_ADAPTATION_SOLUTION_ID],
        [triple_names::GLOBAL_WARMING_SOLUTION, triple_names::GLOBAL_WARMING_SOLUTION_ID],
        [triple_names::REDUCE_EMISSIONS_GLOBAL_WARMING_SOLUTION, triple_names::REDUCE_EMISSIONS_GLOBAL_WARMING_SOLUTION_ID],
        [triple_names::CLIMATE_ADAPTATION_GLOBAL_WARMING_SOLUTION, triple_names::CLIMATE_ADAPTATION_GLOBAL_WARMING_SOLUTION_ID],
        [triple_names::COLUMN_POTENTIAL_LOSS_EXPLAINS_PROBLEM, triple_names::COLUMN_POTENTIAL_LOSS_EXPLAINS_PROBLEM_ID],
        [triple_names::COLUMN_POTENTIAL_GAIN_EXPLAINS_SOLUTION, triple_names::COLUMN_POTENTIAL_GAIN_EXPLAINS_SOLUTION_ID],
        [triple_names::GRAM_PER_KWH, triple_names::GRAM_PER_KWH_ID],
        [triple_names::CASH_FLOW, triple_names::CASH_FLOW_ID],
        [triple_names::CASH_FLOW_STATEMENT, triple_names::CASH_FLOW_STATEMENT_ID],
        [triple_names::INCOME_TAX, triple_names::INCOME_TAX_ID],
        [triple_names::SECOND, triple_names::SECOND_ID],
        [triple_names::CANTON_ZURICH, triple_names::CANTON_ZURICH_ID],
        [triple_names::CITY_ZH, triple_names::CITY_ZH_ID],
        [triple_names::CITY_BE, triple_names::CITY_BE_ID],
        [triple_names::CITY_GE, triple_names::CITY_GE_ID],
        [triple_names::COMPANY_ZURICH, triple_names::COMPANY_ZURICH_ID],
        [triple_names::MIO_SYMBOL, triple_names::MIO_SYMBOL_ID],
        [triple_names::M_SYMBOL, triple_names::M_SYMBOL_ID],
        [triple_names::FACTOR_SYMBOL, triple_names::FACTOR_SYMBOL_ID],
        [triple_names::PERCENT_SYMBOL, triple_names::PERCENT_SYMBOL_ID],
        [triple_names::CHF_SYMBOL, triple_names::CHF_SYMBOL_ID],
        [triple_names::SWISS_FRANC, triple_names::SWISS_FRANC_ID],
        [triple_names::SWISS_FRANC_CURRENCY, triple_names::SWISS_FRANC_CURRENCY_ID],
        [triple_names::U_S_DOLLAR_NAME, triple_names::U_S_DOLLAR_ID],
        [triple_names::US_DOLLAR_NAME, triple_names::US_DOLLAR_ID],
        [triple_names::US_DOLLAR_CURRENCY, triple_names::US_DOLLAR_CURRENCY_ID],
        [triple_names::EURO_CURRENCY, triple_names::EURO_CURRENCY_ID],
        [triple_names::USD_SYMBOL, triple_names::USD_SYMBOL_ID],
        [triple_names::DOLLAR_ALIAS, triple_names::DOLLAR_ALIAS_ID],
        [triple_names::U_S_DOLLAR_ALIAS, triple_names::U_S_DOLLAR_ALIAS_ID],
        [triple_names::IN_USD, triple_names::IN_USD_ID],
        [triple_names::EUR_SYMBOL, triple_names::EUR_SYMBOL_ID],
        [triple_names::EURO_SIGN_SYMBOL, triple_names::EURO_SIGN_SYMBOL_ID],
        [triple_names::SHARE_RATIO, triple_names::SHARE_RATIO_ID],
        [triple_names::SHARE_SYMBOL, triple_names::SHARE_SYMBOL_ID],
        [triple_names::EURO_SIGN_ALIAS, triple_names::EURO_SIGN_ALIAS_ID],
        [triple_names::IN_EUR, triple_names::IN_EUR_ID],
        [triple_names::COMPANY_VESTAS, triple_names::COMPANY_VESTAS_ID],
        [triple_names::COMPANY_ABB, triple_names::COMPANY_ABB_ID],
        [triples::SYSTEM_CONFIG, triples::SYSTEM_CONFIG_ID],
    ];

    const array FORMULAS = [
        [formula_names::SCALE_TO_SEC, formula_names::SCALE_TO_SEC_ID],
        [formula_names::SCALE_HOUR, formula_names::SCALE_HOUR_ID],
        [formula_names::THIS_NAME, formula_names::THIS_ID],
        [formula_names::PRIOR, formula_names::PRIOR_ID],
        [formula_names::SCALE_MIO_TO_ONE, formula_names::SCALE_MIO_TO_ONE_ID],
        [formula_names::SCALE_BIL_TO_ONE, formula_names::SCALE_BIL_TO_ONE_ID],
        [formula_names::SCALE_TRILLION_TO_ONE, formula_names::SCALE_TRILLION_TO_ONE_ID],
        [formula_names::GLOBAL_HTP, formula_names::GLOBAL_HTP_ID],
        [formula_names::JOULE_DEF, formula_names::JOULE_DEF_ID],
        [formula_names::CITY_POPULATION, formula_names::CITY_POPULATION_ID],
        [formulas::INCREASE, formulas::INCREASE_ID],
    ];

    // the verbs and views are pinned by the code id
    const array VERBS = [
        [verbs::NOT_SET, verbs::NOT_SET_ID],
        [verbs::IS, verbs::IS_ID],
        [verbs::PART, verbs::PART_ID],
        [verbs::OF, verbs::OF_ID],
        [verbs::ON, verbs::ON_ID],
        [verbs::WITH, verbs::WITH_ID],
        [verbs::HAS, verbs::HAS_ID],
        [verbs::CAN_BE_PART_OF, verbs::CAN_BE_PART_OF_ID],
        [verbs::FOLLOW, verbs::FOLLOW_ID],
        [verbs::MEASURE, verbs::MEASURE_ID],
        [verbs::ALIAS, verbs::ALIAS_ID],
        [verbs::CAN_CONTAIN, verbs::CAN_CONTAIN_ID],
        [verbs::CAN, verbs::CAN_ID],
        [verbs::CAN_BE, verbs::CAN_BE_ID],
        [verbs::CAN_GET, verbs::CAN_GET_ID],
        [verbs::CAN_CAUSE, verbs::CAN_CAUSE_ID],
        [verbs::CAN_HAVE, verbs::CAN_HAVE_ID],
        [verbs::CAN_USE, verbs::CAN_USE_ID],
        [verbs::SCALED, verbs::SCALED_ID],
        [verbs::PER, verbs::PER_ID],
        [verbs::TIMES, verbs::TIMES_ID],
        [verbs::IN, verbs::IN_ID],
        [verbs::TO, verbs::TO_ID],
        [verbs::SYMBOL, verbs::SYMBOL_ID],
        [verbs::AND, verbs::AND_ID],
        [verbs::RANK, verbs::RANK_ID],
        [verbs::SELECTOR, verbs::SELECTOR_ID],
        [verbs::BETWEEN, verbs::BETWEEN_ID],
        [verbs::KIND_OF, verbs::KIND_OF_ID],
        [verbs::NAME_OF, verbs::NAME_OF_ID],
        [verbs::BY_PARTS, verbs::BY_PARTS_ID],
        [verbs::MUST_BE_ONE_OF, verbs::MUST_BE_ONE_OF_ID],
        [verbs::TIME_STEP, verbs::TIME_STEP_ID],
        [verbs::TERM_STEP, verbs::TERM_STEP_ID],
        [verbs::TERM_NEED_STEP, verbs::TERM_NEED_STEP_ID],
        [verbs::USES, verbs::USES_ID],
        [verbs::ISSUE, verbs::ISSUE_ID],
        [verbs::ACRONYM, verbs::ACRONYM_ID],
        [verbs::INFLUENCE, verbs::INFLUENCE_ID],
        [verbs::USED_BY, verbs::USED_BY_ID],
        [verbs::CAN_BE_MADE_OF, verbs::CAN_BE_MADE_OF_ID],
        [verbs::CAN_BE_PACKED_IN, verbs::CAN_BE_PACKED_IN_ID],
        [verbs::USED_FOR, verbs::USED_FOR_ID],
        [verbs::BEFORE, verbs::BEFORE_ID],
        [verbs::AFTER, verbs::AFTER_ID],
        [verbs::SUPPORTS, verbs::SUPPORTS_ID],
        [verbs::EXPLAINS, verbs::EXPLAINS_ID],
        [verbs::REFINES, verbs::REFINES_ID],
        [verbs::EVIDENCE, verbs::EVIDENCE_ID],
        [verbs::ANALOGOUS, verbs::ANALOGOUS_ID],
        [verbs::LIMITS, verbs::LIMITS_ID],
        [verbs::ENABLES, verbs::ENABLES_ID],
        [verbs::REDUCES, verbs::REDUCES_ID],
        [verbs::ADDS_TO, verbs::ADDS_TO_ID],
        [verbs::COMPETES, verbs::COMPETES_ID],
        [verbs::AIMS_TO_TRIGGER, verbs::AIMS_TO_TRIGGER_ID],
        [verbs::LOWERS_BARRIER, verbs::LOWERS_BARRIER_ID],
        [verbs::SINGLE_ATTEMPT, verbs::SINGLE_ATTEMPT_ID],
        [verbs::EXPECTED_FROM, verbs::EXPECTED_FROM_ID],
        [verbs::WEIGHTED, verbs::WEIGHTED_ID],
    ];

    const array VIEWS = [
        [views::START_NAME, views::START_ID],
        [views::TABLE, views::TABLE_ID],
        [views::WORD, views::WORD_ID],
        [views::TRIPLE, views::TRIPLE_ID],
        [views::VALUE, views::VALUE_DEFAULT_ID],
        [views::SOURCE, views::SOURCE_ID],
        [views::REF, views::REF_ID],
        [views::FORMULA, views::FORMULA_ID],
        [views::RESULT, views::RESULT_ID],
        [views::VERB, views::VERB_ID],
        [views::LOGIN, views::LOGIN_ID],
        [views::LOGOUT, views::LOGOUT_ID],
        [views::SIGNUP, views::SIGNUP_ID],
        [views::LOGIN_ACTIVATE, views::LOGIN_ACTIVATE_ID],
        [views::LOGIN_RESET, views::LOGIN_RESET_ID],
        [views::CALCULATOR, views::CALCULATOR_ID],
        [views::COMPARISON_TABLE, views::COMPARISON_TABLE_ID],
        [views::TABLE_EDITOR, views::TABLE_EDITOR_ID],
        [views::VALUE_OVERWRITE, views::VALUE_OVERWRITE_ID],
        [views::RESULT_OVERWRITE, views::RESULT_OVERWRITE_ID],
        [views::VALUE_ADD_NO_JS, views::VALUE_ADD_NO_JS_ID],
        [views::VALUE_ADD, views::VALUE_ADD_ID],
        [views::WORD_ADD, views::WORD_ADD_ID],
        [views::TRIPLE_ADD, views::TRIPLE_ADD_ID],
        [views::WORD_DEFAULT_DETAIL, views::WORD_DEFAULT_DETAIL_ID],
        [views::TRIPLE_DEFAULT_DETAIL, views::TRIPLE_DEFAULT_DETAIL_ID],
        [views::VALUE_DEFAULT_DETAIL, views::VALUE_DEFAULT_DETAIL_ID],
        [views::SOURCE_DEFAULT_DETAIL, views::SOURCE_DEFAULT_DETAIL_ID],
        [views::REF_DEFAULT_DETAIL, views::REF_DEFAULT_DETAIL_ID],
        [views::FORMULA_DEFAULT_DETAIL, views::FORMULA_DEFAULT_DETAIL_ID],
        [views::RESULT_DEFAULT_DETAIL, views::RESULT_DEFAULT_DETAIL_ID],
        [views::VERB_DEFAULT_DETAIL, views::VERB_DEFAULT_DETAIL_ID],
        [views::WORD_DEFAULT_RANGE, views::WORD_DEFAULT_RANGE_ID],
        [views::TRIPLE_DEFAULT_RANGE, views::TRIPLE_DEFAULT_RANGE_ID],
        [views::VALUE_DEFAULT_RANGE, views::VALUE_DEFAULT_RANGE_ID],
        [views::SOURCE_DEFAULT_RANGE, views::SOURCE_DEFAULT_RANGE_ID],
        [views::REF_DEFAULT_RANGE, views::REF_DEFAULT_RANGE_ID],
        [views::FORMULA_DEFAULT_RANGE, views::FORMULA_DEFAULT_RANGE_ID],
        [views::RESULT_DEFAULT_RANGE, views::RESULT_DEFAULT_RANGE_ID],
        [views::VERB_DEFAULT_RANGE, views::VERB_DEFAULT_RANGE_ID],
        [views::WORD_DEFAULT_DETAIL_RANGE, views::WORD_DEFAULT_DETAIL_RANGE_ID],
        [views::TRIPLE_DEFAULT_DETAIL_RANGE, views::TRIPLE_DEFAULT_DETAIL_RANGE_ID],
        [views::VALUE_DEFAULT_DETAIL_RANGE, views::VALUE_DEFAULT_DETAIL_RANGE_ID],
        [views::SOURCE_DEFAULT_DETAIL_RANGE, views::SOURCE_DEFAULT_DETAIL_RANGE_ID],
        [views::REF_DEFAULT_DETAIL_RANGE, views::REF_DEFAULT_DETAIL_RANGE_ID],
        [views::FORMULA_DEFAULT_DETAIL_RANGE, views::FORMULA_DEFAULT_DETAIL_RANGE_ID],
        [views::RESULT_DEFAULT_DETAIL_RANGE, views::RESULT_DEFAULT_DETAIL_RANGE_ID],
        [views::VERB_DEFAULT_DETAIL_RANGE, views::VERB_DEFAULT_DETAIL_RANGE_ID],
        [views::WORD_ADD_DETAIL, views::WORD_ADD_DETAIL_ID],
        [views::WORD_EDIT, views::WORD_EDIT_ID],
        [views::WORD_DEL, views::WORD_DEL_ID],
        [views::TRIPLE_ADD_DETAIL, views::TRIPLE_ADD_DETAIL_ID],
        [views::TRIPLE_EDIT, views::TRIPLE_EDIT_ID],
        [views::TRIPLE_DEL, views::TRIPLE_DEL_ID],
        [views::VALUE_ADD_DETAIL, views::VALUE_ADD_DETAIL_ID],
        [views::VALUE_EDIT, views::VALUE_EDIT_ID],
        [views::VALUE_DEL, views::VALUE_DEL_ID],
        [views::FORMULA_ADD, views::FORMULA_ADD_ID],
        [views::FORMULA_EDIT, views::FORMULA_EDIT_ID],
        [views::FORMULA_DEL, views::FORMULA_DEL_ID],
        [views::FORMULA_TEST, views::FORMULA_TEST_ID],
        [views::RESULT_ADD, views::RESULT_ADD_ID],
        [views::RESULT_EDIT, views::RESULT_EDIT_ID],
        [views::RESULT_DEL, views::RESULT_DEL_ID],
        [views::RESULT_EXPLAIN, views::RESULT_EXPLAIN_ID],
        [views::SOURCE_ADD, views::SOURCE_ADD_ID],
        [views::SOURCE_EDIT, views::SOURCE_EDIT_ID],
        [views::SOURCE_DEL, views::SOURCE_DEL_ID],
        [views::REF_ADD, views::REF_ADD_ID],
        [views::REF_EDIT, views::REF_EDIT_ID],
        [views::REF_DEL, views::REF_DEL_ID],
        [views::VERB_ADD, views::VERB_ADD_ID],
        [views::VERB_EDIT, views::VERB_EDIT_ID],
        [views::VERB_DEL, views::VERB_DEL_ID],
        [views::GROUP_ADD, views::GROUP_ADD_ID],
        [views::GROUP_EDIT, views::GROUP_EDIT_ID],
        [views::GROUP_DEL, views::GROUP_DEL_ID],
        [views::FORMULA_LINK_ADD, views::FORMULA_LINK_ADD_ID],
        [views::FORMULA_LINK_EDIT, views::FORMULA_LINK_EDIT_ID],
        [views::FORMULA_LINK_DEL, views::FORMULA_LINK_DEL_ID],
        [views::FORMULA_LINK_DEFAULT, views::FORMULA_LINK_DEFAULT_ID],
        [views::VIEW_LINK_ADD, views::VIEW_LINK_ADD_ID],
        [views::VIEW_LINK_EDIT, views::VIEW_LINK_EDIT_ID],
        [views::VIEW_LINK_DEL, views::VIEW_LINK_DEL_ID],
        [views::TERM_VIEW_DEFAULT, views::TERM_VIEW_DEFAULT_ID],
        [views::VIEW_ADD, views::VIEW_ADD_ID],
        [views::VIEW_EDIT, views::VIEW_EDIT_ID],
        [views::VIEW_DEL, views::VIEW_DEL_ID],
        [views::VIEW, views::VIEW_DEFAULT_ID],
        [views::COMPONENT_ADD, views::COMPONENT_ADD_ID],
        [views::COMPONENT_EDIT, views::COMPONENT_EDIT_ID],
        [views::COMPONENT_DEL, views::COMPONENT_DEL_ID],
        [views::COMPONENT, views::COMPONENT_DEFAULT_ID],
        [views::COMPONENT_LINK_ADD, views::COMPONENT_LINK_ADD_ID],
        [views::COMPONENT_LINK_EDIT, views::COMPONENT_LINK_EDIT_ID],
        [views::COMPONENT_LINK_DEL, views::COMPONENT_LINK_DEL_ID],
        [views::COMPONENT_LINK_DEFAULT, views::COMPONENT_LINK_DEFAULT_ID],
        [views::VIEW_RELATION_ADD, views::VIEW_RELATION_ADD_ID],
        [views::VIEW_RELATION_EDIT, views::VIEW_RELATION_EDIT_ID],
        [views::VIEW_RELATION_DEL, views::VIEW_RELATION_DEL_ID],
        [views::VIEW_RELATION_DEFAULT, views::VIEW_RELATION_DEFAULT_ID],
        [views::CONFIRM_ADD, views::CONFIRM_ADD_ID],
        [views::CONFIRM_EDIT, views::CONFIRM_EDIT_ID],
        [views::CONFIRM_DEL, views::CONFIRM_DEL_ID],
        [views::CONFIRM_VIEW, views::CONFIRM_VIEWS_ID],
        [views::UNDO, views::UNDO_ID],
        [views::LIST_VIEWS, views::LIST_VIEWS_ID],
        [views::LIST_COMPONENTS, views::LIST_COMPONENTS_ID],
        [views::WORD_FIND, views::WORD_FIND_ID],
        [views::SEARCH_FULL, views::SEARCH_FULL_ID],
        [views::VALUE_FIND, views::VALUE_FIND_ID],
        [views::REF_FIND, views::REF_FIND_ID],
        [views::FIND_DUPLICATES, views::FIND_DUPLICATES_ID],
        [views::PHRASE, views::PHRASE_ID],
        [views::TERM, views::TERM_ID],
        [views::PHRASE_VALUES, views::PHRASE_VALUES_ID],
        [views::WORD_RELATED, views::WORD_RELATED_ID],
        [views::WORD_LOG, views::WORD_LOG_ID],
        [views::VALUE_DETAIL, views::VALUE_DETAIL_ID],
        [views::SANDBOX, views::SANDBOX_ID],
        [views::CHANGE_LOG_USER, views::CHANGE_LOG_USER_ID],
        [views::CHANGE_LOG_RECENT, views::CHANGE_LOG_RECENT_ID],
        [views::OBJECT_HISTORY, views::OBJECT_HISTORY_ID],
        [views::CHANGE_DETAIL, views::CHANGE_DETAIL_ID],
        [views::USER, views::USER_ID],
        [views::USER_CONFIG, views::USER_CONFIG_ID],
        [views::ADMIN_CONFIG, views::ADMIN_CONFIG_ID],
        [views::SYSTEM_CONFIG, views::SYSTEM_CONFIG_ID],
        [views::USER_QUARANTINE, views::USER_QUARANTINE_ID],
        [views::ALL_QUARANTINE, views::ALL_QUARANTINE_ID],
        [views::ADMIN_MAIN, views::ADMIN_MAIN_ID],
        [views::USER_ADMIN_ADD, views::USER_ADMIN_ADD_ID],
        [views::USER_ADMIN_EDIT, views::USER_ADMIN_EDIT_ID],
        [views::USER_ADMIN_DEL, views::USER_ADMIN_DEL_ID],
        [views::ERROR_LOG, views::ERROR_LOG_ID],
        [views::ERROR_UPDATE, views::ERROR_UPDATE_ID],
        [views::SYS_LOG_DETAIL, views::SYS_LOG_DETAIL_ID],
        [views::SYSTEM_LOG, views::SYSTEM_LOG_ID],
        [views::USER_JOBS, views::USER_JOBS_ID],
        [views::ALL_JOBS, views::ALL_JOBS_ID],
        [views::JOB_DETAIL, views::JOB_DETAIL_ID],
        [views::CALC_STATUS, views::CALC_STATUS_ID],
        [views::JOB_ASYNC, views::JOB_ASYNC_ID],
        [views::JOB_CONTROL, views::JOB_CONTROL_ID],
        [views::JOB_CHECK, views::JOB_CHECK_ID],
        [views::PASTE_TABLE, views::PASTE_TABLE_ID],
        [views::IMPORT, views::IMPORT_ID],
        [views::IMPORT_PREVIEW, views::IMPORT_PREVIEW_ID],
        [views::IMPORT_RESULT, views::IMPORT_RESULT_ID],
        [views::IMPORT_URL, views::IMPORT_URL_ID],
        [views::IMPORT_WIKIDATA, views::IMPORT_WIKIDATA_ID],
        [views::EXPORT, views::EXPORT_ID],
        [views::EXPORT_JSON, views::EXPORT_JSON_ID],
        [views::EXPORT_XML, views::EXPORT_XML_ID],
        [views::EXPORT_CSV, views::EXPORT_CSV_ID],
        [views::EXPORT_ODS, views::EXPORT_ODS_ID],
        [views::EXPORT_SELECTIONS, views::EXPORT_SELECTIONS_ID],
        [views::EXPORT_OBJECT, views::EXPORT_OBJECT_ID],
        [views::EXPORT_READY, views::EXPORT_READY_ID],
        [views::LANGUAGE_SELECT, views::LANGUAGE_SELECT_ID],
        [views::LANGUAGE, views::LANGUAGE_ID],
        [views::LIST_LANGUAGES, views::LIST_LANGUAGES_ID],
        [views::PHRASE_TRANSLATIONS, views::PHRASE_TRANSLATIONS_ID],
        [views::TRANSLATIONS_MISSING, views::TRANSLATIONS_MISSING_ID],
        [views::LANGUAGE_FORMS, views::LANGUAGE_FORMS_ID],
        [views::LANGUAGE_ADD, views::LANGUAGE_ADD_ID],
        [views::LANGUAGE_EDIT, views::LANGUAGE_EDIT_ID],
        [views::LANGUAGE_DEL, views::LANGUAGE_DEL_ID],
        [views::ABOUT, views::ABOUT_ID],
        [views::SETUP, views::SETUP_ID],
        [views::LIST_WORDS, views::LIST_WORDS_ID],
        [views::VERBS, views::VERBS_ID],
        [views::LIST_TRIPLES, views::LIST_TRIPLES_ID],
        [views::LIST_SOURCES, views::LIST_SOURCES_ID],
        [views::LIST_REFS, views::LIST_REFS_ID],
        [views::LIST_FORMULAS, views::LIST_FORMULAS_ID],
        [views::COMPLETE, views::COMPLETE_ID],
        [views::BASE_UNITS, views::BASE_UNITS_ID],
        [views::RANKING, views::RANKING_ID],
        [views::SCIENCE, views::SCIENCE_ID],
        [views::HISTORIC, views::HISTORIC_ID],
        [views::BIOLOGICAL, views::BIOLOGICAL_ID],
        [views::EDUCATION, views::EDUCATION_ID],
        [views::TOURISTIC, views::TOURISTIC_ID],
        [views::GRAPH, views::GRAPH_ID],
        [views::SIMPLE, views::SIMPLE_ID],
        [views::MATH_CONST, views::MATH_CONST_ID],
        [views::GLOBAL_PROBLEM, views::GLOBAL_PROBLEM_ID],
        [views::CURRENCY, views::CURRENCY_ID],
    ];

    const array COMPONENTS = [
        [components::WORD_NAME, components::WORD_ID],
        [components::MATRIX_NAME, components::MATRIX_ID],
        [components::SOLUTION_PRIO_TITLE_NAME, components::SOLUTION_PRIO_TITLE_ID],
        [components::FORM_NAME, components::FORM_NAME_ID],
        [components::FORM_PLURAL, components::FORM_PLURAL_ID],
    ];

    const array SOURCES = [
        [sources::SIB, sources::SIB_ID],
        [sources::WIKIDATA, sources::WIKIDATA_ID],
        [sources::BFS, sources::BFS_ID],
        [sources::TRUST_ME_BRO, sources::TRUST_ME_BRO_ID],
    ];

    // a reference is pinned by its external key
    const array REFS = [
        [refs::PI_KEY, refs::PI_ID],
        [refs::MATH_KEY, refs::MATH_ID],
    ];

    const array USERS = [
        [users::SYSTEM_NAME, users::SYSTEM_ID],
        [users::SYSTEM_ADMIN_NAME, users::SYSTEM_ADMIN_ID],
        [users::SYSTEM_TEST_NAME, users::SYSTEM_TEST_ID],
        [users::SYSTEM_TEST_PARTNER_NAME, users::SYSTEM_TEST_PARTNER_ID],
        [users::SYSTEM_TEST_ADMIN_NAME, users::SYSTEM_TEST_ADMIN_ID],
        [users::SYSTEM_TEST_NORMAL_NAME, users::SYSTEM_TEST_NORMAL_ID],
        [users::SYSTEM_LOG_NAME, users::SYSTEM_LOG_ID],
    ];

    const array CHANGE_TABLES = [
        [change_tables::USER, change_tables::USER_ID],
        [change_tables::WORD, change_tables::WORD_ID],
        [change_tables::TRIPLE, change_tables::TRIPLE_ID],
    ];

    // a field name is unique only within its table, so the pinned id must be one of the ids of the name
    const array CHANGE_FIELDS = [
        [change_fields::FLD_WORD_NAME, change_fields::FLD_WORD_NAME_ID],
    ];

    // the pinned ids of each class that has a list.csv
    const array PINNED_IDS = [
        word::class => self::WORDS,
        triple::class => self::TRIPLES,
        formula::class => self::FORMULAS,
        verb::class => self::VERBS,
        view::class => self::VIEWS,
        component::class => self::COMPONENTS,
        source::class => self::SOURCES,
        ref::class => self::REFS,
        user::class => self::USERS,
        change_table::class => self::CHANGE_TABLES,
        change_field::class => self::CHANGE_FIELDS,
    ];

    // the classes that define the pinned id consts of each class, which a re-pin may rewrite
    const array CONST_CLASSES = [
        word::class => [word_names::class, words::class],
        triple::class => [triple_names::class, triples::class],
        formula::class => [formula_names::class, formulas::class],
        verb::class => [verbs::class],
        view::class => [views::class],
        component::class => [components::class],
        source::class => [sources::class],
        ref::class => [refs::class],
        user::class => [users::class],
        change_table::class => [change_tables::class],
        change_field::class => [change_fields::class],
    ];

    /**
     * compare the pinned id of each object with the id of its name or code id in the list.csv
     * and in the database, so a shifted id is reported with the id that should be pinned;
     * with AUTO_RE_PIN_OBJECT_ID the const of a shifted id is rewritten in its php file,
     * which this run still reads with the old value, so the tests need a rerun;
     * without it the ids to re-pin are listed in docs/pinned_id_findings.md
     *
     * @param test_cleanup $t the test environment that collects the results
     * @param array $db_rows the csv lines of each class as just read from the database
     * @return bool true if all pinned ids match the list.csv files and the database
     */
    function check(test_cleanup $t, array $db_rows): bool
    {
        $lib = new library();
        $result = true;
        $md_txt = '';
        $tested = 0;
        $to_re_pin = 0;
        foreach (self::PINNED_IDS as $class => $pinned) {
            $csv_file_path = $lib->class_csv_file_path($class);
            $name = $lib->class_to_name($class);
            $file_ids = $this->key_ids(file($csv_file_path), $class);
            $db_ids = $this->key_ids($db_rows[$class] ?? [], $class);
            $test_name = 'the pinned ' . $name . ' ids match ' . $csv_file_path . ':';
            if (!$t->assert_empty($test_name, $this->id_diff($pinned, $file_ids))) {
                $result = false;
            }
            $test_name = 'the pinned ' . $name . ' ids match the database:';
            if (!$t->assert_empty($test_name, $this->id_diff($pinned, $db_ids))) {
                $result = false;
            }
            $re_pin_lst = $this->re_pin_list($class, $pinned, $file_ids, $db_ids);
            if (test_files::AUTO_RE_PIN_OBJECT_ID) {
                $test_name = 'the pinned ' . $name . ' ids are not re-pinned, else rerun the tests:';
                $t->assert_empty($test_name, $this->re_pin($re_pin_lst));
            }
            $md_txt .= $this->findings_md($name, count($pinned), $re_pin_lst);
            $tested += count($pinned);
            $to_re_pin += count($re_pin_lst);
        }
        if (!test_files::AUTO_RE_PIN_OBJECT_ID) {
            $t->update_path_file(test_files::DOCS_PINNED_ID_FINDINGS, $this->findings_header($tested, $to_re_pin) . $md_txt);
        }
        return $result;
    }

    /**
     * the pinned ids that differ from the database with the const that pins it and the new id;
     * a const is rewritten only if the name or code id has exactly one id in the database and
     * the list.csv has the same, so a stale list.csv or a name used more than once is never guessed
     *
     * @param string $class the class of the pinned objects e.g. word
     * @param array $pinned the pairs of the name or code id and the pinned id
     * @param array $file_ids the ids of each name and code id in the list.csv
     * @param array $db_ids the ids of each name and code id in the database
     * @return array for each id to re-pin the name, the pinned and the new id, the const as
     *               [reflection, const name, pattern] and why it cannot be rewritten or an empty text
     */
    private function re_pin_list(string $class, array $pinned, array $file_ids, array $db_ids): array
    {
        $result = [];
        foreach ($pinned as [$key, $id]) {
            $ids = array_values(array_unique($db_ids[$key] ?? []));
            if (!in_array($id, $ids)) {
                $csv_ids = array_values(array_unique($file_ids[$key] ?? []));
                $new_id = count($ids) == 1 ? $ids[0] : 0;
                $const = $this->pin_const($class, $key, $id);
                if ($new_id == 0) {
                    $reason = count($ids) . ' ids in the database';
                } elseif ($csv_ids != $ids) {
                    $reason = 'the list.csv has ' . (count($csv_ids) == 0 ? 'none' : implode(' or ', $csv_ids));
                } elseif ($const == null) {
                    $reason = 'no single const with the pinned id';
                } else {
                    $reason = '';
                }
                $result[] = [$key, $id, $new_id, $const, $reason];
            }
        }
        return $result;
    }

    /**
     * rewrite the number of each const that can be re-pinned without guessing
     *
     * @param array $re_pin_lst the ids to re-pin as created by re_pin_list
     * @return string the rewritten consts e.g. "triple_names::GRAM_PER_KWH_ID 1961 to 1963; " and
     *                the ids that need a re-pin by hand or an empty text if all ids are fine
     */
    private function re_pin(array $re_pin_lst): string
    {
        $result = '';
        foreach ($re_pin_lst as [$key, $id, $new_id, $const, $reason]) {
            if ($reason == '') {
                [$ref, $const_name, $pattern] = $const;
                $file = $ref->getFileName();
                file_put_contents($file, preg_replace($pattern, '${1}' . $new_id . ';', file_get_contents($file), 1));
                $result .= $ref->getShortName() . '::' . $const_name . ' ' . $id . ' to ' . $new_id . '; ';
            } else {
                $result .= $key . ' pinned as ' . $id . ' needs a re-pin by hand, because of ' . $reason . '; ';
            }
        }
        return $result;
    }

    /**
     * @param int $tested the number of pinned ids of all classes
     * @param int $to_re_pin the number of pinned ids that differ from the database
     * @return string the start of docs/pinned_id_findings.md
     */
    private function findings_header(int $tested, int $to_re_pin): string
    {
        $result = '# Pinned id findings' . "\n\n";
        $result .= 'generated by test_db_load::csv_recreate (src/test/php/create/test_pinned_ids.php) - do not edit manually' . "\n\n";
        $result .= 'the *_ID consts that pin the seed database id of an object for the tests compared with the database after the reset; ';
        $result .= 'with files::AUTO_RE_PIN_OBJECT_ID the test run rewrites the consts listed below, see docs/llm/testing.md' . "\n\n";
        $result .= $tested . ' pinned ids of ' . count(self::PINNED_IDS) . ' classes tested, ';
        if ($to_re_pin == 0) {
            $result .= 'all ids are fine' . "\n\n";
        } else {
            $result .= $to_re_pin . ' to re-pin' . "\n\n";
        }
        return $result;
    }

    /**
     * @param string $name the name of the class e.g. word
     * @param int $tested the number of pinned ids of the class
     * @param array $re_pin_lst the ids to re-pin as created by re_pin_list
     * @return string the section of the class in docs/pinned_id_findings.md
     */
    private function findings_md(string $name, int $tested, array $re_pin_lst): string
    {
        $result = '## ' . $name . "\n\n";
        if (count($re_pin_lst) == 0) {
            $result .= $tested . ' ' . $name . 's tested, all ids are fine' . "\n\n";
        } else {
            $result .= $tested . ' ' . $name . 's tested, ' . count($re_pin_lst) . ' to re-pin:' . "\n\n";
            foreach ($re_pin_lst as [$key, $id, $new_id, $const, $reason]) {
                if ($reason == '') {
                    [$ref, $const_name] = $const;
                    $result .= '- `' . $ref->getShortName() . '::' . $const_name . '` ' . $id . ' to ' . $new_id . ' (' . $key . ')' . "\n";
                } else {
                    $result .= '- ' . $key . ' pinned as ' . $id . ': re-pin by hand, because of ' . $reason . "\n";
                }
            }
            $result .= "\n";
        }
        return $result;
    }

    /**
     * find the one const that pins the id of the given name or code id; an id const that refers
     * to another const e.g. word_names::MATH_ID = words::MATH_ID is not used, because the const
     * it refers to is the one to rewrite
     *
     * @param string $class the class of the pinned object e.g. word
     * @param string $key the name or code id of the pinned object
     * @param int $id the pinned id
     * @return array|null the reflection of the const class, the const name and the pattern of
     *                    its definition or null if no single const is found
     */
    private function pin_const(string $class, string $key, int $id): ?array
    {
        $candidates = [];
        foreach (self::CONST_CLASSES[$class] as $const_class) {
            $ref = new ReflectionClass($const_class);
            $src = file_get_contents($ref->getFileName());
            foreach ($ref->getConstants() as $const => $value) {
                $pattern = '/(const int ' . $const . '\s*=\s*)' . $id . ';/';
                if (str_ends_with($const, '_ID') and $value === $id and preg_match($pattern, $src)) {
                    $candidates[] = [$ref, $const, $pattern];
                }
            }
        }
        // several consts with the same id e.g. views::WORD_ID and views::WORD_ADD_ID are told apart
        // by the const of the name that starts like the id const e.g. CHF for CHF_ID
        if (count($candidates) > 1) {
            $candidates = array_values(array_filter($candidates, function ($candidate) use ($key) {
                [$ref, $const] = $candidate;
                $base = substr($const, 0, -strlen('_ID'));
                foreach ($ref->getConstants() as $key_const => $value) {
                    if ($value === $key and str_starts_with($key_const, $base)) {
                        return true;
                    }
                }
                return false;
            }));
        }
        return count($candidates) == 1 ? $candidates[0] : null;
    }

    /**
     * @param array $pinned the pairs of the name or code id and the pinned id
     * @param array $key_ids the ids of each name and code id
     * @return string the pinned ids that differ e.g. "gram per kWh is 1963 not 1961; " or an empty text
     */
    private function id_diff(array $pinned, array $key_ids): string
    {
        $result = '';
        foreach ($pinned as [$key, $id]) {
            $ids = $key_ids[$key] ?? [];
            if (!in_array($id, $ids)) {
                if (count($ids) == 0) {
                    $result .= $key . ' is missing, pinned is ' . $id . '; ';
                } else {
                    $result .= $key . ' is ' . implode(' or ', array_unique($ids)) . ' not ' . $id . '; ';
                }
            }
        }
        return $result;
    }

    /**
     * @param array $csv the csv lines of one class starting with the header line
     * @param string $class the class of the rows e.g. word
     * @return array the ids of each name, external key and code id in the rows, because a name
     *               can be used more than once e.g. the same field name in two change tables
     */
    private function key_ids(array $csv, string $class): array
    {
        $lib = new library();
        $result = [];
        if (count($csv) > 0) {
            $key_fields = [$lib->class_to_name($class) . '_name', 'external_key', 'code_id'];
            $key_cols = array_keys(array_intersect($lib->csv_line_to_array($csv[0]), $key_fields));
            foreach (array_slice($csv, 1) as $line) {
                $fields = $lib->csv_line_to_array($line);
                foreach ($key_cols as $col) {
                    $key = $fields[$col] ?? '';
                    if ($key != '') {
                        $result[$key][] = (int)$fields[0];
                    }
                }
            }
        }
        return $result;
    }

}
