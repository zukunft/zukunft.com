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

namespace api\view;

include_once API_SANDBOX_PATH . 'sandbox_typed.php';
include_once API_COMPONENT_PATH . 'component_list.php';
include_once API_VIEW_PATH . 'component_link_list.php';

use api\component\component_list AS component_list_api;
use api\sandbox\sandbox_typed as sandbox_typed_api;
use api\view\component_link_list AS component_link_list_api;

class view extends sandbox_typed_api
{

    /*
     * const for system testing
     */

    // persevered view names for unit and integration tests
    // TN_* means 'test name'
    // TD_* means 'test description'
    // TC_* means 'test code id'
    // TI_* means 'test id'
    const TN_READ = 'Word';
    const TD_READ = 'the default view for words';
    const TC_READ = 'word';
    const TI_READ = 1;
    const TN_ADD = 'System Test View';
    const TN_ADD_VIA_FUNC = 'System Test View added via sql function';
    const TN_ADD_VIA_SQL = 'System Test View added via sql insert';
    const TN_RENAMED = 'System Test View Renamed';
    const TN_COMPLETE = 'System Test View Complete';
    const TN_EXCLUDED = 'System Test View Excluded';
    const TN_TABLE = 'System Test View Table';
    const TN_ALL = 'complete';

    // to test a system view (add word) as unit test without database
    const TN_FORM = 'Add word';
    const TN_FORM_NEW = 'Add New Word';
    const TD_FORM = 'system form to add a word';
    const TC_FORM = 'word_add';

    const TN_READ_RATIO = 'Company ratios';
    const TN_READ_NESN_2016 = 'Nestlé Financial Statement 2016';
    const TD_LINK = 'System Test description for a view term link';

    // array of view names that used for testing and remove them after the test
    const RESERVED_VIEWS = array(
        self::TN_READ,
        self::TN_ADD,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_VIA_FUNC,
        self::TN_RENAMED,
        self::TN_COMPLETE,
        self::TN_EXCLUDED,
        self::TN_TABLE
    );

    // array of test view names create before the test
    const TEST_VIEWS = array(
        self::TN_ADD,
        self::TN_ADD_VIA_SQL,
        self::TN_ADD_VIA_FUNC,
        self::TN_RENAMED,
        self::TN_COMPLETE,
        self::TN_EXCLUDED,
        self::TN_TABLE
    );

    const TEST_VIEWS_AUTO_CREATE = array(
        self::TN_COMPLETE,
        self::TN_EXCLUDED,
        self::TN_TABLE
    );


    /*
     * object vars
     */

    // to link predefined behavier in the frontend
    public ?string $code_id = null;

    // the components linked to this view
    public ?component_link_list_api $components = null;
}
