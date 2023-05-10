<?php

/*

    api/view/view.php - the view object for the frontend API
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

include_once API_SANDBOX_PATH . 'sandbox_typed.php';

class view_api extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // persevered view names for unit and integration tests
    // TN_* means 'test name'
    // TD_* means 'test description'
    // TI_* means 'test code id'
    const TN_READ = 'Word';
    const TD_READ = 'the default view for words';
    const TI_READ = 'word';
    const TN_ADD = 'System Test View';
    const TN_RENAMED = 'System Test View Renamed';
    const TN_COMPLETE = 'System Test View Complete';
    const TN_TABLE = 'System Test View Table';

    // to test a system view (add word) as unit test without database
    const TN_FORM = 'Add word';
    const TD_FORM = 'system form to add a word';
    const TI_FORM = 'word_add';

    const TN_READ_RATIO = 'Company ratios';
    const TN_READ_NESN_2016 = 'Nestlé Financial Statement 2016';

    // array of view names that used for testing and remove them after the test
    const RESERVED_VIEWS = array(
        self::TN_ADD,
        self::TN_RENAMED,
        self::TN_COMPLETE,
        self::TN_TABLE
    );

    // array of test view names create before the test
    const TEST_VIEWS = array(
        self::TN_COMPLETE,
        self::TN_TABLE
    );


    /*
     * object vars
     */

    // the mouse over tooltip for the word
    public ?string $description = null;
    public ?string $code_id = null;

    // the components linked to this view
    public ?component_list_api $components = null;
}
