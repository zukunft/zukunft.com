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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/agpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

use api\phrase_api;
use api\value_api;
use api\word_api;

function create_test_values(testing $t): void
{
    $t->header('Check if all base values are exist and create them if needed');

    // add the number of inhabitants in the canton of zurich without time definition
    $t->test_value(array(
        word_api::TN_CANTON,
        word_api::TN_ZH,
        word_api::TN_INHABITANTS,
        word_api::TN_MIO
        ),
        value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // ... same with the concrete year
    $t->test_value(array(
        word_api::TN_CANTON,
        word_api::TN_ZH,
        word_api::TN_INHABITANTS,
        word_api::TN_MIO,
        word_api::TN_2020
    ),
        value_api::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // add the number of inhabitants in the city of zurich without time definition using the phrase zurich (city) instead of two single words
    $t->test_value(array(
        phrase_api::TN_ZH_CITY,
        word_api::TN_INHABITANTS
    ),
        value_api::TV_CITY_ZH_INHABITANTS_2019);

    // ... same with the concrete year
    $t->test_value(array(
        phrase_api::TN_ZH_CITY,
        word_api::TN_INHABITANTS,
        word_api::TN_2019
    ),
        value_api::TV_CITY_ZH_INHABITANTS_2019);

    // add the number of inhabitants in switzerland without time definition
    $t->test_value(array(
        word_api::TN_CH,
        word_api::TN_INHABITANTS,
        word_api::TN_MIO
    ),
        value_api::TV_CH_INHABITANTS_2020_IN_MIO);

    // ... same with the concrete year
    $t->test_value(array(
        word_api::TN_CH,
        word_api::TN_INHABITANTS,
        word_api::TN_MIO,
        word_api::TN_2020
    ),
        value_api::TV_CH_INHABITANTS_2020_IN_MIO);

    // ... same with the previous year
    $t->test_value(array(
        word_api::TN_CH,
        word_api::TN_INHABITANTS,
        word_api::TN_MIO,
        word_api::TN_2019
    ),
        value_api::TV_CH_INHABITANTS_2019_IN_MIO);

    // add the percentage of inhabitants in Canton Zurich compared to Switzerland for calculation validation
    $t->test_value(array(
        word_api::TN_CANTON,
        word_api::TN_ZH,
        word_api::TN_CH,
        word_api::TN_INHABITANTS,
        word_api::TN_PCT,
        word_api::TN_2020
    ),
        value_api::TV_PCT);

    // add the increase of inhabitants in Switzerland from 2019 to 2020 for calculation validation
    $t->test_value(array(
        word_api::TN_CH,
        word_api::TN_INHABITANTS,
        word_api::TN_INCREASE,
        word_api::TN_PCT,
        word_api::TN_2020
    ),
        value_api::TV_INCREASE);

    // add some simple number for formula testing
    $t->test_value(array(
        word_api::TN_SHARE,
        word_api::TN_CHF
    ),
        value_api::TV_SHARE_PRICE);

    $t->test_value(array(
        word_api::TN_EARNING,
        word_api::TN_CHF
    ),
        value_api::TV_EARNINGS_PER_SHARE);

}
