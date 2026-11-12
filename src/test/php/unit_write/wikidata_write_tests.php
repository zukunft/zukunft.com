<?php

/*

    test/php/unit_write/wikidata_write_tests.php - test retrieving and capturing wikidata entity data
    --------------------------------------------

    retrieves the entity data json from wikidata for a given wikidata id and stores
    it as a test fixture in the wikidata test resource folder


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

include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_IMPORT . 'import_wikidata_preview.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once test_paths::CONST . 'files.php';
include_once test_paths::UNIT . 'wikidata_tests.php';
include_once test_paths::UTILS . 'test_base.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata_preview;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\unit\wikidata_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_write_tests
{

    // the wikidata id of the US dollar used as a second capture example next to Pi (refs::PI_KEY)
    const string USD_KEY = 'Q4917';
    // the wikidata id of the price property used as a property capture example
    const string PRICE_PROPERTY = 'P2284';
    // a wikidata property id that does not exist used for the negative property tests
    const string UNKNOWN_PROPERTY = 'P999999999';
    // the wikidata id and the expected names used to test the conversion of the US dollar price into euro
    const string EURO_KEY = 'Q4916';
    const string PRICE_NAME = 'price';
    const string USD_NAME = 'United States dollar';
    // the wikidata id of the city of Zurich and its population property, whose values have no unit
    const string ZH_KEY = 'Q72';
    const string POPULATION_PROPERTY = 'P1082';
    const string ZH_NAME = 'Zurich';
    const string POPULATION_NAME = 'population';
    // the labels of the two "criterion used" items that separate the Zurich population values of the same year
    const string POPULATION_PRESENT_NAME = 'population present';
    const string RESIDENT_POPULATION_NAME = 'resident population';
    // stands for the id of any unit in the expected message that the name of a unit cannot be read
    const string UNIT_ID_MARK = 'System Test Wikidata Unit Id';

    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'wikidata import ';
        $t->header($ts);

        $imp = new import_wikidata();

        // positive: retrieve the wikidata json for two example entities and store them as test fixtures
        $this->assert_capture($t, $imp, refs::PI_KEY);
        $this->assert_capture($t, $imp, self::USD_KEY);

        // positive: retrieve and store the wikidata json of a property (P2284)
        $this->assert_property_capture($t, $imp);

        // convert the captured US dollar price (Q4917 + P2284) into a
        // zukunft.com import json in src/test/resources/import/wikidata/to_import
        $this->assert_convert($t, $imp);

        // convert the captured population of Zurich (Q72 + P1082), whose values have no unit
        $this->assert_capture($t, $imp, self::ZH_KEY);
        $this->assert_convert_population($t, $imp);

        // the values that the refresh of a wikidata reference offers, converted with the phrases of the pod
        $this->assert_refresh_convert($t, self::ZH_KEY, wikidata_tests::ZURICH_CITY, test_files::IMPORT_WIKIDATA_REFRESH_Q72);
        $this->assert_refresh_convert($t, self::USD_KEY, wikidata_tests::USD_NAME, test_files::IMPORT_WIKIDATA_REFRESH_Q4917);

        // negative: an unknown wikidata id returns a failure message and stores nothing
        $test_name = 'store of an unknown wikidata id (' . refs::CHANGE_NEW_KEY . ') fails';
        $bad_msg = $imp->store_json(refs::CHANGE_NEW_KEY, test_paths::IMPORT_WIKIDATA_CACHE);
        $t->assert_true($test_name, !$bad_msg->is_ok());
    }

    /**
     * convert the captured population of Zurich (Q72) and the population property (P1082): the values have
     * no unit, so each value is assigned to the city word itself and no unit word is created
     * @param test_cleanup $t the test environment with the assert functions
     * @param import_wikidata $imp the importer with the convert function
     * @return void
     */
    private function assert_convert_population(test_cleanup $t, import_wikidata $imp): void
    {
        $test_name = 'every population value of ' . self::ZH_NAME . ' is converted';
        $imp->store_json(self::POPULATION_PROPERTY, test_paths::IMPORT_WIKIDATA_CACHE);
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_Q72);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_P1082);
        $msg = new user_message();
        // without the phrases of the pod, so that the live data is converted to the wikidata labels
        $result = $imp->convert($entity_json, $property_json, [], [], $msg);
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        // the values up to 1900 have no criterion, many later years have a value for each criterion
        $test_name = 'each population value of ' . self::ZH_NAME . ' has only the population, the city, the year and its criterion';
        $target = [
            [self::POPULATION_NAME, self::ZH_NAME],
            [self::POPULATION_NAME, self::ZH_NAME, self::POPULATION_PRESENT_NAME],
            [self::POPULATION_NAME, self::ZH_NAME, self::RESIDENT_POPULATION_NAME]
        ];
        $t->assert($test_name, $this->value_words_without_time($result), $target);

        $test_name = 'no two population values of ' . self::ZH_NAME . ' have the same words';
        $t->assert($test_name, $this->value_words_used_twice($result), []);
    }

    /**
     * convert the live entity, which the capture above has stored, with the phrases of the pod like the refresh of
     * a wikidata reference and compare the import json with the last run, so that a change of the live data is
     * reported as such and not only as a failed assert of the wikidata_import workflow (ref_write_url_tests)
     *
     * @param test_cleanup $t the test environment with the assert functions
     * @param string $wikidata_id the wikidata id of the captured entity e.g. "Q72"
     * @param string $name the name of the phrase of the pod with this wikidata reference e.g. "Zurich (city)"
     * @param string $file the import json of the last run e.g. test_files::IMPORT_WIKIDATA_REFRESH_Q72
     * @return void
     */
    private function assert_refresh_convert(test_cleanup $t, string $wikidata_id, string $name, string $file): void
    {
        $msg = new user_message($t->usr1);
        $claim_msg = new user_message($t->usr1); // not reported, because the live claims may be reported for another reason e.g. the qualifier P31
        $entity_json = file_get_contents(test_paths::IMPORT_WIKIDATA_CACHE . $wikidata_id . import_wikidata::JSON_EXTENSION);
        $preview = new import_wikidata_preview($t->usr1);
        $import = $preview->import_of_entity($entity_json, $name, $msg, $claim_msg);
        $json = json_encode($import, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $test_name = 'the live wikidata entity ' . $wikidata_id . ' gives the import json of the last run with the phrases of the pod';
        $t->assert_file($test_name, $json, $file, test_files::JSON);
        $t->assert_msg($test_name, $msg);
    }

    /**
     * @param array $import a zukunft.com import json as an array e.g. as created by import_wikidata::convert
     * @return array the sorted distinct word lists of the values without the time words e.g. [["population", "Zurich"]]
     */
    private function value_words_without_time(array $import): array
    {
        $time_words = [];
        foreach ($import[json_fields::WORDS] ?? [] as $word) {
            if (($word[json_fields::TYPE_NAME] ?? '') == phrase_types::TIME) {
                $time_words[] = $word[json_fields::NAME];
            }
        }
        $word_lists = [];
        foreach ($import[json_fields::VALUES] ?? [] as $value) {
            $word_lists[] = array_values(array_diff($value[json_fields::WORDS], $time_words));
        }
        // sorted, so that the result does not depend on the order of the live wikidata claims
        $result = array_values(array_unique($word_lists, SORT_REGULAR));
        sort($result);
        return $result;
    }

    /**
     * @param array $import a zukunft.com import json as an array e.g. as created by import_wikidata::convert
     * @return array the word lists that more than one value uses, which would be conflicting values in the import
     */
    private function value_words_used_twice(array $import): array
    {
        $word_lists = array_column($import[json_fields::VALUES] ?? [], json_fields::WORDS);
        $doubles = array_diff_key($word_lists, array_unique($word_lists, SORT_REGULAR));
        return array_values($doubles);
    }

    /**
     * get the wikidata price property (P2284) json via the general property getter and store it as a fixture
     * @param test_cleanup $t the test environment with the assert functions
     * @param import_wikidata $imp the importer used to retrieve and store the json
     * @return void
     */
    private function assert_property_capture(test_cleanup $t, import_wikidata $imp): void
    {
        // get the property json via the general property getter and store it in the same way as the entities
        $test_name = 'store the wikidata property json for ' . self::PRICE_PROPERTY;
        $json = $imp->get_property_json(self::PRICE_PROPERTY);
        $msg = $imp->store_text(self::PRICE_PROPERTY, $json, test_paths::IMPORT_WIKIDATA_CACHE);
        $t->assert_true($test_name . ' ' . $msg->all_message_text(), $msg->is_ok());

        $test_name = 'store of an empty wikidata json fails';
        $bad_msg = $imp->store_text(self::UNKNOWN_PROPERTY, '', test_paths::IMPORT_WIKIDATA_CACHE);
        $t->assert_msg_false($test_name, $bad_msg, self::UNKNOWN_PROPERTY);

        // and the captured json file contains the requested property id
        // only checked if wikidata could be reached so that an offline test run continues with a warning
        if ($msg->is_ok()) {
            $test_name = 'the captured wikidata property json for ' . self::PRICE_PROPERTY . ' contains the property id';
            $file = test_paths::IMPORT_WIKIDATA_CACHE . self::PRICE_PROPERTY . import_wikidata::JSON_EXTENSION;
            $t->assert_text_contains($test_name, file_get_contents($file), self::PRICE_PROPERTY);

            // negative: offline every property returns no json, so only a reached wikidata tells an unknown one
            $test_name = 'an unknown wikidata property returns no json';
            $bad_json = $imp->get_property_json(self::UNKNOWN_PROPERTY);
            $t->assert($test_name, $bad_json, '');
        }
    }

    /**
     * convert the captured US dollar price (Q4917) and the price property (P2284) into a zukunft.com
     * import json and check that the price word and the values of the currency pair "EUR per USD" are created
     * @param test_cleanup $t the test environment with the assert functions
     * @param import_wikidata $imp the importer with the convert function
     * @return void
     */
    private function assert_convert(test_cleanup $t, import_wikidata $imp): void
    {
        // capture the euro unit as a fixture; the importer without fixture folder reads its name live from wikidata
        $imp->store_json(self::EURO_KEY, test_paths::IMPORT_WIKIDATA_CACHE);
        $entity_json = file_get_contents(test_paths::IMPORT_WIKIDATA_CACHE . self::USD_KEY . import_wikidata::JSON_EXTENSION);
        $property_json = file_get_contents(test_paths::IMPORT_WIKIDATA_CACHE . self::PRICE_PROPERTY . import_wikidata::JSON_EXTENSION);

        // convert and write the zukunft.com import json to the wikidata to import folder
        $test_name = 'the converted import json is written to the wikidata to import folder';
        $msg = new user_message();
        // with the listed pair "EUR per USD" of the seed, so that the euro prices are values of the pair
        $pairs = wikidata_tests::PAIRS_EUR_PER_USD;
        $file = $imp->convert_to_file(self::USD_KEY, $entity_json, $property_json, [], $pairs, $msg, test_paths::IMPORT_WIKIDATA_TO_IMPORT);
        $t->assert_text_contains($test_name, $file, test_paths::IMPORT_WIKIDATA_TO_IMPORT . import_wikidata::IMPORT_PREFIX . self::USD_KEY);

        // only the unit, because the live claims may be reported for another reason e.g. the qualifier P31
        $test_name = 'the name of the unit of every US dollar price can be read from wikidata';
        $msg_txt = $msg->all_message_text();
        $t->assert_text_not_contains($test_name, $msg_txt, $this->unit_missing_start(self::USD_NAME));
        $msg->reset();

        // the written import file contains the price word and the values of the currency pair
        if ($file != '') {
            $json = file_get_contents($file);
            $test_name = 'the import file contains the "' . self::PRICE_NAME . '" word';
            $t->assert_text_contains($test_name, $json, self::PRICE_NAME);
            $test_name = 'the import file contains the values of the currency pair "' . wikidata_tests::EUR_PER_USD . '"';
            $t->assert_text_contains($test_name, $json, wikidata_tests::EUR_PER_USD);
            $test_name = 'the import file contains the wikidata reference (' . self::USD_KEY . ') of the words';
            $t->assert_text_contains($test_name, $json, self::USD_KEY);
        }

        // the cleanup is switched off for the test so that the converted import file can be inspected
        // (in an import pipeline call $imp->cleanup_file($file) after the import to remove the file)
    }

    /**
     * @param string $entity_name the name of the entity e.g. "United States dollar"
     * @return string the message that a value of the entity is not imported until the id of its unit, which any unit fills
     */
    private function unit_missing_start(string $entity_name): string
    {
        global $mtr;
        $vars = [msg_id::VAR_NAME => $entity_name, msg_id::VAR_ID => self::UNIT_ID_MARK];
        $unit_missing = new library()->msg_var_text([[msg_id::WIKIDATA_UNIT_LABEL_MISSING, $vars]], $mtr);
        $result = strstr($unit_missing, self::UNIT_ID_MARK, true);
        // empty if the message names no unit id, because an empty text is found in every message, so the
        // "not contains" assert fails instead of passing by mistake
        if ($result === false) {
            $result = '';
        }
        return $result;
    }

    /**
     * retrieve and store the wikidata json for the given id and check the captured fixture
     * @param test_cleanup $t the test environment with the assert functions
     * @param import_wikidata $imp the importer used to retrieve and store the json
     * @param string $wikidata_id the wikidata entity id e.g. "Q167" that is captured
     * @return void
     */
    private function assert_capture(test_cleanup $t, import_wikidata $imp, string $wikidata_id): void
    {
        // store the wikidata json as a test fixture
        $test_name = 'store the wikidata json for ' . $wikidata_id;
        $msg = $imp->store_json($wikidata_id, test_paths::IMPORT_WIKIDATA_CACHE);
        $t->assert_true($test_name . ' ' . $msg->all_message_text(), $msg->is_ok());

        // and the captured json file contains the requested entity id
        // only checked if wikidata could be reached so that an offline test run continues with a warning
        if ($msg->is_ok()) {
            $test_name = 'the captured wikidata json for ' . $wikidata_id . ' contains the entity id';
            $file = test_paths::IMPORT_WIKIDATA_CACHE . $wikidata_id . import_wikidata::JSON_EXTENSION;
            $t->assert_text_contains($test_name, file_get_contents($file), $wikidata_id);
        }
    }

}