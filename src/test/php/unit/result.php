<?php

/*

    test/unit/result.php - unit testing of the FORMULA VALUE functions
    ---------------------------
  

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

namespace test;

include_once API_RESULT_PATH . 'result.php';

use api\result_api;
use api\word_api;
use html\result\result as result_dsp;
use model\phrase_list;
use model\result;
use model\sql_db;

class result_unit_tests
{

    function run(test_cleanup $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'result->';
        $t->resource_path = 'db/result/';
        $json_file = 'unit/result/result_import_part.json';
        $usr->set_id(1);


        $t->header('Unit tests of the result class (src/main/php/model/formula/result.php)');

        $t->subheader('SQL creation tests');

        // check the sql to load a result by the id
        $res = new result($usr);
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_sql_by_id($db_con, 1);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of Postgres
        $db_con->db_type = sql_db::MYSQL;
        $qp = $res->load_sql_by_id($db_con, 1);
        $t->assert_qp($qp, sql_db::MYSQL);

        // check the sql to load a result by the phrase group
        $res->reset($usr);
        $res->phr_grp_id = 1;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $res->load_by_grp_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of Postgres
        $db_con->db_type = sql_db::MYSQL;
        $qp = $res->load_by_grp_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);

        // ... and additional to the phrase group the time
        $db_con->db_type = sql_db::POSTGRES;
        $res->time_id = 2;
        $qp = $res->load_by_grp_time_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of Postgres
        $db_con->db_type = sql_db::MYSQL;
        $qp = $res->load_by_grp_time_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);

        $t->subheader('Display tests');

        // test phrase based default formatter
        // ... for big values
        $wrd_const = $t->new_word(word_api::TN_READ);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd_const->phrase());
        $res->phr_lst = $phr_lst;
        $res->value = result_api::TV_INT;
        $t->assert('result->val_formatted test big numbers', $res->val_formatted(), "123'456");

        // ... for small values 12.35 instead of 12.34 due to rounding
        $res->value = result_api::TV_FLOAT;
        $t->assert('result->val_formatted test small numbers', $res->val_formatted(), "12.35");

        // ... for percent values
        $res = $t->dummy_result_pct();
        $t->assert('result->val_formatted test percent formatting', $res->val_formatted(), '1.23 %');


        $t->subheader('Im- and Export tests');

        $t->assert_json_file(new result($usr), $json_file);


        $t->subheader('HTML frontend unit tests');

        $val = $t->dummy_result();
        $t->assert_api_to_dsp($val, new result_dsp());

    }

}