<?php

/*

    shared/triples.php - predefined triples used in the backend and frontend as code id
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

class triples
{

    // this list is included in all preserved triple names
    // *_COM is the tooltip for the word; to have the comments on one place the yaml is the preferred place
    // *_ID is the expected database id only used for system testing

    // keyword to select the system configuration
    const SYSTEM_CONFIG = 'system configuration';
    const SYSTEM_CONFIG_ID = 73;

    // triples included in the initial setup that are used for system testing
    const TN_READ = 'Mathematical constant';
    const TI_READ = 1;
    const TD_READ = 'A mathematical constant that never changes e.g. Pi';
    const TN_PI = 'Pi';
    const TN_CUBIC_METER = 'm3';
    const TN_PI_NAME = 'Pi (math)';
    const TI_PI = 2;
    const TD_PI = 'ratio of the circumference of a circle to its diameter';
    const TN_E = '𝑒 (math)';
    const TI_E = 3;
    const TD_E = 'Is the limit of (1 + 1/n)^n as n approaches infinity';
    const TN_ADD = 'System Test Triple';
    const TN_ADD_AUTO = 'System Test Triple';
    const TN_EXCLUDED = 'System Test Excluded Zurich Insurance is not part of the City of Zurich';
    const TN_ADD_VIA_FUNC = 'System Test Triple added via sql function';
    const TN_ADD_VIA_SQL = 'System Test Triple added via prepared sql insert';

    const TN_ZH_CITY = 'Zurich (City)';
    const TI_ZH_CITY = 38;
    const TN_ZH_CITY_NAME = 'City of Zurich';
    const TN_BE_CITY = 'Bern (City)';
    const TI_BE_CITY = 39;
    const TN_GE_CITY = 'Geneva (City)';
    const TI_GE_CITY = 40;
    const TN_ZH_CANTON = 'Zurich (Canton)';
    const TN_ZH_CANTON_NAME = 'Canton Zurich';
    const TN_ZH_COMPANY = "Zurich Insurance";
    const COMPANY_VESTAS = "Vestas SA";
    const COMPANY_ABB = "ABB (Company)";
    const TN_2014_FOLLOW = "2014 is follower of 2013";
    const TN_TAXES_OF_CF = "Income taxes is part of cash flow statement";

    // list of predefined triple used for system testing that are expected to be never renamed
    const RESERVED_NAMES = array(
        self::SYSTEM_CONFIG,
        self::TN_ADD,
        self::TN_EXCLUDED
    );

    // array of triple names that used for db read testing and that should not be renamed
    const FIXED_NAMES = array(
        self::TN_READ
    );

    // list of triples that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const TEST_TRIPLES = array(
        self::TN_ADD,
        self::TN_ADD_VIA_FUNC,
        self::TN_ADD_VIA_SQL
    );

    const TEST_TRIPLE_STANDARD = array(
        self::TN_ADD,
        self::TN_EXCLUDED
    );

}
