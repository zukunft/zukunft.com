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

namespace unit;

include_once SERVICE_PATH . 'config.php';
include_once DB_PATH . 'sql.php';
include_once MODEL_REF_PATH . 'source.php';
include_once MODEL_GROUP_PATH . 'group.php';
include_once MODEL_VALUE_PATH . 'value.php';
include_once SHARED_CONST_PATH . 'words.php';

use cfg\component\component;
use cfg\component\component_link;
use cfg\component\component_link_list;
use cfg\config;
use cfg\db\sql;
use cfg\db\sql_creator;
use cfg\db\sql_db;
use cfg\formula\formula;
use cfg\formula\formula_link;
use cfg\formula\formula_link_type;
use cfg\phrase\phrase;
use cfg\ref\source;
use cfg\ref\source_type;
use cfg\sandbox\sandbox;
use cfg\sandbox\sandbox_link;
use cfg\sandbox\sandbox_named;
use cfg\user\user;
use cfg\value\value;
use cfg\verb\verb;
use cfg\view\view;
use cfg\word\triple;
use cfg\word\word;
use cfg\word\word_db;
use shared\library;
use shared\const\sources;
use shared\const\words;
use test\test_cleanup;

class sandbox_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $lib = new library();

        // start the test section (ts)
        $ts = 'unit sandbox ';
        $t->header($ts);

        $t->subheader($ts . 'name list');
        $test_name = 'names match cached names';
        $wrd_lst = $t->word_list();
        // call the names function with a high limit to force the usage of the slow loop
        $name_list = implode('.', $wrd_lst->names(100));
        $name_list_cache = implode('.', array_keys($wrd_lst->name_pos_lst()));
        $t->assert($test_name, $name_list_cache, $name_list);
        $test_name = 'names match not cached names including excluded';
        $name_list_ex = implode('.', array_keys($wrd_lst->name_pos_lst_all()));
        $wrd_ex = $t->word_education();
        $wrd_ex->exclude();
        $wrd_lst->add_by_name($wrd_ex);
        $name_list_ex_cache = implode('.', array_keys($wrd_lst->name_pos_lst_all()));
        // TODO activate and add the handling of excluded named objects
        //$t->assert_not($test_name, $name_list_ex_cache, $name_list);
        $test_name = 'cached names match cached names including excluded';
        //$t->assert($test_name, $name_list_ex_cache, $name_list_ex);


        $t->subheader($ts . 'link');
        $test_name = 'name with key separator can be used';
        $wrd = $t->word();
        $to = $t->word();
        $vrb = $t->verb();
        $wrd->set_name($wrd->name() . sandbox_link::KEY_SEP . $vrb->name());
        $trp = new triple($usr);
        $trp->set_from($wrd->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($to->phrase());
        $key_vrb = $trp->key();
        $wrd->set_name($t->word()->name());
        $to->set_name($vrb->name() . sandbox_link::KEY_SEP . $to->name());
        $key_to = $trp->key();
        $t->assert_not($test_name, $key_vrb, $key_to);
        // TODO activate this test based on changing the verb
        //      which implies that the changing of the verb name is updating the cache
        //      so a requirement is that the cache update trigger is implemented
        /*
        $wrd = $t->word();
        $to = $t->word();
        $vrb = $t->verb();
        $vrb->set_name($vrb->name() . sandbox_link::KEY_SEP . $wrd->name());
        $trp = new triple($usr);
        $trp->set_from($wrd->phrase());
        $trp->set_verb($vrb);
        $trp->set_to($to->phrase());
        $key_vrb = $trp->key();
        $vrb->set_name($t->verb()->name());
        $to->set_name($to->name() . sandbox_link::KEY_SEP . $to->name());
        $key_to = $trp->key();
        $t->assert_not($test_name, $key_vrb, $key_to);
        */


        $t->subheader($ts . 'link list');
        $lst = new component_link_list($usr);
        $test_name = 'add link is fine';
        $result = $lst->add_link($t->component_link());
        $t->assert_true($test_name, $result);
        $test_name = 'adding link twice is rejected';
        $result = $lst->add_link($t->component_link());
        $t->assert_false($test_name, $result);
        $lst = new component_link_list($usr);
        $test_name = 'add component is fine';
        $result = $lst->add(1, $t->view(), $t->component(), 1);
        $t->assert_true($test_name, $result);
        $test_name = 'add component at the same position is rejected';
        $result = $lst->add(1, $t->view(), $t->component(), 1);
        $t->assert_false($test_name, $result);
        $test_name = 'add component at a different position is fine';
        $result = $lst->add(2, $t->view(), $t->component(), 2);
        $t->assert_true($test_name, $result);
        $test_name = 'add same component at different position without db id is fine';
        $result = $lst->add(0, $t->view(), $t->component(), 3);
        $t->assert_true($test_name, $result);
        $test_name = 'add same component at different position with same db id is rejected';
        $result = $lst->add(1, $t->view(), $t->component(), 3);
        $t->assert_false($test_name, $result);

        // TODO review the tests below e.g. by using the test section ($ts) and $test_name like above
        $t->subheader($ts . 'functions that does not need a database connection');

        // test if two sources are supposed to be the same
        $src1 = new source($usr);
        $src1->set(1, sources::IPCC_AR6_SYNTHESIS);
        $src2 = new source($usr);
        $src2->set(2, sources::IPCC_AR6_SYNTHESIS);
        $result = $src1->is_same($src2);
        $t->assert("are two sources supposed to be the same", $result, true);

        // ... and they are of course also similar
        $result = $src1->is_similar($src2);
        $t->assert("... and similar", $result, true);

        // TODO review test (start with test_name="" and move the creation to the test object creation)
        // a source can have the same name as a word
        $wrd1 = new word($usr);
        $wrd1->set_id(1);
        $wrd1->set_name(sources::IPCC_AR6_SYNTHESIS);
        $src2 = new source($usr);
        $src2->set_id(2);
        $src2->set_name(sources::IPCC_AR6_SYNTHESIS);
        $result = $wrd1->is_same($src2);
        $t->assert("a source is not the same as a word even if they have the same name", $result, false);

        // but a formula should not have the same name as a word
        $wrd = new word($usr);
        $wrd->set_name(words::MIO);
        $frm = new formula($usr);
        $frm->set_name(words::MIO);
        $result = $wrd->is_similar($frm);
        $t->assert("a formula should not have the same name as a word", $result, true);

        // ... but they are not the same
        $result = $wrd->is_same($frm);
        $t->assert("... but they are not the same", $result, false);

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

        prg_version_is_newer_test($t);


        // start the test section (ts)
        $ts = 'unit database connector ';
        $t->header($ts);

        $db_con = new sql_db();

        /*
         * General tests (one by one for each database)
         */

        // test a simple SQL user select query for Postgres by name
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(user::class);
        $db_con->set_name('formula_link_norm_by_id');
        $db_con->set_usr(SYSTEM_USER_ID);
        $db_con->set_where_std(null, 'Test User');
        $created_sql = $db_con->select_by_set_id();
        // TODO use the file
        $expected_sql = $t->file('db/formula/formula_link_by_id.sql');
        $expected_sql = "PREPARE formula_link_norm_by_id (text) AS SELECT user_id, user_name FROM users WHERE user_name = $1;";
        $t->display('Postgres select max', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(user::class);
        $db_con->set_name('formula_link_norm_by_id_mysql');
        $db_con->set_usr(SYSTEM_USER_ID);
        $db_con->set_where_std(null, 'Test User');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "PREPARE formula_link_norm_by_id_mysql FROM 'SELECT user_id,  user_name FROM users WHERE user_name = ?';";
        $t->display('MySQL select max', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a simple SQL max select creation for Postgres without where
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(value::class);
        $db_con->set_fields(array('MAX(group_id) AS max_id'));
        $created_sql = $db_con->select_by_set_id(false);
        $expected_sql = "SELECT MAX(group_id) AS max_id FROM values;";
        $t->display('Postgres select max', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(value::class);
        $db_con->set_fields(array('MAX(group_id) AS max_id'));
        $created_sql = $db_con->select_by_set_id(false);
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " MAX(group_id) AS max_id FROM `values`;";
        $t->display('MySQL select max', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a simple SQL select creation for Postgres without the standard id and name identification
        $sc = new sql_creator();
        $sc->set_db_type(sql_db::POSTGRES);
        $sc->set_class(config::class);
        $sc->set_name('query_test');
        $sc->set_fields(array('value'));
        $sc->add_where(sql::FLD_CODE_ID, config::VERSION_DB);
        $created_sql = $sc->sql();
        $expected_sql = "PREPARE query_test (text) AS SELECT config_id,  config_name,  value FROM config WHERE code_id = $1 AND code_id IS NOT NULL;";
        $t->assert('non id Postgres select', $lib->trim($created_sql), $lib->trim($expected_sql));
        $created_par = implode(',', $sc->get_par());
        $expected_par = "version_database";
        $t->assert('non id Postgres parameter', $lib->trim($created_par), $lib->trim($expected_par));

        // ... same for MySQL
        $sc->db_type = sql_db::MYSQL;
        $sc->set_class(config::class);
        $sc->set_name('query_test');
        $sc->set_fields(array('value'));
        $sc->add_where(sql::FLD_CODE_ID, config::VERSION_DB);
        $created_sql = $sc->sql();
        $expected_sql = "PREPARE query_test FROM 'SELECT config_id,  config_name,  `value` FROM config WHERE code_id = ?';";
        $t->assert('non id MySQL select', $lib->trim($created_sql), $lib->trim($expected_sql));
        $created_par = implode(',', $sc->get_par());
        $expected_par = "version_database";
        $t->assert('non id MySQL parameter', $lib->trim($created_par), $lib->trim($expected_par));
        $t->display('non id MySQL select', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a simple SQL select creation for Postgres with the standard id and name identification
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(source_type::class);
        $db_con->set_name('source_type_by_id');
        $db_con->set_where_std(2);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "PREPARE source_type_by_id (bigint) AS 
              SELECT source_type_id,  type_name 
                FROM source_types
               WHERE source_type_id = $1;";
        $t->display('Postgres select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(source_type::class);
        $db_con->set_name('source_type_by_id');
        $db_con->set_where_std(2);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "PREPARE source_type_by_id FROM
             'SELECT source_type_id, type_name
                FROM source_types
               WHERE source_type_id = ?';";
        $t->display('MySQL select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a simple SQL select of the user defined word for Postgres by the id
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_fields(array(word_db::FLD_PLURAL, sandbox_named::FLD_DESCRIPTION, phrase::FLD_TYPE, word_db::FLD_VIEW));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT word_id,
                     word_name,
                     plural,
                     description,
                     phrase_type_id,
                     view_id
                FROM user_words
               WHERE word_id = $1 
                 AND user_id = $2;';
        $t->display('Postgres user word select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_fields(array('plural', sandbox_named::FLD_DESCRIPTION, 'phrase_type_id', 'view_id'));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT word_id,
                     word_name,
                     plural,
                     description,
                     phrase_type_id,
                     view_id
                FROM user_words
               WHERE word_id = ? 
                 AND user_id = ?;';
        $t->display('MySQL user word select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a very simple SQL select of the user defined word for Postgres by the id
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT word_id,
                     word_name
                FROM user_words
               WHERE word_id = $1 
                 AND user_id = $2;';
        $t->display('Postgres user word id select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(word::class, true);
        $db_con->set_usr(1);
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT word_id,
                     word_name
                FROM user_words
               WHERE word_id = ? 
                 AND user_id = ?;';
        $t->display('MySQL user word id select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a simple SQL select the formulas linked to a phrase
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, 1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT 
                        formula_link_id,  
                        formula_id,  
                        phrase_id
                   FROM formula_links
                  WHERE phrase_id = $1;';
        $t->display('Postgres formulas linked to a phrase select based on phrase id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_where_link_no_fld(0, 0, 1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = 'SELECT 
                    formula_link_id,  
                    formula_id,  
                    phrase_id
               FROM formula_links
              WHERE phrase_id = ?;';
        $t->display('MySQL formulas linked to a phrase select based on phrase id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test a list of links SQL select creation for Postgres selected by a linked object
        /*
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_type(sql_db::TBL_TRIPLE);
        $db_con->set_join_fields(array(sql::FLD_CODE_ID, 'name_plural','name_reverse','name_plural_reverse','formula_name',sandbox_named::FLD_DESCRIPTION), sql_db::TBL_VERB);
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
        $t->display('Postgres select based on id', $lib->trim($expected_sql), $lib->trim($created_sql));
        */

        /*
         * Start of the concrete database object test fpr Postgres
         */

        // test a SQL select creation of user sandbox data for Postgres
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array(source::FLD_URL, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_id = $1;";
        $t->display('Postgres user sandbox select', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for search by name
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array(source::FLD_URL, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE (u.source_name = $1 
                     OR (s.source_name = $1 AND u.source_name IS NULL));";
        $t->display('Postgres user sandbox select by name', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for search by code_id
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array(source::FLD_URL, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, '', 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT " . "
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        CASE WHEN (u.source_name    <> '' IS NOT TRUE) THEN s.source_name    ELSE u.source_name    END AS source_name,
                        CASE WHEN (u.url            <> '' IS NOT TRUE) THEN s.url            ELSE u.url            END AS url,
                        CASE WHEN (u.description    <> '' IS NOT TRUE) THEN s.description    ELSE u.description    END AS description,
                        CASE WHEN (u.source_type_id IS           NULL) THEN s.source_type_id ELSE u.source_type_id END AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.code_id = $1 AND s.code_id IS NOT NULL;";
        $t->display('Postgres user sandbox select by code_id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for all users by id
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID, 'url', sandbox_named::FLD_DESCRIPTION, 'source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT
                        source_id,
                        source_name,
                        code_id,
                        url,
                        description,
                        source_type_id
                   FROM sources 
                  WHERE source_id = $1;";
        $t->display('Postgres all user select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... similar with joined fields
        $db_con->set_class(formula::class);
        $db_con->set_fields(array(
            user::FLD_ID,
            formula::FLD_FORMULA_TEXT,
            formula::FLD_FORMULA_USER_TEXT,
            sandbox_named::FLD_DESCRIPTION,
            formula::FLD_TYPE,
            formula::FLD_ALL_NEEDED,
            formula::FLD_LAST_UPDATE,
            sandbox::FLD_EXCLUDED));
        $db_con->set_join_fields(array(sql::FLD_CODE_ID), 'formula_type');
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT s.formula_id,
                     s.formula_name,
                     s.user_id,
                     s.formula_text,
                     s.resolved_text,
                     s.description,
                     s.formula_type_id,
                     s.all_values_needed,
                     s.last_update,
                     s.excluded,
                     l.code_id 
                FROM formulas s
           LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
               WHERE s.formula_id = $1;";
        $t->display('Postgres all user join select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for user sandbox data (should match with the parameters in formula->load)
        $db_con->set_class(formula::class);
        $db_con->set_usr_fields(array('formula_text', 'resolved_text', sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(
            formula::FLD_TYPE,
            formula::FLD_ALL_NEEDED,
            formula::FLD_LAST_UPDATE));
        $db_con->set_usr_bool_fields(array(sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT s.formula_id,
                       u.formula_id AS user_formula_id,
                       s.user_id,
                       CASE WHEN (u.formula_name      <> '' IS NOT TRUE) THEN s.formula_name         ELSE u.formula_name         END AS formula_name,
                       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN s.formula_text         ELSE u.formula_text         END AS formula_text,
                       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN s.resolved_text        ELSE u.resolved_text        END AS resolved_text,
                       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN s.description          ELSE u.description          END AS description,
                       CASE WHEN (u.formula_type_id   IS           NULL) THEN s.formula_type_id      ELSE u.formula_type_id      END AS formula_type_id,
                       CASE WHEN (u.all_values_needed IS           NULL) THEN s.all_values_needed    ELSE u.all_values_needed    END AS all_values_needed,
                       CASE WHEN (u.last_update       IS           NULL) THEN s.last_update          ELSE u.last_update          END AS last_update,
                       CASE WHEN (u.excluded          IS           NULL) THEN COALESCE(s.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                  FROM formulas s
             LEFT JOIN user_formulas u ON s.formula_id = u.formula_id 
                                      AND u.user_id = 1 
               WHERE s.formula_id = $1;";
        $t->display('Postgres user sandbox join select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for a link table
        $db_con->set_class(triple::class);
        $db_con->set_fields(array(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID, 'phrase_type_id'));
        $db_con->set_usr_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION, sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('s.triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT s.triple_id,
                     u.triple_id AS user_triple_id,
                     s.user_id,
                     s.from_phrase_id,
                     s.to_phrase_id,
                     s.verb_id,
                     s.phrase_type_id,
                     CASE WHEN (u.name_given  <> '' IS NOT TRUE) THEN s.name_given  ELSE u.name_given  END AS name_given,
                     CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description,
                     CASE WHEN (u.excluded    <> '' IS NOT TRUE) THEN s.excluded    ELSE u.excluded    END AS excluded
                FROM triples s 
           LEFT JOIN user_triples u ON s.triple_id = u.triple_id 
                                      AND u.user_id = 1 
               WHERE s.triple_id = 1;";
        $t->display('Postgres user sandbox link select by where text', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the view load_standard SQL creation
        $db_con->set_class(view::class);
        $db_con->set_fields(array(sandbox_named::FLD_DESCRIPTION, view::FLD_TYPE, sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT view_id,
                     view_name,
                     description,
                     view_type_id,
                     excluded
                FROM views
               WHERE view_id = $1;";
        $t->display('Postgres view load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the view load SQL creation
        $db_con->set_class(view::class);
        $db_con->set_usr_fields(array(sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(view::FLD_TYPE, sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.view_id, 
                        u.view_id AS user_view_id, 
                        s.user_id, 
                        CASE WHEN (u.view_name <> ''   IS NOT TRUE) THEN s.view_name    ELSE u.view_name    END AS view_name, 
                        CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description  ELSE u.description  END AS description, 
                        CASE WHEN (u.view_type_id      IS     NULL) THEN s.view_type_id ELSE u.view_type_id END AS view_type_id, 
                        CASE WHEN (u.excluded          IS     NULL) THEN s.excluded     ELSE u.excluded     END AS excluded 
                   FROM views s 
              LEFT JOIN user_views u ON s.view_id = u.view_id 
                                    AND u.user_id = 1 
                  WHERE s.view_id = $1;";
        $t->display('Postgres view load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component_link load_standard SQL creation
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view::FLD_ID, component::FLD_ID);
        $db_con->set_fields(array(component_link::FLD_ORDER_NBR, component_link::FLD_POS_TYPE, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT component_link_id,
                     view_id,
                     component_id,
                     order_nbr,
                     position_type_id,
                     excluded
                FROM component_links 
               WHERE component_link_id = $1;";
        $t->display('Postgres component_link load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same but select by the link ids
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view::FLD_ID, component::FLD_ID);
        $db_con->set_fields(array(component_link::FLD_ORDER_NBR, component_link::FLD_POS_TYPE, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(0, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT component_link_id,
                     view_id,
                     component_id,
                     order_nbr,
                     position_type_id,
                     excluded
                FROM component_links 
               WHERE view_id = $1 AND component_id = $2;";
        $t->display('Postgres component_link load_standard select by link ids', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component_link load SQL creation
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view::FLD_ID, component::FLD_ID);
        $db_con->set_usr_num_fields(array(component_link::FLD_ORDER_NBR, component_link::FLD_POS_TYPE, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.component_link_id, 
                        u.component_link_id AS user_component_link_id, 
                        s.user_id, 
                        s.view_id, 
                        s.component_id, 
                        CASE WHEN (u.order_nbr        IS NULL) THEN s.order_nbr        ELSE u.order_nbr        END AS order_nbr, 
                        CASE WHEN (u.position_type_id IS NULL) THEN s.position_type_id ELSE u.position_type_id END AS position_type_id, 
                        CASE WHEN (u.excluded         IS NULL) THEN s.excluded         ELSE u.excluded         END AS excluded 
                   FROM component_links s 
              LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id 
                                                   AND u.user_id = 1 
                  WHERE s.component_link_id = $1;";
        $t->display('Postgres component_link load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the formula_link load_standard SQL creation
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_fields(array(formula_link_type::FLD_ID, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT formula_link_id,
                     formula_id,
                     phrase_id,
                     formula_link_type_id,
                     excluded
                FROM formula_links 
               WHERE formula_link_id = $1;";
        $t->display('Postgres formula_link load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the formula_link load SQL creation
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(array(formula_link_type::FLD_ID, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.formula_link_id, 
                        u.formula_link_id AS user_formula_link_id, 
                        s.user_id, 
                        s.formula_id, 
                        s.phrase_id, 
                        CASE WHEN (u.formula_link_type_id IS NULL) THEN s.formula_link_type_id ELSE u.formula_link_type_id END AS formula_link_type_id, 
                        CASE WHEN (u.excluded IS NULL) THEN s.excluded ELSE u.excluded END AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1 
                  WHERE s.formula_link_id = $1;";
        $t->display('Postgres formula_link load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component load_standard SQL creation
        $db_con->set_class(component::class);
        $db_con->set_fields(array(sandbox_named::FLD_DESCRIPTION, 'component_type_id', 'word_id_row', 'link_type_id', formula::FLD_ID, 'word_id_col', 'word_id_col2', sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT component_id,
                     component_name,
                     description,
                     component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM components
               WHERE component_id = $1;";
        $t->display('Postgres component load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component load SQL creation
        $db_con->set_class(component::class);
        $db_con->set_usr_fields(array(sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('component_type_id', 'word_id_row', 'link_type_id', formula::FLD_ID, 'word_id_col', 'word_id_col2', sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.component_id,
                        u.component_id AS user_component_id,  
                        s.user_id,  
                        CASE WHEN (u.component_name <> '' IS NOT TRUE) THEN s.component_name    ELSE u.component_name    END AS component_name,  
                        CASE WHEN (u.description         <> '' IS NOT TRUE) THEN s.description            ELSE u.description            END AS description,   
                        CASE WHEN (u.component_type_id    IS NULL)     THEN s.component_type_id ELSE u.component_type_id END AS component_type_id,  
                        CASE WHEN (u.word_id_row               IS NULL)     THEN s.word_id_row            ELSE u.word_id_row            END AS word_id_row,  
                        CASE WHEN (u.link_type_id              IS NULL)     THEN s.link_type_id           ELSE u.link_type_id           END AS link_type_id,  
                        CASE WHEN (u.formula_id                IS NULL)     THEN s.formula_id             ELSE u.formula_id             END AS formula_id,  
                        CASE WHEN (u.word_id_col               IS NULL)     THEN s.word_id_col            ELSE u.word_id_col            END AS word_id_col,  
                        CASE WHEN (u.word_id_col2              IS NULL)     THEN s.word_id_col2           ELSE u.word_id_col2           END AS word_id_col2,  
                        CASE WHEN (u.excluded                  IS NULL)     THEN s.excluded               ELSE u.excluded               END AS excluded
                   FROM components s 
              LEFT JOIN user_components u ON s.component_id = u.component_id 
                                              AND u.user_id = 1 
                  WHERE s.component_id = $1;";
        $t->display('Postgres component load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the triple load_standard SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID);
        $db_con->set_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION, sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT triple_id,
                     from_phrase_id,
                     to_phrase_id,
                     verb_id,
                     name_given,
                     description,
                     excluded
                FROM triples 
               WHERE triple_id = 1;";
        $t->display('Postgres triple load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the triple load SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID);
        $db_con->set_fields(array('phrase_type_id'));
        $db_con->set_usr_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('s.triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        s.triple_id, 
                        u.triple_id AS user_triple_id, 
                        s.user_id,
                        s.from_phrase_id,
                        s.to_phrase_id,
                        s.verb_id, 
                        s.phrase_type_id, 
                        CASE WHEN (u.name_given  <> '' IS NOT TRUE) THEN s.name_given  ELSE u.name_given  END AS name_given, 
                        CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description, 
                        CASE WHEN (u.excluded          IS     NULL) THEN s.excluded    ELSE u.excluded    END AS excluded 
                   FROM triples s 
              LEFT JOIN user_triples u ON s.triple_id = u.triple_id 
                                         AND u.user_id = 1 
                  WHERE s.triple_id = 1;";
        $t->display('Postgres triple load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the verb_list load SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_usr_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(sandbox::FLD_EXCLUDED));
        $db_con->set_join_fields(
            array(sql::FLD_CODE_ID, 'verb_name', 'name_plural', 'name_reverse', 'name_plural_reverse', 'formula_name', sandbox_named::FLD_DESCRIPTION),
            verb::class);
        $db_con->set_fields(array(verb::FLD_ID));
        $db_con->set_where_text('s.to_phrase_id = 2');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                            s.triple_id, 
                            u.triple_id AS user_triple_id, 
                            s.user_id, 
                            s.verb_id, 
                            l.code_id, 
                            l.verb_name, 
                            l.name_plural, 
                            l.name_reverse, 
                            l.name_plural_reverse, 
                            l.formula_name, 
                            l.description,
                            CASE WHEN (u.name_given  <> '' IS NOT TRUE) THEN s.name_given  ELSE u.name_given  END AS name_given, 
                            CASE WHEN (u.description <> '' IS NOT TRUE) THEN s.description ELSE u.description END AS description, 
                            CASE WHEN (u.excluded          IS     NULL) THEN s.excluded    ELSE u.excluded    END AS excluded 
                       FROM triples s 
                  LEFT JOIN user_triples u ON s.triple_id = u.triple_id 
                                             AND u.user_id = 1 
                  LEFT JOIN verbs l ON s.verb_id = l.verb_id 
                      WHERE s.to_phrase_id = 2;";
        $t->display('Postgres verb_list load', $lib->trim($expected_sql), $lib->trim($created_sql));

        /*
         * Start of the corresponding MySQL tests
         */

        // ... and search by id for MySQL
        $db_con->db_type = sql_db::MYSQL;
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array('url', sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.description    IS NULL, s.description,    u.description)    AS description,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.source_id = ?;";
        $t->display('MySQL user sandbox select', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for search by name
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array('url', sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . "
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.description    IS NULL, s.description,    u.description)    AS description,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE (u.source_name = ? 
                     OR (s.source_name = ? AND u.source_name IS NULL));";
        $t->display('MySQL user sandbox select by name', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for search by code_id
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID));
        $db_con->set_usr_fields(array('url', sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('source_type_id'));
        $db_con->set_where_std(0, '', 'wikidata');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . "
                        s.source_id,
                        u.source_id AS user_source_id,
                        s.user_id,
                        s.code_id,
                        IF(u.source_name    IS NULL, s.source_name,    u.source_name)    AS source_name,
                        IF(u.`url`          IS NULL, s.`url`,          u.`url`)          AS `url`,
                        IF(u.description    IS NULL, s.description,    u.description)    AS description,
                        IF(u.source_type_id IS NULL, s.source_type_id, u.source_type_id) AS source_type_id
                   FROM sources s 
              LEFT JOIN user_sources u ON s.source_id = u.source_id 
                                      AND u.user_id = 1 
                  WHERE s.code_id = ?;";
        $t->display('MySQL user sandbox select by code_id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for all users by id
        $db_con->set_class(source::class);
        $db_con->set_fields(array(sql::FLD_CODE_ID, 'url', sandbox_named::FLD_DESCRIPTION, 'source_type_id'));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT
                        source_id,
                        source_name,
                        code_id,
                        `url`,
                        description,
                        source_type_id
                   FROM sources 
                  WHERE source_id = ?;";
        $t->display('MySQL all user select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... similar with joined fields
        $db_con->set_class(formula::class);
        $db_con->set_fields(array(
            user::FLD_ID,
            formula::FLD_FORMULA_TEXT,
            formula::FLD_FORMULA_USER_TEXT,
            sandbox_named::FLD_DESCRIPTION,
            formula::FLD_TYPE,
            formula::FLD_ALL_NEEDED,
            formula::FLD_LAST_UPDATE,
            sandbox::FLD_EXCLUDED));
        $db_con->set_join_fields(array(sql::FLD_CODE_ID), 'formula_type');
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT s.formula_id,
                     s.formula_name,
                     s.user_id,
                     s.formula_text,
                     s.resolved_text,
                     s.description,
                     s.formula_type_id,
                     s.all_values_needed,
                     s.last_update,
                     s.excluded,
                     l.code_id
                FROM formulas s
           LEFT JOIN formula_types l ON s.formula_type_id = l.formula_type_id 
               WHERE s.formula_id = ?;";
        $t->display('MySQL all user join select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for user sandbox data
        $db_con->set_class(formula::class);
        $db_con->set_usr_fields(array(
            formula::FLD_FORMULA_TEXT,
            formula::FLD_FORMULA_USER_TEXT,
            sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array(
            formula::FLD_TYPE,
            formula::FLD_ALL_NEEDED,
            formula::FLD_LAST_UPDATE,
            sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1, '');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                        s.formula_id, 
                        u.formula_id AS user_formula_id, 
                        s.user_id, 
                        IF(u.formula_name      IS NULL, s.formula_name,      u.formula_name)      AS formula_name, 
                        IF(u.formula_text      IS NULL, s.formula_text,      u.formula_text)      AS formula_text, 
                        IF(u.resolved_text     IS NULL, s.resolved_text,     u.resolved_text)     AS resolved_text, 
                        IF(u.description       IS NULL, s.description,       u.description)       AS description, 
                        IF(u.formula_type_id   IS NULL, s.formula_type_id,   u.formula_type_id)   AS formula_type_id, 
                        IF(u.all_values_needed IS NULL, s.all_values_needed, u.all_values_needed) AS all_values_needed, 
                        IF(u.last_update       IS NULL, s.last_update,       u.last_update)       AS last_update, 
                        IF(u.excluded          IS NULL, s.excluded,          u.excluded)          AS excluded
                   FROM formulas s 
              LEFT JOIN user_formulas u ON s.formula_id = u.formula_id 
                                       AND u.user_id = 1 
                  WHERE s.formula_id = ?;";
        $t->display('MySQL all user join select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // ... same for a link table
        $db_con->set_class(triple::class);
        $db_con->set_fields(array(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID, 'phrase_type_id'));
        $db_con->set_usr_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION, sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('s.triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " s.triple_id,
                     u.triple_id AS user_triple_id,
                     s.user_id,
                     s.from_phrase_id,
                     s.to_phrase_id,
                     s.verb_id,
                     s.phrase_type_id,
                     IF(u.name_given  IS NULL, s.name_given,  u.name_given)  AS name_given,
                     IF(u.description IS NULL, s.description, u.description) AS description,
                     IF(u.excluded    IS NULL, s.excluded,    u.excluded)    AS excluded
                FROM triples s 
           LEFT JOIN user_triples u ON s.triple_id = u.triple_id 
                                      AND u.user_id = 1 
               WHERE s.triple_id = 1;";
        $t->display('MySQL user sandbox link select by where text', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component_link load_standard SQL creation
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view::FLD_ID, component::FLD_ID);
        $db_con->set_fields(array('order_nbr', 'position_type_id', sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        component_link_id,
                        view_id,
                        component_id,
                        order_nbr,
                        position_type_id,
                        excluded
                   FROM component_links 
                  WHERE component_link_id = ?;";
        $t->display('MySQL component_link load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component_link load SQL creation
        $db_con->set_class(component_link::class);
        $db_con->set_link_fields(view::FLD_ID, component::FLD_ID);
        $db_con->set_usr_num_fields(array('order_nbr', 'position_type_id', sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1, 2, 3);
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                        s.component_link_id, 
                        u.component_link_id AS user_component_link_id, 
                        s.user_id, s.view_id, s.component_id, 
                        IF(u.order_nbr     IS NULL, s.order_nbr,     u.order_nbr)     AS order_nbr, 
                        IF(u.position_type_id IS NULL, s.position_type_id, u.position_type_id) AS position_type_id, 
                        IF(u.excluded      IS NULL, s.excluded,      u.excluded)      AS excluded 
                   FROM component_links s 
              LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id 
                                                   AND u.user_id = 1 
                  WHERE s.component_link_id = ?;";
        $t->display('MySQL component_link load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the formula_link load_standard SQL creation
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_fields(array(formula_link_type::FLD_ID, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT formula_link_id,
                     formula_id,
                     phrase_id,
                     formula_link_type_id,
                     excluded
                FROM formula_links 
               WHERE formula_link_id = ?;";
        $t->display('MySQL formula_link load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the formula_link load SQL creation
        $db_con->set_class(formula_link::class);
        $db_con->set_link_fields(formula::FLD_ID, phrase::FLD_ID);
        $db_con->set_usr_num_fields(array(formula_link_type::FLD_ID, sandbox::FLD_EXCLUDED));
        $db_con->set_where_link_no_fld(1);
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                        s.formula_link_id,  
                        u.formula_link_id AS user_formula_link_id,  
                        s.user_id,  
                        s.formula_id,  
                        s.phrase_id,          
                        IF(u.formula_link_type_id IS NULL, s.formula_link_type_id, u.formula_link_type_id) AS formula_link_type_id,          
                        IF(u.excluded     IS NULL, s.excluded,     u.excluded)     AS excluded 
                   FROM formula_links s 
              LEFT JOIN user_formula_links u ON s.formula_link_id = u.formula_link_id 
                                            AND u.user_id = 1
                  WHERE s.formula_link_id = ?;";
        $t->display('MySQL formula_link load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component load_standard SQL creation
        $db_con->set_class(component::class);
        $db_con->set_fields(array(sandbox_named::FLD_DESCRIPTION, 'component_type_id', 'word_id_row', 'link_type_id', formula::FLD_ID, 'word_id_col', 'word_id_col2', sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT component_id,
                     component_name,
                     description,
                     component_type_id,
                     word_id_row,
                     link_type_id,
                     formula_id,
                     word_id_col,
                     word_id_col2,
                     excluded
                FROM components
               WHERE component_id = ?;";
        $t->display('MySQL component load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the component load SQL creation
        $db_con->set_class(component::class);
        $db_con->set_usr_fields(array(sandbox_named::FLD_DESCRIPTION));
        $db_con->set_usr_num_fields(array('component_type_id', 'word_id_row', 'link_type_id', formula::FLD_ID, 'word_id_col', 'word_id_col2', sandbox::FLD_EXCLUDED));
        $db_con->set_where_std(1);
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " s.component_id,
                       u.component_id AS user_component_id,
                       s.user_id,
                       IF(u.component_name IS NULL,    s.component_name,    u.component_name)    AS component_name,
                       IF(u.description IS NULL,            s.description,            u.description)            AS description,
                       IF(u.component_type_id IS NULL, s.component_type_id, u.component_type_id) AS component_type_id,
                       IF(u.word_id_row IS NULL,            s.word_id_row,            u.word_id_row)            AS word_id_row,
                       IF(u.link_type_id IS NULL,           s.link_type_id,           u.link_type_id)           AS link_type_id,
                       IF(u.formula_id IS NULL,             s.formula_id,             u.formula_id)             AS formula_id,
                       IF(u.word_id_col IS NULL,            s.word_id_col,            u.word_id_col)            AS word_id_col,
                       IF(u.word_id_col2 IS NULL,           s.word_id_col2,           u.word_id_col2)           AS word_id_col2,
                       IF(u.excluded IS NULL,               s.excluded,               u.excluded)               AS excluded
                  FROM components s
             LEFT JOIN user_components u ON s.component_id = u.component_id 
                                             AND u.user_id = 1 
                 WHERE s.component_id = ?;";
        $t->display('MySQL component load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the triple load_standard SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID);
        $db_con->set_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION, 'phrase_type_id', sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT 
                        triple_id,
                        from_phrase_id,
                        to_phrase_id,
                        verb_id,
                        name_given,
                        description,
                        phrase_type_id,
                        excluded
                   FROM triples 
                  WHERE triple_id = 1;";
        $t->display('MySQL triple load_standard select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

        // test the triple load SQL creation
        $db_con->set_class(triple::class);
        $db_con->set_link_fields(triple::FLD_FROM, triple::FLD_TO, verb::FLD_ID);
        $db_con->set_usr_fields(array(triple::FLD_NAME_GIVEN, sandbox_named::FLD_DESCRIPTION));
        $db_con->set_fields(array('phrase_type_id'));
        $db_con->set_usr_num_fields(array(sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('triple_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $sql_avoid_code_check_prefix = "SELECT";
        $expected_sql = $sql_avoid_code_check_prefix . " 
                        s.triple_id, 
                        u.triple_id AS user_triple_id, 
                        s.user_id, 
                        s.from_phrase_id,
                        s.to_phrase_id, 
                        s.verb_id, 
                        s.phrase_type_id, 
                        IF(u.name_given  IS NULL, s.name_given,  u.name_given)  AS name_given, 
                        IF(u.description IS NULL, s.description, u.description) AS description,
                        IF(u.excluded    IS NULL, s.excluded,    u.excluded)    AS excluded 
                   FROM triples s 
              LEFT JOIN user_triples u ON s.triple_id = u.triple_id 
                                         AND u.user_id = 1 
                  WHERE triple_id = 1;";
        $t->display('MySQL triple load select by id', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                       " . $db_con->get_usr_field(formula::FLD_FORMULA_TEXT, 'f', 'u') . ",
                       " . $db_con->get_usr_field(formula::FLD_FORMULA_USER_TEXT, 'f', 'u') . ",
                       " . $db_con->get_usr_field(sandbox_named::FLD_DESCRIPTION, 'f', 'u') . ",
                       " . $db_con->get_usr_field(formula::FLD_TYPE, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(sql::FLD_CODE_ID, 't', 'c') . ",
                       " . $db_con->get_usr_field(formula::FLD_ALL_NEEDED, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(formula::FLD_LAST_UPDATE, 'f', 'u', sql_db::FLD_FORMAT_VAL) . ",
                       " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'f', 'u', sql_db::FLD_FORMAT_VAL) . "
                  FROM " . $sql_from . " 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = 1 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE " . $sql_where . "
              GROUP BY f.formula_id;";
        $expected_sql = "SELECT f.formula_id,
                       f.formula_name,
                       CASE WHEN (u.formula_text      <> '' IS NOT TRUE) THEN f.formula_text      ELSE u.formula_text      END AS formula_text,
                       CASE WHEN (u.resolved_text     <> '' IS NOT TRUE) THEN f.resolved_text     ELSE u.resolved_text     END AS resolved_text,
                       CASE WHEN (u.description       <> '' IS NOT TRUE) THEN f.description       ELSE u.description       END AS description,
                       CASE WHEN (u.formula_type_id         IS     NULL) THEN f.formula_type_id   ELSE u.formula_type_id   END AS formula_type_id,
                       CASE WHEN (c.code_id           <> '' IS NOT TRUE) THEN t.code_id           ELSE c.code_id           END AS code_id,
                       CASE WHEN (u.all_values_needed       IS     NULL) THEN f.all_values_needed ELSE u.all_values_needed END AS all_values_needed,
                       CASE WHEN (u.last_update             IS     NULL) THEN f.last_update       ELSE u.last_update       END AS last_update,
                       CASE WHEN (u.excluded                IS     NULL) THEN f.excluded          ELSE u.excluded          END AS excluded
                  FROM formula_links l, formulas f 
             LEFT JOIN user_formulas u ON u.formula_id = f.formula_id 
                                      AND u.user_id = 1 
             LEFT JOIN formula_types t ON f.formula_type_id = t.formula_type_id
             LEFT JOIN formula_types c ON u.formula_type_id = c.formula_type_id
                 WHERE l.phrase_id = 1 AND l.formula_id = f.formula_id
              GROUP BY f.formula_id;";
        $t->display('formula list load query', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the phrase load word query
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = 'SELECT w.word_id AS id, 
                    ' . $db_con->get_usr_field("word_name", "w", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM words w   
                 LEFT JOIN user_words u ON u.word_id = w.word_id
                                       AND u.user_id = 1
                  GROUP BY w.word_id, w.word_name ;';
        $expected_sql = "SELECT w.word_id AS id, 
                       CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name    ELSE          u.word_name   END AS word_name,
                       CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id
                                        AND u.user_id = 1
                   GROUP BY w.word_id, w.word_name ;";
        $t->display('phrase load word query', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the phrase load word link query
        $db_con->db_type = sql_db::POSTGRES;
        $created_sql = 'SELECT l.triple_id * -1 AS id,
                    ' . $db_con->get_usr_field("name", "l", "u") . ',
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                      FROM triples l
                 LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                            AND u.user_id = 1
                  GROUP BY l.triple_id, l.name ;';
        $expected_sql = "SELECT l.triple_id * -1 AS id,
                       CASE WHEN (u.name  <> '' IS NOT TRUE) THEN          l.name ELSE          u.name   END AS name,
                       CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM triples l
                 LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                            AND u.user_id = 1
                  GROUP BY l.triple_id, l.name ;";
        $t->display('phrase load word link query', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the phrase load word link query by type
        $db_con->db_type = sql_db::POSTGRES;
        $sql_where_exclude = '(excluded <> 1 OR excluded is NULL)';
        $created_sql = 'SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 2 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . ';';
        $expected_sql = "SELECT from_phrase_id FROM (
                        SELECT DISTINCT
                               l.from_phrase_id,    
                    CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 2 
                           AND l.verb_id = 2 ) AS a 
                         WHERE (excluded <> 1 OR excluded is NULL);";
        $t->display('phrase load word link query by type', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the view component link query by type (used in word_display->assign_dsp_ids)
        $db_con->db_type = sql_db::POSTGRES;
        $db_con->set_class(component_link::class);
        //$db_con->set_join_fields(array('position_type'), 'position_type');
        $db_con->set_fields(array(view::FLD_ID, component::FLD_ID));
        $db_con->set_usr_num_fields(array('order_nbr', 'position_type_id', sandbox::FLD_EXCLUDED));
        $db_con->set_where_text('s.component_id = 1');
        $created_sql = $db_con->select_by_set_id();
        $expected_sql = "SELECT s.component_link_id,
                     u.component_link_id AS user_component_link_id,
                     s.user_id,
                     s.view_id, 
                     s.component_id,
                     CASE WHEN (u.order_nbr        IS NULL) THEN s.order_nbr        ELSE u.order_nbr        END AS order_nbr,
                     CASE WHEN (u.position_type_id IS NULL) THEN s.position_type_id ELSE u.position_type_id END AS position_type_id,
                     CASE WHEN (u.excluded         IS NULL) THEN s.excluded         ELSE u.excluded         END AS excluded
                FROM component_links s
           LEFT JOIN user_component_links u ON s.component_link_id = u.component_link_id 
                                            AND u.user_id = 1  
               WHERE s.component_id = 1;";
        $t->display('phrase load word link query by type', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the view component link max order number query (used in word_display->next_nbr)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " max(m.order_nbr) AS max_order_nbr
                FROM ( SELECT 
                              " . $db_con->get_usr_field("order_nbr", "l", "u", sql_db::FLD_FORMAT_VAL) . " 
                          FROM component_links l 
                    LEFT JOIN user_component_links u ON u.component_link_id = l.component_link_id 
                                                      AND u.user_id = 1 
                        WHERE l.view_id = 1 ) AS m;";
        $expected_sql = "SELECT max(m.order_nbr) AS max_order_nbr
                       FROM ( SELECT CASE WHEN (u.order_nbr   IS NULL) THEN l.order_nbr   ELSE u.order_nbr   END AS order_nbr
                                FROM component_links l 
                           LEFT JOIN user_component_links u ON u.component_link_id = l.component_link_id 
                                                                AND u.user_id = 1
                               WHERE l.view_id = 1 ) AS m;";
        $t->display('phrase load word link query by type', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                                                AND u.user_id = 1
                         WHERE l.to_phrase_id = 1 
                           AND l.verb_id = 2 ) AS a 
                         WHERE ' . $sql_where_exclude . '  ';
        $sql_wrd_other = 'SELECT from_phrase_id FROM (
                          SELECT DISTINCT
                                 l.from_phrase_id,    
                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                            FROM triples l
                       LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                  AND u.user_id = 1
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
                                         AND u.user_id = 1
                       WHERE w.word_id NOT IN ( ' . $sql_wrd_other . ')                                        
                         AND w.word_id = a.id    
                    GROUP BY name ) AS w 
                       WHERE ' . $sql_where_exclude . ';';
        $expected_sql = "SELECT id, name, excluded FROM (
                         SELECT DISTINCT
                                w.word_id AS id, 
                                CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
                                CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                           FROM ( SELECT from_phrase_id AS id FROM (
                                      SELECT DISTINCT
                                             l.from_phrase_id,
                                             CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                                        FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                              AND u.user_id = 1
                                       WHERE l.to_phrase_id = 1 
                                         AND l.verb_id = 2 ) AS a 
                         WHERE (excluded <> 1 OR excluded is NULL)  ) a, words w LEFT JOIN user_words u ON u.word_id = w.word_id 
                                                                   AND u.user_id = 1
                          WHERE w.word_id NOT IN ( SELECT from_phrase_id FROM (
                                                       SELECT DISTINCT
                                                              l.from_phrase_id,    
                                                              CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(l.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                                                         FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                                                      AND u.user_id = 1
                                                        WHERE l.to_phrase_id <> 1 
                                                          AND l.verb_id = 2
                                                          AND l.from_phrase_id IN ( SELECT from_phrase_id AS id FROM (
                                                                                            SELECT DISTINCT
                                                                                                   l.from_phrase_id,
                                                                                                   CASE WHEN (u.excluded              IS     NULL) THEN COALESCE(l.excluded,0)    ELSE COALESCE(u.excluded,0) END AS excluded
                                                                                              FROM triples l LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                                                                                           AND u.user_id = 1
                                                                                             WHERE l.to_phrase_id = 1 
                                                                                               AND l.verb_id = 2 ) AS a 
                                                                                     WHERE (excluded <> 1 OR excluded is NULL)  
                                                                                  )  
                                                     GROUP BY l.from_phrase_id ) AS o 
                                                        WHERE (excluded <> 1 OR excluded is NULL)
                                                 )                                        
                            AND w.word_id = a.id    
                       GROUP BY name ) AS w 
                    WHERE (excluded <> 1 OR excluded is NULL);";
        $t->display('phrase load word link query by type', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                                        AND u.user_id = 1 
                      WHERE w.phrase_type_id = 2
                        " . $sql_where_and . "            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
        $expected_sql = "SELECT id, name 
              FROM ( SELECT w.word_id AS id, 
                                CASE WHEN (u.word_name  <> '' IS NOT TRUE) THEN          w.word_name ELSE          u.word_name   END AS name,
                                CASE WHEN (u.excluded         IS     NULL) THEN COALESCE(w.excluded,0)  ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM triples l, words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 1 
                      WHERE w.phrase_type_id = 2
                        AND w.word_id = l.from_phrase_id 
                        AND l.verb_id = 2              
                        AND l.to_phrase_id = 14            
                   GROUP BY name) AS s
            WHERE (excluded <> 1 OR excluded is NULL)                                    
          ORDER BY name;";
        $t->display('time word selector query by type', $lib->trim($expected_sql), $lib->trim($created_sql));

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
        $expected_sql = "SELECT * FROM (
            SELECT verb_id AS id, 
                   CASE WHEN (name_reverse  <> '' IS NOT TRUE AND name_reverse <> verb_name) THEN CONCAT(verb_name, ' (', name_reverse, ')') ELSE verb_name END AS name,
                   words
              FROM verbs 
      UNION SELECT verb_id * -1 AS id, 
                   CONCAT(name_reverse, ' (', verb_name, ')') AS name,
                   words
              FROM verbs 
             WHERE name_reverse <> '' 
               AND name_reverse <> verb_name) AS links
          ORDER BY words DESC, name;";
        $t->display('verb selector query', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                       " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
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
        $expected_sql = "SELECT l.triple_id,
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
                       CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                       t2.word_id AS word_id2,
                       t2.user_id AS user_id2,
                       CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name,
                       CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural,
                       CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description,
                       CASE WHEN (u2.phrase_type_id      IS     NULL) THEN t2.phrase_type_id ELSE u2.phrase_type_id END AS phrase_type_id,
                       CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded,
                       t2.values AS values2
                  FROM triples l
             LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id 
                                        AND ul.user_id = 1,
                       verbs v, 
                       words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id 
                                                       AND u2.user_id = 1 
                 WHERE l.verb_id = v.verb_id 
                   AND l.from_phrase_id = t2.word_id 
                   AND l.to_phrase_id   = 3
                       AND l.verb_id = 2 
              GROUP BY t2.word_id, l.verb_id
              ORDER BY l.verb_id, word_name;";
        $t->display('word link list load query', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                       " . $db_con->get_usr_field(sandbox::FLD_EXCLUDED, 'l', 'ul', sql_db::FLD_FORMAT_VAL) . ",
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
        $expected_sql = "SELECT l.triple_id,
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
                            CASE WHEN (ul.excluded          IS     NULL) THEN l.excluded      ELSE ul.excluded     END AS excluded,
                            t2.word_id AS word_id2,
                            t2.user_id AS user_id2,
                            CASE WHEN (u2.word_name   <> '' IS NOT TRUE) THEN t2.word_name    ELSE u2.word_name    END AS word_name,
                            CASE WHEN (u2.plural      <> '' IS NOT TRUE) THEN t2.plural       ELSE u2.plural       END AS plural,
                            CASE WHEN (u2.description <> '' IS NOT TRUE) THEN t2.description  ELSE u2.description  END AS description,
                            CASE WHEN (u2.phrase_type_id      IS     NULL) THEN t2.phrase_type_id ELSE u2.phrase_type_id END AS phrase_type_id,
                            CASE WHEN (u2.excluded          IS     NULL) THEN t2.excluded     ELSE u2.excluded     END AS excluded,
                            t2.values AS values2                  
                       FROM triples l LEFT JOIN user_triples ul ON ul.triple_id = l.triple_id
                                                                     AND ul.user_id = 1,
                            verbs v,
                            words t2 LEFT JOIN user_words u2 ON u2.word_id = t2.word_id
                                                            AND u2.user_id = 1
                      WHERE l.verb_id = v.verb_id
                        AND l.from_phrase_id = t2.word_id
                        AND l.to_phrase_id   = 3
                        AND l.verb_id = 2
                   ORDER BY l.verb_id, word_name;";
        $t->display('phrase load word link query by ...', $lib->trim($expected_sql), $lib->trim($created_sql));

        // the general phrase list query (as created in phrase->sql_list)
        $db_con->db_type = sql_db::POSTGRES;
        $sql_words = 'SELECT DISTINCT w.word_id AS id, 
                                  ' . $db_con->get_usr_field("word_name", "w", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                  ' . $db_con->get_usr_field("excluded", "w", "u", sql_db::FLD_FORMAT_BOOL) . '
                             FROM words w   
                        LEFT JOIN user_words u ON u.word_id = w.word_id 
                                              AND u.user_id = 1 ';
        $sql_triples = 'SELECT DISTINCT l.triple_id * -1 AS id, 
                                    ' . $db_con->get_usr_field("name", "l", "u", sql_db::FLD_FORMAT_TEXT, "phrase_name") . ',
                                    ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                               FROM triples l
                          LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                     AND u.user_id = 1 ';
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $expected_sql = "SELECT DISTINCT 
                            id, phrase_name
              FROM ( SELECT DISTINCT
                            w.word_id AS id, 
                            CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name            ELSE u.word_name            END AS phrase_name,
                            CASE WHEN (u.excluded        IS     NULL) THEN COALESCE(w.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM words w   
                  LEFT JOIN user_words u ON u.word_id = w.word_id 
                                        AND u.user_id = 1
               UNION SELECT DISTINCT
                            l.triple_id * -1 AS id, 
                            CASE WHEN (u.name   <> '' IS NOT TRUE) THEN l.name       ELSE u.name       END AS phrase_name,
                            CASE WHEN (u.excluded               IS     NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                       FROM triples l
                  LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                             AND u.user_id = 1
                   ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $t->display('general phrase list query', $lib->trim($expected_sql), $lib->trim($created_sql));

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
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE ' . $sql_where_exclude . ' ';
        $sql_wrd_other = 'SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                               ' . $db_con->get_usr_field("excluded", "l", "u", sql_db::FLD_FORMAT_BOOL) . '
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
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
                                         AND u.user_id = 1
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
                                                AND u.user_id = 1
                         WHERE l.from_phrase_id IN ( ' . $sql_wrd_other . ')                                        
                           AND l.verb_id = 2
                           AND l.to_phrase_id = 2 ) AS t 
                         WHERE ' . $sql_where_exclude . ' ';
        $sql_avoid_code_check_prefix = "SELECT";
        $created_sql = $sql_avoid_code_check_prefix . " DISTINCT id, phrase_name
              FROM ( " . $sql_words . " UNION " . $sql_triples . " ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $expected_sql = "SELECT DISTINCT id, phrase_name
              FROM ( SELECT DISTINCT id, phrase_name, excluded FROM (
                      SELECT DISTINCT
                             w.word_id AS id, 
                              CASE WHEN (u.word_name <> '' IS NOT TRUE) THEN w.word_name            ELSE u.word_name            END AS phrase_name,
                              CASE WHEN (u.excluded        IS     NULL) THEN COALESCE(w.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                        FROM ( SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0  ) a, words w
                   LEFT JOIN user_words u ON u.word_id = w.word_id 
                                         AND u.user_id = 1
                       WHERE w.word_id NOT IN ( SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id <> 2 
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0 ) ) AS o 
                                         WHERE excluded = 0  )                                        
                         AND w.word_id = a.id ) AS w 
                       WHERE excluded = 0  UNION SELECT DISTINCT id, phrase_name, excluded FROM (
                        SELECT DISTINCT
                               l.triple_id * -1 AS id, 
                                CASE WHEN (u.name <> '' IS NOT TRUE) THEN l.name       ELSE u.name       END AS phrase_name,
                                CASE WHEN (u.excluded             IS     NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                          FROM triples l
                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                AND u.user_id = 1
                         WHERE l.from_phrase_id IN ( SELECT from_phrase_id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id <> 2 
                                           AND l.verb_id = 2
                                           AND l.from_phrase_id IN (SELECT from_phrase_id AS id FROM (
                                        SELECT DISTINCT
                                               l.from_phrase_id,    
                                                CASE WHEN (u.excluded IS NULL) THEN COALESCE(l.excluded,0) ELSE COALESCE(u.excluded,0) END AS excluded
                                          FROM triples l
                                     LEFT JOIN user_triples u ON u.triple_id = l.triple_id 
                                                                AND u.user_id = 1
                                         WHERE l.to_phrase_id = 2 
                                           AND l.verb_id = 2 ) AS a 
                                         WHERE excluded = 0 ) ) AS o 
                                         WHERE excluded = 0 )                                        
                           AND l.verb_id = 2
                           AND l.to_phrase_id = 2 ) AS t 
                         WHERE excluded = 0  ) AS p
             WHERE excluded = 0
          ORDER BY p.phrase_name;";
        $t->display('general phrase list query by type', $lib->trim($expected_sql), $lib->trim($created_sql));


        $t->subheader($ts . 'user sandbox sql creation');

        // init
        $t->name = '_sandbox->';
        $t->resource_path = 'db/sandbox/';

        // the word changer query (used in _sandbox->changer_sql)
        $wrd = new word($usr);
        $wrd->set_id(1);
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

}