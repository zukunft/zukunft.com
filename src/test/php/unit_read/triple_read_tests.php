<?php

/*

    test/php/unit_read/triple.php - database unit testing of the triple functions
    -----------------------------


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

include_once paths::SHARED_TYPES . 'verbs.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED . 'json_fields.php';
include_once paths::MODEL_CONST . 'files.php';

use Zukunft\ZukunftCom\main\php\cfg\const\files;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_db_load;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class triple_read_tests
{

    // the seed triple of the currency codes e.g. "EUR is a ISO 4217 code"
    const string ISO_4217_CODE = 'ISO 4217 code';

    function run(test_cleanup $t): void
    {

        global $sys;
        $msg = new user_message();

        // init
        $t_db = new test_db_load($t);
        $t->name = 'triple read db->';
        $t->resource_path = 'db/triple/';

        // start the test section (ts)
        $ts = 'db read triple ';
        $t->header($ts);

        $t->subheader($ts . 'prepare');
        // load the verb used for testing
        $is_id = $sys->typ_lst->vrb->id(verbs::IS);
        // load the words used for testing the triples (Zurich (city) and Zurich (canton)
        $wrd_zh = $t_db->load_word($msg, word_names::ZH);
        $wrd_canton = $t_db->load_word($msg, word_names::CANTON);
        // create the group test word
        $wrd_company = $t_db->test_word($msg, word_names::COMPANY);

        $t->subheader($ts . 'load');
        $test_name = 'load triple ' . triple_names::MATH_CONST . ' by name and id';
        $trp = new triple($t->usr1);
        $trp->load_by_name(triple_names::MATH_CONST, $msg);
        $trp_by_id = new triple($t->usr1);
        $trp_by_id->load_by_id($trp->id(), $msg);
        $t->assert($test_name, $trp_by_id->name(), triple_names::MATH_CONST);
        $t->assert($test_name, $trp_by_id->description, triple_names::MATH_CONST_COM);

        $test_name = 'load the standard of ' . triple_names::MATH_CONST . ' by its from, verb and to';
        $trp_std = new triple($t->usr1);
        $trp_std->load_standard_by_type_link(word_names::CONST_ID, verbs::PART_ID, word_names::MATH_ID, $msg);
        $t->assert($test_name, $trp_std->id(), triple_names::MATH_CONST_ID);
        // negative: the reversed link is another triple, which does not exist
        $test_name = '... but not by the reversed link';
        $trp_rev = new triple($t->usr1);
        $trp_rev->load_standard_by_type_link(word_names::MATH_ID, verbs::PART_ID, word_names::CONST_ID, $msg);
        $t->assert($test_name, $trp_rev->id(), 0);
        $test_name = 'load the standard of ' . triple_names::MATH_CONST . ' by its from and to';
        $trp_std = new triple($t->usr1);
        $trp_std->load_standard_by_link(word_names::CONST_ID, word_names::MATH_ID, $msg);
        $t->assert($test_name, $trp_std->id(), triple_names::MATH_CONST_ID);
        $test_name = '... but not by the reversed from and to';
        $trp_rev = new triple($t->usr1);
        $trp_rev->load_standard_by_link(word_names::MATH_ID, word_names::CONST_ID, $msg);
        $t->assert($test_name, $trp_rev->id(), 0);
        $msg->reset();

        // a triple can carry a phrase type like a word: the unit triple "gram per kWh" of the
        // use case import is typed "measure unit", so the type must survive the db round trip
        // (import, save, load), else a table header shows the unit like a normal phrase
        $test_name = 'the measure type of the unit triple ' . triple_names::GRAM_PER_KWH . ' is saved';
        $trp_unit = new triple($t->usr1);
        $trp_unit->load_by_name(triple_names::GRAM_PER_KWH, $msg);
        $t->assert_true($test_name, $trp_unit->is_measure());
        // negative: a triple imported without a type is no measure
        $test_name = '... and a triple without a type is no measure';
        $t->assert_false($test_name, $trp_by_id->is_measure());

        $t->subheader($ts . 'currency pairs');
        // the wikidata import gives the price of a currency in another currency only to a listed pair,
        // so each pair of the seed must be a measure unit of two currency codes e.g. "EUR per USD"
        $iso_code = new triple($t->usr1);
        $iso_code->load_by_name(self::ISO_4217_CODE, $msg);
        $test_name = 'each currency pair of the seed is a measure unit of two ' . self::ISO_4217_CODE . 's';
        $pairs = $this->seed_currency_pairs();
        $t->assert($test_name, $this->no_currency_pairs($t, $pairs, $iso_code->phrase(), $msg), []);

        $test_name = '... and the seed lists the pairs';
        $t->assert_true($test_name, count($pairs) > 0);

        // negative: a measure unit of two other words is no currency pair
        $test_name = '... and ' . triple_names::GRAM_PER_KWH . ' is no currency pair';
        $result = $this->no_currency_pairs($t, [triple_names::GRAM_PER_KWH], $iso_code->phrase(), $msg);
        $t->assert($test_name, $result, [triple_names::GRAM_PER_KWH]);

        $t->subheader($ts . 'views tab');
        // assigned_views.json assigns the view of all mathematical constants to the triple as its default view
        $test_name = 'the view directly assigned to ' . triple_names::MATH_CONST . ' is on top of its views tab';
        $trp_by_id->load_views_related($msg);
        $msk_ids = $trp_by_id->views_related->ids();
        $top_id = $msk_ids[0] ?? 0;
        $t->assert($test_name, $top_id, views::MATH_CONST_ID);
        // negative: the system default triple view is only the fallback for a triple without any view
        $test_name = '... and the system default triple view is not offered instead';
        $t->assert_contains_not($test_name, $msk_ids, views::TRIPLE_ID);

        $test_name = 'triple load ' . word_names::CANTON . ' ' . word_names::ZH . ' by link';
        $lnk_canton = new triple($t->usr1);
        $lnk_canton->load_by_link_id( $wrd_zh->id(), $msg, $is_id, $wrd_canton->id() );
        $target = word_names::ZH . ' (' . word_names::CANTON . ')';
        $result = $lnk_canton->name();
        $t->assert($test_name, $result, $target, $t::TIMEOUT_LIMIT_DB);

        $test_name = 'triple generated name of ' . word_names::CANTON . ' ' . word_names::ZH . ' via function';
        $lnk_canton->set_name('');
        $result = $lnk_canton->name_generated();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple load ' . triple_names::COMPANY_ZURICH . ' by link';
        $lnk_company = new triple($t->usr1);
        $lnk_company->load_by_link_id( $wrd_zh->id(), $msg, $is_id, $wrd_company->id() );
        $target = triple_names::COMPANY_ZURICH;
        $result = $lnk_company->name();
        $t->assert($test_name, $result, $target);

        $test_name = 'triple generated name of ' . triple_names::COMPANY_ZURICH . ' via function';
        $lnk_company->set_name('');
        $target = 'Zurich (company)';
        $result = $lnk_company->name_generated();
        $t->assert($test_name, $result, $target);

    }

    /**
     * @return array the names of the currency pairs of the seed e.g. "EUR per USD", which are the triples of the
     *               currencies json with the verb "per" and without a name, because the import derives it
     */
    private function seed_currency_pairs(): array
    {
        $seed = json_decode(file_get_contents(files::MESSAGE_PATH . files::CURRENCIES), true);
        $result = [];
        foreach ($seed[json_fields::TRIPLES] ?? [] as $trp) {
            if ($trp[json_fields::EX_VERB] == verbs::PER_NAME) {
                $result[] = implode(' ', [$trp[json_fields::EX_FROM], verbs::PER_NAME, $trp[json_fields::EX_TO]]);
            }
        }
        return $result;
    }

    /**
     * @param test_cleanup $t the test environment with the user that reads the triples
     * @param array $names the names of the triples to check e.g. the pairs of the seed
     * @param phrase $iso_code the phrase "ISO 4217 code" of the seed
     * @param user_message $msg to collect what the database read reports
     * @return array the names of the triples that are no measure unit of two currency codes, empty if all are pairs
     */
    private function no_currency_pairs(test_cleanup $t, array $names, phrase $iso_code, user_message $msg): array
    {
        $result = [];
        foreach ($names as $name) {
            $pair = new triple($t->usr1);
            $pair->load_by_name($name, $msg);
            if (!$this->is_currency_pair($pair, $iso_code, $msg)) {
                $result[] = $name;
            }
        }
        return $result;
    }

    /**
     * @param triple $pair a triple loaded from the database e.g. "EUR per USD"
     * @param phrase $iso_code the phrase "ISO 4217 code" of the seed
     * @param user_message $msg to collect what the database read reports
     * @return bool true if the triple is a measure unit whose from and to are each an ISO 4217 code
     */
    private function is_currency_pair(triple $pair, phrase $iso_code, user_message $msg): bool
    {
        return $pair->is_measure()
            and $this->is_iso_code($pair->get_from()->id(), $iso_code, $msg)
            and $this->is_iso_code($pair->get_to()->id(), $iso_code, $msg);
    }

    /**
     * @param int $code_id the phrase id of the from or the to of a currency pair e.g. the word "EUR"
     * @param phrase $iso_code the phrase "ISO 4217 code" of the seed, which gives the user that reads the link
     * @param user_message $msg to collect what the database read reports
     * @return bool true if the seed links the code with "is a" to the ISO 4217 code e.g. "EUR is a ISO 4217 code"
     */
    private function is_iso_code(int $code_id, phrase $iso_code, user_message $msg): bool
    {
        global $sys;
        $is_id = $sys->typ_lst->vrb->id(verbs::IS);
        $lnk = new triple($iso_code->get_user());
        return $lnk->load_by_link_id($code_id, $msg, $is_id, $iso_code->id()) > 0;
    }
}

