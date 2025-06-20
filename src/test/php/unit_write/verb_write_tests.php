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

namespace unit_write;

include_once SHARED_TYPES_PATH . 'verbs.php';

use cfg\verb\verb;
use shared\enum\foaf_direction;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class verb_write_tests
{

    function run(test_cleanup $t): void
    {

        global $vrb_cac;

        $t->header('verb database write tests');

        // check the loading of the "is a" verb
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->load_by_id($vrb_cac->id(verbs::IS));
        $t->assert('verb->load ', $vrb->name(), verbs::IS_NAME);

        // test the creation of a new verb
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->set_name(verbs::TEST_ADD_NAME);
        $result = $vrb->save()->get_last_message();
        $t->assert('verb->add', $result);

        // ... test if adding the verb is part of the change log
        $result = $t->log_last_by_user();
        $t->assert('verb->add log', $result, 'zukunft.com system test added "System Test Verb"');

        // test verb not yet used can be deleted
        $vrb = new verb;
        $vrb->load_by_name(verbs::TEST_ADD_NAME);
        // TODO this setting of the user should actually not be needed
        $vrb->set_user($t->usr1);
        $result = $vrb->del();
        $t->assert('verb->del ', $result);

        // ... test if deleting the verb is part of the change log
        $result = $t->log_last_by_user();
        $t->assert('verb->add log', $result, 'zukunft.com system test deleted "System Test Verb"');

        // TODO add more tests e.g. that a verb name cannot be used for a word any more


        $t->header('verb list write tests');

        // check the loading of the "is a" verb
        $wrd_ZH = $t->load_word(words::ZH);
        $vrb_lst = $wrd_ZH->link_types(foaf_direction::UP);
        $t->assert_contains('verb_list->link_types ', $vrb_lst->db_id_list(), [verbs::IS_NAME]);
    }

}