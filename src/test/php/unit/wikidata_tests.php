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
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::SHARED . 'library.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_tests
{

    // the message parts of a dummy unit and criterion without name (test_files::IMPORT_WIKIDATA_UNIT_Q0)
    const string UNIT_WITHOUT_NAME = 'unit "Q0"';
    const string CRITERION_WITHOUT_NAME = 'criterion "Q0"';
    // the criterion "resident population" of the Zurich population (test_files::IMPORT_WIKIDATA_UNIT_Q56229488)
    const string RESIDENT_POPULATION_ID = 'Q56229488';
    // the second criterion of the population of Zurich, the population present
    const string POPULATION_PRESENT_ID = 'Q56229478';
    // the wikidata ids of Zurich, of the property population and of the property price
    const string ZURICH_KEY = 'Q72';
    const string ZURICH_CITY = 'Zurich (city)';
    const string POPULATION_PROPERTY = 'P1082';
    const string PRICE_PROPERTY = 'P2284';
    const string RESIDENT_POPULATION_WITHOUT_NAME = 'criterion "Q56229488"';
    // the entity, the property and the claims of test_files::IMPORT_WIKIDATA_UNIT_CLAIM_RULES that are
    // reported, each as its claim id and its amount
    const string ZURICH = 'Zurich';
    const string POPULATION = 'population';
    const array CLAIM_SOMEVALUE = ['Q72$somevalue', ''];
    const array CLAIM_NOVALUE = ['Q72$novalue', ''];
    const array CLAIM_DEPRECATED = ['Q72$deprecated', '+103'];
    const array CLAIM_DECADE = ['Q72$decade', '+104'];
    const string DECADE_TIME = '+1970-00-00T00:00:00Z';
    const array CLAIM_TIME_UNKNOWN = ['Q72$time-unknown', '+105'];
    const array CLAIM_TIME_TWICE = ['Q72$time-twice', '+106'];
    const array CLAIM_APPLIES_TO_PART = ['Q72$applies-to-part', '+107'];
    const string APPLIES_TO_PART = 'P518';
    const array CLAIM_SAME_YEAR_1 = ['Q72$same-year-1', '+108'];
    const array CLAIM_SAME_YEAR_2 = ['Q72$same-year-2', '+109'];
    const string SAME_WORDS = 'population, Zurich, 1900';
    // the phrases of a pod by their wikidata id as the caller of the converter declares them: the triple
    // "Zurich (city)" for the entity Q72 and the word population for the property P1082
    const array POD_ZURICH_POPULATION = [
        'P1082' => [[
            json_fields::NAME => 'population',
            json_fields::WORDS => [[json_fields::NAME => 'population']],
            json_fields::TRIPLES => [],
        ]],
        'Q72' => [[
            json_fields::NAME => 'Zurich (city)',
            json_fields::WORDS => [[json_fields::NAME => 'Zurich'], [json_fields::NAME => 'city']],
            json_fields::TRIPLES => [[
                json_fields::NAME => 'Zurich (city)',
                json_fields::EX_FROM => 'Zurich',
                json_fields::EX_VERB => 'is a',
                json_fields::EX_TO => 'city',
            ]],
        ]],
    ];
    // a pod with the wikidata reference of the criterion "resident population" at two phrases
    const array POD_CRITERION_TWICE = [
        self::RESIDENT_POPULATION_ID => [
            [json_fields::NAME => 'resident population', json_fields::WORDS => [[json_fields::NAME => 'resident population']]],
            [json_fields::NAME => 'residents', json_fields::WORDS => [[json_fields::NAME => 'residents']]],
        ],
    ];
    const string RESIDENT_POPULATION_PHRASES = 'resident population, residents';
    // a pod with the wikidata reference of the US dollar at its two spellings
    const string USD_KEY = 'Q4917';
    const string USD_LABEL = 'United States dollar';
    const array POD_USD_TWICE = [
        self::USD_KEY => [
            [json_fields::NAME => 'US dollar', json_fields::WORDS => [[json_fields::NAME => 'US dollar']]],
            [json_fields::NAME => 'U.S. dollar', json_fields::WORDS => [[json_fields::NAME => 'U.S. dollar']]],
        ],
    ];
    const string USD_PHRASES = 'US dollar, U.S. dollar';
    // the US dollar of the pod and the currency pairs as the caller of the converter declares them, e.g. the
    // seed triple "EUR per USD" for the euros of one US dollar, without name, because the import derives it
    const string EURO_KEY = 'Q4916';
    const string USD_NAME = 'US dollar';
    const string PRICE = 'price';
    const string EUR_PER_USD = 'EUR per USD';
    const string USD_PER_EUR = 'USD per EUR';
    const array POD_USD = [
        self::USD_KEY => [[
            json_fields::NAME => self::USD_NAME,
            json_fields::WORDS => [[json_fields::NAME => 'dollar'], [json_fields::NAME => 'US']],
            json_fields::TRIPLES => [[
                json_fields::NAME => self::USD_NAME,
                json_fields::EX_FROM => 'dollar',
                json_fields::EX_VERB => verbs::OF_NAME,
                json_fields::EX_TO => 'US',
            ]],
        ]],
    ];
    const array PAIR_EUR_PER_USD = [
        json_fields::NAME => self::EUR_PER_USD,
        json_fields::WORDS => [[json_fields::NAME => 'EUR'], [json_fields::NAME => 'USD']],
        json_fields::TRIPLES => [[
            json_fields::EX_FROM => 'EUR',
            json_fields::EX_VERB => verbs::PER_NAME,
            json_fields::EX_TO => 'USD',
            json_fields::TYPE_NAME => phrase_types::MEASURE,
        ]],
    ];
    const array PAIR_USD_PER_EUR = [
        json_fields::NAME => self::USD_PER_EUR,
        json_fields::WORDS => [[json_fields::NAME => 'USD'], [json_fields::NAME => 'EUR']],
        json_fields::TRIPLES => [[
            json_fields::EX_FROM => 'USD',
            json_fields::EX_VERB => verbs::PER_NAME,
            json_fields::EX_TO => 'EUR',
            json_fields::TYPE_NAME => phrase_types::MEASURE,
        ]],
    ];
    // the US dollar and the euro with the pair "EUR per USD", with only the reverse pair and without any pair
    const array PAIRS_EUR_PER_USD = [self::USD_KEY => [self::EURO_KEY => self::PAIR_EUR_PER_USD], self::EURO_KEY => []];
    const array PAIRS_USD_PER_EUR = [self::USD_KEY => [], self::EURO_KEY => [self::USD_KEY => self::PAIR_USD_PER_EUR]];
    const array PAIRS_NONE = [self::USD_KEY => [], self::EURO_KEY => []];
    // the first euro price of the US dollar fixture, which has no claim id, and its inverse with its two digits
    const array CLAIM_EURO_PRICE = ['', '+0.92'];
    const string EURO_PRICE_INVERSE = '1.1';
    // the price of the hand made US dollar of test_files::IMPORT_WIKIDATA_UNIT_CURRENCY_PRICE_ZERO
    const array CLAIM_PRICE_ZERO = ['Q4917$zero', '+0'];

    /**
     * the offline tests of the wikidata converter with hand made wikidata answers,
     * so that the result does not depend on the live data that wikidata_write_tests reads
     *
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {
        global $mtr;

        // start the test section (ts)
        $ts = 'wikidata ';
        $t->header($ts);

        $t->subheader($ts . 'convert');
        // reads the units and criteria only from the hand made answers, so the test never sends a live request
        $imp = new import_wikidata(test_paths::IMPORT_WIKIDATA_UNIT);
        $msg = new user_message();

        // the dummy prices are in euro, without a unit, in the unit Q0 that has no name,
        // with the criterion "resident population" and with the criterion Q0 that has no name
        // without the currencies of the pod the euro cannot be known as a currency, so it is a unit of its own
        $test_name = 'the wikidata prices of the US dollar are converted to words and values';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q4917);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_P2284);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q4917_PROPERTY_TYPE), true);
        $result = $imp->convert($entity_json, $property_json, [], [], $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'the price in a unit without a name is reported as not imported';
        $msg_txt = $msg->all_message_text();
        $t->assert_text_contains($test_name, $msg_txt, self::UNIT_WITHOUT_NAME);

        $test_name = 'the price with a criterion without a name is reported as not imported';
        $t->assert_text_contains($test_name, $msg_txt, self::CRITERION_WITHOUT_NAME);
        $msg->reset();

        // negative: the pod has the wikidata reference of the US dollar at two phrases, so none is guessed
        $test_name = 'an entity that is the reference of two phrases of the pod is not converted';
        $result = $imp->convert($entity_json, $property_json, self::POD_USD_TWICE, [], $msg);
        $t->assert($test_name, $result, []);

        $test_name = '... and both phrases are named in the message';
        $vars = [
            msg_id::VAR_NAME => self::USD_LABEL,
            msg_id::VAR_ID => self::USD_KEY,
            msg_id::VAR_NAME_LIST => self::USD_PHRASES,
        ];
        $expected = new library()->msg_var_text([[msg_id::WIKIDATA_ID_TWICE, $vars]], $mtr);
        $t->assert($test_name, $msg->text(), $expected);
        $msg->reset();

        // the population of Zurich has no unit and two values of 2021 that differ only by their criterion
        $test_name = 'the wikidata population of Zurich is converted to values of the city word with year and criterion';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q72);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_P1082);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q72_PROPERTY_TYPE), true);
        $result = $imp->convert($entity_json, $property_json, [], [], $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = 'the wikidata population of Zurich is converted without a message';
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        // the pod has the triple "Zurich (city)" with the reference Q72 and the word population with P1082
        $test_name = 'the population of Zurich is converted to the phrases of the pod with these wikidata references';
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q72_POD_NAMES), true);
        $result = $imp->convert($entity_json, $property_json, self::POD_ZURICH_POPULATION, [], $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = '... without a message';
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        // the import preview reads the entity only once and the properties that the pod knows not at all
        $test_name = 'the property json of a property of the pod gives the values without reading the property label';
        $prp_json = import_wikidata::property_json(self::POPULATION_PROPERTY);
        $result = $imp->convert($entity_json, $prp_json, self::POD_ZURICH_POPULATION, [], $msg);
        $t->assert_json($test_name, $result, $target);
        $msg->reset();

        // negative: a property without label that the pod does not know cannot be named
        $test_name = '... and nothing without the phrase of the pod for the property';
        $result = $imp->convert($entity_json, $prp_json, [], [], $msg);
        $t->assert($test_name, $result, []);
        $msg->reset();

        $test_name = 'the import preview finds the properties of the claims of Zurich';
        $t->assert($test_name, $imp->property_ids($entity_json), [self::POPULATION_PROPERTY]);

        // so that the preview loads the phrases of the pod with these references before the convert
        $test_name = 'the import preview finds the wikidata ids of the entity and the criteria of the population';
        $target_ids = [self::ZURICH_KEY, self::RESIDENT_POPULATION_ID, self::POPULATION_PRESENT_ID];
        $t->assert($test_name, $imp->item_ids($entity_json, [self::POPULATION_PROPERTY]), $target_ids);

        // negative: a property that the pod does not know adds no id
        $test_name = '... and only the entity id for a property without claims';
        $t->assert($test_name, $imp->item_ids($entity_json, [self::PRICE_PROPERTY]), [self::ZURICH_KEY]);

        // negative: a criterion that is the reference of two phrases of the pod is never guessed
        $test_name = 'the value of a criterion that is the reference of two phrases is not imported';
        $result = $imp->convert($entity_json, $property_json, self::POD_CRITERION_TWICE, [], $msg);
        $values = $result[json_fields::VALUES] ?? [];
        $t->assert($test_name, count($values), 2);

        $test_name = '... and both phrases are named in the message';
        $vars = [
            msg_id::VAR_NAME => self::ZURICH,
            msg_id::VAR_ID => self::RESIDENT_POPULATION_ID,
            msg_id::VAR_NAME_LIST => self::RESIDENT_POPULATION_PHRASES,
        ];
        $expected = new library()->msg_var_text([[msg_id::WIKIDATA_ID_TWICE, $vars]], $mtr);
        $t->assert($test_name, $msg->text(), $expected);
        $msg->reset();

        // negative: a folder without the answer of a criterion is reported instead of asking wikidata
        $test_name = 'a criterion without a hand made answer is reported and not read from wikidata';
        $imp_no_file = new import_wikidata(test_paths::IMPORT_WIKIDATA);
        $imp_no_file->convert($entity_json, $property_json, [], [], $msg);
        $msg_txt = $msg->all_message_text();
        $missing_file = test_paths::IMPORT_WIKIDATA . self::RESIDENT_POPULATION_ID . import_wikidata::JSON_EXTENSION;
        $t->assert_text_contains($test_name, $msg_txt, $missing_file);

        $test_name = '... so that the value of this criterion is not imported';
        $t->assert_text_contains($test_name, $msg_txt, self::RESIDENT_POPULATION_WITHOUT_NAME);
        $msg->reset();

        $test_name = 'an entity text that is no json is not converted';
        $result = $imp->convert('', $property_json, [], [], $msg);
        $t->assert($test_name, $result, []);
        $msg->reset();

        $test_name = 'a property json without an entity is not converted';
        $no_entity_json = json_encode([import_wikidata::WD_ENTITIES => []]);
        $result = $imp->convert($entity_json, $no_entity_json, [], [], $msg);
        $t->assert($test_name, $result, []);
        $msg->reset();

        // a hand made Zurich with one population claim per rule: the claims that follow the rules
        // are imported, and every other claim is reported by the message of its rule
        $t->subheader($ts . 'claim rules');
        $test_name = 'the claims that follow the rules are imported with the year of each precision and sign';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_CLAIM_RULES);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_CLAIM_RULES), true);
        $result = $imp->convert($entity_json, $property_json, [], [], $msg);
        $t->assert_json($test_name, $result, $target);
        $msg_txt = $msg->all_message_text();

        $test_name = 'an unknown value is reported';
        $expected = $this->claim_message(msg_id::WIKIDATA_NO_VALUE, self::CLAIM_SOMEVALUE);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = '... and so is no value';
        $expected = $this->claim_message(msg_id::WIKIDATA_NO_VALUE, self::CLAIM_NOVALUE);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'a deprecated value is reported';
        $expected = $this->claim_message(msg_id::WIKIDATA_DEPRECATED, self::CLAIM_DEPRECATED);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'a point in time of a decade is reported';
        $time = [msg_id::VAR_TIME => self::DECADE_TIME];
        $expected = $this->claim_message(msg_id::WIKIDATA_TIME_PRECISION, self::CLAIM_DECADE, $time);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'an unknown point in time is reported';
        $time = [msg_id::VAR_TIME => ''];
        $expected = $this->claim_message(msg_id::WIKIDATA_TIME_UNKNOWN, self::CLAIM_TIME_UNKNOWN, $time);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'two points in time of one value are reported';
        $expected = $this->claim_message(msg_id::WIKIDATA_TIME_TWICE, self::CLAIM_TIME_TWICE);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'a qualifier that changes what the number means is reported';
        $qualifier = [msg_id::VAR_FIELD_NAME => self::APPLIES_TO_PART];
        $expected = $this->claim_message(msg_id::WIKIDATA_QUALIFIER_UNKNOWN, self::CLAIM_APPLIES_TO_PART, $qualifier);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = 'two values with the same words are both reported';
        $words = [msg_id::VAR_NAME_LIST => self::SAME_WORDS];
        $expected = $this->claim_message(msg_id::WIKIDATA_SAME_WORDS, self::CLAIM_SAME_YEAR_1, $words);
        $t->assert_text_contains($test_name, $msg_txt, $expected);

        $test_name = '... the second one too';
        $expected = $this->claim_message(msg_id::WIKIDATA_SAME_WORDS, self::CLAIM_SAME_YEAR_2, $words);
        $t->assert_text_contains($test_name, $msg_txt, $expected);
        $msg->reset();

        // the price of a currency in another currency is a value of the listed pair, which names both currencies
        $t->subheader($ts . 'currency pairs');
        $test_name = 'the euro prices of the US dollar are values of the listed pair ' . self::EUR_PER_USD;
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q4917);
        $property_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_P2284);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q4917_CURRENCY_PAIR), true);
        $result = $imp->convert($entity_json, $property_json, self::POD_USD, self::PAIRS_EUR_PER_USD, $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = '... and the price in a unit without a name is still reported';
        $t->assert_text_contains($test_name, $msg->all_message_text(), self::UNIT_WITHOUT_NAME);
        $msg->reset();

        // only the reverse pair is listed, so the number is the inverse with the digits of wikidata
        $test_name = 'the euro prices of the US dollar are the inverse of the listed reverse pair ' . self::USD_PER_EUR;
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q4917_REVERSE_PAIR), true);
        $result = $imp->convert($entity_json, $property_json, self::POD_USD, self::PAIRS_USD_PER_EUR, $msg);
        $t->assert_json($test_name, $result, $target);

        $test_name = '... and the user is told that the number is the inverse';
        $price = [
            msg_id::VAR_NAME => self::USD_NAME,
            msg_id::VAR_PHRASE_NAME => self::PRICE,
            msg_id::VAR_TRIPLE_NAME => self::USD_PER_EUR,
            msg_id::VAR_VALUE_CHK => self::EURO_PRICE_INVERSE,
        ];
        $expected = $this->claim_message(msg_id::WIKIDATA_CURRENCY_PAIR_INVERTED, self::CLAIM_EURO_PRICE, $price);
        $t->assert_text_contains($test_name, $msg->all_message_text(), $expected);
        $msg->reset();

        // negative: without a listed pair the import cannot know which currency is the base
        $test_name = 'a price in a currency without listed pair is not imported';
        $result = $imp->convert($entity_json, $property_json, self::POD_USD, self::PAIRS_NONE, $msg);
        $values = $result[json_fields::VALUES] ?? [];
        $t->assert($test_name, array_column($values, json_fields::WORDS), [[self::PRICE, self::USD_NAME]]);

        $test_name = '... and is reported with the currency of the price';
        $price = [
            msg_id::VAR_NAME => self::USD_NAME,
            msg_id::VAR_PHRASE_NAME => self::PRICE,
            msg_id::VAR_NAME_TO => self::EURO_KEY,
        ];
        $expected = $this->claim_message(msg_id::WIKIDATA_CURRENCY_PAIR_MISSING, self::CLAIM_EURO_PRICE, $price);
        $t->assert_text_contains($test_name, $msg->all_message_text(), $expected);
        $msg->reset();

        // negative: a price of zero has no inverse
        $test_name = 'a price of zero is not imported for a reverse pair';
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_CURRENCY_PRICE_ZERO);
        $result = $imp->convert($entity_json, $property_json, self::POD_USD, self::PAIRS_USD_PER_EUR, $msg);
        $t->assert($test_name, $result[json_fields::VALUES] ?? null, []);

        $test_name = '... and is reported';
        $price = [
            msg_id::VAR_NAME => self::USD_NAME,
            msg_id::VAR_PHRASE_NAME => self::PRICE,
            msg_id::VAR_TRIPLE_NAME => self::USD_PER_EUR,
        ];
        $expected = $this->claim_message(msg_id::WIKIDATA_CURRENCY_PRICE_ZERO, self::CLAIM_PRICE_ZERO, $price);
        $t->assert_text_contains($test_name, $msg->all_message_text(), $expected);
        $msg->reset();
    }

    /**
     * @param msg_id $msg_id the message of the rule e.g. msg_id::WIKIDATA_DEPRECATED
     * @param array $claim the claim id and the amount of the hand made claim e.g. self::CLAIM_DEPRECATED
     * @param array $vars the vars of the rule e.g. the point in time of the claim, which also replace the entity
     *                    and the property of the hand made Zurich e.g. by the US dollar and its price
     * @return string the message of the converter that the claim of the hand made Zurich is not imported
     */
    private function claim_message(msg_id $msg_id, array $claim, array $vars = []): string
    {
        global $mtr;
        [$claim_id, $amount] = $claim;
        $claim_vars = [
            msg_id::VAR_NAME => self::ZURICH,
            msg_id::VAR_PHRASE_NAME => self::POPULATION,
            msg_id::VAR_ID => $claim_id,
            msg_id::VAR_VALUE => $amount,
        ];
        return new library()->msg_var_text([[$msg_id, $vars + $claim_vars]], $mtr);
    }

}
