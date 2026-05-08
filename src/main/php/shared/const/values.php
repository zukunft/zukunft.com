<?php

/*

    shared/const/values.php - values used by the system for testing
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

    Copyright (c) 1995-2025 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\main\php\shared\const;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;

class values
{

    // a list of fixed values that are used for system tests
    // *_ID is the group id of the value
    // *_FORM is the default formatted value
    CONST float PI_LONG = 3.14159265358979323846264338327950288419716939937510; // pi
    CONST float PI = 3.1415926535898; // pi
    CONST float PI_SHORT = 3.1415927; // pi
    const int PI_ID = 32770;
    const int PI_SYMBOL_ID = 32770;
    CONST float SAMPLE_ZERO = 0.0;
    CONST float E = 2.718281828459045235360; // Euler number
    const int E_ID = 32771;
    CONST float E_CONST = 0.57721566490153; // Euler const
    const int E_CONST_ID = 32771;
    const int TRANSITION_OF_CS = 9192631770;
    const int SPEED_OF_LIGHT = 299792458;
    const string SPEED_OF_LIGHT_TXT = "299'792'458";
    CONST int SAMPLE_INT = 123456;
    CONST string SAMPLE_INT_COM = 'System Test Word Description for value curl testing';
    CONST float SAMPLE_FLOAT = 123.456;
    CONST int SAMPLE_BIG = 123456789;
    CONST int SAMPLE_BIGGER = 234567890;
    CONST string SAMPLE_FLOAT_HIGH_QUOTE_FORM = "123'456";
    CONST string SAMPLE_FLOAT_SPACE_FORM = "123 456";
    CONST float SAMPLE_PCT = 0.182642816772838; // to test the percentage calculation by the percent of Swiss inhabitants living in Canton Zurich
    CONST float INCREASE = 0.007871833296164; // to test the increase calculation by the increase of inhabitants in Switzerland from 2019 to 2020
    CONST float CANTON_ZH_INHABITANTS_2020_IN_MIO = 1.553423;
    CONST int CITY_ZH_INHABITANTS_2019 = 415367;
    CONST float CH_INHABITANTS_2019_IN_MIO = 8.438822;
    CONST float CH_INHABITANTS_2020_IN_MIO = 8.505251;
    CONST float SHARE_PRICE = 17.08;
    CONST float EARNINGS_PER_SHARE = 1.22;
    CONST string SALES_INCREASE_2017_FORM = '90.03 %';
    const string NESN_SALES_2016_FORM = '89\'469';

    CONST string TIME = '2025-06-07 12:30:00 UTC'; // to test time values
    CONST string TEXT = POD_NAME; // to test text values
    CONST string GEO = '47.263179, 8.684730'; // to test geo values
    CONST string DB_TEXT = 'old db text sample value'; // to test updating text values



    // list of values that are used for system testing that should be removed are the system test has been completed
    // and that are never expected to be used by a user
    const array TEST_VALUES = array(
        [words::TEST_ADD_GROUP_PRIME],
    );

}
