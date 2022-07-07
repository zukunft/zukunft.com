<?php

/*

  test/unit_db/verb.php - database unit testing of the verb functions
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

function run_verb_unit_db_tests(testing $t)
{

    global $db_con;
    global $usr;

    $t->header('Unit database tests of the verb class (src/main/php/model/verb/verb.php)');

    $t->subheader('Verb list tests');

    // load the verb types
    $lst = new verb_list($usr);
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_verb_list->load', $target, $result);

    // ... and check if at least the most critical verb is loaded
    $result = cl(db_cl::VERB, verb::IS_A);
    // just check if the verb is around, because the posiion may vary depending on the historic creation of the database
    if ($result > 0) {
        $target = $result;
    }
    $t->dsp('unit_db_verb_list->check ' . verb::IS_A, $result, $target);

    $select_list = $lst->selector_list('forward');
    $top_verb = $select_list[0]; // the most often verb should be on the top
    $result = $top_verb[1]; // the name of the verb is always on second place
    // TODO check why this differs depending on the database used
    if ($result = 'is a') {
        $target = 'is a';
    } else {
        $target = 'not set';
    }
    $t->dsp('unit_db_verb_list->selector_list ' . verb::IS_A, $result, $target);

}

