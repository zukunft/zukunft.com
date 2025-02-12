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

include_once SHARED_TYPES_PATH . 'phrase_type.php';
include_once SHARED_TYPES_PATH . 'verbs.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\phrase\phrase;
use cfg\phrase\phrase_types;
use cfg\word\word;
use cfg\word\word_list;
use shared\const\triples;
use shared\const\words;
use shared\types\phrase_type as phrase_type_shared;
use test\test_cleanup;

class word_read_tests
{

    function run(test_cleanup $t): void
    {

        global $db_con;
        global $phr_typ_cac;

        // init
        $t->name = 'word read->';
        $t->resource_path = 'db/word/';


        $t->header('word db read tests');

        $t->subheader('word load');
        $wrd = new word($t->usr1);
        $t->assert_load($wrd, words::MATH);
        $t->assert('word description ', $wrd->description, words::MATH_COM);

        // TODO load plural, type and view


        $t->subheader('word types tests');

        $test_name = 'load the phrase types';
        $lst = new phrase_types();
        $result = $lst->load($db_con);
        $t->assert_true($test_name, $result);

        $test_name = 'check that at least ' . phrase_type_shared::NORMAL . ' is loaded';
        $result = $phr_typ_cac->id(phrase_type_shared::NORMAL);
        $t->assert($test_name, $result, 1);


        $t->subheader('word API object creation tests');

        $wrd = $t->load_word(words::MATH, $t->usr1);
        $t->assert_export_reload($wrd);

        $t->subheader('Word frontend tests');

        $test_name = 'get the most useful view for a word';
        $wrd = $t->load_word(words::MATH, $t->usr1);
        $dsp_id = $wrd->calc_view_id();
        $t->assert($test_name, $dsp_id, 0);


        // TODO move to the other word list tests
        $t->header('word list database unit tests');
        $t->name = 'word list read db->';


        $t->subheader('Word list load and modification tests');

        // create word objects for testing
        $wrd = new word ($t->usr1);
        $wrd->load_by_name(words::MATH);
        $wrd_scale = new word ($t->usr1);
        $wrd_scale->load_by_name(words::MIO);
        $phr = new phrase ($t->usr1);
        $phr->load_by_name(triples::PI_NAME);
        $phr_grp = $t->load_phrase_group(array(triples::PI));

        // load a word list by the word id
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $t->assert('load_by_id', $wrd_lst->name(), '"' . words::MATH . '"');

        // load a word list by the word ids
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id(), $wrd_scale->id()));
        $t->assert('load_by_ids', $wrd_lst->name(), '"' . words::MATH . '","' . words::MIO . '"');

        // load a word list by the word name
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_names(array(words::MATH));
        $t->assert('load_by_name', $wrd_lst->name(), '"' . words::MATH . '"');

        // load a word list by the word ids
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_names(array(words::MATH, words::MIO));
        $t->assert('load_by_names', $wrd_lst->name(), '"' . words::MATH . '","' . words::MIO . '"');

        // load a word list by the phrase group
        if ($phr_grp != null) {
            $wrd_lst = new word_list ($t->usr1);
            $wrd_lst->load_by_grp_id($phr_grp->id());
            $t->assert('load_by_group', $wrd_lst->name(), '"' . triples::PI . '"');
        }

        // load a word list by type
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_type($phr_typ_cac->id(phrase_type_shared::PERCENT));
        $t->assert('load_by_type', $wrd_lst->name(), '"' . words::PCT . '"');

        // load a word list by name pattern
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_like('S');
        $t->assert_contains('load_by_pattern', $wrd_lst->names(),
            array("S", "September", "Share", "Share Price", "SI base unit", "Sv"));

        // add a word to a list by the word id
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $wrd_lst->add_id($wrd_scale->id());
        $t->assert('add_id', $wrd_lst->name(), '"' . words::MATH . '","' . words::MIO . '"');

        // add a word to a list by the word name
        $wrd_lst = new word_list ($t->usr1);
        $wrd_lst->load_by_ids(array($wrd->id()));
        $wrd_lst->add_name(words::MIO);
        $t->assert('add_id', $wrd_lst->name(), '"' . words::MATH . '","' . words::MIO . '"');


        $t->subheader('FOAF read tests');

        // TODO review all tests base on this one
        $test_name = 'The list von cities must contain at least Zurich, Bern ans Geneva';
        $foaf_lst = $t->word_city()->are()->names();
        $fixed_lst = $t->phrase_list_cities()->wrd_lst_all()->names();
        $t->assert_contains($test_name, $foaf_lst, $fixed_lst);


        $t->header('triple database read tests');
        $t->name = 'triple read db->';

        $t->subheader('triple export tests');

        $trp = $t->triple_pi();
        $t->assert_export_reload($trp);
    }

}

