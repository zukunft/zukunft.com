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
use cfg\db\sql_creator;
use cfg\db\sql_type;
use cfg\ref\source_type_list;
use html\ref\source as source_dsp;
use cfg\ref\source;
use cfg\db\sql_db;
use test\test_cleanup;

class source_tests
{
    function run(test_cleanup $t): void
    {

        global $usr;

        // init for source
        $sc = new sql_creator();
        $t->name = 'source->';
        $t->resource_path = 'db/ref/';

        $t->header('source unit tests');

        $t->subheader('source sql setup');
        $src = new source($usr);
        $t->assert_sql_table_create($src);
        $t->assert_sql_index_create($src);
        $t->assert_sql_foreign_key_create($src);

        $t->subheader('source sql read');
        $t->assert_sql_by_id($sc, $src);
        $t->assert_sql_by_name($sc, $src);
        $t->assert_sql_by_code_id($sc, $src);

        $t->subheader('source sql read standard and user changes by id');
        $src = new source($usr);
        $src->set_id(4);
        $t->assert_sql_standard($sc, $src);
        $src->set_id(5);
        $t->assert_sql_not_changed($sc, $src);
        $t->assert_sql_user_changes($sc, $src);

        $t->subheader('source sql read standard by name');
        $src = new source($usr);
        $src->set_name(source_api::TN_READ_REF);
        $t->assert_sql_standard($sc, $src);

        $t->subheader('source sql write insert');
        // TODO test the log version for db write
        $src = $t->source();
        $t->assert_sql_insert($sc, $src);
        $t->assert_sql_insert($sc, $src, [sql_type::USER]);
        $t->assert_sql_insert($sc, $src, [sql_type::LOG]);
        $t->assert_sql_insert($sc, $src, [sql_type::LOG, sql_type::USER]);

        $t->subheader('source sql write update');
        $src_renamed = $src->cloned(source_api::TN_RENAMED);
        $t->assert_sql_update($sc, $src_renamed, $src);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::USER]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::USER]);
        $src_renamed->exclude();
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::EXCLUDE]);
        $t->assert_sql_update($sc, $src_renamed, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);
        $src_only_excluded = clone $src;
        $src_only_excluded->exclude();
        $t->assert_sql_update($sc, $src_only_excluded, $src, [sql_type::LOG, sql_type::EXCLUDE]);
        $t->assert_sql_update($sc, $src_only_excluded, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader('source sql delete');
        $t->assert_sql_delete($sc, $src);
        $t->assert_sql_delete($sc, $src, [sql_type::USER]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER]);
        $t->assert_sql_delete($sc, $src, [sql_type::USER, sql_type::EXCLUDE]);
        $t->assert_sql_delete($sc, $src, [sql_type::LOG, sql_type::USER, sql_type::EXCLUDE]);

        $t->subheader('source api unit tests');
        $src = $t->source();
        $t->assert_api_json($src);
        $db_con = new sql_db();
        $t->assert_api_msg($db_con, $src);

        $t->subheader('source frontend unit tests');
        $t->assert_api_to_dsp($src, new source_dsp());

        $t->subheader('source import and export tests');
        $t->assert_ex_and_import($t->source());
        $t->assert_ex_and_import($t->source_filled());
        $json_file = 'unit/ref/bipm.json';
        $t->assert_json_file(new source($usr), $json_file);


        $t->header('source type unit tests');

        $t->subheader('source type sql read');
        $source_type_list = new source_type_list();
        $t->assert_sql_all($sc, $source_type_list);

    }

}

