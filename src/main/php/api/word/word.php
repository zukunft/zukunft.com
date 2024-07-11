<?php

/*

    api/word/word.php - the minimal word object for the backend to frontend API transfer
    -----------------


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

namespace api\word;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';

use api\phrase\phrase as phrase_api;
use api\phrase\term as term_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use cfg\phrase_type;
use cfg\word as word_cfg;

class word extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // word names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the word used for testing created with the initial setup (see also TWN_*)
    // TI_* is the database id based on the initial load
    // TD_* is the tooltip/description of the word
    const TN_READ = 'Mathematics';
    const TD_READ = 'Mathematics is an area of knowledge that includes the topics of numbers and formulas';
    const TI_MATH = 1;
    const TN_READ_PLURAL = 'Mathematics';
    const TN_CONST = 'constant';
    const TD_CONST = 'fixed and well-defined number';
    const TI_CONST = 2;
    const TN_ONE = 'one';
    const TI_ONE = 3;
    const TN_PI = 'Pi';
    const TI_PI = 4;
    const TD_PI = 'ratio of the circumference of a circle to its diameter';
    const TN_CIRCUMFERENCE = 'circumference';
    const TI_CIRCUMFERENCE = 5;
    const TN_DIAMETER = 'diameter';
    const TI_DIAMETER = 6;
    const TN_E = "Euler's constant";
    const TI_E = 6;
    const TN_SECOND = 'second';
    const TI_SECOND = 19;
    const TN_YEAR = 'Year';
    const TI_YEAR = 137;
    const TN_MINUTE = 'minute';
    const TI_MINUTE = 101;
    const TN_MIO = 'million';
    const TI_MIO = 170;
    const TN_MIO_SHORT = 'mio';
    const TN_COUNTRY = 'Country';
    const TN_CH = 'Switzerland';
    const TI_CH = 197;
    const TN_DE = 'Germany';
    const TN_CANTON = 'Canton';
    const TI_CANTON = 198;
    const TN_CITY = 'City';
    const TI_CITY = 199;
    const TN_ZH = 'Zurich';
    const TI_ZH = 200;
    const TN_BE = 'Bern';
    const TI_BE = 201;
    const TN_GE = 'Geneva';
    const TI_GE = 202;
    const TN_INHABITANT = 'inhabitant';
    const TI_INHABITANT = 204;
    // TODO add test to search for words in all language forms e.g. plural
    const TN_INHABITANTS = 'inhabitants';
    const TN_2013 = '2013';
    const TI_2013 = 326;
    const TN_2014 = '2014';
    const TI_2014 = 325;
    const TN_2015 = '2015';
    const TI_2015 = 205;
    const TN_2016 = '2016';
    const TI_2016 = 206;
    const TN_2017 = '2017';
    const TI_2017 = 207;
    const TN_2018 = '2018';
    const TI_2018 = 208;
    const TN_2019 = '2019';
    const TI_2019 = 142;
    const TN_2020 = '2020';
    const TI_2020 = 209;
    const TN_PCT = 'percent';
    const TI_PCT = 172;
    // _PRE are the predefined words
    const TN_THIS_PRE = 'this'; // the test name for the predefined word 'this'
    const TI_THIS = 192;
    const TN_PRIOR_PRE = 'prior';
    const TI_PRIOR = 194;
    const TN_PARTS = 'parts';
    const TI_PARTS = 265;
    const TN_TOTAL_PRE = 'total';
    const TI_TOTAL = 266;
    const TN_COMPANY = 'Company';
    const TI_COMPANY = 322;
    const TN_ABB = 'ABB';
    const TI_ABB = 323;
    const TN_VESTAS = 'Vestas';
    const TI_VESTAS = 324;
    const TN_CHF = 'CHF';
    const TI_CHF = 316;
    const TN_SALES = 'Sales';
    const TI_SALES = 317;
    const TN_CASH_FLOW = 'cash flow statement';
    const TI_CASH_FLOW = 274;
    const TN_TAX = 'Income taxes';
    const TI_TAX = 273;
    const TN_GWP = 'global warming potential';
    const TI_GWP = 1070;

    // persevered word names for unit and integration tests based on the database
    // TWN_* - is a Test Word Name for words created only for testing (see also TN_*)
    const TN_ADD = 'System Test Word';
    const TN_ADD_VIA_FUNC = 'System Test Word added via sql function';
    const TN_ADD_VIA_SQL = 'System Test Word added via sql insert';
    const TN_ADD_GROUP_PRIME_FUNC = 'System Test Word for prime group add via sql function';
    const TN_ADD_GROUP_PRIME_SQL = 'System Test Word for prime group add via sql insert';
    const TN_ADD_GROUP_MOST_FUNC = 'System Test Word for main group add via sql function';
    const TN_ADD_GROUP_MOST_SQL = 'System Test Word for main group add via sql insert';
    const TN_ADD_GROUP_BIG_FUNC = 'System Test Word for big group add via sql function';
    const TN_ADD_GROUP_BIG_SQL = 'System Test Word for big group add via sql insert';
    const TN_RENAMED = 'System Test Word Renamed';
    const TN_RENAMED_GROUP_PRIME_FUNC = 'System Test Word for prime group RENAMED via sql function';
    const TN_RENAMED_GROUP_PRIME_SQL = 'System Test Word for prime group RENAMED via sql insert';
    const TN_RENAMED_GROUP_MOST_FUNC = 'System Test Word for main group RENAMED via sql function';
    const TN_RENAMED_GROUP_MOST_SQL = 'System Test Word for main group RENAMED via sql insert';
    const TN_RENAMED_GROUP_BIG_FUNC = 'System Test Word for big group RENAMED via sql function';
    const TN_RENAMED_GROUP_BIG_SQL = 'System Test Word for big group RENAMED via sql insert';
    const TN_PARENT = 'System Test Word Parent';
    const TN_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const TWN_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TN_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const TN_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const TN_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const TN_SECTOR = 'System Test Word with differentiator e.g. Sector';
    const TN_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const TN_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const TN_CASH = 'System Test Word multi levels e.g. Cash';
    const TN_2021 = 'System Test Time Word e.g. 2021';
    const TN_2022 = 'System Test Another Time Word e.g. 2022';
    const TWN_CHF = 'System Test Measure Word e.g. CHF';
    const TN_SHARE = 'System Test Word Share';
    const TN_PRICE = 'System Test Word Share Price';
    const TN_EARNING = 'System Test Word Earnings';
    const TN_PE = 'System Test Word PE Ratio';
    const TN_IN_K = 'System Test Scaling Word e.g. thousands';
    const TN_BIL = 'System Test Scaling Word e.g. billions';
    const TN_TOTAL = 'System Test Word Total';
    const TN_INCREASE = 'System Test Word Increase';
    const TN_THIS = 'System Test Word This';
    const TN_PRIOR = 'System Test Word Prior';
    const TN_TIME_JUMP = 'System Test Word Time Jump e.g. yearly';
    const TN_LATEST = 'System Test Word Latest';
    const TN_SCALING_PCT = 'System Test Word Scaling Percent';
    const TN_SCALING_MEASURE = 'System Test Word Scaling Measure';
    const TN_CALC = 'System Test Word Calc';
    const TN_LAYER = 'System Test Word Layer';

    const TN_ADD_API = 'System Test Word API';
    const TD_ADD_API = 'System Test Word API Description';
    const TN_UPD_API = 'System Test Word API Renamed';
    const TD_UPD_API = 'System Test Word API Description Renamed';


    // list of predefined words used for system testing that are expected to be never renamed
    const RESERVED_WORDS = array(
        word_cfg::SYSTEM_CONFIG,
        self::TN_READ,
        self::TN_CONST,
        self::TN_PI,
        self::TN_ONE,
        self::TN_MIO,
        self::TN_MIO_SHORT,
        self::TN_COUNTRY,
        self::TN_CH,
        self::TN_DE,
        self::TN_CANTON,
        self::TN_CITY,
        self::TN_ZH,
        self::TN_BE,
        self::TN_GE,
        self::TN_INHABITANT,
        self::TN_INHABITANTS,
        self::TN_YEAR,
        self::TN_2015,
        self::TN_2016,
        self::TN_2017,
        self::TN_2018,
        self::TN_2019,
        self::TN_2020,
        self::TN_PCT,
        self::TN_COMPANY,
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_GROUP_MOST_FUNC,
        self::TN_ADD_GROUP_MOST_SQL,
        self::TN_ADD_GROUP_PRIME_FUNC,
        self::TN_ADD_GROUP_PRIME_SQL,
        self::TN_ADD_GROUP_BIG_FUNC,
        self::TN_ADD_GROUP_BIG_SQL,
        self::TN_RENAMED,
        self::TN_RENAMED_GROUP_MOST_FUNC,
        self::TN_RENAMED_GROUP_MOST_SQL,
        self::TN_RENAMED_GROUP_PRIME_FUNC,
        self::TN_RENAMED_GROUP_PRIME_SQL,
        self::TN_RENAMED_GROUP_BIG_FUNC,
        self::TN_RENAMED_GROUP_BIG_SQL,
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );
    // list of words that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_WORDS = array(
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_GROUP_PRIME_FUNC,
        self::TN_ADD_GROUP_PRIME_SQL,
        self::TN_ADD_GROUP_MOST_FUNC,
        self::TN_ADD_GROUP_MOST_SQL,
        self::TN_ADD_GROUP_BIG_FUNC,
        self::TN_ADD_GROUP_BIG_SQL,
        self::TN_RENAMED,
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );
    // list of words that are used for system testing and that should be created before the system test starts
    const TEST_WORDS_CREATE = array(
        self::TN_ADD,
        self::TN_PARENT,
        self::TN_FIN_REPORT,
        self::TWN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TWN_CHF,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_IN_K,
        self::TN_BIL,
        self::TN_TOTAL,
        self::TN_INCREASE,
        self::TN_THIS,
        self::TN_PRIOR,
        self::TN_TIME_JUMP,
        self::TN_LATEST,
        self::TN_SCALING_PCT,
        self::TN_SCALING_MEASURE,
        self::TN_CALC,
        self::TN_LAYER,
        self::TN_ADD_API,
        self::TN_UPD_API
    );
    const TEST_WORDS_MEASURE = array(self::TWN_CHF);
    const TEST_WORDS_SCALING_HIDDEN = array(self::TN_ONE);
    const TEST_WORDS_SCALING = array(self::TN_IN_K, self::TN_MIO, self::TN_MIO_SHORT, self::TN_BIL);
    const TEST_WORDS_PERCENT = array(self::TN_PCT);
    // the time words must be in correct order because the following is set during creation
    const TEST_WORDS_TIME_YEAR = array(
        self::TN_2015,
        self::TN_2016,
        self::TN_2017,
        self::TN_2018,
        self::TN_2021,
        self::TN_2022
    );


    /*
     * object vars
     */

    // the mouse over tooltip for the word
    // a null value is needed to detect if nothing has been changed by the user
    public ?string $description = null;

    // the language specific forms
    private ?string $plural = null;

    // the main parent phrase
    private ?phrase_api $parent;


    /*
     * construct and map
     */

    function __construct(int $id = 0, string $name = '', ?string $description = null)
    {
        parent::__construct($id, $name);
        $this->description = $description;
        $this->parent = null;
        $this->type_id = null;
    }


    /*
     * set and get
     */

    function set_description(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string the display value of the tooltip where null is an empty string
     */
    function description(): string
    {
        if ($this->description == null) {
            return '';
        } else {
            return $this->description;
        }
    }

    function set_plural(?string $plural): void
    {
        $this->plural = $plural;
    }

    function plural(): ?string
    {
        return $this->plural;
    }

    function set_parent(?phrase_api $parent): void
    {
        $this->parent = $parent;
    }

    function parent(): ?phrase_api
    {
        return $this->parent;
    }

    /**
     * @param string|null $code_id the code id of the phrase type
     */
    function set_type(?string $code_id): void
    {
        global $phrase_types;
        if ($code_id == null) {
            $this->set_type_id(null);
        } else {
            $this->set_type_id($phrase_types->id($code_id));
        }
    }

    /**
     * TODO use ENUM instead of string in php version 8.1
     * @return phrase_type|null the phrase type of this word
     */
    function type(): ?object
    {
        global $phrase_types;
        if ($this->type_id == null) {
            return null;
        } else {
            return $phrase_types->get_by_id($this->type_id);
        }
    }


    /*
     * cast
     */

    /**
     * @return phrase_api the related phrase api or display object with the basic values filled
     */
    function phrase(): phrase_api
    {
        return new phrase_api($this);
    }

    function term(): term_api
    {
        return new term_api($this);
    }

}
