<?php

/*

    test/php/const/formula_names.php - predefined formulas used only for system testing
    --------------------------------

    the formulas used in the backend and frontend are in main/php/shared/const/formulas.php
    this separate class holds the test-only formulas, ids, expressions and lists

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

use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\word_names;

class formula_names
{

    // formulas for stand-alone unit tests that are added with the system initial data load
    // *_COM is the tooltip for the formula; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    // *_EXP is the formula expression in the human-readable format
    // *_DB is the formula expression in the database reference format
    const int NOT_SET_ID = 0;
    const string SCALE_TO_SEC = 'scale minute to sec';
    const string SCALE_TO_SEC_EXP = '"second (time)" = "minute" * 60';
    const string SCALE_TO_SEC_EXP_REF = '{w24}={w104}*60';
    const string SCALE_TO_SEC_LATEX = '\text{s} = 60 \cdot \text{min}';
    const string SCALE_TO_SEC_EXP_REF_SHORT_SYMBOL = '{w24}={w}*60';
    const string SCALE_TO_SEC_EXP_REF_ID_NOT_A_NUMBER = '{w24}={wO}*60';
    const string SCALE_TO_SEC_EXP_REF_SYMBOL_NOT_VALID = '{w24}={d1}*60';
    const string SCALE_TO_SEC_EXP_PHRASE_ID_NOT_VALID = '{wO}={w104}*60';
    const string SCALE_TO_SEC_COM = 'to convert times in minutes to seconds and the other way round';
    const string SCALE_TO_SEC_CODE_ID = 'scale_minute_to_sec';
    const int SCALE_TO_SEC_ID = 1;
    const string SCALE_HOUR = 'scale hour to sec';
    const int SCALE_HOUR_ID = 2;
    const string SCALE_HOUR_EXP = '{w24}={w105}*3600';
    const string DIAMETER = '= "circumference" / "Pi"';
    const string DIAMETER_DB = '={w' . word_names::CIRCUMFERENCE_ID . '}/{w' . word_names::PI_ID . '}';
    const string THIS_NAME = 'this';
    const int THIS_ID = 18;
    const string THIS_EXP = '="Now"';
    const string THIS_COM = 'hardcoded formula to select now, today, this year, ...';
    const string PRIOR = 'prior';
    const int PRIOR_ID = 20;
    const string PRIOR_EXP = '=value["time jump"->,"Now"->"follower"]';
    const string PERCENT = 'percent';
    const string INCREASE = 'increase';
    const int INCREASE_ID = 21;
    const string INCREASE_EXP = '"' . words::PERCENT . '" = ( "' . word_names::THIS_NAME . '" - "' . word_names::PRIOR_NAME . '" ) / "' . word_names::PRIOR_NAME . '"';
    const string INCREASE_ALTERNATIVE_EXP = '"' . words::PERCENT . '" = 1 - ( "' . word_names::THIS_NAME . '" / "' . word_names::PRIOR_NAME . '" )';
    const string INCREASE_DB = '{w' . words::PCT_ID . '}=({f' . self::THIS_ID . '}-{f' . self::PRIOR_ID . '})/{f' . self::PRIOR_ID . '}';
    const string LITRE_TO_M3 = 'scale litre to m3';
    const string BIGGEST_CITY = 'population in the city of Zurich in percent of '  . words::CH;
    const string SCALE_MIO = 'scale millions to one';
    const int SCALE_MIO_ID = 3;
    const string SCALE_MIO_EXP = '"one" = "millions" * 1000000';
    const string SCALE_MIO_DB = '{w' . word_names::ONE_ID . '} = {w' . word_names::MIO_ID . '} * 1000000';
    const string PARTS_IN_PERCENT = 'parts in percent';
    const string PARTS_IN_PERCENT_EXP = '"' . words::PERCENT . '" = "parts" "' . verbs::OF_NAME . '" / "total"'; // TODO check if separate verb "of each" is needed
    const string PARTS_IN_PERCENT_DB = '{w' . words::PCT_ID . '}={w' . word_names::PARTS_ID . '}{v' . verbs::OF_ID . '}/{w' . words::TOTAL_ID . '}';
    const string CITY_POPULATION = 'city population';
    const int CITY_POPULATION_ID = 26;
    const string CITY_POPULATION_EXP = '"total" = &sum; ( "inhabitants" "of all" "city" )';

    // persevered formula names for unit and integration tests
    const string SYSTEM_TEST_ADD = 'System Test Formula'; // to test adding a new formula to the database and using the increase formula
    const string SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Formula via SQL function';
    const string SYSTEM_TEST_ADD_COM = 'System Test Formula Description';
    const string SYSTEM_TEST_RENAMED = 'System Test Formula Renamed';
    const string SYSTEM_TEST_EXCLUDED = 'System Test Formula Excluded';
    const string SYSTEM_TEST_THIS = 'System Test Formula This'; // to test if another formula of the functional type "this" can be created
    const string SYSTEM_TEST_THIS_EXP = '= "System Test Formula This"';
    const string SYSTEM_TEST_RATIO = 'System Test Formula PE Ratio'; // to test a simple ration calculation like how many times Switzerland is bigger than the canton zurich or the price to earning ration for equity
    const string SYSTEM_TEST_RATIO_EXP = '"System Test Word PE Ratio" = "System Test Word Share Price" / "System Test Word Earnings"';
    const string SYSTEM_TEST_SECTOR = 'System Test Formula sector'; // to test the selection by a phrases and parents e.g. split all country totals by canton
    const string SYSTEM_TEST_SECTOR_EXP = '= "country" "differentiator" "canton" / "System Test Word Total"';
    const string SYSTEM_TEST_SCALE_K = 'System Test Formula scale thousand to one';
    const string SYSTEM_TEST_SCALE_K_EXP = '"one" = "System Test Scaling Word e.g. thousands" * 1000';
    const string SYSTEM_TEST_SCALE_TO_K = 'System Test Formula scale one to thousand';
    const string SYSTEM_TEST_SCALE_TO_K_EXP = '"System Test Scaling Word e.g. thousands" = "one" / 1000';
    const string SYSTEM_TEST_SCALE_MIO = 'System Test Formula scale millions to one';
    const string SYSTEM_TEST_SCALE_MIO_EXP = '"one" = "million" * 1000000';
    const string SYSTEM_TEST_SCALE_BIL = 'System Test Formula scale billions to one';
    const string SYSTEM_TEST_SCALE_BIL_EXP = '"one" = "System Test Scaling Word e.g. billions" * 1000000000';

    const string TEST_SPEED_PREFIX = 'System Test Formula for speed testing ';

    // formula names used for integration tests
    // that are removed after each test
    // and therefore cannot be used by users
    const array TEST_FORMULAS = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_RENAMED,
        self::SYSTEM_TEST_EXCLUDED,
        self::SYSTEM_TEST_THIS,
        self::SYSTEM_TEST_RATIO,
        self::SYSTEM_TEST_SECTOR,
        self::SYSTEM_TEST_SCALE_K,
        self::SYSTEM_TEST_SCALE_TO_K,
        self::SYSTEM_TEST_SCALE_MIO,
        self::SYSTEM_TEST_SCALE_BIL
    );

}
