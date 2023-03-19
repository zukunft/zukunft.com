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

namespace api;

use cfg\phrase_type;
use html\term_dsp;
use html\word_dsp;
use word;

class word_api extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // word names for stand-alone unit tests that are added with the system initial data load
    // TN_* is the name of the word used for testing
    // TD_* is the tooltip/description of the word
    const TN_READ = 'Mathematical constant';
    const TD_READ = 'A mathematical constant that never changes e.g. Pi';
    const TN_CONST = 'Pi';
    const TN_COUNTRY = 'Country';
    const TN_CANTON = 'Canton';
    const TN_CITY = 'City';
    const TN_CH = 'Switzerland';
    const TN_READ_GERMANY = 'Germany';
    const TN_ZH = 'Zurich';
    const TN_ZH_CANTON = 'Zurich (Canton)';
    const TN_ZH_CITY = 'Zurich (City)';
    const TN_INHABITANTS = 'inhabitants';
    const TN_INHABITANT = 'inhabitant';
    const TN_ONE = 'one';
    const TN_MIO = 'million';
    const TN_MIO_SHORT = 'mio';
    const TN_2015 = '2015';
    const TN_2016 = '2016';
    const TN_2017 = '2017';
    const TN_2018 = '2018';
    const TN_2019 = '2019';
    const TN_2020 = '2020';
    const TN_YEAR = 'Year';
    const TN_PCT = 'percent';

    // persevered word names for unit and integration tests based on the database
    const TN_ADD = 'System Test Word';
    const TN_RENAMED = 'System Test Word Renamed';
    const TN_PARENT = 'System Test Word Parent';
    const TN_COMPANY = 'System Test Word Group e.g. Company';
    const TN_FIN_REPORT = 'System Test Word with many relations e.g. Financial Report';
    const TN_CASH_FLOW = 'System Test Word Parent without Inheritance e.g. Cash Flow Statement';
    const TN_TAX_REPORT = 'System Test Word Child without Inheritance e.g. Income Taxes';
    const TN_ASSETS = 'System Test Word containing multi levels e.g. Assets';
    const TN_ASSETS_CURRENT = 'System Test Word multi levels e.g. Current Assets';
    const TN_SECTOR = 'System Test Word with differentiator e.g. Sector';
    const TN_ENERGY = 'System Test Word usage as differentiator e.g. Energy';
    const TN_WIND_ENERGY = 'System Test Word usage as differentiator e.g. Wind Energy';
    const TN_CASH = 'System Test Word multi levels e.g. Cash';
    const TN_2021 = 'System Test Time Word e.g. 2021';
    const TN_2022 = 'System Test Another Time Word e.g. 2022';
    const TN_CHF = 'System Test Measure Word e.g. CHF';
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


    // word groups for creating the test words and remove them after the test
    const RESERVED_WORDS = array(
        word::DB_SETTINGS,
        self::TN_READ,
        self::TN_ADD,
        self::TN_RENAMED,
        self::TN_PARENT,
        self::TN_COMPANY,
        self::TN_FIN_REPORT,
        self::TN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_ASSETS,
        self::TN_ASSETS_CURRENT,
        self::TN_SECTOR,
        self::TN_ENERGY,
        self::TN_WIND_ENERGY,
        self::TN_CASH,
        self::TN_2021,
        self::TN_2022,
        self::TN_CHF,
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
    const TEST_WORDS_STANDARD = array(
        self::TN_PARENT,
        self::TN_CH,
        self::TN_COUNTRY,
        self::TN_CANTON,
        self::TN_CITY,
        self::TN_COMPANY,
        self::TN_CASH_FLOW,
        self::TN_TAX_REPORT,
        self::TN_INHABITANTS,
        self::TN_MIO,
        self::TN_INCREASE,
        self::TN_YEAR,
        self::TN_2020,
        self::TN_SHARE,
        self::TN_PRICE,
        self::TN_EARNING,
        self::TN_PE,
        self::TN_TOTAL
    );
    const TEST_WORDS_MEASURE = array(self::TN_CHF);
    const TEST_WORDS_SCALING_HIDDEN = array(self::TN_ONE);
    const TEST_WORDS_SCALING = array(self::TN_IN_K, self::TN_MIO, self::TN_BIL);
    const TEST_WORDS_PERCENT = array(self::TN_PCT);
    // the time words must be in correct order because the following is set during creation
    const TEST_WORDS_TIME_YEAR = array(
        self::TN_2015,
        self::TN_2016,
        self::TN_2017,
        self::TN_2018,
        self::TN_2019,
        self::TN_2020,
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

    function set_description(string $description): void
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

    function term(): term_api
    {
        return new term_api($this->id, $this->name, word::class);
    }

    /**
     * @returns word_dsp the cast object with the HTML code generating functions
     */
    function dsp_obj(): word_dsp
    {
        $wrd_dsp = new word_dsp($this->id, $this->name, $this->description);
        $wrd_dsp->set_plural($this->plural);
        $wrd_dsp->type_id = $this->type_id;
        if ($this->parent != null) {
            $wrd_dsp->set_parent($this->parent->dsp_obj());
        }
        return $wrd_dsp;
    }

}
