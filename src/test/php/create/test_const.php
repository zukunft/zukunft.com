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

    // the timestamp used for unit testing
    const string DUMMY_DATETIME = '2022-12-26T18:23:45+01:00';
    // usage used for unit testing
    const int DUMMY_USAGE = 2;
    const int DUMMY_USAGE_VERB = 23;
    // impact used for unit testing
    const float DUMMY_IMPACT = 3.4;
    const float DUMMY_IMPACT_VERB = 123.4;

}