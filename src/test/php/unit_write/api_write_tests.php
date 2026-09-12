<?php

/*

    test/unit_write/api_write_tests.php - test the api read interface via REST get calls
    -----------------------------------

    the api is used mostly for reading (get); the write path (post/put/delete) needs an
    authenticated session with an anti-csrf token (see controller::change_permitted), so this
    test focuses on the get calls that can run standalone against the served api. it reads each
    main object type by its default id and compares the json to the committed fixture.


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

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'triple.php';
include_once paths::MODEL_VERB . 'verb.php';
include_once paths::MODEL_FORMULA . 'formula.php';
include_once paths::MODEL_VIEW . 'view.php';
include_once paths::MODEL_COMPONENT . 'component.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\view\view;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class api_write_tests
{

    /*
     * do it
     */

    /**
     * read each main object type via a REST get call against the served api
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'api get ';
        $t->header($ts);

        $t->subheader($ts . 'read the main object types');
        // each of these object types is readable by its default id and has a committed json
        // fixture, so a get call can be checked standalone; the value, source, ref, group and
        // user types need a specific id or an admin login and are covered by unit_api/api_tests
        $t->assert_api_get(word::class);
        $t->assert_api_get(verb::class);
        $t->assert_api_get(triple::class);
        $t->assert_api_get(formula::class);
        $t->assert_api_get(view::class);
        $t->assert_api_get(component::class);

        /*
         * TODO Prio 0 activate
        $t->subheader($ts . ' direct');
        foreach (def::MAIN_CLASSES as $class) {
            $t->assert_api_post($class, $t);
            $t->assert_api_post_direct(word::class, $t->usr1, $t);
            $t->assert_api_put(word::class, $t);
            $t->assert_api_del_direct(word::class, $t->usr1, $t);
        }

        // a user without login (ip user) must be refused, because the ip user change block is
        // enforced centrally in the model save/del, not only in the http/view.php frontend
        $t_usr = new test_users($t);
        $t->assert_api_write_blocked_for_ip_user(word::class, $t_usr->user_ip_loaded(), $t);

        // a request without a requesting user on the message must be refused the same way
        $t->assert_api_write_blocked_without_user(word::class, $t);

        $t->subheader($ts . ' api login');

        // TODO Prio 1 add an api login

        $t->subheader($ts . ' via api call');
        foreach (def::MAIN_CLASSES as $class) {
            $t->assert_api_post($class, $t);
            $t->assert_api_post(word::class, $t);
            $t->assert_api_put(word::class, $t);
            $t->assert_api_del(word::class);
        }
        */

    }

}