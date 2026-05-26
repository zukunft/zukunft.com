<?php

/*

    test/unit/word_tests.php - word unit tests
    ------------------------


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

    Copyright (c) 1995-2024 zukunft.com AG, Zurich
    Heang Lor <heang@zukunft.com>

    http://zukunft.com

*/

namespace Zukunft\ZukunftCom\test\php\unit;

use Zukunft\ZukunftCom\main\php\cfg\const\paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'words.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase as phrase_ui;
use Zukunft\ZukunftCom\main\php\web\phrase\phrase_list as phrase_list_ui;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_tests
{

    function run(test_cleanup $t): void
    {

        global $sys;
        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t->name = 'word->';
        $t->resource_path = 'db/word/';

        // start the test section (ts)
        $ts = 'unit word ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $wrd = $t_wrd->word();
        $t->assert_sql_table_create($wrd);
        $t->assert_sql_index_create($wrd);
        $t->assert_sql_foreign_key_create($wrd);

        $t->subheader($ts . 'sql read');
        $wrd = new word($usr);
        $t->assert_sql_by_id($sc, $wrd);
        $t->assert_sql_by_name($sc, $wrd);
        $this->assert_sql_formula_name($t, $sc, $wrd);

        $t->subheader($ts . 'sql read default and user changes');
        $wrd = new word($usr);
        $wrd->id = words::CONST_ID;
        $t->assert_sql_standard($sc, $wrd);
        $t->assert_sql_not_changed($sc, $wrd);
        $t->assert_sql_user_changes($sc, $wrd);
        $t->assert_sql_changing_users($sc, $wrd);
        $this->assert_sql_view($t, $wrd);

        $t->subheader($ts . 'sql write insert');
        $wrd = new word($usr);
        $wrd->set_name(words::TEST_ADD);
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG]);
        $wrd = $t_wrd->word();
        $t->assert_sql_insert($sc, $wrd);
        $t->assert_sql_insert($sc, $wrd, [sql_type::USER]);
        $t->assert_sql_insert($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $wrd_view = $t_wrd->word_view_set();
        $t->assert_sql_insert($sc, $wrd_view, [sql_type::LOG, sql_type::USER]);
        $wrd_no_view = $t_wrd->word_view_not_4_user();
        $t->assert_sql_save_fields($sc, $wrd_no_view, $wrd_view, [sql_type::LOG, sql_type::USER]);
        $wrd_view = $t_wrd->word_excluded();
        $t->assert_sql_insert($sc, $wrd_view, [sql_type::LOG, sql_type::USER]);
        // the insert log test is already tested by the horizontal test so check if an incomplete word object returns a user message
        $wrd = $t_wrd->word_incomplete();
        $t->assert_sql_insert_fail($sc, $wrd, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $wrd = $t_wrd->word();
        $wrd_renamed = $wrd->cloned(words::TEST_RENAMED);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::USER]);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG, sql_type::USER]);
        $wrd_renamed_admin = $wrd->cloned(words::TEST_RENAMED);
        $wrd_renamed_admin->set_protection_by_code_id(protection_types::ADMIN);
        $t->assert_sql_update($sc, $wrd_renamed_admin, $wrd, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update failed cases e.g. description update');
        $wrd = $t_wrd->word();
        $wrd->description = words::MATH_COM;
        $wrd_updated = $t_wrd->word();
        $wrd_updated->set_user($usr_sys);
        $wrd_updated->plural = words::TEST_RENAMED;
        $wrd_updated->description = words::TEST_RENAMED;
        $wrd_updated->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::TIME);
        $t->assert_sql_update($sc, $wrd_updated, $wrd, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write update of all fields changed');
        $wrd_filled = $t_wrd->word_filled();
        $wrd_renamed->id = $wrd->id();
        $t->assert_sql_update($sc, $wrd_renamed, $wrd_filled, [sql_type::LOG]);

        $t->subheader($ts . 'sql write delete');
        $t->assert_sql_delete($sc, $wrd);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $wrd, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $wrd, [sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'base object handling');
        $wrd = $t_wrd->word_filled();
        $t->assert_reset($wrd);

        $t->subheader($ts . 'api');
        $wrd = $t_wrd->word();
        $t->assert_api_json($wrd);
        $wrd = $t_wrd->word_filled();
        $t->assert_api_json($wrd);
        $wrd->include();
        $t->assert_api($wrd, 'word_full');
        $wrd = $t_wrd->word();
        $t->assert_api($wrd, 'word_body');

        $t->subheader($ts . 'api/word handler reads the ?incl_related URL param into the api_type_list');
        // the api/word/index.php GET handler now opts into api_types::INCL_RELATED only when
        // ?incl_related is truthy on the URL — this keeps single-word fetches cheap by default
        // and lets callers (e.g. the default word view) ask for the related list explicitly.
        // these tests cover the url_array -> api_type_list translation (api_type_list::from_url_array)
        // since reading $_GET inside a function is forbidden by the unit-testability rule
        $base = [api_types::HEADER];
        $with = api_type_list::from_url_array([url_var::INCL_RELATED => '1'], $base);
        $without = api_type_list::from_url_array([], $base);
        $test_name = 'api/word ?incl_related=1 enables api_types::INCL_RELATED';
        $t->assert_true($t->name . $test_name, $with->incl_related());
        $test_name = 'api/word without ?incl_related does NOT enable api_types::INCL_RELATED';
        $t->assert_true($t->name . $test_name, !$without->incl_related());
        $test_name = 'api/word HEADER base flag is preserved in both cases';
        $t->assert_true($t->name . $test_name, $with->use_header() and $without->use_header());

        $t->subheader($ts . 'backend api_json_array emits phrases_related only when INCL_RELATED is set');
        // assign a related phrase list manually so the test stays DB-free; one entry is enough
        // to verify the gating + serialisation (the per-verb load logic is covered separately
        // by the load_phrases_related unit test against the triple_list fixture)
        $wrd = $t_wrd->word_chf();
        $related = new phrase_list($t->usr1);
        $related->add((new test_phrases($t))->phrase_pi());
        $wrd->phrases_related = $related;
        $with_related = new api_type_list([api_types::INCL_RELATED, api_types::TEST_MODE]);
        $without_related = new api_type_list([api_types::TEST_MODE]);
        $vars_with = $wrd->api_json_array($with_related);
        $vars_without = $wrd->api_json_array($without_related);
        $test_name = 'word api_json_array includes phrases_related when INCL_RELATED is set';
        $t->assert_true($t->name . $test_name, array_key_exists(json_fields::PHRASES_RELATED, $vars_with));
        $test_name = 'word api_json_array omits phrases_related without INCL_RELATED';
        $t->assert_true($t->name . $test_name, !array_key_exists(json_fields::PHRASES_RELATED, $vars_without));
        // negative: a word with an empty phrases_related list does not emit the key
        $bare_wrd = $t_wrd->word_chf();
        $bare_wrd->phrases_related = new phrase_list($t->usr1);
        $vars_bare = $bare_wrd->api_json_array($with_related);
        $test_name = 'word api_json_array omits phrases_related when the list is empty';
        $t->assert_true($t->name . $test_name, !array_key_exists(json_fields::PHRASES_RELATED, $vars_bare));

        $t->subheader($ts . 'html frontend');
        $wrd = $t_wrd->word();
        $t->assert_api_to_ui($wrd, new word_ui());

        $t->subheader($ts . 'frontend page title with related phrases is truncated by the per-verb limit');
        // build a Zurich word_ui and a phrase_list with three related triples (City, Canton,
        // Company). With related_limit=2 the title shows "Zurich (City, Canton, ...)" — the
        // "..." links to views::WORD_RELATED_ID for the full grouped-by-verb overview.
        // With related_limit=4 all three fit so the title shows "Zurich (City, Canton, Company)"
        // and no "..." appears. The phrase entries carry short display labels (City, Canton,
        // Company) so the renderer's `$phr->name()` lookup yields the user-facing word and
        // its `$phr->id()` points at the connecting triple so the link targets the triple.
        $wrd_ui_render = new word_ui();
        $wrd_ui_render->set_name(words::ZH);
        $wrd_ui_render->set_id(words::ZH_ID);
        $wrd_ui_render->phrases_related = self::related_zurich_phrases();
        $form = new system_form();
        $title_short = $form->title_of_named_with_edit_link($wrd_ui_render, 2);
        $test_name = 'title shows City link for related limit 2';
        $t->assert_true($t->name . $test_name,
            str_contains($title_short, '<a href="/http/view.php?m=' . views::PHRASE_ID . '&id='
                . triples::CITY_ZH_ID . '">City</a>'));
        $test_name = 'title shows Canton link for related limit 2';
        $t->assert_true($t->name . $test_name,
            str_contains($title_short, '<a href="/http/view.php?m=' . views::PHRASE_ID . '&id='
                . triples::CANTON_ZURICH_ID . '">Canton</a>'));
        $test_name = 'title shows "..." linking to WORD_RELATED when count > limit';
        $t->assert_true($t->name . $test_name,
            str_contains($title_short, '<a href="/http/view.php?m=' . views::WORD_RELATED_ID . '&id='
                . words::ZH_ID . '">...</a>'));
        $test_name = 'title with limit 2 hides the third (Company) entry';
        $t->assert_true($t->name . $test_name, !str_contains($title_short, '>Company</a>'));

        $title_full = $form->title_of_named_with_edit_link($wrd_ui_render, 4);
        $test_name = 'title with limit 4 includes the Company link';
        $t->assert_true($t->name . $test_name, str_contains($title_full, '>Company</a>'));
        $test_name = 'title with limit 4 omits the "..." overflow indicator';
        $t->assert_true($t->name . $test_name, !str_contains($title_full, '>...</a>'));

        $t->subheader($ts . 'im- and export');
        // TODO check that all objects have a im and export test
        $t->assert_ex_and_import($t_wrd->word(), $usr_sys);
        $t->assert_ex_and_import($t_wrd->word_filled(), $usr_sys);
        $json_file = 'unit/word/second.json';
        $t->assert_json_file(new word($usr), $json_file);

        $t->subheader($ts . 'sync and fill');
        $test_name = 'check if the word fill function set all database fields';
        $usr_msg = new user_message();
        $wrd_imp = $t_wrd->word_filled();
        $wrd_db = new word($wrd_imp->get_user());
        $wrd_db->fill($wrd_imp, $usr_sys);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_imp, $usr_msg)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_db_fld_names, []);
        $test_name = 'check if importing of just the admin protection does overwrite the protection in the database';
        $wrd_db = $t_wrd->word_filled();
        $wrd_imp = $t_wrd->word();
        $wrd_db_after = clone $wrd_db;
        $wrd_db_after->fill($wrd_imp, $usr_sys);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_db_after, $usr_msg)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_db_fld_names, []);
        $test_name = 'check if importing just the word name does not overwrite any database fields';
        $wrd_db = $t_wrd->word_filled();
        $wrd_imp = $t_wrd->word_name_only();
        $wrd_db_after = clone $wrd_db;
        $wrd_db_after->fill($wrd_imp, $usr_sys);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_db_after, $usr_msg)->names();
        $t->assert($t->name . 'fill: ' . $test_name, $non_db_fld_names, []);
        $test_name = 'check if the word id is filled up';
        $wrd_imp = $t_wrd->word();
        $wrd_imp->id = 0;
        $wrd_db = $t_wrd->word();
        $wrd_imp->fill($wrd_db, $usr_sys);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_imp, $usr_msg)->names();
        $t->assert($t->name . 'fill id: ' . $test_name, $non_db_fld_names, []);
        $test_name = 'check if description can be set to an empty string';
        $wrd_imp = $t_wrd->word();
        $wrd_imp->set_description('');
        $wrd_db = $t_wrd->word();
        $wrd_db->fill($wrd_imp, $usr_sys);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_imp, $usr_msg)->names();
        $t->assert($t->name . 'fill id: ' . $test_name, $non_db_fld_names, [sql_db::FLD_DESCRIPTION]);
        $test_name = 'check if the code id cannot be set by normal user';
        $wrd_imp = $t_wrd->word();
        $wrd_imp->set_code_id('test code id', $usr_sys);
        $wrd_db = $t_wrd->word();
        $wrd_db->fill($wrd_imp, $usr);
        $non_db_fld_names = $wrd_db->db_fields_changed($wrd_imp, $usr_msg)->names();
        $t->assert($t->name . 'fill id: ' . $test_name, $non_db_fld_names, [sql_db::FLD_CODE_ID]);

        $test_name = 'check if database would not be updated if only the name is given in import';
        $in_wrd = $t_wrd->word_name_only();
        $db_wrd = $t_wrd->word_filled();
        $t->assert($t->name . 'needs_db_update ' . $test_name, $in_wrd->needs_db_update($db_wrd), false);

    }

    /**
     * check the load SQL statements creation to get the word corresponding to the formula name
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param sql_creator $sc does not need to be connected to a real database
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_formula_name(test_cleanup $t, sql_creator $sc, word $wrd): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $wrd->load_sql_by_formula_name($sc, formulas::SCALE_TO_SEC);
        $result = $t->assert_qp($qp, $sc->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $sc->reset(sql_db::MYSQL);
            $qp = $wrd->load_sql_by_formula_name($sc, formulas::SCALE_TO_SEC);
            $t->assert_qp($qp, $sc->db_type);
        }
    }

    /**
     * check the load SQL statements creation to get the view
     *
     * @param test_cleanup $t the testing object with the error counter
     * @param word $wrd the user sandbox object e.g. a word
     * @return void true if all tests are fine
     */
    private function assert_sql_view(test_cleanup $t, word $wrd): void
    {
        $db_con = new sql_db();

        // check the Postgres query syntax
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $wrd->view_sql($db_con);
        $result = $t->assert_qp($qp, $db_con->db_type);

        // ... and check the MySQL query syntax
        if ($result) {
            $db_con->db_type = sql_db::MYSQL;
            $qp = $wrd->view_sql($db_con);
            $t->assert_qp($qp, $db_con->db_type);
        }
    }

    /**
     * build a frontend phrase_list with three related-phrase entries for the Zurich title test:
     * City (links to triple "City of Zurich"), Canton (links to "Canton Zurich"), and
     * Company (links to "Zurich Insurance"). Each entry wraps a word_ui whose name is the
     * short display label (so the renderer's $phr->name() yields "City"/"Canton"/"Company")
     * and whose id is the connecting triple's id (so the renderer's $phr->id() points the
     * link at the triple's detail view via views::PHRASE_ID)
     */
    private static function related_zurich_phrases(): phrase_list_ui
    {
        $lst = new phrase_list_ui();
        $lst->add(self::related_phrase(triples::CITY_ZH_ID, 'City'));
        $lst->add(self::related_phrase(triples::CANTON_ZURICH_ID, 'Canton'));
        $lst->add(self::related_phrase(triples::COMPANY_ZURICH_ID, 'Company'));
        return $lst;
    }

    private static function related_phrase(int $id, string $display): phrase_ui
    {
        $wrd = new word_ui();
        $wrd->id = $id;
        $wrd->set_name($display);
        $phr = new phrase_ui();
        $phr->set_obj($wrd);
        return $phr;
    }

}