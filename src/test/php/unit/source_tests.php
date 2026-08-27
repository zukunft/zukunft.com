<?php

/*

    test/unit/source.php - unit testing for external sources
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

use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_type;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type_list;
use Zukunft\ZukunftCom\main\php\cfg\user\user_message;
use Zukunft\ZukunftCom\main\php\shared\const\def;
use Zukunft\ZukunftCom\main\php\shared\types\protection_types;
use Zukunft\ZukunftCom\main\php\web\component\execute\system_form;
use Zukunft\ZukunftCom\main\php\web\component\execute\ui_base;
use Zukunft\ZukunftCom\main\php\web\ref\source as source_ui;
use Zukunft\ZukunftCom\main\php\web\user\user as user_ui;
use Zukunft\ZukunftCom\main\php\web\user\user_message as user_message_ui;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\url_var;
use Zukunft\ZukunftCom\test\php\create\test_sources;
use Zukunft\ZukunftCom\test\php\create\test_terms;
use Zukunft\ZukunftCom\test\php\create\test_users;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;

class source_tests
{
    function run(test_cleanup $t): void
    {


        // init for source
        $sc = new sql_creator();
        $t_src = new test_sources($t);
        $msg = new user_message();
        $t->name = 'source->';
        $t->resource_path = 'db/ref/';

        // start the test section (ts)
        $ts = 'unit source ';
        $t->header($ts);

        $t->subheader($ts . 'sql setup');
        $src = new source($t->usr1);
        $t->assert_sql_table_create($src);
        $t->assert_sql_index_create($src);
        $t->assert_sql_foreign_key_create($src);

        $t->subheader($ts . 'sql read');
        $t->assert_sql_by_id($sc, $src);
        $t->assert_sql_by_name($sc, $src);
        $t->assert_sql_by_code_id($sc, $src);

        $t->subheader($ts . 'sql read standard and user changes by id');
        $src = new source($t->usr1);
        $src->id = 4;
        $t->assert_sql_standard($sc, $src);
        // the same two queries for many objects at once, which the user page uses to read the
        // standard values and the other users of all changed objects of one type with one query
        $t->assert_sql_standard_by_ids($sc, $src);
        $t->assert_sql_changing_users_by_ids($sc, $src);
        $src->id = 5;
        $t->assert_sql_not_changed($sc, $src);
        $t->assert_sql_user_changes($sc, $src);

        $t->subheader($ts . 'sql read standard by name');
        $src = new source($t->usr1);
        $src->set_name(sources::WIKIDATA);
        $t->assert_sql_standard_by_name($sc, $src);

        $t->subheader($ts . 'sql write insert');
        // TODO test the log version for db write
        $src = $t_src->source_reserved();
        $t->assert_sql_insert($sc, $src);
        $t->assert_sql_insert($sc, $src, [sql_type::USER]);
        $t->assert_sql_insert($sc, $src, [sql_type::LOG, sql_type::USER]);
        $src = $t_src->source_incomplete();
        $t->assert_sql_insert_fail($sc, $src, [sql_type::LOG]);

        $t->subheader($ts . 'sql write update');
        $src = $t_src->source_reserved();
        $src_renamed = $src->cloned(sources::SYSTEM_TEST_RENAMED);
        $t->assert_sql_update($sc, $src_renamed, $src);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::USER]);
        $src_renamed_admin = $src->cloned(sources::SYSTEM_TEST_RENAMED);
        $src_renamed_admin->set_protection_by_code_id(protection_types::ADMIN);
        $t->assert_sql_update($sc, $src_renamed_admin, $src, [sql_type::LOG]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::USER]);
        $src_renamed->exclude();
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::EXCLUDE]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);
        $src_only_excluded = clone $src;
        $src_only_excluded->exclude();
        $t->assert_sql_update($sc, $src_only_excluded, $src, [sql_type::LOG, sql_type::EXCLUDE]);
        $t->assert_sql_update($sc, $src_only_excluded, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'sql delete');
        $t->assert_sql_delete($sc, $src);
        $t->assert_sql_delete($sc, $src, [sql_type::USER]);
        // is covered already by the horizontal tests
        //$t->assert_sql_delete($sc, $src, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $src, [sql_type::USER, sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader($ts . 'base object handling');
        $src = $t_src->source_filled();
        $t->assert_reset($src);

        $t->subheader($ts . 'api');
        $src = $t_src->source_reserved();
        $t->assert_api_json($src);
        $db_con = new sql_db();
        $src->set_code_id_db(sources::SIB_CODE);
        $t->assert_api_msg($db_con, $src, $msg);

        $t->subheader($ts . 'frontend');
        $src = $t_src->source_reserved();
        $t->assert_api_to_ui($src, new source_ui());

        $test_name = 'the doi of a source creates the url to doi.org';
        $src_ui = new source_ui($t_src->source_filled_included()->api_json());
        $t->assert($test_name, $src_ui->doi_url(), def::LINK_DOI . sources::TEST_DOI);

        $test_name = 'a source without doi has no doi url';
        $src_ui = new source_ui($t_src->source_reserved()->api_json());
        $t->assert_null($test_name, $src_ui->doi_url());

        $ui = new ui_base();
        $test_name = 'the doi of a source is shown as a link to doi.org';
        $src_ui = new source_ui($t_src->source_filled_included()->api_json());
        $t->assert($test_name, $ui->source_doi_link($src_ui),
            '<a href="' . def::LINK_DOI . sources::TEST_DOI . '">' . sources::TEST_DOI . '</a>');

        $test_name = 'a source without doi shows no doi link';
        $src_ui = new source_ui($t_src->source_reserved()->api_json());
        $t->assert($test_name, $ui->source_doi_link($src_ui), '');

        // for sources the code id is a user changeable field, but a code id is only shown to
        // an admin or a developer and only a developer gets the input field, because only a
        // profile that passes the backend can_set_code_id may change it
        global $ui_sys;
        $form = new system_form();
        $t_usr = new test_users($t);
        // source_admin carries the code id, because a source without one renders an empty field
        $src_ui = new source_ui($t_src->source_admin()->api_json());
        // remember the session user so the changed global can be restored after the checks
        $usr_keep = $ui_sys->usr ?? null;
        $test_name = 'a developer sees the code id input field of a source';
        $ui_sys->usr = new user_ui($t->usr_dev->api_json());
        $t->assert_text_contains($test_name, $form->form_field_code_id($src_ui), url_var::CODE_ID);
        $test_name = 'an admin sees the code id of a source as read only text';
        $ui_sys->usr = new user_ui($t->usr_admin->api_json());
        $admin_html = $form->form_field_code_id($src_ui);
        $t->assert_text_contains($test_name, $admin_html, sources::SIB_CODE);
        $test_name = 'an admin gets no code id input field';
        $t->assert_false($test_name, str_contains($admin_html, 'name="' . url_var::CODE_ID . '"'));
        $test_name = 'a normal user does not see the code id of a source';
        $ui_sys->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $t->assert($test_name, $form->form_field_code_id($src_ui), '');
        $test_name = 'a test profile user does not see the code id, so the view snapshots stay clean';
        $ui_sys->usr = new user_ui($t->usr1->api_json());
        $t->assert($test_name, $form->form_field_code_id($src_ui), '');
        $ui_sys->usr = $usr_keep;
        // the confirm check mirrors the backend permission, so an orange warning is
        // shown on the edit view instead of a refused save
        $test_name = 'a code id change of a normal user is refused at the confirm check';
        $chk_msg = new user_message_ui();
        $chk_msg->usr = new user_ui($t_usr->user_sys_normal()->api_json());
        $t->assert_false($test_name, $src_ui->input_valid($chk_msg, url_var::CRUD_UPDATE,
            [url_var::CODE_ID => 'changed', url_var::PRE . url_var::CODE_ID => sources::SIB_CODE]));
        $test_name = 'a code id change of a developer passes the confirm check';
        $chk_msg = new user_message_ui();
        $chk_msg->usr = new user_ui($t->usr_dev->api_json());
        $t->assert_true($test_name, $src_ui->input_valid($chk_msg, url_var::CRUD_UPDATE,
            [url_var::CODE_ID => 'changed', url_var::PRE . url_var::CODE_ID => sources::SIB_CODE]));

        $t->subheader($ts . 'import and export');
        $t->assert_ex_and_import($t_src->source(), $t->usr_system);
        $t->assert_ex_and_import($t_src->source_filled(), $t->usr_system);
        $json_file = 'unit/ref/bipm.json';
        $t->assert_json_file(new source($t->usr1), $json_file);


        // start the test section (ts)
        $ts = 'unit source type ';
        $t->header($ts);

        $t->subheader($ts . 'type sql read');
        $source_type_list = new source_type_list();
        $t->assert_sql_all($sc, $source_type_list);

    }

}

