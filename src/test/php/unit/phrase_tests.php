<?php

/*

    test/unit/phrase.php - unit testing of the phrase functions
    --------------------


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

namespace unit;

include_once WEB_PHRASE_PATH . 'phrase.php';
include_once SHARED_TYPES_PATH . 'phrase_type.php';

use api\word\word as word_api;
use cfg\db\sql_creator;
use cfg\phrase\phrase_table;
use cfg\phrase\phrase_table_status;
use cfg\phrase\phrase_type;
use html\word\word as word_dsp;
use html\word\triple as triple_dsp;
use html\phrase\phrase as phrase_dsp;
use cfg\phrase\phrase;
use cfg\db\sql_db;
use cfg\word\word;
use test\test_base;
use test\test_cleanup;
use shared\types\phrase_type as phrase_type_shared;

class phrase_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $sc = new sql_creator();
        $t->name = 'phrase->';
        $t->resource_path = 'db/phrase/';

        $t->header('phrase unit tests');

        $t->subheader('phrase sql setup');
        $phr = $t->phrase();
        $t->assert_sql_view_create($phr);

        $t->subheader('phrase sql read');
        $phr = new phrase($usr);
        $t->assert_sql_by_id($sc, $phr);
        $t->assert_sql_by_name($sc, $phr);

        $t->subheader('phrase type api unit tests');
        $phr = $t->phrase();
        $t->assert_api_json($phr);
        $phr = $t->word_filled()->phrase();
        $t->assert_api_json($phr);
        $phr = $t->word_filled()->phrase();
        $phr->include();
        $t->assert_api($phr, 'phrase_word_full');
        $phr = $t->triple_filled_add()->phrase();
        $phr->include();
        $t->assert_api($phr, 'phrase_triple_full');
        $phr = $t->phrase();
        $t->assert_api($phr, 'phrase_body');

        $t->subheader('phrase html frontend unit tests');
        $phr = $t->word()->phrase();
        $t->assert_api_to_dsp($phr, new phrase_dsp());
        $phr = $t->triple_pi()->phrase();
        $t->assert_api_to_dsp($phr, new phrase_dsp());

        // check the Postgres query syntax
        $wrd_company = new word($usr);
        $wrd_company->set(2, word_api::TN_COMPANY);
        $sql_name = 'phrase_list_related';
        $file_name = $t->resource_path . $sql_name . test_base::FILE_EXT;
        $created_sql = $phr->sql_list($wrd_company);
        $expected_sql = $t->file($file_name);
        $t->assert_sql($t->name . $sql_name, $created_sql, $expected_sql
        );



        $t->header('Unit tests of the phrase type class (src/main/php/model/phrase/phrase_type.php)');

        $t->subheader('phrase type api unit tests');
        global $phr_typ_cac;
        $phr_typ = $phr_typ_cac->get_by_code_id(phrase_type_shared::PERCENT);
        $t->assert_api($phr_typ, 'phrase_type');


        $t->subheader('Combined objects like phrases should not be used for im- or export, so not tests is needed. Instead the single objects like word or triple should be im- and exported');


        $t->header('Unit tests of the dynamic table creation');

        $t->subheader('Phrase table status SQL setup statements');
        $phr_tbl_sta = new phrase_table_status('');
        $t->assert_sql_table_create($phr_tbl_sta);
        $t->assert_sql_index_create($phr_tbl_sta);

        $t->subheader('Phrase table SQL setup statements');
        $phr_tbl = new phrase_table('');
        $t->assert_sql_table_create($phr_tbl);
        $t->assert_sql_index_create($phr_tbl);
        $t->assert_sql_foreign_key_create($phr_tbl);

    }

}