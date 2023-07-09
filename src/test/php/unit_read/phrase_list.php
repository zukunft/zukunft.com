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

namespace test;

use api\word_api;
use api\triple_api;
use api\phrase_api;
use cfg\config;
use cfg\phr_ids;
use cfg\phrase_list;
use cfg\phrase_type;
use cfg\phrase;
use cfg\word;

class phrase_list_unit_db_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t->header('Phrase list unit database tests to test src/main/php/model/phrase/phrase_list.php');
        $t->name = 'phrase list_read db->';
        $t->resource_path = 'db/phrase/';

        $t->subheader('Load phrases');
        $test_name = 'api message of phrases list';
        $lst = new phrase_list($t->usr1);
        $id_lst = [1, 2, 3, -1, -2];
        $lst->load_names_by_ids((new phr_ids($id_lst)));
        $result = $lst->api_obj();
        $t->assert_contains($test_name, array_keys($result->db_id_list()), $id_lst);
        $result = json_encode($result);
        $t->assert_text_contains($test_name, $result, '1');


        $t->subheader('Get related phrases');

        // direct children
        $country = new phrase($t->usr1, word_api::TN_COUNTRY);
        $country_lst = $country->direct_children();
        $switzerland = new phrase($t->usr1, word_api::TN_CH);
        $test_name = 'Switzerland is a country';
        $t->assert_contains($test_name, $country_lst->names(), array($switzerland->name()));
        $zurich = new phrase($t->usr1, word_api::TN_ZH);
        $test_name = 'Zurich is a country (even if it is part of a country)';
        $t->assert_contains_not($test_name, $country_lst->names(), array($zurich->name()));
        $test_name = 'The word country is not part of the country list';
        $t->assert_contains_not($test_name, $country_lst->names(), array($country->name()));

        // all children
        $sys_cfg_root_phr = new phrase($t->usr1, word::SYSTEM_CONFIG);
        $sys_cfg_phr_lst = $sys_cfg_root_phr->all_related();
        $auto_years = new phrase($t->usr1, config::YEARS_AUTO_CREATE_DSP);
        $test_name = 'The default number of forecast years is a system configuration parameter';
        $t->assert_contains($test_name, $sys_cfg_phr_lst->names(), array($auto_years->name()));

    }

}

