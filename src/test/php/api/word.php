<?php

/*

    test/php/api/word.php - TESTing of the API U
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

// --------------------------------------
// start testing the system functionality 
// --------------------------------------

function run_api_test(testing $t): void
{
    global $usr;

    $t->assert_api_get(word::class);
    $t->assert_api_get(verb::class);
    $t->assert_api_get(triple::class);
    $t->assert_api_get(value::class);
    $t->assert_api_get(formula::class);
    $t->assert_api_get(view::class);
    $t->assert_api_get(view_cmp::class);

    $t->assert_api_get_list(phrase_list::class);
    $t->assert_api_get_list(term_list::class, [1,-1]);
    // $t->assert_rest(new word($usr, word::TN_READ));

}
