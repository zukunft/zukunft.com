<?php

/*

    shared/const/groups.php - phrase group or value names used by the system for testing
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

class groups
{

    // phrase group or value names used by the system for testing

    // persevered phrase group names for unit and integration tests
    const string TN_READ = 'Pi (math)';
    const string TN_RENAMED = 'Pi';

    const string ZH_CITY_INHABITANTS = words::ZH . ' ' . words::CITY . ' ' . words::INHABITANTS;
    const string ZH_CITY_INHABITANTS_COM = words::INHABITANTS . ' in the ' . words::CITY . ' of ' . words::ZH;

    // si units
    const string TIME_DEFINITION = 'hyperfine transition frequency of Cs';
    const string LENGTH_DEFINITION = 'speed of light';

    // persevered group names for database write tests
    const string TN_ADD_PRIME_FUNC = 'System Test Group prime added via sql function';
    const string TN_ADD_PRIME_SQL = 'System Test Group prime added via sql insert';
    const string TN_ADD_MOST_FUNC = 'System Test Group main added via sql function';
    const string TN_ADD_MOST_SQL = 'System Test Group main added via sql insert';
    const string TN_ADD_BIG_FUNC = 'System Test Group big added via sql function';
    const string TN_ADD_BIG_SQL = 'System Test Group big added via sql insert';

    const string TN_ZH_2019 = self::ZH_CITY_INHABITANTS . ' (' . words::YEAR_2019 . ')';
    const string TN_CH_INCREASE_2020 = words::TEST_INCREASE . ' in ' . words::CH . '\'s ' . words::INHABITANTS . ' from ' . words::YEAR_2019 . ' to ' . words::YEAR_2020 . ' in ' . words::PCT;
    const string TN_ZH_2019_IN_MIO = self::TN_ZH_2019 . ' in ' . words::MIO;
    const string TN_CH_2019 = words::INHABITANTS . ' of ' . words::CH . ' in Mio (' . words::YEAR_2019 . ')';

    const string TN_TIME_VALUE = 'zukunft.com beta launch date';
    const string TD_TIME_VALUE = 'the expected launch date of the first beta version of zukunft.com';
    const string TN_TEXT_VALUE = 'zukunft.com pod URL';
    const string TD_TEXT_VALUE = 'URL of this zukunft.com pod from the system configuration';

    const string TN_GEO_VALUE = 'zukunft.com development geolocation';
    const string TD_GEO_VALUE = 'the geolocation of the initial development of zukunft.com';

    // list of predefined group names used for system testing that are expected to be never renamed
    const array RESERVED_GROUP_NAMES = [
        self::TN_READ,
        self::TN_ZH_2019,
        self::TN_CH_2019
    ];

    // list of group names and the related phrases that are used for system testing
    // and that should be created before the system test starts
    const array TEST_GROUPS_CREATE = [
        [self::TN_READ,
            [words::PI, words::MATH]],
        [self::TN_CH_2019,
            [words::INHABITANTS, words::COUNTRY, words::CH, words::YEAR_2019, words::MIO]]
    ];

}
