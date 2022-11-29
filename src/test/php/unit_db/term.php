<?php

/*

    /test/php/unit_save/term.php - TESTing of the TERM database read functions
    ----------------------------
  

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

class term_unit_db_tests
{
    function run(testing $t): void
    {

        global $db_con;
        global $usr;

        // init
        $t->name = 'term->';

        $t->header('Test the term class (src/main/php/model/term.php)');

        // test load by term by a word db row
        $wrd = new word($usr);
        $qp = $wrd->load_sql_by_id($db_con,1, word::class);
        $db_row = $db_con->get1($qp);
        $trm = new term($usr);
        $trm->row_mapper_obj($db_row, word::class);
        $t->assert($t->name . ' word row mapper', $trm->name(), word::TN_READ);
        $trm_by_obj_id = new term($usr);
        $trm_by_obj_id->load_by_obj_id($trm->id_obj(), word::class);
        $t->assert($t->name . ' word by object id', $trm_by_obj_id->name(), word::TN_READ);

        // test load by term by a triple db row
        $trp = new triple($usr);
        $qp = $trp->load_sql_by_id($db_con,1, triple::class);
        $db_row = $db_con->get1($qp);
        $trm = new term($usr, triple::class);
        $trm->row_mapper_obj($db_row, triple::class);
        $t->assert($t->name . ' triple row mapper', $trm->name(), triple::TN_READ_NAME);
        $trm_by_obj_id = new term($usr);
        $trm_by_obj_id->load_by_obj_id($trm->id_obj(), triple::class);
        $t->assert($t->name . ' triple by object id', $trm_by_obj_id->name(), triple::TN_READ_NAME);

        // test load by term by a formula db row
        $frm = new formula($usr);
        $qp = $frm->load_sql_by_id($db_con,1, formula::class);
        $db_row = $db_con->get1($qp);
        $trm = new term($usr, formula::class);
        $trm->row_mapper_obj($db_row, formula::class);
        $t->assert($t->name . ' formula row mapper', $trm->name(), formula::TN_READ);
        $trm_by_obj_id = new term($usr);
        $trm_by_obj_id->load_by_obj_id($trm->id_obj(), formula::class);
        $t->assert($t->name . ' formula by object id', $trm_by_obj_id->name(), formula::TN_READ);

        // test load by term by a verb db row
        $vrb = new verb();
        $qp = $vrb->load_sql_by_id($db_con,1);
        $db_row = $db_con->get1($qp);
        $trm = new term($usr, verb::class);
        $trm->row_mapper_obj($db_row, verb::class);
        $t->assert($t->name . ' verb row mapper', $trm->name(), verb::TN_READ);
        $trm_by_obj_id = new term($usr);
        $trm_by_obj_id->load_by_obj_id($trm->id_obj(), verb::class);
        $t->assert($t->name . ' verb by object id', $trm_by_obj_id->name(), verb::TN_READ);

        // test loading by term by id and name
        $trm = new term($usr, word::class);
        $t->assert_load($trm, word::TN_READ);


    }
}

