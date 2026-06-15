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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_CONST . 'def.php';
include_once paths::SHARED_CONST . 'formulas.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phr_ids;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_list_read_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $t_db = new test_db_load($t);
        $t->name = 'phrase list_read db->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'db read phrase list ';
        $t->header($ts);

        $t->subheader($ts . 'load');

        $test_name = 'loading phrase names with pattern return the expected word';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(word_names::MATH, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), word_names::MATH);
        $test_name = 'loading phrase names with pattern return the expected triple';
        $lst = new phrase_list($t->usr1);
        $pattern = substr(triple_names::MATH_CONST, 0, -1);
        $lst->load_names($pattern);
        $t->assert_contains($test_name, $lst->names(), triple_names::MATH_CONST);
        $test_name = 'formula names are not included in the normal phrase list';
        $lst = new phrase_list($t->usr1);
        $lst->load_names(formulas::SCALE_TO_SEC);
        // TODO Prio 1 activate
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
        $t->assert_contains($test_name, $lst->names(), words::CH);


        $t->subheader($ts . 'get related');

        // direct children
        $test_name = 'Switzerland is a country';
        $country = new phrase($t->usr1);
        $country->load_by_name(words::COUNTRY);
        $country_lst = $country->direct_children();
        $t->assert_contains($test_name, $country_lst->names(), words::CH);
        $test_name = 'Zurich is a country (even if it is part of a country)';
        $zurich = new phrase($t->usr1);
        $zurich->load_by_name(word_names::ZH);
        $t->assert_contains_not($test_name, $country_lst->names(), word_names::ZH);
        $test_name = 'The word country is not part of the country list';
        $t->assert_contains_not($test_name, $country_lst->names(), words::COUNTRY);

        // all children
        $test_name = 'The default number of forecast years is a system configuration parameter';
        global $cfg;
        $auto_years = $cfg->get_by([triples::AUTOMATIC_CREATE, words::YEAR], def::FALLBACK_RETRY);
        $t->assert_greater($test_name, 0, $auto_years);

        // Canton is related to Switzerland and Zurich
        $phr_canton = $t_db->load_phrase(word_names::CANTON);
        $phr_lst = $phr_canton->all_related();
        $test_name = 'The word Canton is related to Switzerland and Zurich';
        // TODO ABB is not expected to be related even if it is related via zurich and company
        //      but Switzerland is expected to be related
        //$t->assert_contains($test_name, $phr_lst->names(), array(words::TN_ZH, words::TN_CH));

    }

}

