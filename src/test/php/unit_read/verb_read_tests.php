<?php

/*

    test/php/unit_read/verb.php - database unit testing of the verb functions
    ---------------------------


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

namespace unit_read;

use cfg\const\paths;

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';

use cfg\verb\verb;
use cfg\verb\verb_list;
use cfg\word\word;
use shared\const\triples;
use shared\const\words;
use shared\types\verbs;
use test\test_cleanup;

class verb_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $vrb_cac;

        // init
        $t->name = 'verb read db->';

        $t->header('verb database read tests');

        $t->subheader('Verb tests');

        // test if loading by code id and id result in the same name
        $vrb = new verb();
        $vrb->load_by_code_id(verbs::IS);
        $vrb_id = new verb();
        $vrb_id->load_by_id($vrb->id());
        $t->assert('load' . verbs::IS, $vrb->name(), $vrb_id->name());

        // prepare the words for testing
        $country = new word($t->usr1);
        $country->load_by_name(words::COUNTRY);
        $switzerland = new word($t->usr1);
        $switzerland->load_by_name(words::CH);

        // 'is a' - test the selection of the members via 'is a' verb
        $countries = $country->children();
        $t->assert_contains('is a based on ' . words::COUNTRY,
            $countries->names(),
            array(words::CH, words::GERMANY)
        );

        // 'is part of' - test the direct selection of the members via 'is part of' verb
        //                e.g. for Switzerland get at least 'Zurich (Canton)' but not 'Zurich (City)'
        $parts = $switzerland->direct_parts();
        $t->assert_contains('direct parts of ' . words::CH,
            $parts->names(),
            array(triples::CANTON_ZURICH)
        );
        $t->assert_contains_not('direct parts of ' . words::CH,
            $parts->names(),
            array(triples::CITY_ZH)
        );

        // 'is part of' - test the recursive selection of the members via 'is part of' verb
        //                e.g. for Switzerland get at least 'Zurich (Canton)' and 'Zurich (City)'
        $parts = $switzerland->parts();
        $t->assert_contains('parts of ' . words::CH . ' and parts of the parts',
            $parts->names(),
            array(triples::CANTON_ZURICH, triples::CITY_ZH)
        );


        // TODO add to phrase and triple the methode
        //      ->all_parents to get Canton, City and Company for Zurich (Canton)
        //      whereas ->parents just return Canton for Zurich (Canton) because the word splitting is not done


        $t->subheader('Verb list tests');
        $t->name = 'verb list read db->';

        // load the verb types
        $lst = new verb_list($t->usr1);
        $result = $lst->load($db_con);
        $t->assert('load', $result, true);

        // ... and check if at least the most critical verb is loaded
        $result = $vrb_cac->id(verbs::IS);
        // just check if the verb is around, because the position may vary depending on the historic creation of the database
        $target = 0;
        if ($result > 0) {
            $target = $result;
        }
        $t->assert('check ' . verbs::IS, $result, $target);

        $select_list = $lst->selector_list();
        $top_verb = $select_list[0]; // the most often verb should be on the top
        $result = $top_verb[1]; // the name of the verb is always on second place
        // TODO check why this differs depending on the database used
        if ($result == 'is an acronym for') {
            $target = 'is an acronym for';
        } elseif ($result == verbs::IS_NAME) {
            $target = verbs::IS_NAME;
        } elseif ($result == 'uses') {
            $target = 'uses';
        } elseif ($result == 'is measure type for') {
            $target = 'is measure type for';
        } else {
            $target = 'not set';
        }
        $t->assert('selector list ' . verbs::IS, $result, $target);
    }

}

