<?php

/*

    test/php/unit_read/wikidata_read_tests.php - the preview of the wikidata import with the phrases of the database
    ------------------------------------------

    the wikidata answers are the hand made files of the unit tests, so the test never sends a live request,
    but the two maps of the converter (the phrases with a wikidata reference and the currency pairs) and
    the comparison of the values are read from the database of the seed


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

namespace Zukunft\ZukunftCom\test\php\unit_read;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::MODEL_IMPORT . 'import_wikidata.php';
include_once paths::MODEL_IMPORT . 'import_wikidata_preview.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_TYPES . 'verbs.php';
include_once test_paths::UNIT . 'wikidata_tests.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata_preview;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\unit\wikidata_tests;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikidata_read_tests
{

    // the euro label of the seed, whose wikidata reference Q4916 the pair of the US dollar price names
    const string EURO = 'Euro';
    // the source of the values of a wikidata import, declared by name, because the seed has it (sources.json)
    const array SOURCE_DECLARATION = [[json_fields::NAME => sources::WIKIDATA]];
    // the two years of the population of Zurich in the Q72 fixture: 2021 is a word of the seed and 1900 is not
    const string YEAR_OF_POD = '2021';
    const string YEAR_NEW = '1900';
    const array YEAR_NEW_DECLARATION = [json_fields::NAME => self::YEAR_NEW, json_fields::TYPE_NAME => phrase_types::TIME];

    function run(test_cleanup $t): void
    {
        // start the test section (ts)
        $ts = 'db read wikidata import preview ';
        $t->header($ts);
        $imp = new import_wikidata(test_paths::IMPORT_WIKIDATA_UNIT);
        $preview = new import_wikidata_preview($t->usr1, $imp);

        $t->subheader($ts . 'pod phrases');
        // the seed has the triple "Zurich (city)" with Q72 and the word population with P1082 (solution_prio.json)
        $msg = new user_message($t->usr1);
        $claim_msg = new user_message($t->usr1);
        $entity_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q72);
        $import = $preview->import_of_entity($entity_json, wikidata_tests::ZURICH_CITY, $msg, $claim_msg);
        $target = json_decode(file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_TARGET_Q72_POD_NAMES), true);
        $test_name = 'the preview converts the population of Zurich to the phrases of the pod';
        $t->assert_json($test_name, $import[json_fields::VALUES] ?? [], $target[json_fields::VALUES]);

        $test_name = '... with the words of the phrases of the pod and the new words of the criteria';
        $t->assert_json($test_name, $import[json_fields::WORDS] ?? [], $target[json_fields::WORDS]);

        // the seed triple is named like the import derives it, so the name is not repeated
        $test_name = '... and the triple "Zurich (city)" declared by its from, verb and to';
        $zurich = [
            json_fields::EX_FROM => wikidata_tests::ZURICH,
            json_fields::EX_VERB => verbs::IS_NAME,
            json_fields::EX_TO => 'city',
        ];
        $t->assert_json($test_name, $import[json_fields::TRIPLES] ?? [], [$zurich]);

        $test_name = '... without a message';
        $t->assert_msg($test_name, $claim_msg);

        // the triple without name is named like the import derives it, which is "Zurich (city)" for the verb "is a"
        $test_name = 'the preview of Zurich has no new triple, because "Zurich (city)" is a triple of the pod';
        $pv = $preview->preview_of_key(wikidata_tests::ZURICH_KEY, wikidata_tests::ZURICH_CITY, $msg);
        $new_triples = $pv[json_fields::PREVIEW_TRIPLES_NEW] ?? [];
        $t->assert($test_name, $new_triples, []);

        // else the import refuses the values, because an import json declares every object that it uses
        $test_name = '... and the import json declares the source wikidata of its values';
        $pv_import = $pv[json_fields::PREVIEW_IMPORT] ?? [];
        $pv_sources = $pv_import[json_fields::SOURCES] ?? [];
        $t->assert($test_name, $pv_sources, self::SOURCE_DECLARATION);

        // the import of values never changes a phrase of the pod, e.g. the type of the seed word "2021"
        $test_name = '... and declares the year ' . self::YEAR_OF_POD . ' of the pod by its name only';
        $pv_words = array_column($pv_import[json_fields::WORDS] ?? [], null, json_fields::NAME);
        $pod_year = $pv_words[self::YEAR_OF_POD] ?? [];
        $t->assert($test_name, $pod_year, [json_fields::NAME => self::YEAR_OF_POD]);

        // negative: a year that the pod does not have yet is a new word with the type time
        $test_name = '... but the new year ' . self::YEAR_NEW . ' with its type';
        $new_year = $pv_words[self::YEAR_NEW] ?? [];
        $t->assert($test_name, $new_year, self::YEAR_NEW_DECLARATION);

        $test_name = '... without a message';
        $t->assert_msg($test_name, $msg);

        // negative: an item whose properties the pod does not know, e.g. the label answer of the euro
        $test_name = 'nothing is converted for an item without a property of the pod';
        $no_prp_msg = new user_message($t->usr1);
        $euro_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q4916);
        $result = $preview->import_of_entity($euro_json, self::EURO, $no_prp_msg, $claim_msg);
        $t->assert($test_name, $result, []);

        $test_name = '... which is reported';
        $t->assert_text_contains($test_name, $no_prp_msg->all_message_text(), wikidata_tests::EURO_KEY);

        $t->subheader($ts . 'currency pairs');
        // the seed has the US dollar with Q4917, the Euro with Q4916, their ISO codes and the pair "EUR per USD"
        $pair_msg = new user_message($t->usr1);
        $usd_json = file_get_contents(test_files::IMPORT_WIKIDATA_UNIT_Q4917);
        $import = $preview->import_of_entity($usd_json, wikidata_tests::USD_NAME, $msg, $pair_msg);
        $test_name = 'the euro price of the US dollar is a value of the seed pair ' . wikidata_tests::EUR_PER_USD;
        $values = $import[json_fields::VALUES] ?? [];
        $words = array_merge([], ...array_column($values, json_fields::WORDS));
        $t->assert_contains($test_name, $words, wikidata_tests::EUR_PER_USD);

        // negative: the pair names both currencies, so the price is never a value of the euro word itself
        $test_name = '... and not of the ' . self::EURO . ' as a word of its own';
        $t->assert_contains_not($test_name, $words, self::EURO);

        // the pair without name is named "<from> <verb> <to>" for every verb other than "is a"
        $test_name = 'the preview of the US dollar has no new triple, because ' . wikidata_tests::EUR_PER_USD . ' is a triple of the pod';
        $pv = $preview->preview_of_key(wikidata_tests::USD_KEY, wikidata_tests::USD_NAME, $msg);
        $new_triples = $pv[json_fields::PREVIEW_TRIPLES_NEW] ?? [];
        $t->assert($test_name, $new_triples, []);
    }
}
