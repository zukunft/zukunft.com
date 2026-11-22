<?php

/*

    test/php/unit_write/xbrl_write_tests.php - test the database import of a converted XBRL fileset
    ----------------------------------------

    unpacks the XBRL fileset zip, creates the import json in the fileset folder,
    imports the json again into the database that the development reset has loaded it into
    (all_unit_write_tests::run_db_recreate) and checks that nothing changes and that the values can be loaded


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

    Copyright (c) 1995-2026 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit_write;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_CONST . 'files.php';
include_once paths::MODEL_IMPORT . 'import_convert_xbrl.php';
include_once paths::MODEL_IMPORT . 'import_file.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::CONST . 'triple_names.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::UTILS . 'test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\import\import_convert_xbrl;
use Zukunft\ZukunftCom\main\php\cfg\import\import_file;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class xbrl_write_tests
{

    // the seed declaration of "exchange rate" (economics.json), which the converted ABB fileset must repeat
    const array EXCHANGE_RATE_DECLARATION = [
        json_fields::NAME => triple_names::EXCHANGE_RATE,
        json_fields::EX_FROM => word_names::RATE,
        json_fields::EX_VERB => verbs::OF_NAME,
        json_fields::EX_TO => word_names::EXCHANGE,
    ];

    function run(test_cleanup $t): void
    {

        // init
        $msg = new user_message($t->usr1);

        // start the test section (ts)
        $ts = 'db write xbrl ';
        $t->header($ts);

        // unpack the fileset and create the import json in the fileset folder
        $test_name = 'create the import json from the XBRL fileset';
        $conv_xbrl = new import_convert_xbrl;
        $folder = $conv_xbrl->unzip(
            test_files::IMPORT_XBRL_ABB_2013_ZIP,
            test_paths::IMPORT_XBRL,
            ''
        );
        $json_path = $conv_xbrl->convert_folder_to_file($folder, $conv_xbrl->instance_file_name('2013'), $msg, test_base::TEST_TIMESTAMP);
        $t->assert($test_name, $json_path, test_files::IMPORT_XBRL_ABB_2013);
        $json_text = file_get_contents($json_path);
        $json_array = json_decode($json_text, true);

        // every pod imports the copy of the converted json in the main resources (import_file::import_xbrl_data),
        // so a converter change must also change this copy, which is then loaded by the next reset
        $test_name = 'the XBRL json of the db setup is the converted fileset';
        $t->assert_file($test_name, $json_text, files::XBRL_ABB_2013_FILE);

        // the ABB 2013 fileset has no conflicting facts, so that a conflict e.g. after a change of the
        // concept decomposition shows up; the facts in two currencies are no conflict (see facts_json)
        $test_name = 'the convert reports no conflicting facts of the fileset';
        $t->assert($test_name, $msg->all_message_text(), '');

        // a concept that the base setup defines as a triple keeps the link of the seed, because the import cannot change it
        $test_name = 'the import json declares "' . triple_names::EXCHANGE_RATE . '" with the link of the seed';
        $trp_by_name = array_column($json_array[json_fields::TRIPLES] ?? [], null, json_fields::NAME);
        $exchange_rate = $trp_by_name[triple_names::EXCHANGE_RATE] ?? [];
        $t->assert($test_name, $exchange_rate, self::EXCHANGE_RATE_DECLARATION);

        // negative: the triple "equity method", which the seed triple "equity method investments" links to, is no word
        $test_name = '... and "' . triple_names::EQUITY_METHOD . '" is not declared as a word';
        $wrd_names = array_column($json_array[json_fields::WORDS] ?? [], json_fields::NAME);
        $t->assert_contains_not($test_name, $wrd_names, triple_names::EQUITY_METHOD);

        // a link that two concepts create keeps the name that the other triples use
        $test_name = 'the import json declares "' . triple_names::CASH_CASH . '"';
        $trp_names = array_keys($trp_by_name);
        $t->assert_contains($test_name, $trp_names, triple_names::CASH_CASH);

        // the database setup has imported the json already (import_file::import_xbrl_data),
        // so a second import as the same system user and without overwriting a filled field reports
        // any difference between the converted json and the database e.g. after a converter change
        // without a new reset; nothing is added, so nothing needs to be removed after the test
        $test_name = 'the XBRL fileset json is imported again without changes';
        $imf = new import_file();
        $imp_msg = new user_message(user::system());
        $imf->json_file($json_path, user::system(), $imp_msg, false, false, true);
        $t->assert_msg($test_name, $imp_msg, $t::TIMEOUT_LIMIT_IMPORT);

        // check that the imported values can be loaded from the database
        $test_name = 'load the imported total sales value';
        $total_json = end($json_array[json_fields::VALUES]);
        $val = $this->load_value_by_names($t, $total_json[json_fields::WORDS]);
        $t->assert($test_name, $val?->number(), floatval($total_json[json_fields::NUMBER]));
    }

    /**
     * load a value from the database by the phrase names of the import json
     *
     * @param test_cleanup $t the test environment with the test user
     * @param array $names the phrase names that select the value
     * @return value|null the loaded value or null if the names select no value in the database
     */
    private function load_value_by_names(test_cleanup $t, array $names): ?value
    {
        $msg = new user_message();
        $phr_lst = new phrase_list($t->usr1);
        $phr_lst->load_by_names(array_unique($names), $msg);
        $val = new value($t->usr1);
        // the value keeps the group id also if nothing is found, so only the load result tells
        if ($val->load_by_grp($phr_lst->get_grp_id(), $msg)) {
            return $val;
        } else {
            return null;
        }
    }

}
