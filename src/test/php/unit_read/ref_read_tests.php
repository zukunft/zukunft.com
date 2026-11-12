<?php

/*

    test/php/unit_read/ref_read_tests.php - database unit testing of external references
    -------------------------------------


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
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;

include_once paths::MODEL_REF . 'ref_list.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'ref_types.php';
include_once paths::SHARED_CONST . 'refs.php';

use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class ref_read_tests
{

    function run(test_cleanup $t): void
    {
        global $sys;
        global $db_con;
        $msg = new user_message();

        // init
        $lib = new library();
        $t->name = 'ref db read->';

        // start the test section (ts)
        $ts = 'db read reference ';
        $t->header($ts);

        $t->subheader($ts . 'types');

        // load the ref types
        $lst = new ref_type_list();
        $result = $lst->load($db_con, $msg);
        $t->assert('load_types', $result, true);

        // ... and check if at least the most critical is loaded
        // TODO Prio 2 check
        $result = $sys->typ_lst->phr_typ->id(phrase_type_shared::NORMAL);
        $t->assert('check ' . phrase_type_shared::NORMAL, $result, 1);

        $t->subheader($ts . 'load by external key');

        // the external key is the name of a reference, so the generic cleanup by name finds it
        $test_name = 'the pi reference is found by its external key';
        $ref = new ref($t->usr1);
        $t->assert($test_name, $ref->load_by_name(refs::PI_KEY, $msg), refs::PI_ID);
        $test_name = 'an unknown external key finds no reference';
        $ref = new ref($t->usr1);
        $t->assert($test_name, $ref->load_by_name(refs::SYSTEM_TEST_ADD, $msg), 0);

        // the wikidata import uses the phrases of the pod with the wikidata reference of the item or property
        $t->subheader($ts . 'load by type and keys');
        $wd_type_id = $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA);
        $test_name = 'only the known one of two wikidata keys finds a reference';
        $ref_lst = new ref_list($t->usr1);
        $ref_lst->load_by_type_and_keys($wd_type_id, [refs::ZURICH_CITY_WIKIDATA_KEY, refs::CHANGE_NEW_KEY], $msg);
        $t->assert($test_name, $ref_lst->count(), 1);

        $test_name = '... the reference of the triple Zurich (city)';
        $wd_ref = $ref_lst->lst()[0] ?? null;
        $phr_id = $wd_ref?->phrase_id() ?? 0;
        $t->assert($test_name, $phr_id, triple_names::CITY_ZH_ID * -1);

        $test_name = 'an unknown wikidata key finds no reference';
        $ref_lst = new ref_list($t->usr1);
        $found = $ref_lst->load_by_type_and_keys($wd_type_id, [refs::CHANGE_NEW_KEY], $msg);
        $t->assert_false($test_name, $found);

        $test_name = 'the wikidata property of the population is the reference of the word population';
        $wd_prp_type_id = $sys->typ_lst->ref_typ->id(ref_types::WIKIDATA_PROPERTY);
        $ref_lst = new ref_list($t->usr1);
        $ref_lst->load_by_type_and_keys($wd_prp_type_id, [refs::POPULATION_WIKIDATA_PROPERTY_KEY], $msg);
        $prp_ref = $ref_lst->lst()[0] ?? null;
        $phr_id = $prp_ref?->phrase_id() ?? 0;
        $t->assert($test_name, $phr_id, word_names::POPULATION_ID);

        $test_name = '... but not a reference of the type wikidata, whose key is an item';
        $ref_lst = new ref_list($t->usr1);
        $found = $ref_lst->load_by_type_and_keys($wd_type_id, [refs::POPULATION_WIKIDATA_PROPERTY_KEY], $msg);
        $t->assert_false($test_name, $found);
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        $t->subheader($ts . 'apis');

        $ref = new ref($t->usr1);
        $ref->load_by_id(refs::PI_ID, $msg);
        $t->assert_api($ref);

    }

}

