<?php

/*

    api/view/view_cmp.php - the view component object for the frontend API
    ---------------------


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

class view_cmp_api extends user_sandbox_named_with_type_api
{

    /*
     * const for the api
     */

    const API_NAME = 'component';


    /*
     * const for system testing
     */

    // view components used for unit tests
    // TN_* is the name of the view component used for testing
    // TD_* is the tooltip/description of the view component
    const TN_READ = 'Name';
    const TD_READ = 'simply show the word name';

    // persevered view component names for unit and integration tests
    const TN_ADD = 'System Test View Component';
    const TN_RENAMED = 'System Test View Component Renamed';
    const TN_ADD2 = 'System Test View Component Two';
    const TN_TITLE = 'System Test View Component Title';
    const TN_VALUES = 'System Test View Component Values';
    const TN_RESULTS = 'System Test View Component Results';
    const TN_TABLE = 'System Test View Component Table';

    // array of view names that used for testing and remove them after the test
    const RESERVED_VIEW_COMPONENTS = array(
        self::TN_ADD,
        self::TN_RENAMED,
        self::TN_ADD2,
        self::TN_TITLE,
        self::TN_VALUES,
        self::TN_RESULTS,
        self::TN_TABLE
    );

    // array of test view names create before the test
    const TEST_VIEW_COMPONENTS = array(
        self::TN_TITLE,
        self::TN_VALUES,
        self::TN_RESULTS,
        self::TN_TABLE
    );


    // the code id of the view component type because all types should be loaded in the frontend at startup
    // public int $pos_type_id = view_cmp_pos_type::BELOW;
    // TODO use for default position ?
    // public int $pos_type_id = 1;
}
