<?php

/*

  test_value.php - TESTing of the VALUE class
  -------------
  

zukunft.com - calc with words

copyright 1995-2021 by zukunft.com AG, Blumentalstrasse 15, 8707 Uetikon am See, Switzerland

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

function create_base_values()
{
    test_header('Check if all base values are exist and create them if needed');

    // add the number of inhabitants in the canton of zurich without time definition
    test_value(array(
        word::TN_CANTON,
        word::TN_ZH,
        word::TN_MIO,
        word::TN_INHABITANT
        ),
        value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // ... same with the concrete year
    test_value(array(
        word::TN_CANTON,
        word::TN_ZH,
        word::TN_MIO,
        word::TN_INHABITANT,
        word::TN_2020
    ),
        value::TV_CANTON_ZH_INHABITANTS_2020_IN_MIO);

    // add the number of inhabitants in the city of zurich without time definition using the phrase zurich (city) instead of two single words
    test_value(array(
        phrase::TN_ZH_CITY,
        word::TN_INHABITANT
    ),
        value::TV_CITY_ZH_INHABITANTS_2019);

    // ... same with the concrete year
    test_value(array(
        phrase::TN_ZH_CITY,
        word::TN_INHABITANT,
        word::TN_2019
    ),
        value::TV_CITY_ZH_INHABITANTS_2019);

    // add the number of inhabitants in switzerland without time definition
    test_value(array(
        word::TN_CH,
        word::TN_MIO,
        word::TN_INHABITANT
    ),
        value::TV_CH_INHABITANTS_2019_IN_MIO);

    // ... same with the concrete year
    test_value(array(
        word::TN_CH,
        word::TN_MIO,
        word::TN_INHABITANT,
        word::TN_2019
    ),
        value::TV_CH_INHABITANTS_2019_IN_MIO);

    echo "<br><br>";
}
