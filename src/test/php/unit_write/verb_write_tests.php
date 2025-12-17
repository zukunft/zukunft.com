<?php

/*

    test/php/unit_write/verb_tests.php - write test verbs to the database and check the results
    ----------------------------------


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

include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\shared\enum\foaf_direction;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class verb_write_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;

        // init
        $t_vrb = new test_verbs($t);
        $t_db = new test_db_load($t);
        $usr_msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write verb ';
        $t->header($ts);
        $t_vrb->cleanup($ts);

        $test_name = 'check the loading of the "' . verbs::IS . ')" verb';
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->load_by_id($sys->typ_lst->vrb->id(verbs::IS));
        $t->assert($test_name, $vrb->name(), verbs::IS_NAME);

        $test_name = 'test the creation of a new verb with name "' . verbs::TEST_ADD_NAME . ')" verb';
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->set_name(verbs::TEST_ADD_NAME);
        $t->assert_true($test_name, $vrb->save($usr_msg));

        $test_name = '... test if adding the verb is part of the change log';
        $result = $t->log_last_by_user();
        $t->assert($test_name, $result, 'zukunft.com system test added "System Test Verb"');

        $test_name = 'test verb not yet used can be deleted';
        $vrb = new verb;
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        // TODO this setting of the user should actually not be needed
        $vrb->set_user($t->usr1);
        $t->assert_true($test_name, $vrb->del($usr_msg));

        $test_name = '... test if deleting the verb is part of the change log';
        $result = $t->log_last_by_user();
        $t->assert($test_name, $result, 'zukunft.com system test deleted "System Test Verb"');

        // TODO add more tests e.g. that a verb name cannot be used for a word any more


        $t->subheader($ts . 'list');

        // check the loading of the "is a" verb
        $wrd_ZH = $t_db->load_word(words::ZH);
        $vrb_lst = $wrd_ZH->link_types(foaf_direction::UP);
        $t->assert_contains('verb_list->link_types ', $vrb_lst->db_id_list(), [verbs::IS_NAME]);

        $t_vrb->cleanup($ts);

    }

}