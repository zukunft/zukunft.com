<?php

/*

    test/create/test_const.php - const only used for unit, db read, api, ui, db write and pod tests
    --------------------------


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

namespace Zukunft\ZukunftCom\test\php\create;

class test_const
{

    // fixed replacement for the volatile CSRF session token in HTML snapshot tests
    const string DUMMY_SESSION_TOKEN = '5e18d78c56c5ccb645631d7e5c6657d02a2f7c579cd01d36d3f5539ba07a6c13';

    // the timestamp used for unit testing
    const string DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';
    // one second later, e.g. to test that the change time is the first sort key of the change log
    const string DUMMY_DATETIME_LATER = '2022-12-26T18:23:46+01:00';
    // usage used for unit testing
    const int DUMMY_USAGE_WORD = 3;
    const int DUMMY_USAGE_VERB = 23;
    const int DUMMY_USAGE_SOURCE = 2;
    const int DUMMY_USAGE_FORMULA = 7;
    const int DUMMY_USAGE_VIEW = 1;
    const int DUMMY_USAGE_COMPONENT = 2;
    // the order number of the filled formula link
    const int FORMULA_LINK_ORDER_NBR = 2;
    // the order number logged as a change of the filled component link
    const int COMPONENT_LINK_ORDER_NBR = 3;
    // the order number of the filled view link
    const int TERM_VIEW_ORDER_NBR = 4;
    // the start position logged as a change of the filled view relation
    const int VIEW_RELATION_START_POS = 16;
    // impact used for unit testing
    const float DUMMY_IMPACT = 3.4;
    const float DUMMY_IMPACT_VERB = 123.4;

}