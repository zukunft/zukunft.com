<?php

/*

    test/php/const/word_names.php - predefined word names and related const used only for system testing
    -----------------------------

    the words used in the backend and frontend are in main/php/shared/const/words.php
    this separate class holds the test-only words, ids and lists
    and references the shared words via the shared_words alias

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

namespace Zukunft\ZukunftCom\test\php\const;

use Zukunft\ZukunftCom\main\php\shared\const\words as shared_words;

class word_names
{

    // word names, descriptions and database ids used only for system testing
    // *_ID is the expected database id based on the initial test data load

    // words from import file units.json in order of appearance
    const string MATH = 'mathematics';
    const string MATH_COM = 'Mathematics is an area of knowledge that includes the topics of numbers and formulas';
    const int MATH_ID = 1;
    const string CONST_NAME = 'constant';
    const string ONE = 'one';
    const int ONE_ID = 4;
    const string PI_SYMBOL = 'π';
    const int PI_SYMBOL_ID = 5;
    const string PI = 'Pi';
    const int PI_ID = 17;
    const string E_SYMBOL = "𝑒";
    const int E_SYMBOL_ID = 6;
    const string CIRCUMFERENCE = 'circumference';
    const int CIRCUMFERENCE_ID = 15;
    const string DIAMETER = 'diameter';
    const int DIAMETER_ID = 16;
    const string FLOW = 'flow';
    const int FLOW_ID = 97;
    const string MINUTE = 'minute';
    const int MINUTE_ID = 100;
    const string YEAR_2019 = '2019';
    const int YEAR_2019_ID = 135;
    const string YEAR_2020 = '2020';
    const int YEAR_2020_ID = 136;

    // words from import file scaling.json in order of appearance
    const string MIO = 'million';
    const int MIO_ID = 158;
    const string MIO_COM = '10⁶';
    // "mio" is the symbol word of "million" and has its own database row
    const string MIO_SHORT = 'mio';
    const int MIO_SHORT_ID = 271;
    const string MIO_SHORT_COM = 'the symbol used in formulas for million';
    const string BILLION = 'billion';
    const int BILLION_ID = 159;

    // words from import file time_definition.json in order of appearance
    const string THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const string PRIOR_NAME = 'prior';

    // words from import file base_phrases.json used for the offline phrase selection
    const string FACT = 'fact';
    const int FACT_ID = 191;
    const string GOVERNMENT = 'government';
    const int GOVERNMENT_ID = 193;
    const string GROUP = 'group';
    const int GROUP_ID = 195;
    const string HAND = 'hand';
    const int HAND_ID = 196;
    const string LIFE = 'life';
    const int LIFE_ID = 12;
    const string MAN = 'man';
    const int MAN_ID = 199;
    const string PART = 'part';
    const int PART_ID = 200;
    const string PERSON = 'person';
    const int PERSON_ID = 201;
    const string PLACE = 'place';
    const int PLACE_ID = 202;
    const string WAY = 'way';
    const int WAY_ID = 208;
    const string WOMAN = 'woman';
    const int WOMAN_ID = 209;
    const string WORK = 'work';
    const int WORK_ID = 210;
    const string WORLD = 'world';
    const int WORLD_ID = 211;

    // words from import file solution_prio.json used for the start page in order of appearance
    const string PROBLEM = 'problem';
    const int PROBLEM_ID = 205;
    const string GLOBAL = 'global';
    const int GLOBAL_ID = 194;
    const string POTENTIAL = 'potential';
    const int POTENTIAL_ID = 222;
    const string SOLUTION = 'solution';
    const int SOLUTION_ID = 214;
    const string LOSS = 'loss';
    const int LOSS_ID = 225;
    const string GAIN = 'gain';
    const int GAIN_ID = 223;
    const string CLIMATE = 'climate';
    const int CLIMATE_ID = 226;
    const string WARMER = 'warmer';
    const int WARMER_ID = 229;
    const string POPULISM = 'populism';
    const int POPULISM_ID = 233;
    const string POPULISM_COM = 'a range of political stances that emphasise the idea of the common people and often contrast this group against a privileged elite.';
    const string HEALTH = 'health';
    const int HEALTH_ID = 249;
    const string HEALTH_COM = 'a state of complete physical, mental and social well-being and not merely the absence of disease or infirmity.';
    const string POVERTY = 'poverty';
    const int POVERTY_ID = 251;
    const string POVERTY_COM = 'the state of having insufficient income or resources to meet basic human needs such as food, shelter and clothing.';
    const string EDUCATION = 'education';
    const int EDUCATION_ID = 253;
    const string EDUCATION_COM = 'process of teaching and learning';
    const string HTP = 'htp';
    const int HTP_ID = 269;
    const string TRILLION = 'trillion';
    const int TRILLION_ID = 270;
    const string USD = 'USD';
    const int USD_ID = 383;

    // words from import file company.json used for the start page in order of appearance
    const string SALES = 'sales';
    const int SALES_ID = 540;
    const string CASH = 'cash';
    const int CASH_ID = 541;
    const string STATEMENT = 'statement';
    const int STATEMENT_ID = 542;
    const string PARTS = 'parts';
    const int PARTS_ID = 544;
    const string INCOME = 'income';
    const int INCOME_ID = 298;
    const string TAX = 'tax';
    const int TAX_ID = 301;

    // words from import file country.json used for the start page in order of appearance
    const string GERMANY = 'Germany';
    const string CANTON = 'canton';
    const int CANTON_ID = 186;
    const string CITY = 'city';
    const int CITY_ID = 188;
    const string ZH = 'Zurich';
    const int ZH_ID = 213;
    const string BE = 'Bern';
    const int BE_ID = 185;
    const string GE = 'Geneva';
    const int GE_ID = 192;
    const int INHABITANT_ID = 197;
    // TODO add test to search for words in all language forms e.g. plural
    const string INHABITANTS = 'inhabitants';
    const string YEAR_2013 = '2013';
    const int YEAR_2013_ID = 316;
    const string YEAR_2014 = '2014';
    const int YEAR_2014_ID = 317;
    const string YEAR_2015 = '2015';
    const int YEAR_2015_ID = 318;
    const string YEAR_2016 = '2016';
    const int YEAR_2016_ID = 319;
    const string YEAR_2017 = '2017';
    const int YEAR_2017_ID = 320;
    const string YEAR_2018 = '2018';
    const int YEAR_2018_ID = 321;

    // words from import test file companies.json used for the start page in order of appearance
    const string COMPANY = 'company';
    const string COMPANY_COM = 'legal entity made up of an association of people for the purpose of carrying on a commercial or industrial enterprise';
    const int COMPANY_ID = 189;
    const string ABB = 'ABB';
    const int ABB_ID = 538;
    const string VESTAS = 'Vestas';
    const int VESTAS_ID = 539;

    const string TEXT = 'text';
    const string HTML = 'html';
    const string ALL = 'all';
    const string TIMEOUTS = 'timeouts';
    const string WARNINGS = 'warnings';

    // base words that are fixed part of the base setup
    const string CURRENCY = 'currency';
    const string CURRENCIES = 'currencies';
    // the differentiator word used to qualify a value by business segment e.g. in the XBRL import
    const string SECTOR = 'sector';
    // base income statement words defined in accounting.json and re-declared on import
    const string PROFIT = 'profit';
    const string GROSS = 'gross';
    const string COST = 'cost';
    const int COST_ID = 309;
    const string REVENUE = 'revenue';

    const string LAYOUT_COM = 'the settings to position the components on the screen';
    const string COUNT = 'count';
    const string IMPORT_TYPE = 'import type';
    const string PASSWORD = 'password';
    const string OPEN_API = 'OpenAPI';
    const string MATH_PLURAL = 'mathematics';
    const string CONST_COM = 'fixed and well-defined number';
    const int CONST_ID = 2;
    const string PI_SYMBOL_COM = 'Symbol for the ratio of the circumference of a circle to its diameter';
    const string PI_COM = 'ratio of the circumference of a circle to its diameter';
    const string HOUR = 'hour';
    const int HOUR_ID = 101;
    const string YEAR_2020_COM = 'the year 2020';
    const string YEAR_2021 = '2021';
    const int YEAR_2021_ID = 607;
    const string YEAR_2022 = '2022';
    const int YEAR_2022_ID = 381;
    const string YEAR_2023 = '2023';
    const int YEAR_2023_ID = 606;
    const string YEAR_2024 = '2024';
    const int YEAR_2024_ID = 276;
    const string YEAR_2025 = '2025';
    const int YEAR_2025_ID = 605;
    const string YEAR_2026 = '2026';
    const int YEAR_2026_ID = 604;
    const string YEAR_2027 = '2027';
    const int YEAR_2027_ID = 603;
    const string YEAR_2028 = '2028';
    const int YEAR_2028_ID = 602;
    const string YEAR_2029 = '2029';
    const int YEAR_2029_ID = 601;
    const string YEAR_2030 = '2030';
    const int YEAR_2030_ID = 600;
    const string LIGHT = 'light';
    const int LIGHT_ID = 84;
    const string SPEED = 'speed';
    const int SPEED_ID = 85;
    const string METRE = 'metre';
    const int METRE_ID = 25;
    const string JOULE = 'joule';
    const int JOULE_ID = 49;
    const string JOULE_COM = 'One joule is equal to the amount of work done when a force of one newton displaces a body through a distance of one metre in the direction of that force.';
    const string KG = 'kg';
    const int KG_ID = 28;
    const string KG_COM = 'The kilogram is the SI base unit of mass.';
    const string HYPERFINE = 'hyperfine';
    const int HYPERFINE_ID = 127;
    const string TRANSITION = 'transition';
    const int TRANSITION_ID = 128;
    const string FREQUENCY = 'frequency';
    const int FREQUENCY_ID = 129;
    const string HZ = 'Hz';
    const int HZ_ID = 40;
    const string HZ_COM = 'Is a symbol for hertz, which is the unit of frequency in the International System of Units (SI), often described as being equivalent to one event (or cycle) per second';
    const string DEFINITION = 'definition';
    const int DEFINITION_ID = 131;
    const string YEAR_1983 = '1983';
    const int YEAR_1983_ID = 134;
    const string YEAR_1967 = '1967';
    const int YEAR_1967_ID = 132;
    const int THIS_ID = 180;
    const int PRIOR_ID = 182;
    const string EUR = 'EUR';
    const int EUR_ID = 272;
    const string EURO = 'Euro';
    const int EURO_ID = 371;
    const string DOLLAR = '$';
    const int DOLLAR_ID = 385;
    const string EURO_SIGN = '€';
    const int EURO_SIGN_ID = 387;
    const int CURRENCY_ID = 334;
    const string US = 'US';


    const array WORDS_SCALING = array(self::MIO, self::MIO_SHORT);
    const array WORDS_SCALING_HIDDEN = array(self::ONE);
    const array WORDS_PERCENT = array(shared_words::PCT);

    const string TEST_ADD = 'System Test Word';
    // the database id of the 'System Test Word' is assigned dynamically on insert; this fixed id
    // replaces the volatile id in workflow snapshots so the test result does not change every run
    const int TEST_ADD_ID = 999;
    const string TEST_ADD_CODE_ID = 'System Test Word code id';
    // the plural of the filled 'System Test Word' so the fill test does not borrow the math word's
    // plural "mathematics" and the System Test Word change log reads as its own word
    const string TEST_ADD_PLURAL = 'System Test Words';
    const string TEST_ADD_COM = 'test description added to the word via import';
    // the new description posted by the confirm change workflow to change the test word description
    const string TEST_CHANGE_COM = 'a confirm change test description';
    // the second new description posted by the change_word_all_sandbox_fields workflow, so the
    // change log of the test word shows two confirmed description changes
    const string TEST_CHANGE_TWO_COM = 'a second confirm change test description';
    // the description overwrite of a third user, so the change_word_all_sandbox_fields workflow
    // pages rendered for usr2 also show the 'others' tab
    const string TEST_OTHER_COM = 'a description overwrite of another user';
    // words to test the no update import that only fills up empty fields
    const string TEST_NO_UPD = 'System Test Word No Update';
    const string TEST_NO_UPD_COM = 'the original description that a no update import must keep';
    const string TEST_NO_UPD_CHANGED = 'the changed description that a no update import must ignore';
    const string TEST_FILL_UP = 'System Test Word Fill Up';
    const string TEST_FILL_UP_COM = 'the description added to an empty field by a no update import';
    const string TEST_ADD_TO = 'System Test Word To';
    // the fixed snapshot id of the 'System Test Word To' (like TEST_ADD_ID for the 'System Test Word')
    const int TEST_ADD_TO_ID = 997;
    const string TEST_ADD_VIA_FUNC = 'System Test Word added via sql function';
    const string TEST_ADD_GROUP_PRIME = 'System Test Word for prime values';
    const string TEST_ADD_GROUP_PRIME_FUNC = 'System Test Word for prime group add via sql function';
    const string TEST_ADD_GROUP_PRIME_SQL = 'System Test Word for prime group add via sql insert';
    const string TEST_ADD_GROUP_MOST_FUNC = 'System Test Word for main group add via sql function';
    const string TEST_ADD_GROUP_MOST_SQL = 'System Test Word for main group add via sql insert';
    const string TEST_ADD_GROUP_BIG_FUNC = 'System Test Word for big group add via sql function';
    const string TEST_ADD_GROUP_BIG_SQL = 'System Test Word for big group add via sql insert';
    const string TEST_RENAMED = 'System Test Word Renamed';
    const string TEST_RENAMED_GROUP_PRIME_FUNC = 'System Test Word for prime group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_PRIME_SQL = 'System Test Word for prime group RENAMED via sql insert';
    const string TEST_RENAMED_GROUP_MOST_FUNC = 'System Test Word for main group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_MOST_SQL = 'System Test Word for main group RENAMED via sql insert';
    const string TEST_RENAMED_GROUP_BIG_FUNC = 'System Test Word for big group RENAMED via sql function';
    const string TEST_RENAMED_GROUP_BIG_SQL = 'System Test Word for big group RENAMED via sql insert';
    const string TEST_PARENT = 'System Test Word Parent';
    const string TEST_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const string TEST_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const string TEST_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const string TEST_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const string TEST_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const string TEST_SECTOR = 'System Test Word with differentiator e.g. sector';
    const string TEST_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const string TEST_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const string TEST_CASH = 'System Test Word multi levels e.g. Cash';
    const string TEST_2021 = 'System Test Time Word e.g. 2021';
    const string TEST_2022 = 'System Test Another Time Word e.g. 2022';
    const string TEST_CHF = 'System Test Measure Word e.g. CHF';
    const string TEST_SHARE = 'System Test Word Share';
    const string TEST_PRICE = 'System Test Word Share Price';
    const string TEST_EARNING = 'System Test Word Earnings';
    const string TEST_PE = 'System Test Word PE Ratio';
    const string TEST_FORMULA_PE = 'System Test Formula PE Ratio';
    const string TEST_FORMULA_SECTOR = 'System Test Formula sector';
    const string TEST_MEASURE_CHF = 'System Test Measure Word e.g. CHF';
    const string TEST_IN_K = 'System Test Scaling Word e.g. thousands';
    const string TEST_BIL = 'System Test Scaling Word e.g. billions';
    const string TEST_TOTAL = 'System Test Word Total';
    const string TEST_INCREASE = 'System Test Word Increase';
    const string TEST_PERCENT = 'System Test Word Percent';
    const string TEST_THIS = 'System Test Word This';
    const string TEST_PRIOR = 'System Test Word Prior';
    const string TEST_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const string TEST_LATEST = 'System Test Word Latest';
    const string TEST_SCALING_PCT = 'System Test Word Scaling Percent';
    const string TEST_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const string TEST_CALC = 'System Test Word Calc';
    const string TEST_LAYER = 'System Test Word Layer';
    const string TEST_ADD_API = 'System Test Word API';
    const string TEST_ADD_API_COM = 'System Test Word API Description';
    const string TEST_UPD_API = 'System Test Word API Renamed';
    const string TEST_UPD_API_COM = 'System Test Word API Description Renamed';
    const string TEST_ADD_VALUE = 'System Test Word for value curl testing';
    const string TEST_SPEED_PREFIX = 'System Test Word for speed testing ';


    // list of words used for system testing that should be removed after the system test has completed
    // and that are never expected to be used by a user
    const array TEST_WORDS = array(
        self::TEST_ADD,
        self::TEST_ADD_TO,
        self::TEST_ADD_VIA_FUNC,
        self::TEST_ADD_GROUP_PRIME,
        self::TEST_ADD_GROUP_PRIME_FUNC,
        self::TEST_ADD_GROUP_PRIME_SQL,
        self::TEST_ADD_GROUP_MOST_FUNC,
        self::TEST_ADD_GROUP_MOST_SQL,
        self::TEST_ADD_GROUP_BIG_FUNC,
        self::TEST_ADD_GROUP_BIG_SQL,
        self::TEST_RENAMED,
        self::TEST_PARENT,
        self::TEST_FIN_REPORT,
        self::TEST_CASH_FLOW,
        self::TEST_TAX_REPORT,
        self::TEST_ASSETS,
        self::TEST_ASSETS_CURRENT,
        self::TEST_SECTOR,
        self::TEST_ENERGY,
        self::TEST_WIND_ENERGY,
        self::TEST_CASH,
        self::TEST_2021,
        self::TEST_2022,
        self::TEST_CHF,
        self::TEST_SHARE,
        self::TEST_PRICE,
        self::TEST_EARNING,
        self::TEST_PE,
        self::TEST_FORMULA_PE,
        self::TEST_FORMULA_SECTOR,
        self::TEST_MEASURE_CHF,
        self::TEST_IN_K,
        self::TEST_BIL,
        self::TEST_TOTAL,
        self::TEST_INCREASE,
        self::TEST_PERCENT,
        self::TEST_THIS,
        self::TEST_PRIOR,
        self::TEST_TIME_JUMP,
        self::TEST_LATEST,
        self::TEST_SCALING_PCT,
        self::TEST_SCALING_MEASURE,
        self::TEST_CALC,
        self::TEST_LAYER,
        self::TEST_ADD_API,
        self::TEST_UPD_API
    );

    // list of words used for system testing that should be created before the system test starts
    const array TEST_WORDS_CREATE = array(
        self::TEST_PARENT,
        self::TEST_FIN_REPORT,
        self::TEST_CASH_FLOW,
        self::TEST_TAX_REPORT,
        self::TEST_ASSETS,
        self::TEST_ASSETS_CURRENT,
        self::TEST_SECTOR,
        self::TEST_ENERGY,
        self::TEST_WIND_ENERGY,
        self::TEST_CASH,
        self::TEST_2021,
        self::TEST_2022,
        self::TEST_CHF,
        self::TEST_SHARE,
        self::TEST_PRICE,
        self::TEST_EARNING,
        self::TEST_PE,
        self::TEST_IN_K,
        self::TEST_BIL,
        self::TEST_TOTAL,
        self::TEST_INCREASE,
        self::TEST_THIS,
        self::TEST_PRIOR,
        self::TEST_TIME_JUMP,
        self::TEST_LATEST,
        self::TEST_SCALING_PCT,
        self::TEST_SCALING_MEASURE,
        self::TEST_CALC,
        self::TEST_LAYER,
        self::TEST_ADD_API,
        self::TEST_UPD_API
    );
    const array TEST_WORDS_MEASURE = array(self::TEST_CHF);
    const array TEST_WORDS_SCALING = array(self::TEST_IN_K, self::TEST_BIL);

    // the time words must be in correct order because the following is set during creation
    const array TEST_WORDS_TIME_YEAR = array(
        self::YEAR_2015,
        self::YEAR_2016,
        self::YEAR_2017,
        self::YEAR_2018,
        self::TEST_2021,
        self::TEST_2022
    );

    // list of words where the id is used for system testing
    // TODO Prio 2 combine with similar list in words
    const array TEST_WORD_IDS = array(
        self::ABB_ID => self::ABB,
        self::BE_ID => self::BE,
        self::BILLION_ID => self::BILLION,
        self::CANTON_ID => self::CANTON,
        self::CASH_ID => self::CASH,
        self::FLOW_ID => self::FLOW,
        self::SOLUTION_ID => self::SOLUTION,
        self::STATEMENT_ID => self::STATEMENT,
        shared_words::CH_ID => shared_words::CH,
        shared_words::CHF_ID => shared_words::CHF,
        self::CIRCUMFERENCE_ID => self::CIRCUMFERENCE,
        self::CITY_ID => self::CITY,
        self::CLIMATE_ID => self::CLIMATE,
        self::COMPANY_ID => self::COMPANY,
        self::CONST_ID => self::CONST_NAME,
        self::DIAMETER_ID => self::DIAMETER,
        self::EDUCATION_ID => self::EDUCATION,
        self::GE_ID => self::GE,
        self::GLOBAL_ID => self::GLOBAL,
        shared_words::HAPPY_ID => shared_words::HAPPY,
        self::HEALTH_ID => self::HEALTH,
        self::HTP_ID => self::HTP,
        self::INCOME_ID => self::INCOME,
        self::INHABITANT_ID => self::INHABITANTS,
        shared_words::LAUNCH_ID => shared_words::LAUNCH,
        shared_words::MASTER_POD_NAME_ID => shared_words::MASTER_POD_NAME,
        self::MATH_ID => self::MATH,
        self::MINUTE_ID => self::MINUTE,
        self::MIO_ID => self::MIO,
        self::ONE_ID => self::ONE,
        self::PARTS_ID => self::PARTS,
        shared_words::PCT_ID => shared_words::PCT,
        self::PI_ID => self::PI,
        shared_words::POD_ID => shared_words::POD,
        shared_words::POINT_ID => shared_words::POINT,
        shared_words::POINTS_ID => shared_words::POINTS,
        self::POPULISM_ID => self::POPULISM,
        self::POVERTY_ID => self::POVERTY,
        self::PRIOR_ID => self::PRIOR_NAME,
        self::POTENTIAL_ID => self::POTENTIAL,
        self::PROBLEM_ID => self::PROBLEM,
        self::SALES_ID => self::SALES,
        shared_words::SECOND_ID => shared_words::SECOND,
        self::TAX_ID => self::TAX,
        self::THIS_ID => self::THIS_NAME,
        shared_words::TIME_ID => shared_words::TIME,
        shared_words::TOTAL_ID => shared_words::TOTAL_PRE,
        self::TRILLION_ID => self::TRILLION,
        shared_words::URL_ID => shared_words::URL,
        self::USD_ID => self::USD,
        self::VESTAS_ID => self::VESTAS,
        self::WARMER_ID => self::WARMER,
        self::YEAR_2013_ID => self::YEAR_2013,
        self::YEAR_2014_ID => self::YEAR_2014,
        self::YEAR_2015_ID => self::YEAR_2015,
        self::YEAR_2016_ID => self::YEAR_2016,
        self::YEAR_2017_ID => self::YEAR_2017,
        self::YEAR_2018_ID => self::YEAR_2018,
        self::YEAR_2019_ID => self::YEAR_2019,
        self::YEAR_2020_ID => self::YEAR_2020,
        self::LIGHT_ID => self::LIGHT,
        self::SPEED_ID => self::SPEED,
        self::METRE_ID => self::METRE,
        self::JOULE_ID => self::JOULE,
        self::KG_ID => self::KG,
        self::HYPERFINE_ID => self::HYPERFINE,
        self::TRANSITION_ID => self::TRANSITION,
        self::FREQUENCY_ID => self::FREQUENCY,
        self::HZ_ID => self::HZ,
        self::DEFINITION_ID => self::DEFINITION,
        self::YEAR_1983_ID => self::YEAR_1983,
        self::YEAR_1967_ID => self::YEAR_1967,
        shared_words::YEAR_CAP_ID => shared_words::YEAR_CAP,
        self::ZH_ID => self::ZH,
    );

}
