<?php

/*

    test/php/unit_read/word_tests.php - database unit testing of the word, triple and phrase functions
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

namespace unit_read;

use api\word\triple as triple_api;
use api\word\word as word_api;
use cfg\phrase;
use cfg\phrase_type;
use cfg\phrase_types;
use cfg\verb;
use cfg\word;
use cfg\word_list;
use test\test_cleanup;

class word_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $phrase_types;

        // init
        $t->name = 'word read->';
        $t->resource_path = 'db/word/';


        $t->header('word db read tests');

        $t->subheader('load');

        $test_name = word_api::TN_READ . ' by name and id';
        $wrd = $t->load_word(word_api::TN_READ);
        $wrd_by_id = new word($t->usr1);
        $wrd_by_id->load_by_id($wrd->id());
        $t->assert($test_name, $wrd_by_id->name(), word_api::TN_READ);
        $t->assert($test_name, $wrd_by_id->description, word_api::TD_READ);

        // TODO load plural, type and view


        $t->subheader('word types tests');

        $test_name = 'load the phrase types';
        $lst = new phrase_types();
        $result = $lst->load($db_con);
        $t->assert_true($test_name, $result);

        $test_name = 'check that at least ' . phrase_type::NORMAL . ' is loaded';
        $result = $phrase_types->id(phrase_type::NORMAL);
        $t->assert($test_name, $result, 1);


        $t->subheader('word API object creation tests');

        $wrd = $t->load_word(word_api::TN_READ, $t->usr1);
        $t->assert_api_obj($wrd);

        $t->subheader('Word frontend tests');

        $test_name = 'get the most useful view for a word';
        $wrd = $t->load_word(word_api::TN_READ, $t->usr1);
        $dsp_id = $wrd->calc_view_id();
        $t->assert($test_name, $dsp_id, 0);


        $t->header('Unit database tests of the word list class (src/main/php/model/word/word_list.php)');
        $t->name = 'word list read db->';


        $t->subheader('Word list load and modification tests');

        // create word objects for testing
        $wrd = new word ($t->usr1);
        $wrd->load_by_name(word_api::TN_READ);
        $wrd_scale = new word ($t->usr1);
        $wrd_scale->load_by_name(word_api::TN_MIO);
        $phr = new phrase ($t->usr1);
        $phr->load_by_name(triple_api::TN_PI_NAME);
        $phr_grp = $t->load_phrase_group(array(triple_api::TN_PI));

        // load a word list by the word id
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $t->assert('load_by_id', $wrd_lst->name(), '"' . word_api::TN_READ . '"');

        // load a word list by the word ids
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id(), $wrd_scale->id()));
        $t->assert('load_by_ids', $wrd_lst->name(), '"' . word_api::TN_READ . '","' . word_api::TN_MIO . '"');

        // load a word list by the word name
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_names(array(word_api::TN_READ));
        $t->assert('load_by_name', $wrd_lst->name(), '"' . word_api::TN_READ . '"');

        // load a word list by the word ids
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_names(array(word_api::TN_READ, word_api::TN_MIO));
        $t->assert('load_by_names', $wrd_lst->name(), '"' . word_api::TN_READ . '","' . word_api::TN_MIO . '"');

        // load a word list by the phrase group
        if ($phr_grp != null) {
            $wrd_lst = new word_list ($t->usr1);
            $wrd_lst->load_by_grp_id($phr_grp->id());
            $t->assert('load_by_group', $wrd_lst->name(), '"' . triple_api::TN_PI . '"');
        }

        // load a word list by type
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_type($phrase_types->id(phrase_type::PERCENT));
        $t->assert('load_by_type', $wrd_lst->name(), '"' . word_api::TN_PCT . '"');

        // load a word list by name pattern
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_like('S');
        $t->assert_contains('load_by_pattern', $wrd_lst->names(),
            array("S", "September", "Share", "Share Price", "SI base unit", "Sv"));

        // add a word to a list by the word id
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $wrd_lst->add_id($wrd_scale->id());
        $t->assert('add_id', $wrd_lst->name(), '"' . word_api::TN_READ . '","' . word_api::TN_MIO . '"');

        // add a word to a list by the word name
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $wrd_lst->add_name(word_api::TN_MIO);
        $t->assert('add_id', $wrd_lst->name(), '"' . word_api::TN_READ . '","' . word_api::TN_MIO . '"');


        $t->subheader('FOAF read tests');

        // TODO review all tests base on this one
        $test_name = 'The list von cities must contain at least Zurich, Bern ans Geneva';
        $city_lst = $t->word_city()->are();
        $t->assert_contains($test_name, $t->phrase_list_cities()->wrd_lst_all()->names(), $city_lst->names());


        $t->header('Unit database tests of the triple class (src/main/php/model/word/triple.php)');
        $t->name = 'triple read db->';

        $t->subheader('Frontend API tests');

        $trp = $t->load_triple(triple_api::TN_PI, verb::IS, word_api::TN_READ);
        $t->assert_api_obj($trp);
    }

}

