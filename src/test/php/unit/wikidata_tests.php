<?php

/*

    test/unit/wikidata_tests.php - unit tests of the wikidata data conversion
    ----------------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_USER . 'user_message.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_tests
{

    // the message parts of a dummy unit and criterion without name (test_files::IMPORT_WIKIDATA_Q0)
    const string UNIT_WITHOUT_NAME = 'unit "Q0"';
    const string CRITERION_WITHOUT_NAME = 'criterion "Q0"';

    /**
     * the offline tests of the wikidata converter with hand made wikidata answers,
     * so that the result does not depend on the live data that wikidata_write_tests reads
     *
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'wikidata ';
        $t->header($ts);

        $t->subheader($ts . 'convert');
        $imp = new import_wikidata();
        $msg = new user_message();

        // the dummy prices are in euro, without a unit, in the unit Q0 that has no name,
        // with the criterion "resident population" and with the criterion Q0 that has no name
        $test_name = 'the wikidata prices of the US dollar are converted to words, a triple and values';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q4917);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_P2284);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q4917), true);
        $result = $imp->convert($entity_json, $property_json, $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'the price in a unit without a name is reported as not imported';
        $msg_txt = $msg->all_message_text();
        $t->assert_text_contains($test_name, $msg_txt, self::UNIT_WITHOUT_NAME);

        $test_name = 'the price with a criterion without a name is reported as not imported';
        $t->assert_text_contains($test_name, $msg_txt, self::CRITERION_WITHOUT_NAME);
        $msg->reset();

        // the population of Zurich has no unit and two values of 2021 that differ only by their criterion
        $test_name = 'the wikidata population of Zurich is converted to values of the city word with year and criterion';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q72);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_P1082);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q72), true);
        $result = $imp->convert($entity_json, $property_json, $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'the wikidata population of Zurich is converted without a message';
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        $test_name = 'an entity text that is no json is not converted';
        $result = $imp->convert('', $property_json, $msg);
        $t->assert($test_name, $result, []);
        $msg->reset();

        $test_name = 'a property json without an entity is not converted';
        $no_entity_json = json_encode([import_wikidata::WD_ENTITIES => []]);
        $result = $imp->convert($entity_json, $no_entity_json, $msg);
        $t->assert($test_name, $result, []);
        $msg->reset();
    }

}
