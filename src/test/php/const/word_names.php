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
    const string E = "Euler's number";
    const int E_ID = 18;
    const string CIRCUMFERENCE = 'circumference';
    const int CIRCUMFERENCE_ID = 15;
    const string DIAMETER = 'diameter';
    const int DIAMETER_ID = 16;
    const string FLOW = 'flow';
    const int FLOW_ID = 100;
    const string MINUTE = 'minute';
    const int MINUTE_ID = 103;
    const string YEAR_2019 = '2019';
    const int YEAR_2019_ID = 139;
    const string YEAR_2020 = '2020';
    const int YEAR_2020_ID = 140;

    // words from import file scaling.json in order of appearance
    const string MIO = 'million';
    const int MIO_ID = 159;
    const string MIO_SHORT = 'mio';
    const string BILLION = 'billion';
    const int BILLION_ID = 160;

    // words from import file time_definition.json in order of appearance
    const string THIS_NAME = 'this'; // the test name for the predefined word 'this'
    const string PRIOR_NAME = 'prior';

    // words from import file base_phrases.json used for the offline phrase selection
    const string FACT = 'fact';
    const int FACT_ID = 192;
    const string GOVERNMENT = 'government';
    const int GOVERNMENT_ID = 194;
    const string GROUP = 'group';
    const int GROUP_ID = 196;
    const string HAND = 'hand';
    const int HAND_ID = 197;
    const string LIFE = 'life';
    const int LIFE_ID = 12;
    const string MAN = 'man';
    const int MAN_ID = 200;
    const string PART = 'part';
    const int PART_ID = 202;
    const string PERSON = 'person';
    const int PERSON_ID = 203;
    const string PLACE = 'place';
    const int PLACE_ID = 204;
    const string WAY = 'way';
    const int WAY_ID = 210;
    const string WOMAN = 'woman';
    const int WOMAN_ID = 210;
    const string WORK = 'work';
    const int WORK_ID = 211;
    const string WORLD = 'world';
    const int WORLD_ID = 212;

    // words from import file solution_prio.json used for the start page in order of appearance
    const string PROBLEM = 'problem';
    const int PROBLEM_ID = 206;
    const string GLOBAL = 'global';
    const int GLOBAL_ID = 195;
    const string POTENTIAL = 'potential';
    const int POTENTIAL_ID = 217;
    const string CLIMATE = 'climate';
    const int CLIMATE_ID = 220;
    const string WARMER = 'warmer';
    const int WARMER_ID = 223;
    const string POPULISM = 'populism';
    const int POPULISM_ID = 227;
    const string HEALTH = 'health';
    const int HEALTH_ID = 243;
    const string POVERTY = 'poverty';
    const int POVERTY_ID = 245;
    const string EDUCATION = 'education';
    const int EDUCATION_ID = 247;
    const string HTP = 'htp';
    const int HTP_ID = 255;
    const string TRILLION = 'trillion';
    const int TRILLION_ID = 256;
    const string SWISS_FRANC = 'Swiss franc';
    const int SWISS_FRANC_ID = 321;
    const string USD = 'USD';
    const int USD_ID = 259;

    // words from import file company.json used for the start page in order of appearance
    const string SALES = 'sales';
    const int SALES_ID = 281;
    const string CASH = 'cash';
    const int CASH_ID = 282;
    const string STATEMENT = 'statement';
    const int STATEMENT_ID = 283;
    const string PARTS = 'parts';
    const int PARTS_ID = 286;
    const string INCOME = 'income';
    const int INCOME_ID = 288;
    const string TAX = 'tax';
    const int TAX_ID = 289;

    // words from import file country.json used for the start page in order of appearance
    const string GERMANY = 'Germany';
    const string CANTON = 'canton';
    const int CANTON_ID = 187;
    const string CITY = 'city';
    const int CITY_ID = 189;
    const string ZH = 'Zurich';
    const int ZH_ID = 214;
    const string BE = 'Bern';
    const int BE_ID = 186;
    const string GE = 'Geneva';
    const int GE_ID = 193;
    const int INHABITANT_ID = 198;
    // TODO add test to search for words in all language forms e.g. plural
    const string INHABITANTS = 'inhabitants';
    const string YEAR_2013 = '2013';
    const int YEAR_2013_ID = 294;
    const string YEAR_2014 = '2014';
    const int YEAR_2014_ID = 295;
    const string YEAR_2015 = '2015';
    const int YEAR_2015_ID = 296;
    const string YEAR_2016 = '2016';
    const int YEAR_2016_ID = 297;
    const string YEAR_2017 = '2017';
    const int YEAR_2017_ID = 298;
    const string YEAR_2018 = '2018';
    const int YEAR_2018_ID = 299;

    // words from import test file companies.json used for the start page in order of appearance
    const string COMPANY = 'company';
    const int COMPANY_ID = 190;
    const string ABB = 'ABB';
    const int ABB_ID = 279;
    const string VESTAS = 'Vestas';
    const int VESTAS_ID = 280;

    const string TEXT = 'text';
    const string HTML = 'html';
    const string ALL = 'all';
    const string TIMEOUTS = 'timeouts';
    const string WARNINGS = 'warnings';

    // base words that are fixed part of the base setup
    const string CURRENCY = 'currency';
    // the differentiator word used to qualify a value by business segment e.g. in the XBRL import
    const string SECTOR = 'sector';
    // base income statement words defined in accounting.json and re-declared on import
    const string PROFIT = 'profit';
    const string GROSS = 'gross';
    const string COST = 'cost';
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
    const int HOUR_ID = 105;
    const string YEAR_2020_COM = 'the year 2020';
    const string YEAR_2021 = '2021';
    const int YEAR_2021_ID = 926;
    const string YEAR_2022 = '2022';
    const int YEAR_2022_ID = 925;
    const string YEAR_2023 = '2023';
    const int YEAR_2023_ID = 924;
    const string YEAR_2024 = '2024';
    const int YEAR_2024_ID = 264;
    const string YEAR_2025 = '2025';
    const int YEAR_2025_ID = 1091;
    const string YEAR_2026 = '2026';
    const int YEAR_2026_ID = 1090;
    const string YEAR_2027 = '2027';
    const int YEAR_2027_ID = 1089;
    const string YEAR_2028 = '2028';
    const int YEAR_2028_ID = 1088;
    const string YEAR_2029 = '2029';
    const int YEAR_2029_ID = 1087;
    const string YEAR_2030 = '2030';
    const int YEAR_2030_ID = 1086;
    const string LIGHT = 'light';
    const int LIGHT_ID = 86;
    const string SPEED = 'speed';
    const int SPEED_ID = 87;
    const string METRE = 'metre';
    const int METRE_ID = 27;
    const string HYPERFINE = 'hyperfine';
    const int HYPERFINE_ID = 130;
    const string TRANSITION = 'transition';
    const int TRANSITION_ID = 131;
    const string FREQUENCY = 'frequency';
    const int FREQUENCY_ID = 132;
    const string CS_133 = 'Caesium-133';
    const int CS_133_ID = 134;
    const string HZ = 'Hz';
    const int HZ_ID = 42;
    const string HZ_COM = 'Is a symbol for hertz, which is the unit of frequency in the International System of Units (SI), often described as being equivalent to one event (or cycle) per second';
    const string DEFINITION = 'definition';
    const int DEFINITION_ID = 135;
    const string YEAR_1983 = '1983';
    const int YEAR_1983_ID = 138;
    const string YEAR_1967 = '1967';
    const int YEAR_1967_ID = 136;
    const int THIS_ID = 181;
    const int PRIOR_ID = 183;
    const string SWISS_FRANC_COM = 'The currency of Switzerland and Liechtenstein.';
    const string EUR = 'EUR';
    const int EUR_ID = 2427;
    const string EURO = 'Euro';
    const int EURO_ID = 267;
    const string US_DOLLAR = 'US dollar';
    const int US_DOLLAR_ID = 268;
    const string U_S_DOLLAR = 'U.S. dollar';
    const int U_S_DOLLAR_ID = 2427;
    const string DOLLAR = '$';
    const int DOLLAR_ID = 274;
    const int CURRENCY_ID = 2397;
    const string US = 'US';
    const string GAAP = 'GAAP';
    const string TEST_ADD = 'System Test Word';
    const string TEST_ADD_CODE_ID = 'System Test Word code id';
    const string TEST_ADD_COM = 'test description added to the word via import';
    const string TEST_ADD_TO = 'System Test Word To';
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
    const string TEST_IN_K = 'System Test Scaling Word e.g. thousands';
    const string TEST_BIL = 'System Test Scaling Word e.g. billions';
    const string TEST_TOTAL = 'System Test Word Total';
    const string TEST_INCREASE = 'System Test Word Increase';
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
    const array TEST_WORDS_SCALING_HIDDEN = array(self::ONE);
    const array TEST_WORDS_SCALING = array(self::TEST_IN_K, self::MIO, self::MIO_SHORT, self::TEST_BIL);
    const array TEST_WORDS_PERCENT = array(shared_words::PCT);

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
    const array TEST_WORD_IDS = array(
        self::ABB_ID => self::ABB,
        self::BE_ID => self::BE,
        self::BILLION_ID => self::BILLION,
        self::CANTON_ID => self::CANTON,
        self::CASH_ID => self::CASH,
        self::FLOW_ID => self::FLOW,
        self::STATEMENT_ID => self::STATEMENT,
        shared_words::CH_ID => shared_words::CH,
        shared_words::CHF_ID => shared_words::CHF,
        self::SWISS_FRANC_ID => self::SWISS_FRANC,
        self::CIRCUMFERENCE_ID => self::CIRCUMFERENCE,
        self::CITY_ID => self::CITY,
        self::CLIMATE_ID => self::CLIMATE,
        self::COMPANY_ID => self::COMPANY,
        self::CONST_ID => self::CONST_NAME,
        self::DIAMETER_ID => self::DIAMETER,
        self::E_ID => self::E,
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
        self::HYPERFINE_ID => self::HYPERFINE,
        self::TRANSITION_ID => self::TRANSITION,
        self::FREQUENCY_ID => self::FREQUENCY,
        self::CS_133_ID => self::CS_133,
        self::HZ_ID => self::HZ,
        self::DEFINITION_ID => self::DEFINITION,
        self::YEAR_1983_ID => self::YEAR_1983,
        self::YEAR_1967_ID => self::YEAR_1967,
        shared_words::YEAR_CAP_ID => shared_words::YEAR_CAP,
        self::ZH_ID => self::ZH,
    );

}
