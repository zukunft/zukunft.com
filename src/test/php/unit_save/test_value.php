<?php

/*

  test_value.php - TESTing of the VALUE class
  -------------
  

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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function create_test_values(testing $t)
{
    $t->header('Check if all base values are exist and create them if needed');

    // add the number of inhabitants in the canton of zurich without time definition
    $t->test_value(array(
        word::TN_CANTON,
        word::TN_ZH,
        word::TN_INHABITANT,
        word::TN_MIO
        ),
        value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // ... same with the concrete year
    $t->test_value(array(
        word::TN_CANTON,
        word::TN_ZH,
        word::TN_INHABITANT,
        word::TN_MIO,
        word::TN_2020
    ),
        value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // add the number of inhabitants in the city of zurich without time definition using the phrase zurich (city) instead of two single words
    $t->test_value(array(
        phrase::TN_ZH_CITY,
        word::TN_INHABITANT
    ),
        value::TV_CITY_ZH_INHABITANTS_2019);

    // ... same with the concrete year
    $t->test_value(array(
        phrase::TN_ZH_CITY,
        word::TN_INHABITANT,
        word::TN_2019
    ),
        value::TV_CITY_ZH_INHABITANTS_2019);

    // add the number of inhabitants in switzerland without time definition
    $t->test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_MIO
    ),
        value::TV_CH_INHABITANTS_2020_IN_MIO);

    // ... same with the concrete year
    $t->test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_MIO,
        word::TN_2020
    ),
        value::TV_CH_INHABITANTS_2020_IN_MIO);

    // ... same with the previous year
    $t->test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_MIO,
        word::TN_2019
    ),
        value::TV_CH_INHABITANTS_2019_IN_MIO);

    // add the percentage of inhabitants in Canton Zurich compared to Switzerland for calculation validation
    $t->test_value(array(
        word::TN_CANTON,
        word::TN_ZH,
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_PCT,
        word::TN_2020
    ),
        value::TEST_PCT);

    // add the increase of inhabitants in Switzerland from 2019 to 2020 for calculation validation
    $t->test_value(array(
        word::TN_CH,
        word::TN_INHABITANT,
        word::TN_INCREASE,
        word::TN_PCT,
        word::TN_2020
    ),
        value::TEST_INCREASE);

    // add some simple number for formula testing
    $t->test_value(array(
        word::TN_SHARE,
        word::TN_CHF
    ),
        value::TV_SHARE_PRICE);

    $t->test_value(array(
        word::TN_EARNING,
        word::TN_CHF
    ),
        value::TV_EARNINGS_PER_SHARE);

}
