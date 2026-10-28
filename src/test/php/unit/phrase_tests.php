<?php

/*

    test/unit/phrase.php - unit testing of the phrase functions
    --------------------


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

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;

include_once paths::DB . 'sql.php';
include_once html_paths::PHRASE . 'phrase.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CONST . 'word_names.php';
include_once test_paths::CREATE . 'test_phrases.php';
include_once test_paths::CREATE . 'test_triples.php';
include_once test_paths::CREATE . 'test_words.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_table;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_table_status;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_type;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_base;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class phrase_tests
{

    function run(test_cleanup $t): void
    {


        // init
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t_trp = new test_triples($t);
        $t_phr = new test_phrases($t);
        $t->name = 'phrase->';
        $t->resource_path = 'db/phrase/';

        // start the test section (ts)
        $ts = 'unit phrase ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $phr = $t_phr->phrase();
        $t->assert_sql_view_create($phr);

        $t->subheader($ts . 'sql read');
        $phr = new phrase($t->usr1);
        $t->assert_sql_by_id($sc, $phr);
        $t->assert_sql_by_name($sc, $phr);
        // e.g. the calculator editor names a triple by the name that the user has given it in the same save
        $test_name = 'a phrase is found by the name that the user has given it';
        $qp = $phr->load_sql_by_name($sc, word_names::MATH);
        $t->assert_text_contains($test_name, $qp->sql, 'u.' . phrase::FLD_NAME . ' = ');
        $test_name = '... and by the standard name only if the user has not given it another name';
        $t->assert_text_contains($test_name, $qp->sql, 'u.' . phrase::FLD_NAME . ' ' . sql::IS_NULL);
        $test_name = '... of the user who loads the phrase, which is the first parameter of the query';
        $par_usr_id = $qp->par[0] ?? null;
        $t->assert($test_name, $par_usr_id, $t->usr1->id());
        $test_name = '... and another user gets the names that he has given';
        $phr_other = new phrase($t->usr2);
        $qp = $phr_other->load_sql_by_name($sc, word_names::MATH);
        $par_usr_id = $qp->par[0] ?? null;
        $t->assert($test_name, $par_usr_id, $t->usr2->id());

        $t->subheader($ts . 'type api');
        $phr = $t_phr->phrase();
        $t->assert_api_json($phr);
        $phr = $t_wrd->word_filled()->phrase();
        $t->assert_api_json($phr);
        $phr = $t_wrd->word_filled()->phrase();
        $phr->include();
        $t->assert_api($phr, 'phrase_word_full');
        $phr = $t_trp->triple_filled_add_name()->phrase();
        $phr->include();
        $t->assert_api($phr, 'phrase_triple_full');
        $phr = $t_phr->phrase();
        $t->assert_api($phr, 'phrase_body');

        $t->subheader($ts . 'api mapper');
        $msg = new user_message();

        // a positive phrase id is a word, so the mapper fills the phrase with the word of the api json
        $test_name = 'the api mapper sets the word of the phrase';
        $phr_map = new phrase($t->usr1);
        $phr_map->api_mapper($t_phr->phrase()->api_json_array([], $msg), $msg);
        $t->assert($test_name, $phr_map->name(), word_names::MATH);
        $msg->reset();

        // a negative phrase id is a triple, so a triple json without the from phrase is reported
        $test_name = 'the api mapper reports a triple phrase without the from phrase';
        $phr_map = new phrase($t->usr1);
        $api_json = [json_fields::ID => -triple_names::MATH_CONST_ID, json_fields::FROM => null];
        $t->assert_false($test_name, $phr_map->api_mapper($api_json, $msg));
        $msg->reset();

        $t->subheader($ts . 'export');

        // a triple phrase exports the fields of the triple e.g. the verb name
        $test_name = 'triple phrase export includes the verb';
        $phr = $t_phr->phrase_filled();
        $exp = $phr->export_json($msg, [], false);
        $t->assert($test_name, $exp[json_fields::EX_VERB], verbs::PART_NAME);
        $msg->reset();

        // negative: a word phrase exports the word, so without a verb
        $test_name = 'word phrase export without verb';
        $phr = $t_phr->phrase();
        $exp = $phr->export_json($msg, [], false);
        $t->assert_key_not_exists($test_name, json_fields::EX_VERB, $exp);
        $msg->reset();

        $t->subheader($ts . 'debug id');

        // the full debug id of a phrase names the user of the phrase object
        $test_name = 'the debug id shows the user';
        $phr = $t_phr->phrase();
        $dsp = $phr->dsp_id();
        $t->assert_text_contains($test_name, $dsp, users::SYSTEM_TEST_NAME);

        // negative: the short debug id leaves out the user
        $test_name = 'the short debug id without user';
        $phr = $t_phr->phrase();
        $dsp = $phr->dsp_id(false);
        $t->assert_text_not_contains($test_name, $dsp, users::SYSTEM_TEST_NAME);

        $t->subheader($ts . 'html frontend');
        $phr = $t_wrd->word()->phrase();
        $t->assert_api_to_ui($phr, new phrase_ui());
        $phr = $t_trp->triple_pi()->phrase();
        $t->assert_api_to_ui($phr, new phrase_ui());

        // check the Postgres query syntax
        $wrd_company = new word($t->usr1);
        $wrd_company->set(word_names::CONST_ID, word_names::COMPANY);
        $sql_name = 'phrase_list_related';
        $file_name = $t->resource_path . $sql_name . test_base::FILE_EXT;
        $created_sql = $phr->sql_list($wrd_company);
        $expected_sql = $t->file($file_name);
        $t->assert_sql($t->name . $sql_name, $created_sql, $expected_sql
        );


        // start the test section (ts)
        $ts = 'unit phrase type ';
        $t->header($ts);

        $t->subheader($ts . 'type api');
        global $sys;
        $phr_typ = $sys->typ_lst->phr_typ->get_by_code_id(phrase_type_shared::PERCENT);
        // an own file, because phrase_type.json is the default type of the api get test
        $t->assert_api($phr_typ, 'phrase_type_percent');

        $t->subheader($ts . 'api mapper');

        // the symbol is the own field of the phrase type mapper e.g. the percent sign
        $test_name = 'the api mapper sets the symbol of the phrase type';
        $phr_typ_map = new phrase_type(phrase_type_shared::PERCENT);
        $phr_typ_map->api_mapper([json_fields::ID => $phr_typ->id(), json_fields::SYMBOL => word_names::PERCENT_SYMBOL], $msg);
        $t->assert($test_name, $phr_typ_map->symbol, word_names::PERCENT_SYMBOL);
        $msg->reset();

        // an empty symbol of the api json is not a symbol, so it leaves the symbol unset
        $test_name = 'the api mapper ignores an empty symbol';
        $phr_typ_map = new phrase_type(phrase_type_shared::PERCENT);
        $phr_typ_map->api_mapper([json_fields::ID => $phr_typ->id(), json_fields::SYMBOL => ''], $msg);
        $t->assert_null($test_name, $phr_typ_map->symbol);
        $msg->reset();


        $t->subheader($ts . 'combined objects like phrases should not be used for im- or export, so not tests is needed. Instead the single objects like word or triple should be im- and exported');

        // start the test section (ts)
        $ts = 'unit dynamic table ';
        $t->header($ts);

        $t->subheader($ts . 'phrase table status sql setup');
        $phr_tbl_sta = new phrase_table_status('');
        $t->assert_sql_table_create($phr_tbl_sta);
        $t->assert_sql_index_create($phr_tbl_sta);

        $t->subheader($ts . 'phrase table sql setup');
        $phr_tbl = new phrase_table('');
        $t->assert_sql_table_create($phr_tbl);
        $t->assert_sql_index_create($phr_tbl);
        $t->assert_sql_foreign_key_create($phr_tbl);

    }

}