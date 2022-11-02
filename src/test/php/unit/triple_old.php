<?php

/*

  test/unit/triple.php - unit testing of the word link / triple functions
  -----------------------
  

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

// TODO combine with triple_unit_test

class triple_unit_tests_old
{
    function run(testing $t): void
    {

        global $usr;

        // init
        $db_con = new sql_db();
        $t->name = 'triple->';
        $t->resource_path = 'db/triple/';
        $json_file = 'unit/triple/pi.json';
        $usr->id = 1;

        $t->header('Unit tests of the word class (src/main/php/model/word/triple.php)');


        $t->subheader('SQL statement tests');

        // sql to load a triple by id
        $trp = new triple($usr);
        $trp->id = 1;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load a triple by name
        $trp = new triple($usr);
        $trp->name = phrase::TN_ZH_COMPANY;
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);

        // sql to load a triple by link ids
        $trp = new triple($usr);
        $wrd_from = new word($usr);
        $wrd_from->id = 2;
        $vrb = new verb();
        $vrb->id = 3;
        $wrd_to = new word($usr);
        $wrd_to->id = 4;
        $trp->from = $wrd_from->phrase();
        $trp->verb = $vrb;
        $trp->to = $wrd_to->phrase();
        $t->assert_load_sql($db_con, $trp);
        $t->assert_load_standard_sql($db_con, $trp);
        $trp->id = 5;
        $t->assert_not_changed_sql($db_con, $trp);
        $t->assert_user_config_sql($db_con, $trp);

        // sql to check the usage of a triple

        $t->subheader('Im- and Export tests');

        $t->assert_json(new triple($usr), $json_file);
    }

}