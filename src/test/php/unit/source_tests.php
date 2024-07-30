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

namespace unit;

use api\ref\source as source_api;
use cfg\db\sql;
use cfg\db\sql_type;
use cfg\source_list;
use cfg\source_type_list;
use html\ref\source as source_dsp;
use cfg\source;
use cfg\db\sql_db;
use test\test_cleanup;

class source_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init for source
        $db_con = new sql_db();
        $sc = new sql();
        $t->name = 'source->';
        $t->resource_path = 'db/ref/';
        $json_file = 'unit/ref/bipm.json';

        $t->header('Unit tests of the source class (src/main/php/model/ref/source.php)');

        $t->subheader('SQL statement tests');
        $src = new source($usr);
        $t->assert_sql_table_create($src);
        $t->assert_sql_index_create($src);
        $t->assert_sql_foreign_key_create($src);
        $t->assert_sql_by_id($sc, $src);
        $t->assert_sql_by_name($sc, $src);
        $t->assert_sql_by_code_id($sc, $src);

        // sql to load a source by id
        $src = new source($usr);
        $src->set_id(4);
        $t->assert_sql_standard($sc, $src);

        // sql to load a source by name
        $src = new source($usr);
        $src->set_name(source_api::TN_READ);
        $t->assert_sql_standard($sc, $src);
        $src->set_id(5);
        $t->assert_sql_not_changed($sc, $src);
        $t->assert_sql_user_changes($sc, $src);

        // sql to load the source types
        $source_type_list = new source_type_list();
        $t->assert_sql_all($sc, $source_type_list);

        $t->subheader('source sql write');
        // TODO test the log version for db write
        $src = $t->source();
        $t->assert_sql_insert($sc, $src);
        $t->assert_sql_insert($sc, $src, [sql_type::USER]);
        $t->assert_sql_insert($sc, $src, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $src, [sql_type::LOG, sql_type::USER]);
        $src_renamed = $src->cloned(source_api::TN_RENAMED);
        $t->assert_sql_update($sc, $src_renamed, $src);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::USER]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $src);
        $t->assert_sql_delete($sc, $src, [sql_type::USER]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader('Im- and Export tests');
        $t->assert_json_file(new source($usr), $json_file);

        $t->subheader('source api unit tests');
        $src = $t->source1();
        $t->assert_api_json($src);
        $src = $t->source();
        $t->assert_api_msg($db_con, $src);

        $t->subheader('source frontend unit tests');
        $t->assert_api_to_dsp($src, new source_dsp());


        // init for source list
        $t->name = 'source_list->';

        $src_lst = new source_list($usr);
        $trm_ids = array(1, 2, 3);
        $t->assert_sql_by_ids($sc, $src_lst, $trm_ids);
        $src_lst = new source_list($usr);
        $t->assert_sql_like($sc, $src_lst);

    }

}

