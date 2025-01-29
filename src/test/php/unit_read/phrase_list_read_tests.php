<?php

/*

    test/php/unit_read/phrase_list.php - database unit testing of the phrase list functions
    ----------------------------------


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

include_once SERVICE_PATH . 'config.php';
include_once SHARED_CONST_PATH . 'formulas.php';
include_once SHARED_CONST_PATH . 'triples.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\config;
use cfg\phrase\phr_ids;
use cfg\phrase\phrase;
use cfg\phrase\phrase_list;
use shared\const\formulas;
use shared\const\triples;
use shared\const\words;
use test\test_cleanup;

class phrase_list_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->header('Phrase list unit database tests to test src/main/php/model/phrase/phrase_list.php');
        $t->name = 'phrase list_read db->';
        $t->resource_path = 'db/phrase/';

        $t->subheader('Load phrases');

        $test_name = 'loading phrase names with pattern return the expected word';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(words::MATH, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), words::MATH);
        $test_name = 'loading phrase names with pattern return the expected triple';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(triples::MATH_CONST, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), triples::MATH_CONST);
        $test_name = 'formula names are not included in the normal phrase list';
        $lst = new phrase_list($t->usr1);
        $lst->load_names(formulas::SCALE_TO_SEC);
        // TODO activate Prio 1
        //$t->assert_contains_not($test_name, $lst->names(), formulas::TN_READ);
        $test_name = 'api message of phrases list';
        $lst = new phrase_list($t->usr1);
        $id_lst = [1, 2, 3, -1, -2];
        $lst->load_names_by_ids((new phr_ids($id_lst)));
        $result = $lst->obj_id_lst();
        $t->assert_contains($test_name, $result, $id_lst);
        $result = json_encode($result);
        $t->assert_text_contains($test_name, $result, '1');
        $test_name = 'Switzerland is part of the phrase list staring with S';
        $switzerland = new phrase($t->usr1);
        $switzerland->load_by_name(words::CH);
        $lst->load_like('S');
        $t->assert_contains($test_name, $lst->names(), $switzerland->name());


        $t->subheader('Get related phrases');

        // direct children
        $test_name = 'Switzerland is a country';
        $country = new phrase($t->usr1);
        $country->load_by_name(words::COUNTRY);
        $country_lst = $country->direct_children();
        $t->assert_contains($test_name, $country_lst->names(), $switzerland->name());
        $test_name = 'Zurich is a country (even if it is part of a country)';
        $zurich = new phrase($t->usr1);
        $zurich->load_by_name(words::ZH);
        $t->assert_contains_not($test_name, $country_lst->names(), $zurich->name());
        $test_name = 'The word country is not part of the country list';
        $t->assert_contains_not($test_name, $country_lst->names(), $country->name());

        // all children
        $test_name = 'The default number of forecast years is a system configuration parameter';
        $sys_cfg_root_phr = new phrase($t->usr1);
        $sys_cfg_root_phr->load_by_name(triples::SYSTEM_CONFIG);
        $sys_cfg_phr_lst = $sys_cfg_root_phr->all_children();
        $auto_years = new phrase($t->usr1);
        $auto_years->load_by_name(config::YEARS_AUTO_CREATE_DSP);
        $t->assert_contains($test_name, $sys_cfg_phr_lst->names(), $auto_years->name());

        // Canton is related to Switzerland and Zurich
        $phr_canton = $t->load_phrase(words::CANTON);
        $phr_lst = $phr_canton->all_related();
        $test_name = 'The word Canton is related to Switzerland and Zurich';
        // TODO ABB is not expected to be related even if it is related via zurich and company
        //      but Switzerland is expected to be related
        //$t->assert_contains($test_name, $phr_lst->names(), array(words::TN_ZH, words::TN_CH));

    }

}

