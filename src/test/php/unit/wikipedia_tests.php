<?php

/*

    test/php/unit/wikipedia_tests.php - unit tests of the description taken from a wikipedia page
    ---------------------------------


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
include_once paths::MODEL_REF . 'ref_link_wikipedia.php';
include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_TYPES . 'ref_types.php';

use Zukunft\ZukunftCom\main\php\cfg\import\import_wikidata;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_link_wikipedia;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\test\php\const\files as test_files;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class wikipedia_tests
{

    // the lead paragraph of test_files::IMPORT_WIKIPEDIA_SUMMARY_ZURICH without the pronunciation
    const string ZURICH_DESCRIPTION = 'Zürich is the largest city in Switzerland and the capital of the canton of Zürich (one of the 26 cantons). It is in north-central Switzerland, at the northwestern tip of Lake Zürich.';
    const string ZURICH_WIKIDATA_ID = 'Q72';
    const string TYPE_DISAMBIGUATION = 'disambiguation';
    const string DISAMBIGUATION_EXTRACT = 'Zurich may refer to:';
    const string WITH_IPA_SLASH = 'Zürich (/ˈzjʊərɪk/ ZURE-ik) is a city.';
    const string WITH_LISTEN = 'Zürich (listen) is a city.';
    const string WITHOUT_IPA = 'Zürich is a city.';
    const string WITH_BRACKET = 'Zürich is a city (the largest of Switzerland).';
    const string NO_WIKIDATA_ID = 'Zurich';
    // the lead paragraph of test_files::IMPORT_WIKIPEDIA_SUMMARY_GENEVA, which the summary api sends already
    // without the pronunciation of the page
    const string GENEVA_DESCRIPTION = 'Geneva is the second-most populous city in Switzerland and the most populous in French-speaking Romandy. Situated in the southwest of the country, where the Rhône leaves Lake Geneva, it is the capital of the Republic and Canton of Geneva. Geneva is a global city, an international financial centre, and a worldwide centre for diplomacy, which has led to it being called the "Peace Capital".';

    /**
     * the offline tests of the description and the wikidata id that the refresh icon of a
     * wikipedia reference takes from a hand made wikipedia summary
     *
     * @param test_cleanup $t the test object that includes the test results collected until now
     * @return void
     */
    function run(test_cleanup $t): void
    {

        // start the test section (ts)
        $ts = 'wikipedia ';
        $t->header($ts);
        $wik = new ref_link_wikipedia();
        $summary = json_decode(file_get_contents(test_files::IMPORT_WIKIPEDIA_SUMMARY_ZURICH), true);

        $t->subheader($ts . 'description');

        $test_name = 'the first paragraph without the pronunciation is the description';
        $result = $wik->description($summary);
        $t->assert($test_name, $result, self::ZURICH_DESCRIPTION);

        $test_name = 'a disambiguation page has no description';
        $page = [
            ref_link_wikipedia::WP_TYPE => self::TYPE_DISAMBIGUATION,
            ref_link_wikipedia::WP_EXTRACT => self::DISAMBIGUATION_EXTRACT,
        ];
        $result = $wik->description($page);
        $t->assert($test_name, $result, '');

        $test_name = 'a lead text that starts with a line break still gives the first paragraph';
        $page = [
            ref_link_wikipedia::WP_TYPE => ref_link_wikipedia::WP_TYPE_STANDARD,
            ref_link_wikipedia::WP_EXTRACT => ref_link_wikipedia::PARAGRAPH_SEPARATOR . self::WITHOUT_IPA
                . ref_link_wikipedia::PARAGRAPH_SEPARATOR . self::WITH_BRACKET,
        ];
        $result = $wik->description($page);
        $t->assert($test_name, $result, self::WITHOUT_IPA);

        $test_name = 'a page without lead text has no description';
        $page = [ref_link_wikipedia::WP_TYPE => ref_link_wikipedia::WP_TYPE_STANDARD];
        $result = $wik->description($page);
        $t->assert($test_name, $result, '');

        $t->subheader($ts . 'pronunciation');

        $test_name = 'a bracket with the ipa in slashes is removed';
        $result = $wik->without_pronunciation(self::WITH_IPA_SLASH);
        $t->assert($test_name, $result, self::WITHOUT_IPA);

        $test_name = 'a bracket with the listen link text is removed';
        $result = $wik->without_pronunciation(self::WITH_LISTEN);
        $t->assert($test_name, $result, self::WITHOUT_IPA);

        $test_name = 'a bracket without a pronunciation is kept';
        $result = $wik->without_pronunciation(self::WITH_BRACKET);
        $t->assert($test_name, $result, self::WITH_BRACKET);

        $t->subheader($ts . 'wikidata id');

        $test_name = 'the wikidata id of the page is taken over';
        $result = $wik->wikidata_id($summary);
        $t->assert($test_name, $result, self::ZURICH_WIKIDATA_ID);

        $test_name = 'a wikidata item that is no wikidata id is not taken over';
        $page = [ref_link_wikipedia::WP_WIKIDATA_ID => self::NO_WIKIDATA_ID];
        $result = $wik->wikidata_id($page);
        $t->assert($test_name, $result, '');

        $test_name = 'a page without wikidata item has no wikidata id';
        $result = $wik->wikidata_id([]);
        $t->assert($test_name, $result, '');

        // the summary of the Geneva page as wikipedia sent it, so that a change of the live page or of the
        // summary api does not change this test (the write tests compare the live page)
        $t->subheader($ts . 'frozen page');
        $geneva = json_decode(file_get_contents(test_files::IMPORT_WIKIPEDIA_SUMMARY_GENEVA), true);

        $test_name = 'the description of the frozen Geneva page is its lead paragraph';
        $result = $wik->description($geneva);
        $t->assert($test_name, $result, self::GENEVA_DESCRIPTION);

        $test_name = '... but the same page as a disambiguation page has no description';
        $page = $geneva;
        $page[ref_link_wikipedia::WP_TYPE] = self::TYPE_DISAMBIGUATION;
        $result = $wik->description($page);
        $t->assert($test_name, $result, '');

        $test_name = 'the wikidata id of the frozen Geneva page is taken over';
        $result = $wik->wikidata_id($geneva);
        $t->assert($test_name, $result, refs::GENEVA_KEY);

        $test_name = '... but not from the same page without its wikidata item';
        $page = $geneva;
        unset($page[ref_link_wikipedia::WP_WIKIDATA_ID]);
        $result = $wik->wikidata_id($page);
        $t->assert($test_name, $result, '');

        $t->subheader($ts . 'reference');
        $t_ref = new test_refs($t);

        $test_name = 'the wikipedia reference of a phrase is found by its type';
        $wiki_ref = $t_ref->ref_list_math_wikipedia()->get_by_type_code_id(ref_types::WIKIPEDIA);
        $key = $wiki_ref?->get_external_key();
        $t->assert($test_name, $key, refs::MATH_WIKIPEDIA_KEY);

        $test_name = 'a phrase without wikipedia reference has none';
        $wiki_ref = $t_ref->ref_list_math()->get_by_type_code_id(ref_types::WIKIPEDIA);
        $key = $wiki_ref?->get_external_key();
        $t->assert_null($test_name, $key);

        $test_name = 'a reference without type is skipped, so that the wikipedia reference is still found';
        $untyped_lst = $t_ref->ref_list_math_wikipedia();
        $untyped_lst->get_by_type_code_id(ref_types::WIKIDATA)?->set_predicate_id(null);
        $wiki_ref = $untyped_lst->get_by_type_code_id(ref_types::WIKIPEDIA);
        $key = $wiki_ref?->get_external_key();
        $t->assert($test_name, $key, refs::MATH_WIKIPEDIA_KEY);

        $test_name = 'a reference without type has no type name instead of a fatal error';
        $untyped_ref = $t_ref->reference_math();
        $untyped_ref->set_predicate_id(null);
        $t->assert($test_name, $untyped_ref->type_name(), '');

        $t->subheader($ts . 'refresh');
        $t_wrd = new test_words($t);
        $msg = new user_message();

        // reading wikipedia needs the network and the database, so the refresh itself is a write test
        $test_name = 'without the refresh request the description is kept';
        $wrd = $t_wrd->word_math();
        $wrd->set_description(word_names::MATH_COM);
        $wik->refresh_from_url($wrd, [], $msg);
        $description = $wrd->get_description();
        $t->assert($test_name, $description, word_names::MATH_COM);

        $test_name = '... without a message';
        $t->assert_msg($test_name, $msg);
        $msg->reset();

        $test_name = 'the request to wikipedia waits only a limited time';
        $http = stream_context_get_options(import_wikidata::request_context());
        $timeout = $http[import_wikidata::HTTP_CONTEXT][import_wikidata::HTTP_TIMEOUT] ?? 0;
        $t->assert($test_name, $timeout, import_wikidata::READ_TIMEOUT);

        $test_name = '... and names zukunft.com as user agent, because else wikipedia refuses it';
        $header = $http[import_wikidata::HTTP_CONTEXT][import_wikidata::HTTP_HEADER] ?? '';
        $t->assert_text_contains($test_name, $header, import_wikidata::USER_AGENT);

        // an import waits longer, because no user waits for a page and the entity json of wikidata is large
        $test_name = 'the request of a wikidata import waits longer than the request of a page';
        $import_http = stream_context_get_options(import_wikidata::request_context(import_wikidata::IMPORT_TIMEOUT));
        $import_timeout = $import_http[import_wikidata::HTTP_CONTEXT][import_wikidata::HTTP_TIMEOUT] ?? 0;
        $t->assert($test_name, $import_timeout, import_wikidata::IMPORT_TIMEOUT);
    }

}
