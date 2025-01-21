<?php

/*

    shared/formulas.php - predefined formulas used in the backend and frontend as code id
    ------------------

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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace shared;

use api\verb\verb as verb_api;
use api\word\word as word_api;

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
    const SCALE_TO_SEC = 'scale minute to sec';
    const SCALE_TO_SEC_EXP = '"second" = "minute" * 60';
    const SCALE_TO_SEC_COM = 'to convert times in minutes to seconds and the other way round';
    const SCALE_HOUR = 'scale hour to sec';
    const SCALE_HOUR_ID = 2;
    const DIAMETER = '= "circumference" / "Pi"';
    const DIAMETER_DB = '={w' . words::CIRCUMFERENCE_ID . '}/{w' . words::PI_ID . '}';
    const THIS_NAME = 'this';
    const PRIOR = 'prior';
    const PERCENT = 'percent';
    const INCREASE = 'increase';
    const INCREASE_ID = 21;
    const INCREASE_EXP = '"percent" = ( "this" - "prior" ) / "prior"';
    const INCREASE_ALTERNATIVE_EXP = '"percent" = 1 - ( "this" / "prior" )';
    const INCREASE_DB = '{w' . words::TI_PCT . '}=({w' . words::TI_THIS . '}-{w' . words::TI_PRIOR . '})/{w' . words::TI_PRIOR . '}';
    const LITRE_TO_M3 = 'scale litre to m3';
    const BIGGEST_CITY = 'population in the city of Zurich in percent of Switzerland';
    const SCALE_MIO = 'scale millions to one';
    const SCALE_MIO_EXP = '"one" = "millions" * 1000000';
    const SCALE_MIO_DB = '{w' . words::ONE_ID . '} = {w' . words::TI_MIO . '} * 1000000';
    const PARTS_IN_PERCENT = 'parts in percent';
    const PARTS_IN_PERCENT_EXP = '"percent" = "parts" "of" / "total"'; // TODO check if separate verb "of each" is needed
    const PARTS_IN_PERCENT_DB = '{w' . words::TI_PCT . '}={w' . words::TI_PARTS . '}{v' . verb_api::TI_OF . '}/{w' . words::TI_TOTAL . '}';

    // persevered formula names for unit and integration tests
    const SYSTEM_TEXT_ADD = 'System Test Formula'; // to test adding a new formula to the database and using the increase formula
    const SYSTEM_TEXT_ADD_VIA_FUNC = 'System Test Formula via SQL function';
    const SYSTEM_TEXT_ADD_VIA_SQL = 'System Test Formula via SQL insert';
    const SYSTEM_TEXT_RENAMED = 'System Test Formula Renamed';
    const SYSTEM_TEXT_EXCLUDED = 'System Test Formula Excluded';
    const SYSTEM_TEXT_THIS = 'System Test Formula This'; // to test if another formula of the functional type "this" can be created
    const SYSTEM_TEXT_THIS_EXP = '= "System Test Formula This"';
    const SYSTEM_TEXT_RATIO = 'System Test Formula PE Ratio'; // to test a simple ration calculation like how many times Switzerland is bigger than the canton zurich or the price to earning ration for equity
    const SYSTEM_TEXT_RATIO_EXP = '"System Test Word PE Ratio" = "System Test Word Share Price" / "System Test Word Earnings"';
    const SYSTEM_TEXT_SECTOR = 'System Test Formula Sector'; // to test the selection by a phrases and parents e.g. split all country totals by canton
    const SYSTEM_TEXT_SECTOR_EXP = '= "Country" "differentiator" "Canton" / "System Test Word Total"';
    const SYSTEM_TEXT_SCALE_K = 'System Test Formula scale thousand to one';
    const SYSTEM_TEXT_SCALE_K_EXP = '"one" = "System Test Scaling Word e.g. thousands" * 1000';
    const SYSTEM_TEXT_SCALE_TO_K = 'System Test Formula scale one to thousand';
    const SYSTEM_TEXT_SCALE_TO_K_EXP = '"System Test Scaling Word e.g. thousands" = "one" / 1000';
    const SYSTEM_TEXT_SCALE_MIO = 'System Test Formula scale millions to one';
    const SYSTEM_TEXT_SCALE_MIO_EXP = '"one" = "million" * 1000000';
    const SYSTEM_TEXT_SCALE_BIL = 'System Test Formula scale billions to one';
    const SYSTEM_TEXT_SCALE_BIL_EXP = '"one" = "System Test Scaling Word e.g. billions" * 1000000000';

    // formula names that are reserved either
    // for creating the test formulas, that are removed after the test
    // so these formula names cannot be used for user formulas
    // or for fixed of the default data set that are used for unit tests
    const RESERVED_NAMES = array(
        self::SCALE_TO_SEC,
        self::SYSTEM_TEXT_ADD,
        self::SYSTEM_TEXT_ADD_VIA_FUNC,
        self::SYSTEM_TEXT_ADD_VIA_SQL,
        self::SYSTEM_TEXT_RENAMED,
        self::SYSTEM_TEXT_EXCLUDED,
        self::SYSTEM_TEXT_THIS,
        self::SYSTEM_TEXT_RATIO,
        self::SYSTEM_TEXT_SECTOR,
        self::SYSTEM_TEXT_SCALE_K,
        self::SYSTEM_TEXT_SCALE_TO_K,
        self::SYSTEM_TEXT_SCALE_MIO,
        self::SYSTEM_TEXT_SCALE_BIL
    );

    // array of formula names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::SCALE_TO_SEC
    );

    // formula names used for integration tests
    // that are removed after each test
    // and therefore cannot be used by users
    const TEST_FORMULAS = array(
        self::SYSTEM_TEXT_ADD,
        self::SYSTEM_TEXT_ADD_VIA_FUNC,
        self::SYSTEM_TEXT_ADD_VIA_SQL,
        self::SYSTEM_TEXT_RENAMED,
        self::SYSTEM_TEXT_EXCLUDED,
        self::SYSTEM_TEXT_THIS,
        self::SYSTEM_TEXT_RATIO,
        self::SYSTEM_TEXT_SECTOR,
        self::SYSTEM_TEXT_SCALE_K,
        self::SYSTEM_TEXT_SCALE_TO_K,
        self::SYSTEM_TEXT_SCALE_MIO,
        self::SYSTEM_TEXT_SCALE_BIL
    );
}
