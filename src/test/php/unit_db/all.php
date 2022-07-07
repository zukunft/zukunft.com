<?php

/*

  all.php - run all unit database read only tests in a useful order
  -----------------

  the zukunft.com unit tests should test all class methods, that can be tested without writing to the database


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

function init_unit_db_tests(testing $t)
{

    // add the database rows for read testing
    $t->test_word_link(
        word_link::TN_READ, verb::IS_A, word::TN_READ,
        word_link::TN_READ_NAME, word_link::TN_READ_NAME
    );
    $phr_grp = $t->add_phrase_group(array(word_link::TN_READ_NAME),phrase_group::TN_READ);
    $t->test_value_by_phr_grp($phr_grp, value::TV_READ);

}

function run_unit_db_tests(testing $t)
{
    $t->header('Start the zukunft.com unit database read only tests');

    // do the database unit tests
    run_system_unit_db_tests($t);
    run_sql_db_unit_db_tests($t);
    run_formula_unit_db_tests($t);
    run_protection_unit_db_tests($t);
    run_ref_unit_db_tests($t);
    run_share_unit_db_tests($t);
    run_user_unit_db_tests($t);
    run_verb_unit_db_tests($t);
    run_view_unit_db_tests($t);
    run_word_unit_db_tests($t);
    run_value_unit_db_tests($t);

    run_value_unit_db_tests($t);

}