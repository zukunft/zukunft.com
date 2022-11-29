<?php

/*

    test/unit_db/value.php - database unit testing of the value functions
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

function run_value_unit_db_tests(testing $t): void
{

    global $usr;

    // init
    $t->name = 'value->';

    $t->header('Unit database tests of the value class (src/main/php/model/value/value.php)');

    $t->subheader('Frontend API tests');

    $val = new value($usr);
    $val->load_by_id(1, value::class);
    $val->load_objects();
    $api_val = $val->api_obj();
    $t->assert($t->name . 'api->id', $api_val->id, $val->id());
    $t->assert($t->name . 'api->number', $api_val->number(), $val->number());


    $phr_grp = $t->add_phrase_group(array(triple::TN_READ_NAME),phrase_group::TN_READ);
    $val = $t->load_value_by_phr_grp($phr_grp);
    $t->assert_api_exp($val);

}

