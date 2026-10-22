<?php

/*

    test/unit/ref.php - unit testing of the reference  functions
    -----------------
  

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

include_once paths::MODEL_USER . 'user_message.php';
include_once paths::SHARED_CONST . 'impacts.php';
include_once paths::SHARED_CONST . 'refs.php';
include_once paths::SHARED_CONST . 'sources.php';
include_once paths::SHARED_CONST . 'views.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED . 'json_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_list;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\ref_type_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\views;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\json_fields;
use Zukunft\ZukunftCom\main\php\shared\types\api_types;
use Zukunft\ZukunftCom\main\php\shared\types\ref_types;
use Zukunft\ZukunftCom\main\php\shared\types\share_types;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\ref\ref as ref_ui;
use Zukunft\ZukunftCom\main\php\shared\const\impacts;
use Zukunft\ZukunftCom\main\php\shared\const\refs;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_const;
use Zukunft\ZukunftCom\test\php\create\test_refs;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_types;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use DateTime;

class ref_tests
{
    function run(test_cleanup $t): void
    {

        // TODO Prio 1 use the users of $t->env->usr_... instead

        // init for reference
        $sc = new sql_creator();
        $t_ref = new test_refs($t);
        $t_typ = new test_types($t);
        $t->name = 'ref->';
        $t->resource_path = 'db/ref/';

        // start the test section (ts)
        $ts = 'unit reference ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $ref = new ref($t->usr1);
        $t->assert_sql_table_create($ref);
        $t->assert_sql_index_create($ref);
        $t->assert_sql_foreign_key_create($ref);

        $t->subheader($ts . 'sql read');
        $t->assert_sql_by_id($sc, $ref);
        $t->assert_sql_by_link($sc, $ref);
        $this->assert_sql_link_ids($t, $sc, $ref);
        $this->assert_sql_ex_key($t, $sc, $ref);

        $t->subheader($ts . 'sql read standard and user changes by id');
        $ref = new ref($t->usr1);
        $ref->id = 3;
        $t->assert_sql_standard($sc, $ref);
        $ref = $t_ref->reference();
        $t->assert_sql_standard_by_type_link($sc, $ref);

        $t->subheader($ts . 'sql read all type');
        $ref_type_list = new ref_type_list();
        $t->assert_sql_all($sc, $ref_type_list);

        // a ref list is a sandbox list like the source list, so it builds its own list queries
        // instead of loading a single ref per id (see change_log_list::load_changed_objects)
        $t->subheader($ts . 'list sql read');
        $ref_lst = new ref_list($t->usr1);
        $sc->reset(sql_db::POSTGRES);
        $t->assert_qp($ref_lst->load_sql_by_ids($sc, [1, 2]), sql_db::POSTGRES);
        $sc->reset(sql_db::MYSQL);
        $t->assert_qp($ref_lst->load_sql_by_ids($sc, [1, 2]), sql_db::MYSQL);
        $sc->reset(sql_db::POSTGRES);
        $t->assert_qp($ref_lst->load_sql_by_phr_id($sc, 1), sql_db::POSTGRES);
        $sc->reset(sql_db::MYSQL);
        $t->assert_qp($ref_lst->load_sql_by_phr_id($sc, 1), sql_db::MYSQL);

        $t->subheader($ts . 'sql write insert');
        $ref = $t_ref->reference();
        $t->assert_sql_insert($sc, $ref);
        $ref_usr = $t_ref->reference_user();
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::USER]);
        $t->assert_sql_insert($sc, $ref_usr, [sql_type::LOG, sql_type::USER]);
        $ref_filled = $t_ref->ref_filled();
        $t->assert_sql_insert($sc, $ref_filled, [sql_type::LOG]);
        $ref_filled_usr = $t_ref->ref_filled_user();
        $t->assert_sql_insert($sc, $ref_filled_usr, [sql_type::LOG, sql_type::USER]);
        $ref = $t_ref->reference_incomplete();
        $t->assert_sql_insert_fail($sc, $ref, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $ref = $t_ref->reference_change();
        $ref_changed = $ref->cloned_linked(refs::CHANGE_NEW_KEY);
        $ref_changed->description = null;
        $t->assert_sql_update($sc, $ref_changed, $ref);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::USER]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG]);
        $t->assert_sql_update($sc, $ref_changed, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'sql delete');
        // TODO Prio 1 log deleting the link by logging the change of the external link to empty
        $t->assert_sql_delete($sc, $ref);
        $t->assert_sql_delete($sc, $ref, [sql_type::LOG, sql_type::USER]);

        $t->subheader($ts . 'base object handling');
        $ref = $t_ref->ref_filled();
        $t->assert_reset($ref);

        $t->subheader($ts . 'read access (share)');
        // a non-public reference must not be disclosed to another user via a related-reference list
        // (idor); ref extends sandbox (is_readable_by) and ref_list has its own filter_readable_by
        global $sys;
        $private_id = $sys->typ_lst->shr_typ->id(share_types::PRIVATE);
        $ref_priv = $t_ref->reference();
        $ref_priv->set_owner_id($t->usr1->id);
        $ref_priv->set_share_id($private_id);
        $test_name = 'the owner may read their own private reference';
        $t->assert_true($test_name, $ref_priv->is_readable_by($t->usr1));
        $test_name = 'another user may not read a private reference';
        $t->assert_false($test_name, $ref_priv->is_readable_by($t->usr2));
        $ref_pub = $t_ref->reference();
        $ref_pub->set_owner_id($t->usr1->id);
        $ref_lst = new ref_list($t->usr2);
        $ref_lst->set_lst([$ref_priv, $ref_pub]);
        $ref_lst->filter_readable_by($t->usr2);
        $test_name = 'the readable filter keeps only the public reference for another user';
        $t->assert($test_name, count($ref_lst->lst()), 1);

        $t->subheader($ts . 'save');
        // the type is part of the prime index, so a reference without a type is refused before any
        // database access with a message instead of failing in the duplicate check
        $test_name = 'a reference without a type is not saved';
        $msg = new user_message();
        $ref_no_type = $t_ref->reference();
        $ref_no_type->set_predicate_id(null);
        $t->assert_false($test_name, $ref_no_type->save($msg));
        $t->assert_false($test_name, $msg->is_ok());

        $t->subheader($ts . 'api');
        $ref = $t_ref->reference1();
        $t->assert_api_json($ref);
        $ref = $t_ref->reference_plus();
        $t->assert_api($ref);

        $t->subheader($ts . 'api mapper');

        // the frontend sends the reference as api json, so the mapper must take the external key
        $test_name = 'the api mapper sets the external key of a reference';
        $msg = new user_message($t->usr1);
        $ref_map = new ref($t->usr1);
        $ref_map->api_mapper($t_ref->reference()->api_json_array([], $msg), $msg);
        $t->assert($test_name, $ref_map->get_external_key(), refs::PI_KEY);
        $msg->reset();

        $test_name = 'the api mapper ignores an empty external key';
        $ref_no_key = new ref($t->usr1);
        $api_json = [json_fields::ID => refs::PI_ID, json_fields::EXTERNAL_KEY => ''];
        $ref_no_key->api_mapper($api_json, $msg);
        $ext_key = $ref_no_key->get_external_key();
        $t->assert_null($test_name, $ext_key);
        $msg->reset();

        // an empty phrase id of e.g. an add form must not link the reference to a phrase
        $test_name = 'the api mapper ignores a phrase id of zero';
        $ref_map->api_mapper([json_fields::ID => refs::PI_ID, json_fields::PHRASE_ID => 0], $msg);
        $t->assert($test_name, $ref_map->phrase_id(), 0);
        $msg->reset();

        // the base url of a reference type is part of the pod setup, so a trusted source sets it
        $test_name = 'the trusted api mapper sets the base url of a reference type';
        $ref_typ = new ref_type();
        $ref_typ->api_mapper([json_fields::ID => ref_types::WIKIDATA_ID, json_fields::URL => refs::PI_URL], $msg, true);
        $t->assert($test_name, $ref_typ->url, refs::PI_URL);
        $msg->reset();

        // ... but never an api message of a frontend
        $test_name = 'the untrusted api mapper ignores the base url of a reference type';
        $ref_typ = new ref_type();
        $ref_typ->api_mapper([json_fields::ID => ref_types::WIKIDATA_ID, json_fields::URL => refs::PI_URL], $msg);
        $t->assert_null($test_name, $ref_typ->url);
        $msg->reset();

        $t->subheader($ts . 'api json array');
        // a reference sends the url and the external key
        $test_name = 'the url of a reference';
        $ref = $t_ref->reference_plus();
        $vars = $ref->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::URL] ?? '', refs::PI_URL);
        $t->assert($test_name, $vars[json_fields::EXTERNAL_KEY] ?? '', refs::PI_KEY);
        $msg->reset();

        // negative: a reference without a url sends an empty url
        $test_name = 'no url of a reference without url';
        $ref = $t_ref->reference();
        $vars = $ref->api_json_array([], $msg);
        $t->assert_null($test_name, $vars[json_fields::URL] ?? null);
        $msg->reset();

        // the plain api message names the phrase by its id
        $test_name = 'the phrase id of a reference';
        $ref = $t_ref->reference();
        $vars = $ref->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::PHRASE_ID] ?? 0, word_names::PI_ID);
        $t->assert_false($test_name, array_key_exists(json_fields::PHRASES, $vars));
        $msg->reset();

        // negative: with the phrase flag the phrase is nested instead of its id
        $test_name = 'the nested phrase of a reference';
        $ref = $t_ref->reference();
        $vars = $ref->api_json_array([api_types::INCL_PHRASES, api_types::TEST_MODE], $msg);
        $t->assert($test_name, $vars[json_fields::PHRASES][0][json_fields::NAME] ?? '', word_names::PI);
        $t->assert_false($test_name, array_key_exists(json_fields::PHRASE_ID, $vars));
        $msg->reset();

        // the source of a reference is sent by its id
        $test_name = 'the source id of a reference';
        $ref = $t_ref->reference_plus();
        $vars = $ref->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::SOURCE_ID] ?? 0, sources::WIKIDATA_ID);
        $msg->reset();

        // negative: a reference without a source sends no source id
        $test_name = 'no source id of a reference without source';
        $ref = $t_ref->reference();
        $vars = $ref->api_json_array([], $msg);
        $t->assert_false($test_name, array_key_exists(json_fields::SOURCE_ID, $vars));
        $msg->reset();

        // the cached impact is sent if it has been calculated
        $test_name = 'the impact of a reference';
        $ref = $t_ref->reference();
        $ref->impact = impacts::MAX;
        $vars = $ref->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::IMPACT] ?? 0, impacts::MAX);
        $msg->reset();

        // negative: a reference without an impact sends no impact
        $test_name = 'no impact of a reference without impact';
        $ref = $t_ref->reference();
        $vars = $ref->api_json_array([], $msg);
        $t->assert_false($test_name, array_key_exists(json_fields::IMPACT, $vars));
        $msg->reset();

        // a reference list sends one entry per reference
        $test_name = 'one entry per reference of a list';
        $ref_lst = $t_ref->ref_list_math();
        $vars = $ref_lst->api_json_array([], $msg);
        $t->assert($test_name, count($vars), 2);
        $t->assert($test_name, $vars[0][json_fields::ID] ?? 0, refs::PI_ID);
        $msg->reset();

        // negative: an empty reference list sends an empty list
        $test_name = 'an empty reference list';
        $ref_lst = $t_ref->list_empty();
        $vars = $ref_lst->api_json_array([], $msg);
        $t->assert($test_name, $vars, []);
        $msg->reset();

        // a reference type sends its name, code id and base url
        $test_name = 'the reference type';
        $ref_typ = $t_typ->ref_type();
        $ref_typ->url = refs::PI_URL;
        $vars = $ref_typ->api_json_array([], $msg);
        $t->assert($test_name, $vars[json_fields::CODE_ID] ?? '', ref_types::WIKIDATA);
        $t->assert($test_name, $vars[json_fields::URL] ?? '', refs::PI_URL);
        $msg->reset();

        // negative: a reference type without a base url sends an empty url
        $test_name = 'no url of a reference type without url';
        $ref_typ = $t_typ->ref_type();
        $vars = $ref_typ->api_json_array([], $msg);
        $t->assert_null($test_name, $vars[json_fields::URL] ?? null);
        $msg->reset();

        $t->subheader($ts . 'frontend');
        $ref = $t_ref->reference_plus();
        $t->assert_api_to_ui($ref, new ref_ui());

        // the ref edit view shows the last update time and the system calculated impact as
        // display only info, because the user can never change these two db fields
        global $ui_sys, $mtr;
        $form = new system_form();
        $ref_ui = $t_ref->ref_info_ui();
        $test_name = 'the ref edit view shows the time of the last update';
        $t->assert_text_contains($test_name, $form->show_last_update($ref_ui),
            date_format(new DateTime(test_const::DUMMY_DATETIME), $ui_sys->cfg->date_time_format()));
        $test_name = 'the ref edit view shows the impact as read only text';
        $t->assert_text_contains($test_name, $form->show_impact($ref_ui), (string)impacts::MAX);
        // the ref default page shows the linked phrase, the type, the external key, the url
        // and the source, each behind the label of the matching form field
        $test_name = 'the ref page links the phrase the reference belongs to';
        $t->assert_text_contains($test_name, $form->show_ref_phrase($ref_ui), word_names::PI);
        $test_name = 'the ref page shows the reference type behind its label';
        $t->assert_text_contains($test_name, $form->show_ref_type($ref_ui), ref_types::WIKIDATA);
        $test_name = 'the ref page shows the external key behind its label';
        $t->assert_text_contains($test_name, $form->show_ref_key($ref_ui), refs::PI_KEY);
        $test_name = 'the ref page shows the url as a link to the external page';
        $t->assert_text_contains($test_name, $form->show_ref_url($ref_ui),
            '<a href="' . refs::PI_URL . '">');
        $test_name = 'the ref page links the source of the reference';
        $t->assert_text_contains($test_name, $form->show_ref_source($ref_ui), sources::SIB);
        // the last update and the impact are written by the system, so a reference that has
        // never been updated or ranked shows no lonely labels; unlike a user-settable field
        // there is nothing the user could fill in behind them
        $ref_plain = new ref_ui($t_ref->reference()->api_json([api_types::TEST_MODE]));
        $test_name = 'a never updated ref shows no last update line';
        $t->assert($test_name, $form->show_last_update($ref_plain), '');
        $test_name = 'a not yet ranked ref shows no impact line';
        $t->assert($test_name, $form->show_impact($ref_plain), '');
        // negative: the user-settable fields keep their label if the field is not set, so that the
        // user sees which field is empty; a new reference of an add form has nothing set at all
        $test_name = 'a ref without an url shows only the url label';
        $t->assert($test_name, $form->show_ref_url($ref_plain), $t->labeled(msg_id::FORM_FIELD_URL, ''));
        $test_name = 'a ref without a source shows only the source label';
        $t->assert($test_name, $form->show_ref_source($ref_plain), $t->labeled(msg_id::FORM_SELECT_SOURCE, ''));
        $test_name = 'a new ref shows only the phrase label';
        $ref_empty = new ref_ui();
        $t->assert($test_name, $form->show_ref_phrase($ref_empty), $t->labeled(msg_id::FORM_SELECT_PHRASE, ''));
        $test_name = 'a new ref shows only the reference type label';
        $t->assert($test_name, $form->show_ref_type($ref_empty), $t->labeled(msg_id::FORM_SELECT_REF_TYPE, ''));
        $test_name = 'a new ref shows only the external key label';
        $t->assert($test_name, $form->show_ref_key($ref_empty), $t->labeled(msg_id::FORM_FIELD_EXTERNAL_KEY, ''));
        // the phrase of a reference cannot be changed, so the ref edit form has no phrase field
        // and names the word or triple in the form title instead
        $test_name = 'the ref edit form title names the change of a reference';
        $ttl_html = $form->form_tile(views::REF_EDIT, msg_id::FORM_TITLE_REF_EDIT, $ref_ui);
        $t->assert_text_contains($test_name, $ttl_html, $mtr->txt(msg_id::FORM_TITLE_REF_EDIT));
        $test_name = 'the ref edit form title links the phrase of the reference';
        $t->assert_text_contains($test_name, $ttl_html, $ref_ui->phrase()->name_link());
        // a reference whose phrase is known by its id only (e.g. a test render from the url
        // values) has no phrase name, so the title shows no link without a name
        $test_name = 'the ref edit form title of a phrase without name has no phrase link';
        $ttl_plain_html = $form->form_tile(views::REF_EDIT, msg_id::FORM_TITLE_REF_EDIT, $ref_plain);
        $t->assert_text_not_contains($test_name, $ttl_plain_html, '<a ');

        $t->subheader($ts . 'import and export');
        $t->assert_ex_and_import($t_ref->reference(), $t->usr_system);
        $t->assert_ex_and_import($t_ref->ref_filled(), $t->usr_system);
        $json_file = 'unit/ref/wikipedia.json';
        $t->assert_json_file(new ref($t->usr1), $json_file);

        // the "refs" part of a word or triple import json must be a list of objects, because
        // import_mapper expects an array; the check guards the mapper call in
        // word::import_mapper and triple::import_mapper against an uncaught fatal
        $ref_json = [
            [json_fields::NAME => refs::PI_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]
        ];
        $test_name = 'a list of reference objects can be mapped by the import';
        $t->assert_true($test_name, ref::import_list_valid($ref_json));
        $test_name = 'a list of several reference objects can be mapped by the import';
        $ref_json[] = [json_fields::NAME => refs::MATH_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE];
        $t->assert_true($test_name, ref::import_list_valid($ref_json));
        // a phrase without any reference names an empty list, so nothing is mapped and nothing
        // is reported to the user
        $test_name = 'an empty reference list can be mapped by the import';
        $t->assert_true($test_name, ref::import_list_valid([]));

        // negative: the compact form that names the reference type as the key and the external
        // key as the value is the format that has ended an import with a fatal
        $test_name = 'a reference map of the type to the key cannot be mapped by the import';
        $t->assert_false($test_name, ref::import_list_valid([
            refs::WIKIDATA_TYPE => refs::PI_KEY
        ]));
        $test_name = 'a reference list with a text entry cannot be mapped by the import';
        $t->assert_false($test_name, ref::import_list_valid([refs::PI_KEY]));
        // one object instead of a list of objects reads like a map of the field names, so it
        // cannot be mapped either
        $test_name = 'a single reference object cannot be mapped by the import';
        $t->assert_false($test_name, ref::import_list_valid(
            [json_fields::NAME => refs::PI_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]));
        $test_name = 'a reference given as a text cannot be mapped by the import';
        $t->assert_false($test_name, ref::import_list_valid(refs::PI_KEY));
        // a valid entry after an invalid one must not overrule the invalid entry
        $test_name = 'a reference list is only valid if every entry is an object';
        $t->assert_false($test_name, ref::import_list_valid([
            refs::PI_KEY,
            [json_fields::NAME => refs::MATH_KEY, json_fields::TYPE_NAME => refs::WIKIDATA_TYPE]
        ]));

    }

    /**
     * test the SQL statement creation for a value phrase link list in all SQL dialect
     * and check if the statement name is unique
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the test database connection
     * @param ref $ref the reference object for which the load by link ids sql statement creation should be tested
     * @return void
     */
    private function assert_sql_link_ids(
        test_cleanup $t,
        sql_creator  $sc,
        ref          $ref): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);

        // check the MySQL query syntax
        $sc->reset(sql_db::MYSQL);
        $qp = $ref->load_sql_by_link_ids($sc, 1, 2);
        $t->assert_qp($qp, $sc->db_type);
    }

    /**
     * check the sql to load a reference by its external key for both database dialects
     *
     * @param test_cleanup $t the test environment
     * @param sql_creator $sc the sql creator that is reset for each dialect
     * @param ref $ref the reference whose query is checked
     */
    private function assert_sql_ex_key(
        test_cleanup $t,
        sql_creator  $sc,
        ref          $ref): void
    {
        // check the Postgres query syntax
        $sc->reset(sql_db::POSTGRES);
        $qp = $ref->load_sql_by_ex_key($sc, refs::PI_KEY);
        $t->assert_qp($qp, $sc->db_type);

        // check the MySQL query syntax
        $sc->reset(sql_db::MYSQL);
        $qp = $ref->load_sql_by_ex_key($sc, refs::PI_KEY);
        $t->assert_qp($qp, $sc->db_type);
    }

}

