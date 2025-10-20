<?php

/*

    shared/const/formulas.php - predefined formulas used in the backend and frontend as code id
    -------------------------

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

use Zukunft\ZukunftCom\main\php\shared\types\verbs;

class formulas
{

    // this list includes all preserved formula names
    // *_COM is the tooltip for the formula; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing
    // *_EXP is the formula expression in the human-readable format
    // *_DB is the formula expression in the database reference format

    // keyword to select the system configuration
    /*
     * const for system testing
     */

    // formulas for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the formula used for testing
    // TI_* is the database id based on the initial load
    // TF_* is the formula expression in the human-readable format
    // TR_* is the formula expression in the database reference format
    const int NOT_SET_ID = 0;
    const string SCALE_TO_SEC = 'scale minute to sec';
    const string SCALE_TO_SEC_EXP = '"second" = "minute" * 60';
    const string SCALE_TO_SEC_COM = 'to convert times in minutes to seconds and the other way round';
    const string SCALE_TO_SEC_CODE_ID = 'scale_minute_to_sec';
    const int SCALE_TO_SEC_ID = 1;
    const string SCALE_HOUR = 'scale hour to sec';
    const int SCALE_HOUR_ID = 2;
    const string DIAMETER = '= "circumference" / "Pi"';
    const string DIAMETER_DB = '={w' . words::CIRCUMFERENCE_ID . '}/{w' . words::PI_ID . '}';
    const string THIS_NAME = 'this';
    const int THIS_ID = 18;
    const string THIS_EXP = '="Now"';
    const string PRIOR = 'prior';
    const int PRIOR_ID = 20;
    const string PRIOR_EXP = '=value["time jump"->,"Now"->"follower"]';
    const string PERCENT = 'percent';
    const string INCREASE = 'increase';
    const int INCREASE_ID = 21;
    const string INCREASE_EXP = '"' . words::PERCENT . '" = ( "' . words::THIS_NAME . '" - "' . words::PRIOR_NAME . '" ) / "' . words::PRIOR_NAME . '"';
    const string INCREASE_ALTERNATIVE_EXP = '"' . words::PERCENT . '" = 1 - ( "' . words::THIS_NAME . '" / "' . words::PRIOR_NAME . '" )';
    const string INCREASE_DB = '{w' . words::PCT_ID . '}=({w' . words::THIS_ID . '}-{w' . words::PRIOR_ID . '})/{w' . words::PRIOR_ID . '}';
    const string LITRE_TO_M3 = 'scale litre to m3';
    const string BIGGEST_CITY = 'population in the city of Zurich in percent of '  . words::CH . '';
    const string SCALE_MIO = 'scale millions to one';
    const string SCALE_MIO_EXP = '"one" = "millions" * 1000000';
    const string SCALE_MIO_DB = '{w' . words::ONE_ID . '} = {w' . words::MIO_ID . '} * 1000000';
    const string PARTS_IN_PERCENT = 'parts in percent';
    const string PARTS_IN_PERCENT_EXP = '"' . words::PERCENT . '" = "parts" "' . verbs::OF_NAME . '" / "total"'; // TODO check if separate verb "of each" is needed
    const string PARTS_IN_PERCENT_DB = '{w' . words::PCT_ID . '}={w' . words::PARTS_ID . '}{v' . verbs::OF_ID . '}/{w' . words::TOTAL_ID . '}';
    const string CITY_POPULATION = 'city population';
    const int CITY_POPULATION_ID = 26;
    const string CITY_POPULATION_EXP = '"total" = &sum; ( "inhabitants" "of all" "City" )';

    // persevered formula names for unit and integration tests
    const string SYSTEM_TEST_ADD = 'System Test Formula'; // to test adding a new formula to the database and using the increase formula
    const string SYSTEM_TEST_ADD_VIA_FUNC = 'System Test Formula via SQL function';
    const string SYSTEM_TEST_ADD_VIA_SQL = 'System Test Formula via SQL insert';
    const string SYSTEM_TEST_ADD_COM = 'System Test Formula Description';
    const string SYSTEM_TEST_RENAMED = 'System Test Formula Renamed';
    const string SYSTEM_TEST_EXCLUDED = 'System Test Formula Excluded';
    const string SYSTEM_TEST_THIS = 'System Test Formula This'; // to test if another formula of the functional type "this" can be created
    const string SYSTEM_TEST_THIS_EXP = '= "System Test Formula This"';
    const string SYSTEM_TEST_RATIO = 'System Test Formula PE Ratio'; // to test a simple ration calculation like how many times Switzerland is bigger than the canton zurich or the price to earning ration for equity
    const string SYSTEM_TEST_RATIO_EXP = '"System Test Word PE Ratio" = "System Test Word Share Price" / "System Test Word Earnings"';
    const string SYSTEM_TEST_SECTOR = 'System Test Formula sector'; // to test the selection by a phrases and parents e.g. split all country totals by canton
    const string SYSTEM_TEST_SECTOR_EXP = '= "Country" "differentiator" "Canton" / "System Test Word Total"';
    const string SYSTEM_TEST_SCALE_K = 'System Test Formula scale thousand to one';
    const string SYSTEM_TEST_SCALE_K_EXP = '"one" = "System Test Scaling Word e.g. thousands" * 1000';
    const string SYSTEM_TEST_SCALE_TO_K = 'System Test Formula scale one to thousand';
    const string SYSTEM_TEST_SCALE_TO_K_EXP = '"System Test Scaling Word e.g. thousands" = "one" / 1000';
    const string SYSTEM_TEST_SCALE_MIO = 'System Test Formula scale millions to one';
    const string SYSTEM_TEST_SCALE_MIO_EXP = '"one" = "million" * 1000000';
    const string SYSTEM_TEST_SCALE_BIL = 'System Test Formula scale billions to one';
    const string SYSTEM_TEST_SCALE_BIL_EXP = '"one" = "System Test Scaling Word e.g. billions" * 1000000000';

    // formula names that are reserved either
    // for creating the test formulas, that are removed after the test
    // so these formula names cannot be used for user formulas
    // or for fixed of the default data set that are used for unit tests
    const array RESERVED_NAMES = array(
        self::SCALE_TO_SEC,
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_ADD_VIA_SQL,
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

    // array of formula names that used for db read testing and that should not be renamed
    const array FIXED_NAMES = array(
        self::SCALE_TO_SEC
    );

    // formula names used for integration tests
    // that are removed after each test
    // and therefore cannot be used by users
    const array TEST_FORMULAS = array(
        self::SYSTEM_TEST_ADD,
        self::SYSTEM_TEST_ADD_VIA_FUNC,
        self::SYSTEM_TEST_ADD_VIA_SQL,
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
