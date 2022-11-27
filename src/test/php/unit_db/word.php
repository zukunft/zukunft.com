<?php

/*

  test/unit_db/word.php - database unit testing of the word, triple and phrase functions
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

use cfg\phrase_type;

function run_word_unit_db_tests(testing $t)
{

    global $db_con;
    global $usr;

    // init
    $t->header('Unit database tests of the word class (src/main/php/model/word/word.php)');
    $t->name = 'word->';
    $t->resource_path = 'db/word/';

    $t->subheader('Word types tests');

    // load the word types
    $lst = new word_type_list();
    $result = $lst->load($db_con);
    $target = true;
    $t->dsp('unit_db_word->load_types', $target, $result);

    // ... and check if at least the most critical is loaded
    $result = cl(db_cl::PHRASE_TYPE, phrase_type::NORMAL);
    $target = 1;
    $t->dsp('unit_db_word->check ' . phrase_type::NORMAL, $result, $target);

    $t->subheader('Frontend API tests');

    $wrd = $t->load_word(word::TN_READ);
    $t->assert_api_exp($wrd);


    $t->header('Unit database tests of the word list class (src/main/php/model/word/word_list.php)');
    $t->name = 'word_list->';

    $t->subheader('Word list load and modification tests');

    // create word objects for testing
    $wrd = new word($usr);
    $wrd->load_by_name(word::TN_READ, word::class);
    $wrd_scale = new word($usr);
    $wrd_scale->load_by_name(word::TN_READ_SCALE, word::class);
    $phr = new phrase($usr);
    $phr->load_by_name(triple::TN_READ_NAME);
    $phr_grp = $t->load_phrase_group(array(triple::TN_READ));

    // load a word list by the word id
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_ids(array($wrd->id));
    $t->assert('load_by_id', $wrd_lst->name(), '"' . word::TN_READ . '"');

    // load a word list by the word ids
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_ids(array($wrd->id, $wrd_scale->id));
    $t->assert('load_by_ids', $wrd_lst->name(), '"' . word::TN_READ . '","' . word::TN_READ_SCALE . '"');

    // load a word list by the word name
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_READ));
    $t->assert('load_by_name', $wrd_lst->name(), '"' . word::TN_READ . '"');

    // load a word list by the word ids
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_names(array(word::TN_READ, word::TN_READ_SCALE));
    $t->assert('load_by_names', $wrd_lst->name(), '"' . word::TN_READ . '","' . word::TN_READ_SCALE . '"');

    // load a word list by the phrase group
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_grp_id($phr_grp->id);
    $t->assert('load_by_group', $wrd_lst->name(), '"' . triple::TN_READ . '"');

    // load a word list by type
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_type(cl(db_cl::PHRASE_TYPE, phrase_type::PERCENT));
    $t->assert('load_by_type', $wrd_lst->name(), '"' . word::TN_READ_PERCENT . '"');

    // load a word list by name pattern
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_pattern('S');
    $t->assert_contains('load_by_pattern', $wrd_lst->names(), array("S","September","Share","Share Price","SI base unit","Sv"));

    // add a word to a list by the word id
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_ids(array($wrd->id));
    $wrd_lst->add_id($wrd_scale->id);
    $t->assert('add_id', $wrd_lst->name(), '"' . word::TN_READ . '","' . word::TN_READ_SCALE . '"');

    // add a word to a list by the word name
    $wrd_lst = new word_list($usr);
    $wrd_lst->load_by_ids(array($wrd->id));
    $wrd_lst->add_name(word::TN_READ_SCALE);
    $t->assert('add_id', $wrd_lst->name(), '"' . word::TN_READ . '","' . word::TN_READ_SCALE . '"');


    $t->header('Unit database tests of the word class (src/main/php/model/word/triple.php)');
    $t->name = 'triple->';

    $t->subheader('Frontend API tests');

    $trp = $t->load_triple(triple::TN_READ, verb::IS_A, word::TN_READ);
    $t->assert_api_exp($trp);

}

