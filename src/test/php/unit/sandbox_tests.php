<?php

/*

  test_units.php - UNIT TESTing for zukunft.com
  --------------
  

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

use Zukunft\ZukunftCom\main\php\cfg\const\def;
use Zukunft\ZukunftCom\main\php\cfg\const\paths;

include_once paths::SERVICE . 'config.php';
include_once paths::DB . 'sql.php';
include_once paths::MODEL_FORMULA . 'formula_db.php';
include_once paths::MODEL_GROUP . 'group.php';
include_once paths::MODEL_REF . 'source.php';
include_once paths::MODEL_REF . 'source_db.php';
include_once paths::MODEL_VALUE . 'value.php';
include_once paths::MODEL_VERB . 'verb_db.php';
include_once paths::MODEL_VIEW . 'view_db.php';
include_once paths::MODEL_WORD . 'triple_db.php';
include_once paths::MODEL_USER . 'user_db.php';
include_once paths::SHARED_CONST . 'words.php';
include_once paths::SHARED_ENUM . 'messages.php';
include_once paths::SHARED_CONST_FIELDS . 'fields.php';
include_once paths::SHARED_CONST_FIELDS . 'word_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'triple_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'view_fields.php';
include_once paths::SHARED_CONST_FIELDS . 'formula_fields.php';

use Zukunft\ZukunftCom\main\php\cfg\component\component;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link;
use Zukunft\ZukunftCom\main\php\cfg\component\component_link_list;
use Zukunft\ZukunftCom\main\php\service\config;
use Zukunft\ZukunftCom\main\php\cfg\db\sql;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_creator;
use Zukunft\ZukunftCom\main\php\cfg\db\sql_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_db;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link;
use Zukunft\ZukunftCom\main\php\cfg\formula\formula_link_type;
use Zukunft\ZukunftCom\main\php\cfg\phrase\phrase;
use Zukunft\ZukunftCom\main\php\cfg\ref\source;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_type;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox;
use Zukunft\ZukunftCom\main\php\cfg\sandbox\sandbox_link;
use Zukunft\ZukunftCom\main\php\cfg\ref\source_db;
use Zukunft\ZukunftCom\main\php\cfg\user\user;
use Zukunft\ZukunftCom\main\php\cfg\user\user_db;
use Zukunft\ZukunftCom\main\php\cfg\value\value;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb;
use Zukunft\ZukunftCom\main\php\cfg\verb\verb_db;
use Zukunft\ZukunftCom\main\php\cfg\view\view_db;
use Zukunft\ZukunftCom\main\php\cfg\word\triple;
use Zukunft\ZukunftCom\main\php\cfg\word\triple_db;
use Zukunft\ZukunftCom\main\php\cfg\word\word;
use Zukunft\ZukunftCom\main\php\cfg\word\word_db;
use Zukunft\ZukunftCom\main\php\web\sandbox\db_object as db_object_ui;
use Zukunft\ZukunftCom\main\php\shared\const\components;
use Zukunft\ZukunftCom\main\php\shared\const\users;
use Zukunft\ZukunftCom\main\php\shared\enum\messages as msg_id;
use Zukunft\ZukunftCom\main\php\shared\library;
use Zukunft\ZukunftCom\main\php\shared\const\sources;
use Zukunft\ZukunftCom\main\php\shared\types\phrase_types;
use Zukunft\ZukunftCom\test\php\const\triple_names;
use Zukunft\ZukunftCom\test\php\const\word_names;
use Zukunft\ZukunftCom\test\php\create\test_components;
use Zukunft\ZukunftCom\test\php\create\test_triples;
use Zukunft\ZukunftCom\test\php\create\test_verbs;
use Zukunft\ZukunftCom\test\php\create\test_views;
use Zukunft\ZukunftCom\test\php\create\test_words;
use Zukunft\ZukunftCom\test\php\utils\test_cleanup;
use Zukunft\ZukunftCom\main\php\shared\const\fields\fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\word_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\triple_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\view_fields;
use Zukunft\ZukunftCom\main\php\shared\const\fields\formula_fields;

class sandbox_tests
{
    function run(test_cleanup $t): void
    {

        global $sys;

        // init
        $t_wrd = new test_words($t);
        $t_vrb = new test_verbs($t);
        $t_trp = new test_triples($t);
        $t_msk = new test_views($t);
        $t_cmp = new test_components($t);
        $lib = new library();

        // start the test section (ts)
        $ts = 'unit sandbox ';
        $t->header($ts);

        $t->subheader($ts . 'name list');
        $test_name = 'names match cached names';
        $wrd_lst = $t_wrd->word_list();
        // call the names function with a high limit to force the usage of the slow loop
        $name_list = implode('.', $wrd_lst->names(false, 100));
        $name_list_cache = implode('.', array_keys($wrd_lst->name_pos_lst()));
        $t->assert($test_name, $name_list_cache, $name_list);
        $test_name = 'names match not cached names including excluded';
        $name_list_ex = implode('.', array_keys($wrd_lst->name_pos_lst_all()));
        $wrd_ex = $t_wrd->word_education();
        $wrd_ex->exclude();
        $wrd_lst->add_by_key($wrd_ex);
        $name_list_ex_cache = implode('.', array_keys($wrd_lst->name_pos_lst_all()));
        // TODO Prio 2 activate and add the handling of excluded named objects
        //$t->assert_not($test_name, $name_list_ex_cache, $name_list);
        $test_name = 'cached names match cached names including excluded';
        //$t->assert($test_name, $name_list_ex_cache, $name_list_ex);


        $t->subheader($ts . 'link');
        $test_name = 'name with key separator can be used';
        $wrd = $t_wrd->word();
        $to = $t_wrd->word();
        $vrb = $t_vrb->verb();
        $wrd->set_name($wrd->name() . sandbox_link::KEY_SEP . $vrb->name());
        $trp = new triple($t->usr1);
        $trp->set_from($wrd->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($to->phrase());
        $key_vrb = $trp->get_key();
        $wrd->set_name($t_wrd->word()->name());
        $to->set_name($vrb->name() . sandbox_link::KEY_SEP . $to->name());
        $key_to = $trp->get_key();
        $t->assert_not($test_name, $key_vrb, $key_to);
        // TODO Prio 2 activate this test based on changing the verb
        //      which implies that the changing of the verb name is updating the cache
        //      so a requirement is that the cache update trigger is implemented
        /*
        $wrd = $t_wrd->word();
        $to = $t_wrd->word();
        $vrb = $t_vrb->verb();
        $vrb->set_name($vrb->name() . sandbox_link::KEY_SEP . $wrd->name());
        $trp = new triple($t->usr1);
        $trp->set_from($wrd->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($to->phrase());
        $key_vrb = $trp->key();
        $vrb->set_name($t_vrb->verb()->name());
        $to->set_name($to->name() . sandbox_link::KEY_SEP . $to->name());
        $key_to = $trp->key();
        $t->assert_not($test_name, $key_vrb, $key_to);
        */


        $t->subheader($ts . 'link list');
        $lst = new component_link_list($t->usr1);
        $test_name = 'add link is fine';
        $result = $lst->add_link($t_cmp->component_link());
        $t->assert_true($test_name, $result);
        $test_name = 'adding link twice is rejected';
        $result = $lst->add_link($t_cmp->component_link());
        $t->assert_false($test_name, $result);
        $lst = new component_link_list($t->usr1);
        $test_name = 'add component is fine';
        $result = $lst->add(1, $t_msk->view(), $t_cmp->component(), 1);
        $t->assert_true($test_name, $result);
        $test_name = 'add component at the same position is rejected';
        $result = $lst->add(1, $t_msk->view(), $t_cmp->component(), 1);
        $t->assert_false($test_name, $result);
        $test_name = 'add component at a different position is fine';
        $result = $lst->add(2, $t_msk->view(), $t_cmp->component(), 2);
        $t->assert_true($test_name, $result);
        $test_name = 'add same component at different position without db id is fine';
        $result = $lst->add(0, $t_msk->view(), $t_cmp->component(), 3);
        $t->assert_true($test_name, $result);
        $test_name = 'add same component at different position with same db id is rejected';
        $result = $lst->add(1, $t_msk->view(), $t_cmp->component(), 3);
        $t->assert_false($test_name, $result);

        // an import links the components before their ids are known, so two of them are told
        // apart by the name and only a real repetition is rejected
        $test_name = 'add a component without a db id';
        $lst = new component_link_list($t->usr1);
        $result = $lst->add(0, $t_msk->view(), $t_cmp->by_name(components::TEST_VALUES_NAME), 1);
        $t->assert_true($test_name, $result);
        $test_name = 'add another component without a db id at the same position';
        $result = $lst->add(0, $t_msk->view(), $t_cmp->by_name(components::TEST_RESULTS_NAME), 1);
        $t->assert_true($test_name, $result);
        $test_name = 'add the same component without a db id again is rejected';
        $result = $lst->add(0, $t_msk->view(), $t_cmp->by_name(components::TEST_VALUES_NAME), 1);
        $t->assert_false($test_name, $result);

        // TODO review the tests below e.g. by using the test section ($ts) and $test_name like above
        $t->subheader($ts . 'functions that does not need a database connection');

        $test_name = 'a missing selector overwrite is reported to the user via the message object';
        $dbo_ui = new db_object_ui();
        $result = $dbo_ui->verb_selector('test_form', null);
        $target = 'verb_selector function is not overwritten by ' . db_object_ui::class;
        $t->assert($test_name, $result, $target);

        // the shared helper behind the dummy parent functions above; only the warning level is
        // tested, because the error level would raise the error count and ERROR_LIMIT is zero
        $test_name = 'the missing overwrite helper names the function and the class';
        $miss_txt = log_missing_overwrite_warning('test_fnc', word::class);
        $target = 'test_fnc function is not overwritten by ' . word::class;
        $t->assert($test_name, $miss_txt, $target);

        $test_name = 'the missing overwrite text has no unresolved message variable left';
        $t->assert_text_not_contains($test_name, $miss_txt, msg_id::VAR_START);

        // test if two sources are supposed to be the same
        $src1 = new source($t->usr1);
        $src1->set(sources::SIB_ID, sources::IPCC_AR6_SYNTHESIS);
        $src2 = new source($t->usr1);
        $src2->set(sources::WIKIDATA_ID, sources::IPCC_AR6_SYNTHESIS);
        $result = $src1->is_same($src2);
        $t->assert("two sources are not the same if the id does not match", $result, false);

        // ... and they are of course also similar
        $result = $src1->is_similar($src2);
        $t->assert("... but they are similar", $result, true);

        // ... but could be the same
        $src1->id = 0;
        $result = $src1->is_same($src2);
        $t->assert("two sources are supposed to be the same if the id id empty", $result, true);

        // TODO review test (start with test_name="" and move the creation to the test object creation)
        // a source can have the same name as a word
        $wrd1 = new word($t->usr1);
        $wrd1->id = 1;
        $wrd1->set_name(sources::IPCC_AR6_SYNTHESIS);
        $src2 = new source($t->usr1);
        $src2->id = 2;
        $src2->set_name(sources::IPCC_AR6_SYNTHESIS);
        $result = $wrd1->is_same($src2);
        $t->assert("a source is not the same as a word even if they have the same name", $result, false);

        // but a formula should not have the same name as a word
        $wrd = new word($t->usr1);
        $wrd->set_name(word_names::MIO);
        $wrd->type_id = $sys->typ_lst->phr_typ->id(phrase_types::FORMULA_LINK);
        $frm = new formula($t->usr1);
        $frm->set_name(word_names::MIO);
        $result = $wrd->is_similar($frm);
        $t->assert("a formula should not have the same name as a word", $result, true);

        // ... but they are not the same
        $result = $wrd->is_same($frm);
        $t->assert("... but they are not the same", $result, false);

        $test_name = 'a triple with the same link is similar even if it has a different name';
        $trp1 = $t_trp->triple();
        $trp2 = clone $trp1;
        $trp2->id = 0;
        $trp2->set_name(triple_names::SYSTEM_TEST_ADD);
        $t->assert($test_name, $trp1->is_similar($trp2), true);

        $test_name = '... but a triple with the same link and a different name is not the same';
        $t->assert($test_name, $trp1->is_same($trp2), false);

        // a word with the name 'millions' is similar to a formulas named 'millions', but not the same, so

        $t->subheader($ts . 'sql base functions');

        // test sf (Sql Formatting) function
        $db_con = new sql_db();

        // ... postgres version
        $db_con->db_type = sql_db::POSTGRES;
        $text = "'4'";
        $target = "'''4'''";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $target = "4";
        $result = $db_con->sf($text, sql_db::FLD_FORMAT_VAL);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "2021";
        $target = "'2021'";
        $result = $db_con->sf($text, sql_db::FLD_FORMAT_TEXT);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "four";
        $target = "'four'";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "'four'";
        $target = "'''four'''";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $text = " ";
        $target = "NULL";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        // ... MySQL version
        $db_con->db_type = sql_db::MYSQL;
        $text = "'4'";
        $target = "'\'4\''";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $target = "4";
        $result = $db_con->sf($text, sql_db::FLD_FORMAT_VAL);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "2021";
        $target = "'2021'";
        $result = $db_con->sf($text, sql_db::FLD_FORMAT_TEXT);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "four";
        $target = "'four'";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $text = "'four'";
        $target = "'\'four\''";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $text = " ";
        $target = "NULL";
        $result = $db_con->sf($text);
        $t->assert(", sf: " . $text, $result, $target);

        $t->subheader($ts . 'version control');

        $this->prg_version_is_newer_test($t);


        // start the test section (ts)
        $ts = 'unit database connector ';
        $t->header($ts);

        $db_con = new sql_db();
        // a directly created connection skips the entry point that sets the requesting user in
        // production, so set it here; set_class takes its default query user from the connection
        // (sql_db::usr_req), so user-scoped SQL is built for usr1 (id 3) unless a test overrides it
        // with an explicit set_usr
        $db_con->usr_req = $t->usr1;

        /*
         * General tests (one by one for each database)
         */

        // test a simple SQL user select query for Postgres by name
        $test_name = 'Postgres select max';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(user::class);
        $db_con->set_name('formula_link_norm_by_id');
        $db_con->set_usr(users::SYSTEM_ID);
        $db_con->set_where_std(null, 'Test User');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_norm_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL select max';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(user::class);
        $db_con->set_name('formula_link_norm_by_id_mysql');
        $db_con->set_usr(users::SYSTEM_ID);
        $db_con->set_where_std(null, 'Test User');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_norm_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a simple SQL max select creation for Postgres without where
        $test_name = 'Postgres select max';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(value::class);
        $db_con->set_fields(array('MAX(group_id) AS max_id'));
        $created_sql = $db_con->select_by_set_id(false);
        $expected_sql = $t->file('db/sql_creator/value_max.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL select max';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(value::class);
        $db_con->set_fields(array('MAX(group_id) AS max_id'));
        $created_sql = $db_con->select_by_set_id(false);
        $expected_sql = $t->file('db/sql_creator/value_max_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a simple SQL select creation for Postgres without the standard id and name identification
        $sc = new sql_creator();
        $sc->set_db_type(sql_db::POSTGRES);
        $sc->set_class(config::class);
        $sc->set_name('query_test');
        $sc->set_fields(array('value'));
        $sc->add_where(fields::FLD_CODE_ID, config::VERSION_DB);
        $created_sql = $sc->sql();
        $test_name = 'non id Postgres select';
        $expected_sql = $t->file('db/sql_creator/query_test.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));
        $created_par = implode(',', $sc->get_par());
        $expected_par = "version_database";
        $t->assert('non id Postgres parameter', $lib->trim($created_par), $lib->trim($expected_par));

        // ... same for MySQL
        $sc->db_type = sql_db::MYSQL;
        $sc->set_class(config::class);
        $sc->set_name('query_test');
        $sc->set_fields(array('value'));
        $sc->add_where(fields::FLD_CODE_ID, config::VERSION_DB);
        $created_sql = $sc->sql();
        $test_name = 'non id MySQL select';
        $expected_sql = $t->file('db/sql_creator/query_test_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));
        $created_par = implode(',', $sc->get_par());
        $expected_par = "version_database";
        $t->assert('non id MySQL parameter', $lib->trim($created_par), $lib->trim($expected_par));
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a simple SQL select creation for Postgres with the standard id and name identification
        $test_name = 'Postgres select based on id';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(source_type::class);
        $db_con->set_name('source_type_by_id');
        $db_con->set_where_std(2);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/source_type_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL select based on id';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(source_type::class);
        $db_con->set_name('source_type_by_id');
        $db_con->set_where_std(2);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/source_type_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a simple SQL select of the user defined word for Postgres by the id
        $test_name = 'Postgres user word select based on id';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_fields(array(word_fields::FLD_PLURAL, fields::FLD_DESCRIPTION, phrase::FLD_TYPE, fields::FLD_VIEW));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/word_usr_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL user word select based on id';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_fields(array('plural', fields::FLD_DESCRIPTION, 'phrase_type_id', 'view_id'));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/word_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a very simple SQL select of the user defined word for Postgres by the id
        $test_name = 'Postgres user word id select based on id';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/word_usr_id_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL user word id select based on id';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/word_usr_id_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a simple SQL select the formulas linked to a phrase
        $test_name = 'Postgres formulas linked to a phrase select based on phrase id';
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, 1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_by_phrase.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for MySQL
        $test_name = 'MySQL formulas linked to a phrase select based on phrase id';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, 1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_by_phrase_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test a list of links SQL select creation for Postgres selected by a linked object
        /*
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $db_con->set_join_fields(array(fields::FLD_CODE_ID, 'name_plural','name_reverse','name_plural_reverse','formula_name',fields::FLD_DESCRIPTION), sql_db::TBL_VERB);
        $db_con->set_where(2);
        $created_sql = $db_con->select();
        $expected_sql = "SELECT l.verb_id,
                         l.code_id,
                         l.verb_name,
                         l.name_plural,
                         l.name_reverse,
                         l.name_plural_reverse,
                         l.formula_name,
                         l.description
                    FROM triples s
               LEFT JOIN verbs ON s.verb_id = l.verb_id
                         ".$sql_where."
                GROUP BY v.verb_id
                ORDER BY v.verb_id;";
        $t->assert('Postgres select based on id', $lib->trim($created_sql), $lib->trim($expected_sql));
        */

        /*
         * Start of the concrete database object test fpr Postgres
         */

        // test a SQL select creation of user sandbox data for Postgres
        $db_con->db_type = sql_db::POSTGRES;

        // ... same but select by the link ids
        $test_name = 'Postgres component_link load_standard select by link ids';
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view_fields::FLD_ID, component::FLD_ID);
        $db_con->set_fields(array(component_link::FLD_ORDER_NBR, component_link::FLD_POS_TYPE, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(0, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_link_std_by_link_ids.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component_link load SQL creation
        $test_name = 'Postgres component_link load select by id';
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view_fields::FLD_ID, component::FLD_ID);
        $db_con->set_usr_num_fields(array(component_link::FLD_ORDER_NBR, component_link::FLD_POS_TYPE, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_link_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the formula_link load_standard SQL creation
        $test_name = 'Postgres formula_link load_standard select by id';
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_fields(array(formula_link_type::FLD_ID, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_std_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the formula_link load SQL creation
        $test_name = 'Postgres formula_link load select by id';
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(array(formula_link_type::FLD_ID, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_usr_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component load_standard SQL creation
        $test_name = 'Postgres component load_standard select by id';
        $db_con->set_class(component::class);
        $db_con->set_fields(array(fields::FLD_DESCRIPTION, 'component_type_id', 'word_id_row', 'link_type_id', formula_fields::FLD_ID, 'word_id_col', 'word_id_col2', fields::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_std_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component load SQL creation
        $test_name = 'Postgres component load select by id';
        $db_con->set_class(component::class);
        $db_con->set_usr_fields(array(fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('component_type_id', 'word_id_row', 'link_type_id', formula_fields::FLD_ID, 'word_id_col', 'word_id_col2', fields::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the triple load_standard SQL creation
        $test_name = 'Postgres triple load_standard select by id';
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple_fields::FLD_FROM, triple_fields::FLD_TO, verb_db::FLD_ID);
        $db_con->set_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION, fields::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/triple_std_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the triple load SQL creation
        $test_name = 'Postgres triple load select by id';
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple_fields::FLD_FROM, triple_fields::FLD_TO, verb_db::FLD_ID);
        $db_con->set_fields(array('phrase_type_id'));
        $db_con->set_usr_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(fields::FLD_EXCLUDED));
        $db_con->set_where_text('s.triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/triple_by_id.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the verb_list load SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_usr_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(fields::FLD_EXCLUDED));
        $db_con->set_join_fields(
            array(fields::FLD_CODE_ID, 'verb_name', 'name_plural', 'name_reverse', 'name_plural_reverse', 'formula_name', fields::FLD_DESCRIPTION),
            verb::class);
        $db_con->set_fields(array(verb_db::FLD_ID));
        $db_con->set_where_text('s.to_phrase_id = 2');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'Postgres verb_list load';
        $expected_sql = $t->file('db/sql_creator/triple_verb_join.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        /*
         * Start of the corresponding MySQL tests
         */

        // ... and search by id for MySQL
        $test_name = 'MySQL user sandbox select';
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(source::class);
        $db_con->set_fields(array(fields::FLD_CODE_ID));
        $db_con->set_usr_fields(array(fields::FLD_URL, fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/source_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for search by name
        $db_con->set_class(source::class);
        $db_con->set_fields(array(fields::FLD_CODE_ID));
        $db_con->set_usr_fields(array(fields::FLD_URL, fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL user sandbox select by name';
        $expected_sql = $t->file('db/sql_creator/source_usr_by_name_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for search by code_id
        $db_con->set_class(source::class);
        $db_con->set_fields(array(fields::FLD_CODE_ID));
        $db_con->set_usr_fields(array(fields::FLD_URL, fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, '', 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL user sandbox select by code_id';
        $expected_sql = $t->file('db/sql_creator/source_usr_by_code_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for all users by id
        $test_name = 'MySQL all user select by id';
        $db_con->set_class(source::class);
        $db_con->set_fields(array(fields::FLD_CODE_ID, fields::FLD_URL, fields::FLD_DESCRIPTION, 'source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/source_all_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... similar with joined fields
        $db_con->set_class(formula::class);
        $db_con->set_fields(array(
            user_db::FLD_ID,
            formula_fields::FLD_FORMULA_TEXT,
            formula_fields::FLD_FORMULA_USER_TEXT,
            fields::FLD_DESCRIPTION,
            formula_fields::FLD_TYPE,
            formula_fields::FLD_ALL_NEEDED,
            fields::FLD_LAST_UPDATE,
            fields::FLD_EXCLUDED));
        $db_con->set_join_fields(array(fields::FLD_CODE_ID), 'formula_type');
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL all user join select by id';
        $expected_sql = $t->file('db/sql_creator/formula_join_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for user sandbox data
        $db_con->set_class(formula::class);
        $db_con->set_usr_fields(array(
            formula_fields::FLD_FORMULA_TEXT,
            formula_fields::FLD_FORMULA_USER_TEXT,
            fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(
            formula_fields::FLD_TYPE,
            formula_fields::FLD_ALL_NEEDED,
            fields::FLD_LAST_UPDATE,
            fields::FLD_EXCLUDED));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL all user join select by id';
        $expected_sql = $t->file('db/sql_creator/formula_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // ... same for a link table
        $db_con->set_class(triple::class);
        $db_con->set_fields(array(triple_fields::FLD_FROM, triple_fields::FLD_TO, verb_db::FLD_ID, 'phrase_type_id'));
        $db_con->set_usr_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION, fields::FLD_EXCLUDED));
        $db_con->set_where_text('s.triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL user sandbox link select by where text';
        $expected_sql = $t->file('db/sql_creator/triple_usr_by_text_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component_link load_standard SQL creation
        $test_name = 'MySQL component_link load_standard select by id';
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view_fields::FLD_ID, component::FLD_ID);
        $db_con->set_fields(array('order_nbr', 'position_type_id', fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_link_std_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component_link load SQL creation
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view_fields::FLD_ID, component::FLD_ID);
        $db_con->set_usr_num_fields(array('order_nbr', 'position_type_id', fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL component_link load select by id';
        $expected_sql = $t->file('db/sql_creator/component_link_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the formula_link load_standard SQL creation
        $test_name = 'MySQL formula_link load_standard select by id';
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_fields(array(formula_link_type::FLD_ID, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/formula_link_std_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the formula_link load SQL creation
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula_fields::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(array(formula_link_type::FLD_ID, fields::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL formula_link load select by id';
        $expected_sql = $t->file('db/sql_creator/formula_link_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component load_standard SQL creation
        $test_name = 'MySQL component load_standard select by id';
        $db_con->set_class(component::class);
        $db_con->set_fields(array(fields::FLD_DESCRIPTION, 'component_type_id', 'word_id_row', 'link_type_id', formula_fields::FLD_ID, 'word_id_col', 'word_id_col2', fields::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/component_std_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the component load SQL creation
        $db_con->set_class(component::class);
        $db_con->set_usr_fields(array(fields::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('component_type_id', 'word_id_row', 'link_type_id', formula_fields::FLD_ID, 'word_id_col', 'word_id_col2', fields::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL component load select by id';
        $expected_sql = $t->file('db/sql_creator/component_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the triple load_standard SQL creation
        $test_name = 'MySQL triple load_standard select by id';
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple_fields::FLD_FROM, triple_fields::FLD_TO, verb_db::FLD_ID);
        $db_con->set_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION, 'phrase_type_id', fields::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = $t->file('db/sql_creator/triple_std_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // test the triple load SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple_fields::FLD_FROM, triple_fields::FLD_TO, verb_db::FLD_ID);
        $db_con->set_usr_fields(array(triple_fields::FLD_NAME_GIVEN, fields::FLD_DESCRIPTION));
        $db_con->set_fields(array('phrase_type_id'));
        $db_con->set_usr_num_fields(array(fields::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'MySQL triple load select by id';
        $expected_sql = $t->file('db/sql_creator/triple_usr_by_id_mysql.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        /*
         * Build sample queries in the Postgres format to use the database syntax check of the IDE
         */

        // the formula list load query
        $db_con->db_type = sql_db::POSTGRES;
        $sql_from = 'formula_links l, formulas f';
        $sql_where = sql_db::LNK_TBL . '.phrase_id = 1 AND l.formula_id = f.formula_id';
        $created_sql = "SELECT 
                       f.formula_id,
                       f.formula_name,
                       " . $db_con->get_usr_field(formula_fields::FLD_FORMULA_TEXT, 'f', 'u') . ",
                       " . $db_con->get_usr_field(formula_fields::FLD_FORMULA_USER_TEXT, 'f', 'u') . ",
                       " . $db_con->get_usr_field(fields::FLD_DESCRIPTION, 'f', 'u') . ",
                       " . $db_con->get_usr_field(formula_fields::FLD_TYPE, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(fields::FLD_CODE_ID, 't', 'c') . ",
                       " . $db_con->get_usr_field(formula_fields::FLD_ALL_NEEDED, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(fields::FLD_LAST_UPDATE, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(fields::FLD_EXCLUDED, 'f', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM " . $sql_from . " 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = 3 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE " . $sql_where . "
              GROUP BY f.formula_id;";
        $test_name = 'formula list load query';
        $expected_sql = $t->file('db/sql_creator/formula_list_load.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the phrase load word query
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = 'SELECT w.word_id AS id, 
                    ' . $db_con->get_usr_field("word_name", "w", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM words w   
                 LEFT JOIN user_words u ON u.word_id = w.word_id
                                       AND u.user_id = 3
                  GROUP BY w.word_id, w.word_name ;';
        $test_name = 'phrase load word query';
        $expected_sql = $t->file('db/sql_creator/phrase_word_query.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the phrase load word link query
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = 'SELECT l.triple_id * -1 AS id,
                    ' . $db_con->get_usr_field("name", "l", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM triples l
                 LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                            AND u.user_id = 3
                  GROUP BY l.triple_id, l.name ;';
        $test_name = 'phrase load word link query';
        $expected_sql = $t->file('db/sql_creator/phrase_word_link_query.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the phrase load word link query by type
        $db_con->db_type = sql_db::POSTGRES;
        $sql_where_exclude = '(excluded <> 1 OR excluded is NULL)';
        $created_sql = 'SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 3
                         WHERE l.to_phrase_id = 2 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . ';';
        $test_name = 'phrase load word link query by type';
        $expected_sql = $t->file('db/sql_creator/phrase_word_link_by_type.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the view component link query by type (used in word_display->assign_dsp_ids)
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(component_link::class);
        //$db_con->set_join_fields(array('position_type'), 'position_type');
        $db_con->set_fields(array(view_fields::FLD_ID, component::FLD_ID));
        $db_con->set_usr_num_fields(array('order_nbr', 'position_type_id', fields::FLD_EXCLUDED));
        $db_con->set_where_text('s.component_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $test_name = 'phrase load word link query by type';
        $expected_sql = $t->file('db/sql_creator/view_component_link_by_type.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the view component link max order number query (used in word_display->next_nbr)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT 
                              " . $db_con->get_usr_field("order_nbr", "l", "u", sql_db::FLD_FORMAT_VAL) . " 
                          FROM component_links l 
                    LEFT JOIN user_component_links u ON u.component_link_id = l.component_link_id 
                                                      AND u.user_id = 3 
                        WHERE l.view_id = 1 ) AS m;";
        $test_name = 'phrase load word link query by type';
        $expected_sql = $t->file('db/sql_creator/view_component_link_max_order.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the phrase load word link query by phrase
        $db_con->db_type = sql_db::POSTGRES;
        $sql_field_names = 'id, name, excluded';
        $sql_where_exclude = '(excluded <> 1 OR excluded is NULL)';
        $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 3
                         WHERE l.to_phrase_id = 1 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . '  ';
        $sql_wrd_other = 'SELECT from_phrase_id FROM (
                          SELECT DISTINCT
                                 l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                            FROM triples l
                       LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                  AND u.user_id = 3
                           WHERE l.to_phrase_id <> 1 
                             AND l.verb_id = 2
                             AND l.from_phrase_id IN ( ' . $sql_wrd_all . ' )  
                        GROUP BY l.from_phrase_id ) AS o 
                           WHERE ' . $sql_where_exclude . ' ';
        $created_sql = 'SELECT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 3
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ')                                        
                         AND w.word_id = a.id    
                    GROUP BY name ) AS w 
                       WHERE ' . $sql_where_exclude . ';';
        $test_name = 'phrase load word link query by type';
        $expected_sql = $t->file('db/sql_creator/phrase_word_link_by_phrase.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the time word selector query by type (used in word_display->dsp_time_selector)
        // $sql_avoid_code_check_prefix is used to avoid SQL code checks by the IDE on the query building process,
        // which is not needed because the check is done on the $expected_sql and the $created_sql is compared with the checked
        $db_con->db_type = sql_db::POSTGRES;
        $sql_from = "triples l, words w";
        $sql_where_and = "AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = 2             
                        AND l.to_phrase_id = 14 ";
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " id, name 
              FROM ( SELECT w.word_id AS id, 
                            " . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "name") . ",    
                            " . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . "
                       FROM " . $sql_from . "   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 3 
                      WHERE w.phrase_type_id = 2
                        " . $sql_where_and . "            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
        $test_name = 'time word selector query by type';
        $expected_sql = $t->file('db/sql_creator/time_word_selector.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the verb selector query (used in word_display->selector_link)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_name = "CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name";
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " * FROM (
            SELECT verb_id AS id, 
                   " . $sql_name . ",
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $test_name = 'verb selector query';
        $expected_sql = $t->file('db/sql_creator/verb_selector.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the word link list load query (used in triple_list->load)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_where = sql_db::LNK_TBL . '.to_phrase_id   = 3';
        $sql_type = 'AND l.verb_id = 2';
        $sql_wrd1_fields = '';
        $sql_wrd1_from = '';
        $sql_wrd1 = '';
        $sql_wrd2_fields = "t2.word_id AS word_id2,
                t2.user_id AS user_id2,
                 CASE WHEN (u2.word_name <> '' IS NOT TRUE) THEN t2.word_name ELSE u2.word_name END AS word_name,
                 CASE WHEN (u2.plural <> '' IS NOT TRUE) THEN t2.plural ELSE u2.plural END AS plural,
                 CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description ELSE u2.description END AS description,
                 CASE WHEN (u2.phrase_type_id IS NULL) THEN t2.phrase_type_id ELSE u2.phrase_type_id END AS phrase_type_id,
                 CASE WHEN (u2.excluded IS NULL) THEN t2.excluded ELSE u2.excluded END AS excluded,
                  t2.values AS values2";
        $sql_wrd2_from = ' words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 ';
        $sql_wrd2 = sql_db::LNK_TBL . '.from_phrase_id = t2.word_id';
        $created_sql = "SELECT l.triple_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       " . $db_con->get_usr_field(fields::FLD_EXCLUDED, 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM triples l
             LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              GROUP BY t2.word_id, l.verb_id
              ORDER BY l.verb_id, word_name;";
        $test_name = 'word link list load query';
        $expected_sql = $t->file('db/sql_creator/word_link_list_load.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the phrase load word link query by ...
        // TODO check if and how GROUP BY t2.word_id, l.verb_id can / should be added
        $db_con->db_type = sql_db::POSTGRES;
        $sql_where = sql_db::LNK_TBL . '.to_phrase_id   = 3';
        $sql_type = 'AND l.verb_id = 2';
        $sql_wrd1_fields = '';
        $sql_wrd1_from = '';
        $sql_wrd1 = '';
        $sql_wrd2_fields = "t2.word_id AS word_id2,
                t2.user_id AS user_id2,
                 CASE WHEN (u2.word_name <> '' IS NOT TRUE) THEN t2.word_name ELSE u2.word_name END AS word_name,
                 CASE WHEN (u2.plural <> '' IS NOT TRUE) THEN t2.plural ELSE u2.plural END AS plural,
                 CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description ELSE u2.description END AS description,
                 CASE WHEN (u2.phrase_type_id IS NULL) THEN t2.phrase_type_id ELSE u2.phrase_type_id END AS phrase_type_id,
                 CASE WHEN (u2.excluded IS NULL) THEN t2.excluded ELSE u2.excluded END AS excluded,
                  t2.values AS values2";
        $sql_wrd2_from = ' words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 ';
        $sql_wrd2 = sql_db::LNK_TBL . '.from_phrase_id = t2.word_id';
        $created_sql = "SELECT l.triple_id,
                       l.from_phrase_id,
                       l.verb_id,
                       l.to_phrase_id,
                       l.description,
                       l.name,
                       v.verb_id,
                       v.code_id,
                       v.verb_name,
                       v.name_plural,
                       v.name_reverse,
                       v.name_plural_reverse,
                       v.formula_name,
                       v.description,
                       " . $db_con->get_usr_field(fields::FLD_EXCLUDED, 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
                       " . $sql_wrd1_fields . "
                       " . $sql_wrd2_fields . "
                  FROM triples l
             LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       " . $sql_wrd1_from . "
                       " . $sql_wrd2_from . "
                 WHERE l.verb_id = v.verb_id 
                       " . $sql_wrd1 . "
                   AND " . $sql_wrd2 . " 
                   AND " . $sql_where . "
                       " . $sql_type . " 
              ORDER BY l.verb_id, word_name;";
        $test_name = 'phrase load word link query by ...';
        $expected_sql = $t->file('db/sql_creator/word_link_list_no_group.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the general phrase list query (as created in phrase->sql_list)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                                  ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                  ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                             FROM words w   
                        LEFT JOIN user_words u ON u.word_id = w.word_id 
                                              AND u.user_id = 3 ';
        $sql_triples = 'SELECT DISTINCT l.triple_id * -1 AS id, 
                                    ' . $db_con->get_usr_field("name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                               FROM triples l
                          LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                     AND u.user_id = 3 ';
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $test_name = 'general phrase list query';
        $expected_sql = $t->file('db/sql_creator/phrase_list_general.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));

        // the general phrase list query by type (as created in phrase->sql_list)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_where_exclude = 'excluded = 0';
        $sql_field_names = 'id, phrase_name, excluded';
        $sql_wrd_all = 'SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 3
                                         WHERE l.to_phrase_id = 2
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';
        $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 3
                                         WHERE l.to_phrase_id <> 2
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (' . $sql_wrd_all . ') ) AS o 
                                         WHERE ' . $sql_where_exclude . ' ';
        $sql_words = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                             ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                             ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                        FROM ( ' . $sql_wrd_all . ' ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 3
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ' )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE ' . $sql_where_exclude . ' ';
        $sql_triples = 'SELECT DISTINCT ' . $sql_field_names . ' FROM (
                        SELECT DISTINCT
                               l.triple_id * -1 AS id, 
                               ' . $db_con->get_usr_field("name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 3
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = 2
                           AND l.to_phrase_id = 2 ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $test_name = 'general phrase list query by type';
        $expected_sql = $t->file('db/sql_creator/phrase_list_general_by_type.sql');
        $t->assert($test_name, $lib->trim($created_sql), $lib->trim($expected_sql));


        $t->subheader($ts . 'user sandbox sql creation');

        // init
        $t->name = '_sandbox->';
        $t->resource_path = 'db/sandbox/';

        // the word changer query (used in _sandbox->changer_sql)
        $wrd = new word($t->usr1);
        $wrd->id = 1;
        $sc = $db_con->sql_creator();
        $sc->db_type = sql_db::POSTGRES;
        $qp = $wrd->load_sql_changer($sc);
        $t->assert_qp($qp, $sc->db_type);

        // ... and for MySQL
        $sc->db_type = sql_db::MYSQL;
        $qp = $wrd->load_sql_changer($sc);
        $t->assert_qp($qp, $sc->db_type);

        // ... and the word changer ex owner query (used in _sandbox->changer_sql)
        $wrd->set_owner_id(2);
        $sc->db_type = sql_db::POSTGRES;
        $qp = $wrd->load_sql_changer($sc);
        $t->assert_qp($qp, $sc->db_type);

        // ... and for MySQL
        $sc->db_type = sql_db::MYSQL;
        $qp = $wrd->load_sql_changer($sc);
        $t->assert_qp($qp, $sc->db_type);
    }

    /**
     * unit_test for prg_version_is_newer
     */
    function prg_version_is_newer_test(test_cleanup $t): void
    {
        $lib = new library();
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.0.1'));
        $target = 'false';
        $t->assert('prg_version 0.0.1 is newer than ' . def::PRG_VERSION, $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer(def::PRG_VERSION));
        $target = 'false';
        $t->assert('prg_version ' . def::PRG_VERSION . ' is newer than ' . def::PRG_VERSION, $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer(def::NEXT_VERSION));
        $target = 'true';
        $t->assert('prg_version ' . def::NEXT_VERSION . ' is newer than ' . def::PRG_VERSION, $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.1.0', '0.0.9'));
        $target = 'true';
        $t->assert('prg_version 0.1.0 is newer than 0.0.9', $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.2.3', '1.1.1'));
        $target = 'false';
        $t->assert('prg_version 0.2.3 is newer than 1.1.1', $result, $target);
        // a missing build number is the same as a build number of zero
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.0.3.0', '0.0.3'));
        $target = 'false';
        $t->assert('prg_version 0.0.3.0 is newer than 0.0.3', $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.0.3', '0.0.3.0'));
        $target = 'false';
        $t->assert('prg_version 0.0.3 is newer than 0.0.3.0', $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.0.3.1', '0.0.3'));
        $target = 'true';
        $t->assert('prg_version 0.0.3.1 is newer than 0.0.3', $result, $target);
        $result = $lib->dsp_bool($lib->prg_version_is_newer('0.0.3', '0.0.3.1'));
        $target = 'false';
        $t->assert('prg_version 0.0.3 is newer than 0.0.3.1', $result, $target);
    }
}