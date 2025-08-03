<?php

/*

    test/unit_write/api_write_tests.php - test the api write interface
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

    Copyright (c) 1995-2022 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace unit_write;

use cfg\const\def;
use cfg\const\paths;
use html\const\paths as html_paths;

include_once paths::MODEL_WORD . 'word.php';

use cfg\word\word;
use test\test_cleanup;

class api_write_tests
{

    /*
     * do it
     */

    /**
     * execute the API test using localhost
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'api write ';
        $t->header($ts);

        $t->subheader($ts . ' direct');
        // TODO Prio 0 activate
        //foreach (def::MAIN_CLASSES as $class) {
        //$t->assert_api_post($class);
        $t->assert_api_post_direct(word::class, $t->usr1);
        //$t->assert_api_put(word::class);
        $t->assert_api_del_direct(word::class, $t->usr1);
        //}

        $t->subheader($ts . ' api login');

        // TODO Prio 1 add an api login

        $t->subheader($ts . ' via api call');
        // TODO Prio 0 activate
        //foreach (def::MAIN_CLASSES as $class) {
        //$t->assert_api_post($class);
        //$t->assert_api_post(word::class);
        //$t->assert_api_put(word::class);
        //$t->assert_api_del(word::class);
        //}
        // TODO remove temp
        //$t->assert_api_del_direct(word::class, $t->usr1);
    }

}