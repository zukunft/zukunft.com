<?php

/*

    test/unit/formula_value.php - unit testing of the FORMULA VALUE functions
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

class formula_value_unit_tests
{
    function run(testing $t)
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'formula_value->';
        $t->resource_path = 'db/result/';
        $usr->id = 1;

        $t->header('Unit tests of the formula value class (src/main/php/model/formula/formula_value.php)');

        $t->subheader('SQL creation tests');

        // check the sql to load a formula value by the id
        $fv = new formula_value($usr);
        $fv->id = 1;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $fv->load_by_id_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of PostgreSQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $fv->load_by_id_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);

        // check the sql to load a formula value by the phrase group
        $fv->reset($usr);
        $fv->phr_grp_id = 1;
        $db_con->db_type = sql_db::POSTGRES;
        $qp = $fv->load_by_grp_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of PostgreSQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $fv->load_by_grp_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);

        // ... and additional to the phrase group the time
        $db_con->db_type = sql_db::POSTGRES;
        $fv->time_id = 2;
        $qp = $fv->load_by_grp_time_sql($db_con);
        $t->assert_qp($qp, sql_db::POSTGRES);

        // ... and the same for MySQL databases instead of PostgreSQL
        $db_con->db_type = sql_db::MYSQL;
        $qp = $fv->load_by_grp_time_sql($db_con);
        $t->assert_qp($qp, sql_db::MYSQL);

        $t->subheader('Display tests');

        // test phrase based default formatter
        // ... for big values
        $wrd_const = $t->create_word(word::TN_READ);
        $phr_lst = new phrase_list($usr);
        $phr_lst->add($wrd_const->phrase());
        $fv->phr_lst = $phr_lst;
        $fv->value = 123456;
        $t->assert('formula_value->val_formatted test big numbers', $fv->val_formatted(), "123'456");

        // ... for small values 12.35 instead of 12.34 due to rounding
        $fv->value = 12.3456;
        $t->assert('formula_value->val_formatted test small numbers', $fv->val_formatted(), "12.35");

        // ... for percent values
        $fv = new formula_value($usr);
        $wrd_pct = $t->create_word(word::TN_PCT, word_type_list::DBL_PERCENT);
        $phr_lst->add($wrd_pct->phrase());
        $fv->phr_lst = $phr_lst;
        $fv->value = 0.01234;
        $t->assert('formula_value->val_formatted test percent formatting', $fv->val_formatted(), '1.23 %');


        $t->header('Unit tests of the formula value list class (src/main/php/model/formula/formula_value_list.php)');

        $t->subheader('SQL creation tests');

        // sql to load a list of formula values by the formula id
        $fv_lst = new formula_value_list($usr);
        $frm = new formula($usr);
        $frm->id = 1;
        $t->assert_load_list_sql($db_con, $fv_lst, $frm);

        // sql to load a list of formula values by the phrase group id
        $fv_lst = new formula_value_list($usr);
        $grp = new phrase_group($usr);
        $grp->id = 2;
        $t->assert_load_list_sql($db_con, $fv_lst, $grp);

        // ... and additional select by time
        $time_phr = new phrase($usr);
        $time_phr->id = 3;
        $t->assert_load_list_sql($db_con, $fv_lst, $grp, $time_phr);

        // sql to load a list of formula values by the source phrase group id
        $fv_lst = new formula_value_list($usr);
        $grp = new phrase_group($usr);
        $grp->id = 2;
        $t->assert_load_list_sql($db_con, $fv_lst, $grp, null, true);

        // ... and additional select by time
        $t->assert_load_list_sql($db_con, $fv_lst, $grp, $time_phr, true);

        // sql to load a list of formula values by the word id
        $fv_lst = new formula_value_list($usr);
        $wrd = new word($usr);
        $wrd->id = 2;
        $t->assert_load_list_sql($db_con, $fv_lst, $wrd);

        // sql to load a list of formula values by the triple id
        $fv_lst = new formula_value_list($usr);
        $trp = new word_link($usr);
        $trp->id = 3;
        $t->assert_load_list_sql($db_con, $fv_lst, $trp);

    }

}