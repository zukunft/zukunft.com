<?php

/*

  test/unit_db/word.php - database unit testing of the word, word_link and phrase functions
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
    along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.

    To contact the authors write to:
    Timon Zielonka <timon@zukunft.com>

    Copyright (c) 1995-2021 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

function run_word_unit_db_tests(testing $t)
{

    global $db_con;

    $t->header('Unit database tests of the word class (src/main/php/model/word/word.php)');

    $t->subheader('Word types tests');

    // load the word types
    $lst = new word_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_word->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::WORD_TYPE, word_type_list::DBL_NORMAL);
    $target = 1;
    $t->dsp('unit_db_word->check ' . word_type_list::DBL_NORMAL, $result, $target);

    $t->subheader('Frontend API tests');

    $wrd = $t->load_word(word::TN_READ);
    $t->assert_api($wrd);

    $t->header('Unit database tests of the word class (src/main/php/model/word/word_link.php)');

    $t->subheader('Frontend API tests');

    $trp = $t->load_word_link(word_link::TN_READ, verb::IS_A, word::TN_READ);
    $t->assert_api($trp);

}

