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
use Zukunft\ZukunftCom\test\php\const\paths as test_paths;
use Zukunft\ZukunftCom\main\php\web\const\paths as html_paths;

include_once paths::DB . 'sql_db.php';
include_once paths::MODEL_SANDBOX . 'sandbox.php';
include_once paths::MODEL_WORD . 'word.php';
include_once paths::MODEL_WORD . 'word_db.php';
include_once paths::MODEL_WORD . 'triple_list.php';
include_once html_paths::WORD . 'word.php';
include_once paths::SHARED_TYPES . 'phrase_types.php';
include_once paths::SHARED_CONST . 'triples.php';
include_once paths::SHARED_CONST . 'words.php';
include_once test_paths::CONST . 'word_names.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type_list;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase_list;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_list;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\const\icons;
use Zukunft\ZukunftCom\main\php\web\formula\formula;
use Zukunft\ZukunftCom\main\php\web\html\styles;
use Zukunft\ZukunftCom\main\php\web\types\type_lists;
use Zukunft\ZukunftCom\main\php\web\word\word as word_ui;
use Zukunft\ZukunftCom\main\php\shared\const\formulas;
use Zukunft\ZukunftCom\main\php\shared\const\triples;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\const\words;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\main\php\shared\types\api_type_list;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types as phrase_type_shared;
use Zukunft\ZukunftCom\main\php\shared\types\verbs;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_phrases;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class word_tests
{

    function run(test_cleanup $t, type_lists $cfg): void
    {

        global $sys;
        global $usr;
        global $usr_sys;

        // init
        $sc = new sql_creator();
        $t_wrd = new test_words($t);
        $t_phr = new test_phrases($t);
        $lib = new library();
        $sfm = new system_form();
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
        $wrd->id = word_names::CONST_ID;
        $t->assert_sql_standard($sc, $wrd);
        $t->assert_sql_not_changed($sc, $wrd);
        $t->assert_sql_user_changes($sc, $wrd);
        $t->assert_sql_changing_users($sc, $wrd);
        $this->assert_sql_view($t, $wrd);

        $t->subheader($ts . 'sql write insert');
        $wrd = new word($usr);
        $wrd->set_name(word_names::TEST_ADD);
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
        $wrd_renamed = $wrd->cloned(word_names::TEST_RENAMED);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::USER]);
        $t->assert_sql_update($sc, $wrd_renamed, $wrd, [sql_type::LOG, sql_type::USER]);
        // the changed protection level is part of the update statement
        $wrd_renamed_user_protected = $wrd->cloned(word_names::TEST_RENAMED);
        $wrd_renamed_user_protected->set_protection_by_code_id(protection_types::USER);
        $t->assert_sql_update($sc, $wrd_renamed_user_protected, $wrd, [sql_type::LOG]);

        $test_name = 'no update statement is created if no field has changed';
        $usr_msg = new user_message();
        $wrd_same = clone $wrd;
        $qp = $wrd_same->sql_update($sc, $wrd, $usr_msg);
        $t->assert_true($t->name . $test_name, $qp === null);
        $test_name = '... also for the update statement with logging';
        $qp = $wrd_same->sql_update($sc, $wrd, $usr_msg, new sql_type_list([sql_type::LOG]));
        $t->assert_true($t->name . $test_name, $qp === null);

        $t->subheader($ts . 'sql write update failed cases e.g. description update');
        $wrd = $t_wrd->word();
        $wrd->description = word_names::MATH_COM;
        $wrd_updated = $t_wrd->word();
        $wrd_updated->set_user($usr_sys);
        $wrd_updated->plural = word_names::TEST_RENAMED;
        $wrd_updated->description = word_names::TEST_RENAMED;
        $wrd_updated->type_id = $sys->typ_lst->phr_typ->id(phrase_type_shared::TIME);
        $t->assert_sql_update($sc, $wrd_updated, $wrd, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql write update of all fields changed');
        $wrd_filled = $t_wrd->word_filled();
        $wrd_renamed->id = $wrd->id();
        $t->assert_sql_update($sc, $wrd_renamed, $wrd_filled, [sql_type::LOG]);

        $t->subheader($ts . 'protection');
        $test_name = 'a re-import without protection keeps the database protection';
        $usr_msg = new user_message();
        $wrd_db = $t_wrd->word();
        $wrd_imp = $t_wrd->word();
        $wrd_imp->description = word_names::TEST_RENAMED;
        $wrd_imp->set_protection_id(null);
        $t->assert_false($test_name, in_array(sandbox::FLD_PROTECT, $wrd_imp->db_fields_changed($wrd_db, $usr_msg)->names()));
        $test_name = 'an explicit lower protection is part of the update fields';
        $wrd_imp->set_protection_by_code_id(protection_types::NO_PROTECT);
        $t->assert_true($test_name, in_array(sandbox::FLD_PROTECT, $wrd_imp->db_fields_changed($wrd_db, $usr_msg)->names()));
        $test_name = 'a normal user cannot reduce the protection level';
        $wrd_imp->check_protection_change($wrd_db, $t->usr_normal, $usr_msg);
        $t->assert($test_name, $wrd_imp->protection_id(), $wrd_db->protection_id());
        $test_name = 'the denied reduction is reported to the user';
        $t->assert_text_contains($test_name, $usr_msg->all_message_text(), word_names::MATH);
        $test_name = 'an admin user can reduce the protection level';
        $usr_msg = new user_message();
        $wrd_imp = $t_wrd->word();
        $wrd_imp->set_protection_by_code_id(protection_types::NO_PROTECT);
        $wrd_imp->check_protection_change($wrd_db, $t->usr_admin, $usr_msg);
        $t->assert($test_name, $wrd_imp->protection_id(), $sys->typ_lst->ptc_typ->id(protection_types::NO_PROTECT));
        $test_name = 'the admin reduction is not reported';
        $t->assert($test_name, $usr_msg->all_message_text(), '');

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

        $t->subheader($ts . 'type check');
        $test_name = 'a word with the scaling type is a scaling word';
        $t->assert_true($test_name, $t_wrd->word_mio()->is_scaling());
        $test_name = 'a word with the hidden scaling type is a scaling word';
        $t->assert_true($test_name, $t_wrd->word_one()->is_scaling());
        $test_name = 'a word without a type is not a scaling word';
        $t->assert_false($test_name, $t_wrd->word_mio_unscaled()->is_scaling());
        $test_name = 'the phrase of a scaling word is a scaling phrase';
        $t->assert_true($test_name, $t_wrd->word_mio()->phrase()->is_scaling());
        $test_name = 'the phrase of a word without a type is not a scaling phrase';
        $t->assert_false($test_name, $t_wrd->word_mio_unscaled()->phrase()->is_scaling());

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

        $t->subheader($ts . 'related phrase selection by impact');
        // the most relevant related phrases e.g. the stocks with the highest
        // market capitalisation are kept within the per verb limit and shown first
        $t_trp = new test_triples($t);
        $wrd = $t_wrd->word();
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->add($t_trp->vestas_company());
        $trp_lst->add($t_trp->company_zurich_market_cap());
        $trp_lst->add($t_trp->abb_company());
        $test_name = 'the stocks are kept with the highest market capitalisation first';
        $phr_lst = $wrd->select_phrases_related($trp_lst, 2);
        $target = implode(', ', [triples::COMPANY_ABB, triples::COMPANY_ZURICH, triples::COMPANY_VESTAS]);
        $t->assert($test_name, implode(', ', $phr_lst->names()), $target);
        $test_name = 'the per verb limit keeps the most relevant stocks plus one to indicate the overflow';
        $phr_lst = $wrd->select_phrases_related($trp_lst, 1);
        $t->assert($test_name, implode(', ', $phr_lst->names()), triples::COMPANY_ABB . ', ' . triples::COMPANY_ZURICH);
        $test_name = 'triples without an impact are kept in the order of the database id';
        $trp_lst = new triple_list($t->usr1);
        $trp_lst->add($t_trp->triple_ge());
        $trp_lst->add($t_trp->triple_bern());
        $phr_lst = $wrd->select_phrases_related($trp_lst, 2);
        $t->assert($test_name, implode(', ', $phr_lst->names()), triples::CITY_BE . ', ' . triples::CITY_GE);

        $t->subheader($ts . 'html frontend');
        $wrd = $t_wrd->word();
        $t->assert_api_to_ui($wrd, new word_ui());


        $t->subheader($ts . 'subtitle with phrase limit');

        $test_name = '"Zurich" subtitle has city when limit is at 2';
        $form = new system_form();
        $wrd = $t_wrd->zh_ui();
        $wrd->phr_lst = $t_phr->list_ui();
        $txt = $form->title_named($wrd, 2);
        $lnk = word_names::CITY_ID . '">' . word_names::CITY . '</a>';
        $t->assert_text_contains($test_name, $txt, $lnk);
        $test_name = '... and canton';
        $lnk = word_names::CANTON_ID . '">' . word_names::CANTON . '</a>';
        $t->assert_text_contains($test_name, $txt, $lnk);
        $test_name = '... and "..." for more';
        $lnk = views::WORD_RELATED_ID . '&id=' . word_names::ZH_ID . '">...</a>';
        $t->assert_text_contains($test_name, $txt, $lnk);
        $test_name = '... but company is NOT';
        $t->assert_text_not_contains($test_name, $txt, word_names::COMPANY);

        $test_name = 'company is part if limit is higher';
        $txt = $form->title_named($wrd, 4);
        $t->assert_text_contains($test_name, $txt, word_names::COMPANY);
        $test_name = '... and "..." for more is goner';
        $t->assert_text_not_contains($test_name, $txt, '>...</a>');

        $test_name = 'verb of "CHF is symbol for Swiss Frank"';
        $wrd = $t_wrd->chf_ui();
        $wrd->phr_lst = $t_phr->list_ui();
        $txt = $form->title_named($wrd);
        $t->assert_text_contains($test_name, $txt, verbs::SYMBOL_NAME);
        $test_name = 'link of "CHF is symbol for Swiss Frank" with the description as tooltip';
        $lnk = '<a href="/http/view.php?m=' . views::WORD_ID
            . '&id=' . word_names::SWISS_FRANC_ID . '" title="' . word_names::SWISS_FRANC_COM . '">' . word_names::SWISS_FRANC . '</a>';
        $t->assert_text_contains($test_name, $txt, $lnk);
        $test_name = 'name of "CHF is symbol for Swiss Frank';
        $t->assert_text_contains($test_name, $txt, '>CHF</h4>');

        $test_name = 'moves the edit icon onto the subtitle line';
        $t->assert_text_order($test_name, $txt, '</h4>', icons::EDIT);

        $test_name = 'reverse priority "Zurich is" subtitle has company when company is relevant';
        $wrd = $t_wrd->zh_ui();
        $wrd->phr_lst = $t_phr->list_zh_impact_ui();
        $txt = $form->title_named($wrd);
        $t->assert_text_contains($test_name, $txt, '>' . word_names::COMPANY . '</a>');
        $test_name = '... and still canton';
        $t->assert_text_contains($test_name, $txt, '>' . word_names::CANTON . '</a>');
        $test_name = '... without a tooltip because canton has no description';
        $t->assert_text_contains($test_name, $txt, '&id=' . word_names::CANTON_ID . '">' . word_names::CANTON . '</a>');
        $test_name = '... but city NOT';
        $t->assert_text_not_contains($test_name, $txt, '>' . word_names::CITY . '</a>');

        $test_name = 'if there is no subtitle the edit icon is in the same line';
        $wrd = $t_wrd->chf_ui();
        $wrd->phr_lst = $t_phr->list_zh_ui();
        $txt = $form->title_named($wrd);
        $t->assert_text_contains($test_name, $txt, 'fas fa-edit');

        $test_name = 'category_html for CHF emits the "is symbol for" verb verbatim';
        $wrd = $t_wrd->swiss_franc_ui();
        $wrd->phr_lst = $t_phr->list_ui();
        $txt = $form->title_named($wrd);
        $t->assert_text_not_contains($test_name, $txt, words::CHF);


        $t->subheader($ts . 'class to type list');

        $test_name = 'word returns the phrase type list';
        $t->assert_true($test_name, $cfg->class_to_type_list(word_ui::class) === $cfg->phr_typ);
        $test_name = 'formula returns the formula type list';
        $t->assert_true($test_name, $cfg->class_to_type_list(formula::class) === $cfg->frm_typ);
        // a class without a type list returns null and logs an error on purpose;
        $test_name = 'type_lists->class_to_type_list returns null for a class without a type list';
        $t->assert_true($test_name, $cfg->class_to_type_list('class_without_a_type_list') === null);


        $t->subheader($ts . 'title with subtitle');

        $test_name = 'shows the non-default type';
        $measure_word = new word_ui($t_wrd->hz()->api_json());
        $type_name = $cfg->phr_typ->name($measure_word->type_id());
        $t->assert_text_contains($test_name, $t->dsp_title_named_edit($measure_word), $type_name);
        $test_name = '.. but the type name of a measure word is not shown for an unrelated word';
        $t->assert_text_not_contains($test_name, $t->dsp_title_named_edit($wrd), $type_name);
        $test_name = 'shows the object name';
        $title = $t->dsp_title_named_edit($wrd);
        $t->assert_text_contains($test_name, $title, $wrd->name());
        $test_name = 'wraps the heading in the heading-line div';
        $t->assert_text_contains($test_name, $title, styles::HEADING_LINE);
        $test_name = 'adds a rename edit link';
        $t->assert_text_contains($test_name, $title, icons::EDIT);
        $test_name = 'wraps a non-default type in a subtitle';
        $t->assert_text_contains($test_name, $t->dsp_title_named_edit($measure_word), styles::SUBTITLE);
        $test_name = 'all optional subtiles';
        $title = $sfm->title_named($t_wrd->zh_full_ui());
        $target = word_names::ZH . ' <' . icons::EDIT
            . '> (' . verbs::IS_NAME . ' ' . word_names::CITY . ', ' . word_names::CANTON . ', ... / '
            . phrase_types::MEASURE_NAME . ' / '
            . share_types::PERSONAL_NAME . ', ' . protection_types::ADMIN_NAME . ')';
        $t->assert($test_name, $lib->html_to_text($title), $target);


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

        // TODO Prio 1 review
        /*
        $test_name = 'a word json without the phrase type keeps the type empty';
        $wrd_new = new word($usr);
        $wrd_new->import_mapper([json_fields::NAME => word_names::MATH], $usr_msg);
        $t->assert_true($t->name . $test_name, $wrd_new->type_id() === null);

        $test_name = 'a word without phrase type never overwrites the type in the database';
        $db_wrd = $t_wrd->word_filled();
        $in_wrd = clone $db_wrd;
        $in_wrd->type_id = null;
        $non_db_fld_names = $in_wrd->db_fields_changed($db_wrd, $usr_msg)->names();
        $t->assert($t->name . $test_name, $non_db_fld_names, []);

        $test_name = '... but a changed phrase type is written to the database';
        $in_wrd->set_type(phrase_type_shared::SCALING_HIDDEN, $usr_sys);
        $non_db_fld_names = $in_wrd->db_fields_changed($db_wrd, $usr_msg)->names();
        $t->assert($t->name . $test_name, $non_db_fld_names, [phrase::FLD_TYPE]);

        $test_name = '... and a null phrase type resets a user specific type to the standard';
        $in_wrd->type_id = null;
        $non_db_fld_names = $in_wrd->db_fields_changed(
            $db_wrd, $usr_msg, new sql_type_list([sql_type::USER]))->names();
        $t->assert($t->name . $test_name, $non_db_fld_names, [phrase::FLD_TYPE]);
        */
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

}