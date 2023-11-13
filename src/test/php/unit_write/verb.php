<?php

/*

    test/php/unit_write/test_verb.php - write test verbs to the database and check the results
    ---------------------------------


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

namespace test;

use api\verb\verb as verb_api;
use api\word\word as word_api;
use cfg\foaf_direction;
use cfg\verb;
use cfg\word_select_direction;

class verb_test
{

    function run(test_cleanup $t): void
    {

        global $verbs;

        $t->header('Test the verb class (classes/verb.php)');

        // check the loading of the "is a" verb
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->load_by_id($verbs->id(verb::IS));
        $t->assert('verb->load ', $vrb->name(), verb_api::TN_IS);

        // test the creation of a new verb
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->set_name(verb_api::TN_ADD);
        $result = $vrb->save();
        $t->assert('verb->add', $result);

        // ... test if adding the verb is part of the change log
        $result = $t->log_last_named();
        $t->assert('verb->add log', $result, 'zukunft.com system test added ');

        // test verb not yet used can be deleted
        $vrb = new verb;
        $vrb->set_user($t->usr1);
        $vrb->load_by_name(verb_api::TN_ADD);
        $result = $vrb->del();
        $t->assert('verb->del ', $result);

        // ... test if deleting the verb is part of the change log
        $result = $t->log_last_named();
        $t->assert('verb->add log', $result, 'zukunft.com system test deleted System Test Verb');

        // TODO add more tests e.g. that a verb name cannot be used for a word any more


        $t->header('Test the verb list class (classes/verb_list.php)');

        // check the loading of the "is a" verb
        $wrd_ZH = $t->load_word(word_api::TN_ZH);
        $vrb_lst = $wrd_ZH->link_types(foaf_direction::UP);
        $t->assert_contains('verb_list->link_types ', $vrb_lst->db_id_list(), [verb_api::TN_IS]);
    }

}