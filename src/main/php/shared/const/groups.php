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

namespace shared\const;

class groups
{

    // phrase group or value names used by the system for testing

    // persevered phrase group names for unit and integration tests
    const TN_READ = 'Pi (math)';
    const TN_RENAMED = 'Pi';

    const ZH_CITY_INHABITANTS = words::ZH . ' ' . words::CITY . ' ' . words::INHABITANTS;
    const ZH_CITY_INHABITANTS_COM = words::INHABITANTS . ' in the ' . words::CITY . ' of ' . words::ZH;

    // persevered group names for database write tests
    const TN_ADD_PRIME_FUNC = 'System Test Group prime added via sql function';
    const TN_ADD_PRIME_SQL = 'System Test Group prime added via sql insert';
    const TN_ADD_MOST_FUNC = 'System Test Group main added via sql function';
    const TN_ADD_MOST_SQL = 'System Test Group main added via sql insert';
    const TN_ADD_BIG_FUNC = 'System Test Group big added via sql function';
    const TN_ADD_BIG_SQL = 'System Test Group big added via sql insert';

    const TN_ZH_2019 = self::ZH_CITY_INHABITANTS . ' (' . words::YEAR_2019 . ')';
    const TN_CH_INCREASE_2020 = words::TEST_INCREASE . ' in ' . words::CH . '\'s ' . words::INHABITANTS . ' from ' . words::YEAR_2019 . ' to ' . words::YEAR_2020 . ' in ' . words::PCT;
    const TN_ZH_2019_IN_MIO = self::TN_ZH_2019 . ' in ' . words::MIO;
    const TN_CH_2019 = words::INHABITANTS . ' of ' . words::CH . ' in Mio (' . words::YEAR_2019 . ')';

    const TN_TIME_VALUE = 'zukunft.com beta launch date';
    const TD_TIME_VALUE = 'the expected launch date of the first beta version of zukunft.com';
    const TN_TEXT_VALUE = 'zukunft.com pod URL';
    const TD_TEXT_VALUE = 'URL of this zukunft.com pod from the system configuration';

    const TN_GEO_VALUE = 'zukunft.com development geolocation';
    const TD_GEO_VALUE = 'the geolocation of the initial development of zukunft.com';

    // list of predefined group names used for system testing that are expected to be never renamed
    const RESERVED_GROUP_NAMES = [
        self::TN_READ,
        self::TN_ZH_2019,
        self::TN_CH_2019
    ];

    // list of group names and the related phrases that are used for system testing
    // and that should be created before the system test starts
    const TEST_GROUPS_CREATE = [
        [self::TN_READ,
            [words::PI, words::MATH]],
        [self::TN_CH_2019,
            [words::INHABITANTS, words::COUNTRY, words::CH, words::YEAR_2019, words::MIO]]
    ];

}
