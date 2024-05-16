<?php

/*

    api/formula/formula.php - the minimal formula object for the frontend API
    -----------------------


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

namespace api\formula;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_PHRASE_PATH . 'term.php';
include_once API_WORD_PATH . 'word.php';
include_once API_VERB_PATH . 'verb.php';

use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\word\word as word_api;
use api\verb\verb as verb_api;

class formula extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // formulas for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the formula used for testing
    // TI_* is the database id based on the initial load
    // TF_* is the formula expression in the human-readable format
    // TR_* is the formula expression in the database reference format
    const TN_READ = 'scale minute to sec';
    const TF_READ = '"second" = "minute" * 60';
    const TD_READ = 'to convert times in minutes to seconds and the other way round';
    const TN_READ_ANOTHER = 'scale hour to sec';
    const TI_READ_ANOTHER = 2;
    const TF_DIAMETER = '= "circumference" / "Pi"';
    const TR_DIAMETER = '={w' . word_api::TI_CIRCUMFERENCE . '}/{w' . word_api::TI_PI . '}';
    const TN_READ_THIS = 'this';
    const TN_READ_PRIOR = 'prior';
    const TN_PERCENT = 'percent';
    const TN_INCREASE = 'increase';
    const TI_INCREASE = 21;
    const TF_INCREASE = '"percent" = ( "this" - "prior" ) / "prior"';
    const TF_INCREASE_ALTERNATIVE = '"percent" = 1 - ( "this" / "prior" )';
    const TR_INCREASE = '{w' . word_api::TI_PCT . '}=({w' . word_api::TI_THIS . '}-{w' . word_api::TI_PRIOR . '})/{w' . word_api::TI_PRIOR . '}';
    const TN_LITRE_TO_M3 = 'scale litre to m3';
    const TN_BIGGEST_CITY = 'population in the city of Zurich in percent of Switzerland';
    const TN_READ_SCALE_MIO = 'scale millions to one';
    const TF_READ_SCALE_MIO = '"one" = "millions" * 1000000';
    const TR_SCALE_MIO = '{w' . word_api::TI_ONE . '} = {w' . word_api::TI_MIO . '} * 1000000';
    const TN_PARTS_IN_PERCENT = 'parts in percent';
    const TF_PARTS_IN_PERCENT = '"percent" = "parts" "of" / "total"'; // TODO check if separate verb "of each" is needed
    const TR_PARTS_IN_PERCENT = '{w' . word_api::TI_PCT . '}={w' . word_api::TI_PARTS . '}{v' . verb_api::TI_OF . '}/{w' . word_api::TI_TOTAL . '}';

    // persevered formula names for unit and integration tests
    const TN_ADD = 'System Test Formula'; // to test adding a new formula to the database and using the increase formula
    const TN_ADD_VIA_FUNC = 'System Test Formula via SQL function';
    const TN_ADD_VIA_SQL = 'System Test Formula via SQL insert';
    const TN_RENAMED = 'System Test Formula Renamed';
    const TN_EXCLUDED = 'System Test Formula Excluded';
    const TN_THIS = 'System Test Formula This'; // to test if another formula of the functional type "this" can be created
    const TF_THIS = '= "System Test Formula This"';
    const TN_RATIO = 'System Test Formula PE Ratio'; // to test a simple ration calculation like how many times Switzerland is bigger than the canton zurich or the price to earning ration for equity
    const TF_RATIO = '"System Test Word PE Ratio" = "System Test Word Share Price" / "System Test Word Earnings"';
    const TN_SECTOR = 'System Test Formula Sector'; // to test the selection by a phrases and parents e.g. split all country totals by canton
    const TF_SECTOR = '= "Country" "differentiator" "Canton" / "System Test Word Total"';
    const TN_SCALE_K = 'System Test Formula scale thousand to one';
    const TF_SCALE_K = '"one" = "System Test Scaling Word e.g. thousands" * 1000';
    const TN_SCALE_TO_K = 'System Test Formula scale one to thousand';
    const TF_SCALE_TO_K = '"System Test Scaling Word e.g. thousands" = "one" / 1000';
    const TN_SCALE_MIO = 'System Test Formula scale millions to one';
    const TF_SCALE_MIO = '"one" = "million" * 1000000';
    const TN_SCALE_BIL = 'System Test Formula scale billions to one';
    const TF_SCALE_BIL = '"one" = "System Test Scaling Word e.g. billions" * 1000000000';

    // formula names that are reserved either
    // for creating the test formulas, that are removed after the test
    // so these formula names cannot be used for user formulas
    // or for fixed of the default data set that are used for unit tests
    const RESERVED_FORMULAS = array(
        self::TN_READ,
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_RENAMED,
        self::TN_EXCLUDED,
        self::TN_THIS,
        self::TN_RATIO,
        self::TN_SECTOR,
        self::TN_SCALE_K,
        self::TN_SCALE_TO_K,
        self::TN_SCALE_MIO,
        self::TN_SCALE_BIL
    );

    // formula names used for integration tests
    // that are removed after each test
    // and therefore cannot be used by users
    const TEST_FORMULAS = array(
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_RENAMED,
        self::TN_EXCLUDED,
        self::TN_THIS,
        self::TN_RATIO,
        self::TN_SECTOR,
        self::TN_SCALE_K,
        self::TN_SCALE_TO_K,
        self::TN_SCALE_MIO,
        self::TN_SCALE_BIL
    );


    /*
     * object vars
     */

    // the formula expression as shown to the user
    private string $user_text;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '')
    {
        parent::__construct($id, $name);
        $this->user_text = '';
    }

    /*
     * set and get
     */

    function set_usr_text(string $usr_text): void
    {
        $this->user_text = $usr_text;
    }

    function usr_text(): string
    {
        return $this->user_text;
    }


    /*
     * cast
     */

    function term(): term_api
    {
        return new term_api($this);
    }


    /*
     * interface
     */

    /**
     * @return array with the formula vars without empty values that are not needed
     */
    function jsonSerialize(): array
    {
        $vars = get_object_vars($this);
        return array_filter($vars, fn($value) => !is_null($value) && $value !== '');
    }

}
